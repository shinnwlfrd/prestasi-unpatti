<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExecutiveSetting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'value',
        'unit',
        'input_type',
        'category',
    ];

    /**
     * Get a setting value by key, with an optional default.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get all executive panel settings as a key => value array.
     */
    public static function getAllSettings(): array
    {
        return static::where('category', 'executive_panel')
            ->pluck('value', 'key')
            ->all();
    }
}
