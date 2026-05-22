<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class StudentAchievement extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::saved(function ($achievement) {
            static::invalidateDashboardCache();
            try {
                DashboardAggregation::rebuild();
            } catch (\Exception $e) {
                \Log::error('Failed to rebuild dashboard aggregations on saved: '.$e->getMessage());
            }
        });

        static::deleted(function ($achievement) {
            static::invalidateDashboardCache();
            try {
                DashboardAggregation::rebuild();
            } catch (\Exception $e) {
                \Log::error('Failed to rebuild dashboard aggregations on deleted: '.$e->getMessage());
            }
        });
    }

    public static function invalidateDashboardCache(): void
    {
        try {
            $key = 'dashboard_cache_version';
            if (! Cache::has($key)) {
                Cache::forever($key, 1);
            } else {
                Cache::increment($key);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to invalidate dashboard cache: '.$e->getMessage());
        }
    }

    public static function getDashboardCacheVersion(): int
    {
        try {
            return (int) Cache::get('dashboard_cache_version', 1);
        } catch (\Exception $e) {
            return 1;
        }
    }

    protected $primaryKey = 'sa_id';

    protected $fillable = [
        'student_id',
        'achievement_id',
        'academic_period_id',
        'student_snapshot',
        'points_snapshot',
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
        'publication_link',
        'sk_required',
        'sk_waiver_reason',
        'sk_waiver_notes',
        'alternative_document_path',
        // Two-stage validation fields
        'validation_stage',
        'current_stage',
        'faculty_validator_id',
        'faculty_validated_at',
        'faculty_notes',
        'university_validator_id',
        'university_validated_at',
        'university_notes',
        // Resubmission fields
        'is_resubmission',
        'resubmission_count',
        'last_resubmitted_at',
        'resubmission_reason',
    ];

    protected $casts = [
        'event_date' => 'date',
        'submitted_at' => 'datetime',
        'student_snapshot' => 'array',
        'points_snapshot' => 'array',
        'sk_required' => 'boolean',
        'faculty_validated_at' => 'datetime',
        'university_validated_at' => 'datetime',
        'is_resubmission' => 'boolean',
        'last_resubmitted_at' => 'datetime',
    ];

    // Status constants - Two-Stage Validation System
    // Stage 1: Faculty Level
    const STATUS_DRAFT = 'draft';

    const STATUS_SUBMITTED = 'submitted';

    const STATUS_FACULTY_REVIEW = 'faculty_review';

    const STATUS_FACULTY_APPROVED = 'faculty_approved';

    const STATUS_FACULTY_REJECTED = 'faculty_rejected';

    const STATUS_FACULTY_REVISION = 'faculty_revision';

    // Stage 2: University Level
    const STATUS_UNIVERSITY_REVIEW = 'university_review';

    const STATUS_UNIVERSITY_APPROVED = 'university_approved';

    const STATUS_UNIVERSITY_REJECTED = 'university_rejected';

    // Legacy statuses (for backward compatibility during migration)
    const STATUS_PENDING = 'Menunggu';

    const STATUS_APPROVED = 'Disetujui';

    const STATUS_REJECTED = 'Ditolak';

    const STATUS_NEED_REVISION = 'Revisi';

    // Validation Stages
    const STAGE_FACULTY = 'faculty';

    const STAGE_UNIVERSITY = 'university';

    const STAGE_COMPLETED = 'completed';

    const LEVEL_UNIVERSITAS = 'Universitas';

    const LEVEL_NASIONAL = 'Nasional';

    const LEVEL_INTERNASIONAL = 'Internasional';

    // SK Waiver reasons
    const SK_WAIVER_TINGKAT_UNIVERSITAS = 'tingkat_universitas';

    const SK_WAIVER_SK_DALAM_PROSES = 'sk_dalam_proses';

    const SK_WAIVER_DOKUMEN_ALTERNATIF = 'dokumen_alternatif';

    const SK_WAIVER_LAINNYA = 'lainnya';

    /**
     * Get grouped statuses for filtering and reporting
     */
    public static function getStatusGroups(): array
    {
        return self::getWorkflowStatusGroups();
    }

    public static function getWorkflowStatusGroups(): array
    {
        return [
            'approved' => [
                self::STATUS_APPROVED,
                self::STATUS_UNIVERSITY_APPROVED,
                'appeal_approved',
            ],
            'pending' => [
                self::STATUS_PENDING,
                self::STATUS_SUBMITTED,
                self::STATUS_FACULTY_REVIEW,
                self::STATUS_FACULTY_APPROVED,
                self::STATUS_UNIVERSITY_REVIEW,
                'appeal_submitted',
            ],
            'rejected' => [
                self::STATUS_REJECTED,
                self::STATUS_FACULTY_REJECTED,
                self::STATUS_UNIVERSITY_REJECTED,
                'appeal_rejected',
            ],
            'revision' => [
                self::STATUS_NEED_REVISION,
                self::STATUS_FACULTY_REVISION,
            ],
            'draft' => [self::STATUS_DRAFT],
        ];
    }

    public static function getFacultyPendingStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_SUBMITTED,
            self::STATUS_FACULTY_REVIEW,
        ];
    }

    public static function getValidationDecisionStatusGroups(): array
    {
        return [
            'approved' => [
                self::STATUS_APPROVED,
                self::STATUS_FACULTY_APPROVED,
                self::STATUS_UNIVERSITY_APPROVED,
                'appeal_approved',
            ],
            'rejected' => [
                self::STATUS_REJECTED,
                self::STATUS_FACULTY_REJECTED,
                self::STATUS_UNIVERSITY_REJECTED,
                'appeal_rejected',
            ],
            'revision' => [
                self::STATUS_NEED_REVISION,
                self::STATUS_FACULTY_REVISION,
                'revision_requested',
            ],
        ];
    }

    /**
     * Get status groups for AchievementRepository filtering
     */
    public static function getRepositoryStatusGroups(): array
    {
        return [
            'pending_verification' => ['submitted', self::STATUS_SUBMITTED, self::STATUS_FACULTY_REVIEW, self::STATUS_PENDING],
            'processing_university' => [self::STATUS_FACULTY_APPROVED, self::STATUS_UNIVERSITY_REVIEW],
            'approved' => [self::STATUS_UNIVERSITY_APPROVED, self::STATUS_APPROVED, 'appeal_approved'],
            'revision' => [self::STATUS_FACULTY_REVISION, self::STATUS_NEED_REVISION],
            'rejected' => [self::STATUS_FACULTY_REJECTED, self::STATUS_UNIVERSITY_REJECTED, self::STATUS_REJECTED, 'appeal_rejected'],
        ];
    }

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

    public function facultyValidator()
    {
        return $this->belongsTo(User::class, 'faculty_validator_id');
    }

    public function universityValidator()
    {
        return $this->belongsTo(User::class, 'university_validator_id');
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

    public function skAssignment()
    {
        return $this->hasOne(SKAssignment::class, 'sa_id', 'sa_id');
    }

    public function skDocument()
    {
        return $this->hasOneThrough(
            SKDocument::class,
            SKAssignment::class,
            'sa_id',
            'id',
            'sa_id',
            'sk_id'
        );
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->validation_status) {
            // New statuses
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SUBMITTED => 'info',
            self::STATUS_FACULTY_REVIEW => 'warning',
            self::STATUS_FACULTY_APPROVED => 'primary',
            self::STATUS_FACULTY_REJECTED => 'danger',
            self::STATUS_FACULTY_REVISION => 'warning',
            self::STATUS_UNIVERSITY_REVIEW => 'warning',
            self::STATUS_UNIVERSITY_APPROVED => 'success',
            self::STATUS_UNIVERSITY_REJECTED => 'danger',
            // Legacy statuses
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_NEED_REVISION => 'info',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->validation_status) {
            // New statuses
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Telah Diajukan',
            self::STATUS_FACULTY_REVIEW => 'Sedang Ditinjau Fakultas',
            self::STATUS_FACULTY_APPROVED => 'Disetujui oleh Fakultas',
            self::STATUS_FACULTY_REJECTED => 'Ditolak oleh Fakultas',
            self::STATUS_FACULTY_REVISION => 'Perlu Revisi (Fakultas)',
            self::STATUS_UNIVERSITY_REVIEW => 'Sedang Ditinjau Universitas',
            self::STATUS_UNIVERSITY_APPROVED => 'Disetujui oleh Universitas',
            self::STATUS_UNIVERSITY_REJECTED => 'Ditolak oleh Universitas',
            // Legacy statuses
            self::STATUS_PENDING, 'Menunggu' => 'Menunggu Verifikasi',
            self::STATUS_APPROVED, 'Disetujui' => 'Selesai Diverifikasi',
            self::STATUS_REJECTED, 'Ditolak' => 'Ditolak',
            self::STATUS_NEED_REVISION, 'Revisi' => 'Perlu Revisi',
            default => $this->validation_status ?? 'Unknown',
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

    public function isFinalStatus(): bool
    {
        return in_array($this->validation_status, [
            self::STATUS_FACULTY_REJECTED,
            self::STATUS_UNIVERSITY_APPROVED,
            self::STATUS_UNIVERSITY_REJECTED,
        ]);
    }

    public function canBeEdited(): bool
    {
        return $this->validation_status === self::STATUS_DRAFT;
    }

    public function canBeSubmitted(): bool
    {
        return $this->validation_status === self::STATUS_DRAFT
            && $this->hasMinimumDocuments();
    }

    public function isInFacultyStage(): bool
    {
        return in_array($this->validation_status, [
            self::STATUS_SUBMITTED,
            self::STATUS_FACULTY_REVIEW,
        ]);
    }

    public function isInUniversityStage(): bool
    {
        return in_array($this->validation_status, [
            self::STATUS_FACULTY_APPROVED,
            self::STATUS_UNIVERSITY_REVIEW,
        ]);
    }

    public function scopePending($query)
    {
        return $query->whereIn('validation_status', [
            self::STATUS_PENDING,
            self::STATUS_SUBMITTED,
            self::STATUS_FACULTY_REVIEW,
            self::STATUS_FACULTY_APPROVED,
            self::STATUS_UNIVERSITY_REVIEW,
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('validation_status', [
            self::STATUS_APPROVED,
            self::STATUS_UNIVERSITY_APPROVED,
        ]);
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('validation_status', [
            self::STATUS_REJECTED,
            self::STATUS_FACULTY_REJECTED,
            self::STATUS_UNIVERSITY_REJECTED,
        ]);
    }

    public function scopeNeedRevision($query)
    {
        return $query->whereIn('validation_status', [
            self::STATUS_NEED_REVISION,
            self::STATUS_FACULTY_REVISION,
        ]);
    }

    // New scopes for two-stage validation
    public function scopeFacultyPending($query)
    {
        return $query->whereIn('validation_status', [
            self::STATUS_SUBMITTED,
            self::STATUS_FACULTY_REVIEW,
        ]);
    }

    public function scopeFacultyApproved($query)
    {
        return $query->where('validation_status', self::STATUS_FACULTY_APPROVED);
    }

    public function scopeFacultyRejected($query)
    {
        return $query->where('validation_status', self::STATUS_FACULTY_REJECTED);
    }

    public function scopeFacultyRevision($query)
    {
        return $query->where('validation_status', self::STATUS_FACULTY_REVISION);
    }

    public function scopeUniversityPending($query)
    {
        return $query->whereIn('validation_status', [
            self::STATUS_FACULTY_APPROVED,
            self::STATUS_UNIVERSITY_REVIEW,
        ]);
    }

    public function scopeUniversityApproved($query)
    {
        return $query->where('validation_status', self::STATUS_UNIVERSITY_APPROVED);
    }

    public function scopeUniversityRejected($query)
    {
        return $query->where('validation_status', self::STATUS_UNIVERSITY_REJECTED);
    }

    public function scopeByFaculty($query, string $faculty)
    {
        return $query->whereHas('student', function ($q) use ($faculty) {
            $q->where('faculty', $faculty);
        });
    }

    public function scopeByStage($query, string $stage)
    {
        return $query->where('current_stage', $stage);
    }
}
