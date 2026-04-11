<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validator\IndexHistoryRequest;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Services\Validator\ValidationService;

class HistoryController extends Controller
{
    public function __construct(
        protected ValidationService $validationService
    ) {}

    public function index(IndexHistoryRequest $request)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        
        // Get scope from current role or session
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null);
        
        // For backward compatibility
        $faculty = $user->role === 'Operator' ? $user->faculty : null;

        $perPage = $request->input('per_page', 15);
        $logs = $this->validationService->getValidationHistory(
            $request->validated(),
            $faculty,
            $perPage,
            $level,
            $facultyId
        );

        $stats = $this->validationService->getHistoryStatistics($faculty, $level, $facultyId);
        $categories = AchievementCategory::orderBy('name')->get();
        $levels = AchievementLevel::where('is_active', true)->orderBy('order')->get();
        $statuses = [
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revision' => 'Revisi'
        ];
        
        // Get faculties for filter (only for super validator with university level)
        $faculties = collect();
        if ($level === 'university') {
            $faculties = \App\Models\Student::select('faculty_id', \Illuminate\Support\Facades\DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->orderBy('faculty')
                ->get()
                ->map(function ($item) {
                    $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();
                    return (object)[
                        'id' => $item->faculty_id,
                        'name' => $nameMap[$item->faculty_id] ?? $item->faculty
                    ];
                });
        }

        return view('validator.history', compact('logs', 'categories', 'levels', 'statuses', 'stats', 'faculties'));
    }
}
