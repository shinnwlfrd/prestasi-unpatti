<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentAchievement;
use App\Models\User;

echo "=== CHECKING UNIVERSITY PENDING ACHIEVEMENTS ===\n\n";

// 1. Check total faculty_approved with university stage
$facultyApprovedUniversityStage = StudentAchievement::where('validation_status', 'faculty_approved')
    ->where('current_stage', 'university')
    ->whereNull('deleted_at')
    ->count();

echo "1. Faculty Approved + University Stage: {$facultyApprovedUniversityStage}\n";

// 2. Check using universityPending scope
$universityPendingCount = StudentAchievement::universityPending()->count();
echo "2. Using universityPending() scope: {$universityPendingCount}\n";

// 3. Check the actual query that controller uses
$controllerQuery = StudentAchievement::with([
    'student',
    'achievement.category',
    'facultyValidator',
    'documents'
])
    ->whereNull('deleted_at')
    ->universityPending()
    ->count();

echo "3. Controller query (with relationships): {$controllerQuery}\n";

// 4. Get sample records
echo "\n=== SAMPLE RECORDS (first 5) ===\n";
$samples = StudentAchievement::universityPending()
    ->with(['student', 'facultyValidator'])
    ->limit(5)
    ->get();

foreach ($samples as $achievement) {
    echo "\nSA ID: {$achievement->sa_id}\n";
    echo "  Student: {$achievement->student->name} ({$achievement->student_id})\n";
    echo "  Event: {$achievement->event_name}\n";
    echo "  Status: {$achievement->validation_status}\n";
    echo "  Stage: {$achievement->current_stage}\n";
    echo "  Faculty Validator: " . ($achievement->facultyValidator ? $achievement->facultyValidator->name : 'None') . "\n";
    echo "  Faculty Validated At: " . ($achievement->faculty_validated_at ? $achievement->faculty_validated_at->format('Y-m-d H:i:s') : 'None') . "\n";
}

// 5. Check if there are any with faculty filter issues
echo "\n\n=== CHECKING FACULTY VALIDATOR RELATIONSHIP ===\n";
$withoutFacultyValidator = StudentAchievement::universityPending()
    ->whereNull('faculty_validator_id')
    ->count();
echo "Achievements without faculty_validator_id: {$withoutFacultyValidator}\n";

$withFacultyValidator = StudentAchievement::universityPending()
    ->whereNotNull('faculty_validator_id')
    ->count();
echo "Achievements with faculty_validator_id: {$withFacultyValidator}\n";

// 6. Check faculties list
echo "\n=== FACULTIES WITH PENDING ACHIEVEMENTS ===\n";
$faculties = User::whereHas('validatedAchievements', function ($q) {
    $q->whereIn('validation_status', [
        StudentAchievement::STATUS_FACULTY_APPROVED,
        StudentAchievement::STATUS_UNIVERSITY_REVIEW,
    ]);
})
    ->select('faculty')
    ->distinct()
    ->whereNotNull('faculty')
    ->orderBy('faculty')
    ->pluck('faculty');

echo "Faculties: " . $faculties->implode(', ') . "\n";
echo "Total faculties: " . $faculties->count() . "\n";

// 7. Check statistics
echo "\n=== STATISTICS ===\n";
$stats = app(\App\Services\Admin\UniversityValidationService::class)->getStatistics();
echo "Pending: {$stats['pending']}\n";
echo "Approved This Month: {$stats['approved_this_month']}\n";
echo "Total Approved: {$stats['total_approved']}\n";
echo "Avg Review Time: {$stats['avg_review_time_hours']}h\n";

echo "\n=== CHECK COMPLETE ===\n";
