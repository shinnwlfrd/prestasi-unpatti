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
        if ($user->role === 'Operator' && $user->faculty) {
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
        if ($user->role === 'Operator' && $user->faculty) {
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

        // Handle SK for approval
        $skId = null;
        if ($request->action === 'approve') {
            if (!$request->filled('sk_id')) {
                return back()->withErrors(['sk_id' => 'SK wajib dipilih untuk approve prestasi.'])->withInput();
            }
            
            $skId = $request->sk_id;
            
            // Create SK assignment
            $skDocument = \App\Models\SKDocument::find($skId);
            if ($skDocument) {
                $skDocument->assignments()->create([
                    'sa_id' => $achievement->sa_id,
                    'assigned_by' => $validator->id,
                    'assigned_at' => now(),
                    'assignment_type' => 'individual',
                    'notes' => $request->notes,
                ]);
            }
        }

        // Process action
        $success = match ($request->action) {
            'approve' => $this->approvalService->approve($achievement, $validator, $request->notes, $skId),
            'reject' => $this->approvalService->reject($achievement, $validator, $request->rejection_reason),
            'request_revision' => $this->approvalService->requestRevision($achievement, $validator, $request->revision_reason, []),
            default => false,
        };

        if ($success) {
            $message = match ($request->action) {
                'approve' => 'Prestasi berhasil disetujui dan SK telah di-assign.',
                'reject' => 'Prestasi berhasil ditolak.',
                'request_revision' => 'Permintaan revisi berhasil dikirim.',
                default => 'Status berhasil diperbarui.',
            };

            return redirect()->route('validator.pending.index')->with('success', $message);
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

    public function getAchievementData(StudentAchievement $achievement)
    {
        // Check faculty access for validator
        $user = auth()->user();
        if ($user->role === 'Operator' && $user->faculty) {
            // Load student first to check faculty
            $achievement->load('student');
            
            if (!$achievement->student || $achievement->student->faculty !== $user->faculty) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }
        }

        // Load all necessary relationships
        $achievement->load([
            'student',
            'achievement.category',
            'documents',
        ]);

        return response()->json([
            'sa_id' => $achievement->sa_id,
            'event_name' => $achievement->event_name,
            'level' => $achievement->level,
            'organizer' => $achievement->organizer,
            'event_date' => $achievement->event_date?->format('d M Y'),
            'description' => $achievement->description,
            'ranking' => $achievement->ranking,
            'validation_status' => $achievement->validation_status,
            'submitted_at' => $achievement->submitted_at?->format('d M Y H:i'),
            'student' => [
                'name' => $achievement->student?->name ?? 'N/A',
                'student_id' => $achievement->student_id,
                'faculty' => $achievement->student?->faculty ?? 'N/A',
            ],
            'documents' => $achievement->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'type_name' => $doc->type_name ?? $doc->document_type,
                    'file_name' => $doc->file_name,
                    'file_path' => $doc->file_path,
                    'status' => $doc->status,
                ];
            }),
        ]);
    }

    /**
     * Verify document (legacy support)
     */
    public function verifyDocument(AchievementDocument $document)
    {
        $validator = auth()->user();

        // Check faculty access
        $achievement = $document->achievement;
        if ($validator->role === 'Operator' && $validator->faculty) {
            if ($achievement->student->faculty !== $validator->faculty) {
                abort(403, 'Anda tidak memiliki akses untuk verifikasi dokumen dari fakultas lain.');
            }
        }

        $document->update([
            'status' => AchievementDocument::STATUS_APPROVED,
            'verified_by' => $validator->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Dokumen berhasil diverifikasi.');
    }
}
