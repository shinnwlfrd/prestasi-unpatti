<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementDocument;
use App\Models\AchievementLevel;
use App\Models\DocumentRevision;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Services\DocumentUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    private StudentAchievement $studentAchievement;

    private AchievementDocument $document;

    private User $operator;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicPeriod::create([
            'name' => '2025/2026 Genap',
            'code' => '2025-2026-GENAP',
            'semester' => 'Genap',
            'year' => '2025/2026',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'is_active' => true,
        ]);

        $category = AchievementCategory::create([
            'name' => 'Akademik',
            'is_active' => true,
        ]);

        AchievementLevel::create([
            'name' => 'Nasional',
            'is_active' => true,
        ]);

        $achievement = Achievement::create([
            'name' => 'Lomba Debat',
            'category_id' => $category->id,
            'point' => 100,
            'is_active' => true,
        ]);

        $this->student = Student::create([
            'student_id' => '20240001',
            'name' => 'Mahasiswa Dokumen',
            'faculty' => 'Fakultas Teknik',
            'faculty_id' => 'FT',
            'email' => 'mahasiswa-dokumen@unpatti.ac.id',
        ]);

        $this->studentAchievement = StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $achievement->id,
            'academic_period_id' => AcademicPeriod::query()->value('id'),
            'event_name' => 'Lomba Debat Nasional',
            'level' => 'Nasional',
            'organizer' => 'Kemendikbud',
            'event_date' => now()->subDays(7),
            'description' => 'Pengujian dokumen',
            'ranking' => 'Juara 1',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'submitted_at' => now()->subDays(2),
        ]);

        Storage::disk('public')->put(
            'achievements/'.$this->studentAchievement->sa_id.'/certificate.pdf',
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF"
        );

        $this->document = AchievementDocument::create([
            'sa_id' => $this->studentAchievement->sa_id,
            'document_type' => AchievementDocument::TYPE_SERTIFIKAT,
            'file_path' => 'achievements/'.$this->studentAchievement->sa_id.'/certificate.pdf',
            'file_name' => 'certificate.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 128,
            'status' => AchievementDocument::STATUS_PENDING,
        ]);

        $this->operator = User::create([
            'name' => 'Operator FT',
            'email' => 'operator-ft@unpatti.ac.id',
            'password' => bcrypt('password'),
            'role' => 'Operator',
            'faculty' => 'Fakultas Teknik',
            'faculty_id' => 'FT',
            'is_active' => true,
        ]);

        UserRole::create([
            'user_id' => $this->operator->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => 'FT',
            'faculty_name' => 'Fakultas Teknik',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Dokumen',
            'email' => 'admin-dokumen@unpatti.ac.id',
            'password' => bcrypt('password'),
            'role' => 'Admin',
            'is_active' => true,
        ]);

        UserRole::create([
            'user_id' => $this->admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->deleteDirectory('achievements');

        parent::tearDown();
    }

    #[Test]
    public function student_session_can_preview_own_document_without_laravel_auth(): void
    {
        $response = $this->withSession([
            'auth_role' => 'student',
            'student_id' => $this->student->student_id,
        ])->get(route('achievements.documents.preview', $this->document));

        $response->assertOk();
    }

    #[Test]
    public function operator_can_verify_document_using_validator_route_alias(): void
    {
        $response = $this->actingAs($this->operator)
            ->withSession([
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => 'FT',
            ])
            ->withHeader('Accept', 'application/json')
            ->post('/validator/documents/'.$this->document->id.'/verify', [
                'action' => 'approve',
            ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'status_label' => 'Disetujui',
        ]);

        $this->assertDatabaseHas('achievement_documents', [
            'id' => $this->document->id,
            'status' => AchievementDocument::STATUS_APPROVED,
            'verified_by' => $this->operator->id,
        ]);
    }

    #[Test]
    public function admin_can_revert_document_and_add_note(): void
    {
        $this->document->update([
            'status' => AchievementDocument::STATUS_APPROVED,
            'verified_by' => $this->operator->id,
            'verified_at' => now(),
        ]);

        $revertResponse = $this->actingAs($this->admin)
            ->withSession(['active_role_type' => 'admin'])
            ->post(route('achievements.documents.revert', $this->document), [
                'reason' => 'Audit ulang',
            ]);

        $revertResponse->assertOk()->assertJson([
            'success' => true,
            'status_label' => 'Menunggu Verifikasi',
        ]);

        $noteResponse = $this->actingAs($this->admin)
            ->withSession(['active_role_type' => 'admin'])
            ->post(route('achievements.documents.addNote', $this->document), [
                'note' => 'Perlu cek ulang sebelum final approval.',
            ]);

        $noteResponse->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('achievement_documents', [
            'id' => $this->document->id,
            'status' => AchievementDocument::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('document_revisions', [
            'document_id' => $this->document->id,
            'action' => DocumentRevision::ACTION_NOTE_ADDED,
            'performed_by' => $this->admin->id,
        ]);
    }

    #[Test]
    public function upload_service_rejects_fake_pdf_with_invalid_magic_bytes(): void
    {
        $service = app(DocumentUploadService::class);
        $file = UploadedFile::fake()->create('fake.pdf', 10, 'application/pdf');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Isi file tidak sesuai dengan format yang diizinkan.');

        $service->uploadDocument(
            $this->studentAchievement,
            $file,
            AchievementDocument::TYPE_SERTIFIKAT
        );
    }
}
