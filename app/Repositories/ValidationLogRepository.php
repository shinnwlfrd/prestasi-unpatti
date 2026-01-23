<?php

namespace App\Repositories;

use App\Models\ValidationLog;
use App\Repositories\Contracts\ValidationLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ValidationLogRepository implements ValidationLogRepositoryInterface
{
    protected ValidationLog $model;

    public function __construct(ValidationLog $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?ValidationLog
    {
        return $this->model->find($id);
    }

    public function create(array $data): ValidationLog
    {
        return $this->model->create($data);
    }

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['studentAchievement.student', 'validator']);

        // Search by student name, NIM, or event name
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('studentAchievement', function ($q) use ($search) {
                    $q->where('event_name', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('student_id', 'like', "%{$search}%");
                        });
                });
            });
        }

        // Filter by new_status (decision)
        if (! empty($filters['decision'])) {
            $query->where('new_status', $filters['decision']);
        }

        // Filter by validator
        if (! empty($filters['validator'])) {
            $query->where('validator_id', $filters['validator']);
        }

        // Filter by date range
        if (! empty($filters['date_from'])) {
            $query->whereDate('validated_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('validated_at', '<=', $filters['date_to']);
        }

        return $query->latest('validated_at')->paginate($perPage)->withQueryString();
    }

    public function getRecentValidations(int $limit = 10): Collection
    {
        return $this->model->with(['studentAchievement.student', 'validator'])
            ->latest('validated_at')
            ->take($limit)
            ->get();
    }

    public function getByValidator(int $validatorId): Collection
    {
        return $this->model->where('validator_id', $validatorId)->get();
    }

    public function getByAchievement(int $achievementId): Collection
    {
        return $this->model->where('sa_id', $achievementId)->get();
    }

    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'approved' => $this->countByStatus('Disetujui'),
            'rejected' => $this->countByStatus('Ditolak'),
            'revision' => $this->countByStatus('Revisi'),
            'today' => $this->countToday(),
        ];
    }

    public function countByStatus(string $status): int
    {
        return $this->model->where('new_status', $status)->count();
    }

    public function countToday(): int
    {
        return $this->model->whereDate('validated_at', today())->count();
    }
}
