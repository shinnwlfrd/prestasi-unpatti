<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /**
     * Export achievements data to CSV/Excel with comprehensive information
     */
    public function exportAchievements(Request $request)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        if (!$currentRole) {
            return response()->json(['error' => 'No active role found'], 403);
        }

        // Build scope metadata for report headers and filename
        $scopeInfo = $this->getScopeInfo($currentRole);

        // Build query based on role scope
        $query = StudentAchievement::with([
            'student',
            'achievement.category',
            'academicPeriod',
            'facultyValidator',
            'universityValidator',
            'documents',
            'skAssignment.skDocument',
        ]);

        // Apply scope filtering
        if (!$currentRole->isUniversityLevel()) {
            $query->whereHas('student', function ($q) use ($currentRole) {
                if ($currentRole->isFacultyLevel()) {
                    $q->where('faculty_id', $currentRole->faculty_id);
                } elseif ($currentRole->isDepartmentLevel()) {
                    $q->where('department_id', $currentRole->department_id);
                } elseif ($currentRole->isProgramStudyLevel()) {
                    $q->where('program_study_id', $currentRole->program_study_id);
                }
            });
        }

        // For pimpinan, only export approved achievements by default
        if ($currentRole->role === 'pimpinan') {
            $query->whereIn('validation_status', ['faculty_approved', 'university_approved']);
        }

        // Apply filters from request
        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('periods')) {
            $periods = is_array($request->periods) ? $request->periods : [$request->periods];
            $query->whereIn('academic_period_id', $periods);
        } elseif ($request->filled('period_id')) {
            $query->where('academic_period_id', $request->period_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('achievement', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('levels')) {
            $levels = is_array($request->levels) ? $request->levels : [$request->levels];
            $query->whereIn('student_achievements.level', $levels);
        }

        // Get data
        $achievements = $query->orderBy('submitted_at', 'desc')->get();

        // Generate file based on format
        $format = $request->get('format', 'excel');
        $filename = 'prestasi_' . $scopeInfo['filename_scope'] . '_' . date('Y-m-d_His');

        if ($format === 'csv') {
            $filename .= '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($achievements, $scopeInfo, $user) {
                $file = fopen('php://output', 'w');
                // Add BOM for Excel UTF-8 support
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Report header with scope info
                fputcsv($file, ['LAPORAN PRESTASI MAHASISWA - SIMAPRES UNPATTI']);
                fputcsv($file, ['Jabatan: ' . $scopeInfo['position_label']]);
                fputcsv($file, ['Cakupan: ' . $scopeInfo['scope_name']]);
                fputcsv($file, ['Dicetak oleh: ' . $user->name]);
                fputcsv($file, ['Tanggal Cetak: ' . now()->format('d/m/Y H:i:s')]);
                fputcsv($file, []);

                // Comprehensive Header row
                fputcsv($file, [
                    'ID Prestasi',
                    'NIM',
                    'Nama Mahasiswa',
                    'Email',
                    'Angkatan',
                    'Fakultas',
                    'Jurusan',
                    'Program Studi',
                    'Nama Event/Kompetisi',
                    'Kategori Prestasi',
                    'Tingkat Prestasi',
                    'Peringkat/Pencapaian',
                    'Penyelenggara',
                    'Lokasi Event',
                    'Tanggal Event',
                    'Deskripsi',
                    'Periode Akademik',
                    'Status Validasi',
                    'Tahap Validasi',
                    'Validator Fakultas',
                    'Tanggal Validasi Fakultas',
                    'Catatan Fakultas',
                    'Validator Universitas',
                    'Tanggal Validasi Universitas',
                    'Catatan Universitas',
                    'Nomor SK',
                    'Tanggal SK',
                    'Jumlah Dokumen',
                    'Submitted By',
                    'Tanggal Submit',
                    'Tanggal Dibuat',
                    'Terakhir Diupdate',
                ]);

                // Data rows with comprehensive information
                foreach ($achievements as $achievement) {
                    // Get SK information
                    $skInfo = $achievement->skAssignment;
                    $skNumber = $skInfo && $skInfo->skDocument ? $skInfo->skDocument->sk_number : '-';
                    $skDate = $skInfo && $skInfo->skDocument && $skInfo->skDocument->issued_date 
                        ? $skInfo->skDocument->issued_date->format('d/m/Y') : '-';

                    fputcsv($file, [
                        $achievement->sa_id,
                        $achievement->student->student_id ?? '-',
                        $achievement->student->name ?? '-',
                        $achievement->student->email ?? '-',
                        $achievement->student->angkatan ?? '-',
                        $achievement->student->faculty ?? '-',
                        $achievement->student->department ?? '-',
                        $achievement->student->program_study ?? '-',
                        $achievement->event_name ?? '-',
                        $achievement->achievement->category->name ?? '-',
                        $achievement->level ?? '-',
                        $achievement->ranking ?? '-',
                        $achievement->organizer ?? '-',
                        $achievement->event_location ?? '-',
                        $achievement->event_date ? $achievement->event_date->format('d/m/Y') : '-',
                        $achievement->event_date ? $achievement->event_date->format('Y') : '-',
                        $achievement->description ?? '-',
                        $achievement->academicPeriod->name ?? '-',
                        $this->getStatusLabel($achievement->validation_status),
                        $this->getCurrentStage($achievement),
                        $achievement->facultyValidator->name ?? '-',
                        $achievement->faculty_validated_at ? $achievement->faculty_validated_at->format('d/m/Y H:i') : '-',
                        $achievement->faculty_notes ?? '-',
                        $achievement->universityValidator->name ?? '-',
                        $achievement->university_validated_at ? $achievement->university_validated_at->format('d/m/Y H:i') : '-',
                        $achievement->university_notes ?? '-',
                        $skNumber,
                        $skDate,
                        $achievement->documents->count(),
                        $this->getSubmittedByLabel($achievement->submitted_by),
                        $achievement->submitted_at ? $achievement->submitted_at->format('d/m/Y H:i') : '-',
                        $achievement->created_at->format('d/m/Y H:i'),
                        $achievement->updated_at->format('d/m/Y H:i'),
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        } else {
            // Excel Format (HTML Table based)
            $filename .= '.xls';
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Cache-Control' => 'max-age=0',
            ];

            $callback = function () use ($achievements, $scopeInfo, $user) {
                echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
                echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                echo '<style>th { background-color: #1f2937; color: white; font-weight: bold; padding: 8px; } td { padding: 6px; border: 1px solid #e5e7eb; }</style>';
                echo '</head><body>';

                // Report header with scope info
                echo '<h1>LAPORAN PRESTASI MAHASISWA - SIMAPRES UNPATTI</h1>';
                echo '<p><strong>Jabatan:</strong> ' . htmlspecialchars($scopeInfo['position_label']) . '</p>';
                echo '<p><strong>Cakupan:</strong> ' . htmlspecialchars($scopeInfo['scope_name']) . '</p>';
                echo '<p><strong>Dicetak oleh:</strong> ' . htmlspecialchars($user->name) . '</p>';
                echo '<p><strong>Tanggal Cetak:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>';
                echo '<br>';

                echo '<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
                
                // Comprehensive Header
                echo '<tr>';
                echo '<th>ID Prestasi</th>';
                echo '<th>NIM</th>';
                echo '<th>Nama Mahasiswa</th>';
                echo '<th>Email</th>';
                echo '<th>Angkatan</th>';
                echo '<th>Fakultas</th>';
                echo '<th>Jurusan</th>';
                echo '<th>Program Studi</th>';
                echo '<th>Nama Event/Kompetisi</th>';
                echo '<th>Kategori Prestasi</th>';
                echo '<th>Tingkat Prestasi</th>';
                echo '<th>Peringkat/Pencapaian</th>';
                echo '<th>Penyelenggara</th>';
                echo '<th>Lokasi Event</th>';
                echo '<th>Tanggal Event</th>';
                echo '<th>Deskripsi</th>';
                echo '<th>Periode Akademik</th>';
                echo '<th>Status Validasi</th>';
                echo '<th>Tahap Validasi</th>';
                echo '<th>Validator Fakultas</th>';
                echo '<th>Tanggal Validasi Fakultas</th>';
                echo '<th>Catatan Fakultas</th>';
                echo '<th>Validator Universitas</th>';
                echo '<th>Tanggal Validasi Universitas</th>';
                echo '<th>Catatan Universitas</th>';
                echo '<th>Nomor SK</th>';
                echo '<th>Tanggal SK</th>';
                echo '<th>Jumlah Dokumen</th>';
                echo '<th>Submitted By</th>';
                echo '<th>Tanggal Submit</th>';
                echo '<th>Tanggal Dibuat</th>';
                echo '<th>Terakhir Diupdate</th>';
                echo '</tr>';

                // Data rows with comprehensive information
                foreach ($achievements as $achievement) {
                    // Get SK information
                    $skInfo = $achievement->skAssignment;
                    $skNumber = $skInfo && $skInfo->skDocument ? $skInfo->skDocument->sk_number : '-';
                    $skDate = $skInfo && $skInfo->skDocument && $skInfo->skDocument->issued_date 
                        ? $skInfo->skDocument->issued_date->format('d/m/Y') : '-';

                    echo '<tr>';
                    echo '<td>' . $achievement->sa_id . '</td>';
                    echo '<td>' . ($achievement->student->student_id ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->email ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->angkatan ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->faculty ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->department ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->program_study ?? '-') . '</td>';
                    echo '<td>' . ($achievement->event_name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->achievement->category->name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->level ?? '-') . '</td>';
                    echo '<td>' . ($achievement->ranking ?? '-') . '</td>';
                    echo '<td>' . ($achievement->organizer ?? '-') . '</td>';
                    echo '<td>' . ($achievement->event_location ?? '-') . '</td>';
                    echo '<td>' . ($achievement->event_date ? $achievement->event_date->format('d/m/Y') : '-') . '</td>';
                    echo '<td>' . ($achievement->event_date ? $achievement->event_date->format('Y') : '-') . '</td>';
                    echo '<td>' . ($achievement->description ?? '-') . '</td>';
                    echo '<td>' . ($achievement->academicPeriod->name ?? '-') . '</td>';
                    echo '<td>' . $this->getStatusLabel($achievement->validation_status) . '</td>';
                    echo '<td>' . $this->getCurrentStage($achievement) . '</td>';
                    echo '<td>' . ($achievement->facultyValidator->name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->faculty_validated_at ? $achievement->faculty_validated_at->format('d/m/Y H:i') : '-') . '</td>';
                    echo '<td>' . ($achievement->faculty_notes ?? '-') . '</td>';
                    echo '<td>' . ($achievement->universityValidator->name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->university_validated_at ? $achievement->university_validated_at->format('d/m/Y H:i') : '-') . '</td>';
                    echo '<td>' . ($achievement->university_notes ?? '-') . '</td>';
                    echo '<td>' . $skNumber . '</td>';
                    echo '<td>' . $skDate . '</td>';
                    echo '<td>' . $achievement->documents->count() . '</td>';
                    echo '<td>' . $this->getSubmittedByLabel($achievement->submitted_by) . '</td>';
                    echo '<td>' . ($achievement->submitted_at ? $achievement->submitted_at->format('d/m/Y H:i') : '-') . '</td>';
                    echo '<td>' . $achievement->created_at->format('d/m/Y H:i') . '</td>';
                    echo '<td>' . $achievement->updated_at->format('d/m/Y H:i') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</body>';
                echo '</html>';
            };
            return response()->stream($callback, 200, $headers);
        }
    }

    /**
     * Export statistics data to JSON
     */
    public function exportStatistics(Request $request)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        if (!$currentRole) {
            return response()->json(['error' => 'No active role found'], 403);
        }

        // Build base query
        $query = StudentAchievement::query();

        // Apply scope filtering
        if (!$currentRole->isUniversityLevel()) {
            $query->whereHas('student', function ($q) use ($currentRole) {
                if ($currentRole->isFacultyLevel()) {
                    $q->where('faculty_id', $currentRole->faculty_id);
                } elseif ($currentRole->isDepartmentLevel()) {
                    $q->where('department_id', $currentRole->department_id);
                } elseif ($currentRole->isProgramStudyLevel()) {
                    $q->where('program_study_id', $currentRole->program_study_id);
                }
            });
        }

        // Statistics by status
        $byStatus = (clone $query)
            ->select('validation_status', DB::raw('count(*) as total'))
            ->groupBy('validation_status')
            ->get()
            ->pluck('total', 'validation_status');

        // Statistics by category
        $byCategory = (clone $query)
            ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->select('achievement_categories.name', DB::raw('count(*) as total'))
            ->groupBy('achievement_categories.name')
            ->get();

        // Statistics by level - use student_achievements.level directly
        $byLevel = (clone $query)
            ->select('student_achievements.level as name', DB::raw('count(*) as total'))
            ->groupBy('student_achievements.level')
            ->get();

        // Monthly trend
        $monthlyTrend = (clone $query)
            ->select(
                DB::raw('DATE_FORMAT(submitted_at, "%Y-%m") as month'),
                DB::raw('count(*) as total')
            )
            ->whereNotNull('submitted_at')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'scope' => [
                'level' => $currentRole->level,
                'name' => $currentRole->getScopeDescription(),
            ],
            'statistics' => [
                'by_status' => $byStatus,
                'by_category' => $byCategory,
                'by_level' => $byLevel,
                'monthly_trend' => $monthlyTrend,
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get status label in Indonesian
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'faculty_review' => 'Review Fakultas',
            'faculty_approved' => 'Disetujui Fakultas',
            'faculty_rejected' => 'Ditolak Fakultas',
            'faculty_revision' => 'Revisi Fakultas',
            'university_review' => 'Review Universitas',
            'university_approved' => 'Disetujui Universitas',
            'university_rejected' => 'Ditolak Universitas',
            'university_revision' => 'Revisi Universitas',
            'appeal_submitted' => 'Banding Diajukan',
            'appeal_approved' => 'Banding Disetujui',
            'appeal_rejected' => 'Banding Ditolak',
            default => $status,
        };
    }

    /**
     * Get current validation stage
     */
    private function getCurrentStage($achievement)
    {
        if (in_array($achievement->validation_status, ['submitted', 'faculty_review', 'faculty_revision'])) {
            return 'Tahap Fakultas';
        } elseif (in_array($achievement->validation_status, ['faculty_approved', 'university_review', 'university_revision'])) {
            return 'Tahap Universitas';
        } elseif (in_array($achievement->validation_status, ['university_approved'])) {
            return 'Selesai - Disetujui';
        } elseif (in_array($achievement->validation_status, ['faculty_rejected', 'university_rejected'])) {
            return 'Ditolak';
        }
        return 'Draft';
    }

    /**
     * Get submitted by label
     */
    private function getSubmittedByLabel($submittedBy)
    {
        return match ($submittedBy) {
            'student' => 'Mahasiswa',
            'validator' => 'Operator',
            'admin' => 'Administrator',
            default => $submittedBy ?? '-',
        };
    }

    /**
     * Get scope information for report headers and filename
     */
    private function getScopeInfo($currentRole): array
    {
        $positionLabels = [
            'rektor' => 'Rektor',
            'wakil_rektor_1' => 'Wakil Rektor I',
            'wakil_rektor_2' => 'Wakil Rektor II',
            'wakil_rektor_3' => 'Wakil Rektor III',
            'dekan' => 'Dekan',
            'wakil_dekan' => 'Wakil Dekan',
            'ketua_jurusan' => 'Ketua Jurusan',
            'sekretaris_jurusan' => 'Sekretaris Jurusan',
            'kaprodi' => 'Ketua Program Studi',
            'sekprodi' => 'Sekretaris Program Studi',
            'direktur_pps' => 'Direktur Pascasarjana',
            'kepala_biro_kemahasiswaan' => 'Kepala Biro Kemahasiswaan',
            'super_admin' => 'Super Admin',
        ];

        $position = $currentRole->position ?? session('pimpinan_position') ?? null;
        $positionLabel = $positionLabels[$position] ?? $currentRole->getRoleDisplayName();

        // Build scope name
        $scopeName = 'Seluruh Universitas';
        $filenameScope = 'universitas';

        if ($currentRole->isProgramStudyLevel()) {
            $prodiName = $currentRole->program_study_name ?? session('pimpinan_program_study_name') ?? 'Program Studi';
            $scopeName = $prodiName;
            $filenameScope = 'prodi_' . strtolower(str_replace([' ', '-', '.'], '_', substr($prodiName, 0, 30)));
        } elseif ($currentRole->isDepartmentLevel()) {
            $deptName = $currentRole->department_name ?? session('pimpinan_department_name') ?? 'Jurusan';
            $scopeName = $deptName;
            $filenameScope = 'jur_' . strtolower(str_replace([' ', '-', '.'], '_', substr($deptName, 0, 30)));
        } elseif ($currentRole->isFacultyLevel()) {
            $facName = $currentRole->faculty_name ?? session('pimpinan_faculty_name') ?? 'Fakultas';
            $scopeName = $facName;
            $filenameScope = 'fak_' . strtolower(str_replace([' ', '-', '.'], '_', substr($facName, 0, 30)));
        }

        return [
            'position_label' => $positionLabel,
            'scope_name' => $scopeName,
            'filename_scope' => $filenameScope,
        ];
    }
}
