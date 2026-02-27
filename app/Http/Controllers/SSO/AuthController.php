<?php

namespace App\Http\Controllers\SSO;

    use Illuminate\Support\Str;
    use Illuminate\Http\Request;
    use InvalidArgumentException;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class AuthController extends Controller
    {
        private $ssoBaseUrl = 'https://sso.unpatti.ac.id';
        private $clientId;
        private $clientSecret;
        private $redirectUri;

        public function __construct()
        {
            $this->clientId = config('sso.client_id');
            $this->clientSecret = config('sso.client_secret');
            $this->redirectUri = config('sso.redirect_uri');
        }

        public function redirect(Request $request)
        {
            $request->session()->put('state', $state = Str::random(40));

            $query = http_build_query([
                'client_id' => $this->clientId,
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
                'scope' => '',
                'state' => $state,
            ]);

            return redirect($this->ssoBaseUrl . '/oauth/authorize?' . $query);
        }

        public function callback(Request $request)
        {
            $state = $request->session()->pull('state');

            throw_unless(
                strlen($state) > 0 && $state === $request->state,
                InvalidArgumentException::class,
                'Invalid state value.'
            );

            try {
                Log::info('SSO Callback Started', [
                    'code' => $request->code ? 'present' : 'missing',
                    'state' => $request->state ? 'present' : 'missing'
                ]);

                // Exchange code for token
                $response = Http::asForm()->post($this->ssoBaseUrl . '/oauth/token', [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri,
                    'code' => $request->code,
                ]);

                if (!$response->successful()) {
                    Log::error('SSO Token Exchange Failed', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return redirect()->route('login')->with('error', 'Gagal mendapatkan access token');
                }

                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'] ?? null;

                if (!$accessToken) {
                    Log::error('SSO Access Token Missing', ['token_data' => $tokenData]);
                    return redirect()->route('login')->with('error', 'Access token tidak ditemukan');
                }

                Log::info('SSO Token Exchange Success', ['token_present' => true]);

                // Get user info from SSO
                $userResponse = Http::withHeaders([
                    "Accept" => "application/json",
                    'Authorization' => 'Bearer ' . $accessToken
                ])->get($this->ssoBaseUrl . '/api/me/roles', [
                            'client_id' => $this->clientId
                        ]);

                if (!$userResponse->successful()) {
                    Log::error('SSO User Info Failed', [
                        'status' => $userResponse->status(),
                        'response' => $userResponse->body()
                    ]);
                    return redirect()->route('login')->with('error', 'Gagal mendapatkan data user');
                }

                $userInfo = $userResponse->json();

                // Detect role from email pattern
                $email = $userInfo['email'] ?? '';

                // PRIORITY 1: Check if user already exists in database (users table)
                // This handles cases where student email was converted to validator/admin
                $existingUser = \App\Models\User::where('email', $email)->first();

                if ($existingUser) {
                    Log::info('SSO Detected Existing User in Database', [
                        'email' => $email,
                        'role' => $existingUser->role
                    ]);
                    
                    // PERBAIKAN: Cek role user di database. 
                    // Jika dia student, arahkan ke penanganan mahasiswa.
                    if (strtolower($existingUser->role) === 'student') {
                        return $this->handleStudentLogin($userInfo, $accessToken, $request);
                    } 
                    
                    // Jika bukan student (admin/validator/dll), arahkan ke penanganan staff.
                    return $this->handleStaffLogin($userInfo, $accessToken, $request);
                }

            } catch (\Exception $e) {
                Log::error('SSO Callback Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->route('login')->with('error', 'Terjadi kesalahan saat autentikasi: ' . $e->getMessage());
            }
        }

        /**
         * Handle student login from SSO
         * PRINCIPLE: SSO as Identity Authority, SIAKAD as Profile Provider
         */
        private function handleStudentLogin($userInfo, $accessToken, Request $request)
        {
            // ========================================================================
            // STEP 1: SSO DATA IS AUTHORITATIVE FOR IDENTITY
            // ========================================================================
            $ssoEmail = $userInfo['email'] ?? '';
            $ssoName = $userInfo['name'] ?? $userInfo['full_name'] ?? 'student';
            $nimFromSSO = explode('@', $ssoEmail)[0]; // Extract NIM from email

            Log::info('SSO Student Login Started', [
                'sso_email' => $ssoEmail,
                'sso_name' => $ssoName,
                'nim_from_sso' => $nimFromSSO,
                'principle' => 'SSO as Identity Authority'
            ]);

            // ========================================================================
            // STEP 2: CREATE/UPDATE USER IN USERS TABLE (FIX REDIRECT LOOP)
            // ========================================================================
            $user = \App\Models\User::updateOrCreate(
                ['email' => $ssoEmail], // Match by SSO email (authoritative)
                [
                    'name' => $ssoName,
                    'role' => 'Student',
                    'provider' => 'unpatti_sso',
                    'provider_id' => $userInfo['id'] ?? $userInfo['user_id'] ?? null,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                    'last_login_method' => 'sso',
                ]
            );

            // ========================================================================
            // STEP 3: LOGIN USER WITH LARAVEL AUTH (STOP REDIRECT LOOP)
            // ========================================================================
            \Illuminate\Support\Facades\Auth::login($user);

            Log::info('User authenticated via Laravel Auth', [
                'user_id' => $user->id,
                'email' => $user->email,
                'auth_check' => \Illuminate\Support\Facades\Auth::check()
            ]);

            // ========================================================================
            // STEP 4: FETCH ADDITIONAL PROFILE FROM SIAKAD (FAULT TOLERANT)
            // ========================================================================
            $siakadProfile = null;
            $siakadFetchSuccess = false;

            try {
                $siakadService = app(\App\Services\SiakadApiService::class);
                $siakadData = $siakadService->getMahasiswaByNim($nimFromSSO);

                if ($siakadData && !empty($siakadData)) {
                    $siakadProfile = [
                        'foto_url' => $siakadData['foto_url'] ?? null,
                        'ipk' => $siakadData['registrasi']['ipk_kumulatif'] ?? null,
                        'fakultas' => $siakadData['fakultas']['nama'] ?? 'Data Belum Tersedia',
                        'fakultas_id' => $siakadData['fakultas']['id'] ?? null,
                        'jurusan' => $siakadData['jurusan']['nama'] ?? 'Data Belum Tersedia',
                        'jurusan_id' => $siakadData['jurusan']['id'] ?? null,
                        'program_studi' => $siakadData['program_studi']['nama'] ?? 'Data Belum Tersedia',
                        'program_studi_id' => $siakadData['program_studi']['id'] ?? null,
                        'angkatan' => substr($nimFromSSO, 0, 4),
                    ];
                    $siakadFetchSuccess = true;

                    Log::info('SIAKAD Profile Fetched Successfully', [
                        'nim' => $nimFromSSO,
                        'has_foto' => !empty($siakadProfile['foto_url']),
                        'has_ipk' => !empty($siakadProfile['ipk']),
                        'ipk_value' => $siakadProfile['ipk'],
                        'foto_url_value' => $siakadProfile['foto_url'],
                        'fakultas' => $siakadProfile['fakultas']
                    ]);
                } else {
                    Log::warning('SIAKAD API returned empty data', ['nim' => $nimFromSSO]);
                }
            } catch (\Exception $e) {
                Log::error('SIAKAD API Error (Non-Critical)', [
                    'nim' => $nimFromSSO,
                    'error' => $e->getMessage(),
                    'action' => 'Continue with SSO data only'
                ]);
            }

            // ========================================================================
            // STEP 5: MERGE SSO (IDENTITY) + SIAKAD (PROFILE) INTO SESSION
            // ========================================================================
            $studentProfile = [
                // Identity from SSO (AUTHORITATIVE - NEVER OVERWRITE)
                'nim' => $nimFromSSO,
                'nama' => $ssoName,
                'email' => $ssoEmail,
                
                // Additional profile from SIAKAD (OPTIONAL)
                'foto_url' => $siakadProfile['foto_url'] ?? null,
                'ipk' => $siakadProfile['ipk'] ?? null,
                'fakultas' => $siakadProfile['fakultas'] ?? 'Data Belum Tersedia',
                'fakultas_id' => $siakadProfile['fakultas_id'] ?? null,
                'jurusan' => $siakadProfile['jurusan'] ?? 'Data Belum Tersedia',
                'jurusan_id' => $siakadProfile['jurusan_id'] ?? null,
                'program_studi' => $siakadProfile['program_studi'] ?? 'Data Belum Tersedia',
                'program_studi_id' => $siakadProfile['program_studi_id'] ?? null,
                'angkatan' => $siakadProfile['angkatan'] ?? substr($nimFromSSO, 0, 4),
                
                // Metadata
                'data_source' => $siakadFetchSuccess ? 'SSO + SIAKAD' : 'SSO Only',
                'siakad_available' => $siakadFetchSuccess,
            ];

            session([
                'student_data' => $studentProfile, // Combined data for dashboard
                'student_profile' => $studentProfile, // Alias for compatibility
                'auth_role' => 'student',
                'student_id' => $nimFromSSO,
                'student_name' => $ssoName,
                'student_email' => $ssoEmail,
                'sso_access_token' => $accessToken,
                'sso_authenticated' => true,
            ]);

            Log::info('Student Session Created', [
                'nim' => $nimFromSSO,
                'email' => $ssoEmail,
                'siakad_profile_loaded' => $siakadFetchSuccess,
                'session_keys' => array_keys(session()->all())
            ]);

            // ========================================================================
            // STEP 6: CHECK IF STUDENT HAS ACHIEVEMENTS (NO DATABASE WRITE)
            // ========================================================================
            $student = \App\Models\Student::find($nimFromSSO);

            if (!$student) {
                // First time login, no achievements yet
                // DO NOT create student record - will be created on first achievement submission
                
                Log::info('SSO Student First Login - No Achievements Yet', [
                    'nim' => $nimFromSSO,
                    'email' => $ssoEmail,
                    'action' => 'Redirect to dashboard without creating student record'
                ]);

                $message = $siakadFetchSuccess 
                    ? 'Selamat datang! Silakan ajukan prestasi pertama Anda.' 
                    : 'Selamat datang! Beberapa data profil tidak tersedia. Silakan ajukan prestasi pertama Anda.';

                return redirect()->route('student.dashboard')
                    ->with($siakadFetchSuccess ? 'info' : 'warning', $message);
            }

            // Student exists (has achievements)
            Log::info('SSO Student Login Success - Has Achievements', [
                'nim' => $student->student_id,
                'email' => $student->email,
                'achievements_count' => $student->achievements()->count()
            ]);

            return redirect()->route('student.dashboard')
                ->with('success', 'Selamat datang kembali, ' . $ssoName . '!');
        }

        /**
         * Handle staff/admin/validator login from SSO
         */
        private function handleStaffLogin($userInfo, $accessToken, Request $request)
        {
            $email = $userInfo['email'] ?? '';
            $name = $userInfo['name'] ?? $userInfo['full_name'] ?? 'User';
            $roles = $userInfo['roles'] ?? [];

            // Check if user already exists
            $user = \App\Models\User::where('email', $email)->first();

            if ($user) {
                // User exists, update last login info
                // User exists, update last login info and sync SSO data
                $user->update([
                    'name' => $name,
                    'provider' => 'unpatti_sso', // Ensure provider is consistent
                    'provider_id' => $userInfo['id'] ?? $userInfo['user_id'] ?? null,
                    'email_verified_at' => now(), // Assume verified if login via SSO
                    'last_login_at' => now(),
                    'last_login_method' => 'sso',
                ]);

                $role = $user->role; // Use existing role from database
                $wasCreated = false;
            } else {
                // User doesn't exist, determine role from SSO roles
                $role = $this->determineRoleFromSSO($roles);

                // Create new user
                $user = \App\Models\User::create([
                    'email' => $email,
                    'name' => $name,
                    'role' => $role,
                    'faculty' => $userInfo['faculty'] ?? null,
                    'provider' => 'unpatti_sso',
                    'provider_id' => $userInfo['id'] ?? $userInfo['user_id'] ?? null,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                    'last_login_method' => 'sso',
                ]);

                $wasCreated = true;
            }

            // Login user with Laravel Auth
            \Illuminate\Support\Facades\Auth::login($user);

            // Store additional session data
            $request->session()->put([
                'sso_user' => $userInfo,
                'sso_token' => $accessToken,
                'sso_authenticated' => true,
            ]);

            Log::info('Staff logged in via SSO', [
                'email' => $email,
                'name' => $name,
                'role' => $role,
                'roles' => $roles,
                'created' => $wasCreated
            ]);

            // Redirect based on role (Menggunakan strtolower agar tidak sensitif huruf besar/kecil)
            $safeRole = strtolower($role);

            if ($safeRole === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Selamat datang, ' . $name . '!');
            } elseif ($safeRole === 'validator') {
                return redirect()->route('validator.dashboard')
                    ->with('success', 'Selamat datang, ' . $name . '!');
            } else {
                // Penanganan darurat jika entah bagaimana mahasiswa lolos ke fungsi ini
                if ($safeRole === 'student') {
                    return redirect()->route('student.dashboard')
                        ->with('success', 'Selamat datang, ' . $name . '!');
                }
                
                return redirect()->route('login')
                    ->with('error', 'Role tidak dikenali (' . $role . '). Hubungi administrator.');
            }
        }

        /**
         * Determine role from SSO roles array
         */
        private function determineRoleFromSSO($roles)
        {
            // Check for admin roles
            if (in_array('admin', $roles) || in_array('wakil_dekan', $roles) || in_array('dekan', $roles)) {
                return 'Admin';
            }

            // Check for validator roles (dosen, staff)
            if (
                in_array('dosen', $roles) || in_array('lecturer', $roles) ||
                in_array('staff', $roles) || in_array('staff_kemahasiswaan', $roles)
            ) {
                return 'Validator';
            }

            // Default to Validator for staff
            return 'Validator';
        }

        public function logout(Request $request)
        {
            $accessToken = $request->session()->get('sso_token');

            if ($accessToken) {
                try {
                    Http::withHeaders([
                        "Accept" => "application/json",
                        'Authorization' => 'Bearer ' . $accessToken
                    ])->get($this->ssoBaseUrl . '/api/logmeout');
                } catch (\Exception $e) {
                    Log::error('SSO Logout Error', ['error' => $e->getMessage()]);
                }
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        }
    }
