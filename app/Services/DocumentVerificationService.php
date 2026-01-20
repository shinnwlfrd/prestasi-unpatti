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
        return $document->approve($verifier, $notes);
    }

    public function rejectDocument(AchievementDocument $document, User $verifier, string $reason): bool
    {
        return $document->reject($verifier, $reason);
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
}
