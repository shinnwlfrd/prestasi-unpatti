<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementAppeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'sa_id',
        'student_id',
        'appeal_reason',
        'additional_notes',
        'publication_link',
        'status',
        'reviewed_by',
        'review_notes',
        'admin_notes',
        'reviewed_at',
        'submitted_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    public function studentAchievement()
    {
        return $this->belongsTo(StudentAchievement::class, 'sa_id', 'sa_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Review',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Unknown',
        };
    }
}
