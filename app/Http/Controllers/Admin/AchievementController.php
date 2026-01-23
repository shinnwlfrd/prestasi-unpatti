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

    public function index(IndexAchievementRequest $request)
    {
        $perPage = $request->input('per_page', 15);
        $achievements = $this->achievementRepo->getWithFilters(
            $request->validated(),
            $perPage
        );

        $categories = AchievementCategory::orderBy('name')->get();

        return view('admin.student-achievements.index', compact('achievements', 'categories'));
    }
}
