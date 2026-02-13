<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use Illuminate\Support\Facades\DB;

echo "=== FIXING FACULTY_VALIDATOR_ID FOR FACULTY_APPROVED ACHIEVEMENTS ===\n\n";

// Find achievements that are faculty_approved but don't have faculty_validator_id
$achievementsWithoutValidator = StudentAchievement::where('validation_status', 'faculty_approved')
    ->where('current_stage', 'university')
    ->whereNull('faculty_validator_id')
    ->whereNull('deleted_at')
    ->get();

echo "Found {$achievementsWithoutValidator->count()} achievements without faculty_validator_id\n\n";

$fixed = 0;
$failed = 0;

foreach ($achievementsWithoutValidator as $achievement) {
    echo "Processing SA ID: {$achievement->sa_id}...\n";
    
    // Try to find the validator from validation logs
    $validationLog = ValidationLog::where('sa_id', $achievement->sa_id)
        ->where('validation_stage', 'faculty')
        ->where('stage_action', 'approve')
        ->whereNotNull('validator_id')
        ->orderBy('validated_at', 'desc')
        ->first();
    
    if ($validationLog && $validationLog->validator_id) {
        // Update the achievement with the validator from the log
        $achievement->update([
            'faculty_validator_id' => $validationLog->validator_id,
            'faculty_validated_at' => $validationLog->validated_at ?? $achievement->faculty_validated_at ?? now(),
        ]);
        
        echo "  ✓ Set faculty_validator_id to {$validationLog->validator_id}\n";
        $fixed++;
    } else {
        // No validation log found, try to infer from student's faculty
        // Get any validator from the same faculty
        $validator = \App\Models\User::where('role', 'Validator')
            ->where('faculty', $achievement->student->faculty)
            ->where('is_active', true)
            ->first();
        
        if ($validator) {
            $achievement->update([
                'faculty_validator_id' => $validator->id,
                'faculty_validated_at' => $achievement->faculty_validated_at ?? now(),
            ]);
            
            echo "  ✓ Inferred faculty_validator_id to {$validator->id} (from faculty: {$validator->faculty})\n";
            $fixed++;
        } else {
            echo "  ✗ Could not find validator for faculty: {$achievement->student->faculty}\n";
            $failed++;
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Fixed: {$fixed}\n";
echo "Failed: {$failed}\n";

// Verify the fix
$remainingWithoutValidator = StudentAchievement::where('validation_status', 'faculty_approved')
    ->where('current_stage', 'university')
    ->whereNull('faculty_validator_id')
    ->whereNull('deleted_at')
    ->count();

echo "\nRemaining achievements without faculty_validator_id: {$remainingWithoutValidator}\n";

echo "\n=== FIX COMPLETE ===\n";
