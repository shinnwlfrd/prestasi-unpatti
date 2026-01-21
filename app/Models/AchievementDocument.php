<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AchievementDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'sa_id',
        'document_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'external_link',
        'status',
        'revision_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Document Types
    const TYPE_SK_RESMI = 'sk_resmi';
    const TYPE_SERTIFIKAT = 'sertifikat';
    const TYPE_FOTO_DOKUMENTASI = 'foto_dokumentasi';
    const TYPE_SURAT_KETERANGAN = 'surat_keterangan';
    const TYPE_LINK_PUBLIKASI = 'link_publikasi';

    const DOCUMENT_TYPES = [
        self::TYPE_SK_RESMI => 'SK Resmi',
        self::TYPE_SERTIFIKAT => 'Sertifikat',
        self::TYPE_FOTO_DOKUMENTASI => 'Foto Dokumentasi',
        self::TYPE_SURAT_KETERANGAN => 'Surat Keterangan',
        self::TYPE_LINK_PUBLIKASI => 'Link Publikasi',
    ];

    const CREDIBILITY_SCORES = [
        self::TYPE_SK_RESMI => 30,
        self::TYPE_SERTIFIKAT => 25,
        self::TYPE_FOTO_DOKUMENTASI => 15,
        self::TYPE_SURAT_KETERANGAN => 20,
        self::TYPE_LINK_PUBLIKASI => 10,
    ];

    // Document Statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_REVISION = 'revision';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_REVISION => 'Perlu Revisi',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    const STATUS_BADGES = [
        self::STATUS_DRAFT => 'secondary',
        self::STATUS_PENDING => 'warning',
        self::STATUS_REVISION => 'info',
        self::STATUS_APPROVED => 'success',
        self::STATUS_REJECTED => 'danger',
    ];

    // Relationships
    public function studentAchievement()
    {
        return $this->belongsTo(StudentAchievement::class, 'sa_id', 'sa_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function revisions()
    {
        return $this->hasMany(DocumentRevision::class, 'document_id')->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getTypeNameAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'secondary';
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->document_type === self::TYPE_LINK_PUBLIKASI) {
            return $this->external_link;
        }
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    // Helper Methods
    public function isImage(): bool
    {
        return in_array($this->file_type, ['image/jpeg', 'image/png', 'image/jpg']);
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_REVISION]);
    }

    public function canBeDeleted(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_REVISION]);
    }

    public function canBeVerified(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // Status Transitions
    public function submit(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }
        
        $this->status = self::STATUS_PENDING;
        $this->save();
        
        $this->logRevision(DocumentRevision::ACTION_SUBMITTED, 'Dokumen disubmit untuk verifikasi');
        
        return true;
    }

    public function approve(User $verifier, ?string $notes = null): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_REVISION])) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->verified_by = $verifier->id;
        $this->verified_at = now();
        $this->revision_notes = $notes;
        $this->save();

        $this->logRevision(DocumentRevision::ACTION_APPROVED, $notes, $verifier->id);

        return true;
    }

    public function reject(User $verifier, string $reason): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_REVISION])) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->verified_by = $verifier->id;
        $this->verified_at = now();
        $this->revision_notes = $reason;
        $this->save();

        $this->logRevision(DocumentRevision::ACTION_REJECTED, $reason, $verifier->id);

        return true;
    }

    public function requestRevision(User $verifier, string $reason): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_REVISION;
        $this->revision_notes = $reason;
        $this->save();

        $this->logRevision(DocumentRevision::ACTION_REVISION_REQUESTED, $reason, $verifier->id);

        return true;
    }

    public function revertToPending(User $admin, ?string $reason = null): bool
    {
        if (!in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED])) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = self::STATUS_PENDING;
        $this->verified_by = null;
        $this->verified_at = null;
        $this->revision_notes = $reason;
        $this->save();

        $this->logRevision(
            'reverted_to_pending', 
            "Status dikembalikan dari {$oldStatus} ke pending. " . ($reason ?? ''), 
            $admin->id
        );

        return true;
    }

    public function addNote(User $admin, string $note): bool
    {
        $this->logRevision('note_added', $note, $admin->id);
        return true;
    }

    public function logRevision(string $action, ?string $notes = null, ?int $performedBy = null): DocumentRevision
    {
        return $this->revisions()->create([
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'external_link' => $this->external_link,
            'action' => $action,
            'notes' => $notes,
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeNeedsRevision($query)
    {
        return $query->where('status', self::STATUS_REVISION);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeEditable($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_REVISION]);
    }
}
