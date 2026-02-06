<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use App\Models\AchievementCategory;
use App\Models\SKDocument;
use App\Services\Admin\UniversityValidationService;
use Illuminate\Http\Request;

class UniversityValidationController extends Controller
{
    public function __construct(
        protected UniversityValidationService $universityValidationService
    ) {}

    /**
     * Display achievements pending university validation
     */
    public function index(Request $request)
    {
        // Get filters
        $filters = [
            'search' => $request->input('search'),
            'faculty' => $request->input('faculty'),
            'level' => $request->input('level'),
            'category' => $request->input('category'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        // Get achievements pending university review
        $achievements = StudentAchievement::with([
            'student',
            'achievement.category',
            'facultyValidator',
            'documents'
        ])
            ->universityPending()
            ->when($filters['search'], function ($q) use ($filters) {
                $q->where(function ($query) use ($filters) {
                    $query->where('event_name', 'like', "%{$filters['search']}%")
                        ->orWhere('student_id', 'like', "%{$filters['search']}%")
                        ->orWhereHas('student', function ($sq) use ($filters) {
                            $sq->where('name', 'like', "%{$filters['search']}%");
                        });
                });
            })
            ->when($filters['faculty'], function ($q) use ($filters) {
                $q->byFaculty($filters['faculty']);
            })
            ->when($filters['level'], fn($q) => $q->where('level', $filters['level']))
            ->when($filters['category'], function ($q) use ($filters) {
                $q->whereHas('achievement', fn($sq) => $sq->where('category_id', $filters['category']));
            })
            ->when($filters['date_from'], fn($q) => $q->whereDate('faculty_validated_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('faculty_validated_at', '<=', $filters['date_to']))
            ->orderBy('faculty_validated_at', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Get categories for filter
        $categories = AchievementCategory::where('is_active', true)->orderBy('order')->get();

        // Get unique faculties for filter
        $faculties = \App\Models\Student::select('faculty')
            ->distinct()
            ->whereNotNull('faculty')
            ->orderBy('faculty')
            ->pluck('faculty');

        // Get statistics
        $stats = $this->universityValidationService->getStatistics();

        return view('admin.university.index', compact('achievements', 'categories', 'faculties', 'filters', 'stats'));
    }

    /**
     * Show achievement detail for university validation
     */
    public function show(StudentAchievement $achievement)
    {
        // Load relationships
        $achievement->load([
            'student',
            'achievement.category',
            'documents.verifier',
            'validationLogs.validator',
            'facultyValidator',
            'checklist',
        ]);

        // Get available SK documents
        $skDocuments = SKDocument::orderBy('issued_date', 'desc')->get();

        return view('admin.university.show', compact('achievement', 'skDocuments'));
    }

    /**
     * Process university validation (approve with SK / reject)
     */
    public function validate(Request $request, StudentAchievement $achievement)
    {
        // Validate request
        $request->validate([
            'action' => 'required|in:approve,reject',
            'sk_id' => 'required_if:action,approve|exists:sk_documents,id',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|string|max:1000',
        ]);

        $admin = auth()->user();

        try {
            $success = match ($request->action) {
                'approve' => $this->universityValidationService->approve(
                    $achievement,
                    $admin,
                    $request->sk_id,
                    $request->notes
                ),
                'reject' => $this->universityValidationService->reject(
                    $achievement,
                    $admin,
                    $request->rejection_reason
                ),
                default => false,
            };

            if ($success) {
                $message = match ($request->action) {
                    'approve' => 'Prestasi berhasil disetujui dan SK telah di-assign. Prestasi resmi tercatat.',
                    'reject' => 'Prestasi berhasil ditolak di tingkat universitas.',
                    default => 'Status berhasil diperbarui.',
                };

                return redirect()->route('admin.university.index')->with('success', $message);
            }

            return back()->with('error', 'Gagal memproses validasi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Start review process (optional step)
     */
    public function startReview(StudentAchievement $achievement)
    {
        $admin = auth()->user();

        try {
            $this->universityValidationService->startReview($achievement, $admin);

            return redirect()->route('admin.university.show', $achievement)
                ->with('success', 'Review universitas dimulai.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk assign SK to multiple achievements
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'achievement_ids' => 'required|array|min:1',
            'achievement_ids.*' => 'exists:student_achievements,sa_id',
            'sk_id' => 'required|exists:sk_documents,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $admin = auth()->user();

        try {
            $results = $this->universityValidationService->bulkAssignSK(
                $request->achievement_ids,
                $request->sk_id,
                $admin,
                $request->notes
            );

            $message = "Bulk assignment selesai. Berhasil: {$results['success']}, Gagal: {$results['failed']}";

            if ($results['failed'] > 0) {
                $message .= ". Errors: " . implode(', ', array_slice($results['errors'], 0, 3));
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
