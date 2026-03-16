<?php

namespace App\Services\Student;

use App\Models\AchievementAppeal;
use App\Models\AchievementDocument;
use App\Models\StudentAchievement;

class AppealService
{
    public function submitAppeal(
        StudentAchievement $achievement,
        string $studentId,
        string $reason,
        ?string $publicationLink = null,
        ?array $documents = null
    ): AchievementAppeal {
        // Create appeal
        $appeal = AchievementAppeal::create([
            'sa_id' => $achievement->sa_id,
            'student_id' => $studentId,
            'reason' => $reason,
            'appeal_reason' => $reason, // alias
            'publication_link' => $publicationLink,
            'status' => 'pending',
        ]);

        // Upload documents if provided
        if ($documents && count($documents) > 0) {
            foreach ($documents as $index => $file) {
                $fileName = 'Appeal_Doc_'.$achievement->sa_id.'_'.($index + 1).'_'.time().'.'.$file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/'.$achievement->sa_id.'/appeals', $fileName, 'public');

                AchievementDocument::create([
                    'sa_id' => $achievement->sa_id,
                    'document_type' => 'dokumen_banding',
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'pending',
                ]);
            }
        }

        return $appeal;
    }

    public function canAppeal(StudentAchievement $achievement): bool
    {
        return $achievement->validation_status === StudentAchievement::STATUS_NEED_REVISION
            && ! $achievement->appeals()->where('status', 'pending')->exists();
    }
}
