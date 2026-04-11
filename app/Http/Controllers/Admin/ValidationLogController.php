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
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $filters = $request->validated();

        // Scope data based on current role if not super admin
        if (!$user->isSuperAdmin()) {
            if ($currentRole && $currentRole->level !== 'university') {
                if ($currentRole->faculty_id) {
                    $filters['faculty_id'] = $currentRole->faculty_id;
                    $request->merge(['faculty_id' => $currentRole->faculty_id]);
                }
                if ($currentRole->department_id) {
                    $filters['department_id'] = $currentRole->department_id;
                    $request->merge(['department_id' => $currentRole->department_id]);
                }
                if ($currentRole->program_study_id) {
                    $filters['program_study_id'] = $currentRole->program_study_id;
                    $request->merge(['program_study_id' => $currentRole->program_study_id]);
                }
            }
        }

        $perPage = $request->input('per_page', 15);
        $logs = $this->validationLogRepo->getWithFilters($filters, $perPage);

        $validators = $this->userRepo->getValidators();
        $stats = $this->validationLogRepo->getStatistics();

        // Get SIGAP data for cascade filter
        $sigapService = app(\App\Services\SigapApiService::class);
        
        // Only show faculty selection if user is Super Admin or University-level
        $sigapFaculties = collect();
        if ($user->isSuperAdmin() || ($currentRole && $currentRole->level === 'university')) {
            $sigapFaculties = collect($sigapService->getFaculties());
        }
        
        $targetFacultyId = $filters['faculty_id'] ?? $request->faculty_id;
        $sigapDepartments = collect();
        if ($targetFacultyId) {
            $sigapDepartments = collect($sigapService->getDepartments($targetFacultyId));
        }
        
        $targetDepartmentId = $filters['department_id'] ?? $request->department_id;
        $sigapStudyPrograms = collect();
        if ($targetDepartmentId) {
            $sigapStudyPrograms = collect($sigapService->getStudyPrograms($targetDepartmentId));
        }

        return view('admin.validation-logs.index', [
            'logs' => $logs,
            'validators' => $validators,
            'stats' => $stats,
            'sigapFaculties' => $sigapFaculties,
            'sigapDepartments' => $sigapDepartments,
            'sigapStudyPrograms' => $sigapStudyPrograms,
            'selectedFaculty' => $targetFacultyId,
            'selectedDepartment' => $targetDepartmentId,
            'selectedStudyProgram' => $filters['program_study_id'] ?? $request->program_study_id,
            'isFacultyScoped' => ($currentRole && $currentRole->level !== 'university' && $currentRole->faculty_id),
            'currentRole' => $currentRole
        ]);
    }
}
