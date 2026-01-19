<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'external_link',
        'action',
        'notes',
        'performed_by',
    ];

    const ACTION_UPLOADED = 'uploaded';
    const ACTION_REPLACED = 'replaced';
    const ACTION_REVISION_REQUESTED = 'revision_requested';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_SUBMITTED = 'submitted';

    const ACTION_LABELS = [
        self::ACTION_UPLOADED => 'Dokumen Diupload',
        self::ACTION_REPLACED => 'Dokumen Diganti',
        self::ACTION_REVISION_REQUESTED => 'Revisi Diminta',
        self::ACTION_APPROVED => 'Dokumen Disetujui',
        self::ACTION_REJECTED => 'Dokumen Ditolak',
        self::ACTION_SUBMITTED => 'Dokumen Disubmit',
    ];

    public function document()
    {
        return $this->belongsTo(AchievementDocument::class, 'document_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }

    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            self::ACTION_UPLOADED, self::ACTION_REPLACED => 'info',
            self::ACTION_SUBMITTED => 'warning',
            self::ACTION_REVISION_REQUESTED => 'warning',
            self::ACTION_APPROVED => 'success',
            self::ACTION_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}
