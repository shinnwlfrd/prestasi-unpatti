<?php

namespace App\Services\Validator;

use App\Models\StudentAchievement;
use App\Repositories\Contracts\AchievementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ValidationService
{
    public function __construct(
        protected AchievementRepositoryInterface $achievementRepo
    ) {
    }

    public function getPendingAchievements(array $filters, ?string $faculty = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = StudentAchievement::with(['student', 'achievement.category', 'documents'])
            ->whereIn('validation_status', ['pending', 'Menunggu'])
            ->orderByDesc('created_at');

        // Filter by faculty if validator has faculty assigned
        if ($faculty) {
            $query->whereHas('student', function ($q) use ($faculty) {
                $q->where('faculty', $faculty);
            });
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('organizer', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Level filter
        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Category filter
        if (!empty($filters['category'])) {
            $query->whereHas('achievement', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('event_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('event_date', '<=', $filters['date_to']);
        }

        // Submitted by filter
        if (!empty($filters['submitted_by'])) {
            $query->where('submitted_by', $filters['submitted_by']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getValidationHistory(array $filters, ?string $faculty = null, int $perPage = 15)
    {
        $query = \App\Models\ValidationLog::with([
            'studentAchievement.student',
            'studentAchievement.achievement.category',
            'studentAchievement.documents',
            'validator',
        ])->orderByDesc('validated_at');

        // Filter by faculty if validator has faculty assigned
        if ($faculty) {
            $query->whereHas('studentAchievement.student', function ($q) use ($faculty) {
                $q->where('faculty', $faculty);
            });
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('studentAchievement', function ($q) use ($search) {
                    $q->where('event_name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                })->orWhereHas('studentAchievement.student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('new_status', $filters['status']);
        }

        // Category filter
        if (!empty($filters['category'])) {
            $query->whereHas('studentAchievement.achievement', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
        }

        // Level filter
        if (!empty($filters['level'])) {
            $query->whereHas('studentAchievement', function ($q) use ($filters) {
                $q->where('level', $filters['level']);
            });
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('validated_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('validated_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getHistoryStatistics(?string $faculty = null): array
    {
        $query = \App\Models\ValidationLog::query();

        if ($faculty) {
            $query->whereHas('studentAchievement.student', function ($q) use ($faculty) {
                $q->where('faculty', $faculty);
            });
        }

        return [
            'total' => $query->count(),
            'approved' => (clone $query)->where('new_status', 'Disetujui')->count(),
            'rejected' => (clone $query)->where('new_status', 'Ditolak')->count(),
            'revision' => (clone $query)->where('new_status', 'Revisi')->count(),
        ];
    }
}
