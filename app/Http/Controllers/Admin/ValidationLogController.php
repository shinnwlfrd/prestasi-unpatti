<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexValidationLogRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\ValidationLogRepositoryInterface;

class ValidationLogController extends Controller
{
    public function __construct(
        protected ValidationLogRepositoryInterface $validationLogRepo,
        protected UserRepositoryInterface $userRepo
    ) {}

    public function index(IndexValidationLogRequest $request)
    {
        $perPage = $request->input('per_page', 15);
        $logs = $this->validationLogRepo->getWithFilters(
            $request->validated(),
            $perPage
        );

        $validators = $this->userRepo->getValidators();
        $stats = $this->validationLogRepo->getStatistics();

        // Get SIGAP data for cascade filter
        $sigapService = app(\App\Services\SigapApiService::class);
        
        $sigapFaculties = collect($sigapService->getFaculties());
        
        $sigapDepartments = collect();
        if ($request->filled('faculty_id')) {
            $sigapDepartments = collect($sigapService->getDepartments($request->faculty_id));
        }
        
        $sigapStudyPrograms = collect();
        if ($request->filled('department_id')) {
            $sigapStudyPrograms = collect($sigapService->getStudyPrograms($request->department_id));
        }

        return view('admin.validation-logs.index', [
            'logs' => $logs,
            'validators' => $validators,
            'stats' => $stats,
            'sigapFaculties' => $sigapFaculties,
            'sigapDepartments' => $sigapDepartments,
            'sigapStudyPrograms' => $sigapStudyPrograms,
            'selectedFaculty' => $request->input('faculty_id'),
            'selectedDepartment' => $request->input('department_id'),
            'selectedStudyProgram' => $request->input('program_study_id'),
        ]);
    }
}
