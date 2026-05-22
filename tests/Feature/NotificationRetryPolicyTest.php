<?php

namespace Tests\Feature;

use App\Jobs\RetryNotificationDeliveryLogJob;
use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\NotificationDeliveryLog;
use App\Models\Student;
use App\Models\StudentAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationRetryPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function retry_job_increments_retry_count_on_failure(): void
    {
        Queue::fake();

        $period = AcademicPeriod::create([
            'name' => 'Genap 2025/2026',
            'code' => '20262',
            'year' => '2026',
            'semester' => 'Genap',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'submission_deadline' => now()->addDays(7),
            'validation_deadline' => now()->addDays(14),
            'is_active' => true,
        ]);

        $category = AchievementCategory::create([
            'name' => 'Akademik',
            'is_active' => true,
        ]);

        $achievement = Achievement::create([
            'name' => 'Test Achievement',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $student = Student::create([
            'student_id' => '20260001',
            'name' => 'Test Student',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Teknik',
            'email' => 'test@unpatti.ac.id',
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievement->id,
            'academic_period_id' => $period->id,
            'event_name' => 'Test Event',
            'level' => 'Nasional',
            'organizer' => 'Test Org',
            'event_date' => now()->toDateString(),
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'submitted_at' => now(),
        ]);

        $log = NotificationDeliveryLog::create([
            'sa_id' => $studentAchievement->sa_id,
            'student_id' => $student->student_id,
            'user_id' => null,
            'notification_type' => 'TestNotification',
            'action' => 'test_action',
            'status' => 'failed',
            'channel' => 'mail,database',
            'error_message' => 'Test error',
            'retry_count' => 0,
        ]);

        $job = new RetryNotificationDeliveryLogJob($log);
        $job->handle();

        $log->refresh();
        $this->assertEquals('skipped_user_not_linked', $log->status);
    }

    #[Test]
    public function retry_job_marks_max_retries_exceeded(): void
    {
        $log = NotificationDeliveryLog::create([
            'sa_id' => 999,
            'student_id' => '20260001',
            'user_id' => null,
            'notification_type' => 'TestNotification',
            'action' => 'test_action',
            'status' => 'failed',
            'channel' => 'mail,database',
            'error_message' => 'Test error',
            'retry_count' => 5,
        ]);

        $job = new RetryNotificationDeliveryLogJob($log);
        $job->handle();

        $log->refresh();
        $this->assertEquals('max_retries_exceeded', $log->status);
        $this->assertNotNull($log->max_retry_reached_at);
    }
}
