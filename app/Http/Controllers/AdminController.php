<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AuthLog;
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
        
        // Recent Validations (last 5)
        $recentValidations = ValidationLog::with(['studentAchievement.student', 'validator'])
            ->latest('validated_at')
            ->take(5)
            ->get();
        
        // Quick Stats by Status
        $statusStats = [
            'menunggu' => StudentAchievement::where('validation_status', 'Menunggu')->count(),
            'disetujui' => StudentAchievement::where('validation_status', 'Disetujui')->count(),
            'ditolak' => StudentAchievement::where('validation_status', 'Ditolak')->count(),
            'revisi' => StudentAchievement::where('validation_status', 'Revisi')->count(),
        ];
        
        // Active Period
        $activePeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();
        
        return view('admin.dashboard', compact(
            'stats',
            'recentAchievements',
            'urgentPending',
            'recentValidations',
            'statusStats',
            'activePeriod'
        ));
    }

    // Students
    public function students()
    {
        $students = Student::withCount('achievements')->paginate(15);
        return view('admin.students.index', compact('students'));
    }

    // Achievements Types
    public function achievementTypes()
    {
        $types = Achievement::with('category')->get();
        return view('admin.achievements.index', compact('types'));
    }

    // Student Achievements
    public function studentAchievements()
    {
        $achievements = StudentAchievement::with(['student', 'achievement.category', 'validator'])->latest()->paginate(15);
        return view('admin.student-achievements.index', compact('achievements'));
    }

    // Validation Logs
    public function validationLogs()
    {
        $logs = ValidationLog::with(['studentAchievement.student', 'validator'])->latest()->paginate(15);
        return view('admin.validation-logs.index', compact('logs'));
    }

    // Auth Logs
    public function authLogs()
    {
        $logs = AuthLog::with('user')->latest()->paginate(20);
        
        $stats = [
            'login_today' => AuthLog::where('action', 'login')
                ->whereDate('created_at', today())
                ->count(),
            'sso_logins' => AuthLog::where('action', 'login')
                ->where('method', 'sso')
                ->whereDate('created_at', today())
                ->count(),
            'local_logins' => AuthLog::where('action', 'login')
                ->where('method', 'local')
                ->whereDate('created_at', today())
                ->count(),
            'failed_logins' => AuthLog::where('action', 'failed_login')
                ->whereDate('created_at', today())
                ->count(),
        ];
        
        return view('admin.auth-logs', compact('logs', 'stats'));
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
                    ->withErrors(['faculty' => 'Fakultas ' . $request->faculty . ' sudah memiliki validator aktif (' . $existingValidator->name . '). Satu fakultas hanya boleh memiliki satu validator.'])
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
            'email' => 'required|email|unique:users,email,' . $user->id,
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
                    ->withErrors(['faculty' => 'Fakultas ' . $request->faculty . ' sudah memiliki validator aktif (' . $existingValidator->name . '). Satu fakultas hanya boleh memiliki satu validator.'])
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
        
        return redirect()->route('admin.users')->with('success', 'User ' . $userName . ' berhasil dihapus.');
    }
}
