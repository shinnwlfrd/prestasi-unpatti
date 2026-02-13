<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SKDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sk_documents';

    protected $fillable = [
        'sk_number',
        'title',
        'file_path',
        'external_link',
        'issued_date',
        'issued_by',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(SKAssignment::class, 'sk_id');
    }

    public function achievements()
    {
        return $this->belongsToMany(
            StudentAchievement::class,
            'sk_assignments',
            'sk_id',
            'sa_id'
        )->withPivot('assigned_by', 'assigned_at', 'assignment_type', 'notes')
          ->withTimestamps();
    }

    // Accessors
    public function getAssignmentCountAttribute(): int
    {
        return $this->assignments()->count();
    }

    public function getFileTypeAttribute(): string
    {
        if ($this->file_path) {
            return 'file';
        } elseif ($this->external_link) {
            return 'link';
        }
        return 'none';
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return $this->external_link;
    }
}
