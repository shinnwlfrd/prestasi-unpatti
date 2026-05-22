<?php

namespace App\Services\Validator;

use App\Models\AcademicPeriod;
use App\Models\AchievementCategory;
use App\Models\ExecutiveSetting;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Services\SigapApiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ValidatorDashboardService
{
    private $departmentCache = [];

    /**
     * Get all dashboard data
     */
    public function getDashboardData(Request $request)
    {
        $scope = $this->getDashboardScope();
        $user = $scope['user'];
        $isPimpinan = $scope['isPimpinan'] ? '1' : '0';
        $level = $scope['level'] ?? 'none';
        $facultyId = $scope['facultyId'] ?? 'none';
        $departmentId = $scope['departmentId'] ?? 'none';
        $programStudyId = $scope['programStudyId'] ?? 'none';
        $position = $scope['position'] ?? 'none';

        $selectedPeriods = $this->getSelectedPeriods($request);
        $periodsKey = implode('-', $selectedPeriods) ?: 'none';

        $userId = $user ? $user->id : 'guest';
        $contextString = "{$userId}_{$isPimpinan}_{$level}_{$facultyId}_{$departmentId}_{$programStudyId}_{$position}_{$periodsKey}";
        $contextHash = md5($contextString);
        $version = StudentAchievement::getDashboardCacheVersion();
        $cacheKey = "validator_dashboard_{$contextHash}_v{$version}";

        return Cache::remember($cacheKey, 600, function () use ($request, $scope, $isPimpinan, $selectedPeriods) {
            $allPeriods = AcademicPeriod::orderBy('start_date', 'desc')->get();

            $studentsQuery = Student::query();
            $achievementsQuery = StudentAchievement::query();

            $this->applyScopeFilters($studentsQuery, $scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId']);
            $this->applyScopeFilters($achievementsQuery, $scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId']);

            if (! empty($selectedPeriods)) {
                $achievementsQuery->whereIn('academic_period_id', $selectedPeriods);
            }

            $totalStudents = $studentsQuery->count();
            $totalAchievements = $achievementsQuery->clone()
                ->when($isPimpinan, fn ($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
                ->count();

            $kpiData = $this->getKpiStats($totalStudents, $totalAchievements, $selectedPeriods, $isPimpinan, $scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId']);

            $levelDist = $this->getLevelDistribution($achievementsQuery, $isPimpinan);
            $regionalKpis = $this->getNationalKpis($totalAchievements, $levelDist);
            $activeUnitsData = $this->getActiveUnitsKpi($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $selectedPeriods);

            $hierarchy = $this->getHierarchyStats($scope, $isPimpinan, $selectedPeriods);
            $trends = $this->getTrendStats($scope, $achievementsQuery, $isPimpinan, $selectedPeriods);

            $riskIndicators = $this->getRiskIndicators($isPimpinan, $hierarchy['comparison'], $selectedPeriods, $kpiData['growth'], $scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $activeUnitsData['totalCount'], $activeUnitsData['activeCount']);
            $validatorData = $this->getValidatorData($request, $achievementsQuery, $isPimpinan);

            return [
                'totalStudents' => $totalStudents,
                'totalAchievements' => $totalAchievements,
                'urgentPending' => $kpiData['urgentPending'],
                'statusStats' => $this->getStatusStats($achievementsQuery),
                'recentActivities' => $trends['activities'],
                'topStudents' => $trends['top_students'],
                'topGpaByAngkatan' => $trends['top_gpa'],
                'achievementsByAngkatan' => $trends['by_angkatan'],
                'categoryDistribution' => $this->getCategoryDistribution($achievementsQuery, $isPimpinan),
                'levelDistribution' => $levelDist,
                'categoryByHierarchy' => $hierarchy['category'],
                'levelByHierarchy' => $hierarchy['level'],
                'hierarchicalComparison' => $hierarchy['comparison'],
                'achievementsPerPeriod' => $trends['per_period'],
                'hierarchicalData' => $hierarchy['comparison'],
                'allPeriods' => $allPeriods,
                'selectedPeriods' => $selectedPeriods,
                'level' => $scope['level'],
                'position' => $scope['position'],
                'positionLabel' => $scope['positionLabel'],
                'scopeName' => $scope['scopeName'],
                'isPimpinan' => $isPimpinan,
                'pendingAchievements' => $validatorData['pendingAchievements'],
                'categories' => $validatorData['categories'],
                'levels' => $validatorData['levels'],
                'routePrefix' => $isPimpinan ? 'pimpinan' : 'validator',
                'achievementRatio' => $kpiData['ratio'],
                'achievementGrowth' => $kpiData['growth'],
                'nationalPercentage' => $regionalKpis['nationalPercentage'],
                'internationalPercentage' => $regionalKpis['internationalPercentage'],
                'activeUnitsCount' => $activeUnitsData['activeCount'],
                'totalUnitsCount' => $activeUnitsData['totalCount'],
                'unitLabel' => $activeUnitsData['label'],
                'efficiencyRanking' => $hierarchy['efficiency'],
                'achievementTrend' => $trends['trend'],
                'riskIndicators' => $riskIndicators,
            ];
        });
    }

    public function getHierarchyStats($scope, $isPimpinan, $selectedPeriods)
    {
        $data = ['comparison' => [], 'category' => [], 'level' => [], 'efficiency' => collect()];
        if ($isPimpinan) {
            $data['comparison'] = $this->getHierarchicalComparison($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $selectedPeriods);
            if (! empty($data['comparison'])) {
                $data['category'] = $this->getCategoryByHierarchy($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $selectedPeriods);
                $data['level'] = $this->getLevelByHierarchy($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $selectedPeriods);
                $data['efficiency'] = $this->calculateEfficiencyRanking($data['comparison']);
            }
        }

        return $data;
    }

    public function getTrendStats($scope, $achievementsQuery, $isPimpinan, $selectedPeriods)
    {
        return [
            'activities' => $this->getRecentActivities($achievementsQuery, $isPimpinan),
            'top_students' => $this->getTopStudents($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $selectedPeriods, $isPimpinan),
            'top_gpa' => $this->getTopGpaByAngkatan($scope['level'], $scope['programStudyId']),
            'by_angkatan' => $this->getAchievementsByAngkatan($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $selectedPeriods, $isPimpinan),
            'per_period' => $this->getAchievementsPerPeriod($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $selectedPeriods, $isPimpinan),
            'trend' => $this->getAchievementTrend($scope['level'], $scope['facultyId'], $scope['departmentId'], $scope['programStudyId'], $isPimpinan),
        ];
    }

    public function getHierarchicalComparison($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $data = [];

        if ($level === 'university') {
            $data['type'] = 'faculty';
            $data['label'] = 'Perbandingan Prestasi Antar Fakultas';

            $nameMap = app(SigapApiService::class)->getUnitNameMap();

            $data['items'] = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->get()
                ->map(function ($item) use ($selectedPeriods, $nameMap) {
                    $item->faculty = $nameMap[$item->faculty_id] ?? $item->faculty;
                    $item->students_count = Student::where('faculty_id', $item->faculty_id)->count();
                    $item->achievements_count = StudentAchievement::query()
                        ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                        ->whereHas('student', function ($q) use ($item) {
                            $q->where('faculty_id', $item->faculty_id);
                        })
                        ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();

                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();

            if ($data['items']->isEmpty()) {
                $data['items'] = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                    ->whereNotNull('faculty_id')
                    ->groupBy('faculty_id')
                    ->get()
                    ->map(function ($item) use ($nameMap) {
                        $item->faculty = $nameMap[$item->faculty_id] ?? $item->faculty;
                        $item->students_count = Student::where('faculty_id', $item->faculty_id)->count();
                        $item->achievements_count = 0;

                        return $item;
                    });
            }
        } elseif ($level === 'faculty') {
            $data['type'] = 'department';
            $data['label'] = 'Perbandingan Prestasi Antar Jurusan';
            $nameMap = app(SigapApiService::class)->getUnitNameMap();

            $data['items'] = Student::where('faculty_id', $facultyId)
                ->select('department_id', DB::raw('MAX(department) as department'))
                ->groupBy('department_id')
                ->get()
                ->map(function ($item) use ($selectedPeriods, $nameMap) {
                    $item->department = $nameMap[$item->department_id] ?? $item->department;
                    $item->students_count = Student::where('department_id', $item->department_id)->count();
                    $item->achievements_count = StudentAchievement::query()
                        ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                        ->whereHas('student', function ($q) use ($item) {
                            $q->where('department_id', $item->department_id);
                        })
                        ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();

                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();

            if ($data['items']->isEmpty()) {
                $data['items'] = Student::where('faculty_id', $facultyId)
                    ->select('department_id', DB::raw('MAX(department) as department'))
                    ->groupBy('department_id')
                    ->get()
                    ->map(function ($item) use ($nameMap) {
                        $item->department = $nameMap[$item->department_id] ?? $item->department;
                        $item->students_count = Student::where('department_id', $item->department_id)->count();
                        $item->achievements_count = 0;

                        return $item;
                    });
            }
        } elseif ($level === 'department') {
            $data['type'] = 'program_study';
            $data['label'] = 'Perbandingan Prestasi Antar Program Studi';
            $nameMap = app(SigapApiService::class)->getUnitNameMap();

            $data['items'] = Student::where('department_id', $departmentId)
                ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                ->groupBy('program_study_id')
                ->get()
                ->map(function ($item) use ($selectedPeriods, $nameMap) {
                    $item->program_study = $nameMap[$item->program_study_id] ?? $item->program_study;
                    $item->students_count = Student::where('program_study_id', $item->program_study_id)->count();
                    $item->achievements_count = StudentAchievement::query()
                        ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                        ->whereHas('student', function ($q) use ($item) {
                            $q->where('program_study_id', $item->program_study_id);
                        })
                        ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();

                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();

            if ($data['items']->isEmpty()) {
                $data['items'] = Student::where('department_id', $departmentId)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get()
                    ->map(function ($item) use ($nameMap) {
                        $item->program_study = $nameMap[$item->program_study_id] ?? $item->program_study;
                        $item->students_count = Student::where('program_study_id', $item->program_study_id)->count();
                        $item->achievements_count = 0;

                        return $item;
                    });
            }
        } elseif ($level === 'program_study') {
            $data['type'] = 'angkatan';
            $data['label'] = 'Perbandingan Prestasi Antar Angkatan';

            $data['items'] = Student::where('program_study_id', $programStudyId)
                ->select('angkatan')
                ->whereNotNull('angkatan')
                ->groupBy('angkatan')
                ->orderBy('angkatan', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) use ($selectedPeriods, $programStudyId) {
                    $item->students_count = Student::where('program_study_id', $programStudyId)
                        ->where('angkatan', $item->angkatan)
                        ->count();
                    $item->achievements_count = StudentAchievement::query()
                        ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                        ->whereHas('student', function ($q) use ($item, $programStudyId) {
                            $q->where('program_study_id', $programStudyId)
                                ->where('angkatan', $item->angkatan);
                        })
                        ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();
                    $item->angkatan_label = 'Angkatan '.$item->angkatan;

                    return $item;
                })
                ->sortByDesc('angkatan')
                ->values();
        } elseif ($level === 'graduate_program') {
            $data['type'] = 'program_study';
            $data['label'] = 'Perbandingan Prestasi Antar Program Pascasarjana';
            $nameMap = app(SigapApiService::class)->getUnitNameMap();

            $data['items'] = Student::where('faculty', 'Program Pascasarjana')
                ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                ->groupBy('program_study_id')
                ->get()
                ->map(function ($item) use ($selectedPeriods, $nameMap) {
                    $item->program_study = $nameMap[$item->program_study_id] ?? $item->program_study;
                    $item->students_count = Student::where('program_study_id', $item->program_study_id)->count();
                    $item->achievements_count = StudentAchievement::query()
                        ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                        ->whereHas('student', function ($q) use ($item) {
                            $q->where('program_study_id', $item->program_study_id);
                        })
                        ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();

                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();
        }

        if (empty($data)) {
            $data = [
                'type' => 'none',
                'label' => 'Ringkasan Capaian Program Studi',
                'items' => collect([]),
            ];
        }

        return $data;
    }

    public function getCategoryByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $data = [];
        $nameMap = app(SigapApiService::class)->getUnitNameMap();

        if ($level === 'university') {
            $faculties = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->get();

            foreach ($faculties as $faculty) {
                $data[$faculty->faculty_id] = [
                    'name' => $nameMap[$faculty->faculty_id] ?? $faculty->faculty,
                    'categories' => $this->getCategoryDistributionForScope('faculty', $faculty->faculty_id, null, null, $selectedPeriods),
                    'departments' => [],
                ];

                $departments = Student::where('faculty_id', $faculty->faculty_id)
                    ->select('department_id', DB::raw('MAX(department) as department'))
                    ->groupBy('department_id')
                    ->get();

                foreach ($departments as $dept) {
                    $data[$faculty->faculty_id]['departments'][$dept->department_id] = [
                        'name' => $nameMap[$dept->department_id] ?? $dept->department,
                        'categories' => $this->getCategoryDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                        'program_studies' => [],
                    ];

                    $programs = Student::where('department_id', $dept->department_id)
                        ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                        ->groupBy('program_study_id')
                        ->get();

                    foreach ($programs as $prog) {
                        $data[$faculty->faculty_id]['departments'][$dept->department_id]['program_studies'][$prog->program_study_id] = [
                            'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                            'categories' => $this->getCategoryDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods),
                        ];
                    }
                }
            }
        } elseif ($level === 'faculty') {
            $departments = Student::where('faculty_id', $facultyId)
                ->select('department_id', DB::raw('MAX(department) as department'))
                ->groupBy('department_id')
                ->get();

            foreach ($departments as $dept) {
                $data[$dept->department_id] = [
                    'name' => $nameMap[$dept->department_id] ?? $dept->department,
                    'categories' => $this->getCategoryDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                    'program_studies' => [],
                ];

                $programs = Student::where('department_id', $dept->department_id)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get();

                foreach ($programs as $prog) {
                    $data[$dept->department_id]['program_studies'][$prog->program_study_id] = [
                        'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                        'categories' => $this->getCategoryDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods),
                    ];
                }
            }
        } elseif ($level === 'department') {
            $programs = Student::where('department_id', $departmentId)
                ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                ->groupBy('program_study_id')
                ->get();

            foreach ($programs as $prog) {
                $data[$prog->program_study_id] = [
                    'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                    'categories' => $this->getCategoryDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods),
                ];
            }
        }

        return $data;
    }

    public function getLevelByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $data = [];
        $nameMap = app(SigapApiService::class)->getUnitNameMap();

        if ($level === 'university') {
            $faculties = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->get();

            foreach ($faculties as $faculty) {
                $data[$faculty->faculty_id] = [
                    'name' => $nameMap[$faculty->faculty_id] ?? $faculty->faculty,
                    'levels' => $this->getLevelDistributionForScope('faculty', $faculty->faculty_id, null, null, $selectedPeriods),
                    'departments' => [],
                ];

                $departments = Student::where('faculty_id', $faculty->faculty_id)
                    ->select('department_id', DB::raw('MAX(department) as department'))
                    ->groupBy('department_id')
                    ->get();

                foreach ($departments as $dept) {
                    $data[$faculty->faculty_id]['departments'][$dept->department_id] = [
                        'name' => $nameMap[$dept->department_id] ?? $dept->department,
                        'levels' => $this->getLevelDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                        'program_studies' => [],
                    ];

                    $programs = Student::where('department_id', $dept->department_id)
                        ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                        ->groupBy('program_study_id')
                        ->get();

                    foreach ($programs as $prog) {
                        $data[$faculty->faculty_id]['departments'][$dept->department_id]['program_studies'][$prog->program_study_id] = [
                            'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                            'levels' => $this->getLevelDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods),
                        ];
                    }
                }
            }
        } elseif ($level === 'faculty') {
            $departments = Student::where('faculty_id', $facultyId)
                ->select('department_id', DB::raw('MAX(department) as department'))
                ->groupBy('department_id')
                ->get();

            foreach ($departments as $dept) {
                $data[$dept->department_id] = [
                    'name' => $nameMap[$dept->department_id] ?? $dept->department,
                    'levels' => $this->getLevelDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                    'program_studies' => [],
                ];

                $programs = Student::where('department_id', $dept->department_id)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get();

                foreach ($programs as $prog) {
                    $data[$dept->department_id]['program_studies'][$prog->program_study_id] = [
                        'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                        'levels' => $this->getLevelDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods),
                    ];
                }
            }
        } elseif ($level === 'department') {
            $programs = Student::where('department_id', $departmentId)
                ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                ->groupBy('program_study_id')
                ->get();

            foreach ($programs as $prog) {
                $data[$prog->program_study_id] = [
                    'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                    'levels' => $this->getLevelDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods),
                ];
            }
        }

        return $data;
    }

    public function getCategoryDistributionForScope($scopeType, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $query = StudentAchievement::query()
            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
            ->whereHas('student', function ($q) use ($scopeType, $facultyId, $departmentId, $programStudyId) {
                if ($scopeType === 'faculty' && $facultyId) {
                    $q->where('faculty_id', $facultyId);
                } elseif ($scopeType === 'department' && $departmentId) {
                    $q->where('department_id', $departmentId);
                } elseif ($scopeType === 'program_study' && $programStudyId) {
                    $q->where('program_study_id', $programStudyId);
                }
            });

        if (! empty($selectedPeriods)) {
            $query->whereIn('academic_period_id', $selectedPeriods);
        }

        return $query->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->select('achievement_categories.name', DB::raw('count(*) as total'))
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function getLevelDistributionForScope($scopeType, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $query = StudentAchievement::query()
            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
            ->whereHas('student', function ($q) use ($scopeType, $facultyId, $departmentId, $programStudyId) {
                if ($scopeType === 'faculty' && $facultyId) {
                    $q->where('faculty_id', $facultyId);
                } elseif ($scopeType === 'department' && $departmentId) {
                    $q->where('department_id', $departmentId);
                } elseif ($scopeType === 'program_study' && $programStudyId) {
                    $q->where('program_study_id', $programStudyId);
                }
            });

        if (! empty($selectedPeriods)) {
            $query->whereIn('academic_period_id', $selectedPeriods);
        }

        return $query->select('student_achievements.level as name', DB::raw('count(*) as total'))
            ->groupBy('student_achievements.level')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function getHierarchicalChartData(array $filters)
    {
        $level = $filters['level'];
        $facultyId = $filters['faculty_id'] ?? null;
        $departmentId = $filters['department_id'] ?? null;
        $programStudyId = $filters['program_study_id'] ?? null;
        $selectedPeriods = $filters['periods'] ?? [];
        $drillLevel = $filters['drill_level'] ?? 'main';
        $parentId = $filters['parent_id'] ?? null;

        $data = [];
        $labels = [];
        $values = [];
        $drillData = [];

        $nameMap = app(SigapApiService::class)->getUnitNameMap();

        if ($drillLevel === 'main') {
            if ($level === 'university') {
                $items = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                    ->whereNotNull('faculty_id')
                    ->groupBy('faculty_id')
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $nameMap) {
                        $item->faculty = $nameMap[$item->faculty_id] ?? $item->faculty;
                        $item->students_count = Student::where('faculty_id', $item->faculty_id)->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item) {
                                $q->where('faculty_id', $item->faculty_id);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        return $item;
                    })
                    ->sortByDesc('achievements_count')
                    ->values();

                foreach ($items as $item) {
                    $labels[] = $item->faculty;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->faculty_id,
                        'name' => $item->faculty,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = 'Perbandingan Prestasi Antar Fakultas';
                $data['canDrillDown'] = true;
                $data['nextLevel'] = 'department';
            } elseif ($level === 'faculty') {
                $items = Student::where('faculty_id', $facultyId)
                    ->select('department_id', DB::raw('MAX(department) as department'))
                    ->groupBy('department_id')
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $nameMap) {
                        $item->department = $nameMap[$item->department_id] ?? $item->department;
                        $item->students_count = Student::where('department_id', $item->department_id)->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item) {
                                $q->where('department_id', $item->department_id);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        return $item;
                    })
                    ->sortByDesc('achievements_count')
                    ->values();

                foreach ($items as $item) {
                    $labels[] = $item->department;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->department_id,
                        'name' => $item->department,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = 'Perbandingan Prestasi Antar Jurusan';
                $data['canDrillDown'] = true;
                $data['nextLevel'] = 'program_study';
            } elseif ($level === 'department') {
                $items = Student::where('department_id', $departmentId)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $nameMap) {
                        $item->program_study = $nameMap[$item->program_study_id] ?? $item->program_study;
                        $item->students_count = Student::where('program_study_id', $item->program_study_id)->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item) {
                                $q->where('program_study_id', $item->program_study_id);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        return $item;
                    })
                    ->sortByDesc('achievements_count')
                    ->values();

                foreach ($items as $item) {
                    $labels[] = $item->program_study;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->program_study_id,
                        'name' => $item->program_study,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = 'Perbandingan Prestasi Antar Program Studi';
                $data['canDrillDown'] = false;
            } elseif ($level === 'program_study') {
                $items = Student::where('program_study_id', $programStudyId)
                    ->select('angkatan')
                    ->whereNotNull('angkatan')
                    ->groupBy('angkatan')
                    ->orderBy('angkatan', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $programStudyId) {
                        $item->students_count = Student::where('program_study_id', $programStudyId)
                            ->where('angkatan', $item->angkatan)
                            ->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item, $programStudyId) {
                                $q->where('program_study_id', $programStudyId)
                                    ->where('angkatan', $item->angkatan);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();
                        $item->name = 'Angkatan '.$item->angkatan;

                        return $item;
                    })
                    ->sortByDesc('angkatan')
                    ->values();

                foreach ($items as $item) {
                    $labels[] = $item->name;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->angkatan,
                        'name' => $item->name,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = 'Perbandingan Prestasi Antar Angkatan';
                $data['canDrillDown'] = false;
            } elseif ($level === 'graduate_program') {
                $items = Student::where('faculty', 'Program Pascasarjana')
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $nameMap) {
                        $item->program_study = $nameMap[$item->program_study_id] ?? $item->program_study;
                        $item->students_count = Student::where('program_study_id', $item->program_study_id)->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item) {
                                $q->where('program_study_id', $item->program_study_id);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        return $item;
                    })
                    ->sortByDesc('achievements_count')
                    ->values();

                foreach ($items as $item) {
                    $labels[] = $item->program_study;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->program_study_id,
                        'name' => $item->program_study,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = 'Perbandingan Prestasi Antar Program Pascasarjana';
                $data['canDrillDown'] = false;
            }
        } elseif ($drillLevel === 'level1') {
            if ($level === 'university') {
                $items = Student::where('faculty_id', $parentId)
                    ->select('department_id', DB::raw('MAX(department) as department'))
                    ->groupBy('department_id')
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $nameMap) {
                        $item->department = $nameMap[$item->department_id] ?? $item->department;
                        $item->students_count = Student::where('department_id', $item->department_id)->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item) {
                                $q->where('department_id', $item->department_id);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        return $item;
                    })
                    ->sortByDesc('achievements_count')
                    ->values();

                $facultyName = $nameMap[$parentId] ?? Student::where('faculty_id', $parentId)->value('faculty');

                foreach ($items as $item) {
                    $labels[] = $item->department;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->department_id,
                        'name' => $item->department,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = "Distribusi Prestasi per Jurusan - {$facultyName}";
                $data['canDrillDown'] = true;
                $data['nextLevel'] = 'program_study';
            } elseif ($level === 'faculty') {
                $items = Student::where('department_id', $parentId)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $nameMap) {
                        $item->program_study = $nameMap[$item->program_study_id] ?? $item->program_study;
                        $item->students_count = Student::where('program_study_id', $item->program_study_id)->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item) {
                                $q->where('program_study_id', $item->program_study_id);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        return $item;
                    })
                    ->sortByDesc('achievements_count')
                    ->values();

                $departmentName = $nameMap[$parentId] ?? Student::where('department_id', $parentId)->value('department');

                foreach ($items as $item) {
                    $labels[] = $item->program_study;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->program_study_id,
                        'name' => $item->program_study,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = "Distribusi Prestasi per Program Studi - {$departmentName}";
                $data['canDrillDown'] = false;
            }
        } elseif ($drillLevel === 'level2') {
            if ($level === 'university') {
                $items = Student::where('department_id', $parentId)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get()
                    ->map(function ($item) use ($selectedPeriods, $nameMap) {
                        $item->program_study = $nameMap[$item->program_study_id] ?? $item->program_study;
                        $item->students_count = Student::where('program_study_id', $item->program_study_id)->count();
                        $item->achievements_count = StudentAchievement::query()
                            ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                            ->whereHas('student', function ($q) use ($item) {
                                $q->where('program_study_id', $item->program_study_id);
                            })
                            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        return $item;
                    })
                    ->sortByDesc('achievements_count')
                    ->values();

                $departmentName = $nameMap[$parentId] ?? Student::where('department_id', $parentId)->value('department');

                foreach ($items as $item) {
                    $labels[] = $item->program_study;
                    $values[] = $item->achievements_count;
                    $drillData[] = [
                        'id' => $item->program_study_id,
                        'name' => $item->program_study,
                        'value' => $item->achievements_count,
                        'students_count' => $item->students_count,
                    ];
                }
                $data['title'] = "Distribusi Prestasi per Program Studi - {$departmentName}";
                $data['canDrillDown'] = false;
            }
        }

        $data['labels'] = $labels;
        $data['values'] = $values;
        $data['drillData'] = $drillData;
        $data['currentLevel'] = $drillLevel;

        return $data;
    }

    public function getEventParticipantsData($eventName, $organizer, $eventLevel)
    {
        $scope = $this->getDashboardScope();
        $isPimpinan = $scope['isPimpinan'];
        $scopeLevel = $scope['level'];
        $facultyId = $scope['facultyId'];
        $departmentId = $scope['departmentId'];
        $programStudyId = $scope['programStudyId'];

        $participants = StudentAchievement::query()
            ->with(['student'])
            ->where('event_name', $eventName)
            ->where('organizer', $organizer)
            ->where('level', $eventLevel)
            ->when($isPimpinan, fn ($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->when($scopeLevel === 'faculty' && $facultyId, function ($q) use ($facultyId) {
                $q->whereHas('student', fn ($sq) => $sq->where('faculty_id', $facultyId));
            })
            ->when($scopeLevel === 'department' && $departmentId, function ($q) use ($departmentId) {
                $q->whereHas('student', fn ($sq) => $sq->where('department_id', $departmentId));
            })
            ->when($scopeLevel === 'program_study' && $programStudyId, function ($q) use ($programStudyId) {
                $q->whereHas('student', fn ($sq) => $sq->where('program_study_id', $programStudyId));
            })
            ->when($scopeLevel === 'graduate_program', function ($q) {
                $q->whereHas('student', fn ($sq) => $sq->where('faculty', 'Program Pascasarjana'));
            })
            ->get()
            ->map(fn ($sa) => $this->mapParticipantData($sa));

        return [
            'event_name' => $eventName,
            'organizer' => $organizer,
            'level' => $eventLevel,
            'participants' => $participants,
        ];
    }

    private function mapParticipantData($sa): array
    {
        $role = auth()->user()->getCurrentRole()->role;

        return [
            'student_id' => $sa->student_id,
            'student_name' => $sa->student->name ?? 'N/A',
            'program_study' => $sa->student->program_study ?? 'N/A',
            'faculty' => $sa->student->faculty ?? 'N/A',
            'submitted_at' => $sa->submitted_at ? $sa->submitted_at->format('d M Y') : 'N/A',
            'validation_status' => $sa->validation_status,
            'details_url' => route($role.'.students.show', $sa->student_id),
        ];
    }

    public function getDashboardScope()
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
        $isPimpinanRoute = request()->routeIs('pimpinan.*');

        $prefix = $isPimpinanRoute ? 'pimpinan' : 'operator';

        $level = $currentRole ? $currentRole->level : (session("{$prefix}_level") ?? session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session("{$prefix}_faculty_id") ?? session('operator_faculty_id') ?? session('pimpinan_faculty_id'));
        $departmentId = $currentRole ? $currentRole->department_id : (session("{$prefix}_department_id") ?? session('operator_department_id') ?? session('pimpinan_department_id'));
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session("{$prefix}_program_study_id") ?? session('operator_program_study_id') ?? session('pimpinan_program_study_id'));
        $position = $currentRole ? $currentRole->position : session('pimpinan_position');

        $facultyId = $facultyId ?: null;
        $departmentId = $departmentId ?: null;
        $programStudyId = $programStudyId ?: null;

        $positionLabels = [
            'rektor' => 'Rektor',
            'wakil_rektor' => 'Wakil Rektor',
            'dekan' => 'Dekan',
            'wakil_dekan' => 'Wakil Dekan',
            'ketua_jurusan' => 'Ketua Jurusan',
            'sekretaris_jurusan' => 'Sekretaris Jurusan',
            'kaprodi' => 'Ketua Program Studi',
            'sekprodi' => 'Sekretaris Program Studi',
            'direktur_pps' => 'Direktur Pascasarjana',
            'super_admin' => 'Super Admin',
        ];
        $positionLabel = $positionLabels[$position] ?? ucfirst(str_replace('_', ' ', $position ?? ''));

        $scopeName = '';
        if ($level === 'university' || $position === 'super_admin') {
            $scopeName = 'Universitas Pattimura';
        } elseif ($level === 'graduate_program') {
            $scopeName = 'Program Pascasarjana';
        } else {
            $scopeName = session("{$prefix}_{$level}_name") ?? ucfirst($level);
        }

        return compact('user', 'isPimpinan', 'level', 'facultyId', 'departmentId', 'programStudyId', 'position', 'positionLabel', 'scopeName');
    }

    public function getSelectedPeriods(Request $request)
    {
        $selectedPeriods = $request->input('periods', []);
        if (empty($selectedPeriods)) {
            $activePeriod = AcademicPeriod::where('is_active', true)->first();
            if ($activePeriod) {
                return [$activePeriod->id];
            }
        }

        return $selectedPeriods;
    }

    public function applyScopeFilters($query, $level, $facultyId, $departmentId, $programStudyId)
    {
        $isAchievementQuery = $query->getModel() instanceof StudentAchievement;

        if ($level === 'faculty' && $facultyId) {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn ($q) => $q->where('faculty_id', $facultyId));
            } else {
                $query->where('faculty_id', $facultyId);
            }
        } elseif ($level === 'department' && $departmentId) {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn ($q) => $q->where('department_id', $departmentId));
            } else {
                $query->where('department_id', $departmentId);
            }
        } elseif ($level === 'program_study' && $programStudyId) {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn ($q) => $q->where('program_study_id', $programStudyId));
            } else {
                $query->where('program_study_id', $programStudyId);
            }
        } elseif ($level === 'graduate_program') {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn ($q) => $q->where('faculty', 'Program Pascasarjana'));
            } else {
                $query->where('faculty', 'Program Pascasarjana');
            }
        }
    }

    public function getKpiStats($totalStudents, $totalAchievements, $selectedPeriods, $isPimpinan, $level, $facultyId, $departmentId, $programStudyId)
    {
        $ratio = $totalStudents > 0 ? $totalAchievements / $totalStudents : 0;
        $growth = 0;
        $urgentPending = 0;

        if (! empty($selectedPeriods)) {
            $earliestPeriod = AcademicPeriod::whereIn('id', $selectedPeriods)->orderBy('start_date', 'asc')->first();

            if ($earliestPeriod) {
                $previousPeriod = AcademicPeriod::where('end_date', '<', $earliestPeriod->start_date)
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($previousPeriod) {
                    $achievementsPrevious = StudentAchievement::query()
                        ->where('academic_period_id', $previousPeriod->id)
                        ->when($isPimpinan, fn ($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
                        ->whereHas('student', function ($q) use ($level, $facultyId, $departmentId, $programStudyId) {
                            if ($level === 'faculty' && $facultyId) {
                                $q->where('faculty_id', $facultyId);
                            } elseif ($level === 'department' && $departmentId) {
                                $q->where('department_id', $departmentId);
                            } elseif ($level === 'program_study' && $programStudyId) {
                                $q->where('program_study_id', $programStudyId);
                            }
                        })
                        ->count();

                    if ($achievementsPrevious > 0) {
                        $growth = (($totalAchievements - $achievementsPrevious) / $achievementsPrevious) * 100;
                    } else {
                        $growth = $totalAchievements > 0 ? 100 : 0;
                    }
                }
            }
        }

        if (! $isPimpinan) {
            $urgentPending = StudentAchievement::query()
                ->where('validation_status', 'submitted')
                ->where('submitted_at', '<', now()->subDays(7))
                ->whereHas('student', function ($q) use ($level, $facultyId, $departmentId, $programStudyId) {
                    $this->applyScopeFilters($q, $level, $facultyId, $departmentId, $programStudyId);
                })
                ->count();
        }

        return ['ratio' => $ratio, 'growth' => $growth, 'urgentPending' => $urgentPending];
    }

    public function getStatusStats($query)
    {
        return $query->clone()
            ->select('validation_status', DB::raw('count(*) as total'))
            ->groupBy('validation_status')
            ->pluck('total', 'validation_status')
            ->toArray();
    }

    public function getCategoryDistribution($query, $isPimpinan)
    {
        return $query->clone()
            ->when($isPimpinan, fn ($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->select('achievement_categories.name', DB::raw('count(*) as total'))
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function getLevelDistribution($query, $isPimpinan)
    {
        return $query->clone()
            ->when($isPimpinan, fn ($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->select('student_achievements.level as name', DB::raw('count(*) as total'))
            ->groupBy('student_achievements.level')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function getNationalKpis($totalAchievements, $levelDistribution)
    {
        $nationalCount = $levelDistribution->where('name', 'Nasional')->first()?->total ?? 0;
        $internationalCount = $levelDistribution->where('name', 'Internasional')->first()?->total ?? 0;

        return [
            'nationalPercentage' => $totalAchievements > 0 ? ($nationalCount / $totalAchievements) * 100 : 0,
            'internationalPercentage' => $totalAchievements > 0 ? ($internationalCount / $totalAchievements) * 100 : 0,
        ];
    }

    public function getActiveUnitsKpi($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $activeCount = 0;
        $totalCount = 0;
        $label = 'Unit';

        if ($level === 'university') {
            $label = 'Fakultas';
            $totalCount = Student::whereNotNull('faculty_id')->distinct('faculty_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(! empty($selectedPeriods), fn ($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn ($q) => $q->whereNotNull('faculty_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.faculty_id')
                ->count('students.faculty_id');
        } elseif ($level === 'faculty') {
            $label = 'Jurusan';
            $totalCount = Student::where('faculty_id', $facultyId)->whereNotNull('department_id')->distinct('department_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(! empty($selectedPeriods), fn ($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn ($q) => $q->where('faculty_id', $facultyId)->whereNotNull('department_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.department_id')
                ->count('students.department_id');
        } elseif ($level === 'department') {
            $label = 'Prodi';
            $totalCount = Student::where('department_id', $departmentId)->whereNotNull('program_study_id')->distinct('program_study_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(! empty($selectedPeriods), fn ($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn ($q) => $q->where('department_id', $departmentId)->whereNotNull('program_study_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.program_study_id')
                ->count('students.program_study_id');
        } elseif ($level === 'program_study') {
            $label = 'Angkatan';
            $totalCount = Student::where('program_study_id', $programStudyId)->whereNotNull('angkatan')->distinct('angkatan')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(! empty($selectedPeriods), fn ($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn ($q) => $q->where('program_study_id', $programStudyId)->whereNotNull('angkatan'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.angkatan')
                ->count('students.angkatan');
        } elseif ($level === 'graduate_program') {
            $label = 'Prodi PPs';
            $totalCount = Student::where('faculty', 'Program Pascasarjana')->whereNotNull('program_study_id')->distinct('program_study_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(! empty($selectedPeriods), fn ($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn ($q) => $q->where('faculty', 'Program Pascasarjana')->whereNotNull('program_study_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.program_study_id')
                ->count('students.program_study_id');
        }

        return compact('activeCount', 'totalCount', 'label');
    }

    public function calculateEfficiencyRanking($hierarchicalComparison)
    {
        return $hierarchicalComparison['items']->map(function ($item) {
            $ratio = $item->students_count > 0 ? ($item->achievements_count / $item->students_count) : 0;

            return [
                'name' => $item->faculty ?? $item->department ?? $item->program_study ?? $item->angkatan_label ?? $item->angkatan ?? 'Unknown',
                'ratio' => $ratio,
                'achievements' => $item->achievements_count,
                'students' => $item->students_count,
            ];
        })->sortByDesc('ratio')->values()->take(10);
    }

    public function getRecentActivities($query, $isPimpinan)
    {
        return $query->clone()
            ->select(
                'event_name',
                'organizer',
                'level',
                DB::raw('MAX(submitted_at) as latest_submission'),
                DB::raw('COUNT(DISTINCT student_id) as participant_count'),
                DB::raw('MIN(sa_id) as first_achievement_id')
            )
            ->when($isPimpinan, fn ($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->groupBy('event_name', 'organizer', 'level')
            ->orderBy('latest_submission', 'desc')
            ->limit(5)
            ->get();
    }

    public function getTopStudents($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan)
    {
        $query = Student::query();
        $this->applyScopeFilters($query, $level, $facultyId, $departmentId, $programStudyId);

        return $query->withCount([
            'achievements' => function ($q) use ($selectedPeriods, $isPimpinan) {
                if ($isPimpinan) {
                    $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
                }
                if (! empty($selectedPeriods)) {
                    $q->whereIn('academic_period_id', $selectedPeriods);
                }
            },
        ])
            ->orderBy('achievements_count', 'desc')
            ->limit(5)
            ->get();
    }

    public function getTopGpaByAngkatan($level, $programStudyId)
    {
        $topGpaByAngkatan = [];
        if ($level === 'program_study' && $programStudyId) {
            $angkatanList = Student::where('program_study_id', $programStudyId)
                ->select('angkatan')
                ->distinct()
                ->orderBy('angkatan', 'desc')
                ->limit(3)
                ->pluck('angkatan');

            foreach ($angkatanList as $angkatan) {
                $topGpaValues = Student::where('program_study_id', $programStudyId)
                    ->where('angkatan', $angkatan)
                    ->whereNotNull('gpa')
                    ->where('gpa', '>', 0)
                    ->select('gpa')
                    ->distinct()
                    ->orderBy('gpa', 'desc')
                    ->limit(3)
                    ->pluck('gpa');

                $thirdGpa = $topGpaValues->get(2) ?? 0;

                $topGpaByAngkatan[$angkatan] = Student::where('program_study_id', $programStudyId)
                    ->where('angkatan', $angkatan)
                    ->whereNotNull('gpa')
                    ->where('gpa', '>=', $thirdGpa)
                    ->where('gpa', '>', 0)
                    ->orderBy('gpa', 'desc')
                    ->orderBy('name', 'asc')
                    ->get();
            }
        }

        return $topGpaByAngkatan;
    }

    public function getAchievementsByAngkatan($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan)
    {
        $query = Student::query();
        $this->applyScopeFilters($query, $level, $facultyId, $departmentId, $programStudyId);

        return $query->select('angkatan')
            ->selectRaw('count(distinct student_id) as student_count')
            ->groupBy('angkatan')
            ->orderBy('angkatan', 'desc')
            ->get()
            ->map(function ($item) use ($selectedPeriods, $level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                $achQuery = StudentAchievement::query()
                    ->whereHas('student', function ($q) use ($item, $level, $facultyId, $departmentId, $programStudyId) {
                        $q->where('angkatan', $item->angkatan);
                        $this->applyScopeFilters($q, $level, $facultyId, $departmentId, $programStudyId);
                    });

                if ($isPimpinan) {
                    $achQuery->whereIn('validation_status', ['faculty_approved', 'university_approved']);
                }

                if (! empty($selectedPeriods)) {
                    $achQuery->whereIn('academic_period_id', $selectedPeriods);
                }

                $item->achievements_count = $achQuery->count();

                return $item;
            });
    }

    public function getAchievementsPerPeriod($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan)
    {
        return AcademicPeriod::query()
            ->when(! empty($selectedPeriods), fn ($q) => $q->whereIn('id', $selectedPeriods))
            ->withCount([
                'achievements' => function ($q) use ($level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                    if ($isPimpinan) {
                        $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
                    }
                    $q->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                        $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
                    });
                },
            ])
            ->orderBy('start_date')
            ->get();
    }

    public function getAchievementTrend($level, $facultyId, $departmentId, $programStudyId, $isPimpinan)
    {
        return AcademicPeriod::query()
            ->orderBy('end_date', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->map(function ($period) use ($level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                $baseQuery = StudentAchievement::query()
                    ->where('academic_period_id', $period->id)
                    ->when($isPimpinan, fn ($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
                    ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                        $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
                    });

                return [
                    'label' => $period->name_short ?? $period->name,
                    'academic' => (clone $baseQuery)->whereHas('achievement', fn ($q) => $q->where('category_id', 1))->count(),
                    'non_academic' => (clone $baseQuery)->whereHas('achievement', fn ($q) => $q->where('category_id', '!=', 1))->count(),
                    'total' => (clone $baseQuery)->count(),
                ];
            })->values();
    }

    public function getRiskIndicators($isPimpinan, $hierarchicalComparison, $selectedPeriods, $achievementGrowth, $level, $facultyId, $departmentId, $programStudyId, $totalUnitsCount, $activeUnitsCount)
    {
        $riskIndicators = [];
        if (! $isPimpinan) {
            return $riskIndicators;
        }

        $settings = ExecutiveSetting::where('category', 'executive_panel')->pluck('value', 'key')->all();

        $minNational = (int) ($settings['min_national_achievements'] ?? 1);
        $growthDropThreshold = (float) ($settings['growth_drop_threshold'] ?? 20);
        $slaDays = (int) ($settings['sla_validation_days'] ?? 7);
        $participationTarget = (float) (($settings['unit_participation_target'] ?? 30) / 100);

        if (! empty($hierarchicalComparison) && isset($hierarchicalComparison['items'])) {
            $noNational = $hierarchicalComparison['items']->filter(function ($unit) use ($selectedPeriods, $minNational) {
                return StudentAchievement::whereIn('validation_status', ['faculty_approved', 'university_approved'])
                    ->whereHas('student', function ($q) use ($unit) {
                        if (isset($unit->faculty_id)) {
                            $q->where('faculty_id', $unit->faculty_id);
                        } elseif (isset($unit->department_id)) {
                            $q->where('department_id', $unit->department_id);
                        } elseif (isset($unit->program_study_id)) {
                            $q->where('program_study_id', $unit->program_study_id);
                        }
                    })
                    ->whereIn('level', ['Nasional', 'Internasional'])
                    ->when(! empty($selectedPeriods), fn ($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                    ->count() < $minNational;
            })->take(2);

            foreach ($noNational as $unit) {
                $riskIndicators[] = [
                    'message' => ($unit->faculty ?? $unit->department ?? $unit->program_study)." belum mencapai target prestasi tingkat Nasional/Internasional ({$minNational}) pada periode ini.",
                    'type' => 'warning',
                    'icon' => 'exclamation-circle',
                ];
            }
        }

        if ($achievementGrowth < -$growthDropThreshold) {
            $riskIndicators[] = [
                'message' => 'Sistem mendeteksi penurunan kuantitas prestasi sebesar '.round(abs($achievementGrowth), 1)."% (melebihi ambang batas {$growthDropThreshold}%).",
                'type' => 'danger',
                'icon' => 'trending-down',
            ];
        }

        $bottlenecks = StudentAchievement::whereIn('validation_status', ['submitted', 'faculty_review', 'university_review', 'faculty_revision', 'university_revision'])
            ->whereNotIn('validation_status', ['faculty_rejected', 'university_rejected'])
            ->where('submitted_at', '<', now()->subDays($slaDays))
            ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
            })->count();

        if ($bottlenecks > 0) {
            $riskIndicators[] = [
                'message' => "Sebanyak {$bottlenecks} pengajuan prestasi melampaui batas waktu validasi (> {$slaDays} hari).",
                'type' => 'danger',
                'icon' => 'clock',
                'action_url' => route('pimpinan.sla-breach-details'),
            ];
        }

        if ($totalUnitsCount > 0 && ($activeUnitsCount / $totalUnitsCount) < $participationTarget) {
            $riskIndicators[] = [
                'message' => 'Tingkat partisipasi unit aktif baru mencapai '.round(($activeUnitsCount / $totalUnitsCount) * 100).'%, di bawah target optimal '.($participationTarget * 100).'%.',
                'type' => 'warning',
                'icon' => 'users',
            ];
        }

        return array_slice($riskIndicators, 0, 5);
    }

    public function getValidatorData(Request $request, $achievementsQuery, $isPimpinan)
    {
        if ($isPimpinan) {
            return [
                'pendingAchievements' => new LengthAwarePaginator([], 0, 15),
                'categories' => collect(),
                'levels' => [],
            ];
        }

        $pendingQuery = $achievementsQuery->clone()
            ->with(['student', 'achievement.category', 'documents'])
            ->where('validation_status', 'submitted');

        if ($request->filled('search')) {
            $search = $request->search;
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('event_name', 'ilike', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('student_id', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $pendingQuery->whereHas('achievement', fn ($q) => $q->where('category_id', $request->category));
        }

        if ($request->filled('level')) {
            $pendingQuery->where('level', $request->level);
        }

        return [
            'pendingAchievements' => $pendingQuery->orderBy('submitted_at', 'asc')->paginate(15),
            'categories' => AchievementCategory::orderBy('name')->get(),
            'levels' => StudentAchievement::distinct()->pluck('level')->filter()->values()->toArray(),
        ];
    }

    public function getSlaBreachDetailsData(Request $request)
    {
        $scope = $this->getDashboardScope();
        $level = $scope['level'];
        $facultyId = $scope['facultyId'];
        $departmentId = $scope['departmentId'];
        $programStudyId = $scope['programStudyId'];

        $selectedPeriods = $this->getSelectedPeriods($request);

        return StudentAchievement::with(['student', 'achievement.category', 'academicPeriod'])
            ->whereIn('validation_status', ['submitted', 'faculty_review', 'university_review', 'faculty_revision', 'university_revision'])
            ->whereNotIn('validation_status', ['faculty_rejected', 'university_rejected'])
            ->where('submitted_at', '<', now()->subDays(7))
            ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
            })
            ->when(! empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                $q->whereIn('academic_period_id', $selectedPeriods);
            })
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->map(function ($achievement) {
                $daysOverdue = (int) now()->diffInDays($achievement->submitted_at);

                return [
                    'sa_id' => $achievement->sa_id,
                    'student_name' => $achievement->student->name ?? 'N/A',
                    'student_id' => $achievement->student->student_id ?? 'N/A',
                    'program_study' => $achievement->student->program_study ?? 'N/A',
                    'event_name' => $achievement->event_name,
                    'category' => $achievement->achievement->category->name ?? 'N/A',
                    'level' => $achievement->level,
                    'submitted_at' => $achievement->submitted_at->format('d M Y'),
                    'days_overdue' => $daysOverdue,
                    'validation_status' => $achievement->validation_status,
                    'status_label' => $achievement->getStatusLabelAttribute(),
                ];
            });
    }
}
