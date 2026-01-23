<?php

namespace App\Repositories;

use App\Models\StudentAchievement;
use App\Repositories\Contracts\AchievementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AchievementRepository implements AchievementRepositoryInterface
{
    protected StudentAchievement $model;

    public function __construct(StudentAchievement $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?StudentAchievement
    {
        return $this->model->find($id);
    }

    public function create(array $data): StudentAchievement
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $achievement = $this->find($id);

        return $achievement ? $achievement->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $achievement = $this->find($id);

        return $achievement ? $achievement->delete() : false;
    }

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['student', 'achievement.category', 'validator']);

        // Search by student name, NIM, or event name
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if (! empty($filters['status'])) {
            $query->where('validation_status', $filters['status']);
        }

        // Filter by level
        if (! empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Filter by category
        if (! empty($filters['category'])) {
            $query->whereHas('achievement', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
        }

        return $query->latest('sa_id')->paginate($perPage)->withQueryString();
    }

    public function getPendingForValidator(?string $faculty = null): Collection
    {
        $query = $this->model->with(['student', 'achievement.category', 'documents'])
            ->whereIn('validation_status', ['pending', 'Menunggu'])
            ->orderByDesc('created_at');

        if ($faculty) {
            $query->whereHas('student', function ($q) use ($faculty) {
                $q->where('faculty', $faculty);
            });
        }

        return $query->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('validation_status', $status)->get();
    }

    public function getByStudent(string $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)->get();
    }

    public function getRecentAchievements(int $limit = 10): Collection
    {
        return $this->model->with(['student', 'achievement.category'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getUrgentPending(int $days = 7, int $limit = 5): Collection
    {
        return $this->model->with(['student', 'achievement.category'])
            ->where('validation_status', 'Menunggu')
            ->where('submitted_at', '<', now()->subDays($days))
            ->orderBy('submitted_at', 'asc')
            ->take($limit)
            ->get();
    }

    public function countByStatus(string $status): int
    {
        return $this->model->where('validation_status', $status)->count();
    }

    public function getStatusStatistics(): array
    {
        return [
            'menunggu' => $this->countByStatus('Menunggu'),
            'disetujui' => $this->countByStatus('Disetujui'),
            'ditolak' => $this->countByStatus('Ditolak'),
            'revisi' => $this->countByStatus('Revisi'),
        ];
    }
}
