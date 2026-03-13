<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $availableRoles = [];
        $email = null;
        $name = null;

        // 1. If authenticated via regular Laravel Auth (Admins, Validators, or Students with existing User record)
        if ($user) {
            $email = $user->email;
            $name = $user->name;

            // Get all active roles for this user from user_roles table
            $availableRoles = $user->activeRoles()->get()->map(function ($role) use ($user) {
                $isCurrent = session('active_role_id') == $role->id;
                
                return [
                    'id' => $role->id,
                    'role' => $role->role,
                    'name' => $role->getRoleDisplayName(),
                    'email' => $user->email,
                    'display_name' => $user->name,
                    'level' => $role->getLevelDisplayName(),
                    'scope' => $role->getScopeDescription(),
                    'position' => $role->position,
                    'current' => $isCurrent
                ];
            })->toArray();
        }

        // 2. Check for Student data (could be session-only or from Student table)
        $isStudentSession = session('auth_role') === 'student';
        $studentId = session('student_id');
        
        if ($isStudentSession && $studentId) {
            $student = \App\Models\Student::where('student_id', $studentId)->first();
            $studentData = session('student_data');
            
            // Collect info from student record or session
            $sEmail = $student->email ?? $studentData['email'] ?? null;
            $sName = $student->name ?? $studentData['nama'] ?? 'Student';
            $sNim = $student->student_id ?? $studentData['nim'] ?? $studentId;

            if ($sEmail) {
                $email = $email ?? $sEmail;
                $name = $name ?? $sName;

                // Check if we already have a mahasiswa role in availableRoles
                $hasMahasiswaRole = collect($availableRoles)->contains('role', 'mahasiswa');

                if (!$hasMahasiswaRole) {
                    $availableRoles[] = [
                        'id' => 'student_' . $sNim,
                        'role' => 'mahasiswa',
                        'name' => 'Mahasiswa',
                        'email' => $sEmail,
                        'display_name' => $sName,
                        'level' => 'Student',
                        'scope' => 'NIM: ' . $sNim,
                        'position' => null,
                        'current' => true
                    ];
                }
            }
        }

        // Final check: if still no identity found, then really unauthorized
        if (!$email) {
            abort(403, 'Unauthorized access');
        }

        // Prepare a user-like object for the view for consistency
        $displayUser = (object) [
            'name' => $name,
            'email' => $email,
            'role_display' => collect($availableRoles)->firstWhere('current', true)['name'] ?? 'User',
            'is_active' => $user?->is_active ?? true,
            'photo_url' => $user?->photo_url ?? session('student_data.foto_url') ?? null,
            'last_login_method' => $user?->last_login_method ?? (session('sso_authenticated') ? 'sso' : null),
            'password' => $user?->password ?? null,
            'provider' => $user?->provider ?? (session('sso_authenticated') ? 'unpatti_sso' : null),
            'linked_at' => $user?->linked_at ?? null,
            'last_login_at' => $user?->last_login_at ?? null,
        ];
        
        return view('profile.index', [
            'user' => $displayUser,
            'availableRoles' => $availableRoles,
            'hasMultipleRoles' => count($availableRoles) > 1
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return back()->with('error', 'Profil Anda dikelola oleh sistem SSO dan tidak dapat diubah di sini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
