<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validator\SubmitAchievementRequest;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\SKDocument;
use App\Models\Student;
use App\Services\Validator\AchievementSubmissionService;

class SubmitController extends Controller
{
    public function __construct(
        protected AchievementSubmissionService $submissionService
    ) {}

    public function create()
    {
        $categories = AchievementCategory::active()->get();
        $levels = AchievementLevel::active()->get();
        $skDocuments = SKDocument::orderBy('issued_date', 'desc')->get();

        return view('validator.submit', compact('categories', 'levels', 'skDocuments'));
    }

    public function store(SubmitAchievementRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Get student IDs
        $studentIds = $validated['student_ids'];

        $createdAchievements = [];
        $errors = [];

        // Attachments from request
        $attachments = $request->file('attachments', []);

        // Create achievement for each student
        foreach ($studentIds as $studentId) {
            try {
                // Get files for this specific student
                $studentFiles = $attachments[$studentId] ?? null;

                if (! $studentFiles || ! isset($studentFiles['certificate'])) {
                    throw new \Exception("Sertifikat untuk mahasiswa ID {$studentId} tidak ditemukan.");
                }

                $certificate = $studentFiles['certificate'];
                $additionalDocs = $studentFiles['additional_documents'] ?? [];

                // Prepare data for this student
                $studentData = array_merge($validated, ['student_id' => $studentId]);

                // Create achievement
                $achievement = $this->submissionService->submitAchievement(
                    $studentData,
                    $certificate,
                    $user,
                    $additionalDocs
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
                } elseif ($request->submit_action === 'reject') {
                    $this->submissionService->handleRejection(
                        $achievement,
                        $user,
                        $request->input('rejection_reason', 'Ditolak saat submit oleh operator.')
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
            return back()->withErrors(['error' => 'Tidak ada prestasi yang berhasil dibuat. '.implode(' ', $errors)])->withInput();
        }

        $successMessage = $count === 1
            ? 'Prestasi mahasiswa berhasil diajukan.'
            : "Berhasil mengajukan prestasi untuk {$count} mahasiswa.";

        if (! empty($errors)) {
            $successMessage .= ' Namun ada beberapa error: '.implode(' ', $errors);
        }

        // Handle approve action
        if ($request->submit_action === 'approve') {
            return redirect()->route('validator.pending.index')
                ->with('success', $successMessage.' Prestasi langsung disetujui.');
        }

        if ($request->submit_action === 'reject') {
            return redirect()->route('validator.pending.index')
                ->with('success', $successMessage.' Prestasi langsung ditolak.');
        }

        // Default: Pending - redirect to first achievement's document page or dashboard
        if ($count === 1) {
            return redirect()->route('achievements.documents.index', $createdAchievements[0])
                ->with('success', $successMessage.' Anda dapat menambahkan dokumen tambahan di bawah ini.');
        }

        return redirect()->route('validator.pending.index')
            ->with('success', $successMessage);
    }
}
