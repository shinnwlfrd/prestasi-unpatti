<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexStudentRequest;
use App\Services\Admin\StudentManagementService;

class StudentController extends Controller
{
    public function __construct(
        protected StudentManagementService $studentService
    ) {}

    public function index(IndexStudentRequest $request)
    {
        $students = $this->studentService->getFilteredStudents(
            $request->validated(),
            15
        );

        $faculties = $this->studentService->getFaculties();
        $stats = $this->studentService->getStatistics();
        
        // Get SIGAP data for cascade filter
        $sigapService = app(\App\Services\SigapApiService::class);
        
        // Get all faculties
        $sigapFaculties = collect($sigapService->getFaculties());
        
        // Get departments based on selected faculty
        $sigapDepartments = collect();
        if ($request->filled('faculty_id')) {
            $sigapDepartments = collect($sigapService->getDepartments($request->faculty_id));
        }
        
        // Get study programs based on selected department
        $sigapStudyPrograms = collect();
        if ($request->filled('department_id')) {
            $sigapStudyPrograms = collect($sigapService->getStudyPrograms($request->department_id));
        }

        return view('admin.students.index', [
            'students' => $students,
            'faculties' => $faculties,
            'facultyCount' => $stats['faculty_count'],
            'sigapFaculties' => $sigapFaculties,
            'sigapDepartments' => $sigapDepartments,
            'sigapStudyPrograms' => $sigapStudyPrograms,
            'selectedFaculty' => $request->input('faculty_id'),
            'selectedDepartment' => $request->input('department_id'),
            'selectedStudyProgram' => $request->input('program_study_id'),
        ]);
    }

    /**
     * Show student detail with their achievements
     */
    public function show($studentId)
    {
        $student = \App\Models\Student::where('student_id', $studentId)->firstOrFail();
        
        $achievements = \App\Models\StudentAchievement::with([
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
