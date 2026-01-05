<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Achievement;
use App\Models\StudentAchievement;

class StudentController extends Controller
{
    public function index()
    {
        $studentId = session('student_id');
        $student = \App\Models\Student::where('student_id', $studentId)->first();
        $studentAchievements = StudentAchievement::where('student_id', $studentId)
            ->with('achievement')
            ->get();

        return view('student.dashboard', compact('student', 'studentAchievements'));
    }

    public function create()
    {
        $types = Achievement::all(); // dari model Achievement
        return view('student.submit', compact('types'));
    }
}