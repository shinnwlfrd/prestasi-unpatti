<?php

namespace App\Observers;

use App\Models\StudentAchievement;

class StudentAchievementObserver
{
    /**
     * Handle the StudentAchievement "updating" event.
     * Auto-fix stage when status changes to ensure consistency.
     */
    public function updating(StudentAchievement $achievement): void
    {
        // Auto-fix stage when status changes to faculty_approved
        if ($achievement->validation_status === StudentAchievement::STATUS_FACULTY_APPROVED 
            && $achievement->current_stage !== StudentAchievement::STAGE_UNIVERSITY) {
            $achievement->current_stage = StudentAchievement::STAGE_UNIVERSITY;
        }
        
        // Auto-fix stage when status changes to university_approved
        if ($achievement->validation_status === StudentAchievement::STATUS_UNIVERSITY_APPROVED 
            && $achievement->current_stage !== StudentAchievement::STAGE_COMPLETED) {
            $achievement->current_stage = StudentAchievement::STAGE_COMPLETED;
        }
        
        // Auto-fix stage when status changes to faculty_rejected
        if ($achievement->validation_status === StudentAchievement::STATUS_FACULTY_REJECTED 
            && $achievement->current_stage !== StudentAchievement::STAGE_COMPLETED) {
            $achievement->current_stage = StudentAchievement::STAGE_COMPLETED;
        }
        
        // Auto-fix stage when status changes to university_rejected
        if ($achievement->validation_status === StudentAchievement::STATUS_UNIVERSITY_REJECTED 
            && $achievement->current_stage !== StudentAchievement::STAGE_COMPLETED) {
            $achievement->current_stage = StudentAchievement::STAGE_COMPLETED;
        }
    }
}
