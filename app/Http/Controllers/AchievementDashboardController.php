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

        // HIGH PRIORITY FEATURES
        
        // 1. Top Performers
        $topPerformers = $this->getTopPerformers($periodId);
        
        // 2. Faculty Comparison
        $facultyComparison = $this->getFacultyComparison($periodId);
        
        // 3. Validator Performance
        $validatorPerformance = $this->getValidatorPerformance($periodId);
        
        // 4. Category Distribution
        $categoryDistribution = $this->getCategoryDistribution($periodId);

        return view('admin.achievements.dashboard', compact(
            'statistics',
            'monthlyTrend',
            'levelDistribution',
            'periods',
            'selectedPeriod',
            'periodComparison',
            'topPerformers',
            'facultyComparison',
            'validatorPerformance',
            'categoryDistribution'
        ));
    }
    
    /**
     * Get top performing students
     */
    protected function getTopPerformers($periodId = null, $limit = 10)
    {
        $query = \App\Models\Student::withCount([
            'achievements' => function($q) use ($periodId) {
                $q->where('validation_status', 'Disetujui');
                if ($periodId) {
                    $q->where('academic_period_id', $periodId);
                }
            }
        ])
        ->having('achievements_count', '>', 0)
        ->orderByDesc('achievements_count')
        ->limit($limit);
        
        return $query->get();
    }
    
    /**
     * Get faculty comparison data
     */
    protected function getFacultyComparison($periodId = null)
    {
        $query = \DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->selectRaw('
                COALESCE(students.faculty, "N/A") as faculty,
                COUNT(*) as total,
                SUM(CASE WHEN student_achievements.validation_status = "Disetujui" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN student_achievements.validation_status = "Menunggu" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN student_achievements.validation_status = "Ditolak" THEN 1 ELSE 0 END) as rejected
            ')
            ->groupBy('students.faculty')
            ->orderByDesc('total');
        
        if ($periodId) {
            $query->where('student_achievements.academic_period_id', $periodId);
        }
        
        return collect($query->get());
    }
    
    /**
     * Get validator performance metrics
     */
    protected function getValidatorPerformance($periodId = null)
    {
        $query = \App\Models\User::where('role', 'Validator')
            ->withCount([
                'validatedAchievements' => function($q) use ($periodId) {
                    if ($periodId) {
                        $q->where('academic_period_id', $periodId);
                    }
                }
            ])
            ->with([
                'validatedAchievements' => function($q) use ($periodId) {
                    $q->select('validator_id', 
                        \DB::raw('AVG(DATEDIFF(updated_at, submitted_at)) as avg_days'))
                        ->whereNotNull('validator_id')
                        ->whereIn('validation_status', ['Disetujui', 'Ditolak']);
                    if ($periodId) {
                        $q->where('academic_period_id', $periodId);
                    }
                    $q->groupBy('validator_id');
                }
            ])
            ->orderByDesc('validated_achievements_count')
            ->limit(10);
        
        return $query->get()->map(function($validator) {
            $avgDays = $validator->validatedAchievements->first()?->avg_days ?? 0;
            return [
                'name' => $validator->name,
                'faculty' => $validator->faculty ?? 'All',
                'total_validated' => $validator->validated_achievements_count,
                'avg_response_days' => round($avgDays, 1)
            ];
        });
    }
    
    /**
     * Get category distribution
     */
    protected function getCategoryDistribution($periodId = null)
    {
        $query = StudentAchievement::join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->selectRaw('
                achievement_categories.name as category,
                COUNT(*) as total,
                SUM(CASE WHEN validation_status = "Disetujui" THEN 1 ELSE 0 END) as approved
            ')
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->orderByDesc('total');
        
        if ($periodId) {
            $query->where('student_achievements.academic_period_id', $periodId);
        }
        
        return $query->get();
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
