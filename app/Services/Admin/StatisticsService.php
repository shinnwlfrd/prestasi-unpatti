<?php

namespace App\Services\Admin;

use App\Models\AcademicPeriod;
use App\Repositories\Contracts\AchievementRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\ValidationLogRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function __construct(
        protected StudentRepositoryInterface $studentRepo,
        protected AchievementRepositoryInterface $achievementRepo,
        protected UserRepositoryInterface $userRepo,
        protected ValidationLogRepositoryInterface $validationLogRepo
    ) {}

    public function getDashboardStatistics(): array
    {
        return [
            'students' => $this->studentRepo->count(),
            'achievements' => $this->achievementRepo->all()->count(),
            'pending' => $this->achievementRepo->countByStatus('Menunggu'),
            'approved' => $this->achievementRepo->countByStatus('Disetujui'),
            'validators' => $this->userRepo->countActiveValidators(),
            'users' => $this->userRepo->count(),
        ];
    }

    public function getStatusStatistics(): array
    {
        return $this->achievementRepo->getStatusStatistics();
    }

    public function getRecentAchievements(int $limit = 10)
    {
        return $this->achievementRepo->getRecentAchievements($limit);
    }

    public function getUrgentPending(int $days = 7, int $limit = 5)
    {
        return $this->achievementRepo->getUrgentPending($days, $limit);
    }

    public function getRecentValidations(int $limit = 10)
    {
        return $this->validationLogRepo->getRecentValidations($limit);
    }

    public function getFacultyValidationStats(?int $periodId = null)
    {
        $activePeriod = $periodId ? AcademicPeriod::find($periodId) : AcademicPeriod::where('is_active', true)->first();

        if (! $activePeriod) {
            return collect();
        }

        // Mapping nama fakultas
        $facultyMapping = [
            'Fakultas Teknik' => 'FT',
            'Fakultas Hukum' => 'FH',
            'Fakultas Ekonomi dan Bisnis' => 'FEB',
            'Fakultas Kedokteran' => 'FK',
            'Fakultas Ilmu Sosial dan Ilmu Politik' => 'FISIP',
            'Fakultas Perikanan dan Ilmu Kelautan' => 'FPIK',
            'Fakultas Sains dan Teknologi' => 'FST',
            'Fakultas Keguruan dan Ilmu Pendidikan' => 'FKIP',
            'Fakultas Pertanian' => 'FP',
            'N/A' => 'N/A',
        ];

        return DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->where('student_achievements.academic_period_id', $activePeriod->id)
            ->whereIn('student_achievements.validation_status', ['Disetujui', 'Ditolak', 'Revisi'])
            ->selectRaw("
                COALESCE(students.faculty, 'N/A') as faculty,
                COUNT(*) as total_validated,
                SUM(CASE WHEN student_achievements.validation_status = 'Disetujui' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN student_achievements.validation_status = 'Ditolak' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN student_achievements.validation_status = 'Revisi' THEN 1 ELSE 0 END) as revision
            ")
            ->groupBy('students.faculty')
            ->orderByDesc('total_validated')
            ->take(10)
            ->get()
            ->map(function ($item) use ($facultyMapping) {
                return [
                    'faculty' => $facultyMapping[$item->faculty] ?? $item->faculty,
                    'total_validated' => $item->total_validated,
                    'approved' => $item->approved,
                    'rejected' => $item->rejected,
                    'revision' => $item->revision,
                    'approval_rate' => $item->total_validated > 0 ? round(($item->approved / $item->total_validated) * 100, 1) : 0,
                ];
            });
    }
}
