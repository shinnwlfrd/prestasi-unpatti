<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAchievementRequest;
use App\Models\Achievement;
use App\Models\AchievementLevel;
use App\Services\Student\AchievementService;

class AchievementController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {}

    public function create()
    {
        $achievements = Achievement::with('category')->get();
        $levels = AchievementLevel::active()->get();

        return view('student.achievement.create', compact('achievements', 'levels'));
    }

    public function store(SubmitAchievementRequest $request)
    {
        $studentId = session('student_id');

        if (! $studentId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $achievement = $this->achievementService->submitAchievement(
            $request->validated(),
            $studentId,
            $request->file('certificate')
        );

        return redirect()->route('achievements.documents.index', $achievement)
            ->with('success', 'Prestasi berhasil diajukan. Silakan upload dokumen pendukung.');
    }
}
