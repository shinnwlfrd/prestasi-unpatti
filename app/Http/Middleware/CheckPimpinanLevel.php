<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPimpinanLevel
{
    /**
     * Handle an incoming request.
     * Check if user is pimpinan (read-only) and store their level/scope in session
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Check if using virtual role from session (for super admin)
        $activeRoleId = session('active_role_id');
        $pimpinanLevel = session('pimpinan_level');

        if ($activeRoleId === 'virtual_pimpinan_university' && $pimpinanLevel) {
            // Already set by RoleSwitchController, just continue
            return $next($request);
        }

        // Super admin can access pimpinan pages
        if ($user->isSuperAdmin()) {
            // Set session to match pimpinan context for layout consistency
            if (session('active_role_type') !== 'pimpinan') {
                session([
                    'active_role_id' => 'virtual_pimpinan_university',
                    'active_role_type' => 'pimpinan'
                ]);
            }
            
            session(['pimpinan_level' => 'university', 'pimpinan_scope' => '*', 'pimpinan_position' => 'super_admin']);
            return $next($request);
        }

        // Check if user has pimpinan role
        if (!$user->isPimpinan()) {
            abort(403, 'Halaman ini hanya untuk Pimpinan.');
        }

        // Get current active role if matches type, otherwise fallback to first pimpinan role
        $currentRole = $user->getCurrentRole();
        $pimpinanRole = ($currentRole && $currentRole->role === 'pimpinan')
            ? $currentRole
            : $user->getRolesByType('pimpinan')->first();

        if (!$pimpinanRole) {
            abort(403, 'Role pimpinan tidak ditemukan.');
        }

        // Store pimpinan level and scope in session for easy access
        session([
            'pimpinan_level' => $pimpinanRole->level,
            'pimpinan_position' => $pimpinanRole->position,
            'pimpinan_faculty_id' => $pimpinanRole->faculty_id ?: null,
            'pimpinan_faculty_name' => $pimpinanRole->faculty_name,
            'pimpinan_department_id' => $pimpinanRole->department_id ?: null,
            'pimpinan_department_name' => $pimpinanRole->department_name,
            'pimpinan_program_study_id' => $pimpinanRole->program_study_id ?: null,
            'pimpinan_program_study_name' => $pimpinanRole->program_study_name,
            'is_read_only' => true, // Pimpinan is always read-only
        ]);

        return $next($request);
    }
}
