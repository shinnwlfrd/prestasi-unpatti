<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Student;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Cache for department queries to avoid duplicates
    private static $departmentCache = [];

    /**
     * Display validator/pimpinan dashboard with hierarchical statistics
     * Validator = full access, Pimpinan = read-only
     */
    public function index(Request $request)
    {
        $scope = $this->getDashboardScope();
        $isPimpinan = $scope['isPimpinan'];
        $level = $scope['level'];
        $facultyId = $scope['facultyId'];
        $departmentId = $scope['departmentId'];
        $programStudyId = $scope['programStudyId'];
        $position = $scope['position'];
        $positionLabel = $scope['positionLabel'];
        $scopeName = $scope['scopeName'];

        // Get selected periods (default to current active period)
        $selectedPeriods = $this->getSelectedPeriods($request);
        $allPeriods = AcademicPeriod::orderBy('start_date', 'desc')->get();

        // Build base queries with scope filtering
        $studentsQuery = Student::query();
        $achievementsQuery = StudentAchievement::query();

        $this->applyScopeFilters($studentsQuery, $level, $facultyId, $departmentId, $programStudyId);
        $this->applyScopeFilters($achievementsQuery, $level, $facultyId, $departmentId, $programStudyId);

        // Filter by selected periods
        if (!empty($selectedPeriods)) {
            $achievementsQuery->whereIn('academic_period_id', $selectedPeriods);
        }

        // Get basic statistics & KPIs
        $totalStudents = $studentsQuery->count();
        $totalAchievements = $achievementsQuery->clone()
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->count();

        $kpiData = $this->getKpiStats($totalStudents, $totalAchievements, $selectedPeriods, $isPimpinan, $level, $facultyId, $departmentId, $programStudyId);
        $achievementRatio = $kpiData['ratio'];
        $achievementGrowth = $kpiData['growth'];
        $urgentPending = $kpiData['urgentPending'];

        // Status breakdown & Distribution
        $statusStats = $this->getStatusStats($achievementsQuery);
        $categoryDistribution = $this->getCategoryDistribution($achievementsQuery, $isPimpinan);
        $levelDistribution = $this->getLevelDistribution($achievementsQuery, $isPimpinan);

        // Regional KPIs
        $nationalStats = $this->getNationalKpis($totalAchievements, $levelDistribution);
        $nationalPercentage = $nationalStats['nationalPercentage'];
        $internationalPercentage = $nationalStats['internationalPercentage'];

        $activeUnitsData = $this->getActiveUnitsKpi($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods);
        $activeUnitsCount = $activeUnitsData['activeCount'];
        $totalUnitsCount = $activeUnitsData['totalCount'];
        $unitLabel = $activeUnitsData['label'];

        // Hierarchical comparisons (Pimpinan only)
        $hierarchicalData = [];
        $categoryByHierarchy = [];
        $levelByHierarchy = [];
        $efficiencyRanking = collect();
        $hierarchicalComparison = [];

        if ($isPimpinan) {
            $hierarchicalComparison = $this->getHierarchicalComparison($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods);

            if (!empty($hierarchicalComparison)) {
                $categoryByHierarchy = $this->getCategoryByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods);
                $levelByHierarchy = $this->getLevelByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods);
                $efficiencyRanking = $this->calculateEfficiencyRanking($hierarchicalComparison);
            }
            $hierarchicalData = $hierarchicalComparison;
        }

        // Engagement & Trends
        $recentActivities = $this->getRecentActivities($achievementsQuery, $isPimpinan);
        $topStudents = $this->getTopStudents($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan);
        $topGpaByAngkatan = $this->getTopGpaByAngkatan($level, $programStudyId);
        $achievementsByAngkatan = $this->getAchievementsByAngkatan($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan);
        $achievementsPerPeriod = $this->getAchievementsPerPeriod($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan);
        $achievementTrend = $this->getAchievementTrend($level, $facultyId, $departmentId, $programStudyId, $isPimpinan);

        // Insights & Pending Tasks
        $riskIndicators = $this->getRiskIndicators($isPimpinan, $hierarchicalComparison, $selectedPeriods, $achievementGrowth, $level, $facultyId, $departmentId, $programStudyId, $totalUnitsCount, $activeUnitsCount);

        $validatorData = $this->getValidatorData($request, $achievementsQuery, $isPimpinan);
        $pendingAchievements = $validatorData['pendingAchievements'];
        $categories = $validatorData['categories'];
        $levels = $validatorData['levels'];

        $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';

        return view('validator.dashboard', compact(
            'totalStudents',
            'totalAchievements',
            'urgentPending',
            'statusStats',
            'recentActivities',
            'topStudents',
            'topGpaByAngkatan',
            'achievementsByAngkatan',
            'categoryDistribution',
            'levelDistribution',
            'categoryByHierarchy',
            'levelByHierarchy',
            'hierarchicalComparison',
            'achievementsPerPeriod',
            'hierarchicalData',
            'allPeriods',
            'selectedPeriods',
            'level',
            'position',
            'positionLabel',
            'scopeName',
            'isPimpinan',
            'pendingAchievements',
            'categories',
            'levels',
            'routePrefix',
            'achievementRatio',
            'achievementGrowth',
            'nationalPercentage',
            'internationalPercentage',
            'activeUnitsCount',
            'totalUnitsCount',
            'unitLabel',
            'efficiencyRanking',
            'achievementTrend',
            'riskIndicators'
        ));
    }

    /**
     * Get hierarchical comparison based on position level
     */
    private function getHierarchicalComparison($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $data = [];

        if ($level === 'university') {
            // Rektor: Compare faculties - use groupBy to prevent duplicates
            $data['type'] = 'faculty';
            $data['label'] = 'Perbandingan Prestasi Antar Fakultas';

            $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

            $data['items'] = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->get()
                ->map(function ($item) use ($selectedPeriods, $nameMap) {
                    $item->faculty = $nameMap[$item->faculty_id] ?? $item->faculty;

                    // Total students in this faculty
                    $item->students_count = Student::where('faculty_id', $item->faculty_id)->count();

                    $item->achievements_count = StudentAchievement::query()
                        ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                        ->whereHas('student', function ($q) use ($item) {
                            $q->where('faculty_id', $item->faculty_id);
                        })
                        ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();
                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();

            // If no items found, get all faculties (even with 0 achievements)
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
            // Dekan: Compare departments - use groupBy to prevent duplicates
            $data['type'] = 'department';
            $data['label'] = 'Perbandingan Prestasi Antar Jurusan';
            $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

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
                        ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();
                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();

            // If no items found, get all departments in faculty (even with 0 achievements)
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
            // Ketua Jurusan: Compare program studies - use groupBy to prevent duplicates
            $data['type'] = 'program_study';
            $data['label'] = 'Perbandingan Prestasi Antar Program Studi';
            $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

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
                        ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();
                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();

            // If no items found, get all program studies in department (even with 0 achievements)
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
            // Kaprodi: Compare Angkatan (Cohorts)
            $data['type'] = 'angkatan';
            $data['label'] = 'Perbandingan Prestasi Antar Angkatan';

            $data['items'] = Student::where('program_study_id', $programStudyId)
                ->select('angkatan')
                ->whereNotNull('angkatan')
                ->groupBy('angkatan')
                ->orderBy('angkatan', 'desc')
                ->limit(10) // Show last 10 cohorts
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
                        ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();
                    // For UI display
                    $item->angkatan_label = "Angkatan " . $item->angkatan;
                    return $item;
                })
                ->sortByDesc('angkatan')
                ->values();
        } elseif ($level === 'graduate_program') {
            // Direktur PPs: Compare program studies within PPs - use groupBy to prevent duplicates
            $data['type'] = 'program_study';
            $data['label'] = 'Perbandingan Prestasi Antar Program Pascasarjana';
            $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

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
                        ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                            $q->whereIn('academic_period_id', $selectedPeriods);
                        })
                        ->count();
                    return $item;
                })
                ->sortByDesc('achievements_count')
                ->values();
        }

        // Return empty structure if no data found
        if (empty($data)) {
            $data = [
                'type' => 'none',
                'label' => 'Ringkasan Capaian Program Studi',
                'items' => collect([])
            ];
        }

        return $data;
    }

    /**
     * Get category distribution by hierarchy
     */
    private function getCategoryByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $data = [];

        $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

        if ($level === 'university') {
            // Rektor: Category by Faculty > Department > Program Study
            $faculties = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->get();

            foreach ($faculties as $faculty) {
                $data[$faculty->faculty_id] = [
                    'name' => $nameMap[$faculty->faculty_id] ?? $faculty->faculty,
                    'categories' => $this->getCategoryDistributionForScope('faculty', $faculty->faculty_id, null, null, $selectedPeriods),
                    'departments' => []
                ];

                // Get departments in this faculty - use groupBy to prevent duplicates
                $departments = Student::where('faculty_id', $faculty->faculty_id)
                    ->select('department_id', DB::raw('MAX(department) as department'))
                    ->groupBy('department_id')
                    ->get();

                foreach ($departments as $dept) {
                    $data[$faculty->faculty_id]['departments'][$dept->department_id] = [
                        'name' => $nameMap[$dept->department_id] ?? $dept->department,
                        'categories' => $this->getCategoryDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                        'program_studies' => []
                    ];

                    // Get program studies in this department - use groupBy to prevent duplicates
                    $programs = Student::where('department_id', $dept->department_id)
                        ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                        ->groupBy('program_study_id')
                        ->get();

                    foreach ($programs as $prog) {
                        $data[$faculty->faculty_id]['departments'][$dept->department_id]['program_studies'][$prog->program_study_id] = [
                            'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                            'categories' => $this->getCategoryDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods)
                        ];
                    }
                }
            }
        } elseif ($level === 'faculty') {
            // Dekan: Category by Department > Program Study - use groupBy to prevent duplicates
            $departments = Student::where('faculty_id', $facultyId)
                ->select('department_id', DB::raw('MAX(department) as department'))
                ->groupBy('department_id')
                ->get();

            foreach ($departments as $dept) {
                $data[$dept->department_id] = [
                    'name' => $nameMap[$dept->department_id] ?? $dept->department,
                    'categories' => $this->getCategoryDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                    'program_studies' => []
                ];

                // Get program studies - use groupBy to prevent duplicates
                $programs = Student::where('department_id', $dept->department_id)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get();

                foreach ($programs as $prog) {
                    $data[$dept->department_id]['program_studies'][$prog->program_study_id] = [
                        'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                        'categories' => $this->getCategoryDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods)
                    ];
                }
            }
        } elseif ($level === 'department') {
            // Ketua Jurusan: Category by Program Study - use groupBy to prevent duplicates
            $programs = Student::where('department_id', $departmentId)
                ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                ->groupBy('program_study_id')
                ->get();

            foreach ($programs as $prog) {
                $data[$prog->program_study_id] = [
                    'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                    'categories' => $this->getCategoryDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods)
                ];
            }
        }

        return $data;
    }

    /**
     * Get level distribution by hierarchy
     */
    private function getLevelByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $data = [];

        $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

        if ($level === 'university') {
            // Rektor: Level by Faculty > Department > Program Study
            $faculties = Student::select('faculty_id', DB::raw('MAX(faculty) as faculty'))
                ->whereNotNull('faculty_id')
                ->groupBy('faculty_id')
                ->get();

            foreach ($faculties as $faculty) {
                $data[$faculty->faculty_id] = [
                    'name' => $nameMap[$faculty->faculty_id] ?? $faculty->faculty,
                    'levels' => $this->getLevelDistributionForScope('faculty', $faculty->faculty_id, null, null, $selectedPeriods),
                    'departments' => []
                ];

                // Get departments - use groupBy to prevent duplicates
                $departments = Student::where('faculty_id', $faculty->faculty_id)
                    ->select('department_id', DB::raw('MAX(department) as department'))
                    ->groupBy('department_id')
                    ->get();

                foreach ($departments as $dept) {
                    $data[$faculty->faculty_id]['departments'][$dept->department_id] = [
                        'name' => $nameMap[$dept->department_id] ?? $dept->department,
                        'levels' => $this->getLevelDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                        'program_studies' => []
                    ];

                    // Get program studies - use groupBy to prevent duplicates
                    $programs = Student::where('department_id', $dept->department_id)
                        ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                        ->groupBy('program_study_id')
                        ->get();

                    foreach ($programs as $prog) {
                        $data[$faculty->faculty_id]['departments'][$dept->department_id]['program_studies'][$prog->program_study_id] = [
                            'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                            'levels' => $this->getLevelDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods)
                        ];
                    }
                }
            }
        } elseif ($level === 'faculty') {
            // Dekan: Level by Department > Program Study - use groupBy to prevent duplicates
            $departments = Student::where('faculty_id', $facultyId)
                ->select('department_id', DB::raw('MAX(department) as department'))
                ->groupBy('department_id')
                ->get();

            foreach ($departments as $dept) {
                $data[$dept->department_id] = [
                    'name' => $nameMap[$dept->department_id] ?? $dept->department,
                    'levels' => $this->getLevelDistributionForScope('department', null, $dept->department_id, null, $selectedPeriods),
                    'program_studies' => []
                ];

                // Get program studies - use groupBy to prevent duplicates
                $programs = Student::where('department_id', $dept->department_id)
                    ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                    ->groupBy('program_study_id')
                    ->get();

                foreach ($programs as $prog) {
                    $data[$dept->department_id]['program_studies'][$prog->program_study_id] = [
                        'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                        'levels' => $this->getLevelDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods)
                    ];
                }
            }
        } elseif ($level === 'department') {
            // Ketua Jurusan: Level by Program Study - use groupBy to prevent duplicates
            $programs = Student::where('department_id', $departmentId)
                ->select('program_study_id', DB::raw('MAX(program_study) as program_study'))
                ->groupBy('program_study_id')
                ->get();

            foreach ($programs as $prog) {
                $data[$prog->program_study_id] = [
                    'name' => $nameMap[$prog->program_study_id] ?? $prog->program_study,
                    'levels' => $this->getLevelDistributionForScope('program_study', null, null, $prog->program_study_id, $selectedPeriods)
                ];
            }
        }

        return $data;
    }

    /**
     * Get category distribution for specific scope
     */
    private function getCategoryDistributionForScope($scopeType, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
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

        if (!empty($selectedPeriods)) {
            $query->whereIn('academic_period_id', $selectedPeriods);
        }

        return $query->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->select('achievement_categories.name', DB::raw('count(*) as total'))
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get level distribution for specific scope
     */
    private function getLevelDistributionForScope($scopeType, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
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

        if (!empty($selectedPeriods)) {
            $query->whereIn('academic_period_id', $selectedPeriods);
        }

        return $query->select('student_achievements.level as name', DB::raw('count(*) as total'))
            ->groupBy('student_achievements.level')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get hierarchical chart data for AJAX requests
     */
    public function getHierarchicalChartData(Request $request)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        // Get scope based on active route prefix
        $isPimpinanRoute = request()->routeIs('pimpinan.*');

        if ($isPimpinanRoute) {
            $level = session('pimpinan_level');
            $facultyId = session('pimpinan_faculty_id');
            $departmentId = session('pimpinan_department_id');
            $programStudyId = session('pimpinan_program_study_id');
        } else {
            $level = session('operator_level');
            $facultyId = session('operator_faculty_id');
            $departmentId = session('operator_department_id');
            $programStudyId = session('operator_program_study_id');
        }

        $level = $level ?? session('operator_level') ?? session('pimpinan_level');
        $facultyId = $facultyId ?? session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = $departmentId ?? session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = $programStudyId ?? session('operator_program_study_id') ?? session('pimpinan_program_study_id');

        // Get request parameters
        $selectedPeriods = $request->input('periods', []);
        $drillLevel = $request->input('drill_level', 'main'); // main, level1, level2
        $parentId = $request->input('parent_id');
        $grandparentId = $request->input('grandparent_id');

        $data = [];
        $labels = [];
        $values = [];
        $drillData = [];

        if ($drillLevel === 'main') {
            // Main level comparison
            if ($level === 'university') {
                // Rektor: Show faculties - use groupBy to prevent duplicates
                $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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
                        'students_count' => $item->students_count
                    ];
                }

                $data['title'] = 'Perbandingan Prestasi Antar Fakultas';
                $data['canDrillDown'] = true;
                $data['nextLevel'] = 'department';

            } elseif ($level === 'faculty') {
                // Dekan: Show departments - use groupBy to prevent duplicates
                $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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
                        'students_count' => $item->students_count
                    ];
                }

                $data['title'] = 'Perbandingan Prestasi Antar Jurusan';
                $data['canDrillDown'] = true;
                $data['nextLevel'] = 'program_study';

            } elseif ($level === 'department') {
                // Ketua Jurusan: Show program studies - use groupBy to prevent duplicates
                $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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
                        'students_count' => $item->students_count
                    ];
                }

                $data['title'] = 'Perbandingan Prestasi Antar Program Studi';
                $data['canDrillDown'] = false;

            } elseif ($level === 'program_study') {
                // Kaprodi: Show Angkatan - use groupBy to prevent duplicates
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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                                $q->whereIn('academic_period_id', $selectedPeriods);
                            })
                            ->count();

                        $item->name = "Angkatan " . $item->angkatan;
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
                        'students_count' => $item->students_count
                    ];
                }

                $data['title'] = 'Perbandingan Prestasi Antar Angkatan';
                $data['canDrillDown'] = false;
            } elseif ($level === 'graduate_program') {
                // Direktur PPs: Show program studies within PPs - use groupBy to prevent duplicates
                $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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
                        'students_count' => $item->students_count
                    ];
                }

                $data['title'] = 'Perbandingan Prestasi Antar Program Pascasarjana';
                $data['canDrillDown'] = false;
            }

        } elseif ($drillLevel === 'level1') {
            // First drill-down level
            $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

            if ($level === 'university') {
                // Rektor drilling into faculty: Show departments - use groupBy to prevent duplicates
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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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
                        'students_count' => $item->students_count
                    ];
                }

                $data['title'] = "Distribusi Prestasi per Jurusan - {$facultyName}";
                $data['canDrillDown'] = true;
                $data['nextLevel'] = 'program_study';

            } elseif ($level === 'faculty') {
                // Dekan drilling into department: Show program studies - use groupBy to prevent duplicates
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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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
                        'students_count' => $item->students_count
                    ];
                }

                $data['title'] = "Distribusi Prestasi per Program Studi - {$departmentName}";
                $data['canDrillDown'] = false;
            }

        } elseif ($drillLevel === 'level2') {
            // Second drill-down level (only for Rektor)
            $nameMap = app(\App\Services\SigapApiService::class)->getUnitNameMap();

            if ($level === 'university') {
                // Rektor drilling into department: Show program studies - use groupBy to prevent duplicates
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
                            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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
                        'students_count' => $item->students_count
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

        return response()->json($data);
    }

    /**
     * Get participants for a specific event
     */
    public function getEventParticipants(Request $request)
    {
        $eventName = $request->query('event_name');
        $organizer = $request->query('organizer');
        $level = $request->query('level');

        if (!$eventName) {
            return response()->json(['error' => 'Event name is required'], 400);
        }

        $isPimpinan = auth()->user()->getCurrentRole()->role === 'pimpinan';

        // Scope settings from session
        $scopeLevel = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

        $participants = StudentAchievement::query()
            ->with(['student'])
            ->where('event_name', $eventName)
            ->where('organizer', $organizer)
            ->where('level', $level)
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->when($scopeLevel === 'faculty' && $facultyId, function ($q) use ($facultyId) {
                $q->whereHas('student', fn($sq) => $sq->where('faculty_id', $facultyId));
            })
            ->when($scopeLevel === 'department' && $departmentId, function ($q) use ($departmentId) {
                $q->whereHas('student', fn($sq) => $sq->where('department_id', $departmentId));
            })
            ->when($scopeLevel === 'program_study' && $programStudyId, function ($q) use ($programStudyId) {
                $q->whereHas('student', fn($sq) => $sq->where('program_study_id', $programStudyId));
            })
            ->when($scopeLevel === 'graduate_program', function ($q) {
                $q->whereHas('student', fn($sq) => $sq->where('faculty', 'Program Pascasarjana'));
            })
            ->get()
            ->map(function ($sa) {
                return [
                    'student_id' => $sa->student_id,
                    'student_name' => $sa->student->name ?? 'N/A',
                    'program_study' => $sa->student->program_study ?? 'N/A',
                    'faculty' => $sa->student->faculty ?? 'N/A',
                    'submitted_at' => $sa->submitted_at ? $sa->submitted_at->format('d M Y') : 'N/A',
                    'validation_status' => $sa->validation_status,
                    'details_url' => route(auth()->user()->getCurrentRole()->role . '.students.show', $sa->student_id)
                ];
            });

        return response()->json([
            'event_name' => $eventName,
            'organizer' => $organizer,
            'level' => $level,
            'participants' => $participants
        ]);
    }

    /**
     * Get departments with caching to avoid duplicate queries
     */
    private function getDepartmentsByFaculty($facultyId, $selectedPeriods = [])
    {
        $cacheKey = "departments_faculty_{$facultyId}_" . md5(serialize($selectedPeriods));

        if (isset(self::$departmentCache[$cacheKey])) {
            return self::$departmentCache[$cacheKey];
        }

        // Use groupBy to prevent duplicate departments
        $departments = Student::where('faculty_id', $facultyId)
            ->select('department_id', DB::raw('MAX(department) as department'))
            ->groupBy('department_id')
            ->get()
            ->map(function ($item) use ($selectedPeriods) {
                $item->achievements_count = StudentAchievement::query()
                    ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                    ->whereHas('student', function ($q) use ($item) {
                        $q->where('department_id', $item->department_id);
                    })
                    ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
                        $q->whereIn('academic_period_id', $selectedPeriods);
                    })
                    ->count();
                return $item;
            })
            ->sortByDesc('achievements_count')
            ->values();

        // If no items found, get all departments in faculty (even with 0 achievements)
        if ($departments->isEmpty()) {
            $departments = Student::where('faculty_id', $facultyId)
                ->select('department_id', DB::raw('MAX(department) as department'))
                ->groupBy('department_id')
                ->get()
                ->map(function ($item) {
                    $item->achievements_count = 0;
                    return $item;
                });
        }

        self::$departmentCache[$cacheKey] = $departments;
        return $departments;
    }

    /**
     * Get dashboard scope and user information
     */
    private function getDashboardScope()
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
        $isPimpinanRoute = request()->routeIs('pimpinan.*');

        $prefix = $isPimpinanRoute ? 'pimpinan' : 'operator';

        $level = session("{$prefix}_level") ?? session('operator_level') ?? session('pimpinan_level');
        $facultyId = session("{$prefix}_faculty_id") ?? session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session("{$prefix}_department_id") ?? session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session("{$prefix}_program_study_id") ?? session('operator_program_study_id') ?? session('pimpinan_program_study_id');
        $position = session('pimpinan_position');

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

    /**
     * Get selected academic periods
     */
    private function getSelectedPeriods(Request $request)
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

    /**
     * Apply hierarchical filters to a query
     */
    private function applyScopeFilters($query, $level, $facultyId, $departmentId, $programStudyId)
    {
        $isAchievementQuery = $query->getModel() instanceof StudentAchievement;

        if ($level === 'faculty' && $facultyId) {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn($q) => $q->where('faculty_id', $facultyId));
            } else {
                $query->where('faculty_id', $facultyId);
            }
        } elseif ($level === 'department' && $departmentId) {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn($q) => $q->where('department_id', $departmentId));
            } else {
                $query->where('department_id', $departmentId);
            }
        } elseif ($level === 'program_study' && $programStudyId) {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn($q) => $q->where('program_study_id', $programStudyId));
            } else {
                $query->where('program_study_id', $programStudyId);
            }
        } elseif ($level === 'graduate_program') {
            if ($isAchievementQuery) {
                $query->whereHas('student', fn($q) => $q->where('faculty', 'Program Pascasarjana'));
            } else {
                $query->where('faculty', 'Program Pascasarjana');
            }
        }
    }

    /**
     * Calculate KPI statistics (Ratio, Growth, Urgent Pending)
     */
    private function getKpiStats($totalStudents, $totalAchievements, $selectedPeriods, $isPimpinan, $level, $facultyId, $departmentId, $programStudyId)
    {
        $ratio = $totalStudents > 0 ? $totalAchievements / $totalStudents : 0;
        $growth = 0;
        $urgentPending = 0;

        if (!empty($selectedPeriods)) {
            $earliestPeriod = AcademicPeriod::whereIn('id', $selectedPeriods)->orderBy('start_date', 'asc')->first();

            if ($earliestPeriod) {
                $previousPeriod = AcademicPeriod::where('end_date', '<', $earliestPeriod->start_date)
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($previousPeriod) {
                    $achievementsPrevious = StudentAchievement::query()
                        ->where('academic_period_id', $previousPeriod->id)
                        ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
                        ->whereHas('student', function ($q) use ($level, $facultyId, $departmentId, $programStudyId) {
                            if ($level === 'faculty' && $facultyId)
                                $q->where('faculty_id', $facultyId);
                            elseif ($level === 'department' && $departmentId)
                                $q->where('department_id', $departmentId);
                            elseif ($level === 'program_study' && $programStudyId)
                                $q->where('program_study_id', $programStudyId);
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

        if (!$isPimpinan) {
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

    /**
     * Get status distribution stats
     */
    private function getStatusStats($query)
    {
        return $query->clone()
            ->select('validation_status', DB::raw('count(*) as total'))
            ->groupBy('validation_status')
            ->pluck('total', 'validation_status')
            ->toArray();
    }

    /**
     * Get category distribution stats
     */
    private function getCategoryDistribution($query, $isPimpinan)
    {
        return $query->clone()
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->select('achievement_categories.name', DB::raw('count(*) as total'))
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get level distribution stats
     */
    private function getLevelDistribution($query, $isPimpinan)
    {
        return $query->clone()
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->select('student_achievements.level as name', DB::raw('count(*) as total'))
            ->groupBy('student_achievements.level')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Calculate National & International KPIs
     */
    private function getNationalKpis($totalAchievements, $levelDistribution)
    {
        $nationalCount = $levelDistribution->where('name', 'Nasional')->first()?->total ?? 0;
        $internationalCount = $levelDistribution->where('name', 'Internasional')->first()?->total ?? 0;

        return [
            'nationalPercentage' => $totalAchievements > 0 ? ($nationalCount / $totalAchievements) * 100 : 0,
            'internationalPercentage' => $totalAchievements > 0 ? ($internationalCount / $totalAchievements) * 100 : 0
        ];
    }

    /**
     * Calculate Active Units KPI
     */
    private function getActiveUnitsKpi($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods)
    {
        $activeCount = 0;
        $totalCount = 0;
        $label = 'Unit';

        if ($level === 'university') {
            $label = 'Fakultas';
            $totalCount = Student::whereNotNull('faculty_id')->distinct('faculty_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->whereNotNull('faculty_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.faculty_id')
                ->count('students.faculty_id');
        } elseif ($level === 'faculty') {
            $label = 'Jurusan';
            $totalCount = Student::where('faculty_id', $facultyId)->whereNotNull('department_id')->distinct('department_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('faculty_id', $facultyId)->whereNotNull('department_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.department_id')
                ->count('students.department_id');
        } elseif ($level === 'department') {
            $label = 'Prodi';
            $totalCount = Student::where('department_id', $departmentId)->whereNotNull('program_study_id')->distinct('program_study_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('department_id', $departmentId)->whereNotNull('program_study_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.program_study_id')
                ->count('students.program_study_id');
        } elseif ($level === 'program_study') {
            $label = 'Angkatan';
            $totalCount = Student::where('program_study_id', $programStudyId)->whereNotNull('angkatan')->distinct('angkatan')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('program_study_id', $programStudyId)->whereNotNull('angkatan'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.angkatan')
                ->count('students.angkatan');
        } elseif ($level === 'graduate_program') {
            $label = 'Prodi PPs';
            $totalCount = Student::where('faculty', 'Program Pascasarjana')->whereNotNull('program_study_id')->distinct('program_study_id')->count();
            $activeCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('faculty', 'Program Pascasarjana')->whereNotNull('program_study_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.program_study_id')
                ->count('students.program_study_id');
        }

        return compact('activeCount', 'totalCount', 'label');
    }

    /**
     * Calculate efficiency ranking based on achievement ratio
     */
    private function calculateEfficiencyRanking($hierarchicalComparison)
    {
        return $hierarchicalComparison['items']->map(function ($item) {
            $ratio = $item->students_count > 0 ? ($item->achievements_count / $item->students_count) : 0;
            return [
                'name' => $item->faculty ?? $item->department ?? $item->program_study ?? $item->angkatan_label ?? $item->angkatan ?? 'Unknown',
                'ratio' => $ratio,
                'achievements' => $item->achievements_count,
                'students' => $item->students_count
            ];
        })->sortByDesc('ratio')->values()->take(10);
    }

    /**
     * Get recent activities grouped by event
     */
    private function getRecentActivities($query, $isPimpinan)
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
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->groupBy('event_name', 'organizer', 'level')
            ->orderBy('latest_submission', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get top students by achievement count
     */
    private function getTopStudents($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan)
    {
        $query = Student::query();
        $this->applyScopeFilters($query, $level, $facultyId, $departmentId, $programStudyId);

        return $query->withCount([
            'achievements' => function ($q) use ($selectedPeriods, $isPimpinan) {
                if ($isPimpinan) {
                    $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
                }
                if (!empty($selectedPeriods)) {
                    $q->whereIn('academic_period_id', $selectedPeriods);
                }
            }
        ])
            ->orderBy('achievements_count', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get top GPA students grouped by angkatan
     */
    private function getTopGpaByAngkatan($level, $programStudyId)
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

    /**
     * Get achievement counts per angkatan
     */
    private function getAchievementsByAngkatan($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan)
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

                if (!empty($selectedPeriods)) {
                    $achQuery->whereIn('academic_period_id', $selectedPeriods);
                }

                $item->achievements_count = $achQuery->count();
                return $item;
            });
    }

    /**
     * Get achievement counts per period
     */
    private function getAchievementsPerPeriod($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods, $isPimpinan)
    {
        return AcademicPeriod::query()
            ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('id', $selectedPeriods))
            ->withCount([
                'achievements' => function ($q) use ($level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                    if ($isPimpinan) {
                        $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
                    }
                    $q->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                        $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
                    });
                }
            ])
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Get achievement trend data (last 6 semesters)
     */
    private function getAchievementTrend($level, $facultyId, $departmentId, $programStudyId, $isPimpinan)
    {
        return AcademicPeriod::query()
            ->orderBy('end_date', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->map(function ($period) use ($level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                $baseQuery = StudentAchievement::query()
                    ->where('academic_period_id', $period->id)
                    ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
                    ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                        $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
                    });

                return [
                    'label' => $period->name_short ?? $period->name,
                    'academic' => (clone $baseQuery)->whereHas('achievement', fn($q) => $q->where('category_id', 1))->count(),
                    'non_academic' => (clone $baseQuery)->whereHas('achievement', fn($q) => $q->where('category_id', '!=', 1))->count(),
                    'total' => (clone $baseQuery)->count()
                ];
            })->values();
    }

    /**
     * Generate risk indicators and alerts
     */
    private function getRiskIndicators($isPimpinan, $hierarchicalComparison, $selectedPeriods, $achievementGrowth, $level, $facultyId, $departmentId, $programStudyId, $totalUnitsCount, $activeUnitsCount)
    {
        $riskIndicators = [];
        if (!$isPimpinan)
            return $riskIndicators;

        if (!empty($hierarchicalComparison) && isset($hierarchicalComparison['items'])) {
            $noNational = $hierarchicalComparison['items']->filter(function ($unit) use ($selectedPeriods) {
                return StudentAchievement::whereIn('validation_status', ['faculty_approved', 'university_approved'])
                    ->whereHas('student', function ($q) use ($unit) {
                        if (isset($unit->faculty_id))
                            $q->where('faculty_id', $unit->faculty_id);
                        elseif (isset($unit->department_id))
                            $q->where('department_id', $unit->department_id);
                        elseif (isset($unit->program_study_id))
                            $q->where('program_study_id', $unit->program_study_id);
                    })
                    ->whereIn('level', ['Nasional', 'Internasional'])
                    ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                    ->count() === 0;
            })->take(2);

            foreach ($noNational as $unit) {
                $riskIndicators[] = [
                    'message' => ($unit->faculty ?? $unit->department ?? $unit->program_study) . " belum mencapai target prestasi tingkat Nasional/Internasional pada periode ini.",
                    'type' => 'warning',
                    'icon' => 'exclamation-circle'
                ];
            }
        }

        if ($achievementGrowth < -20) {
            $riskIndicators[] = [
                'message' => "Sistem mendeteksi penurunan kuantitas prestasi sebesar " . round(abs($achievementGrowth), 1) . "% dibandingkan periode sebelumnya.",
                'type' => 'danger',
                'icon' => 'trending-down'
            ];
        }

        $bottlenecks = StudentAchievement::whereIn('validation_status', ['submitted', 'faculty_review', 'university_review', 'faculty_revision', 'university_revision'])
            ->whereNotIn('validation_status', ['faculty_rejected', 'university_rejected']) // Exclude rejected
            ->where('submitted_at', '<', now()->subDays(7))
            ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
            })->count();

        if ($bottlenecks > 0) {
            $riskIndicators[] = [
                'message' => "Sebanyak {$bottlenecks} pengajuan prestasi melampaui batas waktu validasi (> 7 hari).",
                'type' => 'danger',
                'icon' => 'clock',
                'action_url' => route('pimpinan.sla-breach-details') // Add action URL for modal
            ];
        }

        if ($totalUnitsCount > 0 && ($activeUnitsCount / $totalUnitsCount) < 0.3) {
            $riskIndicators[] = [
                'message' => "Tingkat partisipasi unit aktif baru mencapai " . round(($activeUnitsCount / $totalUnitsCount) * 100) . "%, di bawah target optimal 30%.",
                'type' => 'warning',
                'icon' => 'users'
            ];
        }

        return array_slice($riskIndicators, 0, 5);
    }

    /**
     * Get data for validator search and listing
     */
    private function getValidatorData(Request $request, $achievementsQuery, $isPimpinan)
    {
        if ($isPimpinan) {
            return [
                'pendingAchievements' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'categories' => collect(),
                'levels' => []
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
            $pendingQuery->whereHas('achievement', fn($q) => $q->where('category_id', $request->category));
        }

        if ($request->filled('level')) {
            $pendingQuery->where('level', $request->level);
        }

        return [
            'pendingAchievements' => $pendingQuery->orderBy('submitted_at', 'asc')->paginate(15),
            'categories' => \App\Models\AchievementCategory::orderBy('name')->get(),
            'levels' => StudentAchievement::distinct()->pluck('level')->filter()->values()->toArray()
        ];
    }

    /**
     * Get SLA breach details for modal
     */
    public function getSlaBreachDetails(Request $request)
    {
        $scope = $this->getDashboardScope();
        $level = $scope['level'];
        $facultyId = $scope['facultyId'];
        $departmentId = $scope['departmentId'];
        $programStudyId = $scope['programStudyId'];

        $selectedPeriods = $this->getSelectedPeriods($request);

        $breaches = StudentAchievement::with(['student', 'achievement.category', 'academicPeriod'])
            ->whereIn('validation_status', ['submitted', 'faculty_review', 'university_review', 'faculty_revision', 'university_revision'])
            ->whereNotIn('validation_status', ['faculty_rejected', 'university_rejected'])
            ->where('submitted_at', '<', now()->subDays(7))
            ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                $this->applyScopeFilters($sq, $level, $facultyId, $departmentId, $programStudyId);
            })
            ->when(!empty($selectedPeriods), function ($q) use ($selectedPeriods) {
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

        return response()->json([
            'total' => $breaches->count(),
            'breaches' => $breaches
        ]);
    }
}
