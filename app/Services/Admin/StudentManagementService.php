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

    /**
     * Apply scope filters based on user role (Pimpinan/Operator)
     */
    protected function applyScopeFilters(array $filters): array
    {
        $user = auth()->user();

        // Super admin can see everything
        if ($user->isSuperAdmin()) {
            return $filters;
        }

        // Pimpinan scope filtering
        if ($user->isPimpinan()) {
            $level = session('pimpinan_level');
            
            if ($level === 'faculty') {
                $filters['faculty_id'] = session('pimpinan_faculty_id');
            } elseif ($level === 'department') {
                $filters['faculty_id'] = session('pimpinan_faculty_id');
                $filters['department_id'] = session('pimpinan_department_id');
            } elseif ($level === 'program_study') {
                $filters['faculty_id'] = session('pimpinan_faculty_id');
                $filters['department_id'] = session('pimpinan_department_id');
                $filters['program_study_id'] = session('pimpinan_program_study_id');
            }
        }

        // Operator scope filtering
        if ($user->isOperator()) {
            $level = session('operator_level');
            
            if ($level === 'faculty') {
                $filters['faculty_id'] = session('operator_faculty_id');
            } elseif ($level === 'department') {
                $filters['faculty_id'] = session('operator_faculty_id');
                $filters['department_id'] = session('operator_department_id');
            } elseif ($level === 'program_study') {
                $filters['faculty_id'] = session('operator_faculty_id');
                $filters['department_id'] = session('operator_department_id');
                $filters['program_study_id'] = session('operator_program_study_id');
            }
        }

        return $filters;
    }
}
