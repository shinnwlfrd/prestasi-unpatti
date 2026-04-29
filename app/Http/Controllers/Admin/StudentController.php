<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexStudentRequest;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Services\Admin\StudentManagementService;

class StudentController extends Controller
{
    public function __construct(
        protected StudentManagementService $studentService
    ) {}

    public function index(IndexStudentRequest $request)
    {
        $filters = $request->validated();
        
        $students = $this->studentService->getFilteredStudents($filters, 15);
        $faculties = $this->studentService->getFaculties();
        $stats = $this->studentService->getStatistics();
        $angkatanList = $this->studentService->getAngkatanList();
        
        // Get SIGAP data for cascade filter
        $sigapData = $this->studentService->getSigapCascadeData($filters);
        
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        return view('admin.students.index', [
            'students' => $students,
            'faculties' => $faculties,
            'facultyCount' => $stats['faculty_count'],
            'angkatanList' => $angkatanList,
            'sigapFaculties' => $sigapData['faculties'],
            'sigapDepartments' => $sigapData['departments'],
            'sigapStudyPrograms' => $sigapData['study_programs'],
            'selectedFaculty' => $filters['faculty_id'] ?? null,
            'selectedDepartment' => $filters['department_id'] ?? null,
            'selectedStudyProgram' => $filters['program_study_id'] ?? null,
            'isFacultyScoped' => ($currentRole && $currentRole->level !== 'university' && $currentRole->faculty_id),
            'currentRole' => $currentRole
        ]);
    }

    /**
     * Show student detail with their achievements
     */
    public function show($studentId)
    {
        $student = Student::where('student_id', $studentId)->firstOrFail();
        
        $achievements = StudentAchievement::with([
            'achievement.category',
            'student'
        ])
        ->where('student_id', $studentId)
        ->orderBy('submitted_at', 'desc')
        ->orderBy('sa_id', 'desc')
        ->paginate(10);

        return view('admin.students.show', compact('student', 'achievements'));
    }
}
