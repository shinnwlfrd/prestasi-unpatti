<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'category', // 'Akademik' / 'Non-Akademik'
    ];

    // Relasi
    public function studentAchievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }
}