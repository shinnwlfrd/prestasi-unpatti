<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Models\SKDocument;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;

class SKDocumentController extends Controller
{
    public function index()
    {
        $skDocuments = SKDocument::with('creator')
            ->withCount('assignments')
            ->orderBy('issued_date', 'desc')
            ->paginate(20);

        return view('validator.sk.index', compact('skDocuments'));
    }

    public function show(SKDocument $sk)
    {
        $sk->load(['creator', 'assignments.achievement.student', 'assignments.assignedBy']);

        return view('validator.sk.show', compact('sk'));
    }

    public function getAchievements(SKDocument $sk)
    {
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();

        // Use database-backed active role for more robust scope detection
        $level = $currentRole ? $currentRole->level : (session('operator_level') ?? session('pimpinan_level'));
        $facultyId = $currentRole ? $currentRole->faculty_id : (session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null);
        $departmentId = $currentRole ? $currentRole->department_id : (session('operator_department_id') ?? session('pimpinan_department_id') ?: null);
        $programStudyId = $currentRole ? $currentRole->program_study_id : (session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null);

        // Get pending achievements for AJAX request
        $query = StudentAchievement::with(['student', 'achievement.category', 'academicPeriod'])
            ->where(function ($q) {
                $q->facultyPending() // New system: submitted, faculty_review
                    ->orWhere('validation_status', 'Menunggu'); // Legacy
            })
            ->whereDoesntHave('skAssignment');

        // Apply scope filtering based on active role level
        if ($level === 'faculty' && $facultyId) {
            $query->whereHas('student', fn($q) => $q->where('faculty_id', $facultyId));
        } elseif ($level === 'department' && $departmentId) {
            $query->whereHas('student', fn($q) => $q->where('department_id', $departmentId));
        } elseif ($level === 'program_study' && $programStudyId) {
            $query->whereHas('student', fn($q) => $q->where('program_study_id', $programStudyId));
        } elseif (!$level) {
            // Fallback to user->faculty string if no session level
            $faculty = $user->faculty;
            if ($faculty) {
                $query->whereHas('student', fn($q) => $q->where('faculty', $faculty));
            }
        }

        $achievements = $query->orderBy('submitted_at', 'desc')->get();

        return response()->json([
            'achievements' => $achievements
        ]);
    }

    public function processAssignment(Request $request, SKDocument $sk)
    {
        $validated = $request->validate([
            'achievement_ids' => 'required|array|min:1',
            'achievement_ids.*' => 'exists:student_achievements,sa_id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignedCount = 0;
        $assignedAt = now();
        $userId = auth()->id();

        // Use session-based scope for security check
        $level = session('operator_level') ?? session('pimpinan_level');
        $facultyId = session('operator_faculty_id') ?? session('pimpinan_faculty_id') ?: null;
        $departmentId = session('operator_department_id') ?? session('pimpinan_department_id') ?: null;
        $programStudyId = session('operator_program_study_id') ?? session('pimpinan_program_study_id') ?: null;

        foreach ($validated['achievement_ids'] as $saId) {
            $achievement = StudentAchievement::with('student')->find($saId);

            // Skip if already has SK or not at faculty stage
            if (!$achievement->isInFacultyStage() && $achievement->validation_status !== 'Menunggu') {
                continue;
            }

            // Security check: Ensure validator has access to this student's data
            if ($level === 'faculty' && $achievement->student->faculty_id !== $facultyId)
                continue;
            if ($level === 'department' && $achievement->student->department_id !== $departmentId)
                continue;
            if ($level === 'program_study' && $achievement->student->program_study_id !== $programStudyId)
                continue;
            if (!$level && $achievement->student->faculty !== auth()->user()->faculty)
                continue;

            // Create assignment
            $sk->assignments()->create([
                'sa_id' => $saId,
                'assigned_by' => $userId,
                'assigned_at' => $assignedAt,
                'assignment_type' => 'batch',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update achievement status to approved (Faculty Level)
            $oldStatus = $achievement->validation_status;
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
                'faculty_validator_id' => $userId,
                'faculty_validated_at' => $assignedAt,
                'faculty_notes' => 'Disetujui via batch assignment SK: ' . $sk->sk_number,
                'current_stage' => StudentAchievement::STAGE_UNIVERSITY,
            ]);

            // Create validation log
            $achievement->validationLogs()->create([
                'validator_id' => $userId,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_FACULTY_APPROVED,
                'notes' => 'Disetujui via batch assignment SK: ' . $sk->sk_number . ($validated['notes'] ? '. ' . $validated['notes'] : ''),
                'validation_type' => 'batch_sk_assignment',
                'validated_at' => $assignedAt,
            ]);

            $assignedCount++;
        }

        return redirect()->route('validator.sk.show', $sk)
            ->with('success', "SK berhasil di-assign ke {$assignedCount} prestasi");
    }

    public function preview(SKDocument $sk)
    {
        if ($sk->file_path && \Storage::disk('public')->exists($sk->file_path)) {
            return response()->file(storage_path('app/public/' . $sk->file_path));
        } elseif ($sk->external_link) {
            return redirect($sk->external_link);
        }

        abort(404, 'File SK tidak ditemukan');
    }
}
