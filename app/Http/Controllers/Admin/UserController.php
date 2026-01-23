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
    ) {}

    public function index()
    {
        $users = $this->userService->getUsers(15);

        return view('admin.users.index', compact('users'));
    }

    public function store(StoreUserRequest $request)
    {
        // Check if faculty is already assigned to another validator
        if ($request->role === 'Validator' && $request->faculty && $request->faculty !== 'Semua Fakultas') {
            $existingValidator = $this->userService->checkFacultyAvailability($request->faculty);

            if ($existingValidator) {
                return redirect()->route('admin.users')
                    ->withErrors(['faculty' => 'Fakultas '.$request->faculty.' sudah memiliki validator aktif ('.$existingValidator->name.'). Satu fakultas hanya boleh memiliki satu validator.'])
                    ->withInput()
                    ->with('showModal', true);
            }
        }

        $this->userService->createUser($request->validated());

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        // Check if faculty is already assigned to another validator
        if ($request->role === 'Validator' && $request->faculty && $request->faculty !== 'Semua Fakultas') {
            $existingValidator = $this->userService->checkFacultyAvailability($request->faculty, $user->id);

            if ($existingValidator) {
                return redirect()->route('admin.users')
                    ->withErrors(['faculty' => 'Fakultas '.$request->faculty.' sudah memiliki validator aktif ('.$existingValidator->name.'). Satu fakultas hanya boleh memiliki satu validator.'])
                    ->withInput()
                    ->with('showModal', true)
                    ->with('editUserId', $user->id);
            }
        }

        $this->userService->updateUser($user->id, $request->validated());

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $userName = $user->name;
        $this->userService->deleteUser($user->id);

        return redirect()->route('admin.users')->with('success', 'User '.$userName.' berhasil dihapus.');
    }
}
