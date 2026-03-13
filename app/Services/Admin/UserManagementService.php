<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function __construct(
        protected UserRepositoryInterface $userRepo
    ) {
    }

    public function createUser(array $data): User
    {
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Check if user already exists
            $existingUser = User::where('email', $data['email'])->first();

            if ($existingUser) {
                // User exists - add new role to existing user
                \Illuminate\Support\Facades\Log::info('Adding role to existing user', [
                    'email' => $data['email'],
                    'new_role' => $data['role'],
                    'faculty' => $data['faculty'] ?? null
                ]);

                // Determine role type and level based on role field
                if ($data['role'] === 'Super Admin') {
                    $roleType = 'super_admin';
                    $level = 'university';
                    $position = null;
                    $facultyName = null;
                    $departmentName = null;
                    $programStudyName = null;
                } elseif ($data['role'] === 'Pimpinan') {
                    $roleType = 'pimpinan';
                    $level = 'university'; // Access level like rector
                    $position = 'rektor';  // Position like rector
                    $facultyName = $data['pimpinan_faculty'] ?? null;
                    $departmentName = $data['pimpinan_department'] ?? null;
                    $programStudyName = $data['pimpinan_program_study'] ?? null;
                } elseif ($data['role'] === 'Admin') {
                    $roleType = 'admin';
                    $level = 'university';
                    $position = null;
                    $facultyName = null;
                    $departmentName = null;
                    $programStudyName = null;
                } else {
                    $roleType = 'operator';
                    $level = 'university'; // Default to super validator level as requested
                    $position = null;
                    $facultyName = $data['faculty'] ?? null;
                    $departmentName = null;
                    $programStudyName = null;
                }

                // Create new UserRole
                \App\Models\UserRole::create([
                    'user_id' => $existingUser->id,
                    'role' => $roleType,
                    'level' => $level,
                    'position' => $position,
                    'faculty_name' => $facultyName,
                    'faculty_id' => $data['faculty_id'] ?? null,
                    'department_name' => $departmentName,
                    'department_id' => $data['department_id'] ?? null,
                    'program_study_name' => $programStudyName,
                    'program_study_id' => $data['program_study_id'] ?? null,
                    'is_active' => true,
                    'activated_at' => now(),
                ]);

                \Illuminate\Support\Facades\DB::commit();
                return $existingUser;
            }

            // User doesn't exist - check if it's a student
            $student = \App\Models\Student::where('email', $data['email'])->first();

            if ($student) {
                // Create user from student data
                \Illuminate\Support\Facades\Log::info('Creating user from student', [
                    'email' => $data['email'],
                    'student_id' => $student->student_id,
                    'role' => $data['role']
                ]);

            $data['name'] = $student->name;
            $data['password'] = Hash::make(\Illuminate\Support\Str::random(32)); // Random for SSO
        } else {
            // New user (not from student)
            $data['password'] = Hash::make(\Illuminate\Support\Str::random(32)); // Random for SSO
        }

            // Determine role type and level
            if ($data['role'] === 'Super Admin') {
                $roleType = 'super_admin';
                $level = 'university';
                $position = null;
                $facultyName = null;
                $departmentName = null;
                $programStudyName = null;
            } elseif ($data['role'] === 'Pimpinan') {
                $roleType = 'pimpinan';
                $level = 'university'; // Access level like rector
                $position = 'rektor';  // Default position like rector
                $facultyName = $data['pimpinan_faculty'] ?? null;
                $departmentName = $data['pimpinan_department'] ?? null;
                $programStudyName = $data['pimpinan_program_study'] ?? null;
            } elseif ($data['role'] === 'Admin') {
                $roleType = 'admin';
                $level = 'university';
                $position = null;
                $facultyName = null;
                $departmentName = null;
                $programStudyName = null;
            } else {
                $roleType = 'operator';
                $level = 'university'; // Default to super validator level as requested
                $position = null;
                $facultyName = $data['faculty'] ?? null;
                $departmentName = null;
                $programStudyName = null;
            }

            // New users are always active
            $data['is_active'] = true;

            // Create user
            $user = $this->userRepo->create($data);

            // Create UserRole
            \App\Models\UserRole::create([
                'user_id' => $user->id,
                'role' => $roleType,
                'level' => $level,
                'position' => $position,
                'faculty_name' => $facultyName,
                'faculty_id' => $data['faculty_id'] ?? null,
                'department_name' => $departmentName,
                'department_id' => $data['department_id'] ?? null,
                'program_study_name' => $programStudyName,
                'program_study_id' => $data['program_study_id'] ?? null,
                'is_active' => true,
                'activated_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return $user;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function updateUser(int $id, array $data): bool
    {
        // Remove name from update if not provided (keep existing name)
        if (empty($data['name'])) {
            unset($data['name']);
        }

        // Remove password if provided (not allowed in SSO mode)
        if (isset($data['password'])) {
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

    public function getUsers(int $perPage = 15, ?string $search = null)
    {
        $query = User::with('activeRoles');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                // 1. Cari di tabel users (termasuk kolom legacy/lama jika masih ada yang pakai)
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('role', 'ILIKE', "%{$search}%")
                    ->orWhere('faculty', 'ILIKE', "%{$search}%")

                    // 2. Cari di tabel relasi user_roles menggunakan whereHas
                    ->orWhereHas('activeRoles', function ($roleQuery) use ($search) {
                        $roleQuery->where('role', 'ILIKE', "%{$search}%")
                            ->orWhere('faculty_name', 'ILIKE', "%{$search}%")
                            ->orWhere('department_name', 'ILIKE', "%{$search}%")
                            ->orWhere('program_study_name', 'ILIKE', "%{$search}%")
                            ->orWhere('level', 'ILIKE', "%{$search}%")
                            ->orWhere('position', 'ILIKE', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['search' => $search]); // appends() sudah benar untuk pagination query string
    }
}
