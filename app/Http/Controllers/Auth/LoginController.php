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
        // If SSO is forced, redirect directly to SSO redirect route
        if (config('sso.gates.sso.force', false)) {
            return redirect()->route('sso.redirect');
        }

        return view('auth.login', [
            'localEnabled' => config('sso.gates.local.enabled', false),
            'ssoEnabled' => config('sso.gates.sso.enabled', true),
            'ssoForce' => config('sso.gates.sso.force', true),
        ]);
    }

    public function logout(Request $request)
    {
        if (session('auth_role') === 'student') {
            $request->session()->forget(['auth_role', 'student_id', 'student_data']);
        } else {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
