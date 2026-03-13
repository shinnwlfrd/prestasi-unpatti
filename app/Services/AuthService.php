<?php

namespace App\Services;

use App\Models\AuthLog;
use App\Models\SikadCredential;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
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
