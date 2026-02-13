<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ValidationChecklist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sa_id',
        'validator_id',
        'certificate_valid',
        'event_date_valid',
        'organizer_valid',
        'level_appropriate',
        'documents_complete',
        'notes',
    ];

    protected $casts = [
        'certificate_valid' => 'boolean',
        'event_date_valid' => 'boolean',
        'organizer_valid' => 'boolean',
        'level_appropriate' => 'boolean',
        'documents_complete' => 'boolean',
    ];

    const CHECKLIST_ITEMS = [
        'certificate' => 'Sertifikat Valid',
        'event_date' => 'Tanggal Kegiatan Valid',
        'organizer' => 'Penyelenggara Valid',
        'level' => 'Tingkat Sesuai',
        'documents' => 'Dokumen Lengkap',
    ];

    public function studentAchievement()
    {
        return $this->belongsTo(StudentAchievement::class, 'sa_id', 'sa_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function getCheckedCountAttribute(): int
    {
        $count = 0;
        foreach (array_keys(self::CHECKLIST_ITEMS) as $item) {
            if ($this->{$item.'_valid'}) {
                $count++;
            }
        }

        return $count;
    }

    public function getTotalItemsAttribute(): int
    {
        return count(self::CHECKLIST_ITEMS);
    }

    public function getProgressPercentageAttribute(): float
    {
        return ($this->checked_count / $this->total_items) * 100;
    }

    public function isComplete(): bool
    {
        return $this->checked_count === $this->total_items;
    }
}
