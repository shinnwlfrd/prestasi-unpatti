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
        // Get period filter - use 'all' for "Semua Periode"
        $periodId = $request->input('period');
        
        // Get all periods for dropdown
        $periods = \App\Models\AcademicPeriod::ordered()->get();
        
        // Determine selected period
        $selectedPeriod = null;
        $periodComparison = null;
        
        if ($periodId === 'all' || $periodId === null || $periodId === '') {
            // "Semua Periode" selected - show period comparison
            $periodComparison = $this->approvalService->getPeriodComparison();
            $periodId = null; // Set to null for statistics
        } else {
            // Specific period selected
            $selectedPeriod = \App\Models\AcademicPeriod::find($periodId);
        }
        
        // Get statistics with period filter
        $statistics = $this->approvalService->getApprovalStatistics($periodId);
        $monthlyTrend = $this->approvalService->getMonthlyTrend(6, $periodId);
        $levelDistribution = $this->approvalService->getLevelDistribution($periodId);

        // Recent achievements requiring attention
        $query = StudentAchievement::with(['student', 'achievement.category'])
            ->pending()
            ->latest('submitted_at');
        
        if ($periodId) {
            $query->where('academic_period_id', $periodId);
        }
        
        $pendingReview = $query->take(10)->get();

        return view('admin.achievements.dashboard', compact(
            'statistics',
            'monthlyTrend',
            'levelDistribution',
            'pendingReview',
            'periods',
            'selectedPeriod',
            'periodComparison'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');

        $query = StudentAchievement::with(['student', 'achievement.category', 'validator']);

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
