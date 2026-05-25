<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\UserRole;
use App\Services\SiakadApiService;
use App\Services\SsoAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SSOUserWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Academic Period
        AcademicPeriod::create([
            'name' => '2023/2024 Ganjil',
            'code' => '20231',
            'year' => 2023,
            'semester' => 'Ganjil',
            'start_date' => '2023-09-01',
            'end_date' => '2027-02-28',
            'is_active' => true,
        ]);

        // 2. Seed Master Data
        $cat1 = AchievementCategory::create(['name' => 'Akademik', 'is_active' => true]);
        $cat2 = AchievementCategory::create(['name' => 'Olahraga', 'is_active' => true]);

        AchievementLevel::create(['name' => 'Universitas', 'is_active' => true]);
        AchievementLevel::create(['name' => 'Nasional', 'is_active' => true]);
        AchievementLevel::create(['name' => 'Internasional', 'is_active' => true]);

        Achievement::create([
            'category_id' => $cat1->id,
            'name' => 'Template Akademik',
            'is_active' => true,
        ]);
        Achievement::create([
            'category_id' => $cat2->id,
            'name' => 'Template Olahraga',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function test_sso_student_login_and_crud_workflow(): void
    {
        // 1. Mock SSO and SIAKAD services
        $mockSiakadService = $this->mock(SiakadApiService::class);
        $mockSiakadService->shouldReceive('getMahasiswaByEmail')
            ->andReturn([
                'id_mahasiswa' => 'mahasiswa-uuid-12345',
                'nim' => '202351031',
                'nama' => 'SSO Student Test',
                'email' => '202351031@student.unpatti.ac.id',
                'foto_url' => 'https://example.com/photo.jpg',
                'registrasi' => [
                    'ipk_kumulatif' => 3.85,
                    'angkatan' => 2023,
                ],
                'fakultas' => [
                    'id' => '1',
                    'nama' => 'Fakultas Teknik',
                ],
                'jurusan' => [
                    'id' => '10',
                    'nama' => 'Teknik Elektro',
                ],
                'program_studi' => [
                    'id' => '100',
                    'nama' => 'Teknik Elektro',
                ],
            ]);
        $mockSiakadService->shouldReceive('getMahasiswaByNim')
            ->andReturn([
                'id_mahasiswa' => 'mahasiswa-uuid-12345',
                'nim' => '202351031',
                'nama' => 'SSO Student Test',
                'email' => '202351031@student.unpatti.ac.id',
                'foto_url' => 'https://example.com/photo.jpg',
                'registrasi' => [
                    'ipk_kumulatif' => 3.85,
                    'angkatan' => 2023,
                ],
                'fakultas' => [
                    'id' => '1',
                    'nama' => 'Fakultas Teknik',
                ],
                'jurusan' => [
                    'id' => '10',
                    'nama' => 'Teknik Elektro',
                ],
                'program_studi' => [
                    'id' => '100',
                    'nama' => 'Teknik Elektro',
                ],
            ]);
        $mockSiakadService->shouldReceive('transformToStudentData')
            ->andReturnUsing(function ($data) {
                return [
                    'id_mahasiswa' => $data['id_mahasiswa'],
                    'name' => $data['nama'],
                    'email' => $data['email'],
                    'foto_url' => $data['foto_url'],
                    'ipk' => $data['registrasi']['ipk_kumulatif'],
                    'angkatan' => $data['registrasi']['angkatan'],
                    'faculty_id' => $data['fakultas']['id'],
                    'faculty' => $data['fakultas']['nama'],
                    'department_id' => $data['jurusan']['id'],
                    'department' => $data['jurusan']['nama'],
                    'program_study_id' => $data['program_studi']['id'],
                    'program_study' => $data['program_studi']['nama'],
                ];
            });

        $mockSsoService = $this->mock(SsoAuthService::class);
        $mockSsoService->shouldReceive('exchangeCodeForToken')
            ->with('mock-code')
            ->andReturn('fake-access-token');

        $mockSsoService->shouldReceive('getUserInfo')
            ->with('fake-access-token')
            ->andReturn([
                'email' => '202351031@student.unpatti.ac.id',
                'name' => 'SSO Student Test',
                'id' => 'sso-12345',
            ]);

        $mockSsoService->shouldReceive('getAuthorizationUrl')
            ->andReturn('https://sso.unpatti.ac.id/oauth/authorize?state=test-state');

        // Capture handleStudentLogin logic from SSOAuthService to mock it correctly
        $mockSsoService->shouldReceive('handleStudentLogin')
            ->andReturnUsing(function ($userInfo, $accessToken) {
                $email = strtolower($userInfo['email']);
                $name = $userInfo['name'];
                $nim = '202351031';

                // Fetch SIAKAD Profile (stubbed)
                $siakadData = [
                    'success' => true,
                    'profile' => [
                        'foto_url' => 'https://example.com/photo.jpg',
                        'ipk' => '3.85',
                        'fakultas' => 'Fakultas Teknik',
                        'fakultas_id' => '1',
                        'jurusan' => 'Teknik Elektro',
                        'jurusan_id' => '10',
                        'program_studi' => 'Teknik Elektro',
                        'program_studi_id' => '100',
                        'angkatan' => '2023',
                    ],
                ];

                // Create User if not exists
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'role' => 'Student',
                        'is_active' => true,
                    ]);
                }

                // Prepare Session Data
                $studentProfile = array_merge([
                    'nim' => $nim,
                    'nama' => $name,
                    'email' => $email,
                ], $siakadData['profile']);

                session([
                    'student_data' => $studentProfile,
                    'student_profile' => $studentProfile,
                    'auth_role' => 'student',
                    'student_id' => $nim,
                    'student_name' => $name,
                    'student_email' => $email,
                    'sso_access_token' => $accessToken,
                    'sso_authenticated' => true,
                ]);

                // Create Student record in students table if not exists
                $student = Student::where('student_id', $nim)->first();
                if (! $student) {
                    Student::create([
                        'student_id' => $nim,
                        'name' => $name,
                        'email' => $email,
                        'faculty' => 'Fakultas Teknik',
                        'faculty_id' => '1',
                        'department' => 'Teknik Elektro',
                        'department_id' => '10',
                        'program_study' => 'Teknik Elektro',
                        'program_study_id' => '100',
                        'angkatan' => '2023',
                        'gpa' => 3.85,
                        'foto_url' => 'https://example.com/photo.jpg',
                    ]);
                }

                return [
                    'success' => true,
                    'redirect' => 'student.dashboard',
                    'has_achievements' => false,
                    'siakad_success' => true,
                ];
            });

        // 2. Perform SSO redirect test
        $response = $this->withSession(['state' => 'test-state'])
            ->get(route('sso.redirect'));
        $response->assertRedirect('https://sso.unpatti.ac.id/oauth/authorize?state=test-state');

        // 3. Perform SSO callback test
        $response = $this->withSession(['state' => 'test-state'])
            ->get(route('sso.callback', ['code' => 'mock-code', 'state' => 'test-state']));

        $response->assertRedirect(route('student.dashboard'));
        $this->assertEquals('student', session('auth_role'));
        $this->assertEquals('202351031', session('student_id'));

        // 4. Access student dashboard
        $response = $this->get(route('student.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('SSO Student Test');
        $response->assertSee('202351031');

        // 5. Submit achievement (Create)
        $cat1 = AchievementCategory::where('name', 'Akademik')->first();
        $response = $this->post(route('student.achievement.store'), [
            'category_id' => $cat1->id,
            'event_name' => 'Lomba Karya Tulis Ilmiah Nasional',
            'level' => 'Nasional',
            'organizer' => 'Puspresnas',
            'event_date' => '2023-11-12',
            'ranking' => 'Juara 1',
            'description' => 'Lomba menulis nasional',
            'certificate' => UploadedFile::fake()->create('sertifikat.pdf', 500, 'application/pdf'),
            'additional_documents' => [
                UploadedFile::fake()->create('dokumentasi.jpg', 500, 'image/jpeg'),
            ],
        ]);

        if (session('errors')) {
            dump(session('errors')->getMessages());
        }
        if (session('error')) {
            dump('Error message: '.session('error'));
        }

        $response->assertRedirect(route('student.dashboard'));
        $this->assertDatabaseHas('student_achievements', [
            'student_id' => '202351031',
            'event_name' => 'Lomba Karya Tulis Ilmiah Nasional',
            'validation_status' => 'submitted',
        ]);

        $achievement = StudentAchievement::where('event_name', 'Lomba Karya Tulis Ilmiah Nasional')->firstOrFail();

        // 6. Access student dashboard again to see the achievement (Read)
        $response = $this->get(route('student.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Lomba Karya Tulis Ilmiah Nasional');
        $response->assertSee('Menunggu Verifikasi');

        // 7. Test operator/validator validation (Update status to revision)
        $validator = User::forceCreate([
            'name' => 'Validator Teknik',
            'email' => 'val.teknik@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator',
        ]);

        $role = UserRole::create([
            'user_id' => $validator->id,
            'role' => 'operator',
            'level' => 'faculty',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Teknik',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        // Submit validation as operator (needs revision)
        $response = $this->actingAs($validator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->post(route('validator.pending.validate', $achievement), [
                'action' => 'request_revision',
                'revision_reason' => 'Tolong upload foto dokumentasi tambahan',
                'checklist' => [
                    'certificate_valid' => true,
                    'event_date_valid' => true,
                    'organizer_valid' => true,
                    'level_appropriate' => true,
                    'documents_complete' => false,
                ],
            ]);

        if ($response->status() !== 302) {
            dump($response->status());
            dump($response->getContent());
        }

        $response->assertRedirect(route('validator.pending.index'));
        $achievement->refresh();
        $this->assertEquals('faculty_revision', $achievement->validation_status);

        // 8. Log back in as student (session reset in actingAs)
        // We set session again
        $this->withSession([
            'auth_role' => 'student',
            'student_id' => '202351031',
            'student_name' => 'SSO Student Test',
        ]);

        // Student submits review request after uploading (or simulates resubmission)
        $response = $this->post(route('student.achievement.request-review', $achievement), [
            'reason' => 'Sudah dilengkapi',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $achievement->refresh();
        $this->assertEquals('submitted', $achievement->validation_status);

        // 9. Validator approves achievement
        // First, approve the document!
        $document = $achievement->documents()->firstOrFail();
        $this->actingAs($validator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->post(route('achievements.documents.verify', $document), [
                'action' => 'approve',
            ]);

        $response = $this->actingAs($validator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->post(route('validator.pending.validate', $achievement), [
                'action' => 'approve',
                'notes' => 'Sudah lengkap, disetujui tingkat fakultas',
                'checklist' => [
                    'certificate_valid' => true,
                    'event_date_valid' => true,
                    'organizer_valid' => true,
                    'level_appropriate' => true,
                    'documents_complete' => true,
                ],
            ]);

        if (session('errors')) {
            dump('Validator approval errors: ', session('errors')->getMessages());
        }
        if (session('error')) {
            dump('Validator approval error message: '.session('error'));
        }

        $response->assertRedirect(route('validator.pending.index'));
        $achievement->refresh();
        $this->assertEquals('faculty_approved', $achievement->validation_status);

        // 10. Admin approves achievement at university level
        $admin = User::forceCreate([
            'name' => 'Admin Univ',
            'email' => 'admin.univ@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        $adminRole = UserRole::create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'active_role_id' => $adminRole->id,
                'active_role_type' => 'admin',
            ])
            ->post(route('admin.university.validate', $achievement), [
                'action' => 'approve',
                'notes' => 'Disetujui tingkat universitas',
            ]);

        $response->assertRedirect();
        $achievement->refresh();
        $this->assertEquals('university_approved', $achievement->validation_status);
    }
}
