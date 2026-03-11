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
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

        $query = Student::with([
            'achievements' => function ($q) {
                $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
            }
        ]);

        // Apply scope filtering
        if ($level === 'faculty' && $facultyId) {
            $query->where('faculty_id', $facultyId);
        } elseif ($level === 'department' && $departmentId) {
            $query->where('department_id', $departmentId);
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->where('program_study_id', $programStudyId);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('student_id', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->filled('program_study')) {
            $query->where('program_study', 'ilike', "%{$request->program_study}%");
        }

        if ($request->filled('department')) {
            $query->where('department', 'ilike', "%{$request->department}%");
        }

        if ($request->filled('faculty')) {
            $query->where('faculty', 'ilike', "%{$request->faculty}%");
        }

        // Sort by achievements count
        $students = $query->withCount([
            'achievements' => function ($q) {
                $q->whereIn('validation_status', ['faculty_approved', 'university_approved']);
            }
        ])
            ->orderBy('achievements_count', 'desc')
            ->paginate(20);

        // Get unique angkatan for filter
        $angkatanList = Student::query()
            ->when($level === 'faculty' && $facultyId, fn($q) => $q->where('faculty_id', $facultyId))
            ->when($level === 'department' && $departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->when($level === 'program_study' && $programStudyId, fn($q) => $q->where('program_study_id', $programStudyId))
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');

        // Get faculty list for super validator
        $facultyList = collect();
        if ($level === 'university') {
            $facultyList = Student::query()
                ->whereNotNull('faculty')
                ->distinct()
                ->orderBy('faculty', 'asc')
                ->pluck('faculty');
        }

        // Get all hierarchical mappings
        $hierarchyData = Student::query()
            ->when($level === 'faculty' && $facultyId, fn($q) => $q->where('faculty_id', $facultyId))
            ->select('faculty', 'department', 'program_study')
            ->whereNotNull('faculty')
            ->whereNotNull('department')
            ->whereNotNull('program_study')
            ->distinct()
            ->get();

        // Faculty to Department mapping
        $facultyToDept = $hierarchyData->groupBy('faculty')
            ->map(fn($items) => $items->pluck('department')->unique()->sort()->values());

        // Department to Faculty mapping (reverse) - use groupBy to handle properly
        $deptToFaculty = [];
        foreach ($hierarchyData as $item) {
            if (!isset($deptToFaculty[$item->department])) {
                $deptToFaculty[$item->department] = $item->faculty;
            }
        }

        // Department to Program Study mapping
        $deptToProdi = $hierarchyData->groupBy('department')
            ->map(fn($items) => $items->pluck('program_study')->unique()->sort()->values());

        // Program Study to Department mapping (reverse) - use groupBy to handle properly
        $prodiToDept = [];
        foreach ($hierarchyData as $item) {
            if (!isset($prodiToDept[$item->program_study])) {
                $prodiToDept[$item->program_study] = $item->department;
            }
        }

        // Program Study to Faculty mapping (direct)
        $prodiToFaculty = [];
        foreach ($hierarchyData as $item) {
            if (!isset($prodiToFaculty[$item->program_study])) {
                $prodiToFaculty[$item->program_study] = $item->faculty;
            }
        }

        // All departments and prodis for fallback
        $allDepts = $hierarchyData->pluck('department')->unique()->sort()->values();
        $allProdis = $hierarchyData->pluck('program_study')->unique()->sort()->values();

        return view('validator.students.index', compact(
            'students', 
            'angkatanList', 
            'facultyList', 
            'facultyToDept',
            'deptToFaculty',
            'deptToProdi', 
            'prodiToDept',
            'prodiToFaculty',
            'allDepts',
            'allProdis'
        ));
    }

    /**
     * Show student detail with achievements
     */
    public function show($studentId)
    {
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

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
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

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
