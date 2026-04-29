<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Services\Validator\ValidatorDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected ValidatorDashboardService $dashboardService
    ) {}

    /**
     * Display the main dashboard
     */
    public function index(Request $request)
    {
        $data = $this->dashboardService->getDashboardData($request);
        return view('validator.dashboard', $data);
    }

    /**
     * Get dashboard data for AJAX updates
     */
    public function getDashboardData(Request $request)
    {
        $data = $this->dashboardService->getDashboardData($request);
        return response()->json($data);
    }

    /**
     * Get hierarchical chart data for drill-down
     */
    public function getHierarchicalChartData(Request $request)
    {
        $filters = $request->all();
        $data = $this->dashboardService->getHierarchicalChartData($filters);
        return response()->json($data);
    }

    /**
     * Get participants for a specific event
     */
    public function getEventParticipants(Request $request)
    {
        $eventName = $request->query('event_name');
        $organizer = $request->query('organizer');
        $eventLevel = $request->query('level');

        if (!$eventName) {
            return response()->json(['error' => 'Event name is required'], 400);
        }

        $data = $this->dashboardService->getEventParticipantsData($eventName, $organizer, $eventLevel);
        return response()->json($data);
    }

    /**
     * Get SLA breach details for modal
     */
    public function getSlaBreachDetails(Request $request)
    {
        $breaches = $this->dashboardService->getSlaBreachDetailsData($request);

        return response()->json([
            'total' => $breaches->count(),
            'breaches' => $breaches
        ]);
    }
}
