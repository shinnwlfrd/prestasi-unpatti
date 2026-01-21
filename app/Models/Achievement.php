<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
    ];

    // Relasi
    public function category()
    {
        return $this->belongsTo(AchievementCategory::class, 'category_id');
    }

    public function studentAchievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }
}