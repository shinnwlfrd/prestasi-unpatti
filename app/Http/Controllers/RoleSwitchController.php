<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    /**
     * Show role switch page
     */
    public function showSwitchPage()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get all active roles for this user from user_roles table
        $roles = $user->activeRoles()->with('user')->get();

        // Check if user email exists in students table (mahasiswa role)
        $student = \App\Models\Student::where('email', $user->email)->first();

        // If user is also a student, add mahasiswa role
        if ($student) {
            // Create a pseudo UserRole object for mahasiswa
            $mahasiswaRole = new \App\Models\UserRole([
                'id' => 'student_' . $student->student_id, // Unique ID for student role
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

        if ($roles->count() <= 1 && !$student) {
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

        if (!$user) {
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
                $student = \App\Models\Student::where('student_id', $studentId)
                    ->where('email', $user->email)
                    ->first();

                if (!$student) {
                    return response()->json(['error' => 'Data mahasiswa tidak ditemukan'], 404);
                }

                // Update user's primary role in database
                $user->update([
                    'role' => 'Mahasiswa',
                    'faculty' => $student->faculty,
                ]);

                // Don't logout, just switch session to student mode
                // Keep user auth for SSO users
                session([
                    'auth_role' => 'student',
                    'student_id' => $student->student_id,
                    'student_name' => $student->name,
                    'student_email' => $student->email,
                    'student_nim' => $student->nim ?? $student->student_id,
                    'student_prodi' => $student->program_study_name ?? null,
                    // Keep active_role_id for switching back
                    'previous_role_id' => session('active_role_id'),
                ]);

                // Clear active role to indicate student mode
                session()->forget('active_role_id');
                session()->forget('active_role_type');

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('student.dashboard'),
                    'role' => 'Mahasiswa',
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Role Switch Student Error: ' . $e->getMessage());
                return response()->json(['error' => 'Gagal beralih ke role mahasiswa: ' . $e->getMessage()], 500);
            }
        }

        // Switching from student to user role
        if (session('auth_role') === 'student') {
            // Clear student session
            session()->forget(['auth_role', 'student_id', 'student_name', 'student_email', 'student_nim', 'student_prodi']);
        }

        try {
            // Regular user role switch
            $selectedRole = $user->activeRoles()->find($role_id);

            if (!$selectedRole) {
                return response()->json(['error' => 'Role not found atau tidak aktif'], 404);
            }

            // Store selected role in session
            session([
                'active_role_id' => $selectedRole->id,
                'active_role_type' => $selectedRole->role,
            ]);

            // Update user's primary role/context in database
            // Mapping UserRole 'role' to User 'role'
            $userRoleToUserTable = match ($selectedRole->role) {
                'super_admin', 'admin' => 'Admin',
                'operator' => 'Validator',
                'pimpinan' => 'Pimpinan',
                'mahasiswa' => 'Mahasiswa',
                default => ucfirst($selectedRole->role),
            };

            $user->update([
                'role' => $userRoleToUserTable,
                'faculty' => $selectedRole->faculty_name,
                'faculty_id' => $selectedRole->faculty_id,
            ]);

            // Get redirect URL based on role
            $redirectUrl = $this->getRedirectUrl($selectedRole);

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'role' => $selectedRole->getRoleDisplayName(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Role Switch Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
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
        if (!$role) {
            return redirect()->route('login');
        }

        return redirect($this->getRedirectUrl($role));
    }
}
