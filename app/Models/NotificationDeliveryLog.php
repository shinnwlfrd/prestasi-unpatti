<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationDeliveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sa_id',
        'student_id',
        'user_id',
        'notification_type',
        'action',
        'status',
        'channel',
        'error_message',
        'retry_count',
        'last_retry_at',
        'max_retry_reached_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
