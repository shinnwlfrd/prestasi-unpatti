<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
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

        $query = StudentAchievement::with(['student', 'academicPeriod']);

        // Filter by period if specified
        if ($periodId !== 'all') {
            $query->where('academic_period_id', $periodId);
        }

        $achievements = $query->orderBy('created_at', 'desc')->get();

        $filename = 'laporan_prestasi_' . ($periodId === 'all' ? 'semua_periode' : 'periode_' . $periodId) . '_' . now()->format('Ymd_His');

        if ($format === 'excel') {
            return $this->downloadExcel($achievements, $filename);
        }

        return $this->downloadCsv($achievements, $filename);
    }

    /**
     * Download as CSV
     */
    private function downloadCsv($achievements, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($achievements) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header labels
            fputcsv($file, [
                'ID',
                'NIM',
                'Nama Mahasiswa',
                'Fakultas',
                'Program Studi',
                'Nama Event',
                'Tingkat',
                'Peringkat',
                'Tahun',
                'Periode Akademik',
                'Status Validasi',
                'Tanggal Pengajuan'
            ]);

            foreach ($achievements as $a) {
                fputcsv($file, [
                    $a->sa_id,
                    $a->student->student_id ?? '-',
                    $a->student->name ?? '-',
                    $a->student->faculty ?? '-',
                    $a->student->program_study ?? '-',
                    $a->event_name,
                    $a->level,
                    $a->ranking,
                    $a->event_date ? $a->event_date->format('Y') : '-',
                    $a->academicPeriod->name ?? '-',
                    $a->status_label, // Using model accessor
                    $a->created_at->format('d/m/Y H:i')
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Download as Excel (HTML Table format for broad compatibility)
     */
    private function downloadExcel($achievements, $filename)
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xls\"",
        ];

        $callback = function () use ($achievements) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
            echo '<table border="1">';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">ID</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">NIM</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Nama Mahasiswa</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Fakultas</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Program Studi</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Nama Event</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tingkat</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Peringkat</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tahun</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Periode Akademik</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Status Validasi</th>';
            echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tanggal Pengajuan</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($achievements as $a) {
                echo '<tr>';
                echo '<td>' . $a->sa_id . '</td>';
                echo '<td>' . ($a->student->student_id ?? '-') . '</td>';
                echo '<td>' . ($a->student->name ?? '-') . '</td>';
                echo '<td>' . ($a->student->faculty ?? '-') . '</td>';
                echo '<td>' . ($a->student->program_study ?? '-') . '</td>';
                echo '<td>' . $a->event_name . '</td>';
                echo '<td>' . $a->level . '</td>';
                echo '<td>' . $a->ranking . '</td>';
                echo '<td>' . ($a->event_date ? $a->event_date->format('Y') : '-') . '</td>';
                echo '<td>' . ($a->academicPeriod->name ?? '-') . '</td>';
                echo '<td>' . $a->status_label . '</td>';
                echo '<td>' . $a->created_at->format('d/m/Y H:i') . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table></body></html>';
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
