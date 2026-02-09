<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use App\Models\AuthLog;
use App\Services\Admin\StatisticsService;

class DashboardController extends Controller
{
    public function __construct(
        protected StatisticsService $statisticsService
    ) {
    }

    public function index()
    {
        // Get active period first
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        // Get period filter from request
        $periodId = request()->input('period');

        // Get all periods for dropdown
        $periods = AcademicPeriod::ordered()->get();

        // Specific data for global charts (always needed)
        $globalTrend = $this->getPeriodComparison();

        // Determine selected period and comparison state
        $selectedPeriod = null;
        if ($periodId === null || $periodId === '') {
            if ($activePeriod) {
                $periodId = $activePeriod->id;
                $selectedPeriod = $activePeriod;
                $periodComparison = null;
            } else {
                $periodComparison = null;
                $periodId = null;
            }
        } elseif ($periodId === 'all') {
            $periodComparison = null;
            $periodId = null;
        } else {
            $selectedPeriod = AcademicPeriod::find($periodId);
            $periodComparison = null;
        }

        $isGlobal = !$selectedPeriod;
        $isActivePeriod = $selectedPeriod && $selectedPeriod->is_active;
        $isInactivePeriod = $selectedPeriod && !$selectedPeriod->is_active;

        // Helper for inclusive status counts
        $getInclusiveCount = function ($statuses, $pId = null) {
            return StudentAchievement::whereIn('validation_status', $statuses)
                ->when($pId, fn($q) => $q->where('academic_period_id', $pId))
                ->count();
        };

        // Status groupings (inclusive of legacy and new statuses)
        $pendingStatuses = ['Menunggu', 'submitted', 'faculty_review', 'university_review', 'appeal_submitted'];
        $approvedStatuses = ['Disetujui', 'faculty_approved', 'university_approved', 'appeal_approved'];
        $rejectedStatuses = ['Ditolak', 'faculty_rejected', 'university_rejected', 'appeal_rejected', 'faculty_revision'];

        // Basic Statistics
        $stats = [
            'students' => Student::count(),
            'achievements' => StudentAchievement::when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'pending' => $getInclusiveCount($pendingStatuses, $periodId),
            'total_pending' => $getInclusiveCount($pendingStatuses, null),
            'total_rejected' => $getInclusiveCount($rejectedStatuses, $periodId),
            'approved' => $getInclusiveCount($approvedStatuses, $periodId),
            'validators' => User::where('role', 'Validator')->where('is_active', true)->count(),
            'total_achievements' => StudentAchievement::count(),
            'total_avg_time' => round(\DB::table('student_achievements')
                ->where(function ($q) {
                    $q->whereNotNull('validator_id')
                        ->orWhereNotNull('faculty_validator_id')
                        ->orWhereNotNull('university_validator_id');
                })
                ->whereIn('validation_status', array_merge($approvedStatuses, $rejectedStatuses))
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - submitted_at))/86400) as avg_days')
                ->value('avg_days') ?? 0, 1),
            'global_status_stats' => [
                'menunggu' => $getInclusiveCount($pendingStatuses, null),
                'disetujui' => $getInclusiveCount($approvedStatuses, null),
                'ditolak' => $getInclusiveCount($rejectedStatuses, null),
            ],
            'global_level_distribution' => [
                'Universitas' => StudentAchievement::where('level', 'Universitas')->count(),
                'Nasional' => StudentAchievement::where('level', 'Nasional')->count(),
                'Internasional' => StudentAchievement::where('level', 'Internasional')->count(),
            ],
            'global_category_distribution' => \DB::table('student_achievements')
                ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
                ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
                ->selectRaw('achievement_categories.name as category, COUNT(*) as total')
                ->groupBy('achievement_categories.name')
                ->get(),
            // Two-stage validation stats
            'university_pending' => StudentAchievement::universityPending()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'appeal_pending' => StudentAchievement::appealPending()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
        ];

        // Recent Achievements (last 10)
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

        // University Pending Queue (top 5 oldest)
        $universityPending = StudentAchievement::with(['student', 'achievement.category', 'facultyValidator'])
            ->universityPending()
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('faculty_validated_at', 'asc')
            ->take(5)
            ->get();

        // Appeal Pending Queue (top 5 oldest)
        $appealPending = \App\Models\AchievementAppeal::with(['studentAchievement.student', 'studentAchievement.achievement.category'])
            ->where('status', 'pending')
            ->when($periodId, function ($q) use ($periodId) {
                $q->whereHas('studentAchievement', fn($sq) => $sq->where('academic_period_id', $periodId));
            })
            ->orderBy('submitted_at', 'asc')
            ->take(5)
            ->get();

        // Pending Review (urgent - older than 7 days)
        $urgentPending = StudentAchievement::with(['student', 'achievement.category'])
            ->where('validation_status', 'Menunggu')
            ->where('submitted_at', '<', now()->subDays(7))
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('submitted_at', 'asc')
            ->take(5)
            ->get();

        // Recent Validations (last 10)
        $recentValidations = ValidationLog::with(['studentAchievement.student', 'validator'])
            ->when($periodId, function ($q) use ($periodId) {
                $q->whereHas('studentAchievement', fn($sq) => $sq->where('academic_period_id', $periodId));
            })
            ->latest('validated_at')
            ->take(10)
            ->get();

        // Quick Stats by Status (including new statuses)
        $statusStats = [
            'menunggu' => $getInclusiveCount($pendingStatuses, $periodId),
            'disetujui' => $getInclusiveCount($approvedStatuses, $periodId),
            'ditolak' => $getInclusiveCount($rejectedStatuses, $periodId),
            'revisi' => StudentAchievement::whereIn('validation_status', ['Revisi', 'faculty_revision'])->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            // New two-stage statuses
            'faculty_pending' => StudentAchievement::facultyPending()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'faculty_approved' => StudentAchievement::facultyApproved()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'university_approved' => StudentAchievement::universityApproved()->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
        ];

        // Monitoring Statistics
        $statistics = $this->getApprovalStatistics($periodId);
        $monthlyTrend = $this->getMonthlyTrend(6, $periodId);
        $levelDistribution = $this->getLevelDistribution($periodId);
        $topPerformers = $this->getTopPerformers($periodId);
        $facultyComparison = $this->getFacultyComparison($periodId);
        $categoryDistribution = $this->getCategoryDistribution($periodId);
        $topProgramStudies = $this->getProgramStudyRanking($periodId);
        $anomalies = $this->getAnomalies($periodId);
        $systemActivities = $this->getSystemActivities();
        $masterData = $this->getMasterDataSummary();

        // Specific data for Archived View
        $archivedStats = null;
        if ($isInactivePeriod) {
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

            $ratioData = $facultyComparison->map(function ($f) use ($facultyStudentCounts) {
                $studentCount = $facultyStudentCounts[$f->full_name]->student_count ?? 1;
                return (object) [
                    'faculty' => $f->faculty,
                    'full_name' => $f->full_name,
                    'ratio' => round(($f->total / $studentCount) * 100, 2)
                ];
            })->sortByDesc('ratio')->values();

            $archivedStats = [
                'total_faculties' => $totalFaculties,
                'active_faculties' => $activeFaculties,
                'faculty_ratios' => $ratioData,
            ];
        }

        // Specific data for Active View
        $activeStats = null;
        if ($isActivePeriod) {
            $dailyTrend = $this->getDailyValidationTrend($periodId);
            $facultyBacklog = $this->getFacultyBacklog($periodId);
            $criticalQueue = $this->getCriticalQueue($periodId);

            $activeStats = [
                'daily_trend' => $dailyTrend,
                'faculty_backlog' => $facultyBacklog,
                'critical_queue' => $criticalQueue,
            ];
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentAchievements',
            'universityPending',
            'appealPending',
            'urgentPending',
            'recentValidations',
            'statusStats',
            'activePeriod',
            'periods',
            'selectedPeriod',
            'periodComparison',
            'globalTrend',
            'statistics',
            'monthlyTrend',
            'levelDistribution',
            'topPerformers',
            'facultyComparison',
            'categoryDistribution',
            'topProgramStudies',
            'anomalies',
            'systemActivities',
            'isActivePeriod',
            'isInactivePeriod',
            'isGlobal',
            'masterData',
            'archivedStats',
            'activeStats'
        ));
    }

    // Helper methods for monitoring dashboard
    protected function getPeriodComparison()
    {
        return \DB::table('student_achievements')
            ->join('academic_periods', 'student_achievements.academic_period_id', '=', 'academic_periods.id')
            ->selectRaw("
                academic_periods.name as period,
                COUNT(*) as total,
                SUM(CASE WHEN validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN validation_status = 'Menunggu' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN validation_status = 'Ditolak' THEN 1 ELSE 0 END) as rejected
            ")
            ->groupBy('academic_periods.id', 'academic_periods.name', 'academic_periods.year', 'academic_periods.semester')
            ->orderBy('academic_periods.year', 'desc')
            ->orderBy('academic_periods.semester', 'desc')
            ->get();
    }

    protected function getApprovalStatistics($periodId = null)
    {
        $query = StudentAchievement::query();
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        $total = $query->count();
        $approved = (clone $query)->where('validation_status', 'Disetujui')->count();
        $pending = (clone $query)->where('validation_status', 'Menunggu')->count();

        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        // Calculate average time to approve
        $avgDays = \DB::table('student_achievements')
            ->whereNotNull('validator_id')
            ->whereIn('validation_status', ['Disetujui', 'Ditolak'])
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - submitted_at))/86400) as avg_days')
            ->value('avg_days') ?? 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'approval_rate' => $approvalRate,
            'avg_time_to_approve' => round($avgDays, 1)
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
                        ? \Carbon\Carbon::parse($period->start_date)->format('M Y')
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
        $query = StudentAchievement::selectRaw('level, COUNT(*) as count')
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->groupBy('level')
            ->pluck('count', 'level');

        return [
            'Internasional' => $query->get('Internasional', 0),
            'Nasional' => $query->get('Nasional', 0),
            'Universitas' => $query->get('Universitas', 0),
        ];
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
                    WHEN student_achievements.validation_status IN ('Disetujui', 'university_approved', 'appeal_approved') THEN 1 
                    ELSE 0 
                END) as approved,
                SUM(CASE 
                    WHEN student_achievements.validation_status IN ('Menunggu', 'submitted', 'faculty_review', 'faculty_approved', 'university_review', 'appeal_submitted') THEN 1 
                    ELSE 0 
                END) as pending,
                SUM(CASE 
                    WHEN student_achievements.validation_status IN ('Ditolak', 'faculty_rejected', 'university_rejected', 'appeal_rejected') THEN 1 
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
                    WHEN validation_status IN ('Disetujui', 'university_approved', 'appeal_approved') THEN 1 
                    ELSE 0 
                END) as approved
            ")
            ->when($periodId, fn($q) => $q->where('student_achievements.academic_period_id', $periodId))
            ->groupBy('students.faculty', 'students.program_study')
            ->orderByDesc('total')
            ->get();
    }

    protected function getAnomalies($periodId = null)
    {
        $baseQuery = StudentAchievement::when($periodId, fn($q) => $q->where('academic_period_id', $periodId));

        return [
            'duplicates' => \DB::table('student_achievements')
                ->select('student_id', 'event_name', \DB::raw('COUNT(*) as count'))
                ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
                ->groupBy('student_id', 'event_name')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count(),

            'no_docs' => (clone $baseQuery)
                ->whereNull('certificate')
                ->whereNull('alternative_document_path')
                ->count(),

            'sla_breach' => (clone $baseQuery)
                ->where('validation_status', 'Menunggu')
                ->where('submitted_at', '<', now()->subDays(7))
                ->count(),

            'sync_issue' => (clone $baseQuery)
                ->where(function ($q) {
                    $q->where('level', 'Universitas')->where('event_name', 'ILIKE', '%Nasional%')
                        ->orWhere('level', 'Universitas')->where('event_name', 'ILIKE', '%Internasional%')
                        ->orWhere('level', 'Nasional')->where('event_name', 'ILIKE', '%Internasional%');
                })
                ->count()
        ];
    }

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
        return \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->selectRaw("COALESCE(students.faculty, 'N/A') as faculty, COUNT(*) as count")
            ->where('student_achievements.validation_status', 'Menunggu')
            ->when($periodId, fn($q) => $q->where('student_achievements.academic_period_id', $periodId))
            ->groupBy('students.faculty')
            ->orderByDesc('count')
            ->get();
    }

    protected function getCriticalQueue($periodId = null, $limit = 5)
    {
        return StudentAchievement::with(['student', 'achievement.category'])
            ->where('validation_status', 'Menunggu')
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('submitted_at', 'asc')
            ->take($limit)
            ->get();
    }

    protected function getMasterDataSummary()
    {
        return [
            'faculties_count' => \DB::table('students')->distinct()->count('faculty'),
            'prodis_count' => \DB::table('students')->distinct()->count('program_study'),
            'validators_count' => User::where('role', 'Validator')->where('is_active', true)->count(),
            'operators_count' => User::where('role', 'Admin')->where('is_active', true)->count(),
            'sync_status' => [
                'status' => 'Stable',
                'last_sync' => now()->subMinutes(rand(5, 60))->format('H:i'),
                'percentage' => 100
            ]
        ];
    }
}
