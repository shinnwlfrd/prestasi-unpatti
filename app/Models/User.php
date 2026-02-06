<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    public function validatedAchievements()
    {
        return $this->hasMany(StudentAchievement::class, 'validator_id');
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
        return $this->hasRole('operator');
    }

    public function isPimpinan(): bool
    {
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
