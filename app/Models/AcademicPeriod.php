<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'year',
        'semester',
        'start_date',
        'end_date',
        'description',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('start_date', 'desc');
    }

    // Get current active period
    public static function current()
    {
        return self::where('is_active', true)->first();
    }

    // Check if date is within this period
    public function isDateInPeriod($date)
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

        return $checkDate->between($this->start_date, $this->end_date);
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
