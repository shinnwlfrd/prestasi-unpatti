<?php

namespace App\Support;

use App\Jobs\RetryNotificationDeliveryLogJob;
use App\Models\NotificationDeliveryLog;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class GenericNotificationDispatcher
{
    public static function send(
        object $notifiable,
        Notification $notification,
        array $context = []
    ): ?NotificationDeliveryLog {
        try {
            $notifiable->notify($notification);

            return self::createLog($notification, $notifiable, 'sent', null, $context);
        } catch (\Throwable $throwable) {
            $log = self::createLog($notification, $notifiable, 'failed', $throwable->getMessage(), $context);

            Log::warning('Generic notification delivery failed.', [
                'notification_type' => get_class($notification),
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
                'error' => $throwable->getMessage(),
            ]);

            if ($log && $log->retry_count < config('retry.max_attempts', 5)) {
                $delay = (int) pow(2, $log->retry_count) * 60;
                RetryNotificationDeliveryLogJob::dispatch($log)
                    ->onQueue(config('retry.queue_name', 'notifications'))
                    ->delay(now()->addSeconds($delay));
            }

            return $log;
        }
    }

    private static function createLog(
        Notification $notification,
        object $notifiable,
        string $status,
        ?string $errorMessage = null,
        array $context = []
    ): ?NotificationDeliveryLog {
        return NotificationDeliveryLog::create([
            'sa_id' => $context['sa_id'] ?? null,
            'student_id' => $context['student_id'] ?? null,
            'user_id' => $notifiable->id ?? null,
            'notification_type' => get_class($notification),
            'action' => $context['action'] ?? 'generic',
            'status' => $status,
            'channel' => 'mail,database',
            'error_message' => $errorMessage,
            'meta' => $context,
        ]);
    }
}
