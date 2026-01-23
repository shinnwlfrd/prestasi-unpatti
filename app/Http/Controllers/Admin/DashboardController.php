<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Services\Admin\StatisticsService;

class DashboardController extends Controller
{
    public function __construct(
        protected StatisticsService $statisticsService
    ) {}

    public function index()
    {
        $stats = $this->statisticsService->getDashboardStatistics();
        $statusStats = $this->statisticsService->getStatusStatistics();
        $recentAchievements = $this->statisticsService->getRecentAchievements(10);
        $urgentPending = $this->statisticsService->getUrgentPending(7, 5);
        $recentValidations = $this->statisticsService->getRecentValidations(10);
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $facultyValidationStats = $this->statisticsService->getFacultyValidationStats($activePeriod?->id);

        return view('admin.dashboard', compact(
            'stats',
            'recentAchievements',
            'urgentPending',
            'recentValidations',
            'facultyValidationStats',
            'statusStats',
            'activePeriod'
        ));
    }
}
