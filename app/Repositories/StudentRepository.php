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
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by faculty
        if (! empty($filters['faculty'])) {
            $query->where('faculty', $filters['faculty']);
        }

        // Filter by semester
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

    public function averageGpa(): float
    {
        return round($this->model->avg('gpa') ?? 0, 2);
    }

    public function getFaculties()
    {
        return $this->model->select('faculty')
            ->distinct()
            ->whereNotNull('faculty')
            ->orderBy('faculty')
            ->pluck('faculty');
    }
}
