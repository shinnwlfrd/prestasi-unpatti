<?php

namespace App\Services\Validator;

use App\Helpers\ValidationStatusHelper;
use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementDocument;
use App\Models\SKDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class AchievementSubmissionService
{
    public function __construct(
        protected FacultyValidationService $facultyValidationService
    ) {}

    public function submitAchievement(array $data, UploadedFile $certificate, User $user, ?array $additionalDocuments = []): StudentAchievement
    {
        $achievementId = $this->resolveAchievementIdFromCategory($data['category_id']);
        if (! $achievementId) {
            throw new \Exception('Kategori yang dipilih belum memiliki template prestasi. Hubungi admin.');
        }

        $student = Student::findOrFail($data['student_id']);
        $this->ensureUserCanSubmitForStudent($student, $user);

        // Upload only after student scope has been validated.
        $certificatePath = $certificate->store('certificates', 'public');

        // Determine SK required
        $skRequired = ! ($data['skip_sk'] ?? false);
        $activePeriodId = AcademicPeriod::active()->value('id');
        $initialState = ValidationStatusHelper::getInitialSubmissionState('operator');

        // Create achievement
        $achievement = StudentAchievement::create([
            'student_id' => $data['student_id'],
            'achievement_id' => $achievementId,
            'academic_period_id' => $activePeriodId,
            'student_snapshot' => [
                'student_id' => $student->student_id,
                'id_mahasiswa' => $student->id_mahasiswa,
                'name' => $student->name,
                'email' => $student->email,
                'faculty_id' => $student->faculty_id,
                'faculty' => $student->faculty,
                'department_id' => $student->department_id,
                'department' => $student->department,
                'program_study_id' => $student->program_study_id,
                'program_study' => $student->program_study,
                'angkatan' => $student->angkatan,
                'ipk' => $student->ipk,
                'captured_at' => now()->toIso8601String(),
            ],
            'event_name' => $data['event_name'],
            'level' => $data['level'],
            'organizer' => $data['organizer'],
            'event_date' => $data['event_date'],
            'ranking' => $data['ranking'] ?? null,
            'description' => $data['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => $initialState['validation_status'],
            'validation_stage' => $initialState['validation_stage'],
            'current_stage' => $initialState['current_stage'],
            'validator_id' => null,
            'submitted_by' => 'validator',
            'submitted_at' => now(),
            'sk_required' => $skRequired,
            'sk_waiver_reason' => $data['sk_waiver_reason'] ?? null,
            'sk_waiver_notes' => $data['sk_waiver_notes'] ?? null,
        ]);

        // Process additional documents if any
        if ($additionalDocuments) {
            foreach ($additionalDocuments as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $this->uploadDocument(
                        $achievement,
                        $file,
                        AchievementDocument::TYPE_FOTO_DOKUMENTASI,
                        $user->id,
                        AchievementDocument::STATUS_PENDING // Additional docs start as pending
                    );
                }
            }
        }

        return $achievement;
    }

    public function handleApproval(
        StudentAchievement $achievement,
        $user,
        ?int $skId = null,
        ?UploadedFile $alternativeDocument = null
    ): void {
        // Assign SK if provided
        if ($skId) {
            $sk = SKDocument::findOrFail($skId);

            // Create SK assignment
            $sk->assignments()->create([
                'sa_id' => $achievement->sa_id,
                'assigned_by' => $user->id,
                'assigned_at' => now(),
                'assignment_type' => 'individual',
                'notes' => 'Assigned saat submit prestasi oleh validator',
            ]);
        }

        // Upload alternative document if provided
        if ($alternativeDocument) {
            $altDocPath = $this->uploadDocument(
                $achievement,
                $alternativeDocument,
                'dokumen_alternatif',
                $user->id
            );

            $achievement->update(['alternative_document_path' => $altDocPath]);
        }

        // Log approval
        $notes = $achievement->sk_required
            ? 'Disetujui langsung oleh validator saat submit'
            : 'Disetujui tanpa SK: '.($achievement->sk_waiver_reason ? StudentAchievement::getSkWaiverReasons()[$achievement->sk_waiver_reason] : 'N/A');

        $this->facultyValidationService->approve($achievement, $user, $notes);
    }

    public function handleRejection(StudentAchievement $achievement, $user, string $reason): void
    {
        $this->facultyValidationService->reject($achievement, $user, $reason);
    }

    protected function uploadDocument(
        StudentAchievement $achievement,
        UploadedFile $file,
        string $type,
        int $userId,
        string $status = AchievementDocument::STATUS_APPROVED
    ): string {
        $fileName = $type.'_'.$achievement->sa_id.'_'.time().'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

        $achievement->documents()->create([
            'document_type' => $type,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => $status,
            'verified_by' => $status === AchievementDocument::STATUS_APPROVED ? $userId : null,
            'verified_at' => $status === AchievementDocument::STATUS_APPROVED ? now() : null,
        ]);

        return $filePath;
    }

    protected function resolveAchievementIdFromCategory(int $categoryId): ?int
    {
        $achievement = Achievement::where('category_id', $categoryId)
            ->where('is_active', true)
            ->first();

        return $achievement?->id;
    }

    protected function ensureUserCanSubmitForStudent(Student $student, User $user): void
    {
        $currentRole = $user->getCurrentRole();

        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id'));
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id'));
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id'));

        if ($level === 'university') {
            return;
        }

        if ($level === 'faculty' && $facultyId && $student->faculty_id !== $facultyId) {
            throw new \Exception('Operator tidak dapat membuat prestasi untuk mahasiswa fakultas lain.');
        }

        if ($level === 'department' && $departmentId && $student->department_id !== $departmentId) {
            throw new \Exception('Operator tidak dapat membuat prestasi untuk mahasiswa jurusan lain.');
        }

        if ($level === 'program_study' && $programStudyId && $student->program_study_id !== $programStudyId) {
            throw new \Exception('Operator tidak dapat membuat prestasi untuk mahasiswa prodi lain.');
        }

        if (! $level && $user->faculty && $student->faculty !== $user->faculty) {
            throw new \Exception('Operator tidak dapat membuat prestasi untuk mahasiswa fakultas lain.');
        }
    }
}
