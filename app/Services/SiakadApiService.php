<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiakadApiService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.siakad.base_url');
        $this->apiKey = config('services.siakad.api_key');
    }

    /**
     * Search mahasiswa by keyword (nama or NIM)
     */
    public function searchMahasiswa(
        string $search = '',
        ?string $idProdi = null,
        ?string $idFakultas = null,
        int $page = 1,
        int $pageSize = 15
    ): array {
        try {
            // Build query parameters
            $params = [
                'page' => $page,
                'page.size' => $pageSize,
                'sort' => 'nama_mahasiswa',
            ];

            // Only add search filter if search query is not empty
            if (!empty(trim($search))) {
                $params['filter.search'] = trim($search);
            }

            // Only add prodi filter if provided
            if (!empty($idProdi)) {
                $params['filter.id_prodi'] = $idProdi;
            }

            // Only add fakultas filter if provided
            if (!empty($idFakultas)) {
                $params['filter.id_fakultas'] = $idFakultas;
            }

            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/mahasiswa", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('SIAKAD API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return ['success' => false, 'data' => [], 'meta' => []];
        } catch (\Exception $e) {
            Log::error('SIAKAD API Exception', [
                'message' => $e->getMessage()
            ]);

            return ['success' => false, 'data' => [], 'meta' => []];
        }
    }

    /**
     * Get mahasiswa by NIM
     */
    public function getMahasiswaByNim(string $nim): ?array
    {
        $result = $this->searchMahasiswa($nim, null, null, 1, 1);

        if ($result['success'] && !empty($result['data'])) {
            // Get first result and fetch full detail
            $firstResult = $result['data'][0];
            $idMahasiswa = $firstResult['id_mahasiswa'] ?? null;
            
            if ($idMahasiswa) {
                return $this->getMahasiswaById($idMahasiswa);
            }
        }

        return null;
    }

    /**
     * Get mahasiswa by Email
     */
    public function getMahasiswaByEmail(string $email): ?array
    {
        $result = $this->searchMahasiswa($email, null, null, 1, 1);

        if ($result['success'] && !empty($result['data'])) {
            // Get first result and fetch full detail
            $firstResult = $result['data'][0];
            $idMahasiswa = $firstResult['id_mahasiswa'] ?? null;
            
            if ($idMahasiswa) {
                return $this->getMahasiswaById($idMahasiswa);
            }
        }

        return null;
    }

    /**
     * Get mahasiswa by ID (Detail lengkap)
     */
    public function getMahasiswaById(string $idMahasiswa): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/mahasiswa/{$idMahasiswa}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('SIAKAD API Exception', [
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Get request headers
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        return $headers;
    }

    /**
     * Transform SIAKAD data to local student format (Simplified)
     * Only essential fields for achievement system
     */
    public function transformToStudentData(array $siakadData): array
    {
        // Get NIM from registrasi or direct field
        $nim = $siakadData['registrasi']['nim'] ?? $siakadData['nim'] ?? null;
        
        // Get email with fallback
        $email = $siakadData['registrasi']['email_kampus'] 
            ?? $siakadData['email_pribadi'] 
            ?? ($nim ? $nim . '@student.unpatti.ac.id' : null);

        return [
            // Primary fields
            'student_id' => $nim,
            'id_mahasiswa' => $siakadData['id_mahasiswa'],
            'name' => $siakadData['nama_mahasiswa'],
            'email' => $email,
            
            // Photo
            'foto_url' => $siakadData['foto_url'] ?? null,
            
            // Academic data
            'ipk' => $siakadData['registrasi']['ipk_kumulatif'] ?? null,
            'angkatan' => $nim ? substr($nim, 0, 4) : null, // Extract from NIM
            
            // Fakultas
            'faculty_id' => $siakadData['fakultas']['id'] ?? null,
            'faculty' => $siakadData['fakultas']['nama'] ?? null,
            
            // Jurusan
            'department_id' => $siakadData['jurusan']['id'] ?? null,
            'department' => $siakadData['jurusan']['nama'] ?? null,
            
            // Program Studi
            'program_study_id' => $siakadData['program_studi']['id'] ?? null,
            'program_study' => $siakadData['program_studi']['nama'] ?? null,
        ];
    }
}
