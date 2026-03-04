<?php

namespace App\Services\Validator;

use App\Models\Achievement;
use App\Models\AchievementDocument;
use App\Models\StudentAchievement;
use App\Services\AchievementApprovalService;
use Illuminate\Http\UploadedFile;

class AchievementSubmissionService
{
    public function __construct(
        protected AchievementApprovalService $approvalService
    ) {
    }

    public function submitAchievement(array $data, UploadedFile $certificate, int $userId, ?array $additionalDocuments = []): StudentAchievement
    {
        $achievementId = $this->resolveAchievementIdFromCategory($data['category_id']);
        if (!$achievementId) {
            throw new \Exception('Kategori yang dipilih belum memiliki template prestasi. Hubungi admin.');
        }

        // Upload certificate
        $certificatePath = $certificate->store('certificates', 'public');

        // Determine initial status based on action
        $initialStatus = match ($data['submit_action']) {
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            default => 'Menunggu',
        };

        // Determine SK required
        $skRequired = !($data['skip_sk'] ?? false);

        // Create achievement
        $achievement = StudentAchievement::create([
            'student_id' => $data['student_id'],
            'achievement_id' => $achievementId,
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

        // Process additional documents if any
        if ($additionalDocuments) {
            foreach ($additionalDocuments as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $this->uploadDocument(
                        $achievement,
                        $file,
                        AchievementDocument::TYPE_FOTO_DOKUMENTASI,
                        $userId,
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
            $sk = \App\Models\SKDocument::findOrFail($skId);

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
            : 'Disetujui tanpa SK: ' . ($achievement->sk_waiver_reason ? StudentAchievement::getSkWaiverReasons()[$achievement->sk_waiver_reason] : 'N/A');

        $this->approvalService->approve($achievement, $user, $notes, null);
    }

    public function handleRejection(StudentAchievement $achievement, $user, string $reason): void
    {
        $this->approvalService->reject($achievement, $user, $reason);
    }

    protected function uploadDocument(
        StudentAchievement $achievement,
        UploadedFile $file,
        string $type,
        int $userId,
        string $status = AchievementDocument::STATUS_APPROVED
    ): string {
        $fileName = $type . '_' . $achievement->sa_id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

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
}
