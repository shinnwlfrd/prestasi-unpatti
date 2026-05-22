<?php

namespace App\Helpers;

use App\Models\StudentAchievement;

class ValidationStatusHelper
{
    /**
     * Get standardized status label
     */
    public static function getLabel(string $status): string
    {
        return match ($status) {
            // New statuses - Two-Stage Validation
            StudentAchievement::STATUS_DRAFT => 'Draft',
            StudentAchievement::STATUS_SUBMITTED => 'Telah Diajukan',

            // Faculty Stage
            StudentAchievement::STATUS_FACULTY_REVIEW => 'Sedang Ditinjau Fakultas',
            StudentAchievement::STATUS_FACULTY_APPROVED => 'Disetujui Fakultas',
            StudentAchievement::STATUS_FACULTY_REJECTED => 'Ditolak Fakultas',
            StudentAchievement::STATUS_FACULTY_REVISION => 'Perlu Revisi (Fakultas)',

            // University Stage
            StudentAchievement::STATUS_UNIVERSITY_REVIEW => 'Sedang Ditinjau Universitas',
            StudentAchievement::STATUS_UNIVERSITY_APPROVED => 'Disetujui Universitas',
            StudentAchievement::STATUS_UNIVERSITY_REJECTED => 'Ditolak Universitas',

            // Legacy statuses (for backward compatibility)
            StudentAchievement::STATUS_PENDING, 'Menunggu' => 'Menunggu Verifikasi',
            StudentAchievement::STATUS_APPROVED, 'Disetujui' => 'Selesai Diverifikasi',
            StudentAchievement::STATUS_REJECTED, 'Ditolak' => 'Ditolak',
            StudentAchievement::STATUS_NEED_REVISION, 'Revisi' => 'Perlu Revisi',

            default => $status,
        };
    }

    /**
     * Get status badge color classes
     */
    public static function getBadgeClass(string $status): string
    {
        return match ($status) {
            // Draft
            StudentAchievement::STATUS_DRAFT => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',

            // Submitted / In Review
            StudentAchievement::STATUS_SUBMITTED,
            StudentAchievement::STATUS_FACULTY_REVIEW,
            StudentAchievement::STATUS_UNIVERSITY_REVIEW => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',

            // Faculty Approved (intermediate success)
            StudentAchievement::STATUS_FACULTY_APPROVED => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',

            // Final Approved
            StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            StudentAchievement::STATUS_APPROVED,
            'Disetujui' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',

            // Rejected
            StudentAchievement::STATUS_FACULTY_REJECTED,
            StudentAchievement::STATUS_UNIVERSITY_REJECTED,
            StudentAchievement::STATUS_REJECTED,
            'Ditolak' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',

            // Revision Needed
            StudentAchievement::STATUS_FACULTY_REVISION,
            StudentAchievement::STATUS_NEED_REVISION,
            'Revisi' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',

            // Legacy Pending
            StudentAchievement::STATUS_PENDING,
            'Menunggu' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',

            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Get status description/subtitle
     */
    public static function getDescription(string $status): string
    {
        return match ($status) {
            StudentAchievement::STATUS_DRAFT => 'Belum diajukan',
            StudentAchievement::STATUS_SUBMITTED => 'Menunggu review fakultas',
            StudentAchievement::STATUS_FACULTY_REVIEW => 'Sedang direview oleh fakultas',
            StudentAchievement::STATUS_FACULTY_APPROVED => 'Menunggu review universitas',
            StudentAchievement::STATUS_FACULTY_REJECTED => 'Tidak memenuhi kriteria fakultas',
            StudentAchievement::STATUS_FACULTY_REVISION => 'Perlu perbaikan dokumen',
            StudentAchievement::STATUS_UNIVERSITY_REVIEW => 'Sedang direview oleh universitas',
            StudentAchievement::STATUS_UNIVERSITY_APPROVED => 'Prestasi telah diverifikasi',
            StudentAchievement::STATUS_UNIVERSITY_REJECTED => 'Tidak memenuhi kriteria universitas',
            StudentAchievement::STATUS_PENDING, 'Menunggu' => 'Menunggu verifikasi',
            StudentAchievement::STATUS_APPROVED, 'Disetujui' => 'Telah diverifikasi',
            StudentAchievement::STATUS_REJECTED, 'Ditolak' => 'Tidak memenuhi kriteria',
            StudentAchievement::STATUS_NEED_REVISION, 'Revisi' => 'Perlu perbaikan',
            default => '',
        };
    }

    /**
     * Get status icon
     */
    public static function getIcon(string $status): string
    {
        return match ($status) {
            StudentAchievement::STATUS_DRAFT => 'document',
            StudentAchievement::STATUS_SUBMITTED => 'upload',
            StudentAchievement::STATUS_FACULTY_REVIEW,
            StudentAchievement::STATUS_UNIVERSITY_REVIEW => 'clock',
            StudentAchievement::STATUS_FACULTY_APPROVED => 'check-circle',
            StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            StudentAchievement::STATUS_APPROVED,
            'Disetujui' => 'check-badge',
            StudentAchievement::STATUS_FACULTY_REJECTED,
            StudentAchievement::STATUS_UNIVERSITY_REJECTED,
            StudentAchievement::STATUS_REJECTED,
            'Ditolak' => 'x-circle',
            StudentAchievement::STATUS_FACULTY_REVISION,
            StudentAchievement::STATUS_NEED_REVISION,
            'Revisi' => 'arrow-path',
            StudentAchievement::STATUS_PENDING,
            'Menunggu' => 'clock',
            default => 'question-mark-circle',
        };
    }

    public static function getStageLabel(string $status): string
    {
        return match (true) {
            in_array($status, [
                StudentAchievement::STATUS_PENDING,
                StudentAchievement::STATUS_SUBMITTED,
                StudentAchievement::STATUS_FACULTY_REVIEW,
                StudentAchievement::STATUS_FACULTY_REVISION,
            ], true) => 'Tahap Fakultas',
            in_array($status, [
                StudentAchievement::STATUS_FACULTY_APPROVED,
                StudentAchievement::STATUS_UNIVERSITY_REVIEW,
            ], true) => 'Tahap Universitas',
            in_array($status, [
                StudentAchievement::STATUS_APPROVED,
                StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            ], true) => 'Selesai - Disetujui',
            in_array($status, [
                StudentAchievement::STATUS_REJECTED,
                StudentAchievement::STATUS_FACULTY_REJECTED,
                StudentAchievement::STATUS_UNIVERSITY_REJECTED,
            ], true) => 'Ditolak',
            $status === StudentAchievement::STATUS_DRAFT => 'Draft',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Check if status is approved (any level)
     */
    public static function isApproved(string $status): bool
    {
        return in_array($status, [
            StudentAchievement::STATUS_FACULTY_APPROVED,
            StudentAchievement::STATUS_UNIVERSITY_APPROVED,
            StudentAchievement::STATUS_APPROVED,
            'Disetujui',
        ]);
    }

    /**
     * Check if status is rejected (any level)
     */
    public static function isRejected(string $status): bool
    {
        return in_array($status, [
            StudentAchievement::STATUS_FACULTY_REJECTED,
            StudentAchievement::STATUS_UNIVERSITY_REJECTED,
            StudentAchievement::STATUS_REJECTED,
            'Ditolak',
        ]);
    }

    /**
     * Check if status is pending (any level)
     */
    public static function isPending(string $status): bool
    {
        return in_array($status, [
            StudentAchievement::STATUS_SUBMITTED,
            StudentAchievement::STATUS_FACULTY_REVIEW,
            StudentAchievement::STATUS_FACULTY_APPROVED,
            StudentAchievement::STATUS_UNIVERSITY_REVIEW,
            StudentAchievement::STATUS_PENDING,
            'Menunggu',
        ]);
    }

    /**
     * Check if status needs revision
     */
    public static function needsRevision(string $status): bool
    {
        return in_array($status, [
            StudentAchievement::STATUS_FACULTY_REVISION,
            StudentAchievement::STATUS_NEED_REVISION,
            'Revisi',
        ]);
    }

    /**
     * Get all status options for dropdown
     */
    public static function getAllStatuses(): array
    {
        return [
            // Faculty Stage
            'faculty' => [
                StudentAchievement::STATUS_SUBMITTED => self::getLabel(StudentAchievement::STATUS_SUBMITTED),
                StudentAchievement::STATUS_FACULTY_REVIEW => self::getLabel(StudentAchievement::STATUS_FACULTY_REVIEW),
                StudentAchievement::STATUS_FACULTY_APPROVED => self::getLabel(StudentAchievement::STATUS_FACULTY_APPROVED),
                StudentAchievement::STATUS_FACULTY_REJECTED => self::getLabel(StudentAchievement::STATUS_FACULTY_REJECTED),
                StudentAchievement::STATUS_FACULTY_REVISION => self::getLabel(StudentAchievement::STATUS_FACULTY_REVISION),
            ],
            // University Stage
            'university' => [
                StudentAchievement::STATUS_UNIVERSITY_REVIEW => self::getLabel(StudentAchievement::STATUS_UNIVERSITY_REVIEW),
                StudentAchievement::STATUS_UNIVERSITY_APPROVED => self::getLabel(StudentAchievement::STATUS_UNIVERSITY_APPROVED),
                StudentAchievement::STATUS_UNIVERSITY_REJECTED => self::getLabel(StudentAchievement::STATUS_UNIVERSITY_REJECTED),
            ],
        ];
    }

    public static function getInitialSubmissionState(string $actorType): array
    {
        return match ($actorType) {
            'admin', 'super_admin' => [
                'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
                'validation_stage' => StudentAchievement::STAGE_UNIVERSITY,
                'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
            ],
            default => [
                'validation_status' => StudentAchievement::STATUS_SUBMITTED,
                'validation_stage' => StudentAchievement::STAGE_FACULTY,
                'current_stage' => StudentAchievement::STAGE_FACULTY,
            ],
        };
    }
}
