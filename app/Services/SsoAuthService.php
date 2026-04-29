<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\UserRole;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SsoAuthService
{
    private string $ssoBaseUrl = 'https://sso.unpatti.ac.id';
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $redirectUri;

    public function __construct()
    {
        $this->clientId = config('sso.client_id');
        $this->clientSecret = config('sso.client_secret');
        $this->redirectUri = config('sso.redirect_uri');
    }

    /**
     * Get the SSO authorization URL
     */
    public function getAuthorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return $this->ssoBaseUrl . '/oauth/authorize?' . $query;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code): ?string
    {
        try {
            $response = Http::asForm()->post($this->ssoBaseUrl . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                Log::error('SSO Token Exchange Failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            $tokenData = $response->json();
            return $tokenData['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('SSO Token Exchange Error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get user info from SSO using access token
     */
    public function getUserInfo(string $accessToken): ?array
    {
        try {
            $response = Http::withHeaders([
                "Accept" => "application/json",
                'Authorization' => 'Bearer ' . $accessToken
            ])->get($this->ssoBaseUrl . '/api/me/roles', [
                'client_id' => $this->clientId
            ]);

            if (!$response->successful()) {
                Log::error('SSO User Info Failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('SSO User Info Error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Handle student login flow
     */
    public function handleStudentLogin(array $userInfo, string $accessToken): array
    {
        $email = strtolower($userInfo['email'] ?? '');
        $name = $userInfo['name'] ?? $userInfo['full_name'] ?? 'student';
        $nim = explode('@', $email)[0];

        // Check for soft-deleted student
        $existingStudent = Student::withTrashed()->where('student_id', $nim)->first();
        if ($existingStudent && $existingStudent->trashed()) {
            return [
                'success' => false,
                'error' => 'Akun mahasiswa Anda (' . $nim . ') telah dinonaktifkan. Silakan hubungi Administrator.'
            ];
        }

        // Handle User record
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->update([
                'name' => $name,
                'role' => 'Student',
                'provider' => 'unpatti_sso',
                'provider_id' => $userInfo['id'] ?? $userInfo['user_id'] ?? null,
                'is_active' => true,
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'last_login_method' => 'sso',
            ]);

            $this->syncUserRole($user, 'mahasiswa');
            Auth::login($user);
        }

        // Fetch SIAKAD Profile
        $siakadData = $this->fetchSiakadProfile($email, $nim);

        // Prepare Session Data
        $studentProfile = array_merge([
            'nim' => $nim,
            'nama' => $name,
            'email' => $email,
        ], $siakadData['profile']);

        session([
            'student_data' => $studentProfile,
            'student_profile' => $studentProfile,
            'auth_role' => 'student',
            'student_id' => $nim,
            'student_name' => $name,
            'student_email' => $email,
            'sso_access_token' => $accessToken,
            'sso_authenticated' => true,
        ]);

        $hasAchievements = Student::find($nim) !== null;

        return [
            'success' => true,
            'redirect' => 'student.dashboard',
            'has_achievements' => $hasAchievements,
            'siakad_success' => $siakadData['success']
        ];
    }

    /**
     * Handle staff/admin/operator login flow
     */
    public function handleStaffLogin(array $userInfo, string $accessToken): array
    {
        $email = strtolower($userInfo['email'] ?? '');
        $name = $userInfo['name'] ?? $userInfo['full_name'] ?? 'User';
        $ssoRoles = $userInfo['roles'] ?? [];

        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::warning('Unauthorized staff login attempt via SSO', [
                'email' => $email,
                'sso_roles' => $ssoRoles
            ]);
            return [
                'success' => false,
                'error' => 'Akun Anda (' . $email . ') belum terdaftar di sistem SIMAPRES. Silakan hubungi Administrator.'
            ];
        }

        // Check for soft-deleted user
        if ($user->trashed()) {
            return [
                'success' => false,
                'error' => 'Akun Anda (' . $email . ') telah dinonaktifkan. Silakan hubungi Administrator.'
            ];
        }

        $user->update([
            'name' => $name,
            'provider' => 'unpatti_sso',
            'provider_id' => $userInfo['id'] ?? $userInfo['user_id'] ?? null,
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_method' => 'sso',
        ]);

        Auth::login($user);

        session([
            'sso_user' => $userInfo,
            'sso_token' => $accessToken,
            'sso_authenticated' => true,
        ]);

        $activeRoles = $user->activeRoles()->get();

        if ($activeRoles->isEmpty()) {
            Auth::logout();
            return [
                'success' => false,
                'error' => 'Akun Anda (' . $email . ') tidak memiliki role aktif. Hubungi Administrator.'
            ];
        }

        if ($activeRoles->count() > 1) {
            return [
                'success' => true,
                'redirect' => 'role.switch.page'
            ];
        }

        // Single role - auto initialize and redirect
        $activeRole = $activeRoles->first();
        $this->initializeRoleSession($activeRole);

        $redirectRoute = match ($activeRole->role) {
            'super_admin', 'admin' => 'admin.dashboard',
            'operator' => 'validator.pending.index',
            'pimpinan' => 'pimpinan.dashboard',
            'mahasiswa' => 'student.dashboard',
            default => 'login',
        };

        return [
            'success' => true,
            'redirect' => $redirectRoute,
            'role' => $activeRole->role
        ];
    }

    /**
     * Sync user role to user_roles table
     */
    private function syncUserRole(User $user, string $roleName): void
    {
        $existingRole = UserRole::where('user_id', $user->id)
            ->where('role', $roleName)
            ->first();

        if ($existingRole) {
            $existingRole->update([
                'is_active' => true,
                'activated_at' => $user->linked_at ?? now(),
            ]);
        } else {
            // Only create if no soft-deleted version exists
            $trashedRole = UserRole::onlyTrashed()
                ->where('user_id', $user->id)
                ->where('role', $roleName)
                ->first();

            if (!$trashedRole) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role' => $roleName,
                    'is_active' => true,
                    'activated_at' => $user->linked_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Initialize specific role data in session
     */
    private function initializeRoleSession(UserRole $activeRole): void
    {
        session([
            'active_role_id' => $activeRole->id,
            'active_role_type' => $activeRole->role,
        ]);

        if (in_array($activeRole->role, ['operator', 'pimpinan'])) {
            $prefix = $activeRole->role;
            session([
                "{$prefix}_level" => $activeRole->level,
                "{$prefix}_faculty_id" => $activeRole->faculty_id,
                "{$prefix}_faculty_name" => $activeRole->faculty_name,
                "{$prefix}_department_id" => $activeRole->department_id,
                "{$prefix}_department_name" => $activeRole->department_name,
                "{$prefix}_program_study_id" => $activeRole->program_study_id,
                "{$prefix}_program_study_name" => $activeRole->program_study_name,
            ]);

            if ($activeRole->role === 'pimpinan') {
                session(['pimpinan_position' => $activeRole->position]);
            }
        }
    }

    /**
     * Fetch profile from SIAKAD API
     */
    private function fetchSiakadProfile(string $email, string $nim): array
    {
        $profile = [
            'foto_url' => null,
            'ipk' => null,
            'fakultas' => null,
            'fakultas_id' => null,
            'jurusan' => null,
            'jurusan_id' => null,
            'program_studi' => null,
            'program_studi_id' => null,
            'angkatan' => substr($nim, 0, 4),
        ];

        $success = false;

        try {
            $siakadService = app(\App\Services\SiakadApiService::class);
            $siakadData = $siakadService->getMahasiswaByEmail($email) ?: $siakadService->getMahasiswaByNim($nim);

            if ($siakadData) {
                $profile = [
                    'foto_url' => $siakadData['foto_url'] ?? null,
                    'ipk' => data_get($siakadData, 'registrasi.ipk_kumulatif'),
                    'fakultas' => data_get($siakadData, 'fakultas.nama'),
                    'fakultas_id' => data_get($siakadData, 'fakultas.id'),
                    'jurusan' => data_get($siakadData, 'jurusan.nama'),
                    'jurusan_id' => data_get($siakadData, 'jurusan.id'),
                    'program_studi' => data_get($siakadData, 'program_studi.nama'),
                    'program_studi_id' => data_get($siakadData, 'program_studi.id'),
                    'angkatan' => data_get($siakadData, 'registrasi.angkatan') ?? substr($nim, 0, 4),
                ];
                $success = true;
            }
        } catch (\Exception $e) {
            Log::error('SIAKAD API Error during SSO login', ['error' => $e->getMessage()]);
        }

        return ['success' => $success, 'profile' => $profile];
    }
}
