<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleSwitchController extends Controller
{
    /**
     * Show role switch page
     */
    public function showSwitchPage()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Get available roles for this user (including virtual roles for super_admin)
        $roles = $user->getSwitchableRoles();

        // Check if user email exists in students table (mahasiswa role)
        $student = Student::where('email', $user->email)->first();

        // If user is also a student, add mahasiswa role
        if ($student) {
            // Create a pseudo UserRole object for mahasiswa
            $mahasiswaRole = new UserRole([
                'id' => 'student_'.$student->student_id, // Unique ID for student role
                'user_id' => $user->id,
                'role' => 'mahasiswa',
                'level' => 'student',
                'is_active' => true,
            ]);
            $mahasiswaRole->student_id = $student->student_id;
            $mahasiswaRole->student_name = $student->name;
            $mahasiswaRole->exists = true; // Mark as existing

            // Add to roles collection
            $roles->push($mahasiswaRole);
        }

        if ($roles->count() <= 1 && ! $student) {
            // User only has one role and not a student, redirect to appropriate dashboard
            return $this->redirectToDashboard($roles->first());
        }

        return view('role-switch', compact('roles'));
    }

    /**
     * Get available roles for current user (API endpoint)
     */
    public function getAvailableRoles()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $roles = $user->activeRoles()->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'role' => $role->role,
                'role_display' => $role->getRoleDisplayName(),
                'level' => $role->level,
                'level_display' => $role->getLevelDisplayName(),
                'scope' => $role->getScopeDescription(),
                'position' => $role->position,
                'is_active' => $role->is_active,
            ];
        });

        return response()->json($roles);
    }

    /**
     * Switch to selected role
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|string',
        ]);

        $user = Auth::user();
        $role_id = $request->role_id;

        // Check if switching to student role
        if (str_starts_with($role_id, 'student_')) {
            try {
                $studentId = str_replace('student_', '', $role_id);
                $student = Student::where('student_id', $studentId)
                    ->where('email', $user->email)
                    ->first();

                if (! $student) {
                    return response()->json(['error' => 'Data mahasiswa tidak ditemukan'], 404);
                }

                $previousRoleId = session('active_role_id');
                $this->clearRoleSession();

                session([
                    'auth_role' => 'student',
                    'student_id' => $student->student_id,
                    'student_name' => $student->name,
                    'student_email' => $student->email,
                    'student_nim' => $student->nim ?? $student->student_id,
                    'student_prodi' => $student->program_study_name ?? null,
                    'previous_role_id' => $previousRoleId,
                ]);

                session()->forget('active_role_id');
                session()->forget('active_role_type');

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('student.dashboard'),
                    'role' => 'Mahasiswa',
                ]);
            } catch (\Exception $e) {
                Log::error('Role Switch Student Error: '.$e->getMessage());

                return response()->json(['error' => 'Gagal beralih ke role mahasiswa: '.$e->getMessage()], 500);
            }
        }

        // Clean role-specific session variables before switching
        $this->clearRoleSession();

        // Check if switching to virtual validator role (for super admin)
        if ($role_id === 'virtual_validator_university') {
            session([
                'active_role_id' => 'virtual_validator_university',
                'active_role_type' => 'operator',
                'operator_level' => 'university',
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => route('validator.pending.index'),
                'role' => 'Super Operator',
            ]);
        }

        // Check if switching to virtual pimpinan role (for super admin)
        if ($role_id === 'virtual_pimpinan_university') {
            session([
                'active_role_id' => 'virtual_pimpinan_university',
                'active_role_type' => 'pimpinan',
                'pimpinan_level' => 'university',
                'pimpinan_position' => 'super_admin',
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => route('pimpinan.dashboard'),
                'role' => 'Pimpinan Universitas',
            ]);
        }

        // Switching from student to user role
        if (session('auth_role') === 'student') {
            // Clear student session
            session()->forget(['auth_role', 'student_id', 'student_name', 'student_email', 'student_nim', 'student_prodi']);
        }

        try {
            // Regular user role switch
            $selectedRole = $user->activeRoles()->find($role_id);

            if (! $selectedRole) {
                return response()->json(['error' => 'Role not found atau tidak aktif'], 404);
            }

            // Store selected role in session
            session([
                'active_role_id' => $selectedRole->id,
                'active_role_type' => $selectedRole->role,
            ]);

            // Store specific level and scope in session
            if ($selectedRole->role === 'operator') {
                session([
                    'operator_level' => $selectedRole->level,
                    'operator_faculty_id' => $selectedRole->faculty_id,
                    'operator_faculty_name' => $selectedRole->faculty_name,
                    'operator_department_id' => $selectedRole->department_id,
                    'operator_department_name' => $selectedRole->department_name,
                    'operator_program_study_id' => $selectedRole->program_study_id,
                    'operator_program_study_name' => $selectedRole->program_study_name,
                ]);
            } elseif ($selectedRole->role === 'pimpinan') {
                session([
                    'pimpinan_level' => $selectedRole->level,
                    'pimpinan_faculty_id' => $selectedRole->faculty_id,
                    'pimpinan_faculty_name' => $selectedRole->faculty_name,
                    'pimpinan_department_id' => $selectedRole->department_id,
                    'pimpinan_department_name' => $selectedRole->department_name,
                    'pimpinan_program_study_id' => $selectedRole->program_study_id,
                    'pimpinan_program_study_name' => $selectedRole->program_study_name,
                    'pimpinan_position' => $selectedRole->position,
                ]);
            }

            // Get redirect URL based on role
            $redirectUrl = $this->getRedirectUrl($selectedRole);

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'role' => $selectedRole->getRoleDisplayName(),
            ]);
        } catch (\Exception $e) {
            Log::error('Role Switch Error: '.$e->getMessage());

            return response()->json(['error' => 'Terjadi kesalahan sistem: '.$e->getMessage()], 500);
        }
    }

    /**
     * Get redirect URL based on role
     */
    private function getRedirectUrl($role)
    {
        return match ($role->role) {
            'super_admin', 'admin' => route('admin.dashboard'),
            'operator' => route('validator.pending.index'), // Operator goes to pending (index page)
            'pimpinan' => route('pimpinan.dashboard'),
            'mahasiswa' => route('student.dashboard'),
            default => route('login'),
        };
    }

    /**
     * Redirect to appropriate dashboard
     */
    private function redirectToDashboard($role)
    {
        if (! $role) {
            return redirect()->route('login');
        }

        return redirect($this->getRedirectUrl($role));
    }

    /**
     * Clear role-specific session variables
     */
    private function clearRoleSession()
    {
        session()->forget([
            'operator_level',
            'operator_scope',
            'operator_faculty_id',
            'operator_faculty_name',
            'operator_department_id',
            'operator_department_name',
            'operator_program_study_id',
            'operator_program_study_name',
            'pimpinan_level',
            'pimpinan_scope',
            'pimpinan_faculty_id',
            'pimpinan_faculty_name',
            'pimpinan_department_id',
            'pimpinan_department_name',
            'pimpinan_program_study_id',
            'pimpinan_program_study_name',
            'pimpinan_position',
            'is_read_only',
        ]);
    }
}
