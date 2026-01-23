<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validator\ValidateAchievementRequest;
use App\Models\AchievementDocument;
use App\Models\StudentAchievement;
use App\Models\ValidationChecklist;
use App\Services\AchievementApprovalService;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function __construct(
        protected AchievementApprovalService $approvalService
    ) {}

    public function show(StudentAchievement $achievement)
    {
        // Check faculty access for validator
        $user = auth()->user();
        if ($user->role === 'Validator' && $user->faculty) {
            if ($achievement->student->faculty !== $user->faculty) {
                abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
            }
        }

        $achievement->load([
            'student',
            'achievement.category',
            'documents.verifier',
            'validationLogs.validator',
            'checklist',
        ]);

        $checklist = $achievement->checklist ?? new ValidationChecklist([
            'sa_id' => $achievement->sa_id,
            'validator_id' => auth()->id(),
        ]);

        return view('validator.achievements.show', compact('achievement', 'checklist'));
    }

    public function documents(StudentAchievement $achievement)
    {
        $achievement->load([
            'student',
            'achievement.category',
            'documents.revisions.performer',
            'documents.verifier',
        ]);

        return view('validator.achievements.documents', compact('achievement'));
    }

    public function validate(ValidateAchievementRequest $request, StudentAchievement $achievement)
    {
        // Check faculty access for validator
        $user = auth()->user();
        if ($user->role === 'Validator' && $user->faculty) {
            if ($achievement->student->faculty !== $user->faculty) {
                abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
            }
        }

        $validator = auth()->user();

        // Save checklist if provided
        if ($request->has('checklist')) {
            $checklistData = $request->input('checklist');
            $checklistData['sa_id'] = $achievement->sa_id;
            $checklistData['validator_id'] = $validator->id;
            $this->approvalService->processChecklist($achievement, $validator, $checklistData);
        }

        // Handle SK Resmi upload (WAJIB untuk approve)
        $skDocumentPath = null;
        if ($request->action === 'approve' && $request->hasFile('sk_resmi')) {
            $skDocumentPath = $this->uploadSkResmi($request, $achievement, $validator);
        }

        // Process action
        $success = match ($request->action) {
            'approve' => $this->approvalService->approve($achievement, $validator, $request->notes, $skDocumentPath),
            'reject' => $this->approvalService->reject($achievement, $validator, $request->rejection_reason),
            'request_revision' => $this->approvalService->requestRevision($achievement, $validator, $request->revision_reason, []),
            default => false,
        };

        if ($success) {
            $message = match ($request->action) {
                'approve' => 'Prestasi berhasil disetujui dan SK Resmi telah diupload.',
                'reject' => 'Prestasi berhasil ditolak.',
                'request_revision' => 'Permintaan revisi berhasil dikirim.',
                default => 'Status berhasil diperbarui.',
            };

            return redirect()->route('validator.dashboard')->with('success', $message);
        }

        return back()->with('error', 'Gagal memproses validasi.');
    }

    protected function uploadSkResmi(Request $request, StudentAchievement $achievement, $validator)
    {
        $file = $request->file('sk_resmi');
        $fileName = 'SK_Resmi_'.$achievement->sa_id.'_'.time().'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

        // Create document record
        $achievement->documents()->create([
            'document_type' => AchievementDocument::TYPE_SK_RESMI,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => AchievementDocument::STATUS_APPROVED,
            'verified_by' => $validator->id,
            'verified_at' => now(),
        ]);

        return $filePath;
    }
}
