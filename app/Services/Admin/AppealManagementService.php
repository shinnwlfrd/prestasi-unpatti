<?php

namespace App\Services\Admin;

use App\Models\StudentAchievement;
use App\Models\AchievementAppeal;
use App\Models\User;
use App\Models\ValidationLog;
use App\Notifications\AchievementStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppealManagementService
{
    /**
     * Submit appeal from student
     */
    public function submitAppeal(StudentAchievement $achievement, string $reason, ?string $additionalNotes = null): AchievementAppeal
    {
        // Validate: must be in faculty_revision status
        if ($achievement->validation_status !== StudentAchievement::STATUS_FACULTY_REVISION) {
            throw new \Exception('Banding hanya dapat diajukan untuk prestasi dengan status "Revisi Fakultas".');
        }

        // Validate: no pending appeal
        if ($achievement->appeals()->where('status', 'pending')->exists()) {
            throw new \Exception('Sudah ada banding yang sedang diproses untuk prestasi ini.');
        }

        // Validate: reason is required
        if (empty($reason)) {
            throw new \Exception('Alasan banding wajib diisi.');
        }

        return DB::transaction(function () use ($achievement, $reason, $additionalNotes) {
            $oldStatus = $achievement->validation_status;

            // Create appeal
            $appeal = AchievementAppeal::create([
                'sa_id' => $achievement->sa_id,
                'student_id' => $achievement->student_id,
                'appeal_reason' => $reason,
                'additional_notes' => $additionalNotes,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            // Update achievement status
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_APPEAL_SUBMITTED,
                'current_stage' => StudentAchievement::STAGE_APPEAL,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => null, // Student action
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_APPEAL_SUBMITTED,
                'notes' => "Banding diajukan: {$reason}",
                'validation_stage' => StudentAchievement::STAGE_APPEAL,
                'stage_action' => 'submit_appeal',
                'is_stage_transition' => true,
                'validated_at' => now(),
            ]);

            // Notify admins
            $this->notifyAdmins($achievement, 'appeal_submitted');

            return $appeal;
        });
    }

    /**
     * Approve appeal - reset achievement for re-review
     */
    public function approveAppeal(AchievementAppeal $appeal, User $admin, ?string $notes = null): bool
    {
        // Validate: appeal must be pending
        if ($appeal->status !== 'pending') {
            throw new \Exception('Banding sudah diproses sebelumnya.');
        }

        return DB::transaction(function () use ($appeal, $admin, $notes) {
            $achievement = $appeal->studentAchievement;
            $oldStatus = $achievement->validation_status;

            // Update appeal
            $appeal->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'admin_notes' => $notes,
            ]);

            // Reset achievement to SUBMITTED for re-review from faculty
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_SUBMITTED,
                'current_stage' => StudentAchievement::STAGE_FACULTY,
                // Clear faculty validation data for fresh review
                'faculty_validator_id' => null,
                'faculty_validated_at' => null,
                'faculty_notes' => null,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $admin->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_SUBMITTED,
                'notes' => "Banding disetujui: " . ($notes ?? 'Prestasi akan direview ulang oleh fakultas'),
                'validation_stage' => StudentAchievement::STAGE_APPEAL,
                'stage_action' => 'approve_appeal',
                'is_stage_transition' => true,
                'validated_at' => now(),
            ]);

            // Notify student
            $this->notifyStudent($achievement, 'appeal_approved');

            // Notify faculty validator (new achievement for review)
            if ($achievement->student->faculty) {
                $this->notifyFacultyValidators($achievement, 'new_after_appeal');
            }

            return true;
        });
    }

    /**
     * Reject appeal - achievement stays in faculty_revision
     */
    public function rejectAppeal(AchievementAppeal $appeal, User $admin, string $reason): bool
    {
        // Validate: appeal must be pending
        if ($appeal->status !== 'pending') {
            throw new \Exception('Banding sudah diproses sebelumnya.');
        }

        // Validate: reason is required
        if (empty($reason)) {
            throw new \Exception('Alasan penolakan banding wajib diisi.');
        }

        return DB::transaction(function () use ($appeal, $admin, $reason) {
            $achievement = $appeal->studentAchievement;
            $oldStatus = $achievement->validation_status;

            // Update appeal
            $appeal->update([
                'status' => 'rejected',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'admin_notes' => $reason,
            ]);

            // Return achievement to FACULTY_REVISION
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_FACULTY_REVISION,
                'current_stage' => StudentAchievement::STAGE_FACULTY,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $admin->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_FACULTY_REVISION,
                'notes' => "Banding ditolak: {$reason}",
                'validation_stage' => StudentAchievement::STAGE_APPEAL,
                'stage_action' => 'reject_appeal',
                'is_stage_transition' => true,
                'validated_at' => now(),
            ]);

            // Notify student
            $this->notifyStudent($achievement, 'appeal_rejected');

            return true;
        });
    }

    /**
     * Get appeal statistics
     */
    public function getStatistics(?int $periodId = null): array
    {
        $query = AchievementAppeal::query();
        
        if ($periodId) {
            $query->whereHas('studentAchievement', function ($q) use ($periodId) {
                $q->where('academic_period_id', $periodId);
            });
        }

        $pending = (clone $query)->where('status', 'pending')->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();
        $total = (clone $query)->count();

        // Calculate average review time for appeals
        $avgReviewTime = AchievementAppeal::whereNotNull('reviewed_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (reviewed_at - submitted_at))/3600) as avg_hours')
            ->value('avg_hours') ?? 0;

        // Approval rate
        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        return [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'total' => $total,
            'approval_rate' => $approvalRate,
            'avg_review_time_hours' => round($avgReviewTime, 1),
        ];
    }

    /**
     * Get pending appeals with filters
     */
    public function getPendingAppeals(array $filters = [], int $perPage = 15)
    {
        $query = AchievementAppeal::with([
            'studentAchievement.student',
            'studentAchievement.achievement.category',
            'studentAchievement.facultyValidator',
        ])
        ->where('status', 'pending')
        ->orderBy('submitted_at', 'asc');

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('studentAchievement', function ($sq) use ($search) {
                    $sq->where('event_name', 'like', "%{$search}%")
                       ->orWhere('student_id', 'like', "%{$search}%");
                })
                ->orWhereHas('studentAchievement.student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Faculty filter
        if (!empty($filters['faculty'])) {
            $query->whereHas('studentAchievement.student', function ($q) use ($filters) {
                $q->where('faculty', $filters['faculty']);
            });
        }

        // Category filter
        if (!empty($filters['category'])) {
            $query->whereHas('studentAchievement.achievement', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Notify student about appeal decision
     */
    protected function notifyStudent(StudentAchievement $achievement, string $action): void
    {
        $student = $achievement->student;
        if ($student && $student->user) {
            try {
                $student->user->notify(new AchievementStatusChanged($achievement, $action));
            } catch (\Exception $e) {
                Log::warning('Failed to send notification to student: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notify admins about new appeal
     */
    protected function notifyAdmins(StudentAchievement $achievement, string $action): void
    {
        try {
            $admins = User::where('role', 'Admin')->where('is_active', true)->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new AchievementStatusChanged($achievement, $action));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send notification to admins: ' . $e->getMessage());
        }
    }

    /**
     * Notify faculty validators about new achievement after appeal approved
     */
    protected function notifyFacultyValidators(StudentAchievement $achievement, string $action): void
    {
        try {
            $validators = User::where('role', 'Validator')
                ->where('faculty', $achievement->student->faculty)
                ->where('is_active', true)
                ->get();
            
            foreach ($validators as $validator) {
                $validator->notify(new AchievementStatusChanged($achievement, $action));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send notification to faculty validators: ' . $e->getMessage());
        }
    }
}
