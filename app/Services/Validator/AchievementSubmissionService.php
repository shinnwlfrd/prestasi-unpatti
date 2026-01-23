<?php

namespace App\Services\Validator;

use App\Models\AchievementDocument;
use App\Models\StudentAchievement;
use App\Services\AchievementApprovalService;
use Illuminate\Http\UploadedFile;

class AchievementSubmissionService
{
    public function __construct(
        protected AchievementApprovalService $approvalService
    ) {}

    public function submitAchievement(array $data, UploadedFile $certificate, int $userId): StudentAchievement
    {
        // Upload certificate
        $certificatePath = $certificate->store('certificates', 'public');

        // Determine initial status based on action
        $initialStatus = match ($data['submit_action']) {
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            default => 'Menunggu',
        };

        // Determine SK required
        $skRequired = ! ($data['skip_sk'] ?? false);

        // Create achievement
        $achievement = StudentAchievement::create([
            'student_id' => $data['student_id'],
            'achievement_id' => $data['achievement_id'],
            'event_name' => $data['event_name'],
            'level' => $data['level'],
            'organizer' => $data['organizer'],
            'event_date' => $data['event_date'],
            'ranking' => $data['ranking'] ?? null,
            'description' => $data['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => $initialStatus,
            'validator_id' => $data['submit_action'] !== 'pending' ? $userId : null,
            'submitted_by' => 'validator',
            'submitted_at' => now(),
            'sk_required' => $skRequired,
            'sk_waiver_reason' => $data['sk_waiver_reason'] ?? null,
            'sk_waiver_notes' => $data['sk_waiver_notes'] ?? null,
        ]);

        return $achievement;
    }

    public function handleApproval(
        StudentAchievement $achievement,
        $user,
        ?UploadedFile $skResmi = null,
        ?UploadedFile $alternativeDocument = null
    ): void {
        $skDocumentPath = null;

        // Upload SK Resmi if provided
        if ($skResmi) {
            $skDocumentPath = $this->uploadDocument(
                $achievement,
                $skResmi,
                AchievementDocument::TYPE_SK_RESMI,
                $user->id
            );
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

        $this->approvalService->approve($achievement, $user, $notes, $skDocumentPath);
    }

    public function handleRejection(StudentAchievement $achievement, $user, string $reason): void
    {
        $this->approvalService->reject($achievement, $user, $reason);
    }

    protected function uploadDocument(
        StudentAchievement $achievement,
        UploadedFile $file,
        string $type,
        int $userId
    ): string {
        $fileName = $type.'_'.$achievement->sa_id.'_'.time().'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

        $achievement->documents()->create([
            'document_type' => $type,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => AchievementDocument::STATUS_APPROVED,
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);

        return $filePath;
    }
}
