<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

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
        'program',
        'program_study',
        'program_study_id',
        'program_study_code',
        'angkatan',
        'semester',
        'gpa',
        'email',
        'photo',
        'is_public_profile',
        'profile_slug',
        'bio',
        'social_links',
        'motto',
    ];

    protected $casts = [
        'gpa' => 'float',
        'semester' => 'integer',
        'is_public_profile' => 'boolean',
        'social_links' => 'array',
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
        return $this->photo ? asset('storage/'.$this->photo) : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=3b82f6&color=fff';
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
