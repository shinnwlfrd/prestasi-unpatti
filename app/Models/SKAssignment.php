<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SKAssignment extends Model
{
    use HasFactory;

    protected $table = 'sk_assignments';

    protected $fillable = [
        'sk_id',
        'sa_id',
        'assigned_by',
        'assigned_at',
        'assignment_type',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    // Relationships
    public function skDocument()
    {
        return $this->belongsTo(SKDocument::class, 'sk_id');
    }

    public function achievement()
    {
        return $this->belongsTo(StudentAchievement::class, 'sa_id', 'sa_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
