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
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

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

        // Apply scope filtering - SIMPLIFIED: Always use faculty fallback for now
        $faculty = $validator->faculty;
        if ($faculty) {
            $query->whereHas('student', fn($q) => $q->where('faculty', $faculty));
        }

        // Get filters
        $filters = [
            'search' => $request->input('search'),
            'level' => $request->input('level'),
            'category' => $request->input('category'),
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

        // Get statistics with scope filtering
        $statistics = $this->getStatistics($level, $facultyId, $departmentId, $programStudyId);

        // Debug: Simple count for testing
        $debugCount = StudentAchievement::whereHas('student', fn($q) => $q->where('faculty', $validator->faculty))
            ->where('validation_status', 'Menunggu')
            ->count();

        return view('validator.pending.index', compact('achievements', 'categories', 'filters', 'statistics'))
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
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

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
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

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
     * Get statistics with scope filtering - SIMPLIFIED
     */
    protected function getStatistics(?string $level, mixed $facultyId, mixed $departmentId, mixed $programStudyId): array
    {
        $validator = auth()->user();
        $faculty = $validator->faculty;

        $query = StudentAchievement::query();
        if ($faculty) {
            $query->whereHas('student', fn($q) => $q->where('faculty', $faculty));
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
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

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
