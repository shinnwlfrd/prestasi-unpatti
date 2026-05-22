<?php

namespace App\Jobs;

use App\Models\NotificationDeliveryLog;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Notifications\AchievementStatusChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryNotificationDeliveryLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maxAttempts = 5;

    public int $retryAfter = 60;

    protected NotificationDeliveryLog $log;

    public function __construct(NotificationDeliveryLog $log)
    {
        $this->log = $log;
    }

    public function handle(): void
    {
        $this->log->refresh();

        if ($this->log->status !== 'failed') {
            return;
        }

        if ($this->log->retry_count >= $this->maxAttempts) {
            $this->log->update([
                'status' => 'max_retries_exceeded',
                'max_retry_reached_at' => now(),
            ]);

            Log::warning('Notification delivery max retries exceeded.', [
                'log_id' => $this->log->id,
                'sa_id' => $this->log->sa_id,
                'student_id' => $this->log->student_id,
                'retry_count' => $this->log->retry_count,
            ]);

            return;
        }

        try {
            $student = Student::find($this->log->student_id);

            if (! $student || ! $student->user) {
                $this->log->update([
                    'status' => 'skipped_user_not_linked',
                ]);

                return;
            }

            $student->user->notify(new AchievementStatusChanged(
                StudentAchievement::find($this->log->sa_id),
                $this->log->action ?? 'status_update'
            ));

            $this->log->update([
                'status' => 'sent',
                'retry_count' => $this->log->retry_count + 1,
                'last_retry_at' => now(),
            ]);
        } catch (\Throwable $throwable) {
            $this->log->update([
                'retry_count' => $this->log->retry_count + 1,
                'last_retry_at' => now(),
            ]);

            $delay = $this->calculateBackoff($this->log->retry_count);

            $this->release($delay);

            Log::warning('Notification retry failed, will retry later.', [
                'log_id' => $this->log->id,
                'retry_count' => $this->log->retry_count,
                'delay_seconds' => $delay,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    protected function calculateBackoff(int $attempt): int
    {
        return (int) pow(2, $attempt) * 60;
    }
}
