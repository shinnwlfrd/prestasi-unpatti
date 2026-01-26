<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validator\IndexPendingRequest;
use App\Models\AchievementCategory;
use App\Services\Validator\ValidationService;

class DashboardController extends Controller
{
    public function __construct(
        protected ValidationService $validationService
    ) {}

    public function index(IndexPendingRequest $request)
    {
        $user = auth()->user();
        $faculty = $user->role === 'Validator' ? $user->faculty : null;

        $pendingAchievements = $this->validationService->getPendingAchievements(
            $request->validated(),
            $faculty,
            15
        );

        $categories = AchievementCategory::orderBy('name')->get();
        $levels = ['Universitas', 'Nasional', 'Internasional'];
        
        // Get SK documents for validation modal
        $skDocuments = \App\Models\SKDocument::orderBy('issued_date', 'desc')->get();

        return view('validator.dashboard', compact('pendingAchievements', 'categories', 'levels', 'skDocuments'));
    }
}
