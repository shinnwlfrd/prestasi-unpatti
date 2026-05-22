<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementDocument;
use App\Models\AchievementLevel;
use App\Models\ConfigAuditLog;
use App\Models\SKDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Models\ValidationChecklist;
use App\Services\Admin\UniversityValidationService;
use App\Services\Student\AchievementService;
use App\Services\Validator\FacultyValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PeriodDeadlineTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicPeriod $period;

    protected AchievementCategory $category;

    protected AchievementLevel $level;

    protected User $operatorUser;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base categories and levels
        $this->category = AchievementCategory::create(['name' => 'Akademik', 'is_active' => true]);
        $this->level = AchievementLevel::create(['name' => 'Nasional', 'points' => 15, 'is_active' => true]);

        // Setup Users
        $this->operatorUser = User::forceCreate([
            'name' => 'Operator FE',
            'email' => 'op.fe@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Validator',
            'faculty' => 'Fakultas Ekonomi',
            'faculty_id' => '1',
        ]);
        UserRole::create([
            'user_id' => $this->operatorUser->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'is_active' => true,
        ]);

        $this->adminUser = User::forceCreate([
            'name' => 'Admin Univ',
            'email' => 'admin@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        UserRole::create([
            'user_id' => $this->adminUser->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function student_cannot_submit_after_submission_deadline(): void
    {
        // 1. Setup active period where submission deadline is in the past
        $period = AcademicPeriod::create([
            'name' => 'Genap 2023/2024',
            'code' => '20232',
            'year' => '2023',
            'semester' => 'Genap',
            'start_date' => '2024-03-01',
            'end_date' => '2024-08-31',
            'submission_deadline' => now()->subMinutes(10), // Passed!
            'validation_deadline' => now()->addDays(5),
            'is_active' => true,
        ]);

        // 2. Setup Student
        $student = Student::forceCreate([
            'student_id' => 'S123',
            'name' => 'Mahasiswa Test',
            'email' => 'mhs@test.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'gpa' => 3.5,
        ]);

        // Mock student session data
        $sessionData = [
            'student_id' => 'S123',
            'student_data' => [
                'id_mahasiswa' => 101,
                'nama' => 'Mahasiswa Test',
                'ipk' => 3.5,
            ],
        ];

        // 3. Attempt service call
        $service = app(AchievementService::class);

        $achievementTemplate = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah ditutup');

        session($sessionData);
        $service->submitAchievement(
            [
                'achievement_id' => $achievementTemplate->id,
                'category_id' => $this->category->id,
                'event_name' => 'Lomba Nasional',
                'organizer' => 'Unpatti',
                'event_date' => '2024-04-10',
                'level' => 'Nasional',
            ],
            'S123',
            UploadedFile::fake()->create('certificate.pdf', 100)
        );
    }

    #[Test]
    public function operator_can_validate_after_submission_deadline_but_before_validation_deadline(): void
    {
        // 1. Setup active period
        $period = AcademicPeriod::create([
            'name' => 'Genap 2023/2024',
            'code' => '20232',
            'year' => '2023',
            'semester' => 'Genap',
            'start_date' => '2024-03-01',
            'end_date' => '2024-08-31',
            'submission_deadline' => now()->subMinutes(10), // submission closed!
            'validation_deadline' => now()->addMinutes(10),  // validation open!
            'is_active' => true,
        ]);

        // 2. Setup Student and Achievement
        $student = Student::forceCreate([
            'student_id' => 'S123',
            'name' => 'Mahasiswa Test',
            'email' => 'mhs@test.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'gpa' => 3.5,
        ]);

        $achievementTemplate = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievementTemplate->id,
            'event_name' => 'Lomba Nasional',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => $period->id,
        ]);

        // Mock document verification checks
        AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        // 3. Approve achievement
        $service = app(FacultyValidationService::class);

        // Mock current role session
        session([
            'active_role_id' => $this->operatorUser->activeRoles()->first()->id,
            'active_role_type' => 'operator',
            'operator_level' => 'faculty',
            'operator_faculty_id' => '1',
        ]);

        $result = $service->approve($studentAchievement, $this->operatorUser, 'Approve comments', [
            'certificate_valid' => true,
            'event_date_valid' => true,
            'organizer_valid' => true,
            'level_appropriate' => true,
        ]);

        $this->assertTrue($result);
        $this->assertSame(StudentAchievement::STATUS_FACULTY_APPROVED, $studentAchievement->fresh()->validation_status);
    }

    #[Test]
    public function operator_cannot_validate_after_validation_deadline(): void
    {
        // 1. Setup active period where validation deadline is passed
        $period = AcademicPeriod::create([
            'name' => 'Genap 2023/2024',
            'code' => '20232',
            'year' => '2023',
            'semester' => 'Genap',
            'start_date' => '2024-03-01',
            'end_date' => '2024-08-31',
            'submission_deadline' => now()->subMinutes(20),
            'validation_deadline' => now()->subMinutes(10), // Passed!
            'is_active' => true,
        ]);

        // 2. Setup Student and Achievement
        $student = Student::forceCreate([
            'student_id' => 'S123',
            'name' => 'Mahasiswa Test',
            'email' => 'mhs@test.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'gpa' => 3.5,
        ]);

        $achievementTemplate = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievementTemplate->id,
            'event_name' => 'Lomba Nasional',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => $period->id,
        ]);

        AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        // 3. Try to approve - should throw Exception
        $service = app(FacultyValidationService::class);

        session([
            'active_role_id' => $this->operatorUser->activeRoles()->first()->id,
            'active_role_type' => 'operator',
            'operator_level' => 'faculty',
            'operator_faculty_id' => '1',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Batas waktu validasi');

        $service->approve($studentAchievement, $this->operatorUser, 'Approve comments', [
            'certificate_valid' => true,
            'event_date_valid' => true,
            'organizer_valid' => true,
            'level_appropriate' => true,
        ]);
    }

    #[Test]
    public function config_changes_generate_audit_logs(): void
    {
        // Act as admin
        $this->actingAs($this->adminUser);

        // 1. Period audit check
        $periodData = [
            'name' => 'Ganjil 2026/2027',
            'code' => '20261',
            'year' => '2026',
            'semester' => 'Ganjil',
            'start_date' => '2026-09-01',
            'end_date' => '2027-02-28',
            'submission_deadline' => '2026-12-31 23:59:59',
            'validation_deadline' => '2027-01-15 23:59:59',
            'is_active' => 0,
        ];

        // Store
        $response = $this->post(route('admin.periods.store'), $periodData);
        $response->assertRedirect();

        $period = AcademicPeriod::where('code', '20261')->first();
        $this->assertNotNull($period);

        $log = ConfigAuditLog::where('auditable_type', AcademicPeriod::class)
            ->where('auditable_id', $period->id)
            ->where('action', 'created')
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals($this->adminUser->name, $log->user_name);

        // Update
        $updateData = array_merge($periodData, [
            'name' => 'Ganjil 2026/2027 Updated',
            'is_active' => 1,
        ]);
        $response = $this->put(route('admin.periods.update', $period), $updateData);
        $response->assertRedirect();

        $logUpdate = ConfigAuditLog::where('auditable_type', AcademicPeriod::class)
            ->where('auditable_id', $period->id)
            ->where('action', 'updated')
            ->first();
        $this->assertNotNull($logUpdate);
        $this->assertArrayHasKey('name', $logUpdate->changed_fields);
        $this->assertEquals('Ganjil 2026/2027 Updated', $logUpdate->changed_fields['name']['new']);

        // 2. Level audit check
        $levelData = [
            'name' => 'Provinsi',
            'points' => 5,
            'description' => 'Tingkat Provinsi',
            'is_active' => 1,
        ];

        $response = $this->post(route('admin.levels.store'), $levelData);
        $response->assertRedirect();

        $level = AchievementLevel::where('name', 'Provinsi')->first();
        $this->assertNotNull($level);

        $levelLog = ConfigAuditLog::where('auditable_type', AchievementLevel::class)
            ->where('auditable_id', $level->id)
            ->where('action', 'created')
            ->first();
        $this->assertNotNull($levelLog);

        // 3. Category audit check
        $categoryData = [
            'name' => 'Olahraga',
            'description' => 'Cabang Olahraga',
            'is_active' => 1,
        ];

        $response = $this->post(route('admin.categories.store'), $categoryData);
        $response->assertRedirect();

        $category = AchievementCategory::where('name', 'Olahraga')->first();
        $this->assertNotNull($category);

        $categoryLog = ConfigAuditLog::where('auditable_type', AchievementCategory::class)
            ->where('auditable_id', $category->id)
            ->where('action', 'created')
            ->first();
        $this->assertNotNull($categoryLog);
    }

    #[Test]
    public function points_snapshot_is_captured_on_university_approval_individual_and_batch_sk(): void
    {
        $period = AcademicPeriod::create([
            'name' => 'Genap 2023/2024',
            'code' => '20232',
            'year' => '2023',
            'semester' => 'Genap',
            'start_date' => '2024-03-01',
            'end_date' => '2024-08-31',
            'is_active' => true,
        ]);

        $student = Student::forceCreate([
            'student_id' => 'S123',
            'name' => 'Mahasiswa Test',
            'email' => 'mhs@test.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'gpa' => 3.5,
        ]);

        $achievementTemplate = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievementTemplate->id,
            'event_name' => 'Lomba Nasional',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
            'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
            'academic_period_id' => $period->id,
        ]);

        // Mock document approval
        AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        // Mock checklist completion
        ValidationChecklist::create([
            'sa_id' => $studentAchievement->sa_id,
            'validator_id' => $this->operatorUser->id,
            'certificate_valid' => true,
            'event_date_valid' => true,
            'organizer_valid' => true,
            'level_appropriate' => true,
            'documents_complete' => true,
        ]);

        // 1. Individual Approval
        $service = app(UniversityValidationService::class);

        session([
            'active_role_id' => $this->adminUser->activeRoles()->first()->id,
            'active_role_type' => 'admin',
        ]);

        $result = $service->approve($studentAchievement, $this->adminUser, null, 'Individual university approval');
        $this->assertTrue($result);

        $studentAchievement->refresh();
        $this->assertSame(StudentAchievement::STATUS_UNIVERSITY_APPROVED, $studentAchievement->validation_status);

        $snapshot = $studentAchievement->points_snapshot;
        $this->assertIsArray($snapshot);
        $this->assertEquals('Nasional', $snapshot['level']);
        $this->assertEquals(15, $snapshot['points']);
        $this->assertEquals('Akademik', $snapshot['category']);
        $this->assertEquals($this->level->id, $snapshot['level_id']);
        $this->assertArrayHasKey('captured_at', $snapshot);

        // 2. Batch SK Approval
        $studentAchievement2 = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievementTemplate->id,
            'event_name' => 'Lomba Nasional 2',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
            'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
            'academic_period_id' => $period->id,
        ]);

        AchievementDocument::create([
            'sa_id' => $studentAchievement2->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        ValidationChecklist::create([
            'sa_id' => $studentAchievement2->sa_id,
            'validator_id' => $this->operatorUser->id,
            'certificate_valid' => true,
            'event_date_valid' => true,
            'organizer_valid' => true,
            'level_appropriate' => true,
            'documents_complete' => true,
        ]);

        $sk = SKDocument::create([
            'sk_number' => 'SK/2026/001',
            'title' => 'SK Batch 1',
            'upload_type' => 'link',
            'external_link' => 'http://example.com/sk.pdf',
            'issued_date' => '2026-05-01',
            'issued_by' => 'Rektor',
            'created_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);
        $response = $this->post(route('admin.sk.process-assignment', $sk), [
            'achievement_ids' => [$studentAchievement2->sa_id],
            'notes' => 'Batch assignment notes',
        ]);
        $response->assertRedirect();

        $studentAchievement2->refresh();
        $this->assertSame(StudentAchievement::STATUS_UNIVERSITY_APPROVED, $studentAchievement2->validation_status);

        $snapshot2 = $studentAchievement2->points_snapshot;
        $this->assertIsArray($snapshot2);
        $this->assertEquals('Nasional', $snapshot2['level']);
        $this->assertEquals(15, $snapshot2['points']);
        $this->assertEquals('Akademik', $snapshot2['category']);
        $this->assertEquals($this->level->id, $snapshot2['level_id']);
        $this->assertArrayHasKey('captured_at', $snapshot2);
    }
}
