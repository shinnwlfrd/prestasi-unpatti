<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get period filter from request
        $periodId = request()->input('period');
        
        // Get all periods for dropdown
        $periods = \App\Models\AcademicPeriod::ordered()->get();
        
        // Determine selected period
        $selectedPeriod = null;
        $periodComparison = null;
        
        if ($periodId === 'all' || $periodId === null || $periodId === '') {
            // "Semua Periode" selected - show period comparison
            $periodComparison = $this->getPeriodComparison();
            $periodId = null;
        } else {
            // Specific period selected
            $selectedPeriod = \App\Models\AcademicPeriod::find($periodId);
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
        $activePeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();

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

    // Students
    public function students(Request $request)
    {
        // Get unique faculties for filter dropdown
        $faculties = Student::select('faculty')
            ->distinct()
            ->whereNotNull('faculty')
            ->orderBy('faculty')
            ->pluck('faculty');

        // Calculate stats
        $facultyCount = $faculties->count();
        $avgGpa = round(Student::avg('gpa') ?? 0, 2);

        // Build query
        $query = Student::withCount('achievements');

        // Search by NIM or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by faculty
        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $students = $query->paginate(15)->withQueryString();

        return view('admin.students.index', compact('students', 'faculties', 'facultyCount', 'avgGpa'));
    }

    // Achievements Types
    public function achievementTypes()
    {
        $types = Achievement::with('category')->get();

        return view('admin.achievements.index', compact('types'));
    }

    // Student Achievements
    public function studentAchievements(Request $request)
    {
        $query = StudentAchievement::with(['student', 'achievement.category', 'validator']);

        // Search by student name, NIM, or event name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('achievement', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Per page
        $perPage = $request->input('per_page', 15);

        $achievements = $query->latest('sa_id')->paginate($perPage)->withQueryString();

        // Get categories for filter dropdown
        $categories = \App\Models\AchievementCategory::orderBy('name')->get();

        return view('admin.student-achievements.index', compact('achievements', 'categories'));
    }

    // Validation Logs
    public function validationLogs(Request $request)
    {
        $query = ValidationLog::with(['studentAchievement.student', 'validator']);

        // Search by student name, NIM, or event name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('studentAchievement', function ($q) use ($search) {
                    $q->where('event_name', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('student_id', 'like', "%{$search}%");
                        });
                });
            });
        }

        // Filter by new_status (decision)
        if ($request->filled('decision')) {
            $query->where('new_status', $request->decision);
        }

        // Filter by validator
        if ($request->filled('validator')) {
            $query->where('validator_id', $request->validator);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('validated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('validated_at', '<=', $request->date_to);
        }

        // Per page
        $perPage = $request->input('per_page', 15);

        $logs = $query->latest('validated_at')->paginate($perPage)->withQueryString();

        // Get validators for filter
        $validators = User::where('role', 'Validator')->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => ValidationLog::count(),
            'approved' => ValidationLog::where('new_status', 'Disetujui')->count(),
            'rejected' => ValidationLog::where('new_status', 'Ditolak')->count(),
            'revision' => ValidationLog::where('new_status', 'Revisi')->count(),
            'today' => ValidationLog::whereDate('validated_at', today())->count(),
        ];

        return view('admin.validation-logs.index', compact('logs', 'validators', 'stats'));
    }


    // Users
    public function users()
    {
        $users = User::paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:Admin,Validator',
            'faculty' => 'required_if:role,Validator|nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        // Check if faculty is already assigned to another validator
        if ($request->role === 'Validator' && $request->faculty && $request->faculty !== 'Semua Fakultas') {
            $existingValidator = User::where('role', 'Validator')
                ->where('faculty', $request->faculty)
                ->where('is_active', true)
                ->first();

            if ($existingValidator) {
                return redirect()->route('admin.users')
                    ->withErrors(['faculty' => 'Fakultas '.$request->faculty.' sudah memiliki validator aktif ('.$existingValidator->name.'). Satu fakultas hanya boleh memiliki satu validator.'])
                    ->withInput()
                    ->with('showModal', true);
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'faculty' => $request->faculty === 'Semua Fakultas' ? null : $request->faculty,
            'is_active' => true, // New users are always active
        ]);

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:Admin,Validator',
            'faculty' => 'required_if:role,Validator|nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        // Check if faculty is already assigned to another validator
        if ($request->role === 'Validator' && $request->faculty && $request->faculty !== 'Semua Fakultas') {
            $existingValidator = User::where('role', 'Validator')
                ->where('faculty', $request->faculty)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingValidator) {
                return redirect()->route('admin.users')
                    ->withErrors(['faculty' => 'Fakultas '.$request->faculty.' sudah memiliki validator aktif ('.$existingValidator->name.'). Satu fakultas hanya boleh memiliki satu validator.'])
                    ->withInput()
                    ->with('showModal', true)
                    ->with('editUserId', $user->id);
            }
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'faculty' => $request->faculty === 'Semua Fakultas' ? null : $request->faculty,
            'is_active' => $request->input('is_active', 0) == 1,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui.');
    }

    public function deleteUser(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User '.$userName.' berhasil dihapus.');
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
        $startDate = now()->subMonths($months)->startOfMonth();
        
        $results = \DB::table('student_achievements')
            ->selectRaw("
                TO_CHAR(submitted_at, 'Mon YYYY') as month,
                COUNT(*) as submitted,
                SUM(CASE WHEN validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved
            ")
            ->where('submitted_at', '>=', $startDate)
            ->whereNotNull('submitted_at')
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
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
