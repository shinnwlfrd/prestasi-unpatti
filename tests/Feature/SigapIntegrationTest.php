<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\User;
use App\Models\UserRole;
use App\Services\SiakadApiService;
use App\Services\SigapApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SigapIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicPeriod::create([
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
    }

    #[Test]
    public function student_search_falls_back_to_siakad_when_local_scope_has_no_match(): void
    {
        $operator = User::forceCreate([
            'name' => 'Operator FE',
            'email' => 'operator.sigap@unpatti.ac.id',
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

        $siakad = Mockery::mock(SiakadApiService::class)->makePartial();
        $siakad->shouldReceive('searchMahasiswa')
            ->once()
            ->with('mahasiswa fallback', null, '1', 1, 20)
            ->andReturn([
                'success' => true,
                'data' => [
                    ['id_mahasiswa' => 'SIA123'],
                ],
            ]);

        $siakad->shouldReceive('getMahasiswaById')
            ->once()
            ->with('SIA123')
            ->andReturn([
                'id_mahasiswa' => 'SIA123',
                'nama_mahasiswa' => 'Mahasiswa Fallback',
                'registrasi' => [
                    'nim' => '20220001',
                    'email_kampus' => 'fallback@unpatti.ac.id',
                    'ipk_kumulatif' => 3.8,
                ],
                'fakultas' => [
                    'id' => '1',
                    'nama' => 'Fakultas Ekonomi',
                ],
                'jurusan' => [
                    'id' => '10',
                    'nama' => 'Manajemen',
                ],
                'program_studi' => [
                    'id' => '100',
                    'nama' => 'S1 Manajemen',
                ],
            ]);

        $this->app->instance(SiakadApiService::class, $siakad);

        $response = $this->actingAs($operator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->getJson('/api/sigap/students/search?q=mahasiswa%20fallback');

        $response->assertOk()
            ->assertJsonPath('source', 'siakad')
            ->assertJsonPath('data.0.student_id', '20220001')
            ->assertJsonPath('data.0.faculty_id', '1')
            ->assertJsonPath('data.0.name', 'Mahasiswa Fallback');
    }

    #[Test]
    public function student_search_fallback_respects_program_study_scope(): void
    {
        $operator = User::forceCreate([
            'name' => 'Operator Prodi',
            'email' => 'operator.prodi@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Operator',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Ekonomi',
        ]);

        $role = UserRole::create([
            'user_id' => $operator->id,
            'role' => 'operator',
            'level' => 'program_study',
            'faculty_id' => '1',
            'faculty_name' => 'Fakultas Ekonomi',
            'department_id' => '10',
            'department_name' => 'Manajemen',
            'program_study_id' => '100',
            'program_study_name' => 'S1 Manajemen',
            'is_active' => true,
        ]);

        $siakad = Mockery::mock(SiakadApiService::class)->makePartial();
        $siakad->shouldReceive('searchMahasiswa')
            ->once()
            ->with('scope fallback', '100', '1', 1, 20)
            ->andReturn([
                'success' => true,
                'data' => [
                    ['id_mahasiswa' => 'SIA100'],
                    ['id_mahasiswa' => 'SIA200'],
                ],
            ]);

        $siakad->shouldReceive('getMahasiswaById')
            ->once()
            ->with('SIA100')
            ->andReturn([
                'id_mahasiswa' => 'SIA100',
                'nama_mahasiswa' => 'Mahasiswa Sesuai Scope',
                'registrasi' => [
                    'nim' => '20220002',
                    'email_kampus' => 'scope@unpatti.ac.id',
                ],
                'fakultas' => ['id' => '1', 'nama' => 'Fakultas Ekonomi'],
                'jurusan' => ['id' => '10', 'nama' => 'Manajemen'],
                'program_studi' => ['id' => '100', 'nama' => 'S1 Manajemen'],
            ]);

        $siakad->shouldReceive('getMahasiswaById')
            ->once()
            ->with('SIA200')
            ->andReturn([
                'id_mahasiswa' => 'SIA200',
                'nama_mahasiswa' => 'Mahasiswa Beda Scope',
                'registrasi' => [
                    'nim' => '20220003',
                    'email_kampus' => 'other@unpatti.ac.id',
                ],
                'fakultas' => ['id' => '1', 'nama' => 'Fakultas Ekonomi'],
                'jurusan' => ['id' => '10', 'nama' => 'Manajemen'],
                'program_studi' => ['id' => '999', 'nama' => 'Program Lain'],
            ]);

        $this->app->instance(SiakadApiService::class, $siakad);

        $response = $this->actingAs($operator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'program_study',
                'operator_faculty_id' => '1',
                'operator_department_id' => '10',
                'operator_program_study_id' => '100',
            ])
            ->getJson('/api/sigap/students/search?q=scope%20fallback');

        $response->assertOk()
            ->assertJsonPath('source', 'siakad')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.student_id', '20220002')
            ->assertJsonPath('meta.scope.department_id', '10')
            ->assertJsonPath('meta.scope.program_study_id', '100');
    }

    #[Test]
    public function student_search_fallback_uses_cache_for_identical_requests(): void
    {
        Cache::flush();

        $operator = User::forceCreate([
            'name' => 'Operator Cache',
            'email' => 'operator.cache@unpatti.ac.id',
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

        $siakad = Mockery::mock(SiakadApiService::class)->makePartial();
        $siakad->shouldReceive('searchMahasiswa')
            ->once()
            ->with('cache fallback', null, '1', 1, 20)
            ->andReturn([
                'success' => true,
                'data' => [
                    ['id_mahasiswa' => 'SIA300'],
                ],
            ]);

        $siakad->shouldReceive('getMahasiswaById')
            ->once()
            ->with('SIA300')
            ->andReturn([
                'id_mahasiswa' => 'SIA300',
                'nama_mahasiswa' => 'Mahasiswa Cache',
                'registrasi' => [
                    'nim' => '20220004',
                    'email_kampus' => 'cache@unpatti.ac.id',
                ],
                'fakultas' => ['id' => '1', 'nama' => 'Fakultas Ekonomi'],
                'jurusan' => ['id' => '10', 'nama' => 'Manajemen'],
                'program_studi' => ['id' => '100', 'nama' => 'S1 Manajemen'],
            ]);

        $this->app->instance(SiakadApiService::class, $siakad);

        $request = fn () => $this->actingAs($operator)
            ->withSession([
                'active_role_id' => $role->id,
                'active_role_type' => 'operator',
                'operator_level' => 'faculty',
                'operator_faculty_id' => '1',
            ])
            ->getJson('/api/sigap/students/search?q=cache%20fallback');

        $firstResponse = $request();
        $firstResponse->assertOk()
            ->assertJsonPath('source', 'siakad')
            ->assertJsonPath('meta.cached', false);

        $secondResponse = $request();
        $secondResponse->assertOk()
            ->assertJsonPath('source', 'siakad_cache')
            ->assertJsonPath('message', 'Data mahasiswa fallback SIAKAD diambil dari cache.')
            ->assertJsonPath('meta.cached', true)
            ->assertJsonPath('data.0.student_id', '20220004');
    }

    #[Test]
    public function sigap_filter_endpoint_returns_informative_failure_when_upstream_is_unavailable(): void
    {
        $sigap = Mockery::mock(SigapApiService::class);
        $sigap->shouldReceive('getFaculties')
            ->once()
            ->andReturn([]);
        $sigap->shouldReceive('getLastOperationStatus')
            ->once()
            ->andReturn([
                'success' => false,
                'source' => 'api',
                'message' => 'SIGAP mengembalikan response non-sukses.',
                'meta' => ['http_status' => 503],
            ]);

        $this->app->instance(SigapApiService::class, $sigap);

        $response = $this->getJson('/api/sigap/faculties');

        $response->assertStatus(502)
            ->assertJsonPath('success', false)
            ->assertJsonPath('source', 'api')
            ->assertJsonPath('message', 'SIGAP mengembalikan response non-sukses.')
            ->assertJsonPath('meta.http_status', 503);
    }

    #[Test]
    public function sigap_filter_endpoint_exposes_source_metadata_on_success(): void
    {
        $sigap = Mockery::mock(SigapApiService::class);
        $sigap->shouldReceive('getFaculties')
            ->once()
            ->andReturn([
                ['id' => '1', 'nama' => 'Fakultas Ekonomi'],
            ]);
        $sigap->shouldReceive('getLastOperationStatus')
            ->once()
            ->andReturn([
                'success' => true,
                'source' => 'cache',
                'message' => 'Data fakultas SIGAP diambil dari cache.',
                'meta' => ['count' => 1],
            ]);

        $this->app->instance(SigapApiService::class, $sigap);

        $response = $this->getJson('/api/sigap/faculties');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('source', 'cache')
            ->assertJsonPath('message', 'Data fakultas SIGAP diambil dari cache.')
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.nama', 'Fakultas Ekonomi');
    }
}
