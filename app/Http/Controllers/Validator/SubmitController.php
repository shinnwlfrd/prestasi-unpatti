<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validator\SubmitAchievementRequest;
use App\Models\Achievement;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Services\Validator\AchievementSubmissionService;

class SubmitController extends Controller
{
    public function __construct(
        protected AchievementSubmissionService $submissionService
    ) {}

    public function create()
    {
        $students = Student::orderBy('name')->get();
        $achievements = Achievement::with('category')->get();
        $levels = AchievementLevel::active()->get();
        $skDocuments = \App\Models\SKDocument::orderBy('issued_date', 'desc')->get();

        return view('validator.submit', compact('students', 'achievements', 'levels', 'skDocuments'));
    }

    public function store(SubmitAchievementRequest $request)
    {
        $validated = $request->validated();
        $certificate = $request->file('certificate');
        $user = auth()->user();

        // Create achievement
        $achievement = $this->submissionService->submitAchievement(
            $validated,
            $certificate,
            $user->id
        );

        // Handle approve action
        if ($request->submit_action === 'approve') {
            $this->submissionService->handleApproval(
                $achievement,
                $user,
                $request->input('sk_id'),
                $request->file('alternative_document')
            );

            return redirect()->route('validator.dashboard')
                ->with('success', 'Prestasi berhasil diajukan dan langsung disetujui.');
        }

        // Default: Pending - redirect to document upload page
        return redirect()->route('achievements.documents.index', $achievement)
            ->with('success', 'Prestasi mahasiswa berhasil diajukan. Anda dapat menambahkan dokumen tambahan di bawah ini.');
    }
}
