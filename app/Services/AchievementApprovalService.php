<?php

namespace App\Services;

use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use App\Models\ValidationChecklist;
use App\Models\User;
use App\Notifications\AchievementStatusChanged;
use Illuminate\Support\Facades\DB;

class AchievementApprovalService
{
    public function approve(StudentAchievement $achievement, User $validator, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($achievement, $validator, $notes) {
            $oldStatus = $achievement->validation_status;

            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_APPROVED,
                'validator_id' => $validator->id,
            ]);

            $this->createValidationLog($achievement, $validator, $oldStatus, StudentAchievement::STATUS_APPROVED, $notes);
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
        return ValidationChecklist::updateOrCreate(
            ['sa_id' => $achievement->sa_id, 'validator_id' => $validator->id],
            $checklistData
        );
    }

    protected function createValidationLog(
        StudentAchievement $achievement,
        User $validator,
        string $oldStatus,
        string $newStatus,
        ?string $notes = null,
        ?array $metadata = null
    ): ValidationLog {
        return ValidationLog::create([
            'sa_id' => $achievement->sa_id,
            'validator_id' => $validator->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
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
                \Log::warning('Failed to send notification: ' . $e->getMessage());
            }
        }
    }

    public function getApprovalStatistics(): array
    {
        $total = StudentAchievement::count();
        $pending = StudentAchievement::pending()->count();
        $approved = StudentAchievement::approved()->count();
        $rejected = StudentAchievement::rejected()->count();
        $needRevision = StudentAchievement::needRevision()->count();

        // Average time to approve (in days)
        $dbDriver = config('database.default');
        
        if ($dbDriver === 'sqlite') {
            // SQLite uses JULIANDAY for date calculations
            $avgTimeToApprove = ValidationLog::where('new_status', StudentAchievement::STATUS_APPROVED)
                ->whereNotNull('validated_at')
                ->join('student_achievements', 'validation_logs.sa_id', '=', 'student_achievements.sa_id')
                ->selectRaw('AVG(JULIANDAY(validation_logs.validated_at) - JULIANDAY(student_achievements.submitted_at)) as avg_days')
                ->value('avg_days') ?? 0;
        } else {
            // MySQL and other databases use DATEDIFF
            $avgTimeToApprove = ValidationLog::where('new_status', StudentAchievement::STATUS_APPROVED)
                ->whereNotNull('validated_at')
                ->join('student_achievements', 'validation_logs.sa_id', '=', 'student_achievements.sa_id')
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

    public function getMonthlyTrend(int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');
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

        return $data;
    }

    public function getLevelDistribution(): array
    {
        return StudentAchievement::selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->pluck('count', 'level')
            ->toArray();
    }
}
