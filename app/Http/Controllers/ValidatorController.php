<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use App\Models\ValidationChecklist;
use App\Models\AchievementDocument;
use App\Models\Achievement;
use App\Services\AchievementApprovalService;
use App\Services\DocumentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ValidatorController extends Controller
{
    protected AchievementApprovalService $approvalService;
    protected DocumentVerificationService $verificationService;

    public function __construct(
        AchievementApprovalService $approvalService,
        DocumentVerificationService $verificationService
    ) {
        $this->approvalService = $approvalService;
        $this->verificationService = $verificationService;
    }

    /**
     * Display pending achievements for validation.
     */
    public function dashboard()
    {
        $pendingAchievements = StudentAchievement::with(['student', 'achievement', 'documents'])
            ->whereIn('validation_status', ['pending', 'Menunggu'])
            ->orderByDesc('created_at')
            ->get();

        return view('validator.dashboard', compact('pendingAchievements'));
    }

    /**
     * Display validation history.
     */
    public function history()
    {
        $logs = ValidationLog::with(['studentAchievement.student', 'studentAchievement.achievement', 'validator'])
            ->orderByDesc('validated_at')
            ->paginate(10);

        return view('validator.history', compact('logs'));
    }

    /**
     * Show achievement detail for validation.
     */
    public function show(StudentAchievement $achievement)
    {
        $achievement->load([
            'student',
            'achievement',
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

    /**
     * Show achievement documents for verification.
     */
    public function documents(StudentAchievement $achievement)
    {
        $achievement->load([
            'student',
            'achievement',
            'documents.revisions.performer',
            'documents.verifier',
        ]);

        return view('validator.achievements.documents', compact('achievement'));
    }

    /**
     * Validate achievement (approve/reject/request revision).
     */
    public function validateAchievement(Request $request, StudentAchievement $achievement)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,request_revision',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
            'revision_reason' => 'required_if:action,request_revision|nullable|string|max:1000',
        ]);

        $validator = auth()->user();

        // Validasi SK Resmi WAJIB untuk approve
        if ($request->action === 'approve' && !$request->hasFile('sk_resmi')) {
            return back()->withErrors(['sk_resmi' => 'SK Resmi wajib diupload untuk approve prestasi.'])->withInput();
        }

        // Save checklist if provided
        if ($request->has('checklist')) {
            $checklistData = $request->input('checklist');
            $checklistData['sa_id'] = $achievement->sa_id;
            $checklistData['validator_id'] = $validator->id;
            $this->approvalService->processChecklist($achievement, $validator, $checklistData);
        }

        // Handle SK Resmi upload (WAJIB untuk approve)
        if ($request->action === 'approve' && $request->hasFile('sk_resmi')) {
            $this->uploadSkResmi($request, $achievement, $validator);
        }

        // Process action
        $success = match($request->action) {
            'approve' => $this->approvalService->approve($achievement, $validator, $request->notes),
            'reject' => $this->approvalService->reject($achievement, $validator, $request->rejection_reason),
            'request_revision' => $this->approvalService->requestRevision($achievement, $validator, $request->revision_reason, []),
            default => false,
        };

        if ($success) {
            $message = match($request->action) {
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
        $request->validate([
            'sk_resmi' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $file = $request->file('sk_resmi');
        $fileName = 'SK_Resmi_' . $achievement->sa_id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

        // Create document record
        $achievement->documents()->create([
            'document_type' => AchievementDocument::TYPE_SK_RESMI,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => AchievementDocument::STATUS_APPROVED, // Auto approved karena diupload oleh validator
            'verified_by' => $validator->id,
            'verified_at' => now(),
        ]);
    }

    /**
     * Verify a single document.
     */
    public function verifyDocument(Request $request, AchievementDocument $document)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,revision',
            'notes' => 'required_if:action,reject,revision|nullable|string|max:1000',
        ]);

        $user = auth()->user();

        $result = match($request->action) {
            'approve' => $this->verificationService->approveDocument($document, $user, $request->notes),
            'reject' => $this->verificationService->rejectDocument($document, $user, $request->notes),
            'revision' => $this->verificationService->requestRevision($document, $user, $request->notes),
            default => false,
        };

        if ($result) {
            $message = match($request->action) {
                'approve' => 'Dokumen berhasil disetujui.',
                'reject' => 'Dokumen berhasil ditolak.',
                'revision' => 'Permintaan revisi berhasil dikirim.',
                default => 'Status dokumen berhasil diperbarui.',
            };

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'document' => [
                        'status' => $document->fresh()->status,
                        'status_label' => $document->fresh()->status_label,
                    ],
                ]);
            }

            return back()->with('success', $message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'error' => 'Gagal memproses verifikasi.'], 422);
        }
        return back()->with('error', 'Gagal memproses verifikasi.');
    }

    /**
     * Show form for submitting achievement on behalf of student.
     */
    public function submitForm()
    {
        $students = Student::orderBy('name')->get();
        $achievements = Achievement::all();

        return view('validator.submit', compact('students', 'achievements'));
    }

    /**
     * Store achievement submitted by validator.
     */
    public function submitStore(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'achievement_id' => 'required|exists:achievements,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:Universitas,Nasional,Internasional',
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'description' => 'nullable|string',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        $achievement = StudentAchievement::create([
            'student_id' => $validated['student_id'],
            'achievement_id' => $validated['achievement_id'],
            'event_name' => $validated['event_name'],
            'level' => $validated['level'],
            'organizer' => $validated['organizer'],
            'event_date' => $validated['event_date'],
            'description' => $validated['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => 'Menunggu',
            'submitted_by' => 'validator',
            'submitted_at' => now(),
        ]);

        // Redirect to document upload page so validator can add more documents
        return redirect()->route('achievements.documents.index', $achievement)
            ->with('success', 'Prestasi mahasiswa berhasil diajukan. Anda dapat menambahkan dokumen tambahan di bawah ini.');
    }

    /**
     * Approve an achievement (legacy).
     */
    public function approve(Request $request, $sa_id)
    {
        $request->validate([
            'sk_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'validation_type' => 'required|in:SK Resmi,Dokumen Internal',
        ], [
            'sk_document.required' => 'Surat SK wajib diupload untuk menyetujui prestasi.',
            'sk_document.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'sk_document.max' => 'Ukuran file maksimal 5MB.',
        ]);

        return DB::transaction(function () use ($request, $sa_id) {
            $achievement = StudentAchievement::findOrFail($sa_id);
            $oldStatus = $achievement->validation_status;

            // Upload SK document
            $skPath = $request->file('sk_document')->store('sk_documents', 'public');

            $achievement->update([
                'validation_status' => 'Disetujui',
                'validator_id' => Auth::id(),
            ]);

            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => 'approved',
                'sk_document' => $skPath,
                'validated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Prestasi berhasil disetujui!');
        });
    }

    /**
     * Reject an achievement (legacy).
     */
    public function reject(Request $request, $sa_id)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
            'sk_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'notes.required' => 'Alasan penolakan wajib diisi.',
            'sk_document.required' => 'Surat SK wajib diupload untuk menolak prestasi.',
        ]);

        return DB::transaction(function () use ($request, $sa_id) {
            $achievement = StudentAchievement::findOrFail($sa_id);
            $oldStatus = $achievement->validation_status;

            // Upload SK document
            $skPath = $request->file('sk_document')->store('sk_documents', 'public');

            $achievement->update([
                'validation_status' => 'Ditolak',
                'validator_id' => Auth::id(),
            ]);

            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
                'notes' => $request->notes,
                'sk_document' => $skPath,
                'validated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Prestasi ditolak.');
        });
    }
}
