<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== ANALISIS MENDALAM: KENAPA PRESTASI TIDAK MUNCUL DI ADMIN ===\n\n";

// 1. Cek data mentah di database
echo "1. CEK DATA MENTAH DI DATABASE\n";
echo "   ================================\n";
$rawQuery = "SELECT 
    sa_id,
    student_id,
    event_name,
    validation_status,
    current_stage,
    faculty_validator_id,
    faculty_validated_at,
    deleted_at
FROM student_achievements 
WHERE validation_status = 'faculty_approved' 
AND current_stage = 'university'
LIMIT 5";

$rawResults = DB::select($rawQuery);
echo "   Query: SELECT * FROM student_achievements WHERE validation_status='faculty_approved' AND current_stage='university'\n";
echo "   Hasil: " . count($rawResults) . " records\n\n";

foreach ($rawResults as $row) {
    echo "   SA ID: {$row->sa_id}\n";
    echo "     Status: {$row->validation_status}\n";
    echo "     Stage: {$row->current_stage}\n";
    echo "     Faculty Validator ID: " . ($row->faculty_validator_id ?? 'NULL') . "\n";
    echo "     Deleted At: " . ($row->deleted_at ?? 'NULL') . "\n";
    echo "\n";
}

// 2. Cek scope universityPending
echo "2. CEK SCOPE universityPending()\n";
echo "   ================================\n";
$scopeCount = StudentAchievement::universityPending()->count();
echo "   StudentAchievement::universityPending()->count() = {$scopeCount}\n\n";

// 3. Cek query controller EXACT
echo "3. CEK QUERY CONTROLLER (EXACT SAMA)\n";
echo "   ================================\n";
$controllerQuery = StudentAchievement::with([
    'student',
    'achievement.category',
    'facultyValidator',
    'documents'
])
    ->whereNull('deleted_at')
    ->universityPending();

echo "   Query SQL:\n";
echo "   " . $controllerQuery->toSql() . "\n\n";

$controllerResults = $controllerQuery->get();
echo "   Hasil: {$controllerResults->count()} records\n\n";

// 4. Cek apakah ada filter yang aktif
echo "4. CEK KEMUNGKINAN FILTER TERSEMBUNYI\n";
echo "   ================================\n";

// Cek dengan berbagai kondisi filter
$filters = [
    'no_filter' => StudentAchievement::universityPending()->count(),
    'with_whereNull_deleted' => StudentAchievement::whereNull('deleted_at')->universityPending()->count(),
    'with_student' => StudentAchievement::universityPending()->whereHas('student')->count(),
    'with_facultyValidator' => StudentAchievement::universityPending()->whereHas('facultyValidator')->count(),
    'with_achievement' => StudentAchievement::universityPending()->whereHas('achievement')->count(),
];

foreach ($filters as $filterName => $count) {
    echo "   {$filterName}: {$count} records\n";
}

// 5. Cek apakah ada masalah dengan eager loading
echo "\n5. CEK EAGER LOADING\n";
echo "   ================================\n";
try {
    $withEagerLoad = StudentAchievement::with([
        'student',
        'achievement.category',
        'facultyValidator',
        'documents'
    ])->universityPending()->limit(1)->first();
    
    if ($withEagerLoad) {
        echo "   ✓ Eager loading berhasil\n";
        echo "   Student: " . ($withEagerLoad->student ? $withEagerLoad->student->name : 'NULL') . "\n";
        echo "   Achievement: " . ($withEagerLoad->achievement ? 'OK' : 'NULL') . "\n";
        echo "   Faculty Validator: " . ($withEagerLoad->facultyValidator ? $withEagerLoad->facultyValidator->name : 'NULL') . "\n";
    } else {
        echo "   ✗ Tidak ada data\n";
    }
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 6. Cek route dan middleware
echo "\n6. CEK ROUTE DAN MIDDLEWARE\n";
echo "   ================================\n";
$routes = app('router')->getRoutes();
$adminUniversityRoute = null;

foreach ($routes as $route) {
    if ($route->getName() === 'admin.university.index') {
        $adminUniversityRoute = $route;
        break;
    }
}

if ($adminUniversityRoute) {
    echo "   Route: " . $adminUniversityRoute->uri() . "\n";
    echo "   Method: " . implode(', ', $adminUniversityRoute->methods()) . "\n";
    echo "   Controller: " . $adminUniversityRoute->getActionName() . "\n";
    echo "   Middleware: " . implode(', ', $adminUniversityRoute->middleware()) . "\n";
} else {
    echo "   ✗ Route 'admin.university.index' tidak ditemukan!\n";
}

// 7. Cek apakah ada session atau cache yang mengganggu
echo "\n7. CEK POTENSI MASALAH CACHE/SESSION\n";
echo "   ================================\n";
echo "   Cache driver: " . config('cache.default') . "\n";
echo "   Session driver: " . config('session.driver') . "\n";

// 8. Simulasi request ke controller
echo "\n8. SIMULASI REQUEST KE CONTROLLER\n";
echo "   ================================\n";
try {
    $request = new \Illuminate\Http\Request();
    $controller = app(\App\Http\Controllers\Admin\UniversityValidationController::class);
    
    // Tidak bisa langsung call index karena butuh response, tapi kita bisa cek service
    $service = app(\App\Services\Admin\UniversityValidationService::class);
    $stats = $service->getStatistics();
    
    echo "   Statistics dari service:\n";
    echo "     - Pending: {$stats['pending']}\n";
    echo "     - Approved This Month: {$stats['approved_this_month']}\n";
    echo "     - Total Approved: {$stats['total_approved']}\n";
    echo "     - SK Issued: {$stats['sk_issued']}\n";
    
    if ($stats['pending'] > 0) {
        echo "   ✓ Service mengembalikan data pending\n";
    } else {
        echo "   ✗ Service mengembalikan 0 pending!\n";
    }
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 9. Cek apakah view file ada
echo "\n9. CEK VIEW FILE\n";
echo "   ================================\n";
$viewPath = resource_path('views/admin/university/index.blade.php');
if (file_exists($viewPath)) {
    echo "   ✓ View file exists: {$viewPath}\n";
    echo "   File size: " . filesize($viewPath) . " bytes\n";
} else {
    echo "   ✗ View file NOT FOUND: {$viewPath}\n";
}

// 10. Cek apakah ada error di log
echo "\n10. CEK LARAVEL LOG (5 baris terakhir)\n";
echo "   ================================\n";
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $logLines = file($logPath);
    $lastLines = array_slice($logLines, -5);
    foreach ($lastLines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "   Log file tidak ditemukan\n";
}

echo "\n=== KESIMPULAN ANALISIS ===\n";
echo "\nBerdasarkan analisis di atas:\n";
echo "1. Jika data di database ada (105 records) tapi tidak muncul di browser:\n";
echo "   → Kemungkinan masalah di VIEW atau JAVASCRIPT\n";
echo "   → Atau ada filter default yang aktif\n";
echo "   → Atau masalah cache browser\n\n";

echo "2. Jika query controller mengembalikan 0 records:\n";
echo "   → Kemungkinan masalah di SCOPE atau QUERY\n";
echo "   → Atau masalah di RELATIONSHIP\n\n";

echo "3. Jika statistics menunjukkan pending > 0 tapi table kosong:\n";
echo "   → Kemungkinan masalah di VIEW RENDERING\n";
echo "   → Atau masalah di PAGINATION\n\n";

echo "Silakan cek output di atas untuk menemukan masalahnya.\n";
