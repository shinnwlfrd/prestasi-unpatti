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

        // Apply scope filters for Pimpinan and Operator
        $filters = $this->applyScopeFilters($filters);

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
            $status = $filters['decision'];
            $groups = [
                'approved' => ['faculty_approved', 'university_approved', 'Disetujui', 'appeal_approved'],
                'rejected' => ['faculty_rejected', 'university_rejected', 'Ditolak', 'appeal_rejected'],
                'revision' => ['faculty_revision', 'Revisi', 'revision_requested'],
                'Disetujui' => ['faculty_approved', 'university_approved', 'Disetujui', 'appeal_approved'],
                'Ditolak' => ['faculty_rejected', 'university_rejected', 'Ditolak', 'appeal_rejected'],
                'Revisi' => ['faculty_revision', 'Revisi', 'revision_requested'],
            ];

            if (isset($groups[$status])) {
                $query->whereIn('new_status', $groups[$status]);
            } else {
                $query->where('new_status', $status);
            }
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

        // Filter by SIGAP faculty_id
        if (! empty($filters['faculty_id'])) {
            $query->whereHas('studentAchievement.student', function ($q) use ($filters) {
                $q->where('faculty_id', $filters['faculty_id']);
            });
        }

        // Filter by SIGAP department_id
        if (! empty($filters['department_id'])) {
            $query->whereHas('studentAchievement.student', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        // Filter by SIGAP program_study_id
        if (! empty($filters['program_study_id'])) {
            $query->whereHas('studentAchievement.student', function ($q) use ($filters) {
                $q->where('program_study_id', $filters['program_study_id']);
            });
        }

        return $query->latest('validated_at')->paginate($perPage)->withQueryString();
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
            'approved' => $this->model->whereIn('new_status', ['faculty_approved', 'university_approved', 'Disetujui', 'appeal_approved'])->count(),
            'rejected' => $this->model->whereIn('new_status', ['faculty_rejected', 'university_rejected', 'Ditolak', 'appeal_rejected'])->count(),
            'revision' => $this->model->whereIn('new_status', ['faculty_revision', 'Revisi', 'revision_requested'])->count(),
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
