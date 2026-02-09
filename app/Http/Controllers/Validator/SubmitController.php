<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validator\SubmitAchievementRequest;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Services\Validator\AchievementSubmissionService;

class SubmitController extends Controller
{
    public function __construct(
        protected AchievementSubmissionService $submissionService
    ) {
    }

    public function create()
    {
        $students = Student::orderBy('name')->get();
        $categories = AchievementCategory::active()->get();
        $achievements = Achievement::with('category')->get();
        $levels = AchievementLevel::active()->get();
        $skDocuments = \App\Models\SKDocument::orderBy('issued_date', 'desc')->get();

        return view('validator.submit', compact('students', 'achievements', 'categories', 'levels', 'skDocuments'));
    }

    public function store(SubmitAchievementRequest $request)
    {
        $validated = $request->validated();
        $certificate = $request->file('certificate');
        $user = auth()->user();

        // Get student IDs (can be single or multiple)
        $studentIds = $validated['student_ids'] ?? [$validated['student_id'] ?? null];
        $studentIds = array_filter($studentIds); // Remove empty values

        if (empty($studentIds)) {
            return back()->withErrors(['student_ids' => 'Pilih minimal satu mahasiswa.'])->withInput();
        }

        $createdAchievements = [];
        $errors = [];

        // Create achievement for each student
        foreach ($studentIds as $studentId) {
            try {
                // Prepare data for this student
                $studentData = array_merge($validated, ['student_id' => $studentId]);

                // Create achievement
                $achievement = $this->submissionService->submitAchievement(
                    $studentData,
                    $certificate,
                    $user->id
                );

                $createdAchievements[] = $achievement;

                // Handle approve action
                if ($request->submit_action === 'approve') {
                    $this->submissionService->handleApproval(
                        $achievement,
                        $user,
                        $request->input('sk_id'),
                        $request->file('alternative_document')
                    );
                }
            } catch (\Exception $e) {
                $student = Student::find($studentId);
                $errors[] = "Gagal membuat prestasi untuk {$student->name}: {$e->getMessage()}";
            }
        }

        // Prepare success message
        $count = count($createdAchievements);
        if ($count === 0) {
            return back()->withErrors(['error' => 'Tidak ada prestasi yang berhasil dibuat. ' . implode(' ', $errors)])->withInput();
        }

        $successMessage = $count === 1
            ? 'Prestasi mahasiswa berhasil diajukan.'
            : "Berhasil mengajukan prestasi untuk {$count} mahasiswa.";

        if (!empty($errors)) {
            $successMessage .= ' Namun ada beberapa error: ' . implode(' ', $errors);
        }

        // Handle approve action
        if ($request->submit_action === 'approve') {
            return redirect()->route('validator.pending.index')
                ->with('success', $successMessage . ' Prestasi langsung disetujui.');
        }

        // Default: Pending - redirect to first achievement's document page or dashboard
        if ($count === 1) {
            return redirect()->route('achievements.documents.index', $createdAchievements[0])
                ->with('success', $successMessage . ' Anda dapat menambahkan dokumen tambahan di bawah ini.');
        }

        return redirect()->route('validator.pending.index')
            ->with('success', $successMessage);
    }
}
