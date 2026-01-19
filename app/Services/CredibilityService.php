<?php

namespace App\Services;

use App\Models\StudentAchievement;
use App\Models\AchievementDocument;

class CredibilityService
{
    public function calculateScore(StudentAchievement $achievement): float
    {
        $score = 0;
        $documentTypes = $achievement->documents->pluck('document_type')->unique();

        foreach ($documentTypes as $type) {
            $score += AchievementDocument::CREDIBILITY_SCORES[$type] ?? 0;
        }

        // Bonus for multiple documents of same type (max 10 bonus)
        $documentCount = $achievement->documents->count();
        if ($documentCount > $documentTypes->count()) {
            $bonus = min(($documentCount - $documentTypes->count()) * 2, 10);
            $score += $bonus;
        }

        // Level bonus
        $score += match($achievement->level) {
            StudentAchievement::LEVEL_INTERNASIONAL => 10,
            StudentAchievement::LEVEL_NASIONAL => 5,
            default => 0,
        };

        return min($score, 100);
    }

    public function requiresExtraReview(float $score): bool
    {
        return $score < 70;
    }

    public function getScoreCategory(float $score): string
    {
        if ($score >= 80) return 'high';
        if ($score >= 70) return 'medium';
        return 'low';
    }

    public function getScoreColor(float $score): string
    {
        if ($score >= 80) return 'success';
        if ($score >= 70) return 'warning';
        return 'danger';
    }

    public function validateDocumentRequirements(StudentAchievement $achievement): array
    {
        $errors = [];
        $category = $achievement->achievement?->category;

        if ($category === 'Non-Akademik') {
            $documentTypes = $achievement->documents->pluck('document_type')->unique()->count();
            if ($documentTypes < 2) {
                $errors[] = 'Prestasi non-akademik memerlukan minimal 2 jenis dokumen berbeda.';
            }
        }

        if ($achievement->documents->isEmpty()) {
            $errors[] = 'Minimal satu dokumen bukti harus diunggah.';
        }

        return $errors;
    }
}
