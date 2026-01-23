<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'faculty',
        'photo',
        // SSO fields
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_token_expires_at',
        'provider_data',
        'linked_at',
        'primary_auth',
        'last_login_at',
        'last_login_method',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        'provider_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'provider_data' => 'array',
            'provider_token_expires_at' => 'datetime',
            'linked_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Auth type checks
    public function isLocalOnly(): bool
    {
        return $this->password && ! $this->provider;
    }

    public function isSSOOnly(): bool
    {
        return ! $this->password && $this->provider;
    }

    public function isLinked(): bool
    {
        return $this->password && $this->provider;
    }

    public function canLoginLocal(): bool
    {
        return (bool) $this->password;
    }

    public function canLoginSSO(): bool
    {
        return (bool) $this->provider;
    }

    // Relationships
    public function authLogs(): HasMany
    {
        return $this->hasMany(AuthLog::class);
    }

    public function validatorProfile()
    {
        return $this->hasOne(ValidatorProfile::class);
    }

    public function validatedAchievements()
    {
        return $this->hasMany(StudentAchievement::class, 'validator_id');
    }

    public function validationLogs()
    {
        return $this->hasMany(ValidationLog::class, 'validator_id');
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/'.$this->photo) : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=10b981&color=fff';
    }
}
