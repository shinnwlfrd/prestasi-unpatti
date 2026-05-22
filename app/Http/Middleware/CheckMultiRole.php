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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
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
        $activeRoleType = session('active_role_type');

        if ($activeRoleType) {
            if (in_array($activeRoleType, $roles, true)) {
                return $next($request);
            }

            \Log::warning('CheckMultiRole: Active role does not match route role', [
                'url' => $request->fullUrl(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_roles' => $roles,
                'session_active_role_type' => $activeRoleType,
            ]);

            abort(403, 'Role aktif Anda tidak sesuai untuk mengakses halaman ini.');
        }

        // Super admin without an explicit active context keeps emergency access.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $activeRolesCount = $user->activeRoles()->count();
        $hasRequiredRole = $user->activeRoles()->whereIn('role', $roles)->exists();

        if ($hasRequiredRole && $activeRolesCount <= 1) {
            return $next($request);
        }

        if ($hasRequiredRole && $activeRolesCount > 1) {
            return redirect()->route('role.switch.page')
                ->with('warning', 'Pilih role aktif terlebih dahulu untuk mengakses halaman tersebut.');
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

        abort(403, 'Anda tidak memiliki role yang diperlukan untuk mengakses halaman ini. Role yang diperlukan: '.implode(', ', $roles));
    }
}
