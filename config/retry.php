<?php

return [
    'max_attempts' => env('NOTIFICATION_RETRY_MAX_ATTEMPTS', 5),
    'warning_threshold' => env('NOTIFICATION_RETRY_WARNING_THRESHOLD', 5),
    'cooldown_minutes' => env('NOTIFICATION_RETRY_COOLDOWN_MINUTES', 60),
    'queue_name' => env('NOTIFICATION_RETRY_QUEUE', 'notifications'),
];
