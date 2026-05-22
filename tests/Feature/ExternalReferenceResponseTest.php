<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use App\Services\SiakadApiService;
use App\Services\SigapApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExternalReferenceResponseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function siakad_search_endpoint_exposes_consistent_metadata_on_success(): void
    {
        $user = User::forceCreate([
            'name' => 'Auth User',
            'email' => 'auth.user@unpatti.ac.id',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        UserRole::create([
            'user_id' => $user->id,
            'role' => 'admin',
            'level' => 'university',
            'is_active' => true,
        ]);

        $siakad = \Mockery::mock(SiakadApiService::class);
        $siakad->shouldReceive('searchMahasiswa')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    [
                        'id_mahasiswa' => 'UUID-1',
                        'nama_mahasiswa' => 'Mahasiswa API',
                        'registrasi' => ['nim' => '20220002'],
                        'program_studi' => ['nama' => 'S1 Manajemen'],
                        'fakultas' => ['nama' => 'Fakultas Ekonomi'],
                    ],
                ],
                'meta' => [
                    'page' => 1,
                    'total_pages' => 1,
                ],
            ]);
        $siakad->shouldReceive('getLastOperationStatus')
            ->once()
            ->andReturn([
                'success' => true,
                'source' => 'siakad_api',
                'message' => 'Pencarian mahasiswa SIAKAD berhasil.',
                'meta' => [
                    'http_status' => 200,
                    'result_count' => 1,
                ],
            ]);

        $this->app->instance(SiakadApiService::class, $siakad);

        $response = $this->actingAs($user)->getJson('/api/siakad/mahasiswa/search?search=mahasiswa');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('source', 'siakad_api')
            ->assertJsonPath('message', 'Pencarian mahasiswa SIAKAD berhasil.')
            ->assertJsonPath('meta.result_count', 1)
            ->assertJsonPath('results.0.nim', '20220002')
            ->assertJsonPath('results.0.nama', 'Mahasiswa API');
    }

    #[Test]
    public function sigap_general_endpoint_exposes_consistent_failure_contract(): void
    {
        $sigap = \Mockery::mock(SigapApiService::class);
        $sigap->shouldReceive('getHierarchicalStructure')
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

        $response = $this->getJson('/api/sigap/hierarchy');

        $response->assertStatus(502)
            ->assertJsonPath('success', false)
            ->assertJsonPath('source', 'api')
            ->assertJsonPath('message', 'SIGAP mengembalikan response non-sukses.')
            ->assertJsonPath('meta.http_status', 503);
    }
}
