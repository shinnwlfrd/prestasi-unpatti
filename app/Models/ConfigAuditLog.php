<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'changed_fields',
        'old_values',
        'new_values',
        'user_id',
        'user_name',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'changed_fields' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function auditable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForModel($query, string $type, ?int $id = null)
    {
        $query->where('auditable_type', $type);
        if ($id) {
            $query->where('auditable_id', $id);
        }

        return $query;
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // Accessors
    public function getAuditableNameAttribute(): string
    {
        return match ($this->auditable_type) {
            AcademicPeriod::class, 'AcademicPeriod' => 'Periode Akademik',
            AchievementCategory::class, 'AchievementCategory' => 'Kategori Prestasi',
            AchievementLevel::class, 'AchievementLevel' => 'Level Prestasi',
            default => class_basename($this->auditable_type),
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Dibuat',
            'updated' => 'Diperbarui',
            'deleted' => 'Dihapus',
            'restored' => 'Dipulihkan',
            'activated' => 'Diaktifkan',
            default => $this->action,
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            'activated' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Static helper to create an audit log entry.
     */
    public static function log(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): self {
        $user = $userId ? User::find($userId) : auth()->user();

        $changedFields = null;
        if ($oldValues && $newValues) {
            $changedFields = [];
            foreach ($newValues as $key => $newVal) {
                $oldVal = $oldValues[$key] ?? null;
                if ($oldVal !== $newVal) {
                    $changedFields[$key] = [
                        'old' => $oldVal,
                        'new' => $newVal,
                    ];
                }
            }
            if (empty($changedFields)) {
                $changedFields = null;
            }
        }

        return self::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'changed_fields' => $changedFields,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => $user?->id ?? 0,
            'user_name' => $user?->name ?? 'System',
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent() ? substr(request()->userAgent(), 0, 255) : null,
            'created_at' => now(),
        ]);
    }
}
