<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AcademicPeriod;
use App\Models\StudentAchievement;

echo "\n";
echo "=================================================================\n";
echo "📊 ACHIEVEMENT DISTRIBUTION VERIFICATION\n";
echo "=================================================================\n\n";

$periods = AcademicPeriod::orderBy('start_date', 'desc')->get();

echo "Total Periods: " . $periods->count() . "\n";
echo "Total Achievements: " . StudentAchievement::count() . "\n\n";

echo "Distribution by Period:\n";
echo "-----------------------------------------------------------------\n";

foreach ($periods as $period) {
    $count = StudentAchievement::where('academic_period_id', $period->id)->count();
    $percentage = StudentAchievement::count() > 0 
        ? round(($count / StudentAchievement::count()) * 100, 1) 
        : 0;
    
    $status = $period->is_active ? '✓ ACTIVE' : '  Inactive';
    
    echo sprintf(
        "%-25s [%s] : %4d achievements (%5.1f%%)\n",
        $period->name,
        $status,
        $count,
        $percentage
    );
}

echo "\n";
echo "Status Distribution (All Periods):\n";
echo "-----------------------------------------------------------------\n";

$statuses = [
    'Menunggu' => StudentAchievement::where('validation_status', 'Menunggu')->count(),
    'faculty_approved' => StudentAchievement::where('validation_status', 'faculty_approved')->count(),
    'faculty_rejected' => StudentAchievement::where('validation_status', 'faculty_rejected')->count(),
    'university_approved' => StudentAchievement::where('validation_status', 'university_approved')->count(),
    'university_rejected' => StudentAchievement::where('validation_status', 'university_rejected')->count(),
];

foreach ($statuses as $status => $count) {
    echo sprintf("%-25s : %4d\n", $status, $count);
}

echo "\n";
echo "Level Distribution (All Periods):\n";
echo "-----------------------------------------------------------------\n";

$levels = [
    'Universitas' => StudentAchievement::where('level', 'Universitas')->count(),
    'Nasional' => StudentAchievement::where('level', 'Nasional')->count(),
    'Internasional' => StudentAchievement::where('level', 'Internasional')->count(),
];

foreach ($levels as $level => $count) {
    echo sprintf("%-25s : %4d\n", $level, $count);
}

echo "\n";
echo "Sample Achievements from Each Period:\n";
echo "-----------------------------------------------------------------\n";

foreach ($periods as $period) {
    $sample = StudentAchievement::where('academic_period_id', $period->id)
        ->with('student')
        ->first();
    
    if ($sample) {
        echo "\n{$period->name}:\n";
        echo "  - Event: {$sample->event_name}\n";
        echo "  - Date: {$sample->event_date}\n";
        echo "  - Student: {$sample->student->name}\n";
        echo "  - Status: {$sample->validation_status}\n";
    } else {
        echo "\n{$period->name}: No achievements\n";
    }
}

echo "\n";
echo "=================================================================\n";
echo "✅ Verification Complete!\n";
echo "=================================================================\n\n";
