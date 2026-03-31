<?php

namespace App\Services\Student;

use App\Models\Achievement;
use App\Models\StudentAchievement;
use Illuminate\Http\UploadedFile;

class AchievementService
{
    public function submitAchievement(array $data, string $studentId, UploadedFile $certificate, ?array $additionalDocuments = []): StudentAchievement
    {
        $achievementId = $this->resolveAchievementIdFromCategory($data['category_id']);
        if (!$achievementId) {
            throw new \Exception('Kategori yang dipilih belum memiliki template prestasi. Hubungi admin.');
        }

        // Upload certificate
        $certificatePath = $certificate->store('certificates', 'public');

        if (!$certificatePath) {
            throw new \Exception('Gagal menyimpan file sertifikat ke storage.');
        }

        // Get active academic period
        $activePeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            throw new \Exception('Tidak ada periode akademik aktif. Hubungi admin.');
        }

        // IMPORTANT: Create student record if not exists (first-time submission)
        $this->ensureStudentExists($studentId);

        // Create achievement with two-stage validation status
        $achievement = StudentAchievement::create([
            'student_id' => $studentId,
            'achievement_id' => $achievementId,
            'academic_period_id' => $activePeriod->id,
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
                        'document_type' => \App\Models\AchievementDocument::TYPE_FOTO_DOKUMENTASI, // Default to documentation
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'status' => \App\Models\AchievementDocument::STATUS_PENDING,
                    ]);
                }
            }
        }

        // Log for debugging
        \Log::info('Achievement submitted', [
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
     * Ensure student record exists in database before submitting achievement
     * Creates student from session data if not exists
     */
    protected function ensureStudentExists(string $studentId): void
    {
        $student = \App\Models\Student::withTrashed()->find($studentId);
        
        if ($student) {
            if ($student->trashed()) {
                $student->restore();
                \Log::info('Restored soft-deleted student during achievement submission', ['student_id' => $studentId]);
            }
            return; // Student already exists
        }

        // Get student data from session
        $studentData = session('student_data');
        
        if (!$studentData) {
            throw new \Exception('Student data not found in session. Please login again.');
        }

        // Create student record from session data
        \App\Models\Student::create([
            'student_id' => $studentData['nim'],
            'name' => $studentData['nama'],
            'email' => $studentData['email'],
            'faculty' => $studentData['fakultas'] ?? 'Data Belum Tersedia',
            'faculty_id' => $studentData['fakultas_id'] ?? null,
            'major' => $studentData['jurusan'] ?? 'Data Belum Tersedia',
            'major_id' => $studentData['jurusan_id'] ?? null,
            'program_study' => $studentData['program_studi'] ?? 'Data Belum Tersedia',
            'program_study_id' => $studentData['program_studi_id'] ?? null,
            'year' => $studentData['angkatan'] ?? substr($studentData['nim'], 0, 4),
            'ipk' => $studentData['ipk'] ?? null,
            'foto_url' => $studentData['foto_url'] ?? null,
            'is_active' => true,
        ]);

        \Log::info('Student record created from session data', [
            'student_id' => $studentId,
            'email' => $studentData['email'],
            'data_source' => $studentData['data_source'] ?? 'unknown'
        ]);
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
                StudentAchievement::STATUS_REJECTED
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
}
