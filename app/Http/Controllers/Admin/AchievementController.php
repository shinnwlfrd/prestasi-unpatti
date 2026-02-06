<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAchievementRequest;
use App\Models\AchievementCategory;
use App\Repositories\Contracts\AchievementRepositoryInterface;

class AchievementController extends Controller
{
    public function __construct(
        protected AchievementRepositoryInterface $achievementRepo
    ) {}

    /**
     * Show achievement types/categories
     */
    public function types()
    {
        $categories = AchievementCategory::where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('admin.achievements.index', compact('categories'));
    }

    public function index(IndexAchievementRequest $request)
    {
        $perPage = $request->input('per_page', 15);
        $achievements = $this->achievementRepo->getWithFilters(
            $request->validated(),
            $perPage
        );

        $categories = AchievementCategory::orderBy('name')->get();

        // Get SIGAP data for cascade filter
        $sigapService = app(\App\Services\SigapApiService::class);
        
        $sigapFaculties = collect($sigapService->getFaculties());
        
        $sigapDepartments = collect();
        if ($request->filled('faculty_id')) {
            $sigapDepartments = collect($sigapService->getDepartments($request->faculty_id));
        }
        
        $sigapStudyPrograms = collect();
        if ($request->filled('department_id')) {
            $sigapStudyPrograms = collect($sigapService->getStudyPrograms($request->department_id));
        }

        return view('admin.student-achievements.index', [
            'achievements' => $achievements,
            'categories' => $categories,
            'sigapFaculties' => $sigapFaculties,
            'sigapDepartments' => $sigapDepartments,
            'sigapStudyPrograms' => $sigapStudyPrograms,
            'selectedFaculty' => $request->input('faculty_id'),
            'selectedDepartment' => $request->input('department_id'),
            'selectedStudyProgram' => $request->input('program_study_id'),
        ]);
    }

    /**
     * Show achievement detail (read-only view)
     */
    public function show($id)
    {
        $achievement = \App\Models\StudentAchievement::with([
            'student',
            'achievement.category',
            'documents',
            'facultyValidator',
            'universityValidator',
            'latestAppeal'
        ])->findOrFail($id);

        return view('admin.student-achievements.show', compact('achievement'));
    }
}
