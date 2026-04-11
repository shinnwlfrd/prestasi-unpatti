<?php

/**
 * Script untuk menghitung jumlah SLA Breach
 * 
 * SLA Breach adalah pengajuan prestasi yang melampaui batas waktu validasi (> 7 hari)
 * dan belum selesai divalidasi (tidak termasuk yang sudah ditolak)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentAchievement;
use Carbon\Carbon;

echo "===========================================\n";
echo "   HITUNG JUMLAH SLA BREACH\n";
echo "===========================================\n\n";

// Status yang dihitung sebagai SLA breach
$validStatuses = [
    'submitted',
    'faculty_review',
    'university_review',
    'faculty_revision',
    'university_revision'
];

// Status yang dikecualikan (sudah ditolak)
$excludedStatuses = [
    'faculty_rejected',
    'university_rejected'
];

// Tanggal batas (7 hari yang lalu)
$cutoffDate = Carbon::now()->subDays(7);

echo "Kriteria SLA Breach:\n";
echo "- Status: " . implode(', ', $validStatuses) . "\n";
echo "- Dikecualikan: " . implode(', ', $excludedStatuses) . "\n";
echo "- Tanggal submit sebelum: " . $cutoffDate->format('d M Y H:i') . "\n";
echo "- Hari ini: " . Carbon::now()->format('d M Y H:i') . "\n\n";

// Query untuk menghitung SLA breach
$breaches = StudentAchievement::with(['student', 'achievement.category', 'academicPeriod'])
    ->whereIn('validation_status', $validStatuses)
    ->whereNotIn('validation_status', $excludedStatuses)
    ->where('submitted_at', '<', $cutoffDate)
    ->orderBy('submitted_at', 'asc')
    ->get();

$totalBreaches = $breaches->count();

echo "===========================================\n";
echo "HASIL PERHITUNGAN\n";
echo "===========================================\n";
echo "Total SLA Breach: {$totalBreaches} pengajuan\n\n";

if ($totalBreaches > 0) {
    echo "DETAIL PENGAJUAN YANG MELAMPAUI SLA:\n";
    echo str_repeat("-", 150) . "\n";
    printf("%-5s %-20s %-30s %-25s %-15s %-15s %-15s\n", 
        "No", "NIM", "Nama Mahasiswa", "Event", "Kategori", "Submit", "Terlambat");
    echo str_repeat("-", 150) . "\n";

    $no = 1;
    foreach ($breaches as $breach) {
        $daysOverdue = Carbon::now()->diffInDays($breach->submitted_at);
        $studentName = isset($breach->student->name) ? $breach->student->name : 'N/A';
        $studentId = isset($breach->student->student_id) ? $breach->student->student_id : 'N/A';
        $eventName = substr($breach->event_name, 0, 28);
        $categoryName = isset($breach->achievement->category->name) ? $breach->achievement->category->name : 'N/A';
        $submittedAt = $breach->submitted_at->format('d M Y');
        
        printf("%-5s %-20s %-30s %-25s %-15s %-15s %-15s\n",
            $no++,
            $studentId,
            substr($studentName, 0, 28),
            $eventName,
            substr($categoryName, 0, 13),
            $submittedAt,
            "{$daysOverdue} hari"
        );
    }
    echo str_repeat("-", 150) . "\n\n";

    // Statistik tambahan
    echo "STATISTIK TAMBAHAN:\n";
    echo str_repeat("-", 50) . "\n";
    
    // Breakdown by status
    $byStatus = $breaches->groupBy('validation_status');
    echo "Breakdown by Status:\n";
    foreach ($byStatus as $status => $items) {
        echo "  - {$status}: " . $items->count() . " pengajuan\n";
    }
    echo "\n";

    // Breakdown by days overdue
    $veryLate = $breaches->filter(fn($b) => Carbon::now()->diffInDays($b->submitted_at) > 14)->count();
    $late = $breaches->filter(fn($b) => Carbon::now()->diffInDays($b->submitted_at) <= 14)->count();
    echo "Breakdown by Keterlambatan:\n";
    echo "  - Sangat Terlambat (> 14 hari): {$veryLate} pengajuan\n";
    echo "  - Terlambat (7-14 hari): {$late} pengajuan\n";
    echo "\n";

    // Breakdown by level
    $byLevel = $breaches->groupBy('level');
    echo "Breakdown by Tingkat:\n";
    foreach ($byLevel as $level => $items) {
        echo "  - {$level}: " . $items->count() . " pengajuan\n";
    }
    echo "\n";

    // Top 5 oldest submissions
    echo "5 Pengajuan Paling Lama:\n";
    $oldest = $breaches->take(5);
    foreach ($oldest as $index => $old) {
        $daysOverdue = Carbon::now()->diffInDays($old->submitted_at);
        $studentName = isset($old->student->name) ? $old->student->name : 'N/A';
        echo "  " . ($index + 1) . ". {$old->event_name} - {$studentName} ({$daysOverdue} hari)\n";
    }
    echo "\n";

} else {
    echo "✓ Tidak ada pengajuan yang melampaui SLA\n";
    echo "✓ Semua pengajuan diproses tepat waktu\n\n";
}

echo "===========================================\n";
echo "REKOMENDASI TINDAKAN\n";
echo "===========================================\n";

if ($totalBreaches > 0) {
    echo "1. Prioritaskan validasi untuk pengajuan yang paling lama\n";
    echo "2. Alokasikan lebih banyak validator jika diperlukan\n";
    echo "3. Review proses validasi untuk identifikasi bottleneck\n";
    echo "4. Koordinasi dengan fakultas terkait untuk mempercepat proses\n";
    echo "5. Pertimbangkan untuk menambah SLA menjadi 10-14 hari jika konsisten terlambat\n";
} else {
    echo "✓ Pertahankan kinerja validasi yang baik\n";
    echo "✓ Monitor secara berkala untuk memastikan SLA tetap terjaga\n";
}

echo "\n===========================================\n";
echo "Script selesai dijalankan\n";
echo "===========================================\n";
