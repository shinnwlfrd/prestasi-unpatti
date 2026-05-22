<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementDocument;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Models\ValidationLog;
use App\Services\Validator\FacultyValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentLockedValidationTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicPeriod $period;

    protected AchievementCategory $academicCategory;

    protected AchievementCategory $nonAcademicCategory;

    protected User $facultyValidatorUser;

    protected UserRole $validatorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = AcademicPeriod::create([
            'name' => 'Genap 2023/2024',
            'code' => '20232',
            'year' => '2023',
            'semester' => 'Genap',
            'start_date' => '2024-03-01',
            'end_date' => '2024-08-31',
            'is_active' => true,
        ]);

        $this->academicCategory = AchievementCategory::create(['name' => 'Akademik', 'is_active' => true]);
        $this->nonAcademicCategory = AchievementCategory::create(['name' => 'Seni', 'is_active' => true]);

        AchievementLevel::create(['name' => 'Universitas', 'is_active' => true]);
        AchievementLevel::create(['name' => 'Nasional', 'is_active' => true]);
        AchievementLevel::create(['name' => 'Internasional', 'is_active' => true]);

        // Setup Validator User
        $this->facultyValidatorUser = User::forceCreate([
            'name' => 'Faculty Validator',
            'email' => 'validator.faculty@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Validator',
        ]);

        $this->validatorRole = UserRole::create([
            'user_id' => $this->facultyValidatorUser->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => 1,
            'faculty_name' => 'Fakultas Teknik',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_requires_all_documents_to_be_approved_to_pass_the_document_gate(): void
    {
        $student = Student::forceCreate([
            'student_id' => '12345',
            'name' => 'Test Student',
            'email' => 'student12345@example.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Teknik',
            'gpa' => 3.5,
        ]);

        $achievement = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->academicCategory->id,
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievement->id,
            'event_name' => 'Lomba Akademik Nasional',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => $this->period->id,
        ]);

        // Case 1: No documents -> should fail
        $service = app(FacultyValidationService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tidak ada dokumen yang diupload.');
        $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan approve', []);
    }

    #[Test]
    public function it_fails_gate_if_documents_are_pending_or_rejected(): void
    {
        $student = Student::forceCreate([
            'student_id' => '12346',
            'name' => 'Test Student 2',
            'email' => 'student12346@example.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Teknik',
            'gpa' => 3.5,
        ]);

        $achievement = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->academicCategory->id,
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievement->id,
            'event_name' => 'Lomba Akademik Nasional',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => $this->period->id,
        ]);

        // Add a pending document
        $doc = AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_PENDING,
        ]);

        $service = app(FacultyValidationService::class);

        // Case 2: Has document but status is pending -> should fail
        try {
            $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan approve', []);
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('dokumen yang belum diverifikasi', $e->getMessage());
        }

        // Case 3: Document rejected -> should fail
        $doc->update(['status' => AchievementDocument::STATUS_REJECTED]);
        try {
            $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan approve', []);
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('dokumen yang ditolak', $e->getMessage());
        }
    }

    #[Test]
    public function it_requires_two_approved_document_types_for_non_academic_achievements(): void
    {
        $student = Student::forceCreate([
            'student_id' => '12347',
            'name' => 'Test Student 3',
            'email' => 'student12347@example.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Teknik',
            'gpa' => 3.5,
        ]);

        $achievement = Achievement::create([
            'name' => 'Lomba Non-Akademik',
            'category_id' => $this->nonAcademicCategory->id, // category_id = 2, i.e., non-academic
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievement->id,
            'event_name' => 'Lomba Seni',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => $this->period->id,
        ]);

        // Add 1 approved document type
        AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        $service = app(FacultyValidationService::class);

        // Case 4: Non-academic with only 1 approved document type -> should fail
        try {
            $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan approve', [
                'certificate_valid' => true,
                'event_date_valid' => true,
                'organizer_valid' => true,
                'level_appropriate' => true,
                'documents_complete' => true,
            ]);
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('minimal 2 jenis dokumen', $e->getMessage());
        }

        // Add second approved document type
        AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Surat Tugas',
            'file_path' => 'documents/task.pdf',
            'file_name' => 'task.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        // Now it should pass the document gate and checklist validation
        $result = $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan approve', [
            'certificate_valid' => true,
            'event_date_valid' => true,
            'organizer_valid' => true,
            'level_appropriate' => true,
            'documents_complete' => true,
        ]);

        $this->assertTrue($result);
        $this->assertEquals(StudentAchievement::STATUS_FACULTY_APPROVED, $studentAchievement->fresh()->validation_status);
    }

    #[Test]
    public function it_enforces_checklist_rules_based_on_level_and_category(): void
    {
        $student = Student::forceCreate([
            'student_id' => '12348',
            'name' => 'Test Student 4',
            'email' => 'student12348@example.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Teknik',
            'gpa' => 3.5,
        ]);

        $achievement = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->academicCategory->id,
            'is_active' => true,
        ]);

        // Case 5: Level Nasional (High level, requires organizer and level as well)
        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievement->id,
            'event_name' => 'Lomba Nasional',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Nasional',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => $this->period->id,
        ]);

        AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        $service = app(FacultyValidationService::class);

        // Fails if only certificate and event_date are provided (omitting organizer and level)
        try {
            $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan approve', [
                'certificate_valid' => true,
                'event_date_valid' => true,
            ]);
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Checklist validasi belum lengkap', $e->getMessage());
        }

        // Passes with all required
        $result = $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan approve', [
            'certificate_valid' => true,
            'event_date_valid' => true,
            'organizer_valid' => true,
            'level_appropriate' => true,
        ]);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_creates_detailed_validation_log_with_audit_metadata(): void
    {
        $student = Student::forceCreate([
            'student_id' => '12349',
            'name' => 'Test Student 5',
            'email' => 'student12349@example.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Teknik',
            'gpa' => 3.5,
        ]);

        $achievement = Achievement::create([
            'name' => 'Lomba Akademik',
            'category_id' => $this->academicCategory->id,
            'is_active' => true,
        ]);

        $studentAchievement = StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $achievement->id,
            'event_name' => 'Lomba Universitas',
            'organizer' => 'Unpatti',
            'event_date' => '2024-04-10',
            'level' => 'Universitas',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => $this->period->id,
        ]);

        AchievementDocument::create([
            'sa_id' => $studentAchievement->sa_id,
            'document_type' => 'Sertifikat',
            'file_path' => 'documents/cert.pdf',
            'file_name' => 'cert.pdf',
            'status' => AchievementDocument::STATUS_APPROVED,
        ]);

        $service = app(FacultyValidationService::class);
        $service->approve($studentAchievement, $this->facultyValidatorUser, 'Catatan khusus audit', [
            'certificate_valid' => true,
            'event_date_valid' => true,
        ]);

        // Verify ValidationLog
        $log = ValidationLog::where('sa_id', $studentAchievement->sa_id)
            ->where('new_status', StudentAchievement::STATUS_FACULTY_APPROVED)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Catatan khusus audit', $log->notes);

        $metadata = $log->metadata;
        $this->assertIsArray($metadata);
        $this->assertEquals($this->facultyValidatorUser->id, $metadata['actor_id']);
        $this->assertEquals('faculty', $metadata['scope']['level']);
        $this->assertEquals(1, $metadata['scope']['faculty_id']);
    }
}
