<?php

namespace App\Console\Commands;

use App\Models\ExecutiveSetting;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Notifications\SlaBreachDetected;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckSlaBreaches extends Command
{
    protected $signature = 'sla:check-breaches';

    protected $description = 'Check for achievements that exceed the validation SLA limit and notify validators/admins';

    public function handle()
    {
        $this->info('Checking SLA breaches...');

        // Get SLA threshold days from settings or default to 7
        $slaDays = (int) ExecutiveSetting::getValue('sla_validation_days', 7);
        $this->info("SLA limit is set to: {$slaDays} days");

        $now = now();
        $cutoffDate = $now->subDays($slaDays);

        // 1. Check Faculty Stage
        $facultyAchievements = StudentAchievement::with('student')
            ->whereIn('validation_status', [
                StudentAchievement::STATUS_SUBMITTED,
                StudentAchievement::STATUS_FACULTY_REVIEW,
                'Menunggu', // Backward compatibility just in case
            ])
            ->where('submitted_at', '<', $cutoffDate)
            ->get();

        $this->info('Found '.$facultyAchievements->count().' achievements breaching SLA at Faculty stage.');

        foreach ($facultyAchievements as $achievement) {
            $facultyId = $achievement->student?->faculty_id;

            // Check if submitted_at is null, skip or use created_at
            $submittedAtDate = $achievement->submitted_at ?? $achievement->created_at;
            $submittedAt = Carbon::parse($submittedAtDate);
            $daysOverdue = (int) $submittedAt->diffInDays(now());

            if ($facultyId) {
                // Find operators for this faculty
                $operators = User::whereHas('activeRoles', function ($q) use ($facultyId) {
                    $q->where('role', 'operator')
                        ->where('level', 'faculty')
                        ->where('faculty_id', $facultyId);
                })->get();

                if ($operators->isEmpty()) {
                    $this->warn("No operators found for faculty ID: {$facultyId}. Notifying university admins instead.");
                    // Fallback to notify university admins/operators
                    $this->notifyUniversityAdmins($achievement, $daysOverdue, 'faculty');
                } else {
                    foreach ($operators as $operator) {
                        $operator->notify(new SlaBreachDetected($achievement, $daysOverdue, 'faculty'));
                    }
                }
            } else {
                $this->warn("Achievement sa_id: {$achievement->sa_id} has no associated faculty. Notifying university admins instead.");
                $this->notifyUniversityAdmins($achievement, $daysOverdue, 'faculty');
            }
        }

        // 2. Check University Stage
        $universityAchievements = StudentAchievement::with('student')
            ->whereIn('validation_status', [
                StudentAchievement::STATUS_FACULTY_APPROVED,
                StudentAchievement::STATUS_UNIVERSITY_REVIEW,
            ])
            ->where('faculty_validated_at', '<', $cutoffDate)
            ->get();

        $this->info('Found '.$universityAchievements->count().' achievements breaching SLA at University stage.');

        foreach ($universityAchievements as $achievement) {
            $validatedAtDate = $achievement->faculty_validated_at ?? $achievement->updated_at;
            $validatedAt = Carbon::parse($validatedAtDate);
            $daysOverdue = (int) $validatedAt->diffInDays(now());

            $this->notifyUniversityAdmins($achievement, $daysOverdue, 'university');
        }

        $this->info('SLA breach check completed.');

        return 0;
    }

    protected function notifyUniversityAdmins(StudentAchievement $achievement, int $daysOverdue, string $stage): void
    {
        $admins = User::whereHas('activeRoles', function ($q) {
            $q->whereIn('role', ['admin', 'super_admin'])
                ->orWhere(function ($sub) {
                    $sub->where('role', 'operator')
                        ->where('level', 'university');
                });
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new SlaBreachDetected($achievement, $daysOverdue, $stage));
        }
    }
}
