<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\SikadCredential;
use App\Models\AuthLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Authenticate local user (Admin/Validator)
     */
    public function authenticateLocal(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->logAuthActivity(null, 'failed_login', [
                'method' => 'local',
                'reason' => 'user_not_found',
                'email' => $email,
            ]);
            return null;
        }

        // Check if user only has SSO (no password)
        if (!$user->password) {
            $this->logAuthActivity($user, 'failed_login', [
                'method' => 'local',
                'reason' => 'sso_only_account',
            ]);
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            $this->logAuthActivity($user, 'failed_login', [
                'method' => 'local',
                'reason' => 'invalid_password',
            ]);
            return null;
        }

        if (!$user->is_active) {
            $this->logAuthActivity($user, 'failed_login', [
                'method' => 'local',
                'reason' => 'account_inactive',
            ]);
            return null;
        }

        // Update login info
        $user->update([
            'last_login_at' => now(),
            'last_login_method' => 'local',
        ]);

        $this->logAuthActivity($user, 'login', ['method' => 'local']);

        return $user;
    }

    /**
     * Authenticate student via SIKAD credentials
     */
    public function authenticateStudent(string $studentId, string $password): ?Student
    {
        $credential = SikadCredential::where('student_id', $studentId)->first();

        if (!$credential) {
            return null;
        }

        if (!Hash::check($password, $credential->password_hash)) {
            return null;
        }

        return $credential->student;
    }

    /**
     * Find or create user from SSO data
     */
    public function findOrCreateFromSSO(array $ssoData, array $tokens, string $provider = 'siakad'): User
    {
        $providerId = $ssoData['sub'] ?? $ssoData['id'] ?? $ssoData['nim'] ?? $ssoData['nip'];
        $email = $ssoData['email'];

        // 1. Find by provider_id (most accurate)
        $user = User::where('provider', $provider)
                    ->where('provider_id', $providerId)
                    ->first();

        if ($user) {
            return $this->updateSSOUser($user, $ssoData, $tokens);
        }

        // 2. Find by email (for linking existing local account)
        $user = User::where('email', $email)->first();

        if ($user) {
            return $this->linkExistingUser($user, $provider, $providerId, $ssoData, $tokens);
        }

        // 3. Create new user
        return $this->createSSOUser($provider, $providerId, $ssoData, $tokens);
    }

    /**
     * Update existing SSO user
     */
    protected function updateSSOUser(User $user, array $ssoData, array $tokens): User
    {
        $user->update([
            'name' => $ssoData['name'] ?? $user->name,
            'provider_token' => isset($tokens['access_token']) ? encrypt($tokens['access_token']) : null,
            'provider_refresh_token' => isset($tokens['refresh_token']) ? encrypt($tokens['refresh_token']) : null,
            'provider_token_expires_at' => isset($tokens['expires_in']) 
                ? now()->addSeconds($tokens['expires_in']) 
                : null,
            'provider_data' => $ssoData,
            'last_login_at' => now(),
            'last_login_method' => 'sso',
        ]);

        $this->logAuthActivity($user, 'login', ['method' => 'sso', 'provider' => $user->provider]);

        return $user;
    }

    /**
     * Link existing local user to SSO
     */
    protected function linkExistingUser(User $user, string $provider, string $providerId, 
                                        array $ssoData, array $tokens): User
    {
        $user->update([
            'provider' => $provider,
            'provider_id' => $providerId,
            'provider_token' => isset($tokens['access_token']) ? encrypt($tokens['access_token']) : null,
            'provider_refresh_token' => isset($tokens['refresh_token']) ? encrypt($tokens['refresh_token']) : null,
            'provider_token_expires_at' => isset($tokens['expires_in']) 
                ? now()->addSeconds($tokens['expires_in']) 
                : null,
            'provider_data' => $ssoData,
            'linked_at' => now(),
            'last_login_at' => now(),
            'last_login_method' => 'sso',
        ]);

        $this->logAuthActivity($user, 'sso_link', [
            'method' => 'sso',
            'provider' => $provider,
            'provider_id' => $providerId,
        ]);

        return $user;
    }

    /**
     * Create new user from SSO
     */
    protected function createSSOUser(string $provider, string $providerId, 
                                      array $ssoData, array $tokens): User
    {
        $user = User::create([
            'name' => $ssoData['name'] ?? 'User',
            'email' => $ssoData['email'],
            'password' => null, // SSO user has no local password
            'provider' => $provider,
            'provider_id' => $providerId,
            'provider_token' => isset($tokens['access_token']) ? encrypt($tokens['access_token']) : null,
            'provider_refresh_token' => isset($tokens['refresh_token']) ? encrypt($tokens['refresh_token']) : null,
            'provider_token_expires_at' => isset($tokens['expires_in']) 
                ? now()->addSeconds($tokens['expires_in']) 
                : null,
            'provider_data' => $ssoData,
            'role' => $this->mapRole($ssoData),
            'primary_auth' => 'sso',
            'email_verified_at' => now(), // SSO = verified
            'is_active' => true,
            'last_login_at' => now(),
            'last_login_method' => 'sso',
        ]);

        $this->logAuthActivity($user, 'register', [
            'method' => 'sso',
            'provider' => $provider,
        ]);

        return $user;
    }

    /**
     * Map role from SIAKAD to application role
     */
    protected function mapRole(array $ssoData): string
    {
        $siakadRole = $ssoData['role'] ?? $ssoData['user_type'] ?? $ssoData['type'] ?? 'student';
        $siakadRole = strtolower($siakadRole);

        $mapping = config('sso.role_mapping', []);

        return $mapping[$siakadRole] ?? 'Validator';
    }

    /**
     * Check if email exists with different auth method
     */
    public function checkAccountConflict(string $email): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        if ($user->provider && !$user->password) {
            return [
                'type' => 'sso_only',
                'message' => 'Akun ini terdaftar via SSO. Silakan login dengan SSO.',
                'provider' => $user->provider,
            ];
        }

        if ($user->provider && $user->password) {
            return [
                'type' => 'linked',
                'message' => 'Akun sudah terhubung dengan SSO. Anda bisa login dengan keduanya.',
                'provider' => $user->provider,
            ];
        }

        return [
            'type' => 'local_only',
            'message' => 'Akun lokal ditemukan. Login via SSO akan menghubungkan akun.',
        ];
    }

    /**
     * Log authentication activity
     */
    public function logAuthActivity(?User $user, string $action, array $metadata = []): void
    {
        try {
            AuthLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'method' => $metadata['method'] ?? 'unknown',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log auth activity: ' . $e->getMessage());
        }
    }

    /**
     * Get decrypted access token
     */
    public function getAccessToken(User $user): ?string
    {
        if (!$user->provider_token) {
            return null;
        }

        try {
            return decrypt($user->provider_token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(User $user): bool
    {
        if (!$user->provider_token_expires_at) {
            return true;
        }

        return $user->provider_token_expires_at->isPast();
    }
}
