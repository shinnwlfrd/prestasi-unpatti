<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SigapApiService
{
    private $baseUrl = 'https://api.sigap.unpatti.ac.id';

    /**
     * Get all units from SIGAP API (with pagination handling)
     */
    private function getAllUnits()
    {
        // Check cache first
        $cached = Cache::get('sigap_all_units');
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }

        try {
            $allData = [];
            $page = 1;
            $perPage = 100; // Request more data per page

            do {
                $response = Http::timeout(15)
                    ->retry(2, 500)
                    ->get($this->baseUrl . '/service-referensi/unit', [
                        'page' => $page,
                        'per_page' => $perPage
                    ]);

                if (!$response->successful()) {
                    Log::error('SIGAP API Failed', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    break;
                }

                $result = $response->json();
                $data = $result['data']['data'] ?? [];

                if (empty($data)) {
                    break;
                }

                $allData = array_merge($allData, $data);

                // Check if there's more data
                $currentPage = $result['data']['current_page'] ?? 1;
                $total = $result['data']['total'] ?? 0;
                $hasMore = count($allData) < $total;

                $page++;

            } while ($hasMore && $page <= 10); // Max 10 pages to prevent infinite loop

            Log::info('SIGAP API - All Units Retrieved', ['count' => count($allData)]);

            // Only cache if we actually got data - prevent caching empty results
            if (!empty($allData)) {
                Cache::put('sigap_all_units', $allData, 3600);
            }

            return $allData;

        } catch (\Exception $e) {
            Log::error('SIGAP API Error', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Flatten nested children_recursive structure
     */
    private function flattenUnits($units, &$result = [])
    {
        foreach ($units as $unit) {
            $result[] = $unit;

            if (!empty($unit['children_recursive'])) {
                $this->flattenUnits($unit['children_recursive'], $result);
            }
        }

        return $result;
    }

    /**
     * Get all faculties (Fakultas)
     */
    public function getFaculties()
    {
        // Check cache first
        $cached = Cache::get('sigap_faculties');
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }

        $allUnits = $this->getAllUnits();
        $flatUnits = $this->flattenUnits($allUnits);

        // Filter hanya fakultas
        $faculties = collect($flatUnits)->filter(function ($item) {
            return strtolower($item['jenis_unit'] ?? '') === 'fakultas';
        })->map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'kode' => $item['kode'] ?? null,
                'nama' => $item['nama'] ?? null,
                'nama_en' => $item['nama_en'] ?? null,
                'jenis_unit' => $item['jenis_unit'] ?? null,
                'parent_id' => $item['parent_id'] ?? null,
            ];
        })->values()->all();

        Log::info('SIGAP API - Faculties Retrieved', ['count' => count($faculties)]);

        // Only cache if we got data
        if (!empty($faculties)) {
            Cache::put('sigap_faculties', $faculties, 3600);
        }

        return $faculties;
    }

    /**
     * Get all departments (Jurusan) under a faculty
     */
    public function getDepartments($facultyId = null)
    {
        $cacheKey = $facultyId ? "sigap_departments_{$facultyId}" : 'sigap_departments_all';

        $cached = Cache::get($cacheKey);
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }

        $allUnits = $this->getAllUnits();
        $flatUnits = $this->flattenUnits($allUnits);

        // Filter hanya jurusan
        $departments = collect($flatUnits)->filter(function ($item) use ($facultyId) {
            $isJurusan = strtolower($item['jenis_unit'] ?? '') === 'jurusan';

            if ($facultyId) {
                return $isJurusan && ($item['parent_id'] ?? null) == $facultyId;
            }

            return $isJurusan;
        })->map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'kode' => $item['kode'] ?? null,
                'nama' => $item['nama'] ?? null,
                'nama_en' => $item['nama_en'] ?? null,
                'jenis_unit' => $item['jenis_unit'] ?? null,
                'parent_id' => $item['parent_id'] ?? null,
            ];
        })->values()->all();

        Log::info('SIGAP API - Departments Retrieved', [
            'faculty_id' => $facultyId,
            'count' => count($departments)
        ]);

        // Only cache if we got data
        if (!empty($departments)) {
            Cache::put($cacheKey, $departments, 3600);
        }

        return $departments;
    }

    /**
     * Get all study programs (Program Studi) under a department
     */
    public function getStudyPrograms($departmentId = null)
    {
        $cacheKey = $departmentId ? "sigap_study_programs_{$departmentId}" : 'sigap_study_programs_all';

        $cached = Cache::get($cacheKey);
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }

        $allUnits = $this->getAllUnits();
        $flatUnits = $this->flattenUnits($allUnits);

        // Filter hanya program studi
        $studyPrograms = collect($flatUnits)->filter(function ($item) use ($departmentId) {
            $isProdi = strtolower($item['jenis_unit'] ?? '') === 'program studi';

            if ($departmentId) {
                return $isProdi && ($item['parent_id'] ?? null) == $departmentId;
            }

            return $isProdi;
        })->map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'kode' => $item['kode'] ?? null,
                'nama' => $item['nama'] ?? null,
                'nama_en' => $item['nama_en'] ?? null,
                'jenis_unit' => $item['jenis_unit'] ?? null,
                'parent_id' => $item['parent_id'] ?? null,
            ];
        })->values()->all();

        Log::info('SIGAP API - Study Programs Retrieved', [
            'department_id' => $departmentId,
            'count' => count($studyPrograms)
        ]);

        // Only cache if we got data
        if (!empty($studyPrograms)) {
            Cache::put($cacheKey, $studyPrograms, 3600);
        }

        return $studyPrograms;
    }

    /**
     * Get a flat map of unit IDs to their English names (nama_en)
     */
    public function getUnitNameMap()
    {
        return Cache::remember('sigap_unit_name_map', 3600, function () {
            $allUnits = $this->getAllUnits();
            $flatUnits = $this->flattenUnits($allUnits);

            $map = [];
            foreach ($flatUnits as $unit) {
                if (isset($unit['id'])) {
                    $map[$unit['id']] = $unit['nama_en'] ?? $unit['nama'] ?? 'N/A';
                }
            }

            return $map;
        });
    }

    /**
     * Get hierarchical structure: Faculty -> Department -> Study Program
     */
    public function getHierarchicalStructure()
    {
        return Cache::remember('sigap_hierarchical_structure', 3600, function () {
            $allUnits = $this->getAllUnits();
            $flatUnits = $this->flattenUnits($allUnits);
            $collection = collect($flatUnits);

            // Get faculties
            $faculties = $collection->filter(function ($item) {
                return strtolower($item['jenis_unit'] ?? '') === 'fakultas';
            })->map(function ($faculty) use ($collection) {
                // Get departments under this faculty
                $departments = $collection->filter(function ($item) use ($faculty) {
                    return strtolower($item['jenis_unit'] ?? '') === 'jurusan'
                        && ($item['parent_id'] ?? null) == $faculty['id'];
                })->map(function ($department) use ($collection) {
                    // Get study programs under this department
                    $studyPrograms = $collection->filter(function ($item) use ($department) {
                        return strtolower($item['jenis_unit'] ?? '') === 'program studi'
                            && ($item['parent_id'] ?? null) == $department['id'];
                    })->map(function ($item) {
                        return [
                            'id' => $item['id'] ?? null,
                            'kode' => $item['kode'] ?? null,
                            'nama' => $item['nama'] ?? null,
                            'nama_en' => $item['nama_en'] ?? null,
                            'jenis_unit' => $item['jenis_unit'] ?? null,
                        ];
                    })->values()->all();

                    return [
                        'id' => $department['id'] ?? null,
                        'kode' => $department['kode'] ?? null,
                        'nama' => $department['nama'] ?? null,
                        'nama_en' => $department['nama_en'] ?? null,
                        'jenis_unit' => $department['jenis_unit'] ?? null,
                        'study_programs' => $studyPrograms,
                    ];
                })->values()->all();

                return [
                    'id' => $faculty['id'] ?? null,
                    'kode' => $faculty['kode'] ?? null,
                    'nama' => $faculty['nama'] ?? null,
                    'nama_en' => $faculty['nama_en'] ?? null,
                    'jenis_unit' => $faculty['jenis_unit'] ?? null,
                    'departments' => $departments,
                ];
            })->values()->all();

            Log::info('SIGAP API - Hierarchical Structure Retrieved', [
                'faculties_count' => count($faculties)
            ]);

            return $faculties;
        });
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        Cache::forget('sigap_all_units');
        Cache::forget('sigap_faculties');
        Cache::forget('sigap_departments_all');
        Cache::forget('sigap_study_programs_all');
        Cache::forget('sigap_hierarchical_structure');

        // Clear specific faculty/department caches
        $pattern = 'sigap_departments_*';
        // Note: Laravel doesn't support wildcard cache clearing by default
        // You may need to implement this based on your cache driver

        Log::info('SIGAP API Cache Cleared');
    }
}

