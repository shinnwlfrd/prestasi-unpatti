<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function __construct(
        protected UserRepositoryInterface $userRepo
    ) {}

    public function createUser(array $data): User
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Handle "Semua Fakultas" case
        if (isset($data['faculty']) && $data['faculty'] === 'Semua Fakultas') {
            $data['faculty'] = null;
        }

        // New users are always active
        $data['is_active'] = true;

        return $this->userRepo->create($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        // Hash password if provided
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Handle "Semua Fakultas" case
        if (isset($data['faculty']) && $data['faculty'] === 'Semua Fakultas') {
            $data['faculty'] = null;
        }

        // Handle is_active checkbox
        $data['is_active'] = ($data['is_active'] ?? 0) == 1;

        return $this->userRepo->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepo->delete($id);
    }

    public function checkFacultyAvailability(string $faculty, ?int $excludeUserId = null): ?User
    {
        if ($faculty === 'Semua Fakultas') {
            return null;
        }

        $validators = $this->userRepo->getActiveValidators();

        return $validators->first(function ($validator) use ($faculty, $excludeUserId) {
            return $validator->faculty === $faculty && $validator->id !== $excludeUserId;
        });
    }

    public function getValidators()
    {
        return $this->userRepo->getValidators();
    }

    public function getUsers(int $perPage = 15)
    {
        return $this->userRepo->paginate($perPage);
    }
}
