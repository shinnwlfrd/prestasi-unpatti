<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSOService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected array $scopes;

    public function __construct()
    {
        $this->baseUrl = config('sso.siakad.base_url');
        $this->clientId = config('sso.siakad.client_id');
        $this->clientSecret = config('sso.siakad.client_secret');
        $this->redirectUri = config('sso.siakad.redirect_uri');
        $this->scopes = explode(',', config('sso.siakad.scopes', 'openid,profile,email'));
    }

    /**
     * Generate authorization URL
     */
    public function getAuthorizationUrl(): array
    {
        $state = Str::random(40);
        
        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes),
            'state' => $state,
        ]);

        $url = $this->baseUrl . config('sso.siakad.authorize_endpoint') . '?' . $params;

        return [
            'url' => $url,
            'state' => $state,
        ];
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForTokens(string $code): ?array
    {
        try {
            $response = Http::asForm()->post(
                $this->baseUrl . config('sso.siakad.token_endpoint'),
                [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri,
                    'code' => $code,
                ]
            );

            if ($response->failed()) {
                Log::error('SSO token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('SSO token exchange exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get user info from SSO
     */
    public function getUserInfo(string $accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get($this->baseUrl . config('sso.siakad.userinfo_endpoint'));

            if ($response->failed()) {
                Log::error('SSO userinfo failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('SSO userinfo exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        try {
            $response = Http::asForm()->post(
                $this->baseUrl . config('sso.siakad.token_endpoint'),
                [
                    'grant_type' => 'refresh_token',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $refreshToken,
                ]
            );

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('SSO refresh token exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get logout URL
     */
    public function getLogoutUrl(?string $redirectUrl = null): string
    {
        $params = $redirectUrl ? '?redirect_uri=' . urlencode($redirectUrl) : '';
        return $this->baseUrl . config('sso.siakad.logout_endpoint') . $params;
    }

    /**
     * Validate state parameter (CSRF protection)
     */
    public function validateState(?string $state, ?string $sessionState): bool
    {
        return $state && $sessionState && hash_equals($sessionState, $state);
    }
}
