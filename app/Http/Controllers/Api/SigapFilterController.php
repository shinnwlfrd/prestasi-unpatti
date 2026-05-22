<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SigapApiService;
use Illuminate\Http\Request;

class SigapFilterController extends Controller
{
    protected SigapApiService $sigapService;

    public function __construct(SigapApiService $sigapService)
    {
        $this->sigapService = $sigapService;
    }

    /**
     * Get all faculties
     */
    public function getFaculties()
    {
        $faculties = $this->sigapService->getFaculties();

        return $this->buildSigapResponse($faculties);
    }

    /**
     * Get departments by faculty
     */
    public function getDepartments(Request $request)
    {
        $facultyId = $request->input('faculty_id');
        $departments = $this->sigapService->getDepartments($facultyId);

        return $this->buildSigapResponse($departments, [
            'faculty_id' => $facultyId,
        ]);
    }

    /**
     * Get study programs by department
     */
    public function getStudyPrograms(Request $request)
    {
        $departmentId = $request->input('department_id');
        $studyPrograms = $this->sigapService->getStudyPrograms($departmentId);

        return $this->buildSigapResponse($studyPrograms, [
            'department_id' => $departmentId,
        ]);
    }

    /**
     * Get hierarchical structure
     */
    public function getHierarchy()
    {
        $hierarchy = $this->sigapService->getHierarchicalStructure();

        return $this->buildSigapResponse($hierarchy);
    }

    private function buildSigapResponse(array $data, array $extra = [])
    {
        $status = $this->sigapService->getLastOperationStatus();
        $payload = array_merge([
            'success' => $status['success'] ?? true,
            'data' => $data,
            'source' => $status['source'] ?? 'unknown',
            'message' => $status['message'] ?? null,
            'meta' => $status['meta'] ?? [],
        ], $extra);

        $httpCode = ($status['success'] ?? true) ? 200 : 502;

        return response()->json($payload, $httpCode);
    }
}
