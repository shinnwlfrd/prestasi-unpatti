<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOperatorLevel
{
    /**
     * Handle an incoming request.
     * Check if user is an operator and store their level/scope in session
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Super admin can access operator pages
        if ($user->isSuperAdmin()) {
            session(['operator_level' => 'university', 'operator_scope' => '*']);
            return $next($request);
        }

        // Check if user has operator role
        if (!$user->isOperator()) {
            abort(403, 'Halaman ini hanya untuk Operator Fakultas.');
        }

        // Get operator role details
        $operatorRole = $user->getRolesByType('operator')->first();
        
        if (!$operatorRole) {
            abort(403, 'Role operator tidak ditemukan.');
        }

        // Store operator level and scope in session for easy access
        session([
            'operator_level' => $operatorRole->level,
            'operator_faculty_id' => $operatorRole->faculty_id,
            'operator_faculty_name' => $operatorRole->faculty_name,
            'operator_department_id' => $operatorRole->department_id,
            'operator_department_name' => $operatorRole->department_name,
            'operator_program_study_id' => $operatorRole->program_study_id,
            'operator_program_study_name' => $operatorRole->program_study_name,
        ]);

        return $next($request);
    }
}
