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
            \Log::warning('CheckMultiRole: User not authenticated', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'has_session' => $request->hasSession(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            ]);
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Super admin has access to everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check for virtual role in session (for super admin switching roles)
        $activeRoleType = session('active_role_type');
        if ($activeRoleType && in_array($activeRoleType, $roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        // User doesn't have required role - log details for debugging
        \Log::warning('CheckMultiRole: User lacks required role', [
            'url' => $request->fullUrl(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_legacy_role' => $user->role,
            'required_roles' => $roles,
            'active_roles' => $user->getActiveRoleNames(),
            'session_active_role_type' => $activeRoleType,
        ]);

        return redirect()->route('login')->withErrors([
            'login' => 'Anda tidak memiliki role yang diperlukan untuk mengakses halaman ini. Role yang diperlukan: ' . implode(', ', $roles),
        ]);
    }
}
