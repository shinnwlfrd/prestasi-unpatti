<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SikadCredential;
use App\Models\User;
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
        $key = 'login_attempts:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withErrors(['username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."])
                ->withInput();
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'login_as' => 'required|in:student,validator,admin',
        ]);

        // Check if local login is enabled
        if (!config('sso.gates.local.enabled') && $request->login_as !== 'student') {
            return back()
                ->withErrors(['username' => 'Login lokal tidak tersedia. Silakan gunakan SSO.'])
                ->withInput();
        }

        // Login sebagai Mahasiswa
        if ($request->login_as === 'student') {
            $student = $this->authService->authenticateStudent($request->username, $request->password);
            
            if ($student) {
                RateLimiter::clear($key);
                session(['auth_role' => 'student', 'student_id' => $student->student_id]);
                return redirect()->intended(route('student.dashboard'));
            }
            
            RateLimiter::hit($key, 900);
            return back()->withErrors(['username' => 'NIM atau password salah.'])->withInput();
        }

        // Login sebagai Admin
        if ($request->login_as === 'admin') {
            $user = $this->authService->authenticateLocal($request->username, $request->password);
            
            if ($user && $user->role === 'Admin') {
                RateLimiter::clear($key);
                Auth::login($user);
                return redirect('/admin');
            }
            
            RateLimiter::hit($key, 900);
            
            // Check if account is SSO only
            $conflict = $this->authService->checkAccountConflict($request->username);
            if ($conflict && $conflict['type'] === 'sso_only') {
                return back()->withErrors(['username' => $conflict['message']])->withInput();
            }
            
            return back()->withErrors(['username' => 'Email atau password salah, atau Anda bukan Admin.'])->withInput();
        }

        // Login sebagai Validator
        if ($request->login_as === 'validator') {
            $user = $this->authService->authenticateLocal($request->username, $request->password);
            
            if ($user && $user->role === 'Validator') {
                RateLimiter::clear($key);
                Auth::login($user);
                return redirect()->intended(route('validator.dashboard'));
            }
            
            RateLimiter::hit($key, 900);
            
            // Check if account is SSO only
            $conflict = $this->authService->checkAccountConflict($request->username);
            if ($conflict && $conflict['type'] === 'sso_only') {
                return back()->withErrors(['username' => $conflict['message']])->withInput();
            }
            
            return back()->withErrors(['username' => 'Email atau password salah, atau Anda bukan Validator.'])->withInput();
        }

        return back()->withErrors(['username' => 'Login gagal.'])->withInput();
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
}