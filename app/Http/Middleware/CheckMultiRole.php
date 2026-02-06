<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMultiRole
{
    /**
     * Handle an incoming request.
     * Check if user has any of the specified roles
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Super admin has access to everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        // User doesn't have required role
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
