<?php

namespace App\Http\Controllers;

use App\Models\StudentAchievement;
use App\Services\AchievementApprovalService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AchievementDashboardController extends Controller
{
    protected AchievementApprovalService $approvalService;

    public function __construct(AchievementApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function index(Request $request)
    {
        $statistics = $this->approvalService->getApprovalStatistics();
        $monthlyTrend = $this->approvalService->getMonthlyTrend(6);
        $levelDistribution = $this->approvalService->getLevelDistribution();

        // Recent achievements requiring attention
        $pendingReview = StudentAchievement::with(['student', 'achievement'])
            ->pending()
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $lowCredibility = StudentAchievement::with(['student', 'achievement'])
            ->lowCredibility()
            ->pending()
            ->latest('submitted_at')
            ->take(5)
            ->get();

        return view('admin.achievements.dashboard', compact(
            'statistics',
            'monthlyTrend',
            'levelDistribution',
            'pendingReview',
            'lowCredibility'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');

        $query = StudentAchievement::with(['student', 'achievement', 'validator']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        $achievements = $query->get();

        if ($format === 'pdf') {
            return $this->exportPdf($achievements);
        }

        return $this->exportExcel($achievements);
    }

    protected function exportExcel($achievements)
    {
        $data = $achievements->map(function ($achievement) {
            return [
                'ID' => $achievement->sa_id,
                'Nama Mahasiswa' => $achievement->student?->name ?? '-',
                'NIM' => $achievement->student_id,
                'Nama Lomba' => $achievement->event_name,
                'Kategori' => $achievement->achievement?->category ?? '-',
                'Tingkat' => $achievement->level,
                'Penyelenggara' => $achievement->organizer,
                'Tanggal' => $achievement->event_date?->format('d/m/Y'),
                'Peringkat' => $achievement->ranking ?? '-',
                'Status' => $achievement->status_label,
                'Skor Kredibilitas' => $achievement->credibility_score . '%',
                'Tanggal Submit' => $achievement->submitted_at?->format('d/m/Y H:i'),
                'Validator' => $achievement->validator?->name ?? '-',
            ];
        });

        $filename = 'prestasi_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Headers
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            
            // Data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportPdf($achievements)
    {
        $statistics = $this->approvalService->getApprovalStatistics();

        $pdf = Pdf::loadView('admin.achievements.export-pdf', compact('achievements', 'statistics'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('prestasi_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function chartData(Request $request)
    {
        $type = $request->input('type', 'monthly');

        return response()->json(match($type) {
            'monthly' => $this->approvalService->getMonthlyTrend(6),
            'level' => $this->approvalService->getLevelDistribution(),
            'status' => $this->approvalService->getApprovalStatistics(),
            default => [],
        });
    }
}
