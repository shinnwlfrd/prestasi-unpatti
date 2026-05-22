<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected AchievementService $achievementService
    ) {}

    public function index(Request $request)
    {
        $periodId = $request->input('period');
        $data = $this->dashboardService->getDashboardData($periodId);

        return view('admin.dashboard', $data);
    }

    public function getAnomalyDetails($type, Request $request)
    {
        $periodId = $request->input('period');
        $context = $request->input('context', 'active');

        $validTypes = ['sla_breach', 'duplicates', 'no_docs', 'missing_documents', 'abandoned_drafts'];
        if (! in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid anomaly type'], 400);
        }

        $data = $this->dashboardService->getAnomalyDetailsData($type, $periodId, $context);

        return response()->json([
            'context' => $context,
            'type' => $type,
            'data' => $data,
        ]);
    }

    public function deleteAchievement($id)
    {
        $result = $this->achievementService->deleteAchievement($id);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, $result['status_code'] ?? 500);
    }

    public function getUnitDistribution(Request $request)
    {
        $periodId = $request->input('period');
        $data = $this->dashboardService->getUnitDistribution($periodId);

        return response()->json($data);
    }

    public function getIntegrationHealth()
    {
        return response()->json($this->dashboardService->getIntegrationHealthData());
    }
}
