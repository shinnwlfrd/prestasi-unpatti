<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Services\SiakadApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function referenced_students_can_be_refreshed_from_siakad_via_command(): void
    {
        Student::forceCreate([
            'student_id' => '20220001',
            'id_mahasiswa' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Nama Lama',
            'email' => 'lama@unpatti.ac.id',
            'faculty_id' => '1',
            'faculty' => 'Fakultas Lama',
            'department_id' => '10',
            'department' => 'Jurusan Lama',
            'program_study_id' => '100',
            'program_study' => 'Prodi Lama',
            'angkatan' => '2022',
        ]);

        $this->mock(SiakadApiService::class, function ($mock) {
            $mock->shouldReceive('getMahasiswaById')
                ->once()
                ->with('550e8400-e29b-41d4-a716-446655440000')
                ->andReturn([
                    'id_mahasiswa' => '550e8400-e29b-41d4-a716-446655440000',
                    'nama_mahasiswa' => 'Nama Baru',
                    'registrasi' => [
                        'nim' => '20220001',
                        'email_kampus' => 'baru@unpatti.ac.id',
                        'ipk_kumulatif' => 3.91,
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

            $mock->shouldReceive('transformToStudentData')
                ->once()
                ->andReturn([
                    'student_id' => '20220001',
                    'id_mahasiswa' => '550e8400-e29b-41d4-a716-446655440000',
                    'name' => 'Nama Baru',
                    'email' => 'baru@unpatti.ac.id',
                    'ipk' => 3.91,
                    'angkatan' => '2022',
                    'faculty_id' => '1',
                    'faculty' => 'Fakultas Ekonomi',
                    'department_id' => '10',
                    'department' => 'Manajemen',
                    'program_study_id' => '100',
                    'program_study' => 'S1 Manajemen',
                ]);
        });

        $this->artisan('students:sync-referenced')
            ->expectsOutput('Sinkronisasi mahasiswa referensi selesai.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('students', [
            'student_id' => '20220001',
            'name' => 'Nama Baru',
            'email' => 'baru@unpatti.ac.id',
            'faculty' => 'Fakultas Ekonomi',
            'department' => 'Manajemen',
            'program_study' => 'S1 Manajemen',
        ]);
    }
}
