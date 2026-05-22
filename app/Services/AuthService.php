<?php

namespace App\Services;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Find user from SSO data (does NOT auto-create)
     * Non-student users must be pre-registered by admin
     *
     * @throws \Exception if user not found
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

        // 3. User not found — do NOT auto-create
        // Non-student accounts must be created manually by admin
        throw new \Exception('Akun dengan email '.$email.' belum terdaftar di sistem. Hubungi Administrator.');
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
    protected function linkExistingUser(
        User $user,
        string $provider,
        string $providerId,
        array $ssoData,
        array $tokens
    ): User {
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
     * Map role from SIAKAD to application role
     */
    protected function mapRole(array $ssoData): string
    {
        $siakadRole = $ssoData['role'] ?? $ssoData['user_type'] ?? $ssoData['type'] ?? 'student';
        $siakadRole = strtolower($siakadRole);

        $mapping = config('sso.role_mapping', []);

        return $mapping[$siakadRole] ?? 'Operator';
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
            Log::warning('Failed to log auth activity: '.$e->getMessage());
        }
    }

    /**
     * Get decrypted access token
     */
    public function getAccessToken(User $user): ?string
    {
        if (! $user->provider_token) {
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
        if (! $user->provider_token_expires_at) {
            return true;
        }

        return $user->provider_token_expires_at->isPast();
    }
}
