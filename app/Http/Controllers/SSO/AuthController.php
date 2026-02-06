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
                // User exists in database, use their role
                return $this->handleStaffLogin($userInfo, $accessToken, $request);
            }

            // PRIORITY 2: Check if email contains @student.unpatti.ac.id
            if (str_contains($email, '@student.unpatti.ac.id')) {
                Log::info('SSO Detected Student from Email Pattern', ['email' => $email]);
                // Student detected from email pattern
                return $this->handleStudentLogin($userInfo, $accessToken, $request);
            } else {
                Log::info('SSO Detected Staff from Email', ['email' => $email]);
                // Staff/Admin/Validator detected
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
     */
    private function handleStudentLogin($userInfo, $accessToken, Request $request)
    {
        $email = $userInfo['email'] ?? '';
        $name = $userInfo['name'] ?? $userInfo['full_name'] ?? 'Student';

        // Extract NIM from email (e.g., 12345678@student.unpatti.ac.id -> 12345678)
        $nim = explode('@', $email)[0];

        // Find or create student in database
        $student = \App\Models\Student::firstOrCreate(
            ['student_id' => $nim],
            [
                'name' => $name,
                'email' => $email,
                'faculty' => $userInfo['faculty'] ?? null,
                'program_study' => $userInfo['program_study'] ?? $userInfo['prodi'] ?? null,
                'semester' => $userInfo['semester'] ?? null,
            ]
        );

        // Update student data if exists
        if (!$student->wasRecentlyCreated) {
            $student->update([
                'name' => $name,
                'email' => $email,
                'faculty' => $userInfo['faculty'] ?? $student->faculty,
                'program_study' => $userInfo['program_study'] ?? $userInfo['prodi'] ?? $student->program_study,
            ]);
        }

        // Store session for student
        $request->session()->put([
            'auth_role' => 'student',
            'student_id' => $nim,
            'student_name' => $name,
            'sso_user' => $userInfo,
            'sso_token' => $accessToken,
            'sso_authenticated' => true
        ]);

        Log::info('Student logged in via SSO', [
            'nim' => $nim,
            'name' => $name,
            'email' => $email,
            'created' => $student->wasRecentlyCreated
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', 'Selamat datang, ' . $name . '!');
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

        // Redirect based on role
        if ($role === 'Admin') {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Selamat datang, ' . $name . '!');
        } elseif ($role === 'Validator') {
            return redirect()->route('validator.dashboard')
                ->with('success', 'Selamat datang, ' . $name . '!');
        } else {
            return redirect()->route('login')
                ->with('error', 'Role tidak dikenali. Hubungi administrator.');
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
