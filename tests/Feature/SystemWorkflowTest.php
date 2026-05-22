<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementExport;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SystemWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup initial basic data
        AcademicPeriod::create([
            'name' => 'Ganjil 2023/2024',
            'code' => '20231',
            'year' => '2023',
            'semester' => 'Ganjil',
            'start_date' => '2023-09-01',
            'end_date' => '2024-02-28',
            'is_active' => true,
        ]);

        $category = AchievementCategory::create(['name' => 'Akademik']);
        AchievementLevel::create(['name' => 'Nasional', 'is_active' => true]);
        Achievement::create([
            'name' => 'Lomba Karya Tulis Ilmiah',
            'category_id' => $category->id,
            'description' => 'Test',
        ]);
    }

    #[Test]
    public function test_rektor_login_and_dashboard(): void
    {
        // Rektor is university level pimpinan
        $user = User::forceCreate([
            'name' => 'Rektor Unpatti',
            'email' => 'rektor@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);

        $role = UserRole::create([
            'user_id' => $user->id,
            'role' => 'pimpinan',
            'level' => 'university',
            'position' => 'rektor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'pimpinan',
                'pimpinan_level' => 'university',
                'pimpinan_position' => 'rektor',
            ])
            ->get('/');

        $response->assertRedirect(route('pimpinan.dashboard'));

        // Follow redirect and check dashboard
        $response = $this->actingAs($user)->get('/pimpinan');
        $response->assertStatus(200);
        $response->assertSee('Rektor Unpatti');
        $response->assertSee('Universitas Pattimura');
    }

    #[Test]
    public function test_multi_role_user_switching(): void
    {
        $user = User::forceCreate([
            'name' => 'Multi Role User',
            'email' => 'multi.role@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator', // Default legacy role column
        ]);

        $role1 = UserRole::create([
            'user_id' => $user->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'is_active' => true,
        ]);

        $role2 = UserRole::create([
            'user_id' => $user->id,
            'role' => 'pimpinan',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'position' => 'dekan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/switch-role');

        $response->assertStatus(200);

        // Switch to operator
        $this->actingAs($user);
        $response = $this->post('/api/switch-role', ['role_id' => (string) $role1->id]);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['redirect_url' => route('validator.pending.index')]);
        $user->refresh();
        $this->assertSame('Operator', $user->role);

        // Switch to pimpinan
        $response = $this->post('/api/switch-role', ['role_id' => (string) $role2->id]);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['redirect_url' => route('pimpinan.dashboard')]);
        $user->refresh();
        $this->assertSame('Operator', $user->role);
    }

    #[Test]
    public function test_faculty_data_filtering(): void
    {
        // Setup students and achievements for two faculties
        Student::create([
            'student_id' => 'FT001',
            'name' => 'Student Teknik',
            'faculty_id' => '2', // FT
            'faculty' => 'Fakultas Teknik',
            'email' => 'ft@example.com',
        ]);

        Student::create([
            'student_id' => 'FE001',
            'name' => 'Student Ekonomi',
            'faculty_id' => '1', // FE
            'faculty' => 'Fakultas Ekonomi',
            'email' => 'fe@example.com',
        ]);

        // Operator FE
        $userFE = User::forceCreate([
            'name' => 'Operator FE',
            'email' => 'operator.fe@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator',
        ]);
        $role = UserRole::create([
            'user_id' => $userFE->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'is_active' => true,
        ]);

        $response = $this->actingAs($userFE)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->get('/validator/students');

        $response->assertStatus(200);
        $response->assertSee('Student Ekonomi');
        $response->assertDontSee('Student Teknik');
    }

    #[Test]
    public function test_pimpinan_read_only_enforcement(): void
    {
        $user = User::forceCreate([
            'name' => 'Dekan FT',
            'email' => 'dekan.ft@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Pimpinan',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'role' => 'pimpinan',
            'level' => 'faculty',
            'faculty_id' => '2',
            'position' => 'dekan',
            'is_active' => true,
        ]);

        Student::create([
            'student_id' => 'FT002',
            'name' => 'Student Teknik 2',
            'faculty_id' => '2',
            'email' => 'ft2@example.com',
        ]);

        $achievement = StudentAchievement::create([
            'student_id' => 'FT002',
            'achievement_id' => 1,
            'event_name' => 'Test Event',
            'level' => 'Nasional',
            'organizer' => 'Test Org',
            'event_date' => '2023-10-10',
            'validation_status' => 'submitted',
            'academic_period_id' => 1,
        ]);

        $response = $this->actingAs($user)->post("/validator/pending/{$achievement->sa_id}/validate", [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function test_batch_submission_by_operator(): void
    {
        $user = User::forceCreate([
            'name' => 'Operator FE',
            'email' => 'operator.fe.batch@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'is_active' => true,
        ]);

        Student::create(['student_id' => 'S1', 'name' => 'Student 1', 'faculty_id' => '1', 'email' => 's1@example.com']);
        Student::create(['student_id' => 'S2', 'name' => 'Student 2', 'faculty_id' => '1', 'email' => 's2@example.com']);

        $response = $this->actingAs($user)
            ->withSession([
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->post('/validator/submit', [
                'student_ids' => ['S1', 'S2'],
                'category_id' => 1,
                'event_name' => 'Batch Achievement',
                'level' => 'Nasional',
                'organizer' => 'Puspresnas',
                'event_date' => '2023-11-11',
                'ranking' => 'Juara 1',
                'description' => 'Test Desc',
                'attachments' => [
                    'S1' => [
                        'certificate' => UploadedFile::fake()->create('cert-s1.pdf', 100),
                    ],
                    'S2' => [
                        'certificate' => UploadedFile::fake()->create('cert-s2.pdf', 100),
                    ],
                ],
                'submit_action' => 'pending',
                'skip_sk' => 1,
                'sk_waiver_reason' => 'tingkat_universitas',
            ]);

        $response->assertRedirect();
        // Check if achievements were created
        $this->assertEquals(2, StudentAchievement::where('event_name', 'Batch Achievement')->count());
    }

    #[Test]
    public function test_operator_student_search_is_scoped_to_active_role(): void
    {
        $user = User::forceCreate([
            'name' => 'Scoped Operator',
            'email' => 'scoped.operator@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator',
        ]);
        $role = UserRole::create([
            'user_id' => $user->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'is_active' => true,
        ]);

        Student::create(['student_id' => 'SCOPE1', 'name' => 'Student Scope One', 'faculty_id' => '1', 'email' => 'scope1@example.com']);
        Student::create(['student_id' => 'SCOPE2', 'name' => 'Student Scope Two', 'faculty_id' => '2', 'email' => 'scope2@example.com']);

        $response = $this->actingAs($user)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->get('/api/sigap/students/search?q=Student%20Scope');

        $response->assertOk();
        $response->assertJsonPath('data.0.student_id', 'SCOPE1');
        $response->assertJsonMissing(['student_id' => 'SCOPE2']);
    }

    #[Test]
    public function test_operator_cannot_submit_for_student_outside_scope(): void
    {
        $user = User::forceCreate([
            'name' => 'Scoped Submit Operator',
            'email' => 'scoped.submit@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator',
        ]);
        $role = UserRole::create([
            'user_id' => $user->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'is_active' => true,
        ]);

        Student::create(['student_id' => 'OUT2', 'name' => 'Out Scope Student', 'faculty_id' => '2', 'email' => 'out2@example.com']);

        $response = $this->actingAs($user)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->post('/validator/submit', [
                'student_ids' => ['OUT2'],
                'category_id' => 1,
                'event_name' => 'Out Scope Achievement',
                'level' => 'Nasional',
                'organizer' => 'Puspresnas',
                'event_date' => '2023-11-11',
                'ranking' => 'Juara 1',
                'description' => 'Test Desc',
                'attachments' => [
                    'OUT2' => [
                        'certificate' => UploadedFile::fake()->create('cert-out.pdf', 100),
                    ],
                ],
                'submit_action' => 'pending',
                'skip_sk' => 1,
                'sk_waiver_reason' => 'tingkat_universitas',
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('student_achievements', [
            'student_id' => 'OUT2',
            'event_name' => 'Out Scope Achievement',
        ]);
    }

    #[Test]
    public function test_export_achievements_csv(): void
    {
        config()->set('queue.default', 'sync');

        $user = User::forceCreate([
            'name' => 'Admin Export',
            'email' => 'admin.export@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        $role = UserRole::create([
            'user_id' => $user->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        Student::create(['student_id' => 'S3', 'name' => 'Student 3', 'faculty_id' => '1', 'email' => 's3@example.com']);

        StudentAchievement::create([
            'student_id' => 'S3',
            'achievement_id' => 1,
            'event_name' => 'Export Test Event',
            'level' => 'Nasional',
            'organizer' => 'Test Org',
            'event_date' => '2023-12-12',
            'validation_status' => 'university_approved',
            'academic_period_id' => 1,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->getJson('/api/export/achievements?format=csv');

        $response->assertStatus(202)
            ->assertJsonPath('export.status', AchievementExport::STATUS_COMPLETED)
            ->assertJsonPath('export.format', 'CSV');

        $export = AchievementExport::firstOrFail();

        $download = $this->actingAs($user)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'admin',
            ])
            ->get(route('api.export.download', $export));

        $download->assertOk();
        $content = $download->streamedContent();

        $this->assertStringContainsString('Export Test Event', $content);
    }
}
