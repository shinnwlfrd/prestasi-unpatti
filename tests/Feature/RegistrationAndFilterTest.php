<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\SKDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Services\SiakadApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationAndFilterTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $validator;

    protected $student;

    protected $category;

    protected $sk;

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

        $this->category = AchievementCategory::create(['name' => 'Akademik', 'is_active' => true]);
        AchievementLevel::create(['name' => 'Nasional', 'is_active' => true]);

        $this->student = Student::create([
            'student_id' => '2024001',
            'name' => 'Almira Test',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Hukum',
            'email' => 'almira@test.com',
        ]);

        $this->admin = User::forceCreate([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        $this->sk = SKDocument::create([
            'sk_number' => 'SK/2024/001',
            'title' => 'SK Test',
            'issued_date' => '2024-01-01',
            'issued_by' => 'Rektor',
            'created_by' => $this->admin->id,
        ]);
        UserRole::create([
            'user_id' => $this->admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        $this->validator = User::forceCreate([
            'name' => 'Validator Test',
            'email' => 'validator@test.com',
            'password' => Hash::make('password'),
            'role' => 'Operator',
        ]);
        UserRole::create([
            'user_id' => $this->validator->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Hukum',
            'is_active' => true,
        ]);

        Achievement::create([
            'name' => 'Template Prestasi Test',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);
    }

    private function mockSiakadStudentLookup(): string
    {
        $idMahasiswa = '11111111-1111-1111-1111-111111111111';

        $siakadData = [
            'id_mahasiswa' => $idMahasiswa,
            'nama_mahasiswa' => $this->student->name,
            'registrasi' => [
                'nim' => $this->student->student_id,
                'email_kampus' => $this->student->email,
                'ipk_kumulatif' => '3.75',
            ],
            'fakultas' => [
                'id' => $this->student->faculty_id,
                'nama' => $this->student->faculty,
            ],
            'jurusan' => [
                'id' => null,
                'nama' => null,
            ],
            'program_studi' => [
                'id' => null,
                'nama' => null,
            ],
        ];

        $this->mock(SiakadApiService::class, function ($mock) use ($idMahasiswa, $siakadData) {
            $mock->shouldReceive('getMahasiswaById')
                ->with($idMahasiswa)
                ->andReturn($siakadData);

            $mock->shouldReceive('transformToStudentData')
                ->with($siakadData)
                ->andReturn([
                    'student_id' => $this->student->student_id,
                    'id_mahasiswa' => $idMahasiswa,
                    'name' => $this->student->name,
                    'email' => $this->student->email,
                    'ipk' => '3.75',
                    'angkatan' => '2024',
                    'faculty_id' => $this->student->faculty_id,
                    'faculty' => $this->student->faculty,
                    'department_id' => null,
                    'department' => null,
                    'program_study_id' => null,
                    'program_study' => null,
                ]);
        });

        return $idMahasiswa;
    }

    #[Test]
    public function test_admin_can_register_achievement_with_approve_status()
    {
        $idMahasiswa = $this->mockSiakadStudentLookup();

        $response = $this->actingAs($this->admin)->post('/admin/submit-achievement', [
            'student_ids' => [$idMahasiswa],
            'category_id' => $this->category->id,
            'event_name' => 'Lomba Admin',
            'level' => 'Nasional',
            'organizer' => 'Puspresnas',
            'event_date' => '2024-02-01',
            'ranking' => 'Juara 1',
            'description' => 'Test Desc',
            'attachments' => [
                $idMahasiswa => [
                    'certificate' => UploadedFile::fake()->create('cert.pdf', 100),
                ],
            ],
            'submit_action' => 'approve',
            'sk_id' => $this->sk->id,
        ]);

        $response->assertStatus(302);

        $achievement = StudentAchievement::where('event_name', 'Lomba Admin')->first();
        $this->assertNotNull($achievement);
        $this->assertEquals(StudentAchievement::STATUS_UNIVERSITY_APPROVED, $achievement->validation_status);

        $this->assertDatabaseHas('sk_assignments', [
            'sk_id' => $this->sk->id,
            'sa_id' => $achievement->sa_id,
        ]);
    }

    #[Test]
    public function test_validator_can_register_achievement_with_pending_status()
    {
        $response = $this->actingAs($this->validator)
            ->withSession([
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->post('/validator/submit', [
                'student_ids' => [$this->student->student_id],
                'category_id' => $this->category->id,
                'event_name' => 'Lomba Validator',
                'level' => 'Nasional',
                'organizer' => 'Fakultas',
                'event_date' => '2024-02-01',
                'ranking' => 'Peserta',
                'attachments' => [
                    $this->student->student_id => [
                        'certificate' => UploadedFile::fake()->create('cert.pdf', 100),
                    ],
                ],
                'submit_action' => 'pending',
                'skip_sk' => 1,
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('student_achievements', [
            'event_name' => 'Lomba Validator',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
        ]);
    }

    #[Test]
    public function test_admin_list_filter_by_status()
    {
        $achievement = Achievement::first();

        // Create one approved and one pending
        StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $achievement->id,
            'event_name' => 'Approved Event',
            'level' => 'Nasional',
            'organizer' => 'Org',
            'event_date' => '2024-01-01',
            'validation_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            'academic_period_id' => 1,
        ]);

        StudentAchievement::create([
            'student_id' => $this->student->student_id,
            'achievement_id' => $achievement->id,
            'event_name' => 'Pending Event',
            'level' => 'Nasional',
            'organizer' => 'Org',
            'event_date' => '2024-01-01',
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'academic_period_id' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/student-achievements?status=approved');
        $response->assertSee('Approved Event');
        $response->assertDontSee('Pending Event');
    }
}
