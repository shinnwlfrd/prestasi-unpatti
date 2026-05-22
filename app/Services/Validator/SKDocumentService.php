<?php

namespace App\Services\Validator;

use App\Models\SKDocument;
use App\Models\StudentAchievement;
use Illuminate\Support\Facades\DB;

class SKDocumentService
{
    /**
     * Process batch assignment of achievements to an SK
     */
    public function processBatchAssignment(SKDocument $sk, array $achievementIds, int $userId, ?string $notes = null): int
    {
        return DB::transaction(function () use ($sk, $achievementIds, $userId, $notes) {
            $assignedCount = 0;
            $assignedAt = now();

            // Get session-based scope for security check (optional, but good practice)
            $level = session('operator_level') ?? session('pimpinan_level');
            $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
            $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
            $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

            foreach ($achievementIds as $saId) {
                $achievement = StudentAchievement::with(['student', 'academicPeriod'])->find($saId);

                if (! $achievement) {
                    continue;
                }

                $period = $achievement->academicPeriod;
                if ($period && ! $period->isValidationOpen()) {
                    throw new \Exception('Batas waktu validasi untuk periode "'.$period->name.'" telah berakhir.');
                }

                // Skip if already has SK or not at faculty stage
                if (! in_array($achievement->validation_status, StudentAchievement::getFacultyPendingStatuses(), true)) {
                    continue;
                }

                // Security check: Ensure validator has access to this student's data
                if ($level === 'faculty' && $achievement->student->faculty_id !== $facultyId) {
                    continue;
                }
                if ($level === 'department' && $achievement->student->department_id !== $departmentId) {
                    continue;
                }
                if ($level === 'program_study' && $achievement->student->program_study_id !== $programStudyId) {
                    continue;
                }

                // For legacy faculty check
                if (! $level && $achievement->student->faculty !== auth()->user()->faculty) {
                    continue;
                }

                // Create assignment
                $sk->assignments()->create([
                    'sa_id' => $saId,
                    'assigned_by' => $userId,
                    'assigned_at' => $assignedAt,
                    'assignment_type' => 'batch',
                    'notes' => $notes,
                ]);

                // Update achievement status to approved (Faculty Level)
                $oldStatus = $achievement->validation_status;
                $achievement->update([
                    'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
                    'faculty_validator_id' => $userId,
                    'faculty_validated_at' => $assignedAt,
                    'faculty_notes' => 'Disetujui via batch assignment SK: '.$sk->sk_number,
                    'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
                ]);

                // Create validation log
                $achievement->validationLogs()->create([
                    'validator_id' => $userId,
                    'old_status' => $oldStatus,
                    'new_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
                    'notes' => 'Disetujui via batch assignment SK: '.$sk->sk_number.($notes ? '. '.$notes : ''),
                    'validation_stage' => StudentAchievement::STAGE_FACULTY,
                    'stage_action' => 'batch_approve',
                    'is_stage_transition' => true,
                    'validated_at' => $assignedAt,
                ]);

                $assignedCount++;
            }

            return $assignedCount;
        });
    }
}
