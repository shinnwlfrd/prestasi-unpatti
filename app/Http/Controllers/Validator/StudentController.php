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
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

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

        $departmentList = Student::query()
            ->when($level === 'faculty' && $facultyId, fn($q) => $q->where('faculty_id', $facultyId))
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department', 'asc')
            ->pluck('department');

        // Get mapping of Department to Program Study (already group by dept)
        $prodiMapping = Student::query()
            ->when($level === 'faculty' && $facultyId, fn($q) => $q->where('faculty_id', $facultyId))
            ->select('department', 'program_study')
            ->whereNotNull('department')
            ->whereNotNull('program_study')
            ->distinct()
            ->get();

        $deptToProdi = $prodiMapping->groupBy('department')
            ->map(fn($items) => $items->pluck('program_study')->unique()->values());

        $prodiToDept = $prodiMapping->pluck('department', 'program_study');

        $allProdis = $prodiMapping->pluck('program_study')->unique()->sort()->values();

        return view('validator.students.index', compact('students', 'angkatanList', 'departmentList', 'deptToProdi', 'prodiToDept', 'allProdis'));
    }

    /**
     * Show student detail with achievements
     */
    public function show($studentId)
    {
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

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
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id');
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id');
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id');

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
