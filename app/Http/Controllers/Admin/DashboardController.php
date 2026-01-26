<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use App\Services\Admin\StatisticsService;

class DashboardController extends Controller
{
    public function __construct(
        protected StatisticsService $statisticsService
    ) {}

    public function index()
    {
        // Get period filter from request
        $periodId = request()->input('period');
        
        // Get all periods for dropdown
        $periods = AcademicPeriod::ordered()->get();
        
        // Determine selected period
        $selectedPeriod = null;
        $periodComparison = null;
        
        if ($periodId === 'all' || $periodId === null || $periodId === '') {
            // "Semua Periode" selected - show period comparison
            $periodComparison = $this->getPeriodComparison();
            $periodId = null;
        } else {
            // Specific period selected
            $selectedPeriod = AcademicPeriod::find($periodId);
        }

        // Basic Statistics
        $stats = [
            'students' => Student::count(),
            'achievements' => StudentAchievement::when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'pending' => StudentAchievement::where('validation_status', 'Menunggu')->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'approved' => StudentAchievement::where('validation_status', 'Disetujui')->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'validators' => User::where('role', 'Validator')->where('is_active', true)->count(),
            'users' => User::count(),
        ];

        // Recent Achievements (last 10)
        $recentAchievements = StudentAchievement::with(['student', 'achievement.category'])
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->latest()
            ->take(10)
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
            ->when($periodId, function($q) use ($periodId) {
                $q->whereHas('studentAchievement', fn($sq) => $sq->where('academic_period_id', $periodId));
            })
            ->latest('validated_at')
            ->take(10)
            ->get();

        // Active Period
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        // Quick Stats by Status
        $statusStats = [
            'menunggu' => StudentAchievement::where('validation_status', 'Menunggu')->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'disetujui' => StudentAchievement::where('validation_status', 'Disetujui')->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'ditolak' => StudentAchievement::where('validation_status', 'Ditolak')->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
            'revisi' => StudentAchievement::where('validation_status', 'Revisi')->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))->count(),
        ];

        // Monitoring Statistics
        $statistics = $this->getApprovalStatistics($periodId);
        $monthlyTrend = $this->getMonthlyTrend(6, $periodId);
        $levelDistribution = $this->getLevelDistribution($periodId);
        $topPerformers = $this->getTopPerformers($periodId);
        $facultyComparison = $this->getFacultyComparison($periodId);
        $categoryDistribution = $this->getCategoryDistribution($periodId);

        return view('admin.dashboard', compact(
            'stats',
            'recentAchievements',
            'urgentPending',
            'recentValidations',
            'statusStats',
            'activePeriod',
            'periods',
            'selectedPeriod',
            'periodComparison',
            'statistics',
            'monthlyTrend',
            'levelDistribution',
            'topPerformers',
            'facultyComparison',
            'categoryDistribution'
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
                    return collect([
                        (object)['month' => $period->start_date->format('M Y'), 'submitted' => 0, 'approved' => 0]
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
                (object)['month' => now()->format('M Y'), 'submitted' => 0, 'approved' => 0]
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
        return Student::whereHas('achievements', function($q) use ($periodId) {
                $q->where('validation_status', 'Disetujui');
                if ($periodId) {
                    $q->where('academic_period_id', $periodId);
                }
            })
            ->withCount([
                'achievements' => function($q) use ($periodId) {
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
        $query = \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->selectRaw("
                COALESCE(students.faculty, 'N/A') as faculty,
                COUNT(*) as total,
                SUM(CASE WHEN student_achievements.validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN student_achievements.validation_status = 'Menunggu' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN student_achievements.validation_status = 'Ditolak' THEN 1 ELSE 0 END) as rejected
            ")
            ->groupBy('students.faculty')
            ->orderByDesc('total');
        
        if ($periodId) {
            $query->where('student_achievements.academic_period_id', $periodId);
        }
        
        $results = $query->get();
        
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
        
        foreach ($results as $item) {
            if (isset($facultyMapping[$item->faculty])) {
                $item->faculty = $facultyMapping[$item->faculty];
            }
        }
        
        return collect($results);
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
            ->when($periodId, function($query) use ($periodId) {
                $query->where('student_achievements.academic_period_id', $periodId);
            })
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->get()
            ->keyBy('category_id');
        
        return $allCategories->map(function($category) use ($achievementCounts) {
            $counts = $achievementCounts->get($category->id);
            return (object)[
                'category' => $category->name,
                'total' => $counts->total ?? 0,
                'approved' => $counts->approved ?? 0,
                'color' => $category->color ?? '#8b5cf6'
            ];
        });
    }
}
