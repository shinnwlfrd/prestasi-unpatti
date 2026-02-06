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
        
        if (!$user) {
            abort(403, 'Unauthorized access');
        }
        
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
        
        // Check if user email exists in students table (mahasiswa role)
        $student = \App\Models\Student::where('email', $user->email)->first();
        
        if ($student) {
            $isCurrent = session('auth_role') === 'student';
            
            $availableRoles[] = [
                'id' => 'student_' . $student->student_id,
                'role' => 'mahasiswa',
                'name' => 'Mahasiswa',
                'email' => $student->email,
                'display_name' => $student->name,
                'level' => 'Student',
                'scope' => 'NIM: ' . $student->student_id,
                'position' => null,
                'current' => $isCurrent
            ];
        }
        
        return view('profile.index', [
            'availableRoles' => $availableRoles,
            'hasMultipleRoles' => count($availableRoles) > 1
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
