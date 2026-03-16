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
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';

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

        // Fallback to pimpinan if operator is null (or vice versa) if needed, 
        // but generally session keys should match the active route context.
        $level = $level ?? session('operator_level') ?? session('pimpinan_level');
        $facultyId = $facultyId ?? session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = $departmentId ?? session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = $programStudyId ?? session('operator_program_study_id') ?? session('pimpinan_program_study_id');

        $position = session('pimpinan_position');

        // Get position label and scope name for display
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

        // Get scope name based on level
        $scopeName = '';
        if ($isPimpinanRoute) {
            if ($level === 'university' || $position === 'super_admin') {
                $scopeName = 'Universitas Pattimura';
            } elseif ($level === 'faculty') {
                $scopeName = session('pimpinan_faculty_name') ?? 'Fakultas';
            } elseif ($level === 'department') {
                $scopeName = session('pimpinan_department_name') ?? 'Jurusan';
            } elseif ($level === 'program_study') {
                $scopeName = session('pimpinan_program_study_name') ?? 'Program Studi';
            } elseif ($level === 'graduate_program') {
                $scopeName = 'Program Pascasarjana';
            }
        } else {
            // For operator
            if ($level === 'university') {
                $scopeName = 'Universitas Pattimura';
            } elseif ($level === 'faculty') {
                $scopeName = session('operator_faculty_name') ?? 'Fakultas';
            } elseif ($level === 'department') {
                $scopeName = session('operator_department_name') ?? 'Jurusan';
            } elseif ($level === 'program_study') {
                $scopeName = session('operator_program_study_name') ?? 'Program Studi';
            }
        }

        // Get selected periods (default to current active period)
        $selectedPeriods = $request->input('periods', []);
        if (empty($selectedPeriods)) {
            $activePeriod = AcademicPeriod::where('is_active', true)->first();
            if ($activePeriod) {
                $selectedPeriods = [$activePeriod->id];
            }
        }

        // Get all periods for selector
        $allPeriods = AcademicPeriod::orderBy('start_date', 'desc')->get();

        // Build base query with scope filtering
        $studentsQuery = Student::query();
        $achievementsQuery = StudentAchievement::query();

        // Apply hierarchical filtering
        if ($level === 'faculty' && $facultyId) {
            $studentsQuery->where('faculty_id', $facultyId);
            $achievementsQuery->whereHas('student', function ($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            });
        } elseif ($level === 'department' && $departmentId) {
            $studentsQuery->where('department_id', $departmentId);
            $achievementsQuery->whereHas('student', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        } elseif ($level === 'program_study' && $programStudyId) {
            $studentsQuery->where('program_study_id', $programStudyId);
            $achievementsQuery->whereHas('student', function ($q) use ($programStudyId) {
                $q->where('program_study_id', $programStudyId);
            });
        } elseif ($level === 'graduate_program') {
            // For PPs - traditionally all magister/doctorate students
            $studentsQuery->where('faculty', 'Program Pascasarjana');
            $achievementsQuery->whereHas('student', function ($q) {
                $q->where('faculty', 'Program Pascasarjana');
            });
        } elseif ($level === 'university' || $position === 'super_admin') {
            // No additional filtering needed for university level
        }

        // Filter by selected periods
        if (!empty($selectedPeriods)) {
            $achievementsQuery->whereIn('academic_period_id', $selectedPeriods);
        }

        // Get statistics
        $totalStudents = $studentsQuery->count();
        $totalAchievements = $achievementsQuery->clone()
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->count();

        // Calculate Ratio & Growth for KPI
        $achievementRatio = $totalStudents > 0 ? $totalAchievements / $totalStudents : 0;

        $achievementsPrevious = 0;
        $achievementGrowth = 0;

        if (!empty($selectedPeriods)) {
            // Get the earliest selected period's start date
            $earliestPeriod = AcademicPeriod::whereIn('id', $selectedPeriods)->orderBy('start_date', 'asc')->first();

            if ($earliestPeriod) {
                // Find all periods that ended before this one started
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
                        $achievementGrowth = (($totalAchievements - $achievementsPrevious) / $achievementsPrevious) * 100;
                    } else {
                        $achievementGrowth = $totalAchievements > 0 ? 100 : 0;
                    }
                }
            }
        }

        // Urgent pending alerts (only for validator, not pimpinan)
        $urgentPending = !$isPimpinan ? $achievementsQuery->clone()
            ->where('validation_status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(7))
            ->count() : 0;

        // Status breakdown
        $statusStats = $achievementsQuery->clone()
            ->select('validation_status', DB::raw('count(*) as total'))
            ->groupBy('validation_status')
            ->pluck('total', 'validation_status')
            ->toArray();

        // Recent activities (for pimpinan, only show approved) - limit to 5 unique events
        // Group by event to show competitions, not individual students
        $recentActivities = $achievementsQuery->clone()
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

        // Top 5 students
        $topStudents = Student::query()
            ->when($level === 'faculty' && $facultyId, fn($q) => $q->where('faculty_id', $facultyId))
            ->when($level === 'department' && $departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->when($level === 'program_study' && $programStudyId, fn($q) => $q->where('program_study_id', $programStudyId))
            ->withCount([
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

        // Top 3 GPA per angkatan (only for program_study level - Kaprodi)
        // Limit to 3 most recent angkatan, show all students with same GPA as 3rd rank
        $topGpaByAngkatan = [];
        if ($level === 'program_study' && $programStudyId) {
            $angkatanList = Student::where('program_study_id', $programStudyId)
                ->select('angkatan')
                ->distinct()
                ->orderBy('angkatan', 'desc')
                ->limit(3) // Only 3 most recent angkatan
                ->pluck('angkatan');

            foreach ($angkatanList as $angkatan) {
                // Get top 3 distinct GPA values
                $topGpaValues = Student::where('program_study_id', $programStudyId)
                    ->where('angkatan', $angkatan)
                    ->whereNotNull('gpa')
                    ->where('gpa', '>', 0)
                    ->select('gpa')
                    ->distinct()
                    ->orderBy('gpa', 'desc')
                    ->limit(3)
                    ->pluck('gpa');

                // If we have at least 3 distinct GPA values, get the 3rd one
                // Otherwise, get all students with top GPAs
                if ($topGpaValues->count() >= 3) {
                    $thirdGpa = $topGpaValues->get(2); // 0-indexed, so 2 is the 3rd

                    // Get all students with GPA >= 3rd highest GPA
                    $topGpaByAngkatan[$angkatan] = Student::where('program_study_id', $programStudyId)
                        ->where('angkatan', $angkatan)
                        ->whereNotNull('gpa')
                        ->where('gpa', '>=', $thirdGpa)
                        ->orderBy('gpa', 'desc')
                        ->orderBy('name', 'asc')
                        ->get();
                } else {
                    // Less than 3 distinct GPAs, just get all with top GPAs
                    $topGpaByAngkatan[$angkatan] = Student::where('program_study_id', $programStudyId)
                        ->where('angkatan', $angkatan)
                        ->whereNotNull('gpa')
                        ->where('gpa', '>', 0)
                        ->orderBy('gpa', 'desc')
                        ->orderBy('name', 'asc')
                        ->get();
                }
            }
        }

        // Achievements per angkatan (cohort)
        $achievementsByAngkatan = Student::query()
            ->when($level === 'faculty' && $facultyId, fn($q) => $q->where('faculty_id', $facultyId))
            ->when($level === 'department' && $departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->when($level === 'program_study' && $programStudyId, fn($q) => $q->where('program_study_id', $programStudyId))
            ->select('angkatan')
            ->selectRaw('count(distinct student_id) as student_count')
            ->groupBy('angkatan')
            ->orderBy('angkatan', 'desc')
            ->get()
            ->map(function ($item) use ($selectedPeriods, $level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                $achievementsQuery = \App\Models\StudentAchievement::query()
                    ->whereHas('student', function ($q) use ($item, $level, $facultyId, $departmentId, $programStudyId) {
                        $q->where('angkatan', $item->angkatan);
                        if ($level === 'faculty' && $facultyId) {
                            $q->where('faculty_id', $facultyId);
                        } elseif ($level === 'department' && $departmentId) {
                            $q->where('department_id', $departmentId);
                        } elseif ($level === 'program_study' && $programStudyId) {
                            $q->where('program_study_id', $programStudyId);
                        }
                    });

                if ($isPimpinan) {
                    $achievementsQuery->whereIn('validation_status', ['faculty_approved', 'university_approved']);
                }

                if (!empty($selectedPeriods)) {
                    $achievementsQuery->whereIn('academic_period_id', $selectedPeriods);
                }

                $item->achievements_count = $achievementsQuery->count();
                return $item;
            });

        // Category distribution
        $categoryDistribution = $achievementsQuery->clone()
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->join('achievement_categories', 'achievements.category_id', '=', 'achievement_categories.id')
            ->select('achievement_categories.name', DB::raw('count(*) as total'))
            ->groupBy('achievement_categories.id', 'achievement_categories.name')
            ->orderBy('total', 'desc')
            ->get();

        // Level distribution
        $levelDistribution = $achievementsQuery->clone()
            ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
            ->select('student_achievements.level as name', DB::raw('count(*) as total'))
            ->groupBy('student_achievements.level')
            ->orderBy('total', 'desc')
            ->get();

        // Calculate Percentages for KPI
        $nationalCount = $levelDistribution->where('name', 'Nasional')->first()?->total ?? 0;
        $internationalCount = $levelDistribution->where('name', 'Internasional')->first()?->total ?? 0;

        $nationalPercentage = $totalAchievements > 0 ? ($nationalCount / $totalAchievements) * 100 : 0;
        $internationalPercentage = $totalAchievements > 0 ? ($internationalCount / $totalAchievements) * 100 : 0;

        // Calculate Active Units KPI (e.g., 7 dari 9 Fakultas)
        $activeUnitsCount = 0;
        $totalUnitsCount = 0;
        $unitLabel = 'Unit';

        if ($level === 'university') {
            $unitLabel = 'Fakultas';
            $totalUnitsCount = Student::whereNotNull('faculty_id')->distinct('faculty_id')->count();
            $activeUnitsCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->whereNotNull('faculty_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.faculty_id')
                ->count('students.faculty_id');
        } elseif ($level === 'faculty') {
            $unitLabel = 'Jurusan';
            $totalUnitsCount = Student::where('faculty_id', $facultyId)->whereNotNull('department_id')->distinct('department_id')->count();
            $activeUnitsCount = StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('faculty_id', $facultyId)->whereNotNull('department_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.department_id')
                ->count('students.department_id');
        } elseif ($level === 'department') {
            $unitLabel = 'Prodi';
            $totalUnitsCount = Student::where('department_id', $departmentId)->whereNotNull('program_study_id')->distinct('program_study_id')->count();
            $activeUnitsCount = \App\Models\StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('department_id', $departmentId)->whereNotNull('program_study_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.program_study_id')
                ->count('students.program_study_id');
        } elseif ($level === 'program_study') {
            $unitLabel = 'Angkatan';
            $totalUnitsCount = Student::where('program_study_id', $programStudyId)->whereNotNull('angkatan')->distinct('angkatan')->count();
            $activeUnitsCount = \App\Models\StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('program_study_id', $programStudyId)->whereNotNull('angkatan'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.angkatan')
                ->count('students.angkatan');
        } elseif ($level === 'graduate_program') {
            $unitLabel = 'Prodi PPs';
            $totalUnitsCount = Student::where('faculty', 'Program Pascasarjana')->whereNotNull('program_study_id')->distinct('program_study_id')->count();
            $activeUnitsCount = \App\Models\StudentAchievement::query()
                ->whereIn('validation_status', ['faculty_approved', 'university_approved'])
                ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('academic_period_id', $selectedPeriods))
                ->whereHas('student', fn($q) => $q->where('faculty', 'Program Pascasarjana')->whereNotNull('program_study_id'))
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->distinct('students.program_study_id')
                ->count('students.program_study_id');
        }

        // Hierarchical comparisons and drill-down data (only for pimpinan)
        $hierarchicalComparison = [];
        $categoryByHierarchy = [];
        $levelByHierarchy = [];
        $efficiencyRanking = collect();

        if ($isPimpinan) {
            // Get hierarchical comparison data based on level
            $hierarchicalComparison = $this->getHierarchicalComparison($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods);

            // Only get detailed hierarchy data if we have comparison data
            if (!empty($hierarchicalComparison)) {
                $categoryByHierarchy = $this->getCategoryByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods);
                $levelByHierarchy = $this->getLevelByHierarchy($level, $facultyId, $departmentId, $programStudyId, $selectedPeriods);

                // Calculate Efficiency Ranking (Achievements / Students)
                $efficiencyRanking = $hierarchicalComparison['items']->map(function ($item) {
                    $ratio = $item->students_count > 0 ? ($item->achievements_count / $item->students_count) : 0;
                    return [
                        'name' => $item->faculty ?? $item->department ?? $item->program_study ?? $item->angkatan_label ?? $item->angkatan ?? 'Unknown',
                        'ratio' => $ratio,
                        'achievements' => $item->achievements_count,
                        'students' => $item->students_count
                    ];
                })->sortByDesc('ratio')->values()->take(10);
            }
        }

        // Achievements per period
        $achievementsPerPeriod = AcademicPeriod::query()
            ->when(!empty($selectedPeriods), fn($q) => $q->whereIn('id', $selectedPeriods))
            ->withCount([
                'achievements' => function ($q) use ($level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                    if ($isPimpinan) {
                        $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
                    }
                    $q->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                        if ($level === 'faculty' && $facultyId) {
                            $sq->where('faculty_id', $facultyId);
                        } elseif ($level === 'department' && $departmentId) {
                            $sq->where('department_id', $departmentId);
                        } elseif ($level === 'program_study' && $programStudyId) {
                            $sq->where('program_study_id', $programStudyId);
                        }
                    });
                }
            ])
            ->orderBy('start_date')
            ->get();

        // Trend Data (Last 6 Semesters)
        $achievementTrend = AcademicPeriod::query()
            ->orderBy('end_date', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->map(function ($period) use ($level, $facultyId, $departmentId, $programStudyId, $isPimpinan) {
                $baseQuery = StudentAchievement::query()
                    ->where('academic_period_id', $period->id)
                    ->when($isPimpinan, fn($q) => $q->whereIn('validation_status', ['faculty_approved', 'university_approved']))
                    ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                        if ($level === 'faculty' && $facultyId) {
                            $sq->where('faculty_id', $facultyId);
                        } elseif ($level === 'department' && $departmentId) {
                            $sq->where('department_id', $departmentId);
                        } elseif ($level === 'program_study' && $programStudyId) {
                            $sq->where('program_study_id', $programStudyId);
                        }
                    });

                return [
                    'label' => $period->name_short ?? $period->name,
                    'academic' => (clone $baseQuery)->whereHas('achievement', fn($q) => $q->where('category_id', 1))->count(),
                    'non_academic' => (clone $baseQuery)->whereHas('achievement', fn($q) => $q->where('category_id', '!=', 1))->count(),
                    'total' => (clone $baseQuery)->count()
                ];
            })->values();

        // Generate Risk Indicators & Insights
        $riskIndicators = [];

        if ($isPimpinan) {
            // 1. Units with 0 national achievements
            if (!empty($hierarchicalComparison) && isset($hierarchicalComparison['items'])) {
                $noNational = $hierarchicalComparison['items']->filter(function ($unit) use ($selectedPeriods) {
                    return \App\Models\StudentAchievement::whereIn('validation_status', ['faculty_approved', 'university_approved'])
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

            // 2. Significant achievement drop (>20%)
            if ($achievementGrowth < -20) {
                $riskIndicators[] = [
                    'message' => "Sistem mendeteksi penurunan kuantitas prestasi sebesar " . round(abs($achievementGrowth), 1) . "% dibandingkan periode sebelumnya.",
                    'type' => 'danger',
                    'icon' => 'trending-down'
                ];
            }

            // 3. Validation bottlenecks (> 7 days)
            $bottlenecks = StudentAchievement::whereIn('validation_status', ['submitted', 'faculty_review', 'university_review'])
                ->where('submitted_at', '<', now()->subDays(7))
                ->whereHas('student', function ($sq) use ($level, $facultyId, $departmentId, $programStudyId) {
                    if ($level === 'faculty' && $facultyId)
                        $sq->where('faculty_id', $facultyId);
                    elseif ($level === 'department' && $departmentId)
                        $sq->where('department_id', $departmentId);
                    elseif ($level === 'program_study' && $programStudyId)
                        $sq->where('program_study_id', $programStudyId);
                })
                ->count();

            if ($bottlenecks > 0) {
                $riskIndicators[] = [
                    'message' => "Sebanyak {$bottlenecks} pengajuan prestasi melampaui batas waktu validasi (> 7 hari) dan memerlukan tindakan segera.",
                    'type' => 'danger',
                    'icon' => 'clock'
                ];
            }

            // 4. Low Participation Ratio (< 30% of units active)
            if ($totalUnitsCount > 0 && ($activeUnitsCount / $totalUnitsCount) < 0.3) {
                $riskIndicators[] = [
                    'message' => "Tingkat partisipasi unit aktif baru mencapai " . round(($activeUnitsCount / $totalUnitsCount) * 100) . "%, di bawah target optimal 30%.",
                    'type' => 'warning',
                    'icon' => 'users'
                ];
            }
        }

        $riskIndicators = array_slice($riskIndicators, 0, 5);

        // Hierarchical data for pimpinan (avoid duplicate processing)
        $hierarchicalData = $hierarchicalComparison; // Use already processed data

        // For validator: get pending achievements and filters
        if (!$isPimpinan) {
            $pendingQuery = $achievementsQuery->clone()
                ->with(['student', 'achievement.category', 'documents'])
                ->where('validation_status', 'submitted');

            // Apply search filter
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

            // Apply category filter
            if ($request->filled('category')) {
                $pendingQuery->whereHas('achievement', function ($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }

            // Apply level filter
            if ($request->filled('level')) {
                $pendingQuery->where('level', $request->level);
            }

            $pendingAchievements = $pendingQuery->orderBy('submitted_at', 'asc')->paginate(15);

            // Get categories and levels for filters
            $categories = \App\Models\AchievementCategory::orderBy('name')->get();
            $levels = StudentAchievement::distinct()->pluck('level')->filter()->values()->toArray();
        } else {
            // For pimpinan: empty collections
            $pendingAchievements = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $categories = collect();
            $levels = [];
        }

        // Set route prefix based on role
        $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';

        // Use same view for both roles
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

                    $item->achievements_count = \App\Models\StudentAchievement::query()
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
                    $item->achievements_count = \App\Models\StudentAchievement::query()
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
                    $item->achievements_count = \App\Models\StudentAchievement::query()
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
                    $item->achievements_count = \App\Models\StudentAchievement::query()
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
                    $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                        $item->achievements_count = \App\Models\StudentAchievement::query()
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
                $item->achievements_count = \App\Models\StudentAchievement::query()
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

}
