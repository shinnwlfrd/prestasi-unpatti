<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'is_active',
        'level_id',
        'event_name',
        'organizer',
        'start_date',
        'end_date',
        'location',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relasi
    public function category()
    {
        return $this->belongsTo(AchievementCategory::class, 'category_id');
    }

    public function level()
    {
        return $this->belongsTo(AchievementLevel::class, 'level_id');
    }

    public function studentAchievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }
}
