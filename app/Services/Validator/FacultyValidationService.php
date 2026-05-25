<?php

namespace App\Services\Validator;

use App\Models\StudentAchievement;
use App\Models\User;
use App\Models\ValidationChecklist;
use App\Models\ValidationLog;
use App\Notifications\AchievementStatusChanged;
use App\Services\DocumentVerificationService;
use App\Support\AchievementNotificationDispatcher;
use App\Support\OperationalLogContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacultyValidationService
{
    public function __construct(
        protected DocumentVerificationService $documentVerificationService
    ) {}

    /**
     * Start review process for an achievement
     */
    public function startReview(StudentAchievement $achievement, User $validator): bool
    {
        $this->ensureValidatorCanAccess($achievement, $validator);

        return DB::transaction(function () use ($achievement, $validator) {
            $achievement = StudentAchievement::where('sa_id', $achievement->sa_id)->lockForUpdate()->first();
            if (! $achievement) {
                throw new \Exception('Prestasi tidak ditemukan.');
            }

            // Validate: must be in submitted status
            if (! in_array($achievement->validation_status, [
                StudentAchievement::STATUS_SUBMITTED,
                StudentAchievement::STATUS_PENDING,
            ], true)) {
                throw new \Exception('Prestasi harus dalam status "Diajukan" untuk memulai review.');
            }

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
                'metadata' => $this->getAuditMetadata($achievement, $validator),
            ]);

            return true;
        });
    }

    /**
     * Approve achievement at faculty level
     */
    public function approve(StudentAchievement $achievement, User $validator, ?string $notes = null, array $checklistData = []): bool
    {
        $this->ensureValidatorCanAccess($achievement, $validator);

        return DB::transaction(function () use ($achievement, $validator, $notes, $checklistData) {
            $achievement = StudentAchievement::where('sa_id', $achievement->sa_id)->lockForUpdate()->first();
            if (! $achievement) {
                throw new \Exception('Prestasi tidak ditemukan.');
            }

            // Validate: must be in correct status
            $validStatuses = StudentAchievement::getFacultyPendingStatuses();

            if (! in_array($achievement->validation_status, $validStatuses)) {
                Log::warning('Invalid faculty approval status transition attempted', OperationalLogContext::validationFailure('faculty_approval_invalid_status', [
                    'sa_id' => $achievement->sa_id,
                    'validator_id' => $validator->id,
                    'current_status' => $achievement->validation_status,
                ]));

                throw new \Exception('Status prestasi tidak valid untuk approval fakultas. Status saat ini: '.$achievement->validation_status);
            }

            // Document Verification Gate
            $docCheck = $this->documentVerificationService->canAchievementBeApproved($achievement);
            if (! $docCheck['can_approve']) {
                throw new \Exception(implode(' ', $docCheck['errors']));
            }

            // Process Checklist
            $checklist = ValidationChecklist::withTrashed()
                ->where('sa_id', $achievement->sa_id)
                ->first();

            $formattedChecklist = [
                'certificate_valid' => (bool) ($checklistData['certificate_valid'] ?? false),
                'event_date_valid' => (bool) ($checklistData['event_date_valid'] ?? false),
                'organizer_valid' => (bool) ($checklistData['organizer_valid'] ?? false),
                'level_appropriate' => (bool) ($checklistData['level_appropriate'] ?? false),
                'documents_complete' => (bool) ($checklistData['documents_complete'] ?? false),
            ];

            if ($checklist) {
                if ($checklist->trashed()) {
                    $checklist->restore();
                }
                $checklist->update(array_merge($formattedChecklist, ['validator_id' => $validator->id]));
            } else {
                $checklist = ValidationChecklist::create(array_merge($formattedChecklist, [
                    'sa_id' => $achievement->sa_id,
                    'validator_id' => $validator->id,
                ]));
            }

            // Check if checklist is complete
            if (! $checklist->isCompleteFor($achievement)) {
                throw new \Exception('Checklist validasi belum lengkap untuk kategori/level prestasi ini.');
            }

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
                'metadata' => $this->getAuditMetadata($achievement, $validator),
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
        $this->ensureValidatorCanAccess($achievement, $validator);

        // Validate: reason is required
        if (empty($reason)) {
            throw new \Exception('Alasan penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($achievement, $validator, $reason) {
            $achievement = StudentAchievement::where('sa_id', $achievement->sa_id)->lockForUpdate()->first();
            if (! $achievement) {
                throw new \Exception('Prestasi tidak ditemukan.');
            }

            // Validate: must be in correct status
            $validStatuses = StudentAchievement::getFacultyPendingStatuses();

            if (! in_array($achievement->validation_status, $validStatuses)) {
                throw new \Exception('Status prestasi tidak valid untuk rejection fakultas. Status saat ini: '.$achievement->validation_status);
            }

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
                'metadata' => $this->getAuditMetadata($achievement, $validator),
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
        $this->ensureValidatorCanAccess($achievement, $validator);

        // Validate: reason is required
        if (empty($reason)) {
            throw new \Exception('Alasan revisi wajib diisi.');
        }

        return DB::transaction(function () use ($achievement, $validator, $reason, $requiredDocuments) {
            $achievement = StudentAchievement::where('sa_id', $achievement->sa_id)->lockForUpdate()->first();
            if (! $achievement) {
                throw new \Exception('Prestasi tidak ditemukan.');
            }

            // Validate: must be in correct status
            $validStatuses = StudentAchievement::getFacultyPendingStatuses();

            if (! in_array($achievement->validation_status, $validStatuses)) {
                throw new \Exception('Status prestasi tidak valid untuk request revision. Status saat ini: '.$achievement->validation_status);
            }

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
            $metadata = array_merge(
                $this->getAuditMetadata($achievement, $validator),
                ['required_documents' => $requiredDocuments]
            );

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

        $query = StudentAchievement::byFaculty($faculty)
            ->whereNull('deleted_at'); // Exclude soft-deleted
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }

        $pending = (clone $query)->whereIn('validation_status', StudentAchievement::getFacultyPendingStatuses())->count();
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

    protected function ensureValidatorCanAccess(StudentAchievement $achievement, User $validator): void
    {
        $achievement->loadMissing(['student', 'academicPeriod']);
        $period = $achievement->academicPeriod;
        if ($period && ! $period->isValidationOpen()) {
            throw new \Exception('Batas waktu validasi untuk periode ini telah berakhir.');
        }

        $currentRole = $validator->getCurrentRole();

        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id'));
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id'));
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id'));

        if ($level === 'university') {
            return;
        }

        if ($level === 'faculty' && $facultyId && $achievement->student?->faculty_id !== $facultyId) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari fakultas sendiri.');
        }

        if ($level === 'department' && $departmentId && $achievement->student?->department_id !== $departmentId) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari jurusan sendiri.');
        }

        if ($level === 'program_study' && $programStudyId && $achievement->student?->program_study_id !== $programStudyId) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari prodi sendiri.');
        }

        if (! $level && $validator->faculty && $achievement->student?->faculty !== $validator->faculty) {
            throw new \Exception('Validator hanya dapat memvalidasi prestasi dari fakultas sendiri.');
        }
    }

    /**
     * Notify student about status change
     */
    protected function notifyStudent(StudentAchievement $achievement, string $action): void
    {
        AchievementNotificationDispatcher::notifyStudent($achievement, $action);
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
            Log::warning('Failed to send notification to admins: '.$e->getMessage());
        }
    }

    protected function getAuditMetadata(StudentAchievement $achievement, User $user): array
    {
        $currentRole = $user->getCurrentRole();
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level') ?? ($user->role === 'Admin' ? 'university' : null));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id'));
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id'));
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id'));

        $documentStatuses = $achievement->documents->mapWithKeys(function ($doc) {
            return [$doc->id => [
                'type' => $doc->document_type,
                'status' => $doc->status,
            ]];
        })->toArray();

        return [
            'actor_id' => $user->id,
            'actor_role' => $currentRole ? $currentRole->role_type : $user->role,
            'scope' => [
                'level' => $level,
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,
                'program_study_id' => $programStudyId,
            ],
            'document_statuses' => $documentStatuses,
        ];
    }
}
