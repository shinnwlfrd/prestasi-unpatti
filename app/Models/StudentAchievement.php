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
        'ranking',
        'certificate',
        'submitted_at',
        'validation_status',
        'validator_id',
        'submitted_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_NEED_REVISION = 'need_revision';

    const LEVEL_UNIVERSITAS = 'Universitas';
    const LEVEL_NASIONAL = 'Nasional';
    const LEVEL_INTERNASIONAL = 'Internasional';

    // Relationships
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

    public function documents()
    {
        return $this->hasMany(AchievementDocument::class, 'sa_id', 'sa_id');
    }

    public function checklist()
    {
        return $this->hasOne(ValidationChecklist::class, 'sa_id', 'sa_id');
    }

    public function appeals()
    {
        return $this->hasMany(AchievementAppeal::class, 'sa_id', 'sa_id');
    }

    public function latestAppeal()
    {
        return $this->hasOne(AchievementAppeal::class, 'sa_id', 'sa_id')->latestOfMany();
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match($this->validation_status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_NEED_REVISION => 'info',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->validation_status) {
            self::STATUS_PENDING => 'Menunggu Validasi',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_NEED_REVISION => 'Perlu Revisi',
            default => 'Unknown',
        };
    }

    // Methods
    public function getDocumentTypeCount(): int
    {
        return $this->documents->pluck('document_type')->unique()->count();
    }

    public function hasMinimumDocuments(): bool
    {
        // Non-akademik requires at least 2 different document types
        if ($this->achievement && $this->achievement->category === 'Non-Akademik') {
            return $this->getDocumentTypeCount() >= 2;
        }
        return $this->documents->count() >= 1;
    }

    public function canBeAppealed(): bool
    {
        return $this->validation_status === self::STATUS_REJECTED 
            && !$this->appeals()->where('status', 'pending')->exists();
    }

    public function scopePending($query)
    {
        return $query->where('validation_status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('validation_status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('validation_status', self::STATUS_REJECTED);
    }

    public function scopeNeedRevision($query)
    {
        return $query->where('validation_status', self::STATUS_NEED_REVISION);
    }
}