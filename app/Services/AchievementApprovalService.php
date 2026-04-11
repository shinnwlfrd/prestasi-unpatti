<?php

namespace App\Services;

use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationChecklist;
use App\Models\ValidationLog;
use App\Notifications\AchievementStatusChanged;
use Illuminate\Support\Facades\DB;

class AchievementApprovalService
{
    public function approve(StudentAchievement $achievement, User $validator, ?string $notes = null, ?int $skId = null): bool
    {
        return DB::transaction(function () use ($achievement, $validator, $notes, $skId) {
            $oldStatus = $achievement->validation_status;

            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_APPROVED,
                'validator_id' => $validator->id,
            ]);

            $this->createValidationLog($achievement, $validator, $oldStatus, StudentAchievement::STATUS_APPROVED, $notes, null, $skId);
            $this->notifyStudent($achievement, 'approved');

            return true;
        });
    }

    public function reject(StudentAchievement $achievement, User $validator, string $reason): bool
    {
        return DB::transaction(function () use ($achievement, $validator, $reason) {
            $oldStatus = $achievement->validation_status;

            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_REJECTED,
                'validator_id' => $validator->id,
            ]);

            $this->createValidationLog($achievement, $validator, $oldStatus, StudentAchievement::STATUS_REJECTED, $reason);
            $this->notifyStudent($achievement, 'rejected');

            return true;
        });
    }

    public function requestRevision(StudentAchievement $achievement, User $validator, string $reason, array $requiredDocuments = []): bool
    {
        return DB::transaction(function () use ($achievement, $validator, $reason, $requiredDocuments) {
            $oldStatus = $achievement->validation_status;

            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_NEED_REVISION,
                'validator_id' => $validator->id,
            ]);

            $metadata = ['required_documents' => $requiredDocuments];
            $this->createValidationLog($achievement, $validator, $oldStatus, StudentAchievement::STATUS_NEED_REVISION, $reason, $metadata);
            $this->notifyStudent($achievement, 'need_revision');

            return true;
        });
    }

    public function submitForReview(StudentAchievement $achievement): bool
    {
        $achievement->update([
            'validation_status' => StudentAchievement::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        return true;
    }

    public function processChecklist(StudentAchievement $achievement, User $validator, array $checklistData): ValidationChecklist
    {
        // Check for soft-deleted checklist first to avoid duplicate creation
        $checklist = ValidationChecklist::withTrashed()
            ->where('sa_id', $achievement->sa_id)
            ->where('validator_id', $validator->id)
            ->first();

        if ($checklist) {
            // Restore if soft-deleted
            if ($checklist->trashed()) {
                $checklist->restore();
            }

            $checklist->update($checklistData);
            return $checklist;
        }

        // Create new checklist
        return ValidationChecklist::create(array_merge($checklistData, [
            'sa_id' => $achievement->sa_id,
            'validator_id' => $validator->id,
        ]));
    }

    protected function createValidationLog(
        StudentAchievement $achievement,
        User $validator,
        string $oldStatus,
        string $newStatus,
        ?string $notes = null,
        ?array $metadata = null,
        ?int $skId = null
    ): ValidationLog {
        return ValidationLog::create([
            'sa_id' => $achievement->sa_id,
            'validator_id' => $validator->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'sk_document' => $skId ? (string)$skId : null, // Store SK ID as string for backward compatibility
            'metadata' => $metadata,
            'validated_at' => now(),
        ]);
    }

    protected function notifyStudent(StudentAchievement $achievement, string $action): void
    {
        $student = $achievement->student;
        if ($student && $student->user) {
            try {
                $student->user->notify(new AchievementStatusChanged($achievement, $action));
            } catch (\Exception $e) {
                // Log notification failure but don't break the flow
                \Log::warning('Failed to send notification: '.$e->getMessage());
            }
        }
    }

    public function getApprovalStatistics(?int $periodId = null): array
    {
        $query = StudentAchievement::query();
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        $total = (clone $query)->count();
        $pending = (clone $query)->pending()->count();
        $approved = (clone $query)->approved()->count();
        $rejected = (clone $query)->rejected()->count();
        $needRevision = (clone $query)->needRevision()->count();

        // Average time to approve (in days)
        $dbDriver = config('database.default');

        $avgQuery = ValidationLog::where('validation_logs.new_status', StudentAchievement::STATUS_APPROVED)
            ->whereNotNull('validation_logs.validated_at')
            ->join('student_achievements', 'validation_logs.sa_id', '=', 'student_achievements.sa_id');

        if ($periodId) {
            $avgQuery->where('student_achievements.academic_period_id', $periodId);
        }

        if ($dbDriver === 'sqlite') {
            // SQLite uses JULIANDAY for date calculations
            $avgTimeToApprove = $avgQuery
                ->selectRaw('AVG(JULIANDAY(validation_logs.validated_at) - JULIANDAY(student_achievements.submitted_at)) as avg_days')
                ->value('avg_days') ?? 0;
        } elseif ($dbDriver === 'pgsql') {
            // PostgreSQL uses EXTRACT(EPOCH FROM ...) for date difference
            $avgTimeToApprove = $avgQuery
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (validation_logs.validated_at - student_achievements.submitted_at))/86400) as avg_days')
                ->value('avg_days') ?? 0;
        } else {
            // MySQL uses DATEDIFF
            $avgTimeToApprove = $avgQuery
                ->selectRaw('AVG(DATEDIFF(validation_logs.validated_at, student_achievements.submitted_at)) as avg_days')
                ->value('avg_days') ?? 0;
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'need_revision' => $needRevision,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
            'avg_time_to_approve' => round($avgTimeToApprove, 1),
        ];
    }

    public function getMonthlyTrend(int $months = 6, ?int $periodId = null): array
    {
        $data = [];

        if ($periodId) {
            // Get period details
            $period = \App\Models\AcademicPeriod::find($periodId);

            if ($period) {
                // Generate months within the period
                $startDate = $period->start_date;
                $endDate = $period->end_date;
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    $monthLabel = $currentDate->format('M Y');

                    $submittedQuery = StudentAchievement::whereYear('submitted_at', $currentDate->year)
                        ->whereMonth('submitted_at', $currentDate->month)
                        ->where('academic_period_id', $periodId);

                    $submitted = $submittedQuery->count();

                    $approvedQuery = StudentAchievement::whereYear('submitted_at', $currentDate->year)
                        ->whereMonth('submitted_at', $currentDate->month)
                        ->where('validation_status', StudentAchievement::STATUS_APPROVED)
                        ->where('academic_period_id', $periodId);

                    $approved = $approvedQuery->count();

                    $data[] = [
                        'month' => $monthLabel,
                        'submitted' => $submitted,
                        'approved' => $approved,
                    ];

                    $currentDate->addMonth();
                }
            }
        } else {
            // Default: last 6 months
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthLabel = $date->format('M Y');

                $submitted = StudentAchievement::whereYear('submitted_at', $date->year)
                    ->whereMonth('submitted_at', $date->month)
                    ->count();

                $approved = StudentAchievement::whereYear('submitted_at', $date->year)
                    ->whereMonth('submitted_at', $date->month)
                    ->where('validation_status', StudentAchievement::STATUS_APPROVED)
                    ->count();

                $data[] = [
                    'month' => $monthLabel,
                    'submitted' => $submitted,
                    'approved' => $approved,
                ];
            }
        }

        return $data;
    }

    public function getLevelDistribution(?int $periodId = null): array
    {
        $query = StudentAchievement::selectRaw('level, COUNT(*) as count')
            ->groupBy('level');

        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        return $query->pluck('count', 'level')->toArray();
    }

    public function getPeriodComparison(): array
    {
        $periods = \App\Models\AcademicPeriod::ordered()->get();
        $data = [];

        foreach ($periods as $period) {
            $total = StudentAchievement::where('academic_period_id', $period->id)->count();
            $approved = StudentAchievement::where('academic_period_id', $period->id)
                ->where('validation_status', StudentAchievement::STATUS_APPROVED)
                ->count();
            $pending = StudentAchievement::where('academic_period_id', $period->id)
                ->where('validation_status', StudentAchievement::STATUS_PENDING)
                ->count();
            $rejected = StudentAchievement::where('academic_period_id', $period->id)
                ->where('validation_status', StudentAchievement::STATUS_REJECTED)
                ->count();

            $data[] = [
                'period' => $period->name,
                'total' => $total,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected,
            ];
        }

        return $data;
    }
}
