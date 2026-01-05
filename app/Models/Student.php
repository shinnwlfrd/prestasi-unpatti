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
        'program_study',
        'semester',
        'gpa',
        'email',
        'photo',
    ];

    protected $casts = [
        'gpa' => 'float',
        'semester' => 'integer',
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
        return $this->photo ? asset('storage/' . $this->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3b82f6&color=fff';
    }
}