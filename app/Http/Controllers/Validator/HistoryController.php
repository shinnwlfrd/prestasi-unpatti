<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validator\IndexHistoryRequest;
use App\Models\AchievementCategory;
use App\Services\Validator\ValidationService;

class HistoryController extends Controller
{
    public function __construct(
        protected ValidationService $validationService
    ) {}

    public function index(IndexHistoryRequest $request)
    {
        $user = auth()->user();
        $faculty = $user->role === 'Validator' ? $user->faculty : null;

        $perPage = $request->input('per_page', 15);
        $logs = $this->validationService->getValidationHistory(
            $request->validated(),
            $faculty,
            $perPage
        );

        $stats = $this->validationService->getHistoryStatistics($faculty);
        $categories = AchievementCategory::orderBy('name')->get();
        $levels = ['Universitas', 'Nasional', 'Internasional'];
        $statuses = ['Disetujui', 'Ditolak', 'Revisi'];

        return view('validator.history', compact('logs', 'categories', 'levels', 'statuses', 'stats'));
    }
}
