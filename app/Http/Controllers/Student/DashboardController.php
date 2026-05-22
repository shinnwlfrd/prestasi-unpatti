<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Student\AchievementService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {}

    public function index()
    {
        $studentId = session('student_id');

        if (! $studentId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Try to get student from database (if has achievements)
        $student = Student::withTrashed()->find($studentId);

        // Block if soft-deleted
        if ($student && $student->trashed()) {
            return redirect()->route('login')->with('error', 'Akun mahasiswa Anda telah dinonaktifkan.');
        }

        // If student not found in database, use session data (first-time SSO login)
        if (! $student) {
            $studentData = session('student_data', []);

            if (empty($studentData)) {
                Log::warning('Empty student data in session for student ID', ['student_id' => $studentId]);

                return redirect()->route('login')->with('error', 'Data mahasiswa tidak lengkap di session.');
            }

            // Create a virtual student object from session data
            // Use null for missing SIAKAD fields - the view will display "Tidak Ada Data"
            $rawIpk = $studentData['ipk'] ?? $studentData['gpa'] ?? null;
            $gpaValue = is_numeric($rawIpk) ? (float) $rawIpk : null;

            $student = new Student([
                'student_id' => $studentData['nim'] ?? $studentData['student_id'] ?? $studentId,
                'name' => $studentData['nama'] ?? $studentData['name'] ?? 'Data tidak tersedia',
                'email' => $studentData['email'] ?? '-',
                'faculty' => $studentData['fakultas'] ?? $studentData['faculty'] ?? null,
                'faculty_id' => $studentData['fakultas_id'] ?? $studentData['faculty_id'] ?? null,
                'department' => $studentData['jurusan'] ?? $studentData['department'] ?? null,
                'department_id' => $studentData['jurusan_id'] ?? $studentData['department_id'] ?? null,
                'program_study' => $studentData['program_studi'] ?? $studentData['program_study'] ?? null,
                'program_study_id' => $studentData['program_studi_id'] ?? $studentData['program_study_id'] ?? null,
                'angkatan' => $studentData['angkatan'] ?? substr($studentData['nim'] ?? $studentId, 0, 4),
                'gpa' => $gpaValue,
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
