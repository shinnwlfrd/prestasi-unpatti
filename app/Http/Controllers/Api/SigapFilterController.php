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
        
        return response()->json([
            'success' => true,
            'data' => $faculties
        ]);
    }

    /**
     * Get departments by faculty
     */
    public function getDepartments(Request $request)
    {
        $facultyId = $request->input('faculty_id');
        
        $departments = $this->sigapService->getDepartments($facultyId);
        
        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    /**
     * Get study programs by department
     */
    public function getStudyPrograms(Request $request)
    {
        $departmentId = $request->input('department_id');
        
        $studyPrograms = $this->sigapService->getStudyPrograms($departmentId);
        
        return response()->json([
            'success' => true,
            'data' => $studyPrograms
        ]);
    }

    /**
     * Get hierarchical structure
     */
    public function getHierarchy()
    {
        $hierarchy = $this->sigapService->getHierarchicalStructure();
        
        return response()->json([
            'success' => true,
            'data' => $hierarchy
        ]);
    }
}
