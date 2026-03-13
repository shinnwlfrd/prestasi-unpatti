<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\UserManagementService;

class UserController extends Controller
{

    public function __construct(
        protected UserManagementService $userService
    ) {
    }

    public function index()
    {
        $search = request('search');
        $users = $this->userService->getUsers(15, $search);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $this->userService->createUser($request->validated());

            return redirect()->route('admin.users')->with('success', 'Role berhasil ditambahkan ke mahasiswa.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('password')
            ]);

            return redirect()->route('admin.users')
                ->withErrors(['error' => 'Terjadi kesalahan saat menambahkan role: ' . $e->getMessage()])
                ->withInput()
                ->with('showModal', true);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $this->userService->updateUser($user->id, $request->validated());

            return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $request->except('password')
            ]);

            return redirect()->route('admin.users')
                ->withErrors(['error' => 'Terjadi kesalahan saat update user: ' . $e->getMessage()])
                ->withInput()
                ->with('showModal', true)
                ->with('editUserId', $user->id);
        }
    }

    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $userName = $user->name;
        $this->userService->deleteUser($user->id);

        return redirect()->route('admin.users')->with('success', 'User ' . $userName . ' berhasil dihapus.');
    }

    public function deleteRole(User $user, $roleId)
    {
        try {
            // Prevent deleting own role
            if ($user->id === auth()->id()) {
                return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menghapus role sendiri.');
            }

            // Check if user has more than one role
            $userRoles = $user->activeRoles;
            if ($userRoles->count() <= 1) {
                return redirect()->route('admin.users')->with('error', 'User harus memiliki minimal 1 role. Tidak dapat menghapus role terakhir.');
            }

            // Find and delete the role
            $role = \App\Models\UserRole::where('id', $roleId)
                ->where('user_id', $user->id)
                ->first();

            if (!$role) {
                return redirect()->route('admin.users')->with('error', 'Role tidak ditemukan.');
            }

            $roleName = $role->getRoleDisplayName();
            $role->delete();

            return redirect()->route('admin.users')->with('success', "Role {$roleName} berhasil dihapus dari {$user->name}.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting role', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'role_id' => $roleId
            ]);

            return redirect()->route('admin.users')->with('error', 'Terjadi kesalahan saat menghapus role.');
        }
    }

    /**
     * Create a new user (will get profile from SSO on first login)
     */
    public function createNewUser(\Illuminate\Http\Request $request)
    {
        // Custom validation based on role and level
        $rules = [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Validator,Pimpinan',
            'faculty_id' => 'nullable|string',
            'department_id' => 'nullable|string',
            'program_study_id' => 'nullable|string',
        ];

        // Add conditional rules based on role
        if ($request->role === 'Validator') {
            $rules['validator_faculty'] = 'required|string';
        }

        if ($request->role === 'Pimpinan') {
            $rules['pimpinan_level'] = 'required|in:university,faculty,department,program_study,graduate_program';

            // Add rules based on pimpinan level
            if (in_array($request->pimpinan_level, ['faculty', 'department', 'program_study'])) {
                $rules['pimpinan_faculty'] = 'required|string';
            }

            if (in_array($request->pimpinan_level, ['department', 'program_study'])) {
                $rules['pimpinan_department'] = 'required|string';
            }

            if ($request->pimpinan_level === 'program_study') {
                $rules['pimpinan_program_study'] = 'required|string';
            }
        }

        // Check if assigning Admin role, must be super_admin
        if ($request->role === 'Admin' && (!auth()->check() || !auth()->user()->isSuperAdmin())) {
            return redirect()->route('admin.users')->with('error', 'Hanya Super Admin yang dapat membuat akun Admin baru.');
        }

        $request->validate($rules);

        try {
            // Determine IDs and Names based on role
            $faculty = null;
            $department = null;
            $programStudy = null;

            $facultyId = $request->faculty_id;
            $departmentId = $request->department_id;
            $programStudyId = $request->program_study_id;

            if ($request->role === 'Validator') {
                $faculty = $request->validator_faculty;
            } elseif ($request->role === 'Pimpinan') {
                $pimpinanLevel = $request->pimpinan_level;
                if (in_array($pimpinanLevel, ['faculty', 'department', 'program_study'])) {
                    $faculty = $request->pimpinan_faculty;
                }
                if (in_array($pimpinanLevel, ['department', 'program_study'])) {
                    $department = $request->pimpinan_department;
                }
                if ($pimpinanLevel === 'program_study') {
                    $programStudy = $request->pimpinan_program_study;
                }
            }

            // Create user with temporary data
            // Profile will be updated from SSO on first login
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Hash::make(\Str::random(32)), // Random password, will use SSO
                'role' => $request->role,
                'faculty' => $faculty,
                'provider' => 'unpatti_sso', // Mark as SSO user
                'is_active' => true,
            ]);

            // Create user role based on role type
            if ($request->role === 'Admin') {
                \App\Models\UserRole::create([
                    'user_id' => $user->id,
                    'role' => 'admin',
                    'level' => 'university',
                    'is_active' => true,
                ]);
            } elseif ($request->role === 'Validator') {
                \App\Models\UserRole::create([
                    'user_id' => $user->id,
                    'role' => 'operator',
                    'level' => ($faculty && $faculty !== 'Semua Fakultas') ? 'faculty' : 'university', 
                    'faculty_name' => $faculty,
                    'is_active' => true,
                ]);
            } elseif ($request->role === 'Pimpinan') {
                $pimpinanLevel = $request->pimpinan_level;
                // Map level to position
                $positionMap = [
                    'university' => 'rektor',
                    'faculty' => 'dekan',
                    'department' => 'ketua_jurusan',
                    'program_study' => 'kaprodi',
                    'graduate_program' => 'direktur_pps',
                ];
                $position = $positionMap[$pimpinanLevel] ?? 'rektor';

                \App\Models\UserRole::create([
                    'user_id' => $user->id,
                    'role' => 'pimpinan',
                    'level' => $pimpinanLevel,
                    'faculty_name' => $faculty,
                    'faculty_id' => $facultyId,
                    'department_name' => $department,
                    'department_id' => $departmentId,
                    'program_study_name' => $programStudy,
                    'program_study_id' => $programStudyId,
                    'position' => $position,
                    'is_active' => true,
                ]);
            }

            return redirect()->route('admin.users')->with('success', 'User baru berhasil dibuat. Profil akan diupdate otomatis saat login pertama via SSO.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating new user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('password')
            ]);

            return redirect()->route('admin.users')
                ->withErrors(['error' => 'Terjadi kesalahan saat membuat user: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
