<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementExport extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'requested_for_role_id',
        'context',
        'source',
        'format',
        'status',
        'filters',
        'scope_snapshot',
        'disk',
        'file_path',
        'file_name',
        'row_count',
        'file_size',
        'error_message',
        'queued_at',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'scope_snapshot' => 'array',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedForRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'requested_for_role_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED && filled($this->file_path);
    }
}
