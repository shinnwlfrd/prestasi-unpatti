<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'level',
        'faculty_id',
        'faculty_name',
        'department_id',
        'department_name',
        'program_study_id',
        'program_study_name',
        'position',
        'is_active',
        'activated_at',
        'deactivated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByFaculty($query, string $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }

    public function scopeByDepartment($query, string $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByProgramStudy($query, string $programStudyId)
    {
        return $query->where('program_study_id', $programStudyId);
    }

    /**
     * Helper methods
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isUniversityLevel(): bool
    {
        return $this->level === 'university';
    }

    public function isFacultyLevel(): bool
    {
        return $this->level === 'faculty';
    }

    public function isDepartmentLevel(): bool
    {
        return $this->level === 'department';
    }

    public function isProgramStudyLevel(): bool
    {
        return $this->level === 'program_study';
    }

    /**
     * Get display name for role
     */
    public function getRoleDisplayName(): string
    {
        if ($this->role === 'pimpinan') {
            return match ($this->position) {
                'rektor' => 'Rektor',
                'wakil_rektor_1' => 'Wakil Rektor I',
                'wakil_rektor_2' => 'Wakil Rektor II',
                'wakil_rektor_3' => 'Wakil Rektor III',
                'dekan' => 'Dekan',
                'ketua_jurusan' => 'Ketua Jurusan',
                'kaprodi' => 'Kepala Program Studi',
                'direktur_pps' => 'Direktur Pascasarjana',
                'kepala_biro_kemahasiswaan' => 'Kepala Biro Kemahasiswaan',
                default => 'Pimpinan',
            };
        }

        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin Universitas',
            'operator' => 'Operator Fakultas',
            'mahasiswa' => 'Mahasiswa',
            default => ucfirst($this->role),
        };
    }

    /**
     * Get display name for level
     */
    public function getLevelDisplayName(): string
    {
        return match ($this->level) {
            'university' => 'Universitas',
            'faculty' => 'Fakultas',
            'department' => 'Jurusan',
            'program_study' => 'Program Studi',
            default => '-',
        };
    }

    /**
     * Get full scope description
     */
    public function getScopeDescription(): string
    {
        if ($this->isUniversityLevel()) {
            // Special handling for specific university positions
            if ($this->position === 'direktur_pps') {
                return 'Program Pascasarjana';
            }
            return 'Seluruh Universitas';
        }

        if ($this->isFacultyLevel()) {
            return $this->faculty_name ?? 'Fakultas';
        }

        if ($this->isDepartmentLevel()) {
            $parts = [];
            if ($this->faculty_name) {
                $parts[] = $this->faculty_name;
            }
            if ($this->department_name) {
                $parts[] = $this->department_name;
            }
            return !empty($parts) ? implode(' → ', $parts) : 'Jurusan';
        }

        if ($this->isProgramStudyLevel()) {
            $parts = [];
            if ($this->faculty_name) {
                $parts[] = $this->faculty_name;
            }
            if ($this->department_name) {
                $parts[] = $this->department_name;
            }
            if ($this->program_study_name) {
                $parts[] = $this->program_study_name;
            }
            return !empty($parts) ? implode(' → ', $parts) : 'Program Studi';
        }

        return '-';
    }

    /**
     * Check if this role can access data from specific scope
     */
    public function canAccessFaculty(string $facultyId): bool
    {
        if ($this->isUniversityLevel()) {
            return true;
        }

        return $this->faculty_id === $facultyId;
    }

    public function canAccessDepartment(string $departmentId): bool
    {
        if ($this->isUniversityLevel()) {
            return true;
        }

        if ($this->isFacultyLevel()) {
            // Check if department belongs to this faculty
            // This would need SIGAP API call or cached data
            return true; // Simplified for now
        }

        return $this->department_id === $departmentId;
    }

    public function canAccessProgramStudy(string $programStudyId): bool
    {
        if ($this->isUniversityLevel()) {
            return true;
        }

        if ($this->isFacultyLevel() || $this->isDepartmentLevel()) {
            // Check if program study belongs to this faculty/department
            return true; // Simplified for now
        }

        return $this->program_study_id === $programStudyId;
    }

    /**
     * Activate this role
     */
    public function activate(): void
    {
        $this->update([
            'is_active' => true,
            'activated_at' => now(),
            'deactivated_at' => null,
        ]);
    }

    /**
     * Deactivate this role
     */
    public function deactivate(): void
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }
}
