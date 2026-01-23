<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\SSOService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SSOController extends Controller
{
    protected SSOService $ssoService;

    protected AuthService $authService;

    public function __construct(SSOService $ssoService, AuthService $authService)
    {
        $this->ssoService = $ssoService;
        $this->authService = $authService;
    }

    /**
     * Redirect to SSO provider
     */
    public function redirect(Request $request)
    {
        // Check if SSO is enabled
        if (! config('sso.gates.sso.enabled')) {
            return redirect()->route('login')
                ->with('error', 'Login SSO tidak tersedia saat ini.');
        }

        // Generate authorization URL with state
        $auth = $this->ssoService->getAuthorizationUrl();

        // Store state in session for CSRF protection
        session(['sso_state' => $auth['state']]);

        // Store intended URL if any
        if ($request->has('redirect')) {
            session(['url.intended' => $request->redirect]);
        }

        return redirect($auth['url']);
    }

    /**
     * Handle callback from SSO provider
     */
    public function callback(Request $request)
    {
        // Check for errors from SSO
        if ($request->has('error')) {
            return redirect()->route('login')
                ->with('error', 'Login SSO gagal: '.($request->error_description ?? $request->error));
        }

        // Validate state (CSRF protection)
        if (! $this->ssoService->validateState($request->state, session('sso_state'))) {
            return redirect()->route('login')
                ->with('error', 'Invalid state - kemungkinan serangan CSRF.');
        }

        // Clear state from session
        session()->forget('sso_state');

        // Check if code is present
        if (! $request->has('code')) {
            return redirect()->route('login')
                ->with('error', 'Authorization code tidak ditemukan.');
        }

        // Exchange code for tokens
        $tokens = $this->ssoService->exchangeCodeForTokens($request->code);

        if (! $tokens || ! isset($tokens['access_token'])) {
            return redirect()->route('login')
                ->with('error', 'Gagal mendapatkan token dari SSO.');
        }

        // Get user info from SSO
        $userInfo = $this->ssoService->getUserInfo($tokens['access_token']);

        if (! $userInfo || ! isset($userInfo['email'])) {
            return redirect()->route('login')
                ->with('error', 'Gagal mendapatkan informasi user dari SSO.');
        }

        // Find or create user
        $user = $this->authService->findOrCreateFromSSO($userInfo, $tokens);

        // Check if user is active
        if (! $user->is_active) {
            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Login user
        Auth::login($user, true);

        // Redirect based on role
        return $this->redirectByRole($user);
    }

    /**
     * Logout and optionally logout from SSO
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $this->authService->logAuthActivity($user, 'logout', [
                'method' => $user->last_login_method ?? 'unknown',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // If user logged in via SSO, redirect to SSO logout
        if ($user && $user->provider && $user->last_login_method === 'sso') {
            $logoutUrl = $this->ssoService->getLogoutUrl(url('/'));

            return redirect($logoutUrl);
        }

        return redirect('/');
    }

    /**
     * Redirect user based on role
     */
    protected function redirectByRole($user)
    {
        $intended = session()->pull('url.intended');

        if ($intended) {
            return redirect($intended);
        }

        return match ($user->role) {
            'Admin' => redirect('/admin'),
            'Validator' => redirect()->route('validator.dashboard'),
            default => redirect()->route('login')
                ->with('error', 'Role tidak dikenali. Hubungi administrator.'),
        };
    }
}
