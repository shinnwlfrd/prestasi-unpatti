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
        $scopeFilters = $this->getScopeFilters();
        
        return [
            'students' => $this->getStudentCount($scopeFilters),
            'achievements' => $this->getAchievementCount($scopeFilters),
            'pending' => $this->getPendingCount($scopeFilters),
            'approved' => $this->getApprovedCount($scopeFilters),
            'validators' => $this->userRepo->countActiveValidators(),
            'users' => $this->userRepo->count(),
            'scope_info' => $this->getScopeInfo(),
        ];
    }

    /**
     * Get scope filters based on user role
     */
    protected function getScopeFilters(): array
    {
        $user = auth()->user();
        $filters = [];

        // Super admin can see everything
        if ($user->isSuperAdmin()) {
            return $filters;
        }

        // Pimpinan scope filtering
        if ($user->isPimpinan()) {
            $level = session('pimpinan_level');
            
            if ($level === 'faculty') {
                $filters['faculty_id'] = session('pimpinan_faculty_id');
            } elseif ($level === 'department') {
                $filters['faculty_id'] = session('pimpinan_faculty_id');
                $filters['department_id'] = session('pimpinan_department_id');
            } elseif ($level === 'program_study') {
                $filters['faculty_id'] = session('pimpinan_faculty_id');
                $filters['department_id'] = session('pimpinan_department_id');
                $filters['program_study_id'] = session('pimpinan_program_study_id');
            }
        }

        // Operator scope filtering
        if ($user->isOperator()) {
            $level = session('operator_level');
            
            if ($level === 'faculty') {
                $filters['faculty_id'] = session('operator_faculty_id');
            } elseif ($level === 'department') {
                $filters['faculty_id'] = session('operator_faculty_id');
                $filters['department_id'] = session('operator_department_id');
            } elseif ($level === 'program_study') {
                $filters['faculty_id'] = session('operator_faculty_id');
                $filters['department_id'] = session('operator_department_id');
                $filters['program_study_id'] = session('operator_program_study_id');
            }
        }

        return $filters;
    }

    /**
     * Get scope information for display
     */
    protected function getScopeInfo(): array
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return [
                'level' => 'university',
                'name' => 'Universitas Pattimura',
                'is_scoped' => false,
            ];
        }

        if ($user->isPimpinan()) {
            $level = session('pimpinan_level');
            return [
                'level' => $level,
                'name' => $this->getScopeName('pimpinan', $level),
                'is_scoped' => true,
                'is_read_only' => true,
            ];
        }

        if ($user->isOperator()) {
            $level = session('operator_level');
            return [
                'level' => $level,
                'name' => $this->getScopeName('operator', $level),
                'is_scoped' => true,
                'is_read_only' => false,
            ];
        }

        return [
            'level' => 'university',
            'name' => 'Universitas Pattimura',
            'is_scoped' => false,
        ];
    }

    /**
     * Get scope name for display
     */
    protected function getScopeName(string $roleType, string $level): string
    {
        $prefix = $roleType === 'pimpinan' ? 'pimpinan_' : 'operator_';

        if ($level === 'faculty') {
            return session($prefix . 'faculty_name', 'Fakultas');
        } elseif ($level === 'department') {
            return session($prefix . 'department_name', 'Jurusan');
        } elseif ($level === 'program_study') {
            return session($prefix . 'program_study_name', 'Program Studi');
        }

        return 'Universitas Pattimura';
    }

    protected function getStudentCount(array $filters): int
    {
        $query = DB::table('students');

        if (!empty($filters['faculty_id'])) {
            $query->where('faculty_id', $filters['faculty_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['program_study_id'])) {
            $query->where('program_study_id', $filters['program_study_id']);
        }

        return $query->count();
    }

    protected function getAchievementCount(array $filters): int
    {
        $query = DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->whereNull('student_achievements.deleted_at'); // Include soft-deleted check

        if (!empty($filters['faculty_id'])) {
            $query->where('students.faculty_id', $filters['faculty_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('students.department_id', $filters['department_id']);
        }

        if (!empty($filters['program_study_id'])) {
            $query->where('students.program_study_id', $filters['program_study_id']);
        }

        return $query->count();
    }

    protected function getPendingCount(array $filters): int
    {
        $query = DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->where('student_achievements.validation_status', 'Menunggu')
            ->whereNull('student_achievements.deleted_at'); // Exclude soft-deleted

        if (!empty($filters['faculty_id'])) {
            $query->where('students.faculty_id', $filters['faculty_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('students.department_id', $filters['department_id']);
        }

        if (!empty($filters['program_study_id'])) {
            $query->where('students.program_study_id', $filters['program_study_id']);
        }

        return $query->count();
    }

    protected function getApprovedCount(array $filters): int
    {
        $query = DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->where('student_achievements.validation_status', 'Disetujui')
            ->whereNull('student_achievements.deleted_at'); // Exclude soft-deleted

        if (!empty($filters['faculty_id'])) {
            $query->where('students.faculty_id', $filters['faculty_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('students.department_id', $filters['department_id']);
        }

        if (!empty($filters['program_study_id'])) {
            $query->where('students.program_study_id', $filters['program_study_id']);
        }

        return $query->count();
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
        $scopeFilters = $this->getScopeFilters();
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

        $query = DB::table('student_achievements')
            ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
            ->where('student_achievements.academic_period_id', $activePeriod->id)
            ->whereIn('student_achievements.validation_status', ['Disetujui', 'Ditolak', 'Revisi'])
            ->whereNull('student_achievements.deleted_at'); // Exclude soft-deleted

        // Apply scope filters
        if (!empty($scopeFilters['faculty_id'])) {
            $query->where('students.faculty_id', $scopeFilters['faculty_id']);
        }

        if (!empty($scopeFilters['department_id'])) {
            $query->where('students.department_id', $scopeFilters['department_id']);
        }

        if (!empty($scopeFilters['program_study_id'])) {
            $query->where('students.program_study_id', $scopeFilters['program_study_id']);
        }

        return $query->selectRaw("
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
