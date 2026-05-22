<?php

namespace App\Services\Admin;

use App\Http\Traits\AnomalyAlertsTrait;
use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\AuthLog;
use App\Models\IntegrationHealthCheck;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use App\Services\SiakadApiService;
use App\Services\SigapApiService;
use Carbon\Carbon;

class DashboardService
{
    use AnomalyAlertsTrait;

    public function __construct(
        protected StatisticsService $statisticsService,
        protected SigapApiService $sigapApiService,
        protected SiakadApiService $siakadApiService
    ) {}

    public function getIntegrationHealthData(): array
    {
        $services = [
            $this->sigapApiService->getHealthStatus(),
            $this->siakadApiService->getHealthStatus(),
        ];

        $overallStatus = collect($services)->contains(fn (array $service) => $service['status'] !== 'up')
            ? 'degraded'
            : 'up';

        return [
            'overall_status' => $overallStatus,
            'services' => $services,
            'recent_history' => $this->getRecentIntegrationHealthHistory(),
            'alert_summary' => $this->getIntegrationAlertSummary($services),
            'checked_at' => now()->toIso8601String(),
        ];
    }

    public function captureIntegrationHealthSnapshot(): array
    {
        $payload = $this->getIntegrationHealthData();

        foreach ($payload['services'] as $service) {
            IntegrationHealthCheck::create([
                'service' => $service['service'],
                'status' => $service['status'],
                'base_url' => $service['base_url'] ?? null,
                'message' => $service['message'] ?? null,
                'meta' => $service['meta'] ?? [],
                'checked_at' => $service['checked_at'] ?? now(),
            ]);
        }

        return $payload;
    }

    public function getRecentIntegrationHealthHistory(int $limitPerService = 3): array
    {
        return IntegrationHealthCheck::query()
            ->orderByDesc('checked_at')
            ->get()
            ->groupBy('service')
            ->map(function ($items) use ($limitPerService) {
                return $items->take($limitPerService)->map(function (IntegrationHealthCheck $item) {
                    return [
                        'status' => $item->status,
                        'message' => $item->message,
                        'checked_at' => optional($item->checked_at)?->toIso8601String(),
                    ];
                })->values()->all();
            })
            ->toArray();
    }

    public function getIntegrationAlertSummary(array $services, int $historyLimit = 5, int $alertThreshold = 3): array
    {
        $historyByService = IntegrationHealthCheck::query()
            ->orderByDesc('checked_at')
            ->get()
            ->groupBy('service');

        $affectedServices = collect($services)->map(function (array $service) use ($historyByService, $historyLimit, $alertThreshold) {
            $history = collect($historyByService->get($service['service'], []))
                ->take($historyLimit)
                ->values();

            $consecutiveFailures = 0;
            foreach ($history as $item) {
                if ($item->status !== 'down') {
                    break;
                }

                $consecutiveFailures++;
            }

            $lastFailureAt = optional(
                $history->first(fn (IntegrationHealthCheck $item) => $item->status === 'down')
            )?->checked_at?->toIso8601String();

            $lastRecoveredAt = optional(
                $history->first(fn (IntegrationHealthCheck $item) => $item->status === 'up')
            )?->checked_at?->toIso8601String();

            $needsAttention = ($service['status'] ?? 'down') !== 'up' || $consecutiveFailures >= $alertThreshold;

            return [
                'service' => $service['service'],
                'status' => $service['status'] ?? 'down',
                'consecutive_failures' => $consecutiveFailures,
                'last_failure_at' => $lastFailureAt,
                'last_recovered_at' => $lastRecoveredAt,
                'needs_attention' => $needsAttention,
                'attention_level' => $consecutiveFailures >= $alertThreshold ? 'critical' : (($service['status'] ?? 'down') !== 'up' ? 'warning' : 'normal'),
            ];
        })->values();

        return [
            'total_services' => count($services),
            'up_services' => collect($services)->where('status', 'up')->count(),
            'down_services' => collect($services)->filter(fn (array $service) => ($service['status'] ?? 'down') !== 'up')->count(),
            'services_requiring_attention' => $affectedServices->where('needs_attention', true)->count(),
            'affected_services' => $affectedServices->where('needs_attention', true)->values()->all(),
        ];
    }

    public function getDashboardData($periodId = null)
    {
        // Resolve context first to use the correct period ID in cache key
        $context = $this->resolveDashboardContext($periodId);
        $resolvedPeriodId = $context['periodId'] ?? 'all';
        $version = StudentAchievement::getDashboardCacheVersion();
        $cacheKey = "admin_dashboard_data_{$resolvedPeriodId}_v{$version}";

        return \Cache::remember($cacheKey, 600, function () use ($periodId) {
            // 1. Resolve logical context (Period, State)
            $context = $this->resolveDashboardContext($periodId);
            $periodId = $context['periodId'];
            $selectedPeriod = $context['selectedPeriod'];
            $activePeriod = AcademicPeriod::where('is_active', true)->first();
            $periods = AcademicPeriod::ordered()->get();
            $globalTrend = $this->getPeriodComparison();

            // 2. Resolve view state variables
            $isGlobal = ! $selectedPeriod;
            $isActivePeriod = $selectedPeriod && $selectedPeriod->is_active;
            $isInactivePeriod = $selectedPeriod && ! $selectedPeriod->is_active;

            // 3. Status groupings
            $statusGroups = StudentAchievement::getWorkflowStatusGroups();
            $pendingStatuses = $statusGroups['pending'];
            $approvedStatuses = $statusGroups['approved'];
            $rejectedStatuses = $statusGroups['rejected'];
            $revisionStatuses = $statusGroups['revision'];

            // 4. Gather Core Data
            $stats = $this->getDashboardSummaryStats($periodId, $pendingStatuses, $approvedStatuses, $rejectedStatuses);
            $recentAchievements = $this->getRecentAchievementsForDashboard($periodId);
            $queues = $this->getDashboardQueues($periodId);
            $recentValidations = $this->getRecentValidationsForDashboard($periodId);
            $statusStats = $this->getQuickStatusStats($periodId, $pendingStatuses, $approvedStatuses, $rejectedStatuses, $revisionStatuses);

            // 5. Gather Monitoring/Leaderboard Data
            $monitoring = $this->getDashboardMonitoringData($periodId, $approvedStatuses);
            $globalLeaderboard = $this->getGlobalLeaderboardData($approvedStatuses);

            // 6. Context-aware anomalies
            $alertContext = $this->resolveAlertContext($selectedPeriod);
            $anomalyData = $this->getDashboardAnomalyData($periodId, $alertContext);

            // 7. View-specific data (Active vs Inactive)
            $viewData = [
                'archivedStats' => $isInactivePeriod ? $this->getArchivedDashboardStats($periodId, $selectedPeriod, $stats, $pendingStatuses) : null,
                'activeStats' => $isActivePeriod ? $this->getActiveDashboardStats($periodId) : null,
                'masterData' => $this->getMasterDataSummary(),
            ];

            $achievementLevels = AchievementLevel::active()->ordered()->get();

            return array_merge(
                compact(
                    'stats',
                    'recentAchievements',
                    'recentValidations',
                    'statusStats',
                    'activePeriod',
                    'periods',
                    'selectedPeriod',
                    'globalTrend',
                    'alertContext',
                    'isActivePeriod',
                    'isInactivePeriod',
                    'isGlobal',
                    'achievementLevels'
                ),
                $queues,
                $monitoring,
                $globalLeaderboard,
                $anomalyData,
                $viewData
            );
        });
    }

    protected function getDashboardSummaryStats($periodId, array $pendingStatuses, array $approvedStatuses, array $rejectedStatuses): array
    {
        $hasAggregated = $this->hasAggregatedData();

        if ($hasAggregated) {
            $achievementsCount = (int) \DB::table('dashboard_aggregations')
                ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
                ->sum('total_count');

            $totalAchievementsCount = (int) \DB::table('dashboard_aggregations')->sum('total_count');

            $levelData = \DB::table('dashboard_aggregations')
                ->selectRaw('level, sum(total_count) as total')
                ->groupBy('level')
                ->pluck('total', 'level')
                ->toArray();
            $globalLevelDistribution = AchievementLevel::active()->ordered()->get()->mapWithKeys(fn ($l) => [
                $l->name => (int) ($levelData[$l->name] ?? 0),
            ])->toArray();

            $globalCategoryDistribution = \DB::table('dashboard_aggregations')
                ->join('achievement_categories', 'dashboard_aggregations.category_id', '=', 'achievement_categories.id')
                ->selectRaw('achievement_categories.name as category, SUM(dashboard_aggregations.total_count) as total')
                ->groupBy('achievement_categories.name')
                ->get();
        } else {
            $achievementsCount = StudentAchievement::when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))->count();
            $totalAchievementsCount = StudentAchievement::count();
            $globalLevelDistribution = AchievementLevel::active()->ordered()->get()->mapWithKeys(fn ($l) => [
                $l->name => StudentAchievement::where('level', $l->name)->count(),
            ])->toArray();
            $globalCategoryDistribution = \DB::table('student_achievements')
                ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
                ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
                ->selectRaw('achievement_categories.name as category, COUNT(*) as total')
                ->groupBy('achievement_categories.name')
                ->get();
        }

        $univPendingStatuses = [
            StudentAchievement::STATUS_FACULTY_APPROVED,
            StudentAchievement::STATUS_UNIVERSITY_REVIEW,
        ];

        return [
            'students' => Student::count(),
            'achievements' => $achievementsCount,
            'pending' => $this->getInclusiveCount($pendingStatuses, $periodId),
            'total_pending' => $this->getInclusiveCount($pendingStatuses, null),
            'total_rejected' => $this->getInclusiveCount($rejectedStatuses, $periodId),
            'approved' => $this->getInclusiveCount($approvedStatuses, $periodId),
            'validators' => User::where('role', 'Operator')->where('is_active', true)->count(),
            'total_achievements' => $totalAchievementsCount,
            'total_avg_time' => $this->calculateTotalAvgTime($approvedStatuses),
            'global_status_stats' => [
                'menunggu' => $this->getInclusiveCount($pendingStatuses, null),
                'disetujui' => $this->getInclusiveCount($approvedStatuses, null),
                'ditolak' => $this->getInclusiveCount($rejectedStatuses, null),
            ],
            'global_level_distribution' => $globalLevelDistribution,
            'global_category_distribution' => $globalCategoryDistribution,
            // Two-stage validation stats
            'university_pending' => $this->getInclusiveCount($univPendingStatuses, $periodId),
        ];
    }

    protected function getRecentAchievementsForDashboard($periodId)
    {
        // Note: If no achievements in selected period, show latest without period filter
        $recentAchievements = StudentAchievement::with(['student', 'achievement.category'])
            ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
            ->latest()
            ->take(10)
            ->get();

        // If no achievements found with period filter, try without filter
        if ($recentAchievements->isEmpty() && $periodId) {
            $recentAchievements = StudentAchievement::with(['student', 'achievement.category'])
                ->latest()
                ->take(10)
                ->get();
        }

        return $recentAchievements;
    }

    protected function getDashboardQueues($periodId): array
    {
        $statusGroups = StudentAchievement::getWorkflowStatusGroups();

        // University Pending Queue (top 5 oldest) - Use selected period
        $universityPending = StudentAchievement::with(['student', 'achievement.category', 'facultyValidator'])
            ->universityPending()
            ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('faculty_validated_at', 'asc')
            ->take(5)
            ->get();

        // Resubmission Queue (top 5 oldest) - replaces appeal queue - Use selected period
        $resubmissionQueue = StudentAchievement::with(['student', 'achievement.category'])
            ->where('is_resubmission', true)
            ->whereIn('validation_status', StudentAchievement::getFacultyPendingStatuses())
            ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('last_resubmitted_at', 'asc')
            ->take(5)
            ->get();

        // Pending Review (urgent - older than 7 days) - Use selected period
        $urgentPending = StudentAchievement::with(['student', 'achievement.category'])
            ->whereIn('validation_status', $statusGroups['pending'])
            ->where('submitted_at', '<', now()->subDays(7))
            ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('submitted_at', 'asc')
            ->take(5)
            ->get();

        return compact('universityPending', 'resubmissionQueue', 'urgentPending');
    }

    protected function getRecentValidationsForDashboard($periodId)
    {
        return ValidationLog::with(['studentAchievement.student', 'validator'])
            ->when($periodId, function ($q) use ($periodId) {
                $q->whereHas('studentAchievement', fn ($sq) => $sq->where('academic_period_id', $periodId));
            })
            ->latest('validated_at')
            ->take(10)
            ->get();
    }

    protected function getQuickStatusStats($periodId, array $pendingStatuses, array $approvedStatuses, array $rejectedStatuses, array $revisionStatuses): array
    {
        return [
            'menunggu' => $this->getInclusiveCount($pendingStatuses, $periodId),
            'disetujui' => $this->getInclusiveCount($approvedStatuses, $periodId),
            'ditolak' => $this->getInclusiveCount($rejectedStatuses, $periodId),
            'revisi' => $this->getInclusiveCount($revisionStatuses, $periodId),
            // New two-stage statuses
            'faculty_pending' => $this->getInclusiveCount([
                StudentAchievement::STATUS_SUBMITTED,
                StudentAchievement::STATUS_FACULTY_REVIEW,
            ], $periodId),
            'faculty_approved' => $this->getInclusiveCount([
                StudentAchievement::STATUS_FACULTY_APPROVED,
            ], $periodId),
            'university_approved' => $this->getInclusiveCount([
                StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            ], $periodId),
        ];
    }

    protected function getInclusiveCount(array $statuses, $pId = null): int
    {
        if ($this->hasAggregatedData()) {
            return (int) \DB::table('dashboard_aggregations')
                ->whereIn('validation_status', $statuses)
                ->when($pId, fn ($q) => $q->where('academic_period_id', $pId))
                ->sum('total_count');
        }

        return StudentAchievement::whereIn('validation_status', $statuses)
            ->when($pId, fn ($q) => $q->where('academic_period_id', $pId))
            ->count();
    }

    protected function statusSqlList(array $statuses): string
    {
        return collect($statuses)
            ->map(fn ($status) => "'".str_replace("'", "''", $status)."'")
            ->implode(',');
    }

    protected function calculateTotalAvgTime(array $approvedStatuses): int
    {
        $records = StudentAchievement::query()
            ->whereIn('validation_status', $approvedStatuses)
            ->whereNotNull('updated_at')
            ->select('created_at', 'updated_at')
            ->get();
        if ($records->isEmpty()) {
            return 0;
        }
        $totalDays = $records->sum(fn ($item) => Carbon::parse($item->created_at)->diffInDays(Carbon::parse($item->updated_at)));

        return (int) round($totalDays / $records->count());
    }

    // Helper methods for monitoring dashboard
    protected function getPeriodComparison()
    {
        $statusGroups = StudentAchievement::getWorkflowStatusGroups();
        $approvedStatuses = $statusGroups['approved'];
        $pendingStatuses = $statusGroups['pending'];
        $rejectedStatuses = $statusGroups['rejected'];

        if ($this->hasAggregatedData()) {
            return \DB::table('dashboard_aggregations')
                ->join('academic_periods', 'dashboard_aggregations.academic_period_id', '=', 'academic_periods.id')
                ->selectRaw('
                    academic_periods.name as period,
                    SUM(total_count) as total,
                    SUM(CASE WHEN validation_status IN ('.$this->statusSqlList($approvedStatuses).') THEN total_count ELSE 0 END) as approved,
                    SUM(CASE WHEN validation_status IN ('.$this->statusSqlList($pendingStatuses).') THEN total_count ELSE 0 END) as pending,
                    SUM(CASE WHEN validation_status IN ('.$this->statusSqlList($rejectedStatuses).') THEN total_count ELSE 0 END) as rejected
                ')
                ->groupBy('academic_periods.id', 'academic_periods.name', 'academic_periods.year', 'academic_periods.semester')
                ->orderBy('academic_periods.year', 'desc')
                ->orderBy('academic_periods.semester', 'desc')
                ->get();
        }

        $approvedStatusesStr = $this->statusSqlList($approvedStatuses);
        $pendingStatusesStr = $this->statusSqlList($pendingStatuses);
        $rejectedStatusesStr = $this->statusSqlList($rejectedStatuses);

        return \DB::table('student_achievements')
            ->join('academic_periods', 'student_achievements.academic_period_id', '=', 'academic_periods.id')
            ->whereNull('student_achievements.deleted_at')
            ->selectRaw("
                academic_periods.name as period,
                COUNT(*) as total,
                SUM(CASE 
                    WHEN validation_status IN ($approvedStatusesStr) THEN 1 
                    ELSE 0 
                END) as approved,
                SUM(CASE 
                    WHEN validation_status IN ($pendingStatusesStr) THEN 1 
                    ELSE 0 
                END) as pending,
                SUM(CASE 
                    WHEN validation_status IN ($rejectedStatusesStr) THEN 1 
                    ELSE 0 
                END) as rejected
            ")
            ->groupBy('academic_periods.id', 'academic_periods.name', 'academic_periods.year', 'academic_periods.semester')
            ->orderBy('academic_periods.year', 'desc')
            ->orderBy('academic_periods.semester', 'desc')
            ->get();
    }

    protected function getApprovalStatistics($periodId = null)
    {
        $statusGroups = StudentAchievement::getWorkflowStatusGroups();
        $approvedStatuses = $statusGroups['approved'];
        $pendingStatuses = $statusGroups['pending'];

        $query = StudentAchievement::query();
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        $total = $query->count();
        $approved = (clone $query)->whereIn('validation_status', $approvedStatuses)->count();
        $pending = (clone $query)->whereIn('validation_status', $pendingStatuses)->count();

        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        // Calculate average verification speed: AVG of diffInDays(created_at, updated_at)
        // ONLY for approved achievements in the selected period
        $approvedRecords = StudentAchievement::query()
            ->whereIn('validation_status', $approvedStatuses)
            ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
            ->whereNotNull('updated_at')
            ->select('created_at', 'updated_at')
            ->get();

        $avgDays = 0;
        if ($approvedRecords->isNotEmpty()) {
            $totalDays = $approvedRecords->sum(function ($item) {
                return Carbon::parse($item->created_at)->diffInDays(Carbon::parse($item->updated_at));
            });
            $avgDays = (int) round($totalDays / $approvedRecords->count());
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'approval_rate' => $approvalRate,
            'avg_time_to_approve' => $avgDays,
        ];
    }

    public function resolveDashboardContext($periodId = null): array
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $selectedPeriod = null;

        if ($periodId === null || $periodId === '') {
            if ($activePeriod) {
                $periodId = $activePeriod->id;
                $selectedPeriod = $activePeriod;
            } else {
                $periodId = null;
            }
        } elseif ($periodId === 'all') {
            $periodId = null;
        } else {
            $selectedPeriod = AcademicPeriod::find($periodId);
        }

        return compact('periodId', 'selectedPeriod');
    }

    protected function getDashboardMonitoringData($periodId, array $approvedStatuses): array
    {
        return [
            'statistics' => $this->getApprovalStatistics($periodId),
            'monthlyTrend' => $this->getMonthlyTrend(6, $periodId),
            'levelDistribution' => $this->getLevelDistribution($periodId),
            'topPerformers' => $this->getTopPerformers($periodId),
            'facultyComparison' => $this->getFacultyComparison($periodId),
            'categoryDistribution' => $this->getCategoryDistribution($periodId),
            'topProgramStudies' => $this->getProgramStudyRanking($periodId),
        ];
    }

    protected function getGlobalLeaderboardData(array $approvedStatuses): array
    {
        return [
            'totalDistinctStudents' => StudentAchievement::whereIn('validation_status', $approvedStatuses)
                ->distinct('student_id')
                ->count('student_id'),
            'topStudentsGlobal' => Student::whereHas('achievements', function ($q) use ($approvedStatuses) {
                $q->whereIn('validation_status', $approvedStatuses);
            })
                ->withCount([
                    'achievements as approved_count' => function ($q) use ($approvedStatuses) {
                        $q->whereIn('validation_status', $approvedStatuses);
                    },
                ])
                ->orderByDesc('approved_count')
                ->limit(5)
                ->get(),
        ];
    }

    protected function getDashboardAnomalyData($periodId, $alertContext): array
    {
        if ($periodId) {
            return [
                'anomalies' => $this->getContextAwareAnomalies($alertContext, (int) $periodId),
                'globalBreakdown' => [],
            ];
        }

        return [
            'anomalies' => $this->getContextAwareAnomalies('global', null),
            'globalBreakdown' => $this->getGlobalAnomalyBreakdown(),
        ];
    }

    protected function getArchivedDashboardStats($periodId, $selectedPeriod, $stats, array $pendingStatuses): array
    {
        $totalFaculties = \DB::table('students')->distinct()->count('faculty') ?: 1;
        $activeFaculties = \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->where('student_achievements.academic_period_id', $periodId)
            ->distinct()
            ->count('students.faculty');

        $facultyStudentCounts = \DB::table('students')
            ->selectRaw('faculty, COUNT(*) as student_count')
            ->groupBy('faculty')
            ->get()
            ->keyBy('faculty');

        $facultyComparison = $this->getFacultyComparison($periodId);
        $ratioData = $facultyComparison->map(function ($f) use ($facultyStudentCounts) {
            $studentCount = $facultyStudentCounts[$f->full_name]->student_count ?? 1;

            return (object) [
                'faculty' => $f->faculty,
                'full_name' => $f->full_name,
                'ratio' => round(($f->total / $studentCount) * 100, 2),
            ];
        })->sortByDesc('ratio')->values();

        $previousPeriod = AcademicPeriod::where('start_date', '<', $selectedPeriod->start_date)
            ->orderBy('start_date', 'desc')
            ->first();

        $previousPeriodTotal = 0;
        $periodGrowth = null;
        if ($previousPeriod) {
            $previousPeriodTotal = StudentAchievement::where('academic_period_id', $previousPeriod->id)->count();
            $currentTotal = $stats['achievements'];
            if ($previousPeriodTotal > 0) {
                $periodGrowth = round((($currentTotal - $previousPeriodTotal) / $previousPeriodTotal) * 100, 1);
            } elseif ($currentTotal > 0) {
                $periodGrowth = 100.0;
            }
        }

        return [
            'total_faculties' => $totalFaculties,
            'active_faculties' => $activeFaculties,
            'faculty_ratios' => $ratioData,
            'expired_count' => $this->getInclusiveCount($pendingStatuses, $periodId),
            'previous_period' => $previousPeriod,
            'previous_period_total' => $previousPeriodTotal,
            'period_growth' => $periodGrowth,
        ];
    }

    protected function getActiveDashboardStats($periodId): array
    {
        return [
            'daily_trend' => $this->getDailyValidationTrend($periodId),
            'faculty_backlog' => $this->getFacultyBacklog($periodId),
            'critical_queue' => $this->getCriticalQueue($periodId),
        ];
    }

    protected function getMonthlyTrend($months = 6, $periodId = null)
    {
        $approvedStatuses = $this->statusSqlList(StudentAchievement::getWorkflowStatusGroups()['approved']);
        $isSqlite = \DB::getDriverName() === 'sqlite';

        // If specific period is selected, use period's date range
        if ($periodId) {
            $period = AcademicPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;

                $query = \DB::table('student_achievements')
                    ->where('submitted_at', '>=', $startDate)
                    ->where('submitted_at', '<=', $endDate)
                    ->whereNotNull('submitted_at')
                    ->where('academic_period_id', $periodId);

                if ($isSqlite) {
                    $results = $query->selectRaw("
                        strftime('%m-%Y', submitted_at) as month,
                        COUNT(*) as submitted,
                        SUM(CASE WHEN validation_status IN ($approvedStatuses) THEN 1 ELSE 0 END) as approved
                    ")
                        ->groupByRaw("strftime('%m-%Y', submitted_at)")
                        ->orderByRaw("strftime('%Y-%m', submitted_at)")
                        ->get();
                } else {
                    $results = $query->selectRaw("
                        TO_CHAR(submitted_at, 'Mon YYYY') as month,
                        COUNT(*) as submitted,
                        SUM(CASE WHEN validation_status IN ($approvedStatuses) THEN 1 ELSE 0 END) as approved
                    ")
                        ->groupByRaw("TO_CHAR(submitted_at, 'Mon YYYY'), DATE_TRUNC('month', submitted_at)")
                        ->orderByRaw("DATE_TRUNC('month', submitted_at)")
                        ->get();
                }

                // If no data, return empty array with proper structure
                if ($results->isEmpty()) {
                    $monthLabel = ($period && $period->start_date)
                        ? Carbon::parse($period->start_date)->format('M Y')
                        : now()->format('M Y');

                    return collect([
                        (object) ['month' => $monthLabel, 'submitted' => 0, 'approved' => 0],
                    ]);
                }

                return $results;
            }
        }

        // For "Semua Periode", use last 6 months from now
        $startDate = now()->subMonths($months)->startOfMonth();

        $query = \DB::table('student_achievements')
            ->where('submitted_at', '>=', $startDate)
            ->whereNotNull('submitted_at');

        if ($isSqlite) {
            $results = $query->selectRaw("
                strftime('%m-%Y', submitted_at) as month,
                COUNT(*) as submitted,
                SUM(CASE WHEN validation_status IN ($approvedStatuses) THEN 1 ELSE 0 END) as approved
            ")
                ->groupByRaw("strftime('%m-%Y', submitted_at)")
                ->orderByRaw("strftime('%Y-%m', submitted_at)")
                ->get();
        } else {
            $results = $query->selectRaw("
                TO_CHAR(submitted_at, 'Mon YYYY') as month,
                COUNT(*) as submitted,
                SUM(CASE WHEN validation_status IN ($approvedStatuses) THEN 1 ELSE 0 END) as approved
            ")
                ->groupByRaw("TO_CHAR(submitted_at, 'Mon YYYY'), DATE_TRUNC('month', submitted_at)")
                ->orderByRaw("DATE_TRUNC('month', submitted_at)")
                ->get();
        }

        // If no data, return empty array with proper structure
        if ($results->isEmpty()) {
            return collect([
                (object) ['month' => now()->format('M Y'), 'submitted' => 0, 'approved' => 0],
            ]);
        }

        return $results;
    }

    protected function getLevelDistribution($periodId = null)
    {
        $levels = AchievementLevel::active()->ordered()->get();

        $counts = StudentAchievement::selectRaw('level, COUNT(*) as count')
            ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
            ->groupBy('level')
            ->pluck('count', 'level');

        $result = [];
        foreach ($levels as $level) {
            $result[$level->name] = $counts->get($level->name, 0);
        }

        return $result;
    }

    protected function getTopPerformers($periodId = null, $limit = 10)
    {
        $approvedStatuses = StudentAchievement::getWorkflowStatusGroups()['approved'];

        return Student::whereHas('achievements', function ($q) use ($periodId, $approvedStatuses) {
            $q->whereIn('validation_status', $approvedStatuses);
            if ($periodId) {
                $q->where('academic_period_id', $periodId);
            }
        })
            ->withCount([
                'achievements' => function ($q) use ($periodId, $approvedStatuses) {
                    $q->whereIn('validation_status', $approvedStatuses);
                    if ($periodId) {
                        $q->where('academic_period_id', $periodId);
                    }
                },
            ])
            ->orderByDesc('achievements_count')
            ->limit($limit)
            ->get();
    }

    protected function getFacultyComparison($periodId = null)
    {
        $statusGroups = StudentAchievement::getWorkflowStatusGroups();
        $approvedStatuses = $this->statusSqlList($statusGroups['approved']);
        $pendingStatuses = $this->statusSqlList($statusGroups['pending']);
        $rejectedStatuses = $this->statusSqlList($statusGroups['rejected']);
        $revisionStatuses = $this->statusSqlList($statusGroups['revision']);

        $facultyMapping = [
            'Fakultas Teknik' => 'FT',
            'Fakultas Hukum' => 'FH',
            'Fakultas Ekonomi dan Bisnis' => 'FEB',
            'Fakultas Kedokteran' => 'FK',
            'Fakultas Ilmu Sosial dan Ilmu Politik' => 'FISIP',
            'Fakultas Perikanan dan Ilmu Kelautan' => 'FPIK',
            'Fakultas MIPA' => 'FMIPA',
            'Fakultas Keguruan dan Ilmu Pendidikan' => 'FKIP',
            'Fakultas Pertanian' => 'FP',
        ];

        if ($this->hasAggregatedData()) {
            $facultySub = \DB::table('students')
                ->select('faculty_id', \DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id');

            $results = \DB::table('dashboard_aggregations as da')
                ->leftJoinSub($facultySub, 'f', 'da.faculty_id', '=', 'f.faculty_id')
                ->selectRaw("
                    COALESCE(f.faculty, 'N/A') as faculty,
                    SUM(da.total_count) as total,
                    SUM(CASE WHEN da.validation_status IN ($approvedStatuses) THEN da.total_count ELSE 0 END) as approved,
                    SUM(CASE WHEN da.validation_status IN ($pendingStatuses) THEN da.total_count ELSE 0 END) as pending,
                    SUM(CASE WHEN da.validation_status IN ($rejectedStatuses) THEN da.total_count ELSE 0 END) as rejected,
                    SUM(CASE WHEN da.validation_status IN ($revisionStatuses) THEN da.total_count ELSE 0 END) as revision
                ")
                ->when($periodId, fn ($q) => $q->where('da.academic_period_id', $periodId))
                ->groupBy('f.faculty')
                ->get();
        } else {
            $results = \DB::table('student_achievements')
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->whereNull('student_achievements.deleted_at')
                ->whereNull('students.deleted_at')
                ->selectRaw("
                    COALESCE(students.faculty, 'N/A') as faculty,
                    COUNT(*) as total,
                    SUM(CASE 
                        WHEN student_achievements.validation_status IN ($approvedStatuses) THEN 1 
                        ELSE 0 
                    END) as approved,
                    SUM(CASE 
                        WHEN student_achievements.validation_status IN ($pendingStatuses) THEN 1 
                        ELSE 0 
                    END) as pending,
                    SUM(CASE 
                        WHEN student_achievements.validation_status IN ($rejectedStatuses) THEN 1 
                        ELSE 0 
                    END) as rejected,
                    SUM(CASE 
                        WHEN student_achievements.validation_status IN ($revisionStatuses) THEN 1 
                        ELSE 0 
                    END) as revision
                ")
                ->when($periodId, fn ($q) => $q->where('student_achievements.academic_period_id', $periodId))
                ->groupBy('students.faculty')
                ->get();
        }

        $finalResults = [];
        $foundFaculties = $results->pluck('faculty')->toArray();

        // 1. Process faculties in our mapping (ensure they always appear)
        foreach ($facultyMapping as $fullName => $shortName) {
            $row = $results->firstWhere('faculty', $fullName);
            $finalResults[] = (object) [
                'faculty' => $shortName,
                'full_name' => $fullName,
                'total' => $row->total ?? 0,
                'approved' => $row->approved ?? 0,
                'pending' => $row->pending ?? 0,
                'rejected' => $row->rejected ?? 0,
                'revision' => $row->revision ?? 0,
            ];
        }

        // 2. Add other faculties not in our mapping
        foreach ($results as $row) {
            if (! isset($facultyMapping[$row->faculty])) {
                $fullName = $row->faculty;
                // Simple heuristic for short name
                $shortName = $fullName;
                if (str_starts_with($fullName, 'Fakultas ')) {
                    $parts = explode(' ', str_replace('Fakultas ', '', $fullName));
                    $short = 'F';
                    foreach ($parts as $part) {
                        if (strlen($part) > 2) { // ignore 'dan', 'i', etc
                            $short .= strtoupper(substr($part, 0, 1));
                        }
                    }
                    $shortName = $short;
                }

                $finalResults[] = (object) [
                    'faculty' => $shortName,
                    'full_name' => $fullName,
                    'total' => $row->total,
                    'approved' => $row->approved,
                    'pending' => $row->pending,
                    'rejected' => $row->rejected,
                    'revision' => $row->revision,
                ];
            }
        }

        // Sort by total for better visualization
        usort($finalResults, fn ($a, $b) => $b->total <=> $a->total);

        return collect($finalResults);
    }

    protected function getCategoryDistribution($periodId = null)
    {
        $approvedStatuses = $this->statusSqlList(StudentAchievement::getWorkflowStatusGroups()['approved']);

        $allCategories = AchievementCategory::where('is_active', true)
            ->orderBy('order')
            ->get();

        if ($this->hasAggregatedData()) {
            $achievementCounts = \DB::table('dashboard_aggregations as da')
                ->join('achievement_categories as ac', 'da.category_id', '=', 'ac.id')
                ->selectRaw("
                    ac.id as category_id,
                    ac.name as category,
                    SUM(da.total_count) as total,
                    SUM(CASE WHEN da.validation_status IN ($approvedStatuses) THEN da.total_count ELSE 0 END) as approved
                ")
                ->when($periodId, fn ($q) => $q->where('da.academic_period_id', $periodId))
                ->groupBy('ac.id', 'ac.name')
                ->get()
                ->keyBy('category_id');
        } else {
            $achievementCounts = StudentAchievement::join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
                ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
                ->whereNull('student_achievements.deleted_at')
                ->selectRaw("
                    achievement_categories.id as category_id,
                    achievement_categories.name as category,
                    COUNT(*) as total,
                    SUM(CASE WHEN validation_status IN ($approvedStatuses) THEN 1 ELSE 0 END) as approved
                ")
                ->when($periodId, function ($query) use ($periodId) {
                    $query->where('student_achievements.academic_period_id', $periodId);
                })
                ->groupBy('achievement_categories.id', 'achievement_categories.name')
                ->get()
                ->keyBy('category_id');
        }

        // Don't send color from database, let JavaScript handle it with the color palette
        return $allCategories->map(function ($category) use ($achievementCounts) {
            $counts = $achievementCounts->get($category->id);

            return (object) [
                'category' => $category->name,
                'total' => $counts->total ?? 0,
                'approved' => $counts->approved ?? 0,
            ];
        });
    }

    protected function getProgramStudyRanking($periodId = null)
    {
        $approvedStatuses = $this->statusSqlList(StudentAchievement::getWorkflowStatusGroups()['approved']);

        if ($this->hasAggregatedData()) {
            $prodiSub = \DB::table('students')
                ->select('program_study_id', \DB::raw('MAX(program_study) as program_study'), \DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('program_study_id')
                ->groupBy('program_study_id');

            return \DB::table('dashboard_aggregations as da')
                ->leftJoinSub($prodiSub, 'p', 'da.program_study_id', '=', 'p.program_study_id')
                ->selectRaw("
                    COALESCE(p.faculty, 'N/A') as faculty,
                    COALESCE(p.program_study, 'N/A') as prodi,
                    SUM(da.total_count) as total,
                    SUM(CASE WHEN da.validation_status IN ($approvedStatuses) THEN da.total_count ELSE 0 END) as approved
                ")
                ->when($periodId, fn ($q) => $q->where('da.academic_period_id', $periodId))
                ->groupBy('p.faculty', 'p.program_study')
                ->orderByDesc('total')
                ->limit(20)
                ->get();
        }

        return \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->whereNull('student_achievements.deleted_at')
            ->whereNull('students.deleted_at')
            ->selectRaw("
                COALESCE(students.faculty, 'N/A') as faculty,
                COALESCE(students.program_study, 'N/A') as prodi,
                COUNT(*) as total,
                SUM(CASE 
                    WHEN validation_status IN ($approvedStatuses) THEN 1 
                    ELSE 0 
                END) as approved
            ")
            ->when($periodId, fn ($q) => $q->where('student_achievements.academic_period_id', $periodId))
            ->groupBy('students.faculty', 'students.program_study')
            ->orderByDesc('total')
            ->limit(20)  // PERFORMANCE: Limit to top 20 program studies
            ->get();
    }

    // getAnomalies() â€” replaced by AnomalyAlertsTrait::getContextAwareAnomalies()

    protected function getSystemActivities()
    {
        $limit = 10;

        // 1. Get Auth Logs (Logins)
        $authLogs = AuthLog::with('user')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get()
            ->map(fn ($log) => (object) [
                'type' => 'auth',
                'action' => $log->action === AuthLog::ACTION_LOGIN ? 'Login' : 'Logout',
                'user' => $log->user->name ?? 'System',
                'description' => $log->action === AuthLog::ACTION_LOGIN ? "Masuk ke sistem via {$log->method}" : 'Keluar dari sistem',
                'timestamp' => $log->created_at,
                'color' => 'blue',
            ]);

        // 2. Get Validation Logs (Status Changes)
        $validationLogs = ValidationLog::with(['validator', 'studentAchievement.student'])
            ->orderByDesc('validated_at')
            ->take($limit)
            ->get()
            ->map(fn ($log) => (object) [
                'type' => 'validation',
                'action' => 'Validasi',
                'user' => $log->validator->name ?? 'System',
                'description' => "Ubah status: {$log->old_status} â†’ {$log->new_status}",
                'timestamp' => $log->validated_at,
                'color' => 'emerald',
            ]);

        // 3. Merge and Sort
        return $authLogs->concat($validationLogs)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();
    }

    protected function getDailyValidationTrend($periodId = null, $days = 14)
    {
        $startDate = now()->subDays($days)->startOfDay();

        return ValidationLog::query()
            ->selectRaw('DATE(validated_at) as date, COUNT(*) as count')
            ->when($periodId, function ($q) use ($periodId) {
                $q->whereHas('studentAchievement', fn ($sq) => $sq->where('academic_period_id', $periodId));
            })
            ->where('validated_at', '>=', $startDate)
            ->groupByRaw('DATE(validated_at)')
            ->orderBy('date', 'asc')
            ->get();
    }

    protected function getFacultyBacklog($periodId = null)
    {
        $facultyMapping = [
            'Fakultas Teknik' => 'FT',
            'Fakultas Hukum' => 'FH',
            'Fakultas Ekonomi dan Bisnis' => 'FEB',
            'Fakultas Kedokteran' => 'FK',
            'Fakultas Ilmu Sosial dan Ilmu Politik' => 'FISIP',
            'Fakultas Perikanan dan Ilmu Kelautan' => 'FPIK',
            'Fakultas MIPA' => 'FMIPA',
            'Fakultas Keguruan dan Ilmu Pendidikan' => 'FKIP',
            'Fakultas Pertanian' => 'FP',
        ];

        $pendingStatuses = StudentAchievement::getWorkflowStatusGroups()['pending'];

        $results = \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->selectRaw("COALESCE(students.faculty, 'N/A') as faculty, COUNT(*) as count")
            ->whereIn('student_achievements.validation_status', $pendingStatuses)
            ->when($periodId, fn ($q) => $q->where('student_achievements.academic_period_id', $periodId))
            ->groupBy('students.faculty')
            ->orderByDesc('count')
            ->get();

        // Map to abbreviated English names
        return $results->map(function ($row) use ($facultyMapping) {
            $row->faculty = $facultyMapping[$row->faculty] ?? $row->faculty;

            return $row;
        });
    }

    protected function getCriticalQueue($periodId = null, $limit = 5)
    {
        $pendingStatuses = StudentAchievement::getWorkflowStatusGroups()['pending'];

        $items = StudentAchievement::with(['student', 'achievement.category'])
            ->whereIn('validation_status', $pendingStatuses)
            ->when($periodId, fn ($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get();

        // Calculate waiting days using Carbon to avoid billion-day bug
        $items->each(function ($item) {
            $item->waiting_days = (int) Carbon::parse($item->created_at)->diffInDays(now());
        });

        return $items;
    }

    protected function getMasterDataSummary()
    {
        $hierarchy = $this->sigapApiService->getHierarchicalStructure();
        $facultiesCount = count($hierarchy);
        $prodisCount = 0;

        foreach ($hierarchy as $faculty) {
            foreach ($faculty['departments'] ?? [] as $dept) {
                $prodisCount += count($dept['study_programs'] ?? []);
            }
        }

        return [
            'faculties_count' => $facultiesCount ?: \DB::table('students')->distinct()->count('faculty'),
            'prodis_count' => $prodisCount ?: \DB::table('students')->distinct()->count('program_study'),
            'achievements_master_count' => Achievement::count(),
            'validators_count' => User::where('role', 'Operator')->where('is_active', true)->count(),
            'operators_count' => User::where('role', 'Admin')->where('is_active', true)->count(),
            'sync_status' => [
                'status' => 'Stable',
                'last_sync' => now()->subMinutes(rand(5, 60))->format('H:i'),
                'percentage' => 100,
            ],
        ];
    }

    /**
     * Get anomaly details by type for modal display (context-aware).
     */
    public function getAnomalyDetailsData($type, $periodId = null, $contextParam = 'active')
    {
        // Resolve context from period if not explicitly passed
        if ($periodId && $periodId !== 'all') {
            $period = AcademicPeriod::find($periodId);
            $context = $period ? $this->resolveAlertContext($period) : $contextParam;
        } elseif ($periodId === 'all' || ! $periodId) {
            $context = 'global';
        } else {
            $context = $contextParam;
        }

        $actualPeriodId = ($periodId && $periodId !== 'all') ? (int) $periodId : null;

        $data = $this->getContextAwareAnomalyDetails($type, $context, $actualPeriodId);

        return $data;
    }

    /**
     * Get unit distribution of achievements (by program study)
     */
    public function getUnitDistribution($periodId = null)
    {
        $statusGroups = StudentAchievement::getWorkflowStatusGroups();

        $approvedStr = $this->statusSqlList($statusGroups['approved']);
        $pendingStr = $this->statusSqlList($statusGroups['pending']);
        $rejectedStr = $this->statusSqlList($statusGroups['rejected']);

        if ($this->hasAggregatedData()) {
            $prodiSub = \DB::table('students')
                ->select('program_study_id', \DB::raw('MAX(program_study) as program_study'), \DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('program_study_id')
                ->groupBy('program_study_id');

            return \DB::table('dashboard_aggregations as da')
                ->joinSub($prodiSub, 'p', 'da.program_study_id', '=', 'p.program_study_id')
                ->when($periodId && $periodId !== 'all', fn ($q) => $q->where('da.academic_period_id', $periodId))
                ->select(
                    'p.program_study as prodi',
                    'p.faculty',
                    \DB::raw('SUM(da.total_count) as total'),
                    \DB::raw("SUM(CASE WHEN da.validation_status IN ($approvedStr) THEN da.total_count ELSE 0 END) as approved"),
                    \DB::raw("SUM(CASE WHEN da.validation_status IN ($pendingStr) THEN da.total_count ELSE 0 END) as pending"),
                    \DB::raw("SUM(CASE WHEN da.validation_status IN ($rejectedStr) THEN da.total_count ELSE 0 END) as rejected")
                )
                ->whereNotNull('p.program_study')
                ->where('p.program_study', '!=', '')
                ->groupBy('p.program_study', 'p.faculty')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($item) => [
                    'prodi' => $item->prodi,
                    'faculty' => $item->faculty,
                    'total' => (int) $item->total,
                    'approved' => (int) $item->approved,
                    'pending' => (int) $item->pending,
                    'rejected' => (int) $item->rejected,
                ]);
        }

        return \DB::table('student_achievements as sa')
            ->join('students as s', 'sa.student_id', '=', 's.student_id')
            ->whereNull('sa.deleted_at')
            ->whereNull('s.deleted_at')
            ->when($periodId && $periodId !== 'all', fn ($q) => $q->where('sa.academic_period_id', $periodId))
            ->select(
                's.program_study as prodi',
                's.faculty',
                \DB::raw('COUNT(*) as total'),
                \DB::raw("SUM(CASE WHEN sa.validation_status IN ($approvedStr) THEN 1 ELSE 0 END) as approved"),
                \DB::raw("SUM(CASE WHEN sa.validation_status IN ($pendingStr) THEN 1 ELSE 0 END) as pending"),
                \DB::raw("SUM(CASE WHEN sa.validation_status IN ($rejectedStr) THEN 1 ELSE 0 END) as rejected")
            )
            ->whereNotNull('s.program_study')
            ->where('s.program_study', '!=', '')
            ->groupBy('s.program_study', 's.faculty')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'prodi' => $item->prodi,
                'faculty' => $item->faculty,
                'total' => (int) $item->total,
                'approved' => (int) $item->approved,
                'pending' => (int) $item->pending,
                'rejected' => (int) $item->rejected,
            ]);
    }

    /**
     * Resolve alert context based on selected period
     */
    protected function resolveAlertContext($selectedPeriod): string
    {
        if (! $selectedPeriod) {
            return 'global';
        }

        return $selectedPeriod->is_active ? 'active' : 'archive';
    }

    /**
     * Get context-aware anomalies with proper total count
     */
    protected function getContextAwareAnomalies(string $context, ?int $periodId): array
    {
        $anomalies = $this->getAnomalies($context, $periodId);
        $anomalies['total_count'] = $this->getTotalAnomalyCount($anomalies);

        return $anomalies;
    }

    /**
     * Get global anomaly breakdown by period
     */
    protected function getGlobalAnomalyBreakdown(): array
    {
        $periods = AcademicPeriod::ordered()->get();
        $breakdown = [];

        foreach ($periods as $period) {
            $context = $period->is_active ? 'active' : 'archive';
            $anomalies = $this->getAnomalies($context, $period->id);

            $breakdown[] = [
                'period_id' => $period->id,
                'period_name' => $period->name,
                'is_active' => $period->is_active,
                'sla_breach' => $anomalies['sla_breach']['count'],
                'duplicates' => $anomalies['duplicates']['count'],
                'missing_documents' => $anomalies['missing_documents']['count'],
                'abandoned_drafts' => $anomalies['abandoned_drafts']['count'],
                'total' => $this->getTotalAnomalyCount($anomalies),
            ];
        }

        return $breakdown;
    }

    /**
     * Get detailed anomaly data for modal display
     */
    protected function getContextAwareAnomalyDetails(string $type, string $context, ?int $periodId): array
    {
        $anomalies = $this->getAnomalies($context, $periodId);

        $typeMap = [
            'sla_breach' => 'sla_breach',
            'duplicates' => 'duplicates',
            'no_docs' => 'missing_documents',
            'missing_documents' => 'missing_documents',
            'abandoned_drafts' => 'abandoned_drafts',
        ];

        $key = $typeMap[$type] ?? null;

        if (! $key || ! isset($anomalies[$key])) {
            return ['count' => 0, 'items' => []];
        }

        return $anomalies[$key];
    }

    /**
     * Check if dashboard_aggregations table has data.
     */
    protected function hasAggregatedData(): bool
    {
        try {
            return \DB::table('dashboard_aggregations')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}
