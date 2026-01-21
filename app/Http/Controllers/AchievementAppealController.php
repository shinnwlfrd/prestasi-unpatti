<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitAppealRequest;
use App\Models\AchievementAppeal;
use App\Models\StudentAchievement;
use App\Services\AchievementApprovalService;
use App\Services\DocumentUploadService;
use Illuminate\Http\Request;

class AchievementAppealController extends Controller
{
    protected AchievementApprovalService $approvalService;
    protected DocumentUploadService $uploadService;

    public function __construct(
        AchievementApprovalService $approvalService,
        DocumentUploadService $uploadService
    ) {
        $this->approvalService = $approvalService;
        $this->uploadService = $uploadService;
    }

    public function create(StudentAchievement $achievement)
    {
        // Check if can appeal
        if (!$achievement->canBeAppealed()) {
            return back()->with('error', 'Prestasi ini tidak dapat diajukan banding.');
        }

        $achievement->load(['documents', 'validationLogs.validator']);

        return view('achievements.appeal.create', compact('achievement'));
    }

    public function store(SubmitAppealRequest $request, StudentAchievement $achievement)
    {
        if (!$achievement->canBeAppealed()) {
            return back()->with('error', 'Prestasi ini tidak dapat diajukan banding.');
        }

        // Create appeal
        $appeal = AchievementAppeal::create([
            'sa_id' => $achievement->sa_id,
            'student_id' => $achievement->student_id,
            'appeal_reason' => $request->appeal_reason,
            'publication_link' => $request->publication_link,
            'status' => AchievementAppeal::STATUS_PENDING,
        ]);

        // Upload additional documents if provided
        if ($request->hasFile('additional_documents')) {
            $files = $request->file('additional_documents');
            $types = $request->input('document_types', []);
            
            // Ensure we have types for each file
            foreach ($files as $index => $file) {
                $documentType = $types[$index] ?? 'dokumen_lainnya';
                
                try {
                    $this->uploadService->uploadDocument(
                        $achievement,
                        $file,
                        $documentType,
                        false // Not draft, submit directly
                    );
                } catch (\Exception $e) {
                    // Log error but continue with other files
                    \Log::warning('Failed to upload document in appeal: ' . $e->getMessage());
                }
            }
        }

        // Add publication link as document if provided
        if ($request->filled('publication_link')) {
            $this->uploadService->addExternalLink(
                $achievement,
                $request->publication_link,
                'Link Publikasi (Banding)',
                false // Not draft, submit directly
            );
        }

        // Update achievement status to pending for re-review
        $achievement->update(['validation_status' => StudentAchievement::STATUS_PENDING]);

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Banding berhasil diajukan. Tim validator akan meninjau kembali prestasi Anda.');
    }

    public function index(Request $request)
    {
        $query = AchievementAppeal::with(['studentAchievement.student', 'reviewer'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appeals = $query->paginate(15);

        return view('admin.achievements.appeals.index', compact('appeals'));
    }

    public function show(AchievementAppeal $appeal)
    {
        $appeal->load([
            'studentAchievement.student',
            'studentAchievement.documents',
            'studentAchievement.validationLogs.validator',
            'reviewer',
        ]);

        return view('admin.achievements.appeals.show', compact('appeal'));
    }

    public function review(Request $request, AchievementAppeal $appeal)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'review_notes' => 'required|string|max:1000',
        ]);

        $appeal->update([
            'status' => $request->action === 'approve' 
                ? AchievementAppeal::STATUS_APPROVED 
                : AchievementAppeal::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'review_notes' => $request->review_notes,
            'reviewed_at' => now(),
        ]);

        $achievement = $appeal->studentAchievement;

        if ($request->action === 'approve') {
            $this->approvalService->approve($achievement, auth()->user(), 'Banding disetujui: ' . $request->review_notes);
        } else {
            $this->approvalService->reject($achievement, auth()->user(), 'Banding ditolak: ' . $request->review_notes);
        }

        return redirect()
            ->route('admin.appeals.index')
            ->with('success', 'Banding berhasil ' . ($request->action === 'approve' ? 'disetujui' : 'ditolak') . '.');
    }
}
