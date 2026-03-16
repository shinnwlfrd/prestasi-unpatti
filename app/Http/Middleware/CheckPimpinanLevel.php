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

        // Super admin can access pimpinan pages
        if ($user->isSuperAdmin()) {
            session(['pimpinan_level' => 'university', 'pimpinan_scope' => '*', 'pimpinan_position' => 'super_admin']);
            return $next($request);
        }

        // Check if user has pimpinan role
        if (!$user->isPimpinan()) {
            abort(403, 'Halaman ini hanya untuk Pimpinan.');
        }

        // Get pimpinan role details
        $pimpinanRole = $user->getRolesByType('pimpinan')->first();
        
        if (!$pimpinanRole) {
            abort(403, 'Role pimpinan tidak ditemukan.');
        }

        // Store pimpinan level and scope in session for easy access
        session([
            'pimpinan_level' => $pimpinanRole->level,
            'pimpinan_position' => $pimpinanRole->position,
            'pimpinan_faculty_id' => $pimpinanRole->faculty_id,
            'pimpinan_faculty_name' => $pimpinanRole->faculty_name,
            'pimpinan_department_id' => $pimpinanRole->department_id,
            'pimpinan_department_name' => $pimpinanRole->department_name,
            'pimpinan_program_study_id' => $pimpinanRole->program_study_id,
            'pimpinan_program_study_name' => $pimpinanRole->program_study_name,
            'is_read_only' => true, // Pimpinan is always read-only
        ]);

        return $next($request);
    }
}
