<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SigapApiService;
use Illuminate\Http\Request;

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
                'success' => false,
                'message' => 'Query must be at least 2 characters',
                'data' => []
            ]);
        }

        try {
            $students = \App\Models\Student::query()
                ->where(function($q) use ($query) {
                    // If query is numeric, search student_id starting with the query
                    if (is_numeric($query)) {
                        $q->where('student_id', 'LIKE', "{$query}%");
                    } else {
                        // If query contains text, search in name (anywhere) or student_id (starting with)
                        $q->where('name', 'ILIKE', "%{$query}%")
                          ->orWhere('student_id', 'LIKE', "{$query}%");
                    }
                })
                ->select('student_id', 'name', 'faculty', 'department', 'program', 'angkatan')
                ->orderBy('student_id')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $students,
                'count' => $students->count(),
                'query' => $query
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching students: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}

