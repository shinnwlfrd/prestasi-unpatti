<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm(Request $request)
    {
        // Capture origin URL if provided via query param or referer (if external)
        if ($request->has('return_to')) {
            session(['origin_url' => $request->query('return_to')]);
        } elseif ($request->header('referer') && !str_contains($request->header('referer'), $request->getHost())) {
            session(['origin_url' => $request->header('referer')]);
        }

        return view('auth.login');
    }

    public function logout(Request $request)
    {
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

        $redirectUrl = $request->input('logout_redirect') ?? $request->session()->get('origin_url') ?? env('PORTAL_URL', 'http://127.0.0.1:8000/portal');
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($redirectUrl);
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
