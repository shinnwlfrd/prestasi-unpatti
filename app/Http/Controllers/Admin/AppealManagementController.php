<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementAppeal;
use App\Models\AchievementCategory;
use App\Services\Admin\AppealManagementService;
use Illuminate\Http\Request;

class AppealManagementController extends Controller
{
    public function __construct(
        protected AppealManagementService $appealManagementService
    ) {}

    /**
     * Display pending appeals
     */
    public function index(Request $request)
    {
        // Get filters
        $filters = [
            'search' => $request->input('search'),
            'faculty' => $request->input('faculty'),
            'category' => $request->input('category'),
            'status' => $request->input('status', 'pending'),
        ];

        // Get appeals
        if ($filters['status'] === 'all') {
            $appeals = AchievementAppeal::with([
                'studentAchievement.student',
                'studentAchievement.achievement.category',
                'studentAchievement.facultyValidator',
                'reviewer',
            ])
                ->when($filters['search'], function ($q) use ($filters) {
                    $q->where(function ($query) use ($filters) {
                        $query->where('appeal_reason', 'like', "%{$filters['search']}%")
                            ->orWhereHas('studentAchievement', function ($sq) use ($filters) {
                                $sq->where('event_name', 'like', "%{$filters['search']}%")
                                   ->orWhere('student_id', 'like', "%{$filters['search']}%");
                            })
                            ->orWhereHas('studentAchievement.student', function ($sq) use ($filters) {
                                $sq->where('name', 'like', "%{$filters['search']}%");
                            });
                    });
                })
                ->when($filters['faculty'], function ($q) use ($filters) {
                    $q->whereHas('studentAchievement.student', fn($sq) => $sq->where('faculty', $filters['faculty']));
                })
                ->when($filters['category'], function ($q) use ($filters) {
                    $q->whereHas('studentAchievement.achievement', fn($sq) => $sq->where('category_id', $filters['category']));
                })
                ->orderBy('submitted_at', 'desc')
                ->paginate(20)
                ->withQueryString();
        } else {
            $appeals = $this->appealManagementService->getPendingAppeals($filters, 20);
        }

        // Get categories for filter
        $categories = AchievementCategory::where('is_active', true)->orderBy('order')->get();

        // Get unique faculties for filter
        $faculties = \App\Models\Student::select('faculty')
            ->distinct()
            ->whereNotNull('faculty')
            ->orderBy('faculty')
            ->pluck('faculty');

        // Get statistics
        $stats = $this->appealManagementService->getStatistics();

        return view('admin.appeals.index', compact('appeals', 'categories', 'faculties', 'filters', 'stats'));
    }

    /**
     * Show appeal detail for review
     */
    public function show(AchievementAppeal $appeal)
    {
        // Load relationships
        $appeal->load([
            'studentAchievement.student',
            'studentAchievement.achievement.category',
            'studentAchievement.documents.verifier',
            'studentAchievement.validationLogs.validator',
            'studentAchievement.facultyValidator',
            'reviewer',
        ]);

        return view('admin.appeals.show', compact('appeal'));
    }

    /**
     * Review appeal (approve/reject)
     */
    public function review(Request $request, AchievementAppeal $appeal)
    {
        // Validate request
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|string|max:1000',
        ]);

        $admin = auth()->user();

        try {
            $success = match ($request->action) {
                'approve' => $this->appealManagementService->approveAppeal(
                    $appeal,
                    $admin,
                    $request->notes
                ),
                'reject' => $this->appealManagementService->rejectAppeal(
                    $appeal,
                    $admin,
                    $request->rejection_reason
                ),
                default => false,
            };

            if ($success) {
                $message = match ($request->action) {
                    'approve' => 'Banding disetujui. Prestasi akan direview ulang oleh fakultas.',
                    'reject' => 'Banding ditolak. Mahasiswa harus memperbaiki sesuai catatan revisi.',
                    default => 'Banding berhasil diproses.',
                };

                return redirect()->route('admin.appeals.index')->with('success', $message);
            }

            return back()->with('error', 'Gagal memproses banding.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Get appeal statistics (AJAX)
     */
    public function statistics(Request $request)
    {
        $periodId = $request->input('period_id');
        $stats = $this->appealManagementService->getStatistics($periodId);

        return response()->json($stats);
    }
}
