<?php

/**
 * Script untuk mengisi IPK (GPA) mahasiswa secara random
 * Jalankan dengan: php seed_gpa.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

echo "=== Seed IPK Mahasiswa ===\n\n";

// Get all students
$students = Student::all();
$totalStudents = $students->count();

echo "Total mahasiswa: {$totalStudents}\n";
echo "Mengisi IPK...\n\n";

$updated = 0;
$skipped = 0;

foreach ($students as $student) {
    // Skip if already has GPA
    if ($student->gpa && $student->gpa > 0) {
        $skipped++;
        continue;
    }
    
    // Generate realistic GPA based on angkatan
    // Mahasiswa baru cenderung IPK lebih rendah, senior lebih tinggi
    $currentYear = date('Y');
    $yearsInCollege = $currentYear - $student->angkatan;
    
    if ($yearsInCollege <= 1) {
        // Tahun 1: IPK 2.5 - 3.8
        $gpa = round(mt_rand(250, 380) / 100, 2);
    } elseif ($yearsInCollege == 2) {
        // Tahun 2: IPK 2.7 - 3.9
        $gpa = round(mt_rand(270, 390) / 100, 2);
    } elseif ($yearsInCollege == 3) {
        // Tahun 3: IPK 2.8 - 3.95
        $gpa = round(mt_rand(280, 395) / 100, 2);
    } else {
        // Tahun 4+: IPK 2.9 - 4.0
        $gpa = round(mt_rand(290, 400) / 100, 2);
    }
    
    // Update student
    $student->gpa = $gpa;
    $student->save();
    
    $updated++;
    
    // Show progress every 50 students
    if ($updated % 50 == 0) {
        echo "Progress: {$updated}/{$totalStudents} mahasiswa diupdate...\n";
    }
}

echo "\n=== Selesai ===\n";
echo "Total diupdate: {$updated}\n";
echo "Total dilewati (sudah ada IPK): {$skipped}\n\n";

// Show statistics
echo "=== Statistik IPK ===\n";
$avgGpa = Student::whereNotNull('gpa')->where('gpa', '>', 0)->avg('gpa');
$maxGpa = Student::whereNotNull('gpa')->where('gpa', '>', 0)->max('gpa');
$minGpa = Student::whereNotNull('gpa')->where('gpa', '>', 0)->min('gpa');
$cumlaude = Student::where('gpa', '>=', 3.5)->count();
$sangatMemuaskan = Student::whereBetween('gpa', [3.0, 3.49])->count();

echo "Rata-rata IPK: " . number_format($avgGpa, 2) . "\n";
echo "IPK Tertinggi: " . number_format($maxGpa, 2) . "\n";
echo "IPK Terendah: " . number_format($minGpa, 2) . "\n";
echo "Cumlaude (≥3.5): {$cumlaude} mahasiswa\n";
echo "Sangat Memuaskan (3.0-3.49): {$sangatMemuaskan} mahasiswa\n\n";

// Show top 5 per angkatan
echo "=== Top 5 IPK per Angkatan ===\n";
$angkatanList = Student::select('angkatan')->distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');

foreach ($angkatanList->take(3) as $angkatan) {
    echo "\nAngkatan {$angkatan}:\n";
    $topStudents = Student::where('angkatan', $angkatan)
        ->whereNotNull('gpa')
        ->where('gpa', '>', 0)
        ->orderBy('gpa', 'desc')
        ->limit(5)
        ->get();
    
    foreach ($topStudents as $index => $student) {
        $rank = $index + 1;
        echo "  {$rank}. {$student->name} ({$student->student_id}) - IPK: " . number_format($student->gpa, 2) . "\n";
    }
}

echo "\n✓ Seeder IPK selesai!\n";
