<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Services\SigapApiService;
use App\Services\Validator\FacultyValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FacultyValidationController extends Controller
{
    public function __construct(
        protected FacultyValidationService $facultyValidationService
    ) {}

    /**
     * Display pending achievements for faculty validation
     */
    public function index(Request $request)
    {
        // Prevent browser caching to ensure fresh data after approval
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $validator = auth()->user();
        $currentRole = $validator->getCurrentRole();

        // Get scope from current role or session as fallback
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null);
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id') ?: null);
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null);

        // Build query based on access level
        $query = StudentAchievement::with(['student', 'achievement.category', 'documents'])
            ->whereNull('deleted_at')
            ->whereIn('validation_status', StudentAchievement::getFacultyPendingStatuses());

        $this->applyScopeFiltering($query, $level, $facultyId, $departmentId, $programStudyId, $validator);

        // Apply filters
        $filters = $request->only(['search', 'level', 'category', 'faculty', 'date_from', 'date_to', 'sort_date']);
        $filters['sort_date'] = $filters['sort_date'] ?? 'oldest';

        $achievements = $this->getFilteredAchievements($query, $filters);

        return view('validator.pending.index', [
            'achievements' => $achievements,
            'categories' => AchievementCategory::where('is_active', true)->orderBy('order')->get(),
            'levels' => AchievementLevel::where('is_active', true)->orderBy('order')->get(),
            'faculties' => $level === 'university' ? $this->getFacultiesForFilter() : collect(),
            'filters' => $filters,
            'statistics' => $this->getStatistics($level, $facultyId, $departmentId, $programStudyId),
            'debugCount' => $this->getDebugCount($validator),
        ]);
    }

    private function applyScopeFiltering($query, $level, $facultyId, $departmentId, $programStudyId, $validator)
    {
        if ($level === 'university') {
            return;
        }

        if ($level === 'faculty' && $facultyId) {
            $query->whereHas('student', fn ($q) => $q->where('faculty_id', $facultyId));
        } elseif ($level === 'department' && $departmentId) {
            $query->whereHas('student', fn ($q) => $q->where('department_id', $departmentId));
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->whereHas('student', fn ($q) => $q->where('program_study_id', $programStudyId));
        } elseif ($validator->faculty) {
            $query->whereHas('student', fn ($q) => $q->where('faculty', $validator->faculty));
        }
    }

    private function getFilteredAchievements($query, $filters)
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('event_name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
            ->when($filters['category'] ?? null, fn ($q, $cat) => $q->whereHas('achievement', fn ($sq) => $sq->where('category_id', $cat)))
            ->when($filters['faculty'] ?? null, fn ($q, $fac) => $q->whereHas('student', fn ($sq) => $sq->where('faculty_id', $fac)))
            ->when($filters['date_from'] ?? null, fn ($q, $from) => $q->whereDate('submitted_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn ($q, $to) => $q->whereDate('submitted_at', '<=', $to))
            ->orderBy('submitted_at', ($filters['sort_date'] ?? 'oldest') === 'newest' ? 'desc' : 'asc')
            ->paginate(20)
            ->withQueryString()
            ->appends(['_t' => time()]);
    }

    private function getFacultiesForFilter()
    {
        $nameMap = app(SigapApiService::class)->getUnitNameMap();

        return Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
            ->whereNotNull('faculty_id')
            ->groupBy('faculty_id')
            ->orderBy('faculty')
            ->get()
            ->map(fn ($item) => (object) [
                'id' => $item->faculty_id,
                'name' => $nameMap[$item->faculty_id] ?? $item->faculty,
            ]);
    }

    private function getDebugCount($validator)
    {
        return StudentAchievement::whereHas('student', fn ($q) => $q->where('faculty', $validator->faculty))
            ->whereIn('validation_status', StudentAchievement::getFacultyPendingStatuses())
            ->count();
    }

    /**
     * Show achievement detail for validation
     */
    public function show(StudentAchievement $achievement)
    {
        $validator = auth()->user();

        // Get scope from session
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

        // Check access based on level
        if ($level === 'university') {
            // University level can access all
        } elseif ($level === 'faculty' && $facultyId && $achievement->student->faculty_id !== $facultyId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
        } elseif ($level === 'department' && $departmentId && $achievement->student->department_id !== $departmentId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari jurusan lain.');
        } elseif ($level === 'program_study' && $programStudyId && $achievement->student->program_study_id !== $programStudyId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari prodi lain.');
        } elseif (! $level) {
            // Fallback to old method - check faculty string
            if ($validator->faculty && $achievement->student->faculty !== $validator->faculty) {
                abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
            }
        }

        // Load relationships
        $achievement->load([
            'student',
            'achievement.category',
            'documents.verifier',
            'validationLogs.validator',
            'checklist',
            'facultyValidator',
        ]);

        return view('validator.pending.show', compact('achievement'));
    }

    /**
     * Process validation (approve/reject/revision)
     */
    public function validate(Request $request, StudentAchievement $achievement)
    {
        $validator = auth()->user();

        // Get scope from session
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

        // Check access based on level
        if ($level === 'university') {
            // University level can access all
        } elseif ($level === 'faculty' && $facultyId && $achievement->student->faculty_id !== $facultyId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
        } elseif ($level === 'department' && $departmentId && $achievement->student->department_id !== $departmentId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari jurusan lain.');
        } elseif ($level === 'program_study' && $programStudyId && $achievement->student->program_study_id !== $programStudyId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari prodi lain.');
        } elseif (! $level) {
            // Fallback to old method - check faculty string
            if ($validator->faculty && $achievement->student->faculty !== $validator->faculty) {
                abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
            }
        }

        // Validate request
        $request->validate([
            'action' => 'required|in:approve,reject,request_revision',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
            'revision_reason' => 'required_if:action,request_revision|nullable|string|max:1000',
            'checklist' => 'nullable|array',
        ]);

        try {
            $success = match ($request->action) {
                'approve' => $this->facultyValidationService->approve(
                    $achievement,
                    $validator,
                    $request->notes,
                    $request->input('checklist', [])
                ),
                'reject' => $this->facultyValidationService->reject(
                    $achievement,
                    $validator,
                    $request->rejection_reason
                ),
                'request_revision' => $this->facultyValidationService->requestRevision(
                    $achievement,
                    $validator,
                    $request->revision_reason
                ),
                default => false,
            };

            if ($success) {
                $message = match ($request->action) {
                    'approve' => 'Prestasi berhasil disetujui dan dikirim ke Admin Universitas untuk review final.',
                    'reject' => 'Prestasi berhasil ditolak.',
                    'request_revision' => 'Permintaan revisi berhasil dikirim ke mahasiswa.',
                    default => 'Status berhasil diperbarui.',
                };

                return redirect()->route('validator.pending.index')->with('success', $message);
            }

            return back()->with('error', 'Gagal memproses validasi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Get statistics with scope filtering
     */
    protected function getStatistics(?string $level, mixed $facultyId, mixed $departmentId, mixed $programStudyId): array
    {
        $validator = auth()->user();

        $query = StudentAchievement::query();

        // Apply scope filtering based on operator level
        if ($level === 'university') {
            // University level operator can see all faculties - no filtering
        } elseif ($level === 'faculty' && $facultyId) {
            $query->whereHas('student', fn ($q) => $q->where('faculty_id', $facultyId));
        } elseif ($level === 'department' && $departmentId) {
            $query->whereHas('student', fn ($q) => $q->where('department_id', $departmentId));
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->whereHas('student', fn ($q) => $q->where('program_study_id', $programStudyId));
        } else {
            // Fallback to old method - check faculty string
            $faculty = $validator->faculty;
            if ($faculty) {
                $query->whereHas('student', fn ($q) => $q->where('faculty', $faculty));
            }
        }

        $pending = (clone $query)->whereIn('validation_status', StudentAchievement::getFacultyPendingStatuses())->count();

        $approvedToday = (clone $query)->where(function ($q) {
            $q->where('validation_status', StudentAchievement::STATUS_FACULTY_APPROVED)
                ->whereDate('faculty_validated_at', today())
                ->orWhere(function ($sq) {
                    $sq->where('validation_status', StudentAchievement::STATUS_APPROVED)
                        ->whereDate('updated_at', today());
                });
        })->count();

        $revisionRequested = (clone $query)->where(function ($q) {
            $q->whereIn('validation_status', StudentAchievement::getValidationDecisionStatusGroups()['revision']);
        })->count();

        return [
            'pending' => $pending,
            'approved_today' => $approvedToday,
            'revision_requested' => $revisionRequested,
            'avg_review_time_hours' => 0, // Simplified for now
        ];
    }

    /**
     * Start review process (optional step)
     */
    public function startReview(StudentAchievement $achievement)
    {
        $validator = auth()->user();

        // Get scope from session
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

        // Check access based on level
        if ($level === 'university') {
            // University level can access all
        } elseif ($level === 'faculty' && $achievement->student->faculty_id !== $facultyId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
        } elseif ($level === 'department' && $achievement->student->department_id !== $departmentId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari jurusan lain.');
        } elseif ($level === 'program_study' && $achievement->student->program_study_id !== $programStudyId) {
            abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari prodi lain.');
        } elseif (! $level) {
            // Fallback to old method
            if ($achievement->student->faculty !== $validator->faculty) {
                abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
            }
        }

        try {
            $this->facultyValidationService->startReview($achievement, $validator);

            return redirect()->route('validator.pending.show', $achievement)
                ->with('success', 'Review dimulai. Prestasi ditandai sebagai sedang direview.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get achievement data for modal view
     */
    public function getAchievementData(StudentAchievement $achievement)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        // Get scope from current role or session as fallback
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null);
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id') ?: null);
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null);

        // Access control check
        if ($level === 'university') {
            // University level can access all
        } elseif ($level === 'faculty' && $facultyId && $achievement->student->faculty_id !== $facultyId) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        } elseif ($level === 'department' && $departmentId && $achievement->student->department_id !== $departmentId) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        } elseif ($level === 'program_study' && $programStudyId && $achievement->student->program_study_id !== $programStudyId) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        } elseif (! $level && $user->faculty && $achievement->student->faculty !== $user->faculty) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Load all necessary relationships
        $achievement->load([
            'student',
            'achievement.category',
            'documents',
        ]);

        return response()->json([
            'sa_id' => $achievement->sa_id,
            'event_name' => $achievement->event_name,
            'level' => $achievement->level,
            'organizer' => $achievement->organizer,
            'event_date' => $achievement->event_date instanceof Carbon
                ? $achievement->event_date->format('d M Y')
                : ($achievement->event_date ? Carbon::parse($achievement->event_date)->format('d M Y') : null),
            'description' => $achievement->description,
            'ranking' => $achievement->ranking,
            'validation_status' => $achievement->validation_status,
            'submitted_at' => $achievement->submitted_at instanceof Carbon
                ? $achievement->submitted_at->format('d M Y H:i')
                : ($achievement->submitted_at ? Carbon::parse($achievement->submitted_at)->format('d M Y H:i') : null),
            'student' => [
                'name' => $achievement->student?->name ?? 'N/A',
                'student_id' => $achievement->student_id,
                'faculty' => $achievement->student?->faculty ?? 'N/A',
            ],
            'documents' => $achievement->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'type_name' => $doc->type_name ?? $doc->document_type,
                    'file_name' => $doc->file_name,
                    'file_path' => $doc->file_path,
                    'status' => $doc->status,
                ];
            }),
        ]);
    }
}
