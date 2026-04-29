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
            // Prevent non-superadmin from updating other admin/superadmin accounts
            if (($user->isAdmin() || $user->isSuperAdmin()) && !auth()->user()->isSuperAdmin() && $user->id !== auth()->id()) {
                return redirect()->route('admin.users')->with('error', 'Hanya Super Admin yang dapat mengubah data akun Admin lain.');
            }

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

        // Prevent non-superadmin from deleting admin/superadmin
        if (($user->isSuperAdmin() || $user->isAdmin()) && !auth()->user()->isSuperAdmin()) {
            $roleLabel = $user->isSuperAdmin() ? 'Super Admin' : 'Admin';
            return redirect()->route('admin.users')->with('error', "Hanya Super Admin yang dapat menghapus akun {$roleLabel}.");
        }

        $userName = $user->name;
        $this->userService->deleteUser($user->id);

        return redirect()->route('admin.users')->with('success', 'User ' . $userName . ' berhasil dihapus.');
    }

    public function deleteRole(User $user, $roleId)
    {
        try {
            $result = $this->userService->deleteRole(
                $user, 
                $roleId, 
                auth()->id(), 
                auth()->user()->isSuperAdmin()
            );

            return redirect()->route('admin.users')->with('success', "Role {$result['roleName']} berhasil dihapus dari {$result['userName']}.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting role', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'role_id' => $roleId
            ]);

            return redirect()->route('admin.users')->with('error', $e->getMessage());
        }
    }

    public function createNewUser(\Illuminate\Http\Request $request)
    {
        // Check if a soft-deleted user with this email exists
        $softDeletedUser = User::onlyTrashed()->where('email', $request->email)->first();

        // Custom validation based on role and level
        $rules = [
            // Exclude soft-deleted user from unique check if exists
            'email' => 'required|email|unique:users,email' . ($softDeletedUser ? ',' . $softDeletedUser->id : ',NULL,id,deleted_at,NULL'),
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Operator,Pimpinan',
            'faculty_id' => 'nullable|string',
            'department_id' => 'nullable|string',
            'program_study_id' => 'nullable|string',
        ];

        // Add conditional rules based on role
        if ($request->role === 'Operator') {
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

        $validated = $request->validate($rules);

        try {
            // Append optional fields to validated array to pass to service
            $validated['validator_faculty'] = $request->validator_faculty;
            $validated['pimpinan_level'] = $request->pimpinan_level;
            $validated['pimpinan_faculty'] = $request->pimpinan_faculty;
            $validated['pimpinan_department'] = $request->pimpinan_department;
            $validated['pimpinan_program_study'] = $request->pimpinan_program_study;

            $result = $this->userService->createSsoUser($validated, $softDeletedUser);

            $message = $result['was_restored']
                ? 'User berhasil diaktifkan kembali dengan role baru.'
                : 'User baru berhasil dibuat. Profil akan diupdate otomatis saat login pertama via SSO.';

            return redirect()->route('admin.users')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.users')
                ->withErrors(['error' => 'Terjadi kesalahan saat membuat user: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
