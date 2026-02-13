<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentAchievement;
use App\Models\User;

echo "=== CHECKING FACULTIES LIST ===\n\n";

// 1. Check all validators who have validated achievements
$validatorsWithAchievements = User::whereHas('validatedAchievements')
    ->select('id', 'name', 'faculty')
    ->get();

echo "1. All validators with validated achievements:\n";
foreach ($validatorsWithAchievements as $validator) {
    $count = $validator->validatedAchievements()->count();
    echo "  - {$validator->name} ({$validator->faculty}): {$count} achievements\n";
}

// 2. Check validators with faculty_approved achievements
$validatorsWithFacultyApproved = User::whereHas('validatedAchievements', function ($q) {
    $q->whereIn('validation_status', [
        StudentAchievement::STATUS_FACULTY_APPROVED,
        StudentAchievement::STATUS_UNIVERSITY_REVIEW,
    ]);
})
    ->select('id', 'name', 'faculty')
    ->get();

echo "\n2. Validators with faculty_approved achievements:\n";
foreach ($validatorsWithFacultyApproved as $validator) {
    $count = $validator->validatedAchievements()
        ->whereIn('validation_status', [
            StudentAchievement::STATUS_FACULTY_APPROVED,
            StudentAchievement::STATUS_UNIVERSITY_REVIEW,
        ])
        ->count();
    echo "  - {$validator->name} ({$validator->faculty}): {$count} achievements\n";
}

// 3. Check the exact query from controller
$faculties = User::whereHas('facultyValidatedAchievements', function ($q) {
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

echo "\n3. Faculties from controller query (using facultyValidatedAchievements):\n";
if ($faculties->isEmpty()) {
    echo "  (empty)\n";
} else {
    foreach ($faculties as $faculty) {
        echo "  - {$faculty}\n";
    }
}

// 4. Check the User model for facultyValidatedAchievements relationship
echo "\n4. Checking User model relationships:\n";
$user = User::find(9); // Operator Fakultas Hukum
if ($user) {
    echo "  User: {$user->name}\n";
    echo "  Faculty: {$user->faculty}\n";
    
    // Check if relationship exists
    try {
        $count = $user->facultyValidatedAchievements()->count();
        echo "  facultyValidatedAchievements count: {$count}\n";
        
        $facultyApprovedCount = $user->facultyValidatedAchievements()
            ->whereIn('validation_status', [
                StudentAchievement::STATUS_FACULTY_APPROVED,
                StudentAchievement::STATUS_UNIVERSITY_REVIEW,
            ])
            ->count();
        echo "  faculty_approved achievements: {$facultyApprovedCount}\n";
    } catch (\Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== CHECK COMPLETE ===\n";
