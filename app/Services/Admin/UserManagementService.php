<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserManagementService
{
    public function __construct(
        protected UserRepositoryInterface $userRepo
    ) {
    }

    public function createUser(array $data): User
    {
        DB::beginTransaction();

        try {
            // Check if user already exists (including soft-deleted)
            $existingUser = User::withTrashed()->where('email', $data['email'])->first();

            if ($existingUser) {
                // Restore if soft-deleted
                if ($existingUser->trashed()) {
                    $existingUser->restore();
                    $existingUser->update(['is_active' => true]);
                    Log::info('Restored soft-deleted user for role addition', [
                        'email' => $data['email'],
                    ]);
                }

                // User exists - add new role to existing user
                Log::info('Adding role to existing user', [
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
                    $level = $data['pimpinan_level'] ?? 'university';
                    $positionMap = [
                        'university' => 'rektor',
                        'faculty' => 'dekan',
                        'department' => 'ketua_jurusan',
                        'program_study' => 'kaprodi',
                        'graduate_program' => 'direktur_pps',
                    ];
                    $position = $data['pimpinan_position'] ?? ($positionMap[$level] ?? 'rektor');
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
                    // FIX: Set level based on faculty selection
                    $facultyName = $data['faculty'] ?? null;
                    $level = ($facultyName && $facultyName !== 'Semua Fakultas') ? 'faculty' : 'university';
                    $position = null;
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

                DB::commit();
                return $existingUser;
            }

            // User doesn't exist - check if it's a student
            $student = \App\Models\Student::where('email', $data['email'])->first();

            if ($student) {
                // Create user from student data
                Log::info('Creating user from student', [
                    'email' => $data['email'],
                    'student_id' => $student->student_id,
                    'role' => $data['role']
                ]);

                $data['name'] = $student->name;
                $data['password'] = Hash::make('password'); // Default password
            } else {
                // New user (not from student)
                if (!isset($data['password'])) {
                    $data['password'] = Hash::make('password'); // Default password
                } else {
                    $data['password'] = Hash::make($data['password']);
                }
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
                $level = $data['pimpinan_level'] ?? 'university';
                $positionMap = [
                    'university' => 'rektor',
                    'faculty' => 'dekan',
                    'department' => 'ketua_jurusan',
                    'program_study' => 'kaprodi',
                    'graduate_program' => 'direktur_pps',
                ];
                $position = $data['pimpinan_position'] ?? ($positionMap[$level] ?? 'rektor');
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
                // FIX: Set level based on faculty selection
                $facultyName = $data['faculty'] ?? null;
                $level = ($facultyName && $facultyName !== 'Semua Fakultas') ? 'faculty' : 'university';
                $position = null;
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

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user', [
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

        // Hash password if provided
        if (!empty($data['password'])) {
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

    public function deleteRole(User $user, int $roleId, int $authUserId, bool $isSuperAdmin): array
    {
        // Prevent deleting own role
        if ($user->id === $authUserId) {
            throw new \Exception('Anda tidak dapat menghapus role sendiri.');
        }

        // Check if user has more than one role
        $userRoles = $user->activeRoles;
        if ($userRoles->count() <= 1) {
            throw new \Exception('User harus memiliki minimal 1 role. Tidak dapat menghapus role terakhir.');
        }

        // Find and delete the role
        $role = \App\Models\UserRole::where('id', $roleId)
            ->where('user_id', $user->id)
            ->first();

        if (!$role) {
            throw new \Exception('Role tidak ditemukan.');
        }

        // Prevent non-superadmin from deleting superadmin/admin role
        if (in_array($role->role, ['super_admin', 'admin']) && !$isSuperAdmin) {
            $roleLabel = $role->role === 'super_admin' ? 'Super Admin' : 'Admin';
            throw new \Exception("Hanya Super Admin yang dapat menghapus role {$roleLabel}.");
        }

        $roleName = $role->getRoleDisplayName();
        $role->delete();

        // Sync legacy User.role column with remaining primary role
        $remainingPrimaryRole = $user->getPrimaryRole();
        if ($remainingPrimaryRole) {
            $legacyRoleMapping = [
                'super_admin' => 'Admin',
                'admin' => 'Admin',
                'operator' => 'Operator',
                'pimpinan' => 'Pimpinan',
                'mahasiswa' => 'Student',
            ];
            $user->update([
                'role' => $legacyRoleMapping[$remainingPrimaryRole->role] ?? ucfirst($remainingPrimaryRole->role),
                'faculty' => $remainingPrimaryRole->faculty_name,
            ]);
        }

        return ['success' => true, 'roleName' => $roleName, 'userName' => $user->name];
    }

    public function createSsoUser(array $data, ?User $softDeletedUser = null): array
    {
        DB::beginTransaction();

        try {
            $faculty = null;
            $department = null;
            $programStudy = null;

            $facultyId = $data['faculty_id'] ?? null;
            $departmentId = $data['department_id'] ?? null;
            $programStudyId = $data['program_study_id'] ?? null;

            if ($data['role'] === 'Operator') {
                $faculty = $data['validator_faculty'] ?? null;
            } elseif ($data['role'] === 'Pimpinan') {
                $pimpinanLevel = $data['pimpinan_level'];
                if (in_array($pimpinanLevel, ['faculty', 'department', 'program_study'])) {
                    $faculty = $data['pimpinan_faculty'] ?? null;
                }
                if (in_array($pimpinanLevel, ['department', 'program_study'])) {
                    $department = $data['pimpinan_department'] ?? null;
                }
                if ($pimpinanLevel === 'program_study') {
                    $programStudy = $data['pimpinan_program_study'] ?? null;
                }
            }

            // If soft-deleted user exists, restore and update instead of creating new
            if ($softDeletedUser) {
                $softDeletedUser->restore();
                $softDeletedUser->update([
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'faculty' => $faculty,
                    'provider' => 'unpatti_sso',
                    'is_active' => true,
                    'deleted_at' => null,
                ]);
                $user = $softDeletedUser;

                // Remove old roles and create fresh ones
                \App\Models\UserRole::where('user_id', $user->id)->forceDelete();

                Log::info('Restored soft-deleted user', [
                    'email' => $data['email'],
                    'new_role' => $data['role'],
                ]);
            } else {
                // Create user with temporary data
                // Profile will be updated from SSO on first login
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make(\Illuminate\Support\Str::random(32)), // Random password, will use SSO
                    'role' => $data['role'],
                    'faculty' => $faculty,
                    'provider' => 'unpatti_sso', // Mark as SSO user
                    'is_active' => true,
                ]);
            }

            // Create user role based on role type
            if ($data['role'] === 'Admin') {
                \App\Models\UserRole::create([
                    'user_id' => $user->id,
                    'role' => 'admin',
                    'level' => 'university',
                    'is_active' => true,
                ]);
            } elseif ($data['role'] === 'Operator') {
                \App\Models\UserRole::create([
                    'user_id' => $user->id,
                    'role' => 'operator',
                    'level' => ($faculty && $faculty !== 'Semua Fakultas') ? 'faculty' : 'university', 
                    'faculty_name' => $faculty,
                    'is_active' => true,
                ]);
            } elseif ($data['role'] === 'Pimpinan') {
                $pimpinanLevel = $data['pimpinan_level'];
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

            DB::commit();

            return [
                'success' => true, 
                'user' => $user, 
                'was_restored' => $softDeletedUser !== null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating new user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
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
