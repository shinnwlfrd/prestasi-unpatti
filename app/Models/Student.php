<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'student_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'name',
        'faculty',
        'faculty_id',
        'department',
        'department_id',
        'program_study',
        'program_study_id',
        'angkatan',
        'gpa',
        'email',
        'photo',
        'foto_url', // SIAKAD photo URL
        'ipk', // Alias for gpa
        'year', // Alias for angkatan
        'major', // Alias for department
        'major_id', // Alias for department_id
        'is_active',
    ];

    protected $casts = [
        'gpa' => 'float',
    ];

    // Relasi
    public function sikadCredential()
    {
        return $this->hasOne(SikadCredential::class, 'student_id', 'student_id');
    }

    public function achievements()
    {
        return $this->hasMany(StudentAchievement::class, 'student_id', 'student_id');
    }

    public function studentAchievements()
    {
        return $this->hasMany(StudentAchievement::class, 'student_id', 'student_id');
    }

    public function getPhotoUrlAttribute()
    {
        // Priority: foto_url (SIAKAD) > photo (local upload) > default avatar
        if (!empty($this->attributes['foto_url'])) {
            return $this->attributes['foto_url'];
        }
        
        if (!empty($this->attributes['photo'])) {
            return asset('storage/' . $this->attributes['photo']);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3b82f6&color=fff';
    }
    
    /**
     * Get SIGAP faculty data
     */
    public function getSigapFacultyAttribute()
    {
        if (!$this->faculty_id) {
            return null;
        }
        
        // Cache SIGAP data for 1 hour
        return cache()->remember("sigap_faculty_{$this->faculty_id}", 3600, function () {
            try {
                $sigapService = app(\App\Services\SigapApiService::class);
                $departments = $sigapService->getDepartments();
                
                // Find faculty from departments parent
                foreach ($departments as $dept) {
                    if ($dept['parent_id'] === $this->faculty_id) {
                        return [
                            'id' => $this->faculty_id,
                            'nama' => $this->faculty
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error getting SIGAP faculty', ['error' => $e->getMessage()]);
            }
            
            return null;
        });
    }
    
    /**
     * Get faculty name (alias for faculty field)
     */
    public function getFacultyNameAttribute()
    {
        return $this->faculty;
    }
    
    /**
     * Get program study name (alias for program_study field)
     */
    public function getProgramStudyNameAttribute()
    {
        return $this->program_study;
    }
    
    /**
     * Get SIGAP program study data
     */
    public function getSigapProgramStudyAttribute()
    {
        if (!$this->program_study_id) {
            return null;
        }
        
        // Cache SIGAP data for 1 hour
        return cache()->remember("sigap_prodi_{$this->program_study_id}", 3600, function () {
            try {
                $sigapService = app(\App\Services\SigapApiService::class);
                $studyPrograms = $sigapService->getStudyPrograms();
                
                foreach ($studyPrograms as $prodi) {
                    if ($prodi['id'] === $this->program_study_id) {
                        return $prodi;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error getting SIGAP program study', ['error' => $e->getMessage()]);
            }
            
            return null;
        });
    }
}
