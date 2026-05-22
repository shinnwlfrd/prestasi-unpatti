<?php

namespace App\Support;

use App\Jobs\RetryNotificationDeliveryLogJob;
use App\Models\NotificationDeliveryLog;
use App\Models\StudentAchievement;
use App\Notifications\AchievementStatusChanged;
use Illuminate\Support\Facades\Log;

class AchievementNotificationDispatcher
{
    public static function notifyStudent(StudentAchievement $achievement, string $action): void
    {
        $student = $achievement->student;

        if (! $student) {
            self::createLog($achievement, null, null, $action, 'skipped_student_not_found', null, [
                'reason' => 'student_relation_missing',
            ]);

            return;
        }

        if (! $student->user) {
            self::createLog($achievement, $student->student_id, null, $action, 'skipped_user_not_linked', null, [
                'student_email' => $student->email,
                'reason' => 'student_user_relation_missing',
            ]);

            return;
        }

        try {
            $student->user->notify(new AchievementStatusChanged($achievement, $action));

            self::createLog($achievement, $student->student_id, $student->user->id, $action, 'sent');
        } catch (\Throwable $throwable) {
            $log = self::createLog(
                $achievement,
                $student->student_id,
                $student->user->id,
                $action,
                'failed',
                $throwable->getMessage(),
                ['exception' => get_class($throwable)]
            );

            Log::warning('Failed to send achievement notification to linked student user.', [
                'sa_id' => $achievement->sa_id,
                'student_id' => $student->student_id,
                'user_id' => $student->user->id,
                'action' => $action,
                'error' => $throwable->getMessage(),
            ]);

            if ($log && $log->retry_count < 5) {
                $delay = (int) pow(2, $log->retry_count) * 60;
                RetryNotificationDeliveryLogJob::dispatch($log)
                    ->onQueue('notifications')
                    ->delay(now()->addSeconds($delay));
            }
        }
    }

    private static function createLog(
        StudentAchievement $achievement,
        ?string $studentId,
        ?int $userId,
        string $action,
        string $status,
        ?string $errorMessage = null,
        ?array $meta = null
    ): ?NotificationDeliveryLog {
        return NotificationDeliveryLog::create([
            'sa_id' => $achievement->sa_id,
            'student_id' => $studentId,
            'user_id' => $userId,
            'notification_type' => AchievementStatusChanged::class,
            'action' => $action,
            'status' => $status,
            'channel' => 'mail,database',
            'error_message' => $errorMessage,
            'meta' => $meta,
        ]);
    }
}
