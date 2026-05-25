<?php

namespace App\Support;

class OperationalLogContext
{
    public static function authFailure(string $event, array $context = []): array
    {
        return array_merge([
            'domain' => 'auth',
            'metric_key' => 'auth.failure.rate',
            'event' => $event,
            'severity' => 'warning',
        ], $context);
    }

    public static function uploadFailure(string $event, array $context = []): array
    {
        return array_merge([
            'domain' => 'upload',
            'metric_key' => 'upload.failure.rate',
            'event' => $event,
            'severity' => 'warning',
        ], $context);
    }

    public static function validationFailure(string $event, array $context = []): array
    {
        return array_merge([
            'domain' => 'validation',
            'metric_key' => 'validation.failure.rate',
            'event' => $event,
            'severity' => 'warning',
        ], $context);
    }
}
