<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\AchievementService;

class DashboardController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {
    }

    public function index()
    {
        $studentId = session('student_id');

        if (!$studentId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Try to get student from database (if has achievements)
        $student = \App\Models\Student::find($studentId);

        // If student not found in database, use session data (first-time SSO login)
        if (!$student) {
            $studentData = session('student_data');

            if (!$studentData) {
                return redirect()->route('login')->with('error', 'Student data not found in session.');
            }

            \Log::info('Creating virtual student from session', [
                'student_id' => $studentId,
                'has_foto_url' => !empty($studentData['foto_url']),
                'foto_url_value' => $studentData['foto_url'] ?? null,
                'has_ipk' => !empty($studentData['ipk']),
                'ipk_value' => $studentData['ipk'] ?? null,
                'session_keys' => array_keys($studentData)
            ]);

            // Create a virtual student object from session data
            $student = new \App\Models\Student([
                'student_id' => $studentData['nim'] ?? $studentData['student_id'] ?? '-',
                'name' => $studentData['nama'] ?? $studentData['name'] ?? '-',
                'email' => $studentData['email'] ?? '-',
                'faculty' => $studentData['fakultas'] ?? $studentData['faculty'] ?? 'Data Belum Tersedia',
                'faculty_id' => $studentData['fakultas_id'] ?? $studentData['faculty_id'] ?? null,
                'department' => $studentData['jurusan'] ?? $studentData['department'] ?? 'Data Belum Tersedia',
                'department_id' => $studentData['jurusan_id'] ?? $studentData['department_id'] ?? null,
                'program_study' => $studentData['program_studi'] ?? $studentData['program_study'] ?? 'Data Belum Tersedia',
                'program_study_id' => $studentData['program_studi_id'] ?? $studentData['program_study_id'] ?? null,
                'angkatan' => $studentData['angkatan'] ?? substr($studentData['nim'] ?? $studentId, 0, 4),
                'gpa' => $studentData['ipk'] ?? $studentData['gpa'] ?? 0,
                'photo_url' => $studentData['foto_url'] ?? $studentData['photo_url'] ?? null,
            ]);

            // Mark as virtual (not persisted)
            $student->exists = false;
        }

        $achievements = $this->achievementService->getStudentAchievements($studentId);
        $stats = $this->achievementService->getAchievementStatistics($studentId);

        return view('student.dashboard', compact('student', 'achievements', 'stats'));
    }
}
