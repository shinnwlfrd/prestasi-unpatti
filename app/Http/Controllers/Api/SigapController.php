<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SigapApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SigapController extends Controller
{
    protected $sigapService;

    public function __construct(SigapApiService $sigapService)
    {
        $this->sigapService = $sigapService;
    }

    /**
     * Get all faculties
     */
    public function faculties()
    {
        $faculties = $this->sigapService->getFaculties();

        return response()->json([
            'success' => true,
            'data' => $faculties,
            'count' => count($faculties)
        ]);
    }

    /**
     * Get departments by faculty
     */
    public function departments(Request $request)
    {
        $facultyId = $request->query('faculty_id');
        $departments = $this->sigapService->getDepartments($facultyId);

        return response()->json([
            'success' => true,
            'data' => $departments,
            'count' => count($departments),
            'faculty_id' => $facultyId
        ]);
    }

    /**
     * Get study programs by department
     */
    public function studyPrograms(Request $request)
    {
        $departmentId = $request->query('department_id');
        $studyPrograms = $this->sigapService->getStudyPrograms($departmentId);

        return response()->json([
            'success' => true,
            'data' => $studyPrograms,
            'count' => count($studyPrograms),
            'department_id' => $departmentId
        ]);
    }

    /**
     * Get hierarchical structure
     */
    public function hierarchy()
    {
        $structure = $this->sigapService->getHierarchicalStructure();

        return response()->json([
            'success' => true,
            'data' => $structure,
            'count' => count($structure)
        ]);
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        $this->sigapService->clearCache();

        Log::warning('Cache cleared by user', [
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully'
        ]);
    }

    /**
     * Search students by name or NIM
     */
    public function searchStudents(Request $request)
    {
        $query = $request->query('q', '');

        // Minimum 2 characters
        if (strlen($query) < 2) {
            return response()->json([
                'status' => 'ok'
            ]);
        }

        try {
            Log::info('Protected student search accessed', [
                'user_id' => auth()->id(),
                'query_length' => strlen($query),
            ]);

            return response()->json([
                'status' => 'ok'
            ]);
        } catch (\Exception $e) {
            Log::warning('Student search failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'ok'
            ], 500);
        }
    }
}
