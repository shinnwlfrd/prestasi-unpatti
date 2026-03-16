<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\AcademicPeriod;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class ApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicPeriod::create([
            'name' => 'Ganjil 2023/2024',
            'code' => '20231',
            'year' => '2023',
            'semester' => 'Ganjil',
            'start_date' => '2023-09-01',
            'end_date' => '2024-02-28',
            'is_active' => true,
        ]);

        AchievementCategory::create(['name' => 'Akademik', 'is_active' => true]);
        $cat = AchievementCategory::create(['name' => 'Non-Akademik', 'is_active' => true]);
        Achievement::create([
            'name' => 'Template Prestasi',
            'category_id' => $cat->id,
            'description' => 'Test',
            'is_active' => true,
        ]);

        AchievementLevel::create(['name' => 'Universitas', 'is_active' => true]);
        AchievementLevel::create(['name' => 'Nasional', 'is_active' => true]);
        AchievementLevel::create(['name' => 'Internasional', 'is_active' => true]);
    }

    #[Test]
    public function admin_login_redirects_to_admin_dashboard(): void
    {
        $user = User::forceCreate([
            'name' => 'Admin Test',
            'email' => 'admin@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@unpatti.ac.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
    }

    #[Test]
    public function admin_categories_index_requires_authentication(): void
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_view_and_create_category(): void
    {
        $user = User::forceCreate([
            'name' => 'Admin Cat',
            'email' => 'admin.cat@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Kategori Prestasi');

        $response = $this->actingAs($user)->post(route('admin.categories.store'), [
            'name' => 'Kategori Baru',
            'description' => 'Deskripsi kategori',
        ]);
        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('achievement_categories', ['name' => 'Kategori Baru']);
    }

    #[Test]
    public function operator_can_approve_achievement_at_faculty_level(): void
    {
        $user = User::forceCreate([
            'name' => 'Operator FE',
            'email' => 'op.fe@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Validator',
            'faculty' => 'Fakultas Ekonomi',
            'faculty_id' => '1',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'is_active' => true,
        ]);

        Student::create([
            'student_id' => 'SFE01',
            'name' => 'Mahasiswa FE',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'email' => 'mfe@test.com',
        ]);

        $achievement = StudentAchievement::create([
            'student_id' => 'SFE01',
            'achievement_id' => 1,
            'event_name' => 'Prestasi Test',
            'level' => 'Nasional',
            'organizer' => 'Org',
            'event_date' => '2023-10-01',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'validation_stage' => StudentAchievement::STAGE_FACULTY,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'academic_period_id' => 1,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'active_role_id' => $user->activeRoles()->first()->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->post(route('validator.pending.validate', $achievement), [
                'action' => 'approve',
                'notes' => 'Disetujui',
            ]);

        $response->assertRedirect(route('validator.pending.index'));
        $achievement->refresh();
        $this->assertSame(StudentAchievement::STATUS_FACULTY_APPROVED, $achievement->validation_status);
    }

    #[Test]
    public function admin_can_reject_achievement_at_university_level(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin Univ',
            'email' => 'admin.univ@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);
        UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        Student::create([
            'student_id' => 'SUNIV01',
            'name' => 'Mahasiswa',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
            'email' => 'suniv@test.com',
        ]);

        $achievement = StudentAchievement::create([
            'student_id' => 'SUNIV01',
            'achievement_id' => 1,
            'event_name' => 'Prestasi Univ',
            'level' => 'Nasional',
            'organizer' => 'Org',
            'event_date' => '2023-10-01',
            'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
            'validation_stage' => StudentAchievement::STAGE_UNIVERSITY,
            'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
            'faculty_validator_id' => 1,
            'faculty_validated_at' => now(),
            'academic_period_id' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $admin->activeRoles()->first()->id,
                'active_role_type' => 'admin',
            ])
            ->post(route('admin.university.validate', $achievement), [
                'action' => 'reject',
                'rejection_reason' => 'Alasan penolakan universitas',
            ]);

        $response->assertRedirect();
        $achievement->refresh();
        $this->assertSame(StudentAchievement::STATUS_UNIVERSITY_REJECTED, $achievement->validation_status);
    }

    #[Test]
    public function student_submit_page_requires_student_session(): void
    {
        $response = $this->get(route('student.achievement.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function root_redirects_to_login_when_guest(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function submit_achievement_requires_category_id(): void
    {
        $user = User::forceCreate([
            'name' => 'Operator',
            'email' => 'op.val@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Validator',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'is_active' => true,
        ]);
        Student::create(['student_id' => 'ST1', 'name' => 'S1', 'faculty_id' => '1', 'email' => 'st1@test.com']);

        $response = $this->actingAs($user)
            ->withSession(['operator_level' => 'faculty', 'operator_faculty_id' => '1'])
            ->post(route('validator.submit.store'), [
                'student_ids' => ['ST1'],
                'event_name' => 'Event',
                'level' => 'Nasional',
                'organizer' => 'Org',
                'event_date' => '2023-11-01',
                'certificate' => UploadedFile::fake()->create('cert.pdf', 100),
                'submit_action' => 'pending',
                'skip_sk' => 1,
                'sk_waiver_reason' => 'tingkat_universitas',
            ]);

        $response->assertSessionHasErrors('category_id');
    }
}
