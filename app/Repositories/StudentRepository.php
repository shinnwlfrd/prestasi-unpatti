<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository implements StudentRepositoryInterface
{
    protected Student $model;

    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(string $id): ?Student
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?Student
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): Student
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $student = $this->find($id);

        return $student ? $student->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $student = $this->find($id);

        return $student ? $student->delete() : false;
    }

    public function withAchievementsCount()
    {
        return $this->model->withCount('achievements');
    }

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->withCount('achievements');

        // Search by NIM or name
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by SIGAP faculty_id
        if (! empty($filters['faculty_id'])) {
            $query->where('faculty_id', $filters['faculty_id']);
        }

        // Filter by SIGAP department_id
        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Filter by SIGAP program_study_id
        if (! empty($filters['program_study_id'])) {
            $query->where('program_study_id', $filters['program_study_id']);
        }

        // Filter by old faculty field (for backward compatibility)
        if (! empty($filters['faculty'])) {
            $query->where('faculty', $filters['faculty']);
        }

        // Filter by angkatan
        if (! empty($filters['angkatan'])) {
            $query->where('angkatan', $filters['angkatan']);
        }

        // Filter by semester (for backward compatibility)
        if (! empty($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findByFaculty(string $faculty): Collection
    {
        return $this->model->where('faculty', $faculty)->get();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function countWithFilters(array $filters): int
    {
        $query = $this->model->query();

        if (!empty($filters['faculty_id'])) {
            $query->where('faculty_id', $filters['faculty_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['program_study_id'])) {
            $query->where('program_study_id', $filters['program_study_id']);
        }

        return $query->count();
    }

    public function averageGpa(): float
    {
        return round($this->model->avg('gpa') ?? 0, 2);
    }

    public function averageGpaWithFilters(array $filters): float
    {
        $query = $this->model->query();

        if (!empty($filters['faculty_id'])) {
            $query->where('faculty_id', $filters['faculty_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['program_study_id'])) {
            $query->where('program_study_id', $filters['program_study_id']);
        }

        return round($query->avg('gpa') ?? 0, 2);
    }

    public function getFaculties()
    {
        return $this->model->select('faculty')
            ->distinct()
            ->whereNotNull('faculty')
            ->orderBy('faculty')
            ->pluck('faculty');
    }

    public function getFacultiesWithFilters(array $filters)
    {
        $query = $this->model->select('faculty_id', 'faculty_name')
            ->distinct()
            ->whereNotNull('faculty_id');

        if (!empty($filters['faculty_id'])) {
            $query->where('faculty_id', $filters['faculty_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['program_study_id'])) {
            $query->where('program_study_id', $filters['program_study_id']);
        }

        return $query->get();
    }
}
