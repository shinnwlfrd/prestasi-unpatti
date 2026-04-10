<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display list of students with filters
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null);
        $facultyName = $currentRole ? ($currentRole->faculty_name ?? $user->faculty) : (session('operator_faculty_name') ?? session('pimpinan_faculty_name') ?? $user->faculty ?? null);
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id') ?: null);
        $departmentName = $currentRole ? $currentRole->department_name : (session('operator_department_name') ?? session('pimpinan_department_name') ?? null);
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null);

        $query = Student::with([
            'achievements' => function ($q) {
                $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
            }
        ]);

        // Apply scope filtering
        if ($level === 'faculty') {
            if ($facultyId) {
                $query->where('faculty_id', $facultyId);
            } elseif ($facultyName) {
                $query->where('faculty', $facultyName);
            }
        } elseif ($level === 'department') {
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            } elseif ($departmentName) {
                $query->where('department', $departmentName);
            }
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->where('program_study_id', $programStudyId);
        } elseif ($level === 'graduate_program') {
            $query->where('faculty', 'Program Pascasarjana');
        }

        // Apply filters
        $this->applyFilters($query, $request);

        // Sort by achievements count
        $students = $query->withCount([
            'achievements' => function ($q) {
                $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
            }
        ])
            ->orderBy('achievements_count', 'desc')
            ->paginate(20);

        // Get scoped collection lists
        $angkatanList = $this->getAngkatanList($level, $facultyId, $departmentId, $programStudyId, $facultyName, $departmentName);
        $facultyList = $this->getFacultyList($level, $facultyId, $facultyName);
        $hierarchyData = $this->getHierarchyData($level, $facultyId, $departmentId, $facultyName, $departmentName);

        // UI Visibility Flags
        $showFaculty = ($level === 'university');
        $showDepartment = in_array($level, ['university', 'faculty']);
        $showProgramStudy = in_array($level, ['university', 'faculty', 'department', 'graduate_program']);
        $showGPA = true; // Always show GPA as it is a student metric
        $isPimpinanRoute = request()->routeIs('pimpinan.*');
        $routePrefix = $isPimpinanRoute ? 'pimpinan' : 'validator';

        return view('validator.students.index', array_merge([
            'students' => $students,
            'angkatanList' => $angkatanList,
            'facultyList' => $facultyList,
            'showFaculty' => $showFaculty,
            'showDepartment' => $showDepartment,
            'showProgramStudy' => $showProgramStudy,
            'showGPA' => $showGPA,
            'routePrefix' => $routePrefix,
            'level' => $level,
        ], $hierarchyData));
    }

    /**
     * Apply search and attribute filters to the query
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('student_id', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        foreach (['angkatan', 'program_study', 'department', 'faculty'] as $filter) {
            if ($request->filled($filter)) {
                $operator = $filter === 'angkatan' ? '=' : 'ilike';
                $value = $filter === 'angkatan' ? $request->$filter : "%{$request->$filter}%";
                $query->where($filter, $operator, $value);
            }
        }
    }

    /**
     * Get list of unique student angkatan
     */
    private function getAngkatanList($level, $facultyId, $departmentId, $programStudyId, $facultyName = null, $departmentName = null)
    {
        return Student::query()
            ->when($level === 'faculty', function($q) use ($facultyId, $facultyName) {
                return $facultyId ? $q->where('faculty_id', $facultyId) : $q->where('faculty', $facultyName);
            })
            ->when($level === 'department', function($q) use ($departmentId, $departmentName) {
                return $departmentId ? $q->where('department_id', $departmentId) : $q->where('department', $departmentName);
            })
            ->when($level === 'program_study' && $programStudyId, fn($q) => $q->where('program_study_id', $programStudyId))
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');
    }

    /**
     * Get list of unique faculties
     */
    private function getFacultyList($level, $facultyId, $facultyName = null)
    {
        $sigapService = app(\App\Services\SigapApiService::class);
        $faculties = $sigapService->getFaculties();
        
        if ($level !== 'university') {
            if ($facultyId) {
                $faculties = collect($faculties)->filter(fn($f) => $f['id'] == $facultyId)->all();
            } elseif ($facultyName) {
                $faculties = collect($faculties)->filter(fn($f) => 
                    ($f['nama_en'] ?? '') === $facultyName || ($f['nama'] ?? '') === $facultyName
                )->all();
            }
        }
        
        return collect($faculties)->map(fn($f) => $f['nama_en'] ?? $f['nama'])->unique()->sort()->values();
    }

    private function getHierarchyData($level, $facultyId, $departmentId = null, $facultyName = null, $departmentName = null)
    {
        $sigapService = app(\App\Services\SigapApiService::class);
        $structure = $sigapService->getHierarchicalStructure();
        
        $facultyToDept = [];
        $deptToFaculty = [];
        $deptToProdi = [];
        $prodiToDept = [];
        $prodiToFaculty = [];
        $allDepts = [];
        $allProdis = [];

        // Filter structure based on user level scope
        if ($level !== 'university') {
            if ($facultyId) {
                $structure = collect($structure)->filter(fn($f) => $f['id'] == $facultyId)->all();
            } elseif ($facultyName) {
                $structure = collect($structure)->filter(fn($f) => 
                    ($f['nama_en'] ?? '') === $facultyName || ($f['nama'] ?? '') === $facultyName
                )->all();
            }
        }

        foreach ($structure as $faculty) {
            $fName = $faculty['nama_en'] ?? $faculty['nama'];
            if (!$fName) continue;
            
            $facultyToDept[$fName] = [];
            
            foreach ($faculty['departments'] ?? [] as $dept) {
                $dName = $dept['nama_en'] ?? $dept['nama'];
                if (!$dName) continue;
                
                // Skip departments that don't match for department-level users
                if ($level === 'department') {
                    if ($departmentId && isset($dept['id']) && $dept['id'] != $departmentId) {
                        continue;
                    } elseif ($departmentName && $dName !== $departmentName && ($dept['nama'] ?? '') !== $departmentName) {
                        continue;
                    }
                }
                
                $facultyToDept[$fName][] = $dName;
                $deptToFaculty[$dName] = $fName;
                $allDepts[] = $dName;
                
                if (!isset($deptToProdi[$dName])) {
                    $deptToProdi[$dName] = [];
                }
                
                foreach ($dept['study_programs'] ?? [] as $prodi) {
                    $pName = $prodi['nama_en'] ?? $prodi['nama'];
                    if (!$pName) continue;
                    
                    $deptToProdi[$dName][] = $pName;
                    $prodiToDept[$pName] = $dName;
                    $prodiToFaculty[$pName] = $fName;
                    $allProdis[] = $pName;
                }
            }
        }
        
        return [
            'facultyToDept' => $facultyToDept,
            'deptToFaculty' => $deptToFaculty,
            'deptToProdi' => $deptToProdi,
            'prodiToDept' => $prodiToDept,
            'prodiToFaculty' => $prodiToFaculty,
            'allDepts' => collect($allDepts)->unique()->sort()->values(),
            'allProdis' => collect($allProdis)->unique()->sort()->values(),
        ];
    }


    /**
     * Show student detail with achievements
     */
    public function show($studentId)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null);
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id') ?: null);
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null);

        $student = Student::with(['achievements.achievement.category'])
            ->findOrFail($studentId);

        // Check access
        if ($level === 'faculty' && $student->faculty_id !== $facultyId) {
            abort(403, 'Anda tidak memiliki akses ke mahasiswa ini.');
        } elseif ($level === 'department' && $student->department_id !== $departmentId) {
            abort(403, 'Anda tidak memiliki akses ke mahasiswa ini.');
        } elseif ($level === 'program_study' && $student->program_study_id !== $programStudyId) {
            abort(403, 'Anda tidak memiliki akses ke mahasiswa ini.');
        }

        $achievements = $student->achievements()
            ->with(['achievement.category'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        // Statistics
        $stats = [
            'total' => $student->achievements()->count(),
            'approved' => $student->achievements()->whereIn('validation_status', ['faculty_approved', 'university_approved'])->count(),
            'pending' => $student->achievements()->whereIn('validation_status', ['submitted', 'faculty_review', 'pending'])->count(),
            'rejected' => $student->achievements()->where('validation_status', 'rejected')->count(),
        ];

        return view('validator.students.show', compact('student', 'achievements', 'stats'));
    }

    /**
     * Search students (AJAX endpoint for multi-select)
     */
    public function search(Request $request)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null);
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id') ?: null);
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null);

        $query = Student::query();

        // Apply scope filtering
        if ($level === 'faculty' && $facultyId) {
            $query->where('faculty_id', $facultyId);
        } elseif ($level === 'department' && $departmentId) {
            $query->where('department_id', $departmentId);
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->where('program_study_id', $programStudyId);
        }

        // Search by name or NIM
        $search = $request->input('q', '');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('student_id', 'ilike', "%{$search}%");
            });
        }

        $students = $query->select('id', 'student_id', 'name', 'program_study', 'angkatan')
            ->limit(20)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'name' => $student->name,
                    'program_study' => $student->program_study,
                    'angkatan' => $student->angkatan,
                    'label' => "{$student->name} ({$student->student_id})",
                ];
            });

        return response()->json($students);
    }
}
