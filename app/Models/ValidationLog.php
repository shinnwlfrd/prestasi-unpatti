<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';
    protected $fillable = [
        'sa_id',
        'validator_id',
        'old_status',
        'new_status',
        'notes',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    // Relasi
    public function studentAchievement()
    {
        return $this->belongsTo(StudentAchievement::class, 'sa_id', 'sa_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}