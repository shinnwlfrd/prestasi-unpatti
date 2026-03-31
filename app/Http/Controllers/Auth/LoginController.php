<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return view('auth.login', [
            'localEnabled' => config('sso.gates.local.enabled', true),
            'ssoEnabled' => config('sso.gates.sso.enabled', true),
            'ssoForce' => config('sso.gates.sso.force', false),
        ]);
    }

    public function login(Request $request)
    {
        // Check rate limiting
        $key = 'login_attempts:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors(['email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."])
                ->withInput();
        }

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check if local login is enabled
        if (! config('sso.gates.local.enabled', true)) {
            return back()
                ->withErrors(['email' => 'Login lokal tidak tersedia. Silakan gunakan SSO.'])
                ->withInput();
        }

        // Try to authenticate as Student first (using NIM)
        $student = $this->authService->authenticateStudent($request->email, $request->password);

        if ($student) {
            RateLimiter::clear($key);
            session(['auth_role' => 'student', 'student_id' => $student->student_id]);

            return redirect()->intended(route('student.dashboard'));
        }

        // Try to authenticate as User (Admin/Validator using email)
        $user = $this->authService->authenticateLocal($request->email, $request->password);

        if ($user) {
            RateLimiter::clear($key);
            Auth::login($user);

            // Check if user has multiple active roles
            $activeRoles = $user->activeRoles()->get();
            
            if ($activeRoles->count() > 1) {
                // Multi-role user - redirect to role switcher
                return redirect()->route('role.switch.page');
            }

            // Single role user - redirect based on role
            if ($activeRoles->count() === 1) {
                $role = $activeRoles->first();
                return $this->redirectToRoleDashboard($role);
            }

            // Legacy role system (backward compatibility)
            if ($user->role === 'Admin') {
                return redirect()->intended('/admin');
            } elseif ($user->role === 'Operator') {
                return redirect()->intended(route('validator.dashboard'));
            }

            // Unknown role
            Auth::logout();
            return back()->withErrors(['email' => 'Role tidak dikenali atau tidak aktif.'])->withInput();
        }

        RateLimiter::hit($key, 900);

        // Check if account was soft-deleted
        $trashedUser = \App\Models\User::withTrashed()->where('email', $request->email)->first();
        if ($trashedUser && $trashedUser->trashed()) {
            return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.'])->withInput();
        }
        
        $trashedStudent = \App\Models\Student::withTrashed()->where('student_id', $request->email)->first();
        if ($trashedStudent && $trashedStudent->trashed()) {
            return back()->withErrors(['email' => 'Akun mahasiswa Anda telah dinonaktifkan. Silakan hubungi Administrator.'])->withInput();
        }

        // Check if account is SSO only
        $conflict = $this->authService->checkAccountConflict($request->email);
        if ($conflict && $conflict['type'] === 'sso_only') {
            return back()->withErrors(['email' => $conflict['message']])->withInput();
        }

        return back()->withErrors(['email' => 'Email/NIM atau password salah.'])->withInput();
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

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
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
