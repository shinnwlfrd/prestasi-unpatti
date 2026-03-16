<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAny
{
    /**
     * Handle an incoming request.
     * Allow both student session and regular user auth
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as regular user (admin/validator/pimpinan)
        if (auth()->check()) {
            // User is authenticated via Laravel auth
            // They might be in student mode (session auth_role = student)
            // or in user mode (session active_role_id set)
            return $next($request);
        }

        // Check if authenticated as student only (no user auth)
        if (session('auth_role') === 'student' && session('student_id')) {
            return $next($request);
        }

        // Not authenticated at all
        return redirect()->route('login')->withErrors(['login' => 'Anda harus login terlebih dahulu.']);
    }
}
