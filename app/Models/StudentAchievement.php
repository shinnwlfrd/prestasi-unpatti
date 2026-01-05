<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $primaryKey = 'sa_id';
    protected $fillable = [
        'student_id',
        'achievement_id',
        'event_name',
        'level',
        'organizer',
        'event_date',
        'description',
        'certificate_path',
        'validation_status',
        'validator_id',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    // Relasi
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function validationLogs()
    {
        return $this->hasMany(ValidationLog::class, 'sa_id', 'sa_id');
    }
}