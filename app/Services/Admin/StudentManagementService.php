<?php

namespace App\Services\Admin;

use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentManagementService
{
    public function __construct(
        protected StudentRepositoryInterface $studentRepo
    ) {}

    public function getFilteredStudents(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        // Apply scope filtering for Pimpinan and Operator
        $filters = $this->applyScopeFilters($filters);
        
        return $this->studentRepo->getWithFilters($filters, $perPage);
    }

    public function getStatistics(): array
    {
        // Apply scope filtering for statistics
        $filters = $this->applyScopeFilters([]);
        
        return [
            'total' => $this->studentRepo->countWithFilters($filters),
            'faculty_count' => $this->studentRepo->getFacultiesWithFilters($filters)->count(),
            'avg_gpa' => $this->studentRepo->averageGpaWithFilters($filters),
        ];
    }

    public function getFaculties()
    {
        return $this->studentRepo->getFaculties();
    }

    public function getAngkatanList()
    {
        return \App\Models\Student::select('angkatan')
            ->distinct()
            ->whereNotNull('angkatan')
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');
    }

    public function getSigapCascadeData($filters)
    {
        $sigapService = app(\App\Services\SigapApiService::class);
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        // Only show faculty selection if user is Super Admin or University-level
        $sigapFaculties = collect();
        if ($user->isSuperAdmin() || ($currentRole && $currentRole->level === 'university')) {
            $sigapFaculties = collect($sigapService->getFaculties());
        }

        // Get departments based on selected faculty or scoped faculty
        $sigapDepartments = collect();
        $targetFacultyId = $filters['faculty_id'] ?? null;
        if ($targetFacultyId) {
            $sigapDepartments = collect($sigapService->getDepartments($targetFacultyId));
        }

        // Get study programs based on selected department or scoped department
        $sigapStudyPrograms = collect();
        $targetDepartmentId = $filters['department_id'] ?? null;
        if ($targetDepartmentId) {
            $sigapStudyPrograms = collect($sigapService->getStudyPrograms($targetDepartmentId));
        }

        return [
            'faculties' => $sigapFaculties,
            'departments' => $sigapDepartments,
            'study_programs' => $sigapStudyPrograms,
        ];
    }

    /**
     * Apply scope filters based on user role (Pimpinan/Operator/Admin)
     */
    protected function applyScopeFilters(array $filters): array
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        // Super admin or University level can see everything (filters apply as is)
        if ($user->isSuperAdmin() || ($currentRole && $currentRole->level === 'university')) {
            return $filters;
        }

        // Apply filters from role-based session data
        if ($currentRole) {
            if ($currentRole->faculty_id) {
                $filters['faculty_id'] = $currentRole->faculty_id;
            }
            if ($currentRole->department_id) {
                $filters['department_id'] = $currentRole->department_id;
            }
            if ($currentRole->program_study_id) {
                $filters['program_study_id'] = $currentRole->program_study_id;
            }
        }

        return $filters;
    }
}
