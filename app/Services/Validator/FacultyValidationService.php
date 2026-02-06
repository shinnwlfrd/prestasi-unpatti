<?php

namespace App\Services\Validator;

use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use App\Notifications\AchievementStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacultyValidationService
{
    /**
     * Start review process for an achievement
     */
    public function startReview(StudentAchievement $achievement, User $validator): bool
    {
        // Validate: must be in submitted status
        if ($achievement->validation_status !== StudentAchievement::STATUS_SUBMITTED) {
            throw new \Exception('Prestasi harus dalam status "Diajukan" untuk memulai review.');
        }

        // Validate: validator must be from same faculty
        if ($validator->faculty !== $achievement->student->faculty) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari fakultas sendiri.');
        }

        return DB::transaction(function () use ($achievement, $validator) {
            $oldStatus = $achievement->validation_status;

            // Update achievement to faculty_review
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_FACULTY_REVIEW,
                'current_stage' => StudentAchievement::STAGE_FACULTY,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $validator->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_FACULTY_REVIEW,
                'notes' => 'Memulai review prestasi',
                'validation_stage' => StudentAchievement::STAGE_FACULTY,
                'stage_action' => 'start_review',
                'is_stage_transition' => false,
                'validated_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Approve achievement at faculty level
     */
    public function approve(StudentAchievement $achievement, User $validator, ?string $notes = null): bool
    {
        // Validate: must be in correct status
        if (!in_array($achievement->validation_status, [
            StudentAchievement::STATUS_SUBMITTED,
            StudentAchievement::STATUS_FACULTY_REVIEW
        ])) {
            throw new \Exception('Status prestasi tidak valid untuk approval fakultas.');
        }

        // Validate: validator must be from same faculty
        if ($validator->faculty !== $achievement->student->faculty) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari fakultas sendiri.');
        }

        return DB::transaction(function () use ($achievement, $validator, $notes) {
            $oldStatus = $achievement->validation_status;

            // Update achievement
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
                'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
                'faculty_validator_id' => $validator->id,
                'faculty_validated_at' => now(),
                'faculty_notes' => $notes,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $validator->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
                'notes' => $notes ?? 'Prestasi disetujui oleh fakultas',
                'validation_stage' => StudentAchievement::STAGE_FACULTY,
                'stage_action' => 'approve',
                'is_stage_transition' => true,
                'validated_at' => now(),
            ]);

            // Notify student
            $this->notifyStudent($achievement, 'faculty_approved');

            // Notify admins (new achievement for university review)
            $this->notifyAdmins($achievement, 'new_for_university_review');

            return true;
        });
    }

    /**
     * Reject achievement at faculty level (FINAL)
     */
    public function reject(StudentAchievement $achievement, User $validator, string $reason): bool
    {
        // Validate: must be in correct status
        if (!in_array($achievement->validation_status, [
            StudentAchievement::STATUS_SUBMITTED,
            StudentAchievement::STATUS_FACULTY_REVIEW
        ])) {
            throw new \Exception('Status prestasi tidak valid untuk rejection fakultas.');
        }

        // Validate: validator must be from same faculty
        if ($validator->faculty !== $achievement->student->faculty) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari fakultas sendiri.');
        }

        // Validate: reason is required
        if (empty($reason)) {
            throw new \Exception('Alasan penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($achievement, $validator, $reason) {
            $oldStatus = $achievement->validation_status;

            // Update achievement (FINAL status)
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_FACULTY_REJECTED,
                'current_stage' => StudentAchievement::STAGE_COMPLETED,
                'faculty_validator_id' => $validator->id,
                'faculty_validated_at' => now(),
                'faculty_notes' => $reason,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $validator->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_FACULTY_REJECTED,
                'notes' => $reason,
                'validation_stage' => StudentAchievement::STAGE_FACULTY,
                'stage_action' => 'reject',
                'is_stage_transition' => true,
                'validated_at' => now(),
            ]);

            // Notify student
            $this->notifyStudent($achievement, 'faculty_rejected');

            return true;
        });
    }

    /**
     * Request revision from student
     */
    public function requestRevision(StudentAchievement $achievement, User $validator, string $reason, array $requiredDocuments = []): bool
    {
        // Validate: must be in correct status
        if (!in_array($achievement->validation_status, [
            StudentAchievement::STATUS_SUBMITTED,
            StudentAchievement::STATUS_FACULTY_REVIEW
        ])) {
            throw new \Exception('Status prestasi tidak valid untuk request revision.');
        }

        // Validate: validator must be from same faculty
        if ($validator->faculty !== $achievement->student->faculty) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari fakultas sendiri.');
        }

        // Validate: reason is required
        if (empty($reason)) {
            throw new \Exception('Alasan revisi wajib diisi.');
        }

        return DB::transaction(function () use ($achievement, $validator, $reason, $requiredDocuments) {
            $oldStatus = $achievement->validation_status;

            // Update achievement
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_FACULTY_REVISION,
                'current_stage' => StudentAchievement::STAGE_FACULTY,
                'faculty_validator_id' => $validator->id,
                'faculty_validated_at' => now(),
                'faculty_notes' => $reason,
            ]);

            // Create validation log
            $metadata = ['required_documents' => $requiredDocuments];
            
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $validator->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_FACULTY_REVISION,
                'notes' => $reason,
                'metadata' => $metadata,
                'validation_stage' => StudentAchievement::STAGE_FACULTY,
                'stage_action' => 'request_revision',
                'is_stage_transition' => false,
                'validated_at' => now(),
            ]);

            // Notify student
            $this->notifyStudent($achievement, 'faculty_revision');

            return true;
        });
    }

    /**
     * Get statistics for faculty validator
     */
    public function getStatistics(User $validator, ?int $periodId = null): array
    {
        $faculty = $validator->faculty;

        $query = StudentAchievement::byFaculty($faculty);
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        $pending = (clone $query)->facultyPending()->count();
        $approvedToday = (clone $query)
            ->where('validation_status', StudentAchievement::STATUS_FACULTY_APPROVED)
            ->where('faculty_validator_id', $validator->id)
            ->whereDate('faculty_validated_at', today())
            ->count();
        $revisionRequested = (clone $query)
            ->where('validation_status', StudentAchievement::STATUS_FACULTY_REVISION)
            ->where('faculty_validator_id', $validator->id)
            ->count();

        // Calculate average review time
        $avgReviewTime = ValidationLog::where('validation_logs.validator_id', $validator->id)
            ->where('validation_logs.validation_stage', StudentAchievement::STAGE_FACULTY)
            ->where('validation_logs.stage_action', 'approve')
            ->whereNotNull('validation_logs.validated_at')
            ->join('student_achievements', 'validation_logs.sa_id', '=', 'student_achievements.sa_id')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (validation_logs.validated_at - student_achievements.submitted_at))/3600) as avg_hours')
            ->value('avg_hours') ?? 0;

        return [
            'pending' => $pending,
            'approved_today' => $approvedToday,
            'revision_requested' => $revisionRequested,
            'avg_review_time_hours' => round($avgReviewTime, 1),
        ];
    }

    /**
     * Notify student about status change
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
     * Notify admins about new achievement for university review
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
}
