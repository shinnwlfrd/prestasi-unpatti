<?php

namespace App\Services\Student;

use App\Models\StudentAchievement;
use Illuminate\Http\UploadedFile;

class AchievementService
{
    public function submitAchievement(array $data, string $studentId, UploadedFile $certificate): StudentAchievement
    {
        // Upload certificate
        $certificatePath = $certificate->store('certificates', 'public');

        if (! $certificatePath) {
            throw new \Exception('Gagal menyimpan file sertifikat ke storage.');
        }

        // Create achievement with two-stage validation status
        $achievement = StudentAchievement::create([
            'student_id' => $studentId,
            'achievement_id' => $data['achievement_id'],
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

        // Log for debugging
        \Log::info('Achievement submitted', [
            'sa_id' => $achievement->sa_id,
            'student_id' => $studentId,
            'certificate_path' => $certificatePath,
            'status' => $achievement->validation_status,
            'stage' => $achievement->current_stage,
            'file_exists' => \Storage::disk('public')->exists($certificatePath),
        ]);

        return $achievement;
    }

    public function getStudentAchievements(string $studentId)
    {
        return StudentAchievement::with(['achievement.category', 'validator', 'documents'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function getAchievementStatistics(string $studentId): array
    {
        $achievements = StudentAchievement::where('student_id', $studentId);

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
}
