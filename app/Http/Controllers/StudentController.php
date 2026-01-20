<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Achievement;
use App\Models\StudentAchievement;

class StudentController extends Controller
{
    /**
     * Display the student dashboard with achievements statistics.
     */
    public function index()
    {
        $studentId = session('student_id');

        // Eager load relationships to reduce queries
        $student = Student::where('student_id', $studentId)
            ->with('sikadCredential')
            ->first();

        // Get achievements with eager loading
        $studentAchievements = StudentAchievement::where('student_id', $studentId)
            ->with(['achievement', 'documents'])
            ->orderByDesc('created_at')
            ->get();

        return view('student.dashboard', compact('student', 'studentAchievements'));
    }

    /**
     * Show the form for submitting a new achievement.
     */
    public function create()
    {
        $types = Achievement::all();
        return view('student.submit', compact('types'));
    }

    /**
     * Display student profile.
     */
    public function profile()
    {
        $studentId = session('student_id');

        $student = Student::where('student_id', $studentId)
            ->with('sikadCredential')
            ->first();

        // Get achievement stats
        $achievements = StudentAchievement::where('student_id', $studentId)->get();
        $stats = [
            'total' => $achievements->count(),
            'approved' => $achievements->where('validation_status', 'Disetujui')->count(),
            'pending' => $achievements->where('validation_status', 'Menunggu')->count(),
            'rejected' => $achievements->where('validation_status', 'Ditolak')->count(),
        ];

        return view('student.profile', compact('student', 'stats'));
    }
}