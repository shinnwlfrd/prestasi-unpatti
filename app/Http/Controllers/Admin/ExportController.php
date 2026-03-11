<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use App\Models\AcademicPeriod;
use App\Models\Student;
use App\Models\ValidationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export achievements to CSV or Excel
     */
    public function exportAchievements(Request $request)
    {
        $format = $request->get('format', 'csv');
        $periodId = $request->get('period', 'all');

        $query = StudentAchievement::with([
            'student',
            'achievement.category',
            'academicPeriod',
            'facultyValidator',
            'universityValidator',
            'documents',
            'skAssignment.skDocument',
        ]);

        // Filter by period if specified
        if ($periodId !== 'all') {
            $query->where('academic_period_id', $periodId);
        }

        $achievements = $query->orderBy('created_at', 'desc')->get();

        // Get period info
        $period = $periodId !== 'all' ? AcademicPeriod::find($periodId) : null;
        $periodName = $period ? $period->name : 'Semua Periode';

        $filename = 'laporan_prestasi_' . ($periodId === 'all' ? 'semua_periode' : 'periode_' . $periodId) . '_' . now()->format('Ymd_His');

        if ($format === 'excel') {
            return $this->downloadExcel($achievements, $filename, $periodName);
        }

        return $this->downloadCsv($achievements, $filename, $periodName);
    }

    /**
     * Download as CSV - Same format as Pimpinan
     */
    private function downloadCsv($achievements, $filename, $periodName)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($achievements, $periodName) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Add report header
            fputcsv($file, ['LAPORAN PRESTASI MAHASISWA - SIMAPRES UNPATTI']);
            fputcsv($file, ['Periode: ' . $periodName]);
            fputcsv($file, ['Tanggal Cetak: ' . now()->format('d/m/Y H:i:s')]);
            fputcsv($file, ['Dicetak oleh: ' . auth()->user()->name]);
            fputcsv($file, []);

            // Comprehensive Header row - same as Pimpinan
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
                'Tahun',
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
                    $achievement->status_label,
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

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Download as Excel - Same format as Pimpinan
     */
    private function downloadExcel($achievements, $filename, $periodName)
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xls\"",
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function () use ($achievements, $periodName) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            echo '<style>th { background-color: #1f2937; color: white; font-weight: bold; padding: 8px; } td { padding: 6px; border: 1px solid #e5e7eb; }</style>';
            echo '</head><body>';
            
            // Report header
            echo '<h1>LAPORAN PRESTASI MAHASISWA - SIMAPRES UNPATTI</h1>';
            echo '<p><strong>Periode:</strong> ' . htmlspecialchars($periodName) . '</p>';
            echo '<p><strong>Tanggal Cetak:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>';
            echo '<p><strong>Dicetak oleh:</strong> ' . htmlspecialchars(auth()->user()->name) . '</p>';
            echo '<br>';
            
            echo '<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
            
            // Comprehensive Header - same as Pimpinan
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
            echo '<th>Tahun</th>';
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
                echo '<td>' . $achievement->status_label . '</td>';
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

        return new StreamedResponse($callback, 200, $headers);
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
            'validator' => 'Validator/Operator',
            'admin' => 'Administrator',
            default => $submittedBy ?? '-',
        };
    }
}
