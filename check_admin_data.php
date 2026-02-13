<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Faculty Approved Achievements ===\n\n";

// Check if there are any faculty_approved achievements
$facultyApproved = \App\Models\StudentAchievement::where('validation_status', 'faculty_approved')->get();

echo "Total faculty_approved: " . $facultyApproved->count() . "\n\n";

if ($facultyApproved->count() > 0) {
    foreach ($facultyApproved as $achievement) {
        echo "SA ID: {$achievement->sa_id}\n";
        echo "Event: {$achievement->event_name}\n";
        echo "Status: {$achievement->validation_status}\n";
        echo "Stage: {$achievement->current_stage}\n";
        echo "Faculty: {$achievement->student->faculty}\n";
        echo "---\n";
    }
    
    echo "\n=== Testing universityPending Scope ===\n";
    $universityPending = \App\Models\StudentAchievement::universityPending()->get();
    echo "Total in universityPending scope: " . $universityPending->count() . "\n\n";
    
    if ($universityPending->count() > 0) {
        echo "Achievements in universityPending:\n";
        foreach ($universityPending as $achievement) {
            echo "- SA ID: {$achievement->sa_id}, Status: {$achievement->validation_status}\n";
        }
    }
} else {
    echo "No faculty_approved achievements found.\n";
    echo "\nChecking other statuses:\n";
    echo "- submitted: " . \App\Models\StudentAchievement::where('validation_status', 'submitted')->count() . "\n";
    echo "- faculty_review: " . \App\Models\StudentAchievement::where('validation_status', 'faculty_review')->count() . "\n";
    echo "- university_review: " . \App\Models\StudentAchievement::where('validation_status', 'university_review')->count() . "\n";
}

echo "\n=== Checking STATUS Constants ===\n";
echo "STATUS_FACULTY_APPROVED = '" . \App\Models\StudentAchievement::STATUS_FACULTY_APPROVED . "'\n";
echo "STATUS_UNIVERSITY_REVIEW = '" . \App\Models\StudentAchievement::STATUS_UNIVERSITY_REVIEW . "'\n";
