<?php

namespace App\Services\Admin;

use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationLog;
use App\Models\SKAssignment;
use App\Models\SKDocument;
use App\Notifications\AchievementStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UniversityValidationService
{
    /**
     * Start review process at university level
     */
    public function startReview(StudentAchievement $achievement, User $admin): bool
    {
        // Validate: must be faculty_approved
        if ($achievement->validation_status !== StudentAchievement::STATUS_FACULTY_APPROVED) {
            throw new \Exception('Prestasi harus disetujui fakultas terlebih dahulu.');
        }

        return DB::transaction(function () use ($achievement, $admin) {
            $oldStatus = $achievement->validation_status;

            // Update achievement to university_review
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_UNIVERSITY_REVIEW,
                'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $admin->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_UNIVERSITY_REVIEW,
                'notes' => 'Memulai review tingkat universitas',
                'validation_stage' => StudentAchievement::STAGE_UNIVERSITY,
                'stage_action' => 'start_review',
                'is_stage_transition' => false,
                'validated_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Final approve achievement at university level + assign SK
     */
    public function approve(
        StudentAchievement $achievement,
        User $admin,
        ?int $skId = null,
        ?string $notes = null
    ): bool {
        // Validate: must be faculty_approved or university_review
        if (
            !in_array($achievement->validation_status, [
                StudentAchievement::STATUS_FACULTY_APPROVED,
                StudentAchievement::STATUS_UNIVERSITY_REVIEW
            ])
        ) {
            throw new \Exception('Prestasi harus disetujui fakultas terlebih dahulu.');
        }

        $skDocument = null;
        if ($skId) {
            // Validate: SK exists if provided
            $skDocument = SKDocument::find($skId);
            if (!$skDocument) {
                throw new \Exception('SK tidak ditemukan.');
            }
        }

        return DB::transaction(function () use ($achievement, $admin, $skId, $skDocument, $notes) {
            $oldStatus = $achievement->validation_status;

            // Update achievement (FINAL status)
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
                'current_stage' => StudentAchievement::STAGE_COMPLETED,
                'university_validator_id' => $admin->id,
                'university_validated_at' => now(),
                'university_notes' => $notes,
            ]);

            // Assign SK if provided
            if ($skId && $skDocument) {
                SKAssignment::create([
                    'sk_id' => $skId,
                    'sa_id' => $achievement->sa_id,
                    'assigned_by' => $admin->id,
                    'assigned_at' => now(),
                    'assignment_type' => 'individual',
                    'notes' => $notes,
                ]);
            }

            // Create validation log
            $logData = [
                'sa_id' => $achievement->sa_id,
                'validator_id' => $admin->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
                'notes' => $notes ?? 'Prestasi disetujui universitas' . ($skId ? ' dan SK telah diterbitkan' : ''),
                'validation_stage' => StudentAchievement::STAGE_UNIVERSITY,
                'stage_action' => 'final_approve',
                'is_stage_transition' => true,
                'validated_at' => now(),
            ];

            if ($skId) {
                $logData['sk_document'] = (string) $skId;
            }

            ValidationLog::create($logData);

            // Notify student (FINAL approval)
            $this->notifyStudent($achievement, 'university_approved');

            // Notify faculty validator (FYI)
            if ($achievement->facultyValidator) {
                $this->notifyFacultyValidator($achievement, 'university_approved');
            }

            return true;
        });
    }

    /**
     * Reject achievement at university level (FINAL, rare case)
     */
    public function reject(StudentAchievement $achievement, User $admin, string $reason): bool
    {
        // Validate: must be faculty_approved or university_review
        if (
            !in_array($achievement->validation_status, [
                StudentAchievement::STATUS_FACULTY_APPROVED,
                StudentAchievement::STATUS_UNIVERSITY_REVIEW
            ])
        ) {
            throw new \Exception('Prestasi harus disetujui fakultas terlebih dahulu.');
        }

        // Validate: reason is required
        if (empty($reason)) {
            throw new \Exception('Alasan penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($achievement, $admin, $reason) {
            $oldStatus = $achievement->validation_status;

            // Update achievement (FINAL status)
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_UNIVERSITY_REJECTED,
                'current_stage' => StudentAchievement::STAGE_COMPLETED,
                'university_validator_id' => $admin->id,
                'university_validated_at' => now(),
                'university_notes' => $reason,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $admin->id,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_UNIVERSITY_REJECTED,
                'notes' => $reason,
                'validation_stage' => StudentAchievement::STAGE_UNIVERSITY,
                'stage_action' => 'reject',
                'is_stage_transition' => true,
                'validated_at' => now(),
            ]);

            // Notify student
            $this->notifyStudent($achievement, 'university_rejected');

            // Notify faculty validator (FYI)
            if ($achievement->facultyValidator) {
                $this->notifyFacultyValidator($achievement, 'university_rejected');
            }

            return true;
        });
    }

    /**
     * Bulk assign SK to multiple achievements
     */
    public function bulkAssignSK(array $achievementIds, int $skId, User $admin, ?string $notes = null): array
    {
        $skDocument = SKDocument::find($skId);
        if (!$skDocument) {
            throw new \Exception('SK tidak ditemukan.');
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($achievementIds as $achievementId) {
            try {
                $achievement = StudentAchievement::find($achievementId);

                if (!$achievement) {
                    $results['failed']++;
                    $results['errors'][] = "Achievement ID {$achievementId} tidak ditemukan";
                    continue;
                }

                // Check if already approved
                if ($achievement->validation_status === StudentAchievement::STATUS_UNIVERSITY_APPROVED) {
                    $results['failed']++;
                    $results['errors'][] = "Achievement ID {$achievementId} sudah disetujui sebelumnya";
                    continue;
                }

                // Approve with SK
                $this->approve($achievement, $admin, $skId, $notes);
                $results['success']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Achievement ID {$achievementId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get statistics for university validation
     */
    public function getStatistics(?int $periodId = null): array
    {
        $query = StudentAchievement::whereNull('deleted_at'); // Exclude soft-deleted
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        $pending = (clone $query)->universityPending()->count();
        $approvedThisMonth = (clone $query)
            ->where('validation_status', StudentAchievement::STATUS_UNIVERSITY_APPROVED)
            ->whereYear('university_validated_at', now()->year)
            ->whereMonth('university_validated_at', now()->month)
            ->count();
        $totalApproved = (clone $query)
            ->where('validation_status', StudentAchievement::STATUS_UNIVERSITY_APPROVED)
            ->count();

        // Calculate average time from faculty approval to university approval
        $avgReviewTime = ValidationLog::where('validation_logs.validation_stage', StudentAchievement::STAGE_UNIVERSITY)
            ->where('validation_logs.stage_action', 'final_approve')
            ->whereNotNull('validation_logs.validated_at')
            ->join('student_achievements', 'validation_logs.sa_id', '=', 'student_achievements.sa_id')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (validation_logs.validated_at - student_achievements.faculty_validated_at))/3600) as avg_hours')
            ->value('avg_hours') ?? 0;

        // Count achievements with SK issued
        $skIssued = (clone $query)
            ->where('validation_status', StudentAchievement::STATUS_UNIVERSITY_APPROVED)
            ->whereHas('skAssignment')
            ->count();

        return [
            'pending' => $pending,
            'approved_this_month' => $approvedThisMonth,
            'total_approved' => $totalApproved,
            'sk_issued' => $skIssued,
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
     * Notify faculty validator about university decision
     */
    protected function notifyFacultyValidator(StudentAchievement $achievement, string $action): void
    {
        if ($achievement->facultyValidator) {
            try {
                $achievement->facultyValidator->notify(new AchievementStatusChanged($achievement, $action));
            } catch (\Exception $e) {
                Log::warning('Failed to send notification to faculty validator: ' . $e->getMessage());
            }
        }
    }
}
