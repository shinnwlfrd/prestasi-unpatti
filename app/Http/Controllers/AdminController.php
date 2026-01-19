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
        $stats = [
            'students' => Student::count(),
            'achievements' => StudentAchievement::count(),
            'pending' => StudentAchievement::where('validation_status', 'Menunggu')->count(),
            'approved' => StudentAchievement::where('validation_status', 'Disetujui')->count(),
        ];
        $recentAchievements = StudentAchievement::with(['student', 'achievement'])
            ->latest()->take(5)->get();
        return view('admin.dashboard', compact('stats', 'recentAchievements'));
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
        $types = Achievement::all();
        return view('admin.achievements.index', compact('types'));
    }

    // Student Achievements
    public function studentAchievements()
    {
        $achievements = StudentAchievement::with(['student', 'achievement', 'validator'])->latest()->paginate(15);
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
        ]);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}
