<?php

namespace App\Policies;

use App\Models\AchievementDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AchievementDocumentPolicy
{
    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, AchievementDocument $document): bool
    {
        // Admin can always view
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Student can view their own document
        if (session('auth_role') === 'student' && $document->achievement->student_id === session('student_id')) {
            return true;
        }

        // Operator can view if it belongs to their faculty scope
        if ($user->isOperator()) {
            $level = session('operator_level');
            $achievement = $document->achievement;
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
        // Only the student owner can update/replace the document
        // And only if the achievement is still in 'draft' or 'revision' status
        if (session('auth_role') === 'student' && $document->achievement->student_id === session('student_id')) {
            $status = $document->achievement->validation_status;
            return in_array($status, [\App\Models\StudentAchievement::STATUS_DRAFT, \App\Models\StudentAchievement::STATUS_FACULTY_REVISION]);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AchievementDocument $document): bool
    {
        // Similar logic to update
        if (session('auth_role') === 'student' && $document->achievement->student_id === session('student_id')) {
            $status = $document->achievement->validation_status;
            return in_array($status, [\App\Models\StudentAchievement::STATUS_DRAFT, \App\Models\StudentAchievement::STATUS_FACULTY_REVISION]);
        }

        return false;
    }
}
