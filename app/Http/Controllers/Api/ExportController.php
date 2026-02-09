<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /**
     * Export achievements data to CSV
     */
    public function exportAchievements(Request $request)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        if (!$currentRole) {
            return response()->json(['error' => 'No active role found'], 403);
        }

        // Build query based on role scope
        $query = StudentAchievement::with([
            'student',
            'achievement.category',
            'validator',
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
        $filename = 'prestasi_mahasiswa_' . date('Y-m-d_His');

        if ($format === 'csv') {
            $filename .= '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($achievements) {
                $file = fopen('php://output', 'w');
                // Add BOM for Excel UTF-8 support
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Header row
                fputcsv($file, [
                    'NIM',
                    'Nama Mahasiswa',
                    'Fakultas',
                    'Program Studi',
                    'Nama Prestasi',
                    'Kategori',
                    'Tingkat',
                    'Peringkat',
                    'Penyelenggara',
                    'Tanggal Mulai',
                    'Tanggal Selesai',
                    'Status Validasi',
                    'Validator',
                    'Tanggal Submit',
                    'Tanggal Validasi',
                ]);

                // Data rows
                foreach ($achievements as $achievement) {
                    fputcsv($file, [
                        $achievement->student->student_id ?? '-',
                        $achievement->student->name ?? '-',
                        $achievement->student->faculty ?? '-',
                        $achievement->student->program_study ?? '-',
                        $achievement->event_name ?? '-',
                        $achievement->achievement->category->name ?? '-',
                        $achievement->level ?? '-',
                        $achievement->ranking ?? '-',
                        $achievement->organizer ?? '-',
                        $achievement->event_date ? $achievement->event_date->format('d/m/Y') : '-',
                        $achievement->event_date ? $achievement->event_date->format('d/m/Y') : '-',
                        $this->getStatusLabel($achievement->validation_status),
                        $achievement->validator->name ?? '-',
                        $achievement->submitted_at ? $achievement->submitted_at->format('d/m/Y H:i') : '-',
                        $achievement->validated_at ? $achievement->validated_at->format('d/m/Y H:i') : '-',
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

            $callback = function () use ($achievements) {
                echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
                echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
                echo '<body>';
                echo '<table border="1">';
                echo '<tr>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">NIM</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Nama Mahasiswa</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Fakultas</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Program Studi</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Nama Prestasi</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Kategori</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tingkat</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Peringkat</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Penyelenggara</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tanggal Mulai</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tanggal Selesai</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Status Validasi</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Validator</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tanggal Submit</th>';
                echo '<th style="background-color: #f3f4f6; font-weight: bold;">Tanggal Validasi</th>';
                echo '</tr>';

                foreach ($achievements as $achievement) {
                    echo '<tr>';
                    echo '<td>' . ($achievement->student->student_id ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->faculty ?? '-') . '</td>';
                    echo '<td>' . ($achievement->student->program_study ?? '-') . '</td>';
                    echo '<td>' . ($achievement->event_name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->achievement->category->name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->level ?? '-') . '</td>';
                    echo '<td>' . ($achievement->ranking ?? '-') . '</td>';
                    echo '<td>' . ($achievement->organizer ?? '-') . '</td>';
                    echo '<td>' . ($achievement->event_date ? $achievement->event_date->format('d/m/Y') : '-') . '</td>';
                    echo '<td>' . ($achievement->event_date ? $achievement->event_date->format('d/m/Y') : '-') . '</td>';
                    echo '<td>' . $this->getStatusLabel($achievement->validation_status) . '</td>';
                    echo '<td>' . ($achievement->validator->name ?? '-') . '</td>';
                    echo '<td>' . ($achievement->submitted_at ? $achievement->submitted_at->format('d/m/Y H:i') : '-') . '</td>';
                    echo '<td>' . ($achievement->validated_at ? $achievement->validated_at->format('d/m/Y H:i') : '-') . '</td>';
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
}
