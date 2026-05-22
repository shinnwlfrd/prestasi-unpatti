<?php

namespace App\Policies;

use App\Models\AchievementDocument;
use App\Models\StudentAchievement;
use App\Models\User;

class AchievementDocumentPolicy
{
    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, AchievementDocument $document): bool
    {
        $achievement = $document->studentAchievement;
        if (! $achievement) {
            return false;
        }

        // Admin can always view
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Student can view their own document
        if (session('auth_role') === 'student' && $achievement->student_id === session('student_id')) {
            return true;
        }

        // Operator can view if it belongs to their faculty scope
        if ($user->isOperator()) {
            $level = session('operator_level');
            $student = $achievement->student;

            if ($level === 'university') {
                return true;
            }

            if ($level === 'faculty' && $student->faculty_id == session('operator_faculty_id')) {
                return true;
            }

            // Add department/prodi level check if needed
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AchievementDocument $document): bool
    {
        $achievement = $document->studentAchievement;
        if (! $achievement) {
            return false;
        }

        // Only the student owner can update/replace the document
        // And only if the achievement is still in 'draft' or 'revision' status
        if (session('auth_role') === 'student' && $achievement->student_id === session('student_id')) {
            $status = $achievement->validation_status;

            return in_array($status, [StudentAchievement::STATUS_DRAFT, StudentAchievement::STATUS_FACULTY_REVISION]);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AchievementDocument $document): bool
    {
        $achievement = $document->studentAchievement;
        if (! $achievement) {
            return false;
        }

        // Similar logic to update
        if (session('auth_role') === 'student' && $achievement->student_id === session('student_id')) {
            $status = $achievement->validation_status;

            return in_array($status, [StudentAchievement::STATUS_DRAFT, StudentAchievement::STATUS_FACULTY_REVISION]);
        }

        return false;
    }
}
