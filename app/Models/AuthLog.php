<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'action',
        'method',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    const UPDATED_AT = null; // Only use created_at, not updated_at

    // Actions
    const ACTION_LOGIN = 'login';

    const ACTION_LOGOUT = 'logout';

    const ACTION_FAILED_LOGIN = 'failed_login';

    const ACTION_SSO_LINK = 'sso_link';

    const ACTION_REGISTER = 'register';

    const ACTION_PASSWORD_RESET = 'password_reset';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFailedLogins($query)
    {
        return $query->where('action', self::ACTION_FAILED_LOGIN);
    }
}
