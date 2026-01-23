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

        // Create achievement
        return StudentAchievement::create([
            'student_id' => $studentId,
            'achievement_id' => $data['achievement_id'],
            'event_name' => $data['event_name'],
            'level' => $data['level'],
            'organizer' => $data['organizer'],
            'event_date' => $data['event_date'],
            'ranking' => $data['ranking'] ?? null,
            'description' => $data['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => 'Menunggu',
            'submitted_by' => 'student',
            'submitted_at' => now(),
        ]);
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
            'pending' => (clone $achievements)->where('validation_status', 'Menunggu')->count(),
            'approved' => (clone $achievements)->where('validation_status', 'Disetujui')->count(),
            'rejected' => (clone $achievements)->where('validation_status', 'Ditolak')->count(),
            'revision' => (clone $achievements)->where('validation_status', 'Revisi')->count(),
        ];
    }
}
