<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateAchievementRequest;
use App\Models\StudentAchievement;
use App\Models\ValidationChecklist;
use App\Services\AchievementApprovalService;
use Illuminate\Http\Request;

class AchievementValidationController extends Controller
{
    protected AchievementApprovalService $approvalService;

    public function __construct(AchievementApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function index(Request $request)
    {
        $query = StudentAchievement::with(['student', 'achievement.category', 'documents', 'validator'])
            ->latest('submitted_at');

        // Tab filtering
        $tab = $request->get('tab', 'pending');

        switch ($tab) {
            case 'appeal':
                $query->where('is_appeal', true)
                    ->where('validation_status', StudentAchievement::STATUS_PENDING);
                break;
            case 'revision':
                $query->where('validation_status', StudentAchievement::STATUS_NEED_REVISION);
                break;
            case 'approved':
                $query->where('validation_status', StudentAchievement::STATUS_APPROVED);
                break;
            case 'rejected':
                $query->where('validation_status', StudentAchievement::STATUS_REJECTED);
                break;
            case 'history':
                $query->whereIn('validation_status', [StudentAchievement::STATUS_APPROVED, StudentAchievement::STATUS_REJECTED]);
                break;
            case 'pending':
            default:
                $query->where('validation_status', StudentAchievement::STATUS_PENDING)
                    ->where('is_appeal', false);
                break;
        }

        // Additional Filters
        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $achievements = $query->paginate(15)->withQueryString();
        $statistics = $this->approvalService->getApprovalStatistics();

        // Add appeals count to statistics
        $statistics['appeals'] = StudentAchievement::where('is_appeal', true)
            ->where('validation_status', StudentAchievement::STATUS_PENDING)
            ->count();

        return view('admin.achievements.validation.index', compact('achievements', 'statistics'));
    }

    public function show(StudentAchievement $achievement)
    {
        $achievement->load([
            'student',
            'achievement.category',
            'documents',
            'validationLogs.validator',
            'checklist',
            'appeals.reviewer',
        ]);

        $checklist = $achievement->checklist ?? new ValidationChecklist([
            'sa_id' => $achievement->sa_id,
            'validator_id' => auth()->id(),
        ]);

        // Get available SK documents for selection
        $skDocuments = \App\Models\SKDocument::orderBy('issued_date', 'desc')->get();

        return view('admin.achievements.validation.show', compact('achievement', 'checklist', 'skDocuments'));
    }

    public function validate(ValidateAchievementRequest $request, StudentAchievement $achievement)
    {
        $validator = auth()->user();

        // Save checklist first
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
            'request_revision' => $this->approvalService->requestRevision(
                $achievement,
                $validator,
                $request->revision_reason,
                $request->required_documents ?? []
            ),
            default => false,
        };

        if ($success) {
            $message = match ($request->action) {
                'approve' => 'Prestasi berhasil disetujui dan SK telah di-assign.',
                'reject' => 'Prestasi berhasil ditolak.',
                'request_revision' => 'Permintaan revisi berhasil dikirim.',
                default => 'Status berhasil diperbarui.',
            };

            return redirect()
                ->route('admin.achievements.validation.index')
                ->with('success', $message);
        }

        return back()->with('error', 'Gagal memproses validasi.');
    }

    protected function uploadSkResmi(Request $request, StudentAchievement $achievement, $validator)
    {
        $request->validate([
            'sk_resmi' => 'required|file|mimes:pdf|max:10240', // 10MB
        ]);

        $file = $request->file('sk_resmi');
        $fileName = 'SK_Resmi_'.$achievement->sa_id.'_'.time().'.pdf';
        $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

        // Create document record
        $achievement->documents()->create([
            'document_type' => \App\Models\AchievementDocument::TYPE_SK_RESMI,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => \App\Models\AchievementDocument::STATUS_APPROVED, // Auto approved karena diupload oleh validator
            'verified_by' => $validator->id,
            'verified_at' => now(),
        ]);

        // Return file path untuk disimpan di validation log
        return $filePath;
    }

    public function saveChecklist(Request $request, StudentAchievement $achievement)
    {
        $validated = $request->validate([
            'certificate_valid' => 'boolean',
            'event_date_valid' => 'boolean',
            'organizer_valid' => 'boolean',
            'level_appropriate' => 'boolean',
            'documents_complete' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $checklist = ValidationChecklist::updateOrCreate(
            ['sa_id' => $achievement->sa_id],
            array_merge($validated, ['validator_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'checklist' => $checklist,
            'progress' => $checklist->progress_percentage,
        ]);
    }

    public function history(StudentAchievement $achievement)
    {
        $logs = $achievement->validationLogs()
            ->with(['validator', 'studentAchievement.documents'])
            ->orderBy('validated_at', 'desc')
            ->get();

        return view('admin.achievements.validation.history', compact('achievement', 'logs'));
    }

    public function documents(StudentAchievement $achievement)
    {
        $achievement->load([
            'student',
            'achievement',
            'documents.revisions.performer',
            'documents.verifier',
        ]);

        return view('admin.achievements.validation.documents', compact('achievement'));
    }

    public function revertToPending(Request $request, StudentAchievement $achievement)
    {
        // Only admin can revert
        if (! auth()->check() || auth()->user()->role !== 'Admin') {
            abort(403, 'Hanya admin yang dapat mengembalikan status prestasi.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if achievement is approved or rejected
        if (! in_array($achievement->validation_status, ['Disetujui', 'Ditolak'])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Prestasi harus berstatus Disetujui atau Ditolak untuk dikembalikan.'], 422);
            }

            return back()->with('error', 'Prestasi harus berstatus Disetujui atau Ditolak untuk dikembalikan.');
        }

        $oldStatus = $achievement->validation_status;
        $achievement->validation_status = 'Menunggu';
        $achievement->validator_id = null;
        $achievement->save();

        // Log the revert action
        $achievement->validationLogs()->create([
            'validator_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => 'Menunggu',
            'notes' => 'Status dikembalikan ke Pending oleh Admin. '.($request->reason ?? ''),
            'validated_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status prestasi berhasil dikembalikan ke Pending.',
            ]);
        }

        return back()->with('success', 'Status prestasi berhasil dikembalikan ke Pending.');
    }
}
