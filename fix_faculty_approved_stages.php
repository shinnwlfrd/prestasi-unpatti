<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Faculty Approved Stages ===\n\n";

// Find all faculty_approved achievements with wrong stage
$achievements = \App\Models\StudentAchievement::where('validation_status', 'faculty_approved')
    ->where('current_stage', '!=', 'university')
    ->get();

echo "Found {$achievements->count()} achievements with wrong stage\n\n";

if ($achievements->count() > 0) {
    echo "Updating stages...\n";
    
    $updated = 0;
    foreach ($achievements as $achievement) {
        try {
            $achievement->update([
                'current_stage' => \App\Models\StudentAchievement::STAGE_UNIVERSITY
            ]);
            $updated++;
            echo ".";
        } catch (\Exception $e) {
            echo "X";
        }
    }
    
    echo "\n\n";
    echo "✅ Updated {$updated} achievements\n";
    
    // Verify
    $remaining = \App\Models\StudentAchievement::where('validation_status', 'faculty_approved')
        ->where('current_stage', '!=', 'university')
        ->count();
    
    echo "Remaining with wrong stage: {$remaining}\n";
    
    if ($remaining === 0) {
        echo "\n🎉 All faculty_approved achievements now have correct stage!\n";
    }
} else {
    echo "✅ All faculty_approved achievements already have correct stage\n";
}

echo "\n=== Verification ===\n";
$total = \App\Models\StudentAchievement::where('validation_status', 'faculty_approved')->count();
$correct = \App\Models\StudentAchievement::where('validation_status', 'faculty_approved')
    ->where('current_stage', 'university')
    ->count();

echo "Total faculty_approved: {$total}\n";
echo "With correct stage (university): {$correct}\n";
echo "Percentage: " . ($total > 0 ? round(($correct / $total) * 100, 1) : 0) . "%\n";
