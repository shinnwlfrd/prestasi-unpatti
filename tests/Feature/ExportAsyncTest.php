<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementExport;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportAsyncTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicPeriod $period;

    protected Achievement $achievementTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('queue.default', 'sync');

        $category = AchievementCategory::create([
            'name' => 'Akademik',
            'is_active' => true,
        ]);

        $this->period = AcademicPeriod::create([
            'name' => 'Genap 2025/2026',
            'code' => '20262',
            'year' => '2026',
            'semester' => 'Genap',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'submission_deadline' => now()->addDays(10),
            'validation_deadline' => now()->addDays(20),
            'is_active' => true,
        ]);

        $this->achievementTemplate = Achievement::create([
            'name' => 'Lomba Debat',
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function admin_export_is_generated_as_background_xlsx_and_can_be_downloaded(): void
    {
        $student = Student::forceCreate([
            'student_id' => 'A001',
            'name' => 'Andi Admin',
            'email' => 'andi@example.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'department_id' => '10',
            'department' => 'Manajemen',
            'program_study_id' => '100',
            'program_study' => 'S1 Manajemen',
            'angkatan' => '2022',
        ]);

        StudentAchievement::create([
            'student_id' => $student->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Debat Nasional',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            'submitted_by' => 'admin',
            'submitted_at' => now(),
        ]);

        $admin = User::forceCreate([
            'name' => 'Admin Univ',
            'email' => 'admin.export@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        $role = UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->getJson(route('admin.export.achievements', [
                'period' => $this->period->id,
                'format' => 'excel',
            ]));

        $response->assertStatus(202)
            ->assertJsonPath('export.status', AchievementExport::STATUS_COMPLETED)
            ->assertJsonPath('export.format', 'XLSX');

        $export = AchievementExport::firstOrFail();
        $this->assertEquals(1, $export->row_count);
        $this->assertNotNull($export->file_path);

        $download = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->get(route('admin.export.download', $export));

        $download->assertOk();
        $this->assertStringContainsString('.xlsx', $download->headers->get('content-disposition'));
    }

    #[Test]
    public function validator_export_stays_scoped_to_its_faculty_and_supports_csv_download(): void
    {
        $studentFacultyOne = Student::forceCreate([
            'student_id' => 'F001',
            'name' => 'Fakultas Satu',
            'email' => 'f1@example.com',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
        ]);

        $studentFacultyTwo = Student::forceCreate([
            'student_id' => 'F002',
            'name' => 'Fakultas Dua',
            'email' => 'f2@example.com',
            'faculty_id' => '2',
            'faculty' => 'Fakultas Teknik',
        ]);

        StudentAchievement::create([
            'student_id' => $studentFacultyOne->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Prestasi FE',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            'submitted_by' => 'validator',
            'submitted_at' => now(),
        ]);

        StudentAchievement::create([
            'student_id' => $studentFacultyTwo->student_id,
            'achievement_id' => $this->achievementTemplate->id,
            'academic_period_id' => $this->period->id,
            'event_name' => 'Prestasi FT',
            'organizer' => 'Unpatti',
            'level' => 'Nasional',
            'event_date' => now(),
            'validation_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            'submitted_by' => 'validator',
            'submitted_at' => now(),
        ]);

        $operator = User::forceCreate([
            'name' => 'Operator FE',
            'email' => 'operator.export@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
        ]);

        $role = UserRole::create([
            'user_id' => $operator->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'is_active' => true,
        ]);

        $response = $this->actingAs($operator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->getJson(route('api.export.achievements', [
                'format' => 'csv',
                'period_id' => $this->period->id,
            ]));

        $response->assertStatus(202)
            ->assertJsonPath('export.status', AchievementExport::STATUS_COMPLETED)
            ->assertJsonPath('export.format', 'CSV');

        $export = AchievementExport::firstOrFail();
        $this->assertEquals(1, $export->row_count);

        $recent = $this->actingAs($operator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->getJson(route('api.export.recent'));

        $recent->assertOk()->assertJsonCount(1, 'data');

        $download = $this->actingAs($operator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->get(route('api.export.download', $export));

        $download->assertOk();
        $content = $download->streamedContent();
        $this->assertStringContainsString('Fakultas Satu', $content);
        $this->assertStringNotContainsString('Fakultas Dua', $content);
    }
}
