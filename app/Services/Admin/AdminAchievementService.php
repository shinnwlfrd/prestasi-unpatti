<?php

namespace App\Services\Admin;

use App\Models\Achievement;
use App\Models\AchievementDocument;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\SKDocument;
use App\Models\SKAssignment;
use App\Services\AchievementApprovalService;
use App\Services\SiakadApiService;
use Illuminate\Support\Facades\Log;

class AdminAchievementService
{
    public function __construct(
        protected AchievementApprovalService $approvalService,
        protected SiakadApiService $siakadService
    ) {}

    public function processBatchSubmission(array $validated, array $studentIds, array $attachments, array $requestData, $user)
    {
        $achievementId = Achievement::where('category_id', $validated['category_id'])
            ->where('is_active', true)
            ->value('id');

        if (!$achievementId) {
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

        if (!$siakadData) {
            throw new \Exception("Data mahasiswa dengan ID {$idMahasiswa} tidak ditemukan di SIAKAD.");
        }

        // Get NIM from SIAKAD data
        $nim = $siakadData['registrasi']['nim'] ?? null;

        if (!$nim) {
            throw new \Exception("NIM tidak ditemukan untuk mahasiswa ID {$idMahasiswa}.");
        }

        // Check if student exists in local database
        $student = Student::withTrashed()->find($nim);

        // If not exists, create from SIAKAD data or update existing
        $studentData = $this->siakadService->transformToStudentData($siakadData);
        if (!$student) {
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

        if (!$studentFiles || !isset($studentFiles['certificate'])) {
            throw new \Exception("Sertifikat untuk mahasiswa ID {$idMahasiswa} tidak ditemukan.");
        }

        $certificate = $studentFiles['certificate'];
        $additionalDocs = $studentFiles['additional_documents'] ?? [];

        // Store certificate for this student
        $certificatePath = $certificate->store('certificates', 'public');

        // Determine initial status based on action
        $submitAction = $requestData['submit_action'] ?? 'pending';
        $initialStatus = match ($submitAction) {
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            default => 'Menunggu',
        };

        // Determine SK required
        $skRequired = !($requestData['skip_sk'] ?? false);

        $achievement = StudentAchievement::create([
            'student_id' => $nim,
            'achievement_id' => $achievementId,
            'event_name' => $validated['event_name'],
            'level' => $validated['level'],
            'organizer' => $validated['organizer'],
            'event_date' => $validated['event_date'],
            'ranking' => $validated['ranking'] ?? null,
            'description' => $validated['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => $initialStatus,
            'validator_id' => $submitAction !== 'pending' ? $user->id : null,
            'submitted_by' => 'admin',
            'submitted_at' => now(),
            'sk_required' => $skRequired,
            'sk_waiver_reason' => $requestData['sk_waiver_reason'] ?? null,
            'sk_waiver_notes' => $requestData['sk_waiver_notes'] ?? null,
        ]);

        // Process additional documents if any
        foreach ($additionalDocs as $file) {
            if ($file->isValid()) {
                $fileName = 'SuppDoc_' . $achievement->sa_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

                $achievement->documents()->create([
                    'document_type' => AchievementDocument::TYPE_FOTO_DOKUMENTASI,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => AchievementDocument::STATUS_PENDING,
                ]);
            }
        }

        // Handle different actions
        if ($submitAction === 'approve') {
            $skDocumentPath = null;

            if (!empty($requestData['sk_id'])) {
                $skDocument = SKDocument::find($requestData['sk_id']);
                if ($skDocument) {
                    SKAssignment::create([
                        'sk_id' => $skDocument->id,
                        'sa_id' => $achievement->sa_id,
                        'assigned_by' => $user->id,
                        'assigned_at' => now(),
                    ]);
                    $skDocumentPath = $skDocument->file_path;
                }
            }

            if (isset($requestData['alternative_document_file']) && $requestData['alternative_document_file']->isValid()) {
                $file = $requestData['alternative_document_file'];
                $fileName = 'Alt_Doc_' . $achievement->sa_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

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

            $notes = $skRequired
                ? 'Disetujui langsung oleh admin saat submit'
                : 'Disetujui tanpa SK: ' . (!empty($requestData['sk_waiver_reason']) ? StudentAchievement::getSkWaiverReasons()[$requestData['sk_waiver_reason']] : 'N/A');

            $this->approvalService->approve($achievement, $user, $notes, $skDocumentPath);
        } elseif ($submitAction === 'reject') {
            $this->approvalService->reject($achievement, $user, $requestData['rejection_reason'] ?? null);
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
                        'prodi' => $siakadData['program_studi']['nama'] ?? ''
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error resolving old student ID in service', ['id' => $idMahasiswa, 'error' => $e->getMessage()]);
            }
        }

        return json_encode($selectedStudents);
    }
}
