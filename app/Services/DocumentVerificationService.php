<?php

namespace App\Services;

use App\Models\AchievementDocument;
use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Support\Collection;

class DocumentVerificationService
{
    public function approveDocument(AchievementDocument $document, User $verifier, ?string $notes = null): bool
    {
        $result = $document->approve($verifier, $notes);
        
        if ($result) {
            $this->updateAchievementCredibility($document->studentAchievement);
        }
        
        return $result;
    }

    public function rejectDocument(AchievementDocument $document, User $verifier, string $reason): bool
    {
        $result = $document->reject($verifier, $reason);
        
        if ($result) {
            $this->updateAchievementCredibility($document->studentAchievement);
        }
        
        return $result;
    }

    public function requestRevision(AchievementDocument $document, User $verifier, string $reason): bool
    {
        return $document->requestRevision($verifier, $reason);
    }

    public function bulkApprove(Collection $documents, User $verifier, ?string $notes = null): array
    {
        $approved = 0;
        $failed = 0;

        foreach ($documents as $document) {
            if ($document->approve($verifier, $notes)) {
                $approved++;
            } else {
                $failed++;
            }
        }

        // Update credibility for affected achievements
        $achievementIds = $documents->pluck('sa_id')->unique();
        foreach ($achievementIds as $saId) {
            $achievement = StudentAchievement::find($saId);
            if ($achievement) {
                $this->updateAchievementCredibility($achievement);
            }
        }

        return ['approved' => $approved, 'failed' => $failed];
    }

    public function bulkReject(Collection $documents, User $verifier, string $reason): array
    {
        $rejected = 0;
        $failed = 0;

        foreach ($documents as $document) {
            if ($document->reject($verifier, $reason)) {
                $rejected++;
            } else {
                $failed++;
            }
        }

        // Update credibility for affected achievements
        $achievementIds = $documents->pluck('sa_id')->unique();
        foreach ($achievementIds as $saId) {
            $achievement = StudentAchievement::find($saId);
            if ($achievement) {
                $this->updateAchievementCredibility($achievement);
            }
        }

        return ['rejected' => $rejected, 'failed' => $failed];
    }

    public function getDocumentStatistics(StudentAchievement $achievement): array
    {
        $documents = $achievement->documents;

        return [
            'total' => $documents->count(),
            'draft' => $documents->where('status', AchievementDocument::STATUS_DRAFT)->count(),
            'pending' => $documents->where('status', AchievementDocument::STATUS_PENDING)->count(),
            'revision' => $documents->where('status', AchievementDocument::STATUS_REVISION)->count(),
            'approved' => $documents->where('status', AchievementDocument::STATUS_APPROVED)->count(),
            'rejected' => $documents->where('status', AchievementDocument::STATUS_REJECTED)->count(),
        ];
    }

    public function canAchievementBeApproved(StudentAchievement $achievement): array
    {
        $documents = $achievement->documents;
        $errors = [];

        // Check if there are any documents
        if ($documents->isEmpty()) {
            $errors[] = 'Tidak ada dokumen yang diupload.';
            return ['can_approve' => false, 'errors' => $errors];
        }

        // Check if all documents are verified
        $pendingDocs = $documents->whereIn('status', [
            AchievementDocument::STATUS_DRAFT,
            AchievementDocument::STATUS_PENDING,
            AchievementDocument::STATUS_REVISION
        ]);

        if ($pendingDocs->isNotEmpty()) {
            $errors[] = 'Masih ada ' . $pendingDocs->count() . ' dokumen yang belum diverifikasi.';
        }

        // Check if any document is rejected
        $rejectedDocs = $documents->where('status', AchievementDocument::STATUS_REJECTED);
        if ($rejectedDocs->isNotEmpty()) {
            $errors[] = 'Ada ' . $rejectedDocs->count() . ' dokumen yang ditolak.';
        }

        // Check minimum documents for non-academic
        if ($achievement->achievement?->category === 'Non-Akademik') {
            $approvedTypes = $documents
                ->where('status', AchievementDocument::STATUS_APPROVED)
                ->pluck('document_type')
                ->unique()
                ->count();

            if ($approvedTypes < 2) {
                $errors[] = 'Prestasi non-akademik memerlukan minimal 2 jenis dokumen yang disetujui.';
            }
        }

        return [
            'can_approve' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function updateAchievementCredibility(StudentAchievement $achievement): void
    {
        // Only count approved documents for credibility
        $approvedDocs = $achievement->documents()
            ->where('status', AchievementDocument::STATUS_APPROVED)
            ->get();

        $score = 0;
        $documentTypes = $approvedDocs->pluck('document_type')->unique();

        foreach ($documentTypes as $type) {
            $score += AchievementDocument::CREDIBILITY_SCORES[$type] ?? 0;
        }

        // Bonus for multiple approved documents
        if ($approvedDocs->count() > $documentTypes->count()) {
            $bonus = min(($approvedDocs->count() - $documentTypes->count()) * 2, 10);
            $score += $bonus;
        }

        // Level bonus
        $score += match($achievement->level) {
            StudentAchievement::LEVEL_INTERNASIONAL => 10,
            StudentAchievement::LEVEL_NASIONAL => 5,
            default => 0,
        };

        $achievement->credibility_score = min($score, 100);
        $achievement->requires_extra_review = $score < 70;
        $achievement->save();
    }
}
