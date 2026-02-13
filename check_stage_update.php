<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Stage Update ===\n\n";

// Get one faculty_approved achievement with wrong stage
$achievement = \App\Models\StudentAchievement::where('validation_status', 'faculty_approved')
    ->where('current_stage', 'faculty')
    ->first();

if (!$achievement) {
    echo "No achievement found with faculty_approved status and faculty stage\n";
    exit;
}

echo "BEFORE UPDATE:\n";
echo "SA ID: {$achievement->sa_id}\n";
echo "Status: {$achievement->validation_status}\n";
echo "Stage: {$achievement->current_stage}\n";
echo "\n";

// Try to update the stage
try {
    $achievement->update([
        'current_stage' => \App\Models\StudentAchievement::STAGE_UNIVERSITY
    ]);
    
    $achievement->refresh();
    
    echo "AFTER UPDATE:\n";
    echo "SA ID: {$achievement->sa_id}\n";
    echo "Status: {$achievement->validation_status}\n";
    echo "Stage: {$achievement->current_stage}\n";
    echo "\n";
    
    if ($achievement->current_stage === 'university') {
        echo "✅ Stage update SUCCESSFUL\n";
    } else {
        echo "❌ Stage update FAILED - still: {$achievement->current_stage}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Checking Model Fillable ===\n";
$fillable = (new \App\Models\StudentAchievement())->getFillable();
echo "Is 'current_stage' fillable? " . (in_array('current_stage', $fillable) ? 'YES' : 'NO') . "\n";
if (!in_array('current_stage', $fillable)) {
    echo "Available fillable fields:\n";
    print_r($fillable);
}
