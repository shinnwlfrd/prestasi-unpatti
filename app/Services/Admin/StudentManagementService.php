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
        return $this->studentRepo->getWithFilters($filters, $perPage);
    }

    public function getStatistics(): array
    {
        return [
            'total' => $this->studentRepo->count(),
            'faculty_count' => $this->studentRepo->getFaculties()->count(),
            'avg_gpa' => $this->studentRepo->averageGpa(),
        ];
    }

    public function getFaculties()
    {
        return $this->studentRepo->getFaculties();
    }
}
