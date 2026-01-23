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
        // Basic Statistics
        $stats = [
            'students' => Student::count(),
            'achievements' => StudentAchievement::count(),
            'pending' => StudentAchievement::where('validation_status', 'Menunggu')->count(),
            'approved' => StudentAchievement::where('validation_status', 'Disetujui')->count(),
            'validators' => User::where('role', 'Validator')->where('is_active', true)->count(),
            'users' => User::count(),
        ];

        // Recent Achievements (last 10)
        $recentAchievements = StudentAchievement::with(['student', 'achievement.category'])
            ->latest()
            ->take(10)
            ->get();

        // Pending Review (urgent - older than 7 days)
        $urgentPending = StudentAchievement::with(['student', 'achievement.category'])
            ->where('validation_status', 'Menunggu')
            ->where('submitted_at', '<', now()->subDays(7))
            ->orderBy('submitted_at', 'asc')
            ->take(5)
            ->get();

        // Recent Validations (last 10)
        $recentValidations = ValidationLog::with(['studentAchievement.student', 'validator'])
            ->latest('validated_at')
            ->take(10)
            ->get();

        // Active Period
        $activePeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();

        // Faculty Validation Statistics (for active period only)
        $facultyValidationStats = collect();
        if ($activePeriod) {
            // Mapping nama fakultas - konversi nama panjang ke singkatan
            $facultyMapping = [
                'Fakultas Teknik' => 'FT',
                'Fakultas Hukum' => 'FH',
                'Fakultas Ekonomi dan Bisnis' => 'FEB',
                'Fakultas Kedokteran' => 'FK',
                'Fakultas Ilmu Sosial dan Ilmu Politik' => 'FISIP',
                'Fakultas Perikanan dan Ilmu Kelautan' => 'FPIK',
                'Fakultas Sains dan Teknologi' => 'FST',
                'Fakultas Keguruan dan Ilmu Pendidikan' => 'FKIP',
                'Fakultas Pertanian' => 'FP',
                'N/A' => 'N/A',
            ];

            $facultyValidationStats = \DB::table('student_achievements')
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->where('student_achievements.academic_period_id', $activePeriod->id)
                ->whereIn('student_achievements.validation_status', ['Disetujui', 'Ditolak', 'Revisi'])
                ->selectRaw("
                    COALESCE(students.faculty, 'N/A') as faculty,
                    COUNT(*) as total_validated,
                    SUM(CASE WHEN student_achievements.validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN student_achievements.validation_status = 'Ditolak' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN student_achievements.validation_status = 'Revisi' THEN 1 ELSE 0 END) as revision
                ")
                ->groupBy('students.faculty')
                ->orderByDesc('total_validated')
                ->take(10)
                ->get()
                ->map(function ($item) use ($facultyMapping) {
                    return [
                        'faculty' => $facultyMapping[$item->faculty] ?? $item->faculty,
                        'total_validated' => $item->total_validated,
                        'approved' => $item->approved,
                        'rejected' => $item->rejected,
                        'revision' => $item->revision,
                        'approval_rate' => $item->total_validated > 0 ? round(($item->approved / $item->total_validated) * 100, 1) : 0,
                    ];
                });
        }

        // Quick Stats by Status
        $statusStats = [
            'menunggu' => StudentAchievement::where('validation_status', 'Menunggu')->count(),
            'disetujui' => StudentAchievement::where('validation_status', 'Disetujui')->count(),
            'ditolak' => StudentAchievement::where('validation_status', 'Ditolak')->count(),
            'revisi' => StudentAchievement::where('validation_status', 'Revisi')->count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentAchievements',
            'urgentPending',
            'recentValidations',
            'facultyValidationStats',
            'statusStats',
            'activePeriod'
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
}
