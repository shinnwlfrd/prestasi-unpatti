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
        'academic_period_id',
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
        'is_appeal',
        'appeal_reason',
        'publication_link',
        'appealed_at',
        'sk_required',
        'sk_waiver_reason',
        'sk_waiver_notes',
        'alternative_document_path',
    ];

    protected $casts = [
        'event_date' => 'date',
        'submitted_at' => 'datetime',
        'appealed_at' => 'datetime',
        'is_appeal' => 'boolean',
        'sk_required' => 'boolean',
    ];

    // Status constants - sesuai dengan ENUM di database
    const STATUS_PENDING = 'Menunggu';
    const STATUS_APPROVED = 'Disetujui';
    const STATUS_REJECTED = 'Ditolak';
    const STATUS_NEED_REVISION = 'Revisi';

    const LEVEL_UNIVERSITAS = 'Universitas';
    const LEVEL_NASIONAL = 'Nasional';
    const LEVEL_INTERNASIONAL = 'Internasional';

    // SK Waiver reasons
    const SK_WAIVER_TINGKAT_UNIVERSITAS = 'tingkat_universitas';
    const SK_WAIVER_SK_DALAM_PROSES = 'sk_dalam_proses';
    const SK_WAIVER_DOKUMEN_ALTERNATIF = 'dokumen_alternatif';
    const SK_WAIVER_LAINNYA = 'lainnya';

    public static function getSkWaiverReasons(): array
    {
        return [
            self::SK_WAIVER_TINGKAT_UNIVERSITAS => 'Prestasi tingkat universitas tidak memerlukan SK',
            self::SK_WAIVER_SK_DALAM_PROSES => 'SK sedang dalam proses',
            self::SK_WAIVER_DOKUMEN_ALTERNATIF => 'Menggunakan dokumen alternatif',
            self::SK_WAIVER_LAINNYA => 'Lainnya (jelaskan di catatan)',
        ];
    }

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
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
        // Check if category is not "Akademik" (category_id != 1)
        if ($this->achievement && $this->achievement->category_id !== 1) {
            return $this->getDocumentTypeCount() >= 2;
        }
        return $this->documents->count() >= 1;
    }

    public function canBeAppealed(): bool
    {
        return $this->validation_status === self::STATUS_NEED_REVISION 
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