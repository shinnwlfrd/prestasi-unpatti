<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'sa_id',
        'validator_id',
        'nama_peserta_valid',
        'nama_peserta_notes',
        'nama_lomba_valid',
        'nama_lomba_notes',
        'tanggal_valid',
        'tanggal_notes',
        'peringkat_valid',
        'peringkat_notes',
        'penyelenggara_valid',
        'penyelenggara_notes',
        'keaslian_dokumen_valid',
        'keaslian_dokumen_notes',
        'overall_notes',
    ];

    protected $casts = [
        'nama_peserta_valid' => 'boolean',
        'nama_lomba_valid' => 'boolean',
        'tanggal_valid' => 'boolean',
        'peringkat_valid' => 'boolean',
        'penyelenggara_valid' => 'boolean',
        'keaslian_dokumen_valid' => 'boolean',
    ];

    const CHECKLIST_ITEMS = [
        'nama_peserta' => 'Nama Peserta',
        'nama_lomba' => 'Nama Lomba',
        'tanggal' => 'Tanggal Pelaksanaan',
        'peringkat' => 'Peringkat/Ranking',
        'penyelenggara' => 'Penyelenggara',
        'keaslian_dokumen' => 'Keaslian Dokumen',
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
            if ($this->{$item . '_valid'}) {
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
