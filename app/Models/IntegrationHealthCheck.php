<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationHealthCheck extends Model
{
    protected $fillable = [
        'service',
        'status',
        'base_url',
        'message',
        'meta',
        'checked_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'checked_at' => 'datetime',
    ];
}
