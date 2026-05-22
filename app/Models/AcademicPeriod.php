<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicPeriod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'year',
        'semester',
        'start_date',
        'end_date',
        'submission_deadline',
        'validation_deadline',
        'description',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submission_deadline' => 'datetime',
        'validation_deadline' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_active', 'desc')
            ->orderBy('start_date', 'desc');
    }

    // Get current active period
    public static function current()
    {
        return self::where('is_active', true)->first();
    }

    // Check if date is within this period
    public function isDateInPeriod($date)
    {
        $checkDate = is_string($date) ? Carbon::parse($date) : $date;

        return $checkDate->between($this->start_date, $this->end_date);
    }

    /**
     * Check if student submission is currently open.
     * Uses submission_deadline if set, otherwise falls back to end_date.
     */
    public function isSubmissionOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        $deadline = $this->submission_deadline ?? $this->end_date?->endOfDay();

        if (! $deadline) {
            return false;
        }

        return $now->greaterThanOrEqualTo($this->start_date->startOfDay())
            && $now->lessThanOrEqualTo($deadline);
    }

    /**
     * Check if validation by operators/admin is currently open.
     * Uses validation_deadline if set, otherwise always open (no limit).
     */
    public function isValidationOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->validation_deadline) {
            return true; // No validation deadline means always open for validators
        }

        return now()->lessThanOrEqualTo($this->validation_deadline);
    }

    /**
     * Get human-readable submission deadline.
     */
    public function getSubmissionDeadlineLabelAttribute(): string
    {
        $deadline = $this->submission_deadline ?? $this->end_date;

        return $deadline ? $deadline->format('d M Y H:i') : 'Tidak ditentukan';
    }

    /**
     * Get human-readable validation deadline.
     */
    public function getValidationDeadlineLabelAttribute(): string
    {
        return $this->validation_deadline
            ? $this->validation_deadline->format('d M Y H:i')
            : 'Tidak dibatasi';
    }

    // Activate this period (deactivate others)
    public function activate()
    {
        // Deactivate all other periods
        self::where('id', '!=', $this->id)->update(['is_active' => false]);

        // Activate this period
        $this->is_active = true;
        $this->save();
    }

    // Relationships
    public function achievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }
}
