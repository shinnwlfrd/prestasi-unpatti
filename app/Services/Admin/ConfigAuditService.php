<?php

namespace App\Services\Admin;

use App\Models\ConfigAuditLog;
use Illuminate\Database\Eloquent\Model;

class ConfigAuditService
{
    /**
     * Log a configuration change.
     */
    public function logChange(Model $model, string $action, ?array $oldValues = null, ?array $newValues = null): ConfigAuditLog
    {
        return ConfigAuditLog::log($model, $action, $oldValues, $newValues);
    }
}
