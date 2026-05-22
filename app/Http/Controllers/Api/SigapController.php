<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\SiakadApiService;
use App\Services\SigapApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SigapController extends Controller
{
    protected $sigapService;

    protected $siakadService;

    public function __construct(SigapApiService $sigapService, SiakadApiService $siakadService)
    {
        $this->sigapService = $sigapService;
        $this->siakadService = $siakadService;
    }

    /**
     * Get all faculties
     */
    public function faculties()
    {
        $faculties = $this->sigapService->getFaculties();

        return $this->buildSigapResponse($faculties, [
            'count' => count($faculties),
        ]);
    }

    /**
     * Get departments by faculty
     */
    public function departments(Request $request)
    {
        $facultyId = $request->query('faculty_id');
        $departments = $this->sigapService->getDepartments($facultyId);

        return $this->buildSigapResponse($departments, [
            'count' => count($departments),
            'faculty_id' => $facultyId,
        ]);
    }

    /**
     * Get study programs by department
     */
    public function studyPrograms(Request $request)
    {
        $departmentId = $request->query('department_id');
        $studyPrograms = $this->sigapService->getStudyPrograms($departmentId);

        return $this->buildSigapResponse($studyPrograms, [
            'count' => count($studyPrograms),
            'department_id' => $departmentId,
        ]);
    }

    /**
     * Get hierarchical structure
     */
    public function hierarchy()
    {
        $structure = $this->sigapService->getHierarchicalStructure();

        return $this->buildSigapResponse($structure, [
            'count' => count($structure),
        ]);
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        $this->sigapService->clearCache();

        Log::warning('Cache cleared by user', [
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully',
        ]);
    }

    private function buildSigapResponse(array $data, array $extra = [])
    {
        $status = $this->sigapService->getLastOperationStatus();
        $payload = array_merge([
            'success' => $status['success'] ?? true,
            'data' => $data,
            'source' => $status['source'] ?? 'unknown',
            'message' => $status['message'] ?? null,
            'meta' => $status['meta'] ?? [],
        ], $extra);

        $httpCode = ($status['success'] ?? true) ? 200 : 502;

        return response()->json($payload, $httpCode);
    }

    /**
     * Search students by name or NIM
     */
    public function searchStudents(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        // Minimum 2 characters
        if (strlen($search) < 2) {
            return response()->json([
                'data' => [],
                'source' => 'none',
                'pagination' => [
                    'page' => 1,
                    'per_page' => 20,
                    'has_more' => false,
                ],
            ]);
        }

        try {
            $user = $request->user();
            $currentRole = $user?->getCurrentRole();
            $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
            $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: $request->query('faculty_id'));
            $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id'));
            $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id'));
            $page = max((int) $request->query('page', 1), 1);
            $perPage = min(max((int) $request->query('per_page', 20), 1), 50);
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $studentsQuery = Student::query();

            if ($level === 'faculty' && $facultyId) {
                $studentsQuery->where('faculty_id', $facultyId);
            } elseif ($level === 'department' && $departmentId) {
                $studentsQuery->where('department_id', $departmentId);
            } elseif ($level === 'program_study' && $programStudyId) {
                $studentsQuery->where('program_study_id', $programStudyId);
            } elseif (! $level && $facultyId) {
                $studentsQuery->where('faculty_id', $facultyId);
            }

            $studentsQuery->where(function ($q) use ($search, $operator) {
                $q->where('name', $operator, "%{$search}%")
                    ->orWhere('student_id', $operator, "%{$search}%")
                    ->orWhere('email', $operator, "%{$search}%");
            });

            $students = $studentsQuery
                ->orderBy('name')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage + 1)
                ->get(['student_id', 'name', 'email', 'faculty_id', 'faculty', 'department_id', 'department', 'program_study_id', 'program_study', 'angkatan']);

            $hasMore = $students->count() > $perPage;
            $students = $students->take($perPage)->map(fn (Student $student) => [
                'id' => $student->student_id,
                'student_id' => $student->student_id,
                'name' => $student->name,
                'email' => $student->email,
                'faculty_id' => $student->faculty_id,
                'faculty' => $student->faculty,
                'department_id' => $student->department_id,
                'department' => $student->department,
                'program_study_id' => $student->program_study_id,
                'program_study' => $student->program_study,
                'program' => $student->program_study,
                'angkatan' => $student->angkatan,
                'label' => "{$student->name} ({$student->student_id})",
            ])->values();

            Log::info('Protected student search accessed', [
                'user_id' => auth()->id(),
                'query_length' => strlen($search),
                'result_count' => $students->count(),
            ]);

            if ($students->isNotEmpty()) {
                return response()->json([
                    'data' => $students,
                    'source' => 'local',
                    'pagination' => [
                        'page' => $page,
                        'per_page' => $perPage,
                        'has_more' => $hasMore,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Local student search failed, trying SIAKAD fallback', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
        }

        return $this->searchStudentsFromSiakad(
            search: $search,
            facultyId: $facultyId ?? null,
            departmentId: $departmentId ?? null,
            programStudyId: $programStudyId ?? null,
            page: $page ?? 1,
            perPage: $perPage ?? 20
        );
    }

    private function searchStudentsFromSiakad(
        string $search,
        ?string $facultyId,
        ?string $departmentId,
        ?string $programStudyId,
        int $page,
        int $perPage
    ) {
        try {
            $cacheKey = $this->buildFallbackSearchCacheKey(
                search: $search,
                facultyId: $facultyId,
                departmentId: $departmentId,
                programStudyId: $programStudyId,
                page: $page,
                perPage: $perPage
            );

            $cachedStudents = Cache::get($cacheKey);
            if (is_array($cachedStudents)) {
                return response()->json([
                    'data' => $cachedStudents,
                    'source' => 'siakad_cache',
                    'message' => 'Data mahasiswa fallback SIAKAD diambil dari cache.',
                    'pagination' => [
                        'page' => $page,
                        'per_page' => $perPage,
                        'has_more' => false,
                    ],
                    'meta' => [
                        'cached' => true,
                        'scope' => $this->buildSearchScopeMeta($facultyId, $departmentId, $programStudyId),
                    ],
                ]);
            }

            $result = $this->siakadService->searchMahasiswa(
                $search,
                $programStudyId,
                $facultyId,
                $page,
                $perPage
            );

            if (! ($result['success'] ?? false) || empty($result['data'])) {
                return response()->json([
                    'data' => [],
                    'source' => 'siakad',
                    'message' => 'Data mahasiswa tidak ditemukan di lokal maupun SIAKAD.',
                    'pagination' => [
                        'page' => $page,
                        'per_page' => $perPage,
                        'has_more' => false,
                    ],
                    'meta' => [
                        'cached' => false,
                        'scope' => $this->buildSearchScopeMeta($facultyId, $departmentId, $programStudyId),
                    ],
                ]);
            }

            $mappedStudents = collect($result['data'])
                ->map(function (array $item) {
                    $idMahasiswa = $item['id_mahasiswa'] ?? null;
                    if (! $idMahasiswa) {
                        return;
                    }

                    $detail = $this->siakadService->getMahasiswaById((string) $idMahasiswa);
                    if (! $detail) {
                        return;
                    }

                    return $this->siakadService->transformToStudentData($detail);
                })
                ->filter()
                ->filter(function (array $student) use ($departmentId, $programStudyId) {
                    if ($programStudyId) {
                        return ($student['program_study_id'] ?? null) == $programStudyId;
                    }

                    if ($departmentId) {
                        return ($student['department_id'] ?? null) == $departmentId;
                    }

                    return true;
                })
                ->map(function (array $student) {
                    return [
                        'id' => $student['student_id'] ?? null,
                        'student_id' => $student['student_id'] ?? null,
                        'name' => $student['name'] ?? '-',
                        'email' => $student['email'] ?? null,
                        'faculty_id' => $student['faculty_id'] ?? null,
                        'faculty' => $student['faculty'] ?? null,
                        'department_id' => $student['department_id'] ?? null,
                        'department' => $student['department'] ?? null,
                        'program_study_id' => $student['program_study_id'] ?? null,
                        'program_study' => $student['program_study'] ?? null,
                        'program' => $student['program_study'] ?? null,
                        'angkatan' => $student['angkatan'] ?? null,
                        'label' => ($student['name'] ?? 'Mahasiswa').' ('.($student['student_id'] ?? '-').')',
                    ];
                })
                ->values();

            Cache::put($cacheKey, $mappedStudents->all(), now()->addMinutes(10));

            return response()->json([
                'data' => $mappedStudents,
                'source' => 'siakad',
                'message' => 'Data mahasiswa diambil dari fallback SIAKAD.',
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'has_more' => false,
                ],
                'meta' => [
                    'cached' => false,
                    'scope' => $this->buildSearchScopeMeta($facultyId, $departmentId, $programStudyId),
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('SIAKAD fallback search failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'data' => [],
                'source' => 'siakad',
                'message' => 'Pencarian mahasiswa gagal di SIAKAD.',
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'has_more' => false,
                ],
                'meta' => [
                    'cached' => false,
                    'scope' => $this->buildSearchScopeMeta($facultyId, $departmentId, $programStudyId),
                ],
            ], 502);
        }
    }

    private function buildFallbackSearchCacheKey(
        string $search,
        ?string $facultyId,
        ?string $departmentId,
        ?string $programStudyId,
        int $page,
        int $perPage
    ): string {
        return 'sigap_student_search:'.md5(json_encode([
            'search' => mb_strtolower($search),
            'faculty_id' => $facultyId,
            'department_id' => $departmentId,
            'program_study_id' => $programStudyId,
            'page' => $page,
            'per_page' => $perPage,
        ]));
    }

    private function buildSearchScopeMeta(
        ?string $facultyId,
        ?string $departmentId,
        ?string $programStudyId
    ): array {
        return [
            'faculty_id' => $facultyId,
            'department_id' => $departmentId,
            'program_study_id' => $programStudyId,
        ];
    }
}
