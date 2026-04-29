<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    private const DEFAULT_REDIRECT_PATH = '/dashboard';

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm(Request $request)
    {
        $returnTo = $request->query('return_to');

        if ($this->isSafeRedirect($returnTo)) {
            session(['origin_url' => $returnTo]);
        }

        return view('auth.login');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        if (session('auth_role') === 'student') {
            $request->session()->forget(['auth_role', 'student_id']);
        } else {
            $user = Auth::user();
            if ($user) {
                $this->authService->logAuthActivity($user, 'logout', [
                    'method' => $user->last_login_method ?? 'local',
                ]);
            }
            Auth::logout();
        }

        $requestedRedirect = $request->input('logout_redirect') ?? $request->session()->get('origin_url');
        $redirectUrl = $this->isSafeRedirect($requestedRedirect)
            ? $requestedRedirect
            : self::DEFAULT_REDIRECT_PATH;

        Log::info('User logout redirect resolved', [
            'user_id' => $userId,
            'redirect' => $redirectUrl,
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($redirectUrl);
    }

    private function isSafeRedirect(?string $url): bool
    {
        return is_string($url) && str_starts_with($url, '/');
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToRoleDashboard($role)
    {
        // Store active role in session
        session([
            'active_role_id' => $role->id,
            'active_role_type' => $role->role,
        ]);

        return match ($role->role) {
            'super_admin', 'admin' => redirect()->intended(route('admin.dashboard')),
            'operator' => redirect()->intended(route('validator.pending.index')),
            'pimpinan' => redirect()->intended(route('pimpinan.dashboard')),
            'mahasiswa' => redirect()->intended(route('student.dashboard')),
            default => redirect()->intended('/'),
        };
    }
}
