<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\StudentAchievement;
use App\Models\AchievementCategory;
use App\Models\User;

echo "=== TEST DATA YANG DIKIRIM KE VIEW ===\n\n";

// Simulasi exact query dari controller
echo "1. SIMULASI QUERY CONTROLLER\n";
echo "   ================================\n";

$filters = [
    'search' => null,
    'faculty' => null,
    'level' => null,
    'category' => null,
    'date_from' => null,
    'date_to' => null,
    'sort_date' => 'oldest',
];

$achievements = StudentAchievement::with([
    'student',
    'achievement.category',
    'facultyValidator',
    'documents'
])
    ->whereNull('deleted_at')
    ->universityPending()
    ->when($filters['search'], function ($q) use ($filters) {
        $q->where(function ($query) use ($filters) {
            $query->where('event_name', 'like', "%{$filters['search']}%")
                ->orWhere('student_id', 'like', "%{$filters['search']}%")
                ->orWhereHas('student', function ($sq) use ($filters) {
                    $sq->where('name', 'like', "%{$filters['search']}%");
                });
        });
    })
    ->when($filters['faculty'], function ($q) use ($filters) {
        $q->whereHas('facultyValidator', function ($sq) use ($filters) {
            $sq->where('faculty', $filters['faculty']);
        });
    })
    ->when($filters['level'], fn($q) => $q->where('level', $filters['level']))
    ->when($filters['category'], function ($q) use ($filters) {
        $q->whereHas('achievement', fn($sq) => $sq->where('category_id', $filters['category']));
    })
    ->when($filters['date_from'], fn($q) => $q->whereDate('faculty_validated_at', '>=', $filters['date_from']))
    ->when($filters['date_to'], fn($q) => $q->whereDate('faculty_validated_at', '<=', $filters['date_to']))
    ->when($filters['sort_date'] === 'newest', function ($q) {
        $q->orderBy('faculty_validated_at', 'desc');
    }, function ($q) {
        $q->orderBy('faculty_validated_at', 'asc');
    })
    ->paginate(20);

echo "   Total achievements: {$achievements->total()}\n";
echo "   Per page: {$achievements->perPage()}\n";
echo "   Current page: {$achievements->currentPage()}\n";
echo "   Last page: {$achievements->lastPage()}\n";
echo "   Items in current page: {$achievements->count()}\n\n";

// Cek categories
$categories = AchievementCategory::where('is_active', true)->orderBy('order')->get();
echo "2. CATEGORIES\n";
echo "   ================================\n";
echo "   Total categories: {$categories->count()}\n\n";

// Cek faculties
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

echo "3. FACULTIES\n";
echo "   ================================\n";
echo "   Total faculties: {$faculties->count()}\n";
foreach ($faculties as $faculty) {
    echo "   - {$faculty}\n";
}

// Cek statistics
$service = app(\App\Services\Admin\UniversityValidationService::class);
$statistics = $service->getStatistics();

echo "\n4. STATISTICS\n";
echo "   ================================\n";
echo "   Pending: {$statistics['pending']}\n";
echo "   Approved This Month: {$statistics['approved_this_month']}\n";
echo "   Total Approved: {$statistics['total_approved']}\n";
echo "   SK Issued: {$statistics['sk_issued']}\n";
echo "   Avg Review Time: {$statistics['avg_review_time_hours']}h\n";

// Tampilkan sample data
echo "\n5. SAMPLE DATA (3 pertama)\n";
echo "   ================================\n";
$sampleData = $achievements->take(3);
foreach ($sampleData as $achievement) {
    echo "   SA ID: {$achievement->sa_id}\n";
    echo "     Student: " . ($achievement->student ? $achievement->student->name : 'NULL') . "\n";
    echo "     Event: {$achievement->event_name}\n";
    echo "     Level: {$achievement->level}\n";
    echo "     Faculty Validator: " . ($achievement->facultyValidator ? $achievement->facultyValidator->name : 'NULL') . "\n";
    echo "     Faculty: " . ($achievement->facultyValidator ? $achievement->facultyValidator->faculty : 'NULL') . "\n";
    echo "     Validated At: " . ($achievement->faculty_validated_at ? $achievement->faculty_validated_at->format('d M Y H:i') : 'NULL') . "\n";
    echo "\n";
}

echo "=== KESIMPULAN ===\n\n";

if ($achievements->total() > 0) {
    echo "✓ DATA LENGKAP DAN SIAP DITAMPILKAN\n\n";
    echo "Jika data ini tidak muncul di browser, kemungkinan masalahnya:\n\n";
    echo "1. CACHE BROWSER\n";
    echo "   Solusi: Tekan Ctrl+Shift+Delete, clear cache, lalu hard refresh (Ctrl+Shift+R)\n\n";
    
    echo "2. JAVASCRIPT ERROR\n";
    echo "   Solusi: Buka browser console (F12), cek apakah ada error JavaScript\n\n";
    
    echo "3. VIEW RENDERING ISSUE\n";
    echo "   Solusi: Cek file resources/views/admin/university/index.blade.php\n";
    echo "   Pastikan loop @forelse berfungsi dengan benar\n\n";
    
    echo "4. CSS HIDING ELEMENTS\n";
    echo "   Solusi: Inspect element di browser, cek apakah table row ada tapi hidden\n\n";
    
    echo "5. FILTER DEFAULT AKTIF\n";
    echo "   Solusi: Cek apakah ada filter yang aktif di URL atau session\n";
    echo "   URL seharusnya: /admin/university/pending (tanpa parameter)\n\n";
    
} else {
    echo "✗ TIDAK ADA DATA\n\n";
    echo "Ini aneh karena sebelumnya ada 105 records.\n";
    echo "Kemungkinan ada yang salah dengan query atau scope.\n";
}

echo "\nLangkah selanjutnya:\n";
echo "1. Buka browser\n";
echo "2. Login sebagai admin\n";
echo "3. Buka Developer Tools (F12)\n";
echo "4. Pergi ke tab Console\n";
echo "5. Akses: http://localhost/beasiswa-unpatti/public/admin/university/pending\n";
echo "6. Cek apakah ada error di console\n";
echo "7. Cek apakah ada data di Network tab\n";
