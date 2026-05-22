<?php

namespace App\Services\Validator;

use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use App\Repositories\Contracts\AchievementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ValidationService
{
    public function __construct(
        protected AchievementRepositoryInterface $achievementRepo
    ) {}

    public function getPendingAchievements(array $filters, ?string $faculty = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = StudentAchievement::with(['student', 'achievement.category', 'documents'])
            ->whereIn('validation_status', StudentAchievement::getFacultyPendingStatuses())
            ->orderByDesc('created_at');

        // Filter by faculty if validator has faculty assigned
        if ($faculty) {
            $query->whereHas('student', function ($q) use ($faculty) {
                $q->where('faculty', $faculty);
            });
        }

        // Search filter
        if (! empty($filters['search'])) {
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
        if (! empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Category filter
        if (! empty($filters['category'])) {
            $query->whereHas('achievement', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
        }

        // Date range filter
        if (! empty($filters['date_from'])) {
            $query->whereDate('event_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('event_date', '<=', $filters['date_to']);
        }

        // Submitted by filter
        if (! empty($filters['submitted_by'])) {
            $query->where('submitted_by', $filters['submitted_by']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getValidationHistory(array $filters, ?string $faculty = null, int $perPage = 15, ?string $level = null, mixed $facultyId = null)
    {
        $query = ValidationLog::with([
            'studentAchievement.student',
            'studentAchievement.achievement.category',
            'studentAchievement.documents',
            'validator',
        ])->orderByDesc('validated_at');

        // Apply scope filtering based on operator level
        if ($level === 'university') {
            // University level operator can see all faculties - no filtering unless faculty filter is applied
            if (! empty($filters['faculty'])) {
                $query->whereHas('studentAchievement.student', function ($q) use ($filters) {
                    $q->where('faculty_id', $filters['faculty']);
                });
            }
        } elseif ($level === 'faculty' && $facultyId) {
            $query->whereHas('studentAchievement.student', function ($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            });
        } elseif ($faculty) {
            // Fallback to old method - filter by faculty string
            $query->whereHas('studentAchievement.student', function ($q) use ($faculty) {
                $q->where('faculty', $faculty);
            });
        }

        // Search filter
        if (! empty($filters['search'])) {
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
        if (! empty($filters['status'])) {
            $status = $filters['status'];
            $groups = StudentAchievement::getValidationDecisionStatusGroups();

            if (isset($groups[$status])) {
                $query->whereIn('new_status', $groups[$status]);
            } else {
                $query->where('new_status', $status);
            }
        }

        // Category filter
        if (! empty($filters['category'])) {
            $query->whereHas('studentAchievement.achievement', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
        }

        // Level filter
        if (! empty($filters['level'])) {
            $query->whereHas('studentAchievement', function ($q) use ($filters) {
                $q->where('level', $filters['level']);
            });
        }

        // Date range filter
        if (! empty($filters['date_from'])) {
            $query->whereDate('validated_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('validated_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getHistoryStatistics(?string $faculty = null, ?string $level = null, mixed $facultyId = null): array
    {
        $query = ValidationLog::query();

        // Apply scope filtering based on operator level
        if ($level === 'university') {
            // University level operator can see all faculties - no filtering
        } elseif ($level === 'faculty' && $facultyId) {
            $query->whereHas('studentAchievement.student', function ($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            });
        } elseif ($faculty) {
            // Fallback to old method
            $query->whereHas('studentAchievement.student', function ($q) use ($faculty) {
                $q->where('faculty', $faculty);
            });
        }

        return [
            'total' => $query->count(),
            'approved' => (clone $query)->whereIn('new_status', StudentAchievement::getValidationDecisionStatusGroups()['approved'])->count(),
            'rejected' => (clone $query)->whereIn('new_status', StudentAchievement::getValidationDecisionStatusGroups()['rejected'])->count(),
            'revision' => (clone $query)->whereIn('new_status', StudentAchievement::getValidationDecisionStatusGroups()['revision'])->count(),
        ];
    }
}
