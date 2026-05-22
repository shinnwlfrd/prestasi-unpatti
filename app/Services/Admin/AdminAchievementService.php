<?php

namespace App\Services\Admin;

use App\Helpers\ValidationStatusHelper;
use App\Models\AcademicPeriod;
use App\Models\Achievement;
use App\Models\AchievementDocument;
use App\Models\DocumentRevision;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\ValidationChecklist;
use App\Services\SiakadApiService;
use Illuminate\Support\Facades\Log;

class AdminAchievementService
{
    public function __construct(
        protected UniversityValidationService $universityValidationService,
        protected SiakadApiService $siakadService
    ) {}

    public function processBatchSubmission(array $validated, array $studentIds, array $attachments, array $requestData, $user)
    {
        $achievementId = Achievement::where('category_id', $validated['category_id'])
            ->where('is_active', true)
            ->value('id');

        if (! $achievementId) {
            throw new \Exception('Kategori yang dipilih belum memiliki template prestasi. Hubungi admin.');
        }

        $createdAchievements = [];
        $errors = [];

        foreach ($studentIds as $idMahasiswa) {
            try {
                $achievement = $this->createStudentAchievement(
                    $idMahasiswa,
                    $achievementId,
                    $validated,
                    $attachments[$idMahasiswa] ?? null,
                    $requestData,
                    $user
                );

                $createdAchievements[] = $achievement;
            } catch (\Exception $e) {
                $errors[] = "Gagal membuat prestasi untuk mahasiswa ID {$idMahasiswa}: {$e->getMessage()}";
            }
        }

        return [
            'success_count' => count($createdAchievements),
            'total_count' => count($studentIds),
            'created_achievements' => $createdAchievements,
            'errors' => $errors,
        ];
    }

    protected function createStudentAchievement($idMahasiswa, $achievementId, array $validated, $studentFiles, array $requestData, $user): StudentAchievement
    {
        // Fetch full data from SIAKAD API
        $siakadData = $this->siakadService->getMahasiswaById($idMahasiswa);

        if (! $siakadData) {
            throw new \Exception("Data mahasiswa dengan ID {$idMahasiswa} tidak ditemukan di SIAKAD.");
        }

        // Get NIM from SIAKAD data
        $nim = $siakadData['registrasi']['nim'] ?? null;

        if (! $nim) {
            throw new \Exception("NIM tidak ditemukan untuk mahasiswa ID {$idMahasiswa}.");
        }

        // Check if student exists in local database
        $student = Student::withTrashed()->find($nim);

        // If not exists, create from SIAKAD data or update existing
        $studentData = $this->siakadService->transformToStudentData($siakadData);
        if (! $student) {
            $student = Student::create($studentData);
            Log::info('New student created from SIAKAD', ['nim' => $nim, 'name' => $student->name]);
        } else {
            if ($student->trashed()) {
                $student->restore();
                Log::info('Restored soft-deleted student during achievement creation', ['nim' => $nim]);
            }
            $student->update($studentData);
            Log::info('Student data updated from SIAKAD', ['nim' => $nim, 'name' => $student->name]);
        }

        if (! $studentFiles || ! isset($studentFiles['certificate'])) {
            throw new \Exception("Sertifikat untuk mahasiswa ID {$idMahasiswa} tidak ditemukan.");
        }

        $certificate = $studentFiles['certificate'];
        $additionalDocs = $studentFiles['additional_documents'] ?? [];

        // Store certificate for this student
        $certificatePath = $certificate->store('certificates', 'public');

        $submitAction = $requestData['submit_action'] ?? 'pending';
        $skRequired = ! ($requestData['skip_sk'] ?? false);
        $activePeriodId = AcademicPeriod::active()->value('id');
        $initialState = ValidationStatusHelper::getInitialSubmissionState('admin');

        $achievement = StudentAchievement::create([
            'student_id' => $nim,
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
            'event_name' => $validated['event_name'],
            'level' => $validated['level'],
            'organizer' => $validated['organizer'],
            'event_date' => $validated['event_date'],
            'ranking' => $validated['ranking'] ?? null,
            'description' => $validated['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => $initialState['validation_status'],
            'validation_stage' => $initialState['validation_stage'],
            'current_stage' => $initialState['current_stage'],
            'validator_id' => null,
            'submitted_by' => 'admin',
            'submitted_at' => now(),
            'sk_required' => $skRequired,
            'sk_waiver_reason' => $requestData['sk_waiver_reason'] ?? null,
            'sk_waiver_notes' => $requestData['sk_waiver_notes'] ?? null,
        ]);

        // Create certificate document record in achievement_documents
        $certDoc = $achievement->documents()->create([
            'document_type' => AchievementDocument::TYPE_SERTIFIKAT,
            'file_path' => $certificatePath,
            'file_name' => $certificate->getClientOriginalName(),
            'file_type' => $certificate->getMimeType(),
            'file_size' => $certificate->getSize(),
            'status' => $submitAction === 'approve' ? AchievementDocument::STATUS_APPROVED : AchievementDocument::STATUS_PENDING,
            'verified_by' => $submitAction === 'approve' ? $user->id : null,
            'verified_at' => $submitAction === 'approve' ? now() : null,
        ]);

        if ($submitAction === 'approve') {
            $certDoc->logRevision(DocumentRevision::ACTION_APPROVED, 'Disetujui otomatis saat submit oleh admin', $user->id);
        }

        // Process additional documents if any
        foreach ($additionalDocs as $file) {
            if ($file->isValid()) {
                $fileName = 'SuppDoc_'.$achievement->sa_id.'_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

                $docStatus = $submitAction === 'approve' ? AchievementDocument::STATUS_APPROVED : AchievementDocument::STATUS_PENDING;

                $addDoc = $achievement->documents()->create([
                    'document_type' => AchievementDocument::TYPE_FOTO_DOKUMENTASI,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => $docStatus,
                    'verified_by' => $submitAction === 'approve' ? $user->id : null,
                    'verified_at' => $submitAction === 'approve' ? now() : null,
                ]);

                if ($submitAction === 'approve') {
                    $addDoc->logRevision(DocumentRevision::ACTION_APPROVED, 'Disetujui otomatis saat submit oleh admin', $user->id);
                }
            }
        }

        // Handle different actions
        if ($submitAction === 'approve') {
            if (isset($requestData['alternative_document_file']) && $requestData['alternative_document_file']->isValid()) {
                $file = $requestData['alternative_document_file'];
                $fileName = 'Alt_Doc_'.$achievement->sa_id.'_'.time().'.'.$file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

                $achievement->update(['alternative_document_path' => $filePath]);

                $achievement->documents()->create([
                    'document_type' => 'dokumen_alternatif',
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => AchievementDocument::STATUS_APPROVED,
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                ]);
            }

            // If non-academic and we still have less than 2 distinct approved document types, auto-create a mock foto_dokumentasi
            if ($achievement->achievement && $achievement->achievement->category_id !== 1) {
                $approvedTypesCount = $achievement->documents()
                    ->where('status', AchievementDocument::STATUS_APPROVED)
                    ->pluck('document_type')
                    ->unique()
                    ->count();

                if ($approvedTypesCount < 2) {
                    $mockDoc = $achievement->documents()->create([
                        'document_type' => AchievementDocument::TYPE_FOTO_DOKUMENTASI,
                        'file_path' => $certificatePath,
                        'file_name' => 'Dokumentasi_'.$certificate->getClientOriginalName(),
                        'file_type' => $certificate->getMimeType(),
                        'file_size' => $certificate->getSize(),
                        'status' => AchievementDocument::STATUS_APPROVED,
                        'verified_by' => $user->id,
                        'verified_at' => now(),
                    ]);
                    $mockDoc->logRevision(DocumentRevision::ACTION_APPROVED, 'Dokumen pendukung otomatis disetujui saat submit oleh admin', $user->id);
                }
            }

            // Create a completed validation checklist
            ValidationChecklist::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => $user->id,
                'certificate_valid' => true,
                'event_date_valid' => true,
                'organizer_valid' => true,
                'level_appropriate' => true,
                'documents_complete' => true,
            ]);

            $notes = $skRequired
                ? 'Disetujui langsung oleh admin saat submit'
                : 'Disetujui tanpa SK: '.(! empty($requestData['sk_waiver_reason']) ? StudentAchievement::getSkWaiverReasons()[$requestData['sk_waiver_reason']] : 'N/A');

            $this->universityValidationService->approve($achievement, $user, $requestData['sk_id'] ?? null, $notes);
        } elseif ($submitAction === 'reject') {
            $this->universityValidationService->reject($achievement, $user, $requestData['rejection_reason'] ?? 'Ditolak saat submit oleh admin.');
        }

        return $achievement;
    }

    /**
     * Resolve student data for UI restoration after validation errors
     */
    public function resolveStudentDataForOldInput(array $studentIds): string
    {
        if (empty($studentIds)) {
            return '[]';
        }

        $selectedStudents = [];

        foreach ($studentIds as $idMahasiswa) {
            try {
                $siakadData = $this->siakadService->getMahasiswaById($idMahasiswa);
                if ($siakadData) {
                    $selectedStudents[] = [
                        'id' => $siakadData['id_mahasiswa'],
                        'student_id' => $siakadData['id_mahasiswa'],
                        'nim' => $siakadData['registrasi']['nim'] ?? '',
                        'name' => $siakadData['nama_mahasiswa'],
                        'faculty' => $siakadData['fakultas']['nama'] ?? '',
                        'prodi' => $siakadData['program_studi']['nama'] ?? '',
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error resolving old student ID in service', ['id' => $idMahasiswa, 'error' => $e->getMessage()]);
            }
        }

        return json_encode($selectedStudents);
    }
}
