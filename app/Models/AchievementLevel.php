<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchievementLevel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'points',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
