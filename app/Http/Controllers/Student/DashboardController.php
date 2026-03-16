<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\AchievementService;

class DashboardController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {}

    public function index()
    {
        $studentId = session('student_id');

        if (! $studentId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $student = \App\Models\Student::find($studentId);

        if (! $student) {
            return redirect()->route('login')->with('error', 'Student not found.');
        }

        $achievements = $this->achievementService->getStudentAchievements($studentId);
        $stats = $this->achievementService->getAchievementStatistics($studentId);

        return view('student.dashboard', compact('student', 'achievements', 'stats'));
    }
}
