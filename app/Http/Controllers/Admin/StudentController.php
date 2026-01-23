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

        return view('admin.students.index', [
            'students' => $students,
            'faculties' => $faculties,
            'facultyCount' => $stats['faculty_count'],
            'avgGpa' => $stats['avg_gpa'],
        ]);
    }
}
