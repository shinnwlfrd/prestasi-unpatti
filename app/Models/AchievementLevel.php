<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AchievementLevel extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'description',
        'points',
        'icon',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('points', 'desc');
    }
}
