<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsValidator
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->withErrors(['login' => 'Anda harus login terlebih dahulu.']);
        }

        $user = auth()->user();
        $activeRoleType = session('active_role_type');

        if ($activeRoleType && $activeRoleType !== 'operator') {
            abort(403, 'Role aktif Anda bukan Operator.');
        }

        if (! $activeRoleType && ! $user->isSuperAdmin() && ! $user->hasRole('operator')) {
            abort(403, 'Akses ditolak. Anda bukan Operator.');
        }

        return $next($request);
    }
}
