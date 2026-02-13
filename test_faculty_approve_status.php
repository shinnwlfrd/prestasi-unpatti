<?php

/**
 * Test Script: Verify Faculty Approve Status Change
 * 
 * This script tests if the status properly changes from 'submitted' to 'faculty_approved'
 * after operator approves an achievement.
 * 
 * Run: php artisan tinker < test_faculty_approve_status.php
 */

// Get a test achievement with submitted status
$achievement = \App\Models\StudentAchievement::where('validation_status', 'submitted')
    ->orWhere('validation_status', 'Menunggu')
    ->first();

if (!$achievement) {
    echo "No achievement found with 'submitted' or 'Menunggu' status\n";
    exit;
}

echo "=== BEFORE APPROVE ===\n";
echo "SA ID: {$achievement->sa_id}\n";
echo "Event: {$achievement->event_name}\n";
echo "Status: {$achievement->validation_status}\n";
echo "Stage: {$achievement->current_stage}\n";
echo "Faculty: {$achievement->student->faculty}\n";
echo "\n";

// Get a validator from the same faculty
$validator = \App\Models\User::where('role', 'Validator')
    ->where('faculty', $achievement->student->faculty)
    ->first();

if (!$validator) {
    echo "No validator found for faculty: {$achievement->student->faculty}\n";
    exit;
}

echo "Validator: {$validator->name} ({$validator->faculty})\n";
echo "\n";

// Approve the achievement
try {
    $service = new \App\Services\Validator\FacultyValidationService();
    $result = $service->approve($achievement, $validator, 'Test approval');
    
    echo "=== APPROVE RESULT ===\n";
    echo "Success: " . ($result ? 'YES' : 'NO') . "\n";
    echo "\n";
    
    // Refresh the achievement from database
    $achievement->refresh();
    
    echo "=== AFTER APPROVE ===\n";
    echo "SA ID: {$achievement->sa_id}\n";
    echo "Status: {$achievement->validation_status}\n";
    echo "Stage: {$achievement->current_stage}\n";
    echo "Faculty Validator ID: {$achievement->faculty_validator_id}\n";
    echo "Faculty Validated At: {$achievement->faculty_validated_at}\n";
    echo "\n";
    
    // Check if it appears in faculty pending
    $inFacultyPending = \App\Models\StudentAchievement::facultyPending()
        ->where('sa_id', $achievement->sa_id)
        ->exists();
    
    echo "=== QUERY TESTS ===\n";
    echo "In Faculty Pending: " . ($inFacultyPending ? 'YES (WRONG!)' : 'NO (CORRECT)') . "\n";
    
    // Check if it appears in university pending
    $inUniversityPending = \App\Models\StudentAchievement::universityPending()
        ->where('sa_id', $achievement->sa_id)
        ->exists();
    
    echo "In University Pending: " . ($inUniversityPending ? 'YES (CORRECT)' : 'NO (WRONG!)') . "\n";
    echo "\n";
    
    if ($achievement->validation_status === 'faculty_approved' && !$inFacultyPending && $inUniversityPending) {
        echo "✅ TEST PASSED: Achievement properly moved from faculty to university\n";
    } else {
        echo "❌ TEST FAILED: Achievement status or visibility issue\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
