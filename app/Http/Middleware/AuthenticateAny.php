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
        // Check if user is authenticated as regular user (admin/validator)
        // This check must come first to prevent session switching
        if (auth()->check()) {
            // User is authenticated via Laravel auth (admin/validator)
            // Don't check student session to prevent conflicts
            return $next($request);
        }

        // Only check student session if not authenticated as regular user
        if (session('auth_role') === 'student' && session('student_id')) {
            return $next($request);
        }

        // Not authenticated at all
        return redirect()->route('login')->withErrors(['login' => 'Anda harus login terlebih dahulu.']);
    }
}
