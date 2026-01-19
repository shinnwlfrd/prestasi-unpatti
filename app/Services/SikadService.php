<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Student;

/**
 * Service untuk integrasi dengan SIKAD (Sistem Informasi Akademik)
 * Gunakan service ini untuk fetch dan sync data mahasiswa dari SIKAD
 */
class SikadService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('sikad.base_url', 'https://sikad.unpatti.ac.id/api');
        $this->apiKey = config('sikad.api_key', '');
        $this->timeout = config('sikad.timeout', 30);
    }

    /**
     * Fetch data mahasiswa dari SIKAD berdasarkan NIM
     * 
     * @param string $nim Nomor Induk Mahasiswa
     * @return array|null Data mahasiswa atau null jika tidak ditemukan
     */
    public function fetchStudentData(string $nim): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/students/{$nim}");

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::warning("SIKAD: Failed to fetch student {$nim}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("SIKAD: Error fetching student {$nim}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Fetch data akademik mahasiswa (IPK, SKS, mata kuliah)
     * 
     * @param string $nim Nomor Induk Mahasiswa
     * @return array|null Data akademik atau null jika tidak ditemukan
     */
    public function fetchAcademicData(string $nim): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/students/{$nim}/academic");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error("SIKAD: Error fetching academic data for {$nim}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Sync data mahasiswa dari SIKAD ke database lokal
     * 
     * @param string $nim Nomor Induk Mahasiswa
     * @return Student|null Student model yang di-update atau null jika gagal
     */
    public function syncStudentData(string $nim): ?Student
    {
        $sikadData = $this->fetchStudentData($nim);

        if (!$sikadData) {
            return null;
        }

        try {
            $student = Student::updateOrCreate(
                ['student_id' => $nim],
                [
                    'name' => $sikadData['name'] ?? null,
                    'faculty' => $sikadData['faculty'] ?? null,
                    'program_study' => $sikadData['program_study'] ?? null,
                    'semester' => $sikadData['semester'] ?? null,
                    'gpa' => $sikadData['gpa'] ?? null,
                    'email' => $sikadData['email'] ?? null,
                    'photo' => $sikadData['photo'] ?? null,
                ]
            );

            Log::info("SIKAD: Synced student {$nim} successfully");
            return $student;
        } catch (\Exception $e) {
            Log::error("SIKAD: Error syncing student {$nim}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Verifikasi kredensial mahasiswa dengan SIKAD
     * 
     * @param string $nim Nomor Induk Mahasiswa
     * @param string $password Password SIKAD
     * @return bool True jika kredensial valid
     */
    public function verifyCredentials(string $nim, string $password): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/auth/verify", [
                    'nim' => $nim,
                    'password' => $password,
                ]);

            return $response->successful() && $response->json('valid', false);
        } catch (\Exception $e) {
            Log::error("SIKAD: Error verifying credentials for {$nim}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Batch sync multiple students
     * 
     * @param array $nims Array of NIM
     * @return array Array of results with nim => success status
     */
    public function batchSync(array $nims): array
    {
        $results = [];

        foreach ($nims as $nim) {
            $student = $this->syncStudentData($nim);
            $results[$nim] = $student !== null;
        }

        return $results;
    }

    /**
     * Get HTTP headers for SIKAD API requests
     */
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->apiKey}",
            'X-API-Version' => '1.0',
        ];
    }

    /**
     * Check if SIKAD API is available
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
