<?php

namespace App\Services\Student;

use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use App\Services\SiakadApiService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    public function __construct(
        protected SiakadApiService $siakadService
    ) {}

    public function submitAchievement(array $data, string $studentId, UploadedFile $certificate, ?array $additionalDocuments = []): StudentAchievement
    {
        $achievementId = $this->resolveAchievementIdFromCategory($data['category_id']);
        if (! $achievementId) {
            throw new \Exception('Kategori yang dipilih belum memiliki template prestasi. Hubungi admin.');
        }

        // Upload certificate
        $certificatePath = $certificate->store('certificates', 'public');

        if (! $certificatePath) {
            throw new \Exception('Gagal menyimpan file sertifikat ke storage.');
        }

        $activePeriod = $this->getOpenSubmissionPeriod();
        $student = $this->syncStudentProfile($studentId);

        // Create achievement with two-stage validation status
        $achievement = StudentAchievement::create([
            'student_id' => $studentId,
            'achievement_id' => $achievementId,
            'academic_period_id' => $activePeriod->id,
            'student_snapshot' => $this->buildStudentSnapshot($student),
            'event_name' => $data['event_name'],
            'level' => $data['level'],
            'organizer' => $data['organizer'],
            'event_date' => $data['event_date'],
            'ranking' => $data['ranking'] ?? null,
            'description' => $data['description'] ?? null,
            'certificate' => $certificatePath,
            // Two-stage validation fields
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'validation_stage' => StudentAchievement::STAGE_FACULTY,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'submitted_by' => 'student',
            'submitted_at' => now(),
        ]);

        // Process additional documents if any
        if ($additionalDocuments) {
            foreach ($additionalDocuments as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $path = $file->store('documents/achievements', 'public');

                    $achievement->documents()->create([
                        'document_type' => AchievementDocument::TYPE_FOTO_DOKUMENTASI, // Default to documentation
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'status' => AchievementDocument::STATUS_PENDING,
                    ]);
                }
            }
        }

        // Log for debugging
        Log::info('Achievement submitted', [
            'sa_id' => $achievement->sa_id,
            'student_id' => $studentId,
            'academic_period_id' => $activePeriod->id,
            'certificate' => $certificatePath,
            'additional_docs_count' => count($additionalDocuments ?? []),
            'status' => $achievement->validation_status,
            'stage' => $achievement->current_stage,
            'file_exists' => \Storage::disk('public')->exists($certificatePath),
        ]);

        return $achievement;
    }

    /**
     * Prepare submit form by checking active period and refreshing academic profile.
     */
    public function prepareSubmissionForm(string $studentId): AcademicPeriod
    {
        $activePeriod = $this->getOpenSubmissionPeriod();
        $this->syncStudentProfile($studentId);

        return $activePeriod;
    }

    public function syncStudentProfile(string $studentId): Student
    {
        $student = Student::withTrashed()->find($studentId);

        $studentData = $this->resolveStudentData($studentId, $student);
        $attributes = $this->mapStudentAttributes($studentId, $studentData);

        if ($student && $student->trashed()) {
            $student->restore();
            Log::info('Restored soft-deleted student during achievement submission', ['student_id' => $studentId]);
        }

        $student = Student::updateOrCreate(
            ['student_id' => $studentId],
            $attributes
        );

        $this->updateStudentSession($student);

        Log::info('Student profile refreshed for achievement submission', [
            'student_id' => $studentId,
            'email' => $student->email,
            'id_mahasiswa' => $student->id_mahasiswa,
        ]);

        return $student;
    }

    public function getStudentAchievements(string $studentId)
    {
        // Exclude soft-deleted achievements for students
        return StudentAchievement::with(['achievement.category', 'validator', 'documents'])
            ->where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->latest()
            ->get();
    }

    public function getAchievementStatistics(string $studentId): array
    {
        // Exclude soft-deleted achievements for students
        $achievements = StudentAchievement::where('student_id', $studentId)
            ->whereNull('deleted_at');

        return [
            'total' => $achievements->count(),
            // Two-stage validation statuses
            'submitted' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_SUBMITTED)->count(),
            'faculty_review' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_FACULTY_REVIEW)->count(),
            'faculty_approved' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_FACULTY_APPROVED)->count(),
            'faculty_revision' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_FACULTY_REVISION)->count(),
            'university_review' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_UNIVERSITY_REVIEW)->count(),
            'university_approved' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_UNIVERSITY_APPROVED)->count(),
            // Legacy statuses (for backward compatibility)
            'pending' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_PENDING)->count(),
            'approved' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_APPROVED)->count(),
            'rejected' => (clone $achievements)->whereIn('validation_status', [
                StudentAchievement::STATUS_FACULTY_REJECTED,
                StudentAchievement::STATUS_UNIVERSITY_REJECTED,
                StudentAchievement::STATUS_REJECTED,
            ])->count(),
            'revision' => (clone $achievements)->where('validation_status', StudentAchievement::STATUS_NEED_REVISION)->count(),
        ];
    }

    protected function resolveAchievementIdFromCategory(int $categoryId): ?int
    {
        $achievement = Achievement::where('category_id', $categoryId)
            ->where('is_active', true)
            ->first();

        return $achievement?->id;
    }

    public function requestReview(StudentAchievement $achievement, string $reason): void
    {
        $oldStatus = $achievement->validation_status;

        $achievement->update([
            'validation_status' => StudentAchievement::STATUS_SUBMITTED,
            'is_resubmission' => true,
            'resubmission_count' => ($achievement->resubmission_count ?? 0) + 1,
            'last_resubmitted_at' => now(),
            'resubmission_reason' => $reason,
            'current_stage' => StudentAchievement::STAGE_FACULTY,
            'faculty_validator_id' => null,
            'faculty_validated_at' => null,
            'faculty_notes' => null,
        ]);

        ValidationLog::create([
            'sa_id' => $achievement->sa_id,
            'validator_id' => null,
            'old_status' => $oldStatus,
            'new_status' => StudentAchievement::STATUS_SUBMITTED,
            'notes' => "Review ulang ke-{$achievement->resubmission_count}: {$reason}",
            'validation_type' => 'resubmission',
            'validation_stage' => StudentAchievement::STAGE_FACULTY,
            'stage_action' => 'student_resubmission',
            'metadata' => [
                'actor' => 'student',
                'student_id' => $achievement->student_id,
            ],
            'validated_at' => now(),
        ]);
    }

    public function deleteAchievement(StudentAchievement $achievement): void
    {
        $achievement->delete();

        ValidationLog::create([
            'sa_id' => $achievement->sa_id,
            'validator_id' => null,
            'old_status' => $achievement->validation_status,
            'new_status' => 'deleted',
            'notes' => 'Prestasi dihapus oleh mahasiswa',
            'validation_type' => 'deletion',
            'validation_stage' => $achievement->current_stage ?? StudentAchievement::STAGE_FACULTY,
            'stage_action' => 'student_delete',
            'metadata' => [
                'actor' => 'student',
                'student_id' => $achievement->student_id,
            ],
            'validated_at' => now(),
        ]);
    }

    protected function getOpenSubmissionPeriod(): AcademicPeriod
    {
        $activePeriod = AcademicPeriod::active()->first();
        if (! $activePeriod) {
            throw new \Exception('Tidak ada periode akademik aktif. Pengajuan prestasi belum dibuka.');
        }

        if (! $activePeriod->isSubmissionOpen()) {
            $deadline = $activePeriod->submission_deadline ?? $activePeriod->end_date;
            $deadlineStr = $deadline ? $deadline->format('d M Y H:i') : '';
            throw new \Exception(
                "Periode submit prestasi \"{$activePeriod->name}\" sudah ditutup".
                ($deadlineStr ? " sejak {$deadlineStr}." : '.')
            );
        }

        return $activePeriod;
    }

    protected function resolveStudentData(string $studentId, ?Student $student = null): array
    {
        $sessionData = session('student_data');
        if (! $sessionData) {
            throw new \Exception('Data mahasiswa tidak ditemukan di session. Silakan login ulang.');
        }

        $studentData = [
            'student_id' => $studentId,
            'id_mahasiswa' => $sessionData['id_mahasiswa'] ?? $student?->id_mahasiswa,
            'name' => $sessionData['nama'] ?? session('student_name') ?? $student?->name,
            'email' => $sessionData['email'] ?? session('student_email') ?? $student?->email,
            'foto_url' => $sessionData['foto_url'] ?? $student?->foto_url,
            'ipk' => $sessionData['ipk'] ?? $student?->ipk,
            'angkatan' => $sessionData['angkatan'] ?? $student?->angkatan ?? substr($studentId, 0, 4),
            'faculty_id' => $sessionData['fakultas_id'] ?? $student?->faculty_id,
            'faculty' => $sessionData['fakultas'] ?? $student?->faculty,
            'department_id' => $sessionData['jurusan_id'] ?? $student?->department_id,
            'department' => $sessionData['jurusan'] ?? $student?->department,
            'program_study_id' => $sessionData['program_studi_id'] ?? $student?->program_study_id,
            'program_study' => $sessionData['program_studi'] ?? $student?->program_study,
        ];

        $siakadData = null;
        if (! empty($studentData['id_mahasiswa'])) {
            $siakadData = $this->siakadService->getMahasiswaById((string) $studentData['id_mahasiswa']);
        }

        if (! $siakadData && ! empty($studentData['email'])) {
            $siakadData = $this->siakadService->getMahasiswaByEmail((string) $studentData['email']);
        }

        if (! $siakadData) {
            $siakadData = $this->siakadService->getMahasiswaByNim($studentId);
        }

        if ($siakadData) {
            $studentData = array_merge($studentData, $this->siakadService->transformToStudentData($siakadData));
        }

        return $studentData;
    }

    protected function mapStudentAttributes(string $studentId, array $studentData): array
    {
        return [
            'student_id' => $studentId,
            'id_mahasiswa' => $studentData['id_mahasiswa'] ?? null,
            'name' => $studentData['name'] ?? $studentData['nama'] ?? 'Mahasiswa',
            'email' => $studentData['email'] ?? ($studentId.'@student.unpatti.ac.id'),
            'foto_url' => $studentData['foto_url'] ?? null,
            'ipk' => $studentData['ipk'] ?? null,
            'gpa' => $studentData['ipk'] ?? null,
            'angkatan' => $studentData['angkatan'] ?? substr($studentId, 0, 4),
            'faculty_id' => $studentData['faculty_id'] ?? $studentData['fakultas_id'] ?? null,
            'faculty' => $studentData['faculty'] ?? $studentData['fakultas'] ?? 'Data Belum Tersedia',
            'department_id' => $studentData['department_id'] ?? $studentData['jurusan_id'] ?? null,
            'department' => $studentData['department'] ?? $studentData['jurusan'] ?? 'Data Belum Tersedia',
            'program_study_id' => $studentData['program_study_id'] ?? $studentData['program_studi_id'] ?? null,
            'program_study' => $studentData['program_study'] ?? $studentData['program_studi'] ?? 'Data Belum Tersedia',
        ];
    }

    protected function updateStudentSession(Student $student): void
    {
        $studentData = array_merge(session('student_data', []), [
            'id_mahasiswa' => $student->id_mahasiswa,
            'nim' => $student->student_id,
            'nama' => $student->name,
            'email' => $student->email,
            'foto_url' => $student->foto_url,
            'ipk' => $student->ipk,
            'angkatan' => $student->angkatan,
            'fakultas_id' => $student->faculty_id,
            'fakultas' => $student->faculty,
            'jurusan_id' => $student->department_id,
            'jurusan' => $student->department,
            'program_studi_id' => $student->program_study_id,
            'program_studi' => $student->program_study,
        ]);

        session([
            'student_data' => $studentData,
            'student_profile' => $studentData,
            'student_name' => $student->name,
            'student_email' => $student->email,
        ]);
    }

    protected function buildStudentSnapshot(Student $student): array
    {
        return [
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
        ];
    }
}
