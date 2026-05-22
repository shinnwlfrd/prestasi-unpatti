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
        // Admin can see ALL achievements including soft-deleted ones
        $query = $this->model->withTrashed()->with(['student', 'achievement.category', 'validator']);

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

        // Filter by status (Grouped or Literal)
        if (! empty($filters['status'])) {
            $status = $filters['status'];

            $statusGroups = StudentAchievement::getRepositoryStatusGroups();

            if (array_key_exists($status, $statusGroups)) {
                $query->whereIn('validation_status', $statusGroups[$status]);
            } else {
                $query->where('validation_status', $status);
            }
        }

        // Filter abandoned drafts (drafts older than 30 days)
        if (! empty($filters['abandoned'])) {
            $query->where('validation_status', StudentAchievement::STATUS_DRAFT)
                ->where('updated_at', '<', now()->subDays(30));
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

        // Filter by SIGAP faculty_id
        if (! empty($filters['faculty_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('faculty_id', $filters['faculty_id']);
            });
        }

        // Filter by SIGAP department_id
        if (! empty($filters['department_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        // Filter by SIGAP program_study_id
        if (! empty($filters['program_study_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('program_study_id', $filters['program_study_id']);
            });
        }

        // Admin can see ALL achievements regardless of status
        // No filtering by validation stage - show everything

        return $query->latest('submitted_at')->latest('sa_id')->paginate($perPage)->withQueryString();
    }

    public function getPendingForValidator(?string $faculty = null): Collection
    {
        // Validators should not see soft-deleted achievements
        $query = $this->model->with(['student', 'achievement.category', 'documents'])
            ->whereIn('validation_status', [
                StudentAchievement::STATUS_PENDING,
                StudentAchievement::STATUS_SUBMITTED,
                StudentAchievement::STATUS_FACULTY_REVIEW,
            ])
            ->whereNull('deleted_at')
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
        // Admin dashboard should exclude soft-deleted for recent achievements
        return $this->model->with(['student', 'achievement.category'])
            ->whereNull('deleted_at')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getUrgentPending(int $days = 7, int $limit = 5): Collection
    {
        // Urgent pending should exclude soft-deleted
        return $this->model->with(['student', 'achievement.category'])
            ->pending()
            ->whereNull('deleted_at')
            ->where('submitted_at', '<', now()->subDays($days))
            ->orderBy('submitted_at', 'asc')
            ->take($limit)
            ->get();
    }

    public function countByStatus(string $status): int
    {
        // Status counts should exclude soft-deleted
        return $this->model->where('validation_status', $status)
            ->whereNull('deleted_at')
            ->count();
    }

    public function getStatusStatistics(): array
    {
        return [
            'menunggu' => $this->model->whereNull('deleted_at')->pending()->count(),
            'disetujui' => $this->model->whereNull('deleted_at')->approved()->count(),
            'ditolak' => $this->model->whereNull('deleted_at')->rejected()->count(),
            'revisi' => $this->model->whereNull('deleted_at')->needRevision()->count(),
        ];
    }
}
