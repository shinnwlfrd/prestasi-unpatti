<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\AchievementStatusChanged;
use App\Services\AchievementApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentUserNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function linked_student_user_receives_approval_notification(): void
    {
        Notification::fake();

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
            'name' => 'Template Notifikasi',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $student = Student::create([
            'student_id' => '20260001',
            'name' => 'Mahasiswa Terhubung',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Teknik',
            'email' => 'mahasiswa.terhubung@unpatti.ac.id',
        ]);

        $studentUser = User::forceCreate([
            'name' => 'Mahasiswa Terhubung',
            'email' => 'mahasiswa.terhubung@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Student',
            'is_active' => true,
        ]);

        UserRole::create([
            'user_id' => $studentUser->id,
            'role' => 'mahasiswa',
            'level' => null,
            'is_active' => true,
        ]);

        $admin = User::forceCreate([
            'name' => 'Admin Approval',
            'email' => 'admin.approval@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'is_active' => true,
        ]);

        UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievement->id,
            'academic_period_id' => $period->id,
            'event_name' => 'Lomba Notifikasi',
            'level' => 'Nasional',
            'organizer' => 'Kemendikbud',
            'event_date' => now()->toDateString(),
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
            'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
            'submitted_at' => now()->subDay(),
            'faculty_validated_at' => now()->subHours(12),
        ]);

        $this->assertTrue($student->user->is($studentUser));
        $this->assertTrue($studentUser->student->is($student));

        app(AchievementApprovalService::class)->approve(
            $studentAchievement->fresh(),
            $admin,
            'Disetujui untuk tahap akhir.'
        );

        Notification::assertSentTo(
            $studentUser,
            AchievementStatusChanged::class,
            function (AchievementStatusChanged $notification, array $channels) use ($studentAchievement) {
                $payload = $notification->toArray((object) ['name' => 'Mahasiswa Terhubung']);

                return in_array('mail', $channels, true)
                    && in_array('database', $channels, true)
                    && $payload['achievement_id'] === $studentAchievement->sa_id
                    && $payload['status'] === StudentAchievement::STATUS_UNIVERSITY_APPROVED;
            }
        );
    }
}
