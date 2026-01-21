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
        $user = auth()->user();
        
        $query = StudentAchievement::with(['student', 'achievement.category', 'documents'])
            ->whereIn('validation_status', ['pending', 'Menunggu'])
            ->orderByDesc('created_at');
        
        // Filter by faculty if validator has faculty assigned
        if ($user->role === 'Validator' && $user->faculty) {
            $query->whereHas('student', function($q) use ($user) {
                $q->where('faculty', $user->faculty);
            });
        }
        
        $pendingAchievements = $query->get();

        return view('validator.dashboard', compact('pendingAchievements'));
    }

    /**
     * Display validation history.
     */
    public function history()
    {
        $user = auth()->user();
        
        $query = ValidationLog::with([
            'studentAchievement.student', 
            'studentAchievement.achievement.category', 
            'studentAchievement.documents',
            'validator'
        ])->orderByDesc('validated_at');
        
        // Filter by faculty if validator has faculty assigned
        if ($user->role === 'Validator' && $user->faculty) {
            $query->whereHas('studentAchievement.student', function($q) use ($user) {
                $q->where('faculty', $user->faculty);
            });
        }
        
        $logs = $query->paginate(10);

        return view('validator.history', compact('logs'));
    }

    /**
     * Preview SK document from validation log.
     */
    public function previewSK(ValidationLog $log)
    {
        // Get SK document path
        $skPath = $log->sk_document;
        
        // If not in validation log, try to get from achievement documents
        if (!$skPath && $log->new_status === 'Disetujui') {
            $skDoc = $log->studentAchievement?->documents()
                ->where('document_type', AchievementDocument::TYPE_SK_RESMI)
                ->where('status', AchievementDocument::STATUS_APPROVED)
                ->latest()
                ->first();
            $skPath = $skDoc?->file_path;
        }
        
        // If still no SK, return 404
        if (!$skPath) {
            abort(404, 'SK Resmi tidak ditemukan');
        }
        
        // Check if file exists
        $fullPath = storage_path('app/public/' . $skPath);
        if (!file_exists($fullPath)) {
            abort(404, 'File SK Resmi tidak ditemukan');
        }
        
        // Return file
        return response()->file($fullPath);
    }

    /**
     * Show achievement detail for validation.
     */
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

    /**
     * Show achievement documents for verification.
     */
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

    /**
     * Validate achievement (approve/reject/request revision).
     */
    public function validateAchievement(Request $request, StudentAchievement $achievement)
    {
        // Check faculty access for validator
        $user = auth()->user();
        if ($user->role === 'Validator' && $user->faculty) {
            if ($achievement->student->faculty !== $user->faculty) {
                abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
            }
        }
        
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
        $skDocumentPath = null;
        if ($request->action === 'approve' && $request->hasFile('sk_resmi')) {
            $skDocumentPath = $this->uploadSkResmi($request, $achievement, $validator);
        }

        // Process action
        $success = match($request->action) {
            'approve' => $this->approvalService->approve($achievement, $validator, $request->notes, $skDocumentPath),
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

        // Return file path untuk disimpan di validation log
        return $filePath;
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
        $achievements = Achievement::with('category')->get();
        $levels = \App\Models\AchievementLevel::active()->get();

        return view('validator.submit', compact('students', 'achievements', 'levels'));
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
            'ranking' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'submit_action' => 'required|in:pending,approve',
            'skip_sk' => 'nullable|boolean',
            'sk_resmi' => 'required_if:submit_action,approve|required_unless:skip_sk,1|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sk_waiver_reason' => 'required_if:skip_sk,1|nullable|in:tingkat_universitas,sk_dalam_proses,dokumen_alternatif,lainnya',
            'sk_waiver_notes' => 'required_if:sk_waiver_reason,lainnya|nullable|string|max:1000',
            'alternative_document' => 'required_if:sk_waiver_reason,dokumen_alternatif|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        // Determine initial status based on action
        $initialStatus = $request->submit_action === 'approve' ? 'Disetujui' : 'Menunggu';

        // Determine SK required
        $skRequired = !$request->boolean('skip_sk');

        $achievement = StudentAchievement::create([
            'student_id' => $validated['student_id'],
            'achievement_id' => $validated['achievement_id'],
            'event_name' => $validated['event_name'],
            'level' => $validated['level'],
            'organizer' => $validated['organizer'],
            'event_date' => $validated['event_date'],
            'ranking' => $validated['ranking'] ?? null,
            'description' => $validated['description'] ?? null,
            'certificate' => $certificatePath,
            'validation_status' => $initialStatus,
            'validator_id' => $request->submit_action === 'approve' ? auth()->id() : null,
            'submitted_by' => 'validator',
            'submitted_at' => now(),
            'sk_required' => $skRequired,
            'sk_waiver_reason' => $request->sk_waiver_reason,
            'sk_waiver_notes' => $request->sk_waiver_notes,
        ]);

        // Handle approve action
        if ($request->submit_action === 'approve') {
            $skDocumentPath = null;
            
            // Upload SK Resmi if provided
            if ($request->hasFile('sk_resmi')) {
                $file = $request->file('sk_resmi');
                $fileName = 'SK_Resmi_' . $achievement->sa_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

                $achievement->documents()->create([
                    'document_type' => AchievementDocument::TYPE_SK_RESMI,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => AchievementDocument::STATUS_APPROVED,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);

                $skDocumentPath = $filePath;
            }
            
            // Upload alternative document if provided
            if ($request->hasFile('alternative_document')) {
                $file = $request->file('alternative_document');
                $fileName = 'Alt_Doc_' . $achievement->sa_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

                // Save path to achievement
                $achievement->update(['alternative_document_path' => $filePath]);

                // Also create document record
                $achievement->documents()->create([
                    'document_type' => 'dokumen_alternatif',
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => AchievementDocument::STATUS_APPROVED,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);
            }

            // Log approval
            $notes = $skRequired 
                ? 'Disetujui langsung oleh validator saat submit' 
                : 'Disetujui tanpa SK: ' . ($request->sk_waiver_reason ? StudentAchievement::getSkWaiverReasons()[$request->sk_waiver_reason] : 'N/A');
            
            $this->approvalService->approve($achievement, auth()->user(), $notes, $skDocumentPath);

            return redirect()->route('validator.dashboard')
                ->with('success', 'Prestasi berhasil diajukan dan langsung disetujui.');
        }

        // Default: Pending - redirect to document upload page
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
