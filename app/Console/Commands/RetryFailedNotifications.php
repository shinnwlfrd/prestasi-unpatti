<?php

namespace App\Console\Commands;

use App\Jobs\RetryNotificationDeliveryLogJob;
use App\Models\NotificationDeliveryLog;
use App\Models\User;
use App\Notifications\IntegrationHealthDegraded;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class RetryFailedNotifications extends Command
{
    protected $signature = 'notifications:retry-failed
                            {--max=5 : Maximum retry attempts per log}
                            {--batch-size=100 : Number of logs to process per batch}
                            {--alert-threshold=5 : Alert when max_retries_exceeded exceeds this}';

    protected $description = 'Retry failed notification delivery logs with exponential backoff.';

    public function handle(): int
    {
        $maxAttempts = (int) $this->option('max');
        $alertThreshold = (int) $this->option('alert-threshold');
        $batchSize = (int) $this->option('batch-size');

        $this->info('Starting retry process for failed notifications...');

        $query = NotificationDeliveryLog::where('status', 'failed')
            ->where('retry_count', '<', $maxAttempts)
            ->orderBy('created_at', 'asc')
            ->limit($batchSize);

        $total = $query->count();

        if ($total === 0) {
            $this->info('No failed notifications to retry.');

            // Check if warning threshold exceeded
            $maxRetriesExceeded = NotificationDeliveryLog::where('status', 'max_retries_exceeded')->count();
            if ($maxRetriesExceeded >= $alertThreshold) {
                $this->warn("WARNING: {$maxRetriesExceeded} notifications have exceeded max retries (threshold: {$alertThreshold})");

                // Send alert to admin
                $admins = User::where('role', 'Admin')->where('is_active', true)->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new IntegrationHealthDegraded(
                        'Notification Retry',
                        $maxRetriesExceeded.' notifications have exceeded max retry attempts',
                        now()->toIso8601String()
                    ));
                }
            }

            return self::SUCCESS;
        }

        $this->info("Found {$total} failed notifications to retry.");

        $processed = 0;

        $query->chunk(50, function ($logs) use (&$processed) {
            foreach ($logs as $log) {
                RetryNotificationDeliveryLogJob::dispatch($log)
                    ->onQueue('notifications')
                    ->delay(now()->addSeconds(random_int(1, 10)));

                $processed++;
            }
        });

        $this->info("Dispatched {$processed} retry jobs to queue.");

        return self::SUCCESS;
    }
}
