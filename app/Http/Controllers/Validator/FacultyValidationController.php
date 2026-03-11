<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use App\Models\AchievementCategory;
use App\Models\ValidationLog;
use App\Services\Validator\FacultyValidationService;
use Illuminate\Http\Request;

class FacultyValidationController extends Controller
{
    public function __construct(
        protected FacultyValidationService $facultyValidationService
    ) {
    }

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

        // Get scope from session (set by middleware)
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

        // Debug: Add a simple test to see if controller is called
        if ($request->has('debug')) {
            dd([
                'validator' => $validator->name,
                'faculty' => $validator->faculty,
                'level' => $level,
                'faculty_id' => $facultyId,
                'total_achievements' => StudentAchievement::where('validation_status', 'Menunggu')->count(),
                'faculty_achievements' => StudentAchievement::whereHas('student', fn($q) => $q->where('faculty', $validator->faculty))->where('validation_status', 'Menunggu')->count(),
            ]);
        }

        // Build query based on access level
        $query = StudentAchievement::with(['student', 'achievement.category', 'documents'])
            ->whereNull('deleted_at') // Exclude soft-deleted achievements
            ->where(function ($q) {
                // Support both old and new status systems
                $q->facultyPending() // New system: submitted, faculty_review
                    ->orWhere('validation_status', 'Menunggu'); // Old system: Menunggu
            });

        // Apply scope filtering based on operator level
        if ($level === 'university') {
            // University level operator can see all faculties - no filtering
        } elseif ($level === 'faculty' && $facultyId) {
            $query->whereHas('student', fn($q) => $q->where('faculty_id', $facultyId));
        } elseif ($level === 'department' && $departmentId) {
            $query->whereHas('student', fn($q) => $q->where('department_id', $departmentId));
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->whereHas('student', fn($q) => $q->where('program_study_id', $programStudyId));
        } else {
            // Fallback to old method - check faculty string
            $faculty = $validator->faculty;
            if ($faculty) {
                $query->whereHas('student', fn($q) => $q->where('faculty', $faculty));
            }
        }

        // Get filters
        $filters = [
            'search' => $request->input('search'),
            'level' => $request->input('level'),
            'category' => $request->input('category'),
            'faculty' => $request->input('faculty'), // New: faculty filter for super validator
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort_date' => $request->input('sort_date', 'oldest'), // Default to oldest
        ];

        // Apply filters
        $achievements = $query
            ->when($filters['search'], function ($q) use ($filters) {
                $q->where(function ($query) use ($filters) {
                    $query->where('event_name', 'like', "%{$filters['search']}%")
                        ->orWhere('student_id', 'like', "%{$filters['search']}%")
                        ->orWhereHas('student', function ($sq) use ($filters) {
                            $sq->where('name', 'like', "%{$filters['search']}%");
                        });
                });
            })
            ->when($filters['level'], fn($q) => $q->where('level', $filters['level']))
            ->when($filters['category'], function ($q) use ($filters) {
                $q->whereHas('achievement', fn($sq) => $sq->where('category_id', $filters['category']));
            })
            ->when($filters['faculty'], function ($q) use ($filters) {
                // Filter by faculty (for super validator)
                $q->whereHas('student', fn($sq) => $sq->where('faculty_id', $filters['faculty']));
            })
            ->when($filters['date_from'], fn($q) => $q->whereDate('submitted_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('submitted_at', '<=', $filters['date_to']))
            ->when($filters['sort_date'] === 'newest', function ($q) {
                $q->orderBy('submitted_at', 'desc');
            }, function ($q) {
                $q->orderBy('submitted_at', 'asc'); // Default to oldest
            })
            ->paginate(20)
            ->withQueryString()
            ->appends(['_t' => time()]); // Cache busting: prevent browser from caching pagination

        // Get categories for filter
        $categories = AchievementCategory::where('is_active', true)->orderBy('order')->get();
        
        // Get faculties for filter (only for super validator with university level)
        $faculties = collect();
        if ($level === 'university') {
            $faculties = \App\Models\Student::select('faculty_id', \Illuminate\Support\Facades\DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->orderBy('faculty')
                ->get()
                ->map(function ($item) {
                    $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();
                    return (object)[
                        'id' => $item->faculty_id,
                        'name' => $nameMap[$item->faculty_id] ?? $item->faculty
                    ];
                });
        }

        // Get statistics with scope filtering
        $statistics = $this->getStatistics($level, $facultyId, $departmentId, $programStudyId);

        // Debug: Simple count for testing
        $debugCount = StudentAchievement::whereHas('student', fn($q) => $q->where('faculty', $validator->faculty))
            ->where('validation_status', 'Menunggu')
            ->count();

        return view('validator.pending.index', compact('achievements', 'categories', 'faculties', 'filters', 'statistics'))
            ->with('debugCount', $debugCount);
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
        } elseif (!$level) {
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
        } elseif (!$level) {
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
        ]);

        try {
            $success = match ($request->action) {
                'approve' => $this->facultyValidationService->approve(
                    $achievement,
                    $validator,
                    $request->notes
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
            $query->whereHas('student', fn($q) => $q->where('faculty_id', $facultyId));
        } elseif ($level === 'department' && $departmentId) {
            $query->whereHas('student', fn($q) => $q->where('department_id', $departmentId));
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->whereHas('student', fn($q) => $q->where('program_study_id', $programStudyId));
        } else {
            // Fallback to old method - check faculty string
            $faculty = $validator->faculty;
            if ($faculty) {
                $query->whereHas('student', fn($q) => $q->where('faculty', $faculty));
            }
        }

        // Support both old and new status systems
        $pending = (clone $query)->where(function ($q) {
            $q->facultyPending() // New system: submitted, faculty_review
                ->orWhere('validation_status', 'Menunggu'); // Old system: Menunggu
        })->count();

        $approvedToday = (clone $query)->where(function ($q) {
            $q->where('validation_status', StudentAchievement::STATUS_FACULTY_APPROVED)
                ->whereDate('faculty_validated_at', today())
                ->orWhere(function ($sq) {
                    $sq->where('validation_status', 'Disetujui')
                        ->whereDate('updated_at', today());
                });
        })->count();

        $revisionRequested = (clone $query)->where(function ($q) {
            $q->where('validation_status', StudentAchievement::STATUS_FACULTY_REVISION)
                ->orWhere('validation_status', 'Revisi');
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
        } elseif (!$level) {
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
}
