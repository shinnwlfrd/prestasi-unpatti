<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\AnomalyAlertsTrait;
use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use App\Models\AuthLog;
use App\Services\Admin\StatisticsService;
use App\Services\SigapApiService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use AnomalyAlertsTrait;

    public function __construct(
        protected StatisticsService $statisticsService,
        protected SigapApiService $sigapApiService
    ) {
    }

    public function index()
    {
        // 1. Resolve logical context (Period, State)
        $context = $this->resolveDashboardContext();
        $periodId = $context['periodId'];
        $selectedPeriod = $context['selectedPeriod'];
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $periods = AcademicPeriod::ordered()->get();
        $globalTrend = $this->getPeriodComparison();

        // 2. Resolve view state variables
        $isGlobal = !$selectedPeriod;
        $isActivePeriod = $selectedPeriod && $selectedPeriod->is_active;
        $isInactivePeriod = $selectedPeriod && !$selectedPeriod->is_active;

        // 3. Status groupings
        $pendingStatuses = ['Menunggu', 'submitted', 'faculty_review', 'university_review'];
        $approvedStatuses = ['Disetujui', 'faculty_approved', 'university_approved'];
        $rejectedStatuses = ['Ditolak', 'faculty_rejected', 'university_rejected', 'faculty_revision'];

        // 4. Gather Core Data
        $stats = $this->getDashboardSummaryStats($periodId, $pendingStatuses, $approvedStatuses, $rejectedStatuses);
        $recentAchievements = $this->getRecentAchievementsForDashboard($periodId);
        $queues = $this->getDashboardQueues($periodId);
        $recentValidations = $this->getRecentValidationsForDashboard($periodId);
        $statusStats = $this->getQuickStatusStats($periodId, $pendingStatuses, $approvedStatuses, $rejectedStatuses);

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

        $achievementLevels = \App\Models\AchievementLevel::active()->ordered()->get();
        return view('admin.dashboard', array_merge(
            compact(
                'stats', 'recentAchievements', 'recentValidations', 'statusStats',
                'activePeriod', 'periods', 'selectedPeriod', 'globalTrend', 'alertContext',
                'isActivePeriod', 'isInactivePeriod', 'isGlobal', 'achievementLevels'
            ),
            $queues,
            $monitoring,
            $globalLeaderboard,
            $anomalyData,
            $viewData
        ));
    }


    protected function getDashboardSummaryStats($periodId, array $pendingStatuses, array $approvedStatuses, array $rejectedStatuses): array
    {
        return [
            'students' => Student::count(),
            'achievements' => StudentAchievement::when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'pending' => $this->getInclusiveCount($pendingStatuses, $periodId),
            'total_pending' => $this->getInclusiveCount($pendingStatuses, null),
            'total_rejected' => $this->getInclusiveCount($rejectedStatuses, $periodId),
            'approved' => $this->getInclusiveCount($approvedStatuses, $periodId),
            'validators' => User::where('role', 'Operator')->where('is_active', true)->count(),
            'total_achievements' => StudentAchievement::count(),
            'total_avg_time' => $this->calculateTotalAvgTime($approvedStatuses),
            'global_status_stats' => [
                'menunggu' => $this->getInclusiveCount($pendingStatuses, null),
                'disetujui' => $this->getInclusiveCount($approvedStatuses, null),
                'ditolak' => $this->getInclusiveCount($rejectedStatuses, null),
            ],
            'global_level_distribution' => \App\Models\AchievementLevel::active()->ordered()->get()->mapWithKeys(fn($l) => [
                $l->name => StudentAchievement::where('level', $l->name)->count()
            ])->toArray(),
            'global_category_distribution' => \DB::table('student_achievements')
                ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
                ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
                ->selectRaw('achievement_categories.name as category, COUNT(*) as total')
                ->groupBy('achievement_categories.name')
                ->get(),
            // Two-stage validation stats
            'university_pending' => StudentAchievement::universityPending()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
        ];
    }

    protected function getRecentAchievementsForDashboard($periodId)
    {
        // Note: If no achievements in selected period, show latest without period filter
        $recentAchievements = StudentAchievement::with(['student', 'achievement.category'])
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
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
        // University Pending Queue (top 5 oldest) - Use selected period
        $universityPending = StudentAchievement::with(['student', 'achievement.category', 'facultyValidator'])
            ->universityPending()
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('faculty_validated_at', 'asc')
            ->take(5)
            ->get();

        // Resubmission Queue (top 5 oldest) - replaces appeal queue - Use selected period
        $resubmissionQueue = StudentAchievement::with(['student', 'achievement.category'])
            ->where('is_resubmission', true)
            ->whereIn('validation_status', ['submitted', 'faculty_review'])
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('last_resubmitted_at', 'asc')
            ->take(5)
            ->get();

        // Pending Review (urgent - older than 7 days) - Use selected period
        $urgentPending = StudentAchievement::with(['student', 'achievement.category'])
            ->where('validation_status', 'Menunggu')
            ->where('submitted_at', '<', now()->subDays(7))
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('submitted_at', 'asc')
            ->take(5)
            ->get();

        return compact('universityPending', 'resubmissionQueue', 'urgentPending');
    }

    protected function getRecentValidationsForDashboard($periodId)
    {
        return ValidationLog::with(['studentAchievement.student', 'validator'])
            ->when($periodId, function ($q) use ($periodId) {
                $q->whereHas('studentAchievement', fn($sq) => $sq->where('academic_period_id', $periodId));
            })
            ->latest('validated_at')
            ->take(10)
            ->get();
    }

    protected function getQuickStatusStats($periodId, array $pendingStatuses, array $approvedStatuses, array $rejectedStatuses): array
    {
        return [
            'menunggu' => $this->getInclusiveCount($pendingStatuses, $periodId),
            'disetujui' => $this->getInclusiveCount($approvedStatuses, $periodId),
            'ditolak' => $this->getInclusiveCount($rejectedStatuses, $periodId),
            'revisi' => StudentAchievement::whereIn('validation_status', ['Revisi', 'faculty_revision'])->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            // New two-stage statuses
            'faculty_pending' => StudentAchievement::facultyPending()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'faculty_approved' => StudentAchievement::facultyApproved()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'university_approved' => StudentAchievement::universityApproved()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
        ];
    }

    protected function getInclusiveCount(array $statuses, $pId = null): int
    {
        return StudentAchievement::whereIn('validation_status', $statuses)
            ->when($pId, fn($q) => $q->where('academic_period_id', $pId))
            ->count();
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
        $totalDays = $records->sum(fn($item) => Carbon::parse($item->created_at)->diffInDays(Carbon::parse($item->updated_at)));
        return (int) round($totalDays / $records->count());
    }

    // Helper methods for monitoring dashboard
    protected function getPeriodComparison()
    {
        return \DB::table('student_achievements')
            ->join('academic_periods', 'student_achievements.academic_period_id', '=', 'academic_periods.id')
            ->selectRaw("
                academic_periods.name as period,
                COUNT(*) as total,
                SUM(CASE 
                    WHEN validation_status IN ('Disetujui', 'university_approved') THEN 1 
                    ELSE 0 
                END) as approved,
                SUM(CASE 
                    WHEN validation_status IN ('Menunggu', 'submitted', 'faculty_review', 'faculty_approved', 'university_review') THEN 1 
                    ELSE 0 
                END) as pending,
                SUM(CASE 
                    WHEN validation_status IN ('Ditolak', 'faculty_rejected', 'university_rejected') THEN 1 
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
        $approvedStatuses = ['Disetujui', 'faculty_approved', 'university_approved', 'appeal_approved'];

        $query = StudentAchievement::query();
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        $total = $query->count();
        $approved = (clone $query)->whereIn('validation_status', $approvedStatuses)->count();
        $pending = (clone $query)->where('validation_status', 'Menunggu')->count();

        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        // Calculate average verification speed: AVG of diffInDays(created_at, updated_at)
        // ONLY for approved achievements in the selected period
        $approvedRecords = StudentAchievement::query()
            ->whereIn('validation_status', $approvedStatuses)
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
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
            'avg_time_to_approve' => $avgDays
        ];
    }

    protected function resolveDashboardContext(): array
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $periodId = request()->input('period');
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
                    }
                ])
                ->orderByDesc('approved_count')
                ->limit(5)
                ->get()
        ];
    }

    protected function getDashboardAnomalyData($periodId, $alertContext): array
    {
        if ($periodId) {
            return [
                'anomalies' => $this->getContextAwareAnomalies($alertContext, (int) $periodId),
                'globalBreakdown' => []
            ];
        }

        return [
            'anomalies' => $this->getContextAwareAnomalies('global', null),
            'globalBreakdown' => $this->getGlobalAnomalyBreakdown()
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
                'ratio' => round(($f->total / $studentCount) * 100, 2)
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
        // If specific period is selected, use period's date range
        if ($periodId) {
            $period = AcademicPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;

                $results = \DB::table('student_achievements')
                    ->selectRaw("
                        TO_CHAR(submitted_at, 'Mon YYYY') as month,
                        COUNT(*) as submitted,
                        SUM(CASE WHEN validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved
                    ")
                    ->where('submitted_at', '>=', $startDate)
                    ->where('submitted_at', '<=', $endDate)
                    ->whereNotNull('submitted_at')
                    ->where('academic_period_id', $periodId)
                    ->groupByRaw("TO_CHAR(submitted_at, 'Mon YYYY'), DATE_TRUNC('month', submitted_at)")
                    ->orderByRaw("DATE_TRUNC('month', submitted_at)")
                    ->get();

                // If no data, return empty array with proper structure
                if ($results->isEmpty()) {
                    $monthLabel = ($period && $period->start_date)
                        ? Carbon::parse($period->start_date)->format('M Y')
                        : now()->format('M Y');
                    return collect([
                        (object) ['month' => $monthLabel, 'submitted' => 0, 'approved' => 0]
                    ]);
                }

                return $results;
            }
        }

        // For "Semua Periode", use last 6 months from now
        $startDate = now()->subMonths($months)->startOfMonth();

        $results = \DB::table('student_achievements')
            ->selectRaw("
                TO_CHAR(submitted_at, 'Mon YYYY') as month,
                COUNT(*) as submitted,
                SUM(CASE WHEN validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved
            ")
            ->where('submitted_at', '>=', $startDate)
            ->whereNotNull('submitted_at')
            ->groupByRaw("TO_CHAR(submitted_at, 'Mon YYYY'), DATE_TRUNC('month', submitted_at)")
            ->orderByRaw("DATE_TRUNC('month', submitted_at)")
            ->get();

        // If no data, return empty array with proper structure
        if ($results->isEmpty()) {
            return collect([
                (object) ['month' => now()->format('M Y'), 'submitted' => 0, 'approved' => 0]
            ]);
        }

        return $results;
    }

    protected function getLevelDistribution($periodId = null)
    {
        $levels = \App\Models\AchievementLevel::active()->ordered()->get();
        
        $counts = StudentAchievement::selectRaw('level, COUNT(*) as count')
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
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
        return Student::whereHas('achievements', function ($q) use ($periodId) {
            $q->where('validation_status', 'Disetujui');
            if ($periodId) {
                $q->where('academic_period_id', $periodId);
            }
        })
            ->withCount([
                'achievements' => function ($q) use ($periodId) {
                    $q->where('validation_status', 'Disetujui');
                    if ($periodId) {
                        $q->where('academic_period_id', $periodId);
                    }
                }
            ])
            ->orderByDesc('achievements_count')
            ->limit($limit)
            ->get();
    }

    protected function getFacultyComparison($periodId = null)
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

        // Status mapping to simplify the chart
        // Approved: Disetujui (legacy) OR university_approved OR appeal_approved
        // Pending: Menunggu (legacy) OR submitted OR faculty_review OR faculty_approved OR university_review OR appeal_submitted
        // Rejected: Ditolak (legacy) OR faculty_rejected OR university_rejected OR appeal_rejected
        // Revision: Revisi (legacy) OR faculty_revision

        $results = \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->selectRaw("
                COALESCE(students.faculty, 'N/A') as faculty,
                COUNT(*) as total,
                SUM(CASE 
                    WHEN student_achievements.validation_status IN ('Disetujui', 'university_approved') THEN 1 
                    ELSE 0 
                END) as approved,
                SUM(CASE 
                    WHEN student_achievements.validation_status IN ('Menunggu', 'submitted', 'faculty_review', 'faculty_approved', 'university_review') THEN 1 
                    ELSE 0 
                END) as pending,
                SUM(CASE 
                    WHEN student_achievements.validation_status IN ('Ditolak', 'faculty_rejected', 'university_rejected') THEN 1 
                    ELSE 0 
                END) as rejected,
                SUM(CASE 
                    WHEN student_achievements.validation_status IN ('Revisi', 'faculty_revision') THEN 1 
                    ELSE 0 
                END) as revision
            ")
            ->when($periodId, fn($q) => $q->where('student_achievements.academic_period_id', $periodId))
            ->groupBy('students.faculty')
            ->get();

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
            if (!isset($facultyMapping[$row->faculty])) {
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
        usort($finalResults, fn($a, $b) => $b->total <=> $a->total);

        return collect($finalResults);
    }

    protected function getCategoryDistribution($periodId = null)
    {
        $allCategories = \App\Models\AchievementCategory::where('is_active', true)
            ->orderBy('order')
            ->get();

        $achievementCounts = StudentAchievement::join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->selectRaw("
                achievement_categories.id as category_id,
                achievement_categories.name as category,
                COUNT(*) as total,
                SUM(CASE WHEN validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved
            ")
            ->when($periodId, function ($query) use ($periodId) {
                $query->where('student_achievements.academic_period_id', $periodId);
            })
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->get()
            ->keyBy('category_id');

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
        return \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->selectRaw("
                COALESCE(students.faculty, 'N/A') as faculty,
                COALESCE(students.program_study, 'N/A') as prodi,
                COUNT(*) as total,
                SUM(CASE 
                    WHEN validation_status IN ('Disetujui', 'university_approved') THEN 1 
                    ELSE 0 
                END) as approved
            ")
            ->when($periodId, fn($q) => $q->where('student_achievements.academic_period_id', $periodId))
            ->groupBy('students.faculty', 'students.program_study')
            ->orderByDesc('total')
            ->limit(20)  // PERFORMANCE: Limit to top 20 program studies
            ->get();
    }

    // getAnomalies() — replaced by AnomalyAlertsTrait::getContextAwareAnomalies()

    protected function getSystemActivities()
    {
        $limit = 10;

        // 1. Get Auth Logs (Logins)
        $authLogs = AuthLog::with('user')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get()
            ->map(fn($log) => (object) [
                'type' => 'auth',
                'action' => $log->action === AuthLog::ACTION_LOGIN ? 'Login' : 'Logout',
                'user' => $log->user->name ?? 'System',
                'description' => $log->action === AuthLog::ACTION_LOGIN ? "Masuk ke sistem via {$log->method}" : "Keluar dari sistem",
                'timestamp' => $log->created_at,
                'color' => 'blue'
            ]);

        // 2. Get Validation Logs (Status Changes)
        $validationLogs = ValidationLog::with(['validator', 'studentAchievement.student'])
            ->orderByDesc('validated_at')
            ->take($limit)
            ->get()
            ->map(fn($log) => (object) [
                'type' => 'validation',
                'action' => 'Validasi',
                'user' => $log->validator->name ?? 'System',
                'description' => "Ubah status: {$log->old_status} → {$log->new_status}",
                'timestamp' => $log->validated_at,
                'color' => 'emerald'
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
            ->selectRaw("DATE(validated_at) as date, COUNT(*) as count")
            ->when($periodId, function ($q) use ($periodId) {
                $q->whereHas('studentAchievement', fn($sq) => $sq->where('academic_period_id', $periodId));
            })
            ->where('validated_at', '>=', $startDate)
            ->groupByRaw("DATE(validated_at)")
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

        $pendingStatuses = ['Menunggu', 'submitted', 'faculty_review', 'faculty_approved', 'university_review'];

        $results = \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->selectRaw("COALESCE(students.faculty, 'N/A') as faculty, COUNT(*) as count")
            ->whereIn('student_achievements.validation_status', $pendingStatuses)
            ->when($periodId, fn($q) => $q->where('student_achievements.academic_period_id', $periodId))
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
        $pendingStatuses = ['Menunggu', 'submitted', 'faculty_review', 'faculty_approved', 'university_review'];

        $items = StudentAchievement::with(['student', 'achievement.category'])
            ->whereIn('validation_status', $pendingStatuses)
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
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
                'percentage' => 100
            ]
        ];
    }

    /**
     * Get anomaly details by type for modal display (context-aware).
     */
    public function getAnomalyDetails($type)
    {
        $periodId = request()->input('period');
        $contextParam = request()->input('context', 'active');

        // Resolve context from period if not explicitly passed
        if ($periodId && $periodId !== 'all') {
            $period = AcademicPeriod::find($periodId);
            $context = $period ? $this->resolveAlertContext($period) : $contextParam;
        } elseif ($periodId === 'all' || !$periodId) {
            $context = 'global';
        } else {
            $context = $contextParam;
        }

        $actualPeriodId = ($periodId && $periodId !== 'all') ? (int) $periodId : null;

        $validTypes = ['sla_breach', 'duplicates', 'no_docs', 'missing_documents', 'abandoned_drafts'];
        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid anomaly type'], 400);
        }

        $data = $this->getContextAwareAnomalyDetails($type, $context, $actualPeriodId);

        return response()->json([
            'context' => $context,
            'type' => $type,
            'data' => $data,
        ]);
    }

    /**
     * Delete a student achievement record
     */
    public function deleteAchievement($id)
    {
        try {
            $achievement = StudentAchievement::where('sa_id', $id)->firstOrFail();

            // Store info for logging
            $studentName = $achievement->student->name ?? 'Unknown';
            $eventName = $achievement->event_name;

            // Delete associated documents if they exist
            if ($achievement->certificate && \Storage::disk('public')->exists($achievement->certificate)) {
                \Storage::disk('public')->delete($achievement->certificate);
            }

            if ($achievement->alternative_document_path && \Storage::disk('public')->exists($achievement->alternative_document_path)) {
                \Storage::disk('public')->delete($achievement->alternative_document_path);
            }

            // Delete the achievement record
            $achievement->delete();

            // Log the deletion
            \Log::info("Achievement deleted", [
                'sa_id' => $id,
                'student' => $studentName,
                'event' => $eventName,
                'deleted_by' => auth()->user()->name ?? 'System'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data prestasi berhasil dihapus'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data prestasi tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            \Log::error("Failed to delete achievement", [
                'sa_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUnitDistribution()
    {
        $periodId = request()->input('period');

        // Status groupings
        $approvedStatuses = ['Disetujui', 'faculty_approved', 'university_approved', 'appeal_approved'];
        $pendingStatuses = ['Menunggu', 'submitted', 'faculty_review', 'university_review', 'appeal_submitted'];
        $rejectedStatuses = ['Ditolak', 'faculty_rejected', 'university_rejected', 'appeal_rejected', 'faculty_revision'];

        // Get all program studies with their achievement counts
        $data = \DB::table('student_achievements as sa')
            ->join('students as s', 'sa.student_id', '=', 's.student_id')
            ->when($periodId && $periodId !== 'all', fn($q) => $q->where('sa.academic_period_id', $periodId))
            ->select(
                's.program_study as prodi',
                's.faculty',
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN sa.validation_status IN (\'' . implode("','", $approvedStatuses) . '\') THEN 1 ELSE 0 END) as approved'),
                \DB::raw('SUM(CASE WHEN sa.validation_status IN (\'' . implode("','", $pendingStatuses) . '\') THEN 1 ELSE 0 END) as pending'),
                \DB::raw('SUM(CASE WHEN sa.validation_status IN (\'' . implode("','", $rejectedStatuses) . '\') THEN 1 ELSE 0 END) as rejected')
            )
            ->whereNotNull('s.program_study')
            ->where('s.program_study', '!=', '')
            ->groupBy('s.program_study', 's.faculty')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'prodi' => $item->prodi,
                    'faculty' => $item->faculty,
                    'total' => (int) $item->total,
                    'approved' => (int) $item->approved,
                    'pending' => (int) $item->pending,
                    'rejected' => (int) $item->rejected,
                ];
            });

        return response()->json($data);
    }

    /**
     * Resolve alert context based on selected period
     */
    protected function resolveAlertContext($selectedPeriod): string
    {
        if (!$selectedPeriod) {
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

        if (!$key || !isset($anomalies[$key])) {
            return ['count' => 0, 'items' => []];
        }

        return $anomalies[$key];
    }

}