<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AchievementExport;
use App\Models\StudentAchievement;
use App\Services\Exports\AchievementExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        protected AchievementExportService $exportService
    ) {}

    public function exportAchievements(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $currentRole = $user->getCurrentRole();

        if (! $currentRole) {
            abort(403, 'No active role found');
        }

        $export = $this->exportService->queueScopedExport($user, $currentRole, $request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Export dimasukkan ke antrean.',
                'export' => $this->exportService->serialize($export, 'api.export.download', 'api.export.show'),
            ], 202);
        }

        return redirect()->back()->with('status', 'Export sedang diproses di background. Unduh file dari panel status export.');
    }

    public function recent(Request $request): JsonResponse
    {
        $currentRole = $request->user()->getCurrentRole();

        if (! $currentRole) {
            return response()->json(['data' => []]);
        }

        $exports = $this->exportService
            ->getRecentForUser($request->user(), $currentRole)
            ->map(fn (AchievementExport $export) => $this->exportService->serialize($export, 'api.export.download', 'api.export.show'));

        return response()->json(['data' => $exports]);
    }

    public function show(Request $request, AchievementExport $achievementExport): JsonResponse
    {
        $currentRole = $request->user()->getCurrentRole();
        abort_unless($this->exportService->authorizeDownload($achievementExport, $request->user(), $currentRole) || $achievementExport->user_id === $request->user()->id, 403);

        return response()->json([
            'data' => $this->exportService->serialize($achievementExport, 'api.export.download', 'api.export.show'),
        ]);
    }

    public function download(Request $request, AchievementExport $achievementExport): StreamedResponse
    {
        $currentRole = $request->user()->getCurrentRole();
        abort_unless($this->exportService->authorizeDownload($achievementExport, $request->user(), $currentRole), 403);

        return Storage::disk($achievementExport->disk)->download(
            $achievementExport->file_path,
            $achievementExport->file_name
        );
    }

    public function exportStatistics(Request $request): JsonResponse
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        if (! $currentRole) {
            return response()->json(['error' => 'No active role found'], 403);
        }

        $query = StudentAchievement::query();

        if (! $currentRole->isUniversityLevel()) {
            $query->whereHas('student', function ($q) use ($currentRole) {
                if ($currentRole->isFacultyLevel()) {
                    $q->where('faculty_id', $currentRole->faculty_id);
                } elseif ($currentRole->isDepartmentLevel()) {
                    $q->where('department_id', $currentRole->department_id);
                } elseif ($currentRole->isProgramStudyLevel()) {
                    $q->where('program_study_id', $currentRole->program_study_id);
                }
            });
        }

        $byStatus = (clone $query)
            ->select('validation_status', DB::raw('count(*) as total'))
            ->groupBy('validation_status')
            ->get()
            ->pluck('total', 'validation_status');

        $byCategory = (clone $query)
            ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->select('achievement_categories.name', DB::raw('count(*) as total'))
            ->groupBy('achievement_categories.name')
            ->get();

        $byLevel = (clone $query)
            ->select('student_achievements.level as name', DB::raw('count(*) as total'))
            ->groupBy('student_achievements.level')
            ->get();

        $monthExpression = $this->getMonthExpression();
        $monthlyTrend = (clone $query)
            ->select(
                DB::raw($monthExpression.' as month'),
                DB::raw('count(*) as total')
            )
            ->whereNotNull('submitted_at')
            ->groupBy(DB::raw($monthExpression))
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'scope' => [
                'level' => $currentRole->level,
                'name' => $currentRole->getScopeDescription(),
            ],
            'statistics' => [
                'by_status' => $byStatus,
                'by_category' => $byCategory,
                'by_level' => $byLevel,
                'monthly_trend' => $monthlyTrend,
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function getMonthExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR(submitted_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', submitted_at)",
            default => "DATE_FORMAT(submitted_at, '%Y-%m')",
        };
    }
}
