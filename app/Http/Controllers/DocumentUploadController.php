<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadDocumentsRequest;
use App\Models\AchievementDocument;
use App\Models\StudentAchievement;
use App\Services\CredibilityService;
use App\Services\DocumentUploadService;
use App\Services\DocumentVerificationService;
use Illuminate\Http\Request;

class DocumentUploadController extends Controller
{
    protected DocumentUploadService $uploadService;

    protected DocumentVerificationService $verificationService;

    protected CredibilityService $credibilityService;

    public function __construct(
        DocumentUploadService $uploadService,
        DocumentVerificationService $verificationService,
        CredibilityService $credibilityService
    ) {
        $this->uploadService = $uploadService;
        $this->verificationService = $verificationService;
        $this->credibilityService = $credibilityService;
    }

    public function index(StudentAchievement $achievement)
    {
        // Check if achievement is already approved or rejected - prevent document upload
        if (
            in_array($achievement->validation_status, [
                StudentAchievement::STATUS_APPROVED,
                StudentAchievement::STATUS_REJECTED,
            ])
        ) {
            // Redirect back with message
            $message = $achievement->validation_status === StudentAchievement::STATUS_APPROVED
                ? 'Prestasi ini sudah disetujui. Upload dokumen tidak diperbolehkan.'
                : 'Prestasi ini sudah ditolak. Upload dokumen tidak diperbolehkan.';

            // Determine redirect based on user role
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->role === 'Admin') {
                    return redirect()->route('admin.student-achievements')
                        ->with('warning', $message);
                } elseif ($user->role === 'Validator') {
                    return redirect()->route('validator.pending.index')
                        ->with('warning', $message);
                }
            }

            // Student or default
            return redirect()->route('student.dashboard')->with('warning', $message);
        }

        $achievement->load(['documents.revisions', 'documents.verifier', 'student', 'achievement.category']);

        // Check if user is validator/admin - they can upload all document types including SK Resmi
        $isValidatorOrAdmin = auth()->check() && in_array(auth()->user()->role, ['Admin', 'Validator']);

        // Filter document types
        if ($isValidatorOrAdmin) {
            // Admin/Validator: Exclude SK Resmi and Link Publikasi (SK via modal, Link not needed)
            $documentTypes = collect(AchievementDocument::DOCUMENT_TYPES)
                ->except([
                    AchievementDocument::TYPE_SK_RESMI,
                    AchievementDocument::TYPE_LINK_PUBLIKASI,
                ])
                ->toArray();
        } else {
            // Student: Exclude SK Resmi only
            $documentTypes = collect(AchievementDocument::DOCUMENT_TYPES)
                ->except([AchievementDocument::TYPE_SK_RESMI])
                ->toArray();
        }

        // Non-academic check (category_id != 1)
        $isNonAkademik = $achievement->achievement && $achievement->achievement->category_id !== 1;
        $documentStats = $this->verificationService->getDocumentStatistics($achievement);

        return view('achievements.documents.index', compact(
            'achievement',
            'documentTypes',
            'isNonAkademik',
            'documentStats',
            'isValidatorOrAdmin'
        ));
    }

    public function store(UploadDocumentsRequest $request, StudentAchievement $achievement)
    {
        // Prevent upload if achievement is already approved or rejected
        if (
            in_array($achievement->validation_status, [
                StudentAchievement::STATUS_APPROVED,
                StudentAchievement::STATUS_REJECTED,
            ])
        ) {
            $message = $achievement->validation_status === StudentAchievement::STATUS_APPROVED
                ? 'Prestasi ini sudah disetujui. Upload dokumen tidak diperbolehkan.'
                : 'Prestasi ini sudah ditolak. Upload dokumen tidak diperbolehkan.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            return back()->with('error', $message);
        }

        $files = $request->file('documents', []);
        $types = $request->input('document_types', []);
        $asDraft = $request->boolean('as_draft', true);

        $result = $this->uploadService->uploadMultipleDocuments($achievement, $files, $types, $asDraft);

        // Handle external links
        if ($request->has('external_links')) {
            foreach ($request->external_links as $link) {
                if (!empty($link['url'])) {
                    $this->uploadService->addExternalLink(
                        $achievement,
                        $link['url'],
                        $link['title'] ?? 'Link Publikasi',
                        $asDraft
                    );
                }
            }
        }

        // Validate document requirements
        $validationErrors = $this->credibilityService->validateDocumentRequirements($achievement);

        if (!empty($result['errors'])) {
            return back()
                ->with('warning', 'Beberapa file gagal diunggah.')
                ->with('upload_errors', $result['errors']);
        }

        $message = count($result['uploaded']) . ' dokumen berhasil diunggah sebagai ' . ($asDraft ? 'draft' : 'pending') . '.';
        if (!empty($validationErrors)) {
            $message .= ' Perhatian: ' . implode(' ', $validationErrors);
        }

        // Determine redirect based on user role
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->role === 'Admin') {
                return redirect()->route('admin.achievements.validation.index')->with('success', $message);
            } elseif ($user->role === 'Validator') {
                return redirect()->route('validator.pending.index')->with('success', $message);
            }
        }

        // Student or default
        return redirect()->route('student.dashboard')->with('success', $message);
    }

    public function upload(Request $request, StudentAchievement $achievement)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_type' => 'required|in:' . implode(',', array_keys(AchievementDocument::DOCUMENT_TYPES)),
            'as_draft' => 'boolean',
        ]);

        try {
            $document = $this->uploadService->uploadDocument(
                $achievement,
                $request->file('file'),
                $request->document_type,
                $request->boolean('as_draft', true)
            );

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $document->id,
                    'file_name' => $document->file_name,
                    'file_url' => $document->file_url,
                    'file_size' => $document->file_size_formatted,
                    'type_name' => $document->type_name,
                    'status' => $document->status,
                    'status_label' => $document->status_label,
                    'is_image' => $document->isImage(),
                    'can_edit' => $document->canBeEdited(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function replace(Request $request, AchievementDocument $document)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Check authorization
        $achievement = $document->studentAchievement;
        $this->authorizeDocumentAccess($achievement);

        if (!$document->canBeEdited()) {
            return response()->json([
                'success' => false,
                'error' => 'Dokumen tidak dapat diubah karena sudah diverifikasi.',
            ], 403);
        }

        try {
            $document = $this->uploadService->replaceDocument($document, $request->file('file'));

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $document->id,
                    'file_name' => $document->file_name,
                    'file_url' => $document->file_url,
                    'file_size' => $document->file_size_formatted,
                    'status' => $document->status,
                    'status_label' => $document->status_label,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function replaceCertificate(Request $request, StudentAchievement $achievement)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Check authorization
        $this->authorizeDocumentAccess($achievement);

        // Check if achievement can be edited (using same logic as documents)
        if (
            in_array($achievement->validation_status, [
                StudentAchievement::STATUS_APPROVED,
                StudentAchievement::STATUS_REJECTED,
            ])
        ) {
            return response()->json([
                'success' => false,
                'error' => 'Sertifikat tidak dapat diubah karena prestasi sudah disetujui atau ditolak.',
            ], 403);
        }

        try {
            $achievement = $this->uploadService->replaceCertificate($achievement, $request->file('file'));

            return response()->json([
                'success' => true,
                'certificate_url' => asset('storage/' . $achievement->certificate),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(StudentAchievement $achievement)
    {
        $this->authorizeDocumentAccess($achievement);

        $count = $this->uploadService->submitDocuments($achievement);

        if ($count === 0) {
            return back()->with('warning', 'Tidak ada dokumen draft yang bisa disubmit.');
        }

        return back()->with('success', $count . ' dokumen berhasil disubmit untuk verifikasi.');
    }

    public function submitSingle(AchievementDocument $document)
    {
        $this->authorizeDocumentAccess($document->studentAchievement);

        if ($this->uploadService->submitSingleDocument($document)) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => $document->fresh()->status,
                    'status_label' => $document->fresh()->status_label,
                ]);
            }

            return back()->with('success', 'Dokumen berhasil disubmit untuk verifikasi.');
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => false, 'error' => 'Dokumen tidak dapat disubmit.'], 422);
        }

        return back()->with('error', 'Dokumen tidak dapat disubmit.');
    }

    public function destroy(AchievementDocument $document)
    {
        $achievement = $document->studentAchievement;
        $this->authorizeDocumentAccess($achievement);

        if (!$document->canBeDeleted()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Dokumen tidak dapat dihapus karena sudah diverifikasi.',
                ], 403);
            }

            return back()->with('error', 'Dokumen tidak dapat dihapus karena sudah diverifikasi.');
        }

        try {
            $this->uploadService->deleteDocument($document);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                ]);
            }

            return back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function preview(AchievementDocument $document)
    {
        // Check authorization
        $this->authorizeDocumentAccess($document->studentAchievement);

        if ($document->document_type === AchievementDocument::TYPE_LINK_PUBLIKASI) {
            return redirect()->away($document->external_link);
        }

        if (!$document->file_path) {
            abort(404);
        }

        $path = storage_path('app/public/' . $document->file_path);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function previewCertificate(StudentAchievement $achievement)
    {
        // Check authorization
        $this->authorizeDocumentAccess($achievement);

        if (!$achievement->certificate) {
            abort(404);
        }

        $path = storage_path('app/public/' . $achievement->certificate);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function history(AchievementDocument $document)
    {
        // Check authorization
        $this->authorizeDocumentAccess($document->studentAchievement);

        $document->load(['revisions.performer', 'studentAchievement']);

        return view('achievements.documents.history', compact('document'));
    }

    // Admin/Validator methods
    public function verify(Request $request, AchievementDocument $document)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,revision',
            'notes' => 'required_if:action,reject,revision|nullable|string|max:1000',
        ]);

        $user = auth()->user();

        $result = match ($request->action) {
            'approve' => $this->verificationService->approveDocument($document, $user, $request->notes),
            'reject' => $this->verificationService->rejectDocument($document, $user, $request->notes),
            'revision' => $this->verificationService->requestRevision($document, $user, $request->notes),
            default => false,
        };

        if ($result) {
            $message = match ($request->action) {
                'approve' => 'Dokumen berhasil disetujui.',
                'reject' => 'Dokumen berhasil ditolak.',
                'revision' => 'Permintaan revisi berhasil dikirim.',
                default => 'Status dokumen berhasil diperbarui.',
            };

            if (request()->wantsJson()) {
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

        if (request()->wantsJson()) {
            return response()->json(['success' => false, 'error' => 'Gagal memproses verifikasi.'], 422);
        }

        return back()->with('error', 'Gagal memproses verifikasi.');
    }

    public function revertToPending(Request $request, AchievementDocument $document)
    {
        // Only admin can revert
        if (!auth()->check() || auth()->user()->role !== 'Admin') {
            abort(403, 'Hanya admin yang dapat mengembalikan status dokumen.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if ($document->revertToPending(auth()->user(), $request->reason)) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status dokumen berhasil dikembalikan ke pending.',
                    'document' => [
                        'status' => $document->fresh()->status,
                        'status_label' => $document->fresh()->status_label,
                    ],
                ]);
            }

            return back()->with('success', 'Status dokumen berhasil dikembalikan ke pending.');
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => false, 'error' => 'Gagal mengembalikan status dokumen.'], 422);
        }

        return back()->with('error', 'Gagal mengembalikan status dokumen.');
    }

    public function addNote(Request $request, AchievementDocument $document)
    {
        // Only admin/validator can add notes
        if (!auth()->check() || !in_array(auth()->user()->role, ['Admin', 'Validator'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        if ($document->addNote(auth()->user(), $request->note)) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Catatan berhasil ditambahkan.',
                ]);
            }

            return back()->with('success', 'Catatan berhasil ditambahkan.');
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => false, 'error' => 'Gagal menambahkan catatan.'], 422);
        }

        return back()->with('error', 'Gagal menambahkan catatan.');
    }

    protected function authorizeDocumentAccess(StudentAchievement $achievement): void
    {
        // IMPORTANT: Check regular user auth FIRST to prevent session conflicts
        // This ensures validators/admins are never confused with students
        if (auth()->check()) {
            $user = auth()->user();

            // Admin can access ALL documents (no restrictions)
            if ($user->role === 'Admin') {
                return;
            }

            // Validator can access documents from their faculty
            if ($user->role === 'Validator') {
                // If validator has faculty assigned, check if achievement is from same faculty
                if ($user->faculty) {
                    $achievementFaculty = $achievement->student->faculty ?? null;
                    if ($achievementFaculty === $user->faculty) {
                        return;
                    }
                    abort(403, 'Anda hanya dapat mengakses dokumen dari fakultas Anda.');
                }
                // Validator without faculty can access all (super validator)
                return;
            }

            // Regular user with student relation can access their own
            if ($user->student && $achievement->student_id === $user->student->student_id) {
                return;
            }

            // Authenticated but not authorized for this document
            abort(403, 'Unauthorized access.');
        }

        // Only check student session if NOT authenticated as regular user
        if (session('auth_role') === 'student' && session('student_id')) {
            // Student can only access their own achievements
            if ($achievement->student_id === session('student_id')) {
                return;
            }
            abort(403, 'Unauthorized access.');
        }

        // Not authenticated at all
        abort(403, 'Unauthorized access.');
    }
}
