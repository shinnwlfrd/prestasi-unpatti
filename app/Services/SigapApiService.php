<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SigapApiService
{
    private string $baseUrl;

    private int $timeout;

    private array $lastOperationStatus = [
        'success' => true,
        'source' => 'unknown',
        'message' => null,
        'meta' => [],
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.sigap.base_url', 'https://api.sigap.unpatti.ac.id'), '/');
        $this->timeout = (int) config('services.sigap.timeout', 15);
    }

    public function getHealthStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry(1, 250)
                ->get($this->baseUrl.'/service-referensi/unit', [
                    'page' => 1,
                    'per_page' => 1,
                ]);

            if ($response->successful()) {
                $payload = $response->json();
                $sampleCount = count($payload['data']['data'] ?? []);

                return [
                    'service' => 'SIGAP',
                    'status' => 'up',
                    'message' => 'Endpoint referensi responsif.',
                    'base_url' => $this->baseUrl,
                    'meta' => [
                        'http_status' => $response->status(),
                        'sample_count' => $sampleCount,
                    ],
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            return [
                'service' => 'SIGAP',
                'status' => 'down',
                'message' => 'Endpoint referensi tidak merespons sukses.',
                'base_url' => $this->baseUrl,
                'meta' => [
                    'http_status' => $response->status(),
                ],
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            Log::warning('SIGAP health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'service' => 'SIGAP',
                'status' => 'down',
                'message' => 'Health check SIGAP gagal: '.$e->getMessage(),
                'base_url' => $this->baseUrl,
                'meta' => [],
                'checked_at' => now()->toIso8601String(),
            ];
        }
    }

    public function getLastOperationStatus(): array
    {
        return $this->lastOperationStatus;
    }

    /**
     * Get all units from SIGAP API (with pagination handling)
     */
    private function getAllUnits()
    {
        // Check cache first
        $cached = Cache::get('sigap_all_units');
        if ($cached !== null && ! empty($cached)) {
            $this->setOperationStatus(true, 'cache', 'Data unit SIGAP diambil dari cache.', [
                'count' => count($cached),
            ]);

            return $cached;
        }

        try {
            $allData = [];
            $page = 1;
            $perPage = 100; // Request more data per page

            do {
                $response = Http::timeout($this->timeout)
                    ->retry(2, 500)
                    ->get($this->baseUrl.'/service-referensi/unit', [
                        'page' => $page,
                        'per_page' => $perPage,
                    ]);

                if (! $response->successful()) {
                    Log::error('SIGAP API Failed', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    $this->setOperationStatus(false, 'api', 'SIGAP mengembalikan response non-sukses.', [
                        'http_status' => $response->status(),
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
            if (! empty($allData)) {
                Cache::put('sigap_all_units', $allData, 3600);
                $this->setOperationStatus(true, 'api', 'Data unit SIGAP berhasil diambil dari API.', [
                    'count' => count($allData),
                ]);
            } else {
                $this->setOperationStatus(true, 'api', 'SIGAP merespons sukses tetapi tidak mengembalikan data unit.', [
                    'count' => 0,
                ]);
            }

            return $allData;

        } catch (\Exception $e) {
            Log::error('SIGAP API Error', [
                'error' => $e->getMessage(),
            ]);
            $this->setOperationStatus(false, 'api', 'Permintaan ke SIGAP gagal: '.$e->getMessage());

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

            if (! empty($unit['children_recursive'])) {
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
        if ($cached !== null && ! empty($cached)) {
            $this->setOperationStatus(true, 'cache', 'Data fakultas SIGAP diambil dari cache.', [
                'count' => count($cached),
            ]);

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
        if (! empty($faculties)) {
            Cache::put('sigap_faculties', $faculties, 3600);
            $this->setOperationStatus(true, 'api', 'Data fakultas SIGAP berhasil dimuat.', [
                'count' => count($faculties),
            ]);
        } elseif (($this->lastOperationStatus['success'] ?? true) === true) {
            $this->setOperationStatus(true, 'api', 'SIGAP tidak mengembalikan data fakultas.', [
                'count' => 0,
            ]);
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
        if ($cached !== null && ! empty($cached)) {
            $this->setOperationStatus(true, 'cache', 'Data jurusan SIGAP diambil dari cache.', [
                'count' => count($cached),
                'faculty_id' => $facultyId,
            ]);

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
            'count' => count($departments),
        ]);

        // Only cache if we got data
        if (! empty($departments)) {
            Cache::put($cacheKey, $departments, 3600);
            $this->setOperationStatus(true, 'api', 'Data jurusan SIGAP berhasil dimuat.', [
                'count' => count($departments),
                'faculty_id' => $facultyId,
            ]);
        } elseif (($this->lastOperationStatus['success'] ?? true) === true) {
            $this->setOperationStatus(true, 'api', 'SIGAP tidak mengembalikan data jurusan.', [
                'count' => 0,
                'faculty_id' => $facultyId,
            ]);
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
        if ($cached !== null && ! empty($cached)) {
            $this->setOperationStatus(true, 'cache', 'Data program studi SIGAP diambil dari cache.', [
                'count' => count($cached),
                'department_id' => $departmentId,
            ]);

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
            'count' => count($studyPrograms),
        ]);

        // Only cache if we got data
        if (! empty($studyPrograms)) {
            Cache::put($cacheKey, $studyPrograms, 3600);
            $this->setOperationStatus(true, 'api', 'Data program studi SIGAP berhasil dimuat.', [
                'count' => count($studyPrograms),
                'department_id' => $departmentId,
            ]);
        } elseif (($this->lastOperationStatus['success'] ?? true) === true) {
            $this->setOperationStatus(true, 'api', 'SIGAP tidak mengembalikan data program studi.', [
                'count' => 0,
                'department_id' => $departmentId,
            ]);
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
                    $rawName = $unit['nama'] ?? $unit['nama_en'] ?? 'N/A';

                    $map[$unit['id']] = trim(str_ireplace(['Jurusan', 'Program Studi'], '', $rawName));
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
        $cached = Cache::get('sigap_hierarchical_structure');
        if ($cached !== null && ! empty($cached)) {
            $this->setOperationStatus(true, 'cache', 'Data hirarki SIGAP diambil dari cache.', [
                'count' => count($cached),
            ]);

            return $cached;
        }

        $hierarchy = (function () {
            $allUnits = $this->getAllUnits();
            $flatUnits = $this->flattenUnits($allUnits);
            $collection = collect($flatUnits);

            $cleanName = function ($name) {
                return $name ? trim(str_ireplace(['Jurusan', 'Program Studi'], '', $name)) : null;
            };

            // Get faculties
            $faculties = $collection->filter(function ($item) {
                return strtolower($item['jenis_unit'] ?? '') === 'fakultas';
            })->map(function ($faculty) use ($collection, $cleanName) {
                // Get departments under this faculty
                $departments = $collection->filter(function ($item) use ($faculty) {
                    return strtolower($item['jenis_unit'] ?? '') === 'jurusan'
                        && ($item['parent_id'] ?? null) == $faculty['id'];
                })->map(function ($department) use ($collection, $cleanName) {
                    // Get study programs under this department
                    $studyPrograms = $collection->filter(function ($item) use ($department) {
                        return strtolower($item['jenis_unit'] ?? '') === 'program studi'
                            && ($item['parent_id'] ?? null) == $department['id'];
                    })->map(function ($item) use ($cleanName) {
                        return [
                            'id' => $item['id'] ?? null,
                            'kode' => $item['kode'] ?? null,
                            'nama' => $cleanName($item['nama']) ?? null,
                            'nama_en' => $item['nama_en'] ?? null,
                            'jenis_unit' => $item['jenis_unit'] ?? null,
                        ];
                    })->values()->all();

                    return [
                        'id' => $department['id'] ?? null,
                        'kode' => $department['kode'] ?? null,
                        'nama' => $cleanName($department['nama']) ?? null,
                        'nama_en' => $department['nama_en'] ?? null,
                        'jenis_unit' => $department['jenis_unit'] ?? null,
                        'study_programs' => $studyPrograms,
                    ];
                })->values()->all();

                return [
                    'id' => $faculty['id'] ?? null,
                    'kode' => $faculty['kode'] ?? null,
                    'nama' => $cleanName($faculty['nama']) ?? null,
                    'nama_en' => $faculty['nama_en'] ?? null,
                    'jenis_unit' => $faculty['jenis_unit'] ?? null,
                    'departments' => $departments,
                ];
            })->values()->all();

            if (empty($faculties)) {
                $fallbackPath = database_path('seeders/data/sigap_hierarchy.json');
                if (file_exists($fallbackPath)) {
                    $jsonFaculties = json_decode(file_get_contents($fallbackPath), true);
                    if (! empty($jsonFaculties)) {
                        Log::info('SIGAP API - Using Fallback JSON for Hierarchical Structure');
                        $this->setOperationStatus(true, 'fallback_json', 'Hirarki SIGAP memakai fallback JSON lokal.', [
                            'count' => count($jsonFaculties),
                        ]);

                        return $jsonFaculties;
                    }
                }
            }

            Log::info('SIGAP API - Hierarchical Structure Retrieved', [
                'faculties_count' => count($faculties),
            ]);

            if (! empty($faculties)) {
                $this->setOperationStatus(true, 'api', 'Hirarki SIGAP berhasil dimuat.', [
                    'count' => count($faculties),
                ]);
            } elseif (($this->lastOperationStatus['success'] ?? true) === true) {
                $this->setOperationStatus(true, 'api', 'SIGAP tidak mengembalikan hirarki unit.', [
                    'count' => 0,
                ]);
            }

            return $faculties;
        })();

        if (! empty($hierarchy)) {
            Cache::put('sigap_hierarchical_structure', $hierarchy, 3600);
        }

        return $hierarchy;
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
        Cache::forget('sigap_unit_name_map');

        // Clear specific faculty/department caches
        $pattern = 'sigap_departments_*';
        // Note: Laravel doesn't support wildcard cache clearing by default
        // You may need to implement this based on your cache driver

        Log::info('SIGAP API Cache Cleared');
    }

    private function setOperationStatus(bool $success, string $source, ?string $message = null, array $meta = []): void
    {
        $this->lastOperationStatus = [
            'success' => $success,
            'source' => $source,
            'message' => $message,
            'meta' => $meta,
        ];
    }
}
