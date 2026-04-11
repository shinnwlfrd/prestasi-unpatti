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
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $filters = $request->validated();

        // Scope data based on current role if not super admin
        if (!$user->isSuperAdmin()) {
            if ($currentRole && $currentRole->level !== 'university') {
                if ($currentRole->faculty_id) {
                    $filters['faculty_id'] = $currentRole->faculty_id;
                    $request->merge(['faculty_id' => $currentRole->faculty_id]);
                }
                if ($currentRole->department_id) {
                    $filters['department_id'] = $currentRole->department_id;
                    $request->merge(['department_id' => $currentRole->department_id]);
                }
                if ($currentRole->program_study_id) {
                    $filters['program_study_id'] = $currentRole->program_study_id;
                    $request->merge(['program_study_id' => $currentRole->program_study_id]);
                }
            }
        }

        $students = $this->studentService->getFilteredStudents($filters, 15);
        $faculties = $this->studentService->getFaculties();
        $stats = $this->studentService->getStatistics();
        
        // Get dynamic angkatan list from actual student data
        $angkatanList = Student::select('angkatan')
            ->distinct()
            ->whereNotNull('angkatan')
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');
        
        // Get SIGAP data for cascade filter
        $sigapService = app(\App\Services\SigapApiService::class);
        
        // Only show faculty selection if user is Super Admin or University-level
        $sigapFaculties = collect();
        if ($user->isSuperAdmin() || ($currentRole && $currentRole->level === 'university')) {
            $sigapFaculties = collect($sigapService->getFaculties());
        }
        
        // Get departments based on selected faculty or scoped faculty
        $sigapDepartments = collect();
        $targetFacultyId = $filters['faculty_id'] ?? $request->faculty_id;
        
        if ($targetFacultyId) {
            $sigapDepartments = collect($sigapService->getDepartments($targetFacultyId));
        }
        
        // Get study programs based on selected department or scoped department
        $sigapStudyPrograms = collect();
        $targetDepartmentId = $filters['department_id'] ?? $request->department_id;
        
        if ($targetDepartmentId) {
            $sigapStudyPrograms = collect($sigapService->getStudyPrograms($targetDepartmentId));
        }

        return view('admin.students.index', [
            'students' => $students,
            'faculties' => $faculties,
            'facultyCount' => $stats['faculty_count'],
            'angkatanList' => $angkatanList,
            'sigapFaculties' => $sigapFaculties,
            'sigapDepartments' => $sigapDepartments,
            'sigapStudyPrograms' => $sigapStudyPrograms,
            'selectedFaculty' => $targetFacultyId,
            'selectedDepartment' => $targetDepartmentId,
            'selectedStudyProgram' => $filters['program_study_id'] ?? $request->program_study_id,
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
