<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'faculty',
        'faculty_id',
        'photo',
        // SSO fields
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_token_expires_at',
        'provider_data',
        'linked_at',
        'primary_auth',
        'last_login_at',
        'last_login_method',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        'provider_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'provider_data' => 'array',
            'provider_token_expires_at' => 'datetime',
            'linked_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Auth type checks
    public function isLocalOnly(): bool
    {
        return $this->password && ! $this->provider;
    }

    public function isSSOOnly(): bool
    {
        return ! $this->password && $this->provider;
    }

    public function isLinked(): bool
    {
        return $this->password && $this->provider;
    }

    public function canLoginLocal(): bool
    {
        return (bool) $this->password;
    }

    public function canLoginSSO(): bool
    {
        return (bool) $this->provider;
    }

    // Relationships
    public function authLogs(): HasMany
    {
        return $this->hasMany(AuthLog::class);
    }

    public function validatorProfile()
    {
        return $this->hasOne(ValidatorProfile::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'email', 'email');
    }

    public function validatedAchievements()
    {
        return $this->hasMany(StudentAchievement::class, 'validator_id');
    }

    public function facultyValidatedAchievements()
    {
        return $this->hasMany(StudentAchievement::class, 'faculty_validator_id');
    }

    public function universityValidatedAchievements()
    {
        return $this->hasMany(StudentAchievement::class, 'university_validator_id');
    }

    public function validationLogs()
    {
        return $this->hasMany(ValidationLog::class, 'validator_id');
    }

    /**
     * Multi-role relationships
     */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function activeRoles(): HasMany
    {
        return $this->hasMany(UserRole::class)->where('is_active', true);
    }

    /**
     * Multi-role helper methods
     */
    public function hasRole(string $role): bool
    {
        return $this->activeRoles()->where('role', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->activeRoles()->whereIn('role', $roles)->exists();
    }

    public function hasAllRoles(array $roles): bool
    {
        $userRoles = $this->activeRoles()->pluck('role')->toArray();

        return count(array_intersect($roles, $userRoles)) === count($roles);
    }

    public function getRolesByType(string $role)
    {
        return $this->activeRoles()->where('role', $role)->get();
    }

    /**
     * Get all roles available for switching, including virtual roles for super_admin
     */
    public function getSwitchableRoles()
    {
        $roles = $this->activeRoles()->get();

        // For super_admin, add virtual roles for validator and pimpinan
        if ($this->role === 'Admin' || $this->isSuperAdmin()) {
            $hasSuperAdmin = $roles->where('role', 'super_admin')->isNotEmpty();

            if ($hasSuperAdmin) {
                // Add virtual validator role if not exists
                if ($roles->where('role', 'operator')->where('level', 'university')->isEmpty()) {
                    $validatorRole = new UserRole([
                        'id' => 'virtual_validator_university',
                        'user_id' => $this->id,
                        'role' => 'operator',
                        'level' => 'university',
                        'is_active' => true,
                    ]);
                    $validatorRole->exists = true;
                    $roles->push($validatorRole);
                }

                // Add virtual pimpinan role if not exists
                if ($roles->where('role', 'pimpinan')->where('level', 'university')->isEmpty()) {
                    $pimpinanRole = new UserRole([
                        'id' => 'virtual_pimpinan_university',
                        'user_id' => $this->id,
                        'role' => 'pimpinan',
                        'level' => 'university',
                        'is_active' => true,
                    ]);
                    $pimpinanRole->exists = true;
                    $roles->push($pimpinanRole);
                }
            }
        }

        return $roles;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isOperator(): bool
    {
        if (session('active_role_type') === 'operator') {
            return true;
        }

        return $this->hasRole('operator');
    }

    public function isPimpinan(): bool
    {
        if (session('active_role_type') === 'pimpinan') {
            return true;
        }

        return $this->hasRole('pimpinan');
    }

    public function isMahasiswa(): bool
    {
        return $this->hasRole('mahasiswa');
    }

    /**
     * Get primary role (for backward compatibility)
     */
    public function getPrimaryRole(): ?UserRole
    {
        // Priority: super_admin > admin > operator > pimpinan > mahasiswa
        $priority = ['super_admin', 'admin', 'operator', 'pimpinan', 'mahasiswa'];

        foreach ($priority as $role) {
            $userRole = $this->activeRoles()->where('role', $role)->first();
            if ($userRole) {
                return $userRole;
            }
        }

        return null;
    }

    /**
     * Get all active role names
     */
    public function getActiveRoleNames(): array
    {
        return $this->activeRoles()->pluck('role')->toArray();
    }

    /**
     * Get current active role from session
     */
    public function getCurrentRole(): ?UserRole
    {
        $activeRoleId = session('active_role_id');

        if ($activeRoleId) {
            // Check if it's a virtual role (from Superadmin switching)
            if (str_starts_with($activeRoleId, 'virtual_')) {
                $roleType = session('active_role_type');
                if ($roleType) {
                    $prefix = $roleType === 'operator' ? 'operator' : 'pimpinan';
                    $virtualRole = new UserRole([
                        'id' => $activeRoleId,
                        'user_id' => $this->id,
                        'role' => $roleType,
                        'level' => session("{$prefix}_level", 'university'),
                        'faculty_id' => session("{$prefix}_faculty_id"),
                        'faculty_name' => session("{$prefix}_faculty_name"),
                        'department_id' => session("{$prefix}_department_id"),
                        'department_name' => session("{$prefix}_department_name"),
                        'program_study_id' => session("{$prefix}_program_study_id"),
                        'program_study_name' => session("{$prefix}_program_study_name"),
                        'position' => session("{$prefix}_position"),
                        'is_active' => true,
                    ]);
                    $virtualRole->exists = true;

                    return $virtualRole;
                }
            }

            return $this->activeRoles()->find($activeRoleId);
        }

        return $this->getPrimaryRole();
    }

    /**
     * Check if user can access specific faculty data
     */
    public function canAccessFaculty(string $facultyId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->activeRoles()->where(function ($query) use ($facultyId) {
            $query->where('level', 'university')
                ->orWhere('faculty_id', $facultyId);
        })->exists();
    }

    /**
     * Get accessible faculty IDs
     */
    public function getAccessibleFacultyIds(): array
    {
        if ($this->isSuperAdmin() || $this->activeRoles()->where('level', 'university')->exists()) {
            return ['*']; // All faculties
        }

        return $this->activeRoles()
            ->whereNotNull('faculty_id')
            ->pluck('faculty_id')
            ->unique()
            ->toArray();
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/'.$this->photo) : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=10b981&color=fff';
    }
}
