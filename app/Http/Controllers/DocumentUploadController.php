<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadDocumentsRequest;
use App\Models\AchievementDocument;
use App\Models\StudentAchievement;
use App\Services\CredibilityService;
use App\Services\DocumentUploadService;
use App\Services\DocumentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentUploadController extends Controller
{
    private const ALLOWED_EXTERNAL_DOMAINS = [
        'drive.google.com',
        'docs.google.com',
        'dropbox.com',
        'www.dropbox.com',
    ];

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
        $this->checkAchievementAccess($achievement);

        if ($achievement->isFinalStatus()) {
            $message = 'Prestasi ini sudah dalam status final. Upload dokumen tidak diperbolehkan.';
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->isSuperAdmin()) {
                    return redirect()->route('admin.student-achievements')->with('warning', $message);
                }
                if ($user->isOperator()) {
                    return redirect()->route('validator.pending.index')->with('warning', $message);
                }
            }

            return redirect()->route('student.dashboard')->with('warning', $message);
        }

        $achievement->load(['documents.revisions', 'documents.verifier', 'student', 'achievement.category']);
        $isValidatorOrAdmin = auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isOperator());
        $documentTypes = AchievementDocument::getDocumentTypesForRole($isValidatorOrAdmin ? 'staff' : 'student');

        $isNonAkademik = $achievement->achievement && $achievement->achievement->category_id !== 1;
        $documentStats = $this->verificationService->getDocumentStatistics($achievement);

        return view('achievements.documents.index', compact('achievement', 'documentTypes', 'isNonAkademik', 'documentStats', 'isValidatorOrAdmin'));
    }

    public function store(UploadDocumentsRequest $request, StudentAchievement $achievement)
    {
        $this->checkAchievementAccess($achievement);

        if ($achievement->isFinalStatus()) {
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => 'Status final.'], 403) : back()->with('error', 'Status final.');
        }

        $result = $this->uploadService->uploadMultipleDocuments($achievement, $request->file('documents', []), $request->input('document_types', []), $request->boolean('as_draft', true));

        if ($request->has('external_links')) {
            foreach ($request->external_links as $link) {
                if (! empty($link['url'])) {
                    $this->uploadService->addExternalLink($achievement, $link['url'], $link['title'] ?? 'Link Publikasi', $request->boolean('as_draft', true));
                }
            }
        }

        if (! empty($result['errors'])) {
            return back()->with('warning', 'Beberapa file gagal.')->with('upload_errors', $result['errors']);
        }

        $message = count($result['uploaded']).' dokumen berhasil diunggah.';
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('admin.student-achievements')->with('success', $message);
            }
            if ($user->isOperator()) {
                return redirect()->route('validator.pending.index')->with('success', $message);
            }
        }

        return redirect()->route('student.dashboard')->with('success', $message);
    }

    public function upload(Request $request, StudentAchievement $achievement)
    {
        $this->checkAchievementAccess($achievement);
        $request->validate(['file' => 'required|file|max:10240', 'document_type' => 'required|in:'.implode(',', array_keys(AchievementDocument::DOCUMENT_TYPES))]);

        try {
            $document = $this->uploadService->uploadDocument($achievement, $request->file('file'), $request->document_type, $request->boolean('as_draft', true));

            return response()->json(['success' => true, 'document' => ['id' => $document->id, 'file_name' => $document->file_name, 'file_url' => $document->file_url, 'status_label' => $document->status_label]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function replace(Request $request, AchievementDocument $document)
    {
        $this->authorizeDocumentMutation($document, 'update');
        $request->validate(['file' => 'required|file|max:10240']);

        try {
            $document = $this->uploadService->replaceDocument($document, $request->file('file'));

            return response()->json(['success' => true, 'document' => ['id' => $document->id, 'status_label' => $document->status_label]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function replaceCertificate(Request $request, StudentAchievement $achievement)
    {
        $this->checkAchievementAccess($achievement);
        $request->validate(['file' => 'required|file|max:10240']);

        if ($achievement->isFinalStatus()) {
            return response()->json(['success' => false, 'error' => 'Status final.'], 403);
        }

        try {
            $achievement = $this->uploadService->replaceCertificate($achievement, $request->file('file'));

            return response()->json(['success' => true, 'certificate_url' => asset('storage/'.$achievement->certificate)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function submit(StudentAchievement $achievement)
    {
        $this->checkAchievementAccess($achievement);
        $count = $this->uploadService->submitDocuments($achievement);

        return back()->with($count > 0 ? 'success' : 'warning', $count > 0 ? $count.' dokumen disubmit.' : 'Tidak ada draft.');
    }

    public function submitSingle(AchievementDocument $document)
    {
        $this->authorizeDocumentMutation($document, 'update');
        if ($this->uploadService->submitSingleDocument($document)) {
            return request()->wantsJson() ? response()->json(['success' => true, 'status_label' => $document->fresh()->status_label]) : back()->with('success', 'Berhasil.');
        }

        return request()->wantsJson() ? response()->json(['success' => false], 422) : back()->with('error', 'Gagal.');
    }

    public function destroy(AchievementDocument $document)
    {
        $this->authorizeDocumentMutation($document, 'delete');
        try {
            $this->uploadService->deleteDocument($document);

            return request()->wantsJson() ? response()->json(['success' => true]) : back()->with('success', 'Berhasil dihapus.');
        } catch (\Exception $e) {
            return request()->wantsJson() ? response()->json(['success' => false, 'error' => $e->getMessage()], 422) : back()->with('error', $e->getMessage());
        }
    }

    public function preview(AchievementDocument $document)
    {
        $this->authorizeDocumentView($document);
        if ($document->document_type === AchievementDocument::TYPE_LINK_PUBLIKASI) {
            if (! $this->isAllowedExternalDomain($document->external_link)) {
                Log::warning('Blocked untrusted external document link', [
                    'document_id' => $document->id,
                    'user_id' => auth()->id(),
                    'url' => $document->external_link,
                ]);

                abort(403, 'Untrusted external link');
            }

            return redirect()->away($document->external_link);
        }
        if (! $document->file_path || ! file_exists(storage_path('app/public/'.$document->file_path))) {
            abort(404);
        }

        return response()->file(storage_path('app/public/'.$document->file_path));
    }

    public function previewCertificate(StudentAchievement $achievement)
    {
        $this->checkAchievementAccess($achievement);
        if (! $achievement->certificate || ! file_exists(storage_path('app/public/'.$achievement->certificate))) {
            abort(404);
        }

        return response()->file(storage_path('app/public/'.$achievement->certificate));
    }

    public function history(AchievementDocument $document)
    {
        $this->authorizeDocumentView($document);
        $document->load(['revisions.performer', 'studentAchievement']);

        return view('achievements.documents.history', compact('document'));
    }

    public function verify(Request $request, AchievementDocument $document)
    {
        $request->validate(['action' => 'required|in:approve,reject,revision', 'notes' => 'required_if:action,reject,revision|nullable|string|max:1000']);
        $achievement = $document->studentAchievement;
        if (! $achievement) {
            abort(404);
        }

        $this->authorizeDocumentReview($achievement);
        $user = auth()->user();
        $result = match ($request->action) {
            'approve' => $this->verificationService->approveDocument($document, $user, $request->notes),
            'reject' => $this->verificationService->rejectDocument($document, $user, $request->notes),
            'revision' => $this->verificationService->requestRevision($document, $user, $request->notes),
            default => false,
        };

        return $result ? (request()->wantsJson() ? response()->json(['success' => true, 'status_label' => $document->fresh()->status_label]) : back()->with('success', 'Berhasil.')) : (request()->wantsJson() ? response()->json(['success' => false], 422) : back()->with('error', 'Gagal.'));
    }

    public function revertToPending(Request $request, AchievementDocument $document)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        $achievement = $document->studentAchievement;
        if (! $achievement) {
            abort(404);
        }

        $this->authorizeDocumentReview($achievement);
        $result = $document->revertToPending(auth()->user(), $request->input('reason'));

        return $result
            ? response()->json(['success' => true, 'status_label' => $document->fresh()->status_label])
            : response()->json(['success' => false, 'error' => 'Dokumen tidak dapat dikembalikan ke pending.'], 422);
    }

    public function addNote(Request $request, AchievementDocument $document)
    {
        $request->validate(['note' => 'required|string|max:1000']);

        $achievement = $document->studentAchievement;
        if (! $achievement) {
            abort(404);
        }

        $this->authorizeDocumentReview($achievement);
        $result = $document->addNote(auth()->user(), $request->string('note')->toString());

        return $result
            ? response()->json(['success' => true])
            : response()->json(['success' => false, 'error' => 'Catatan gagal ditambahkan.'], 422);
    }

    protected function checkAchievementAccess(StudentAchievement $achievement, bool $readOnly = false): void
    {
        if (session('auth_role') === 'student' && session('student_id') === $achievement->student_id) {
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $activeRoleType = $this->resolveActiveRoleType($user);

            if ($user->isSuperAdmin() || $activeRoleType === 'admin') {
                return;
            }

            if ($activeRoleType === 'operator') {
                $level = session('operator_level') ?? 'faculty';
                if ($level === 'university' || ($level === 'faculty' && $achievement->student->faculty_id == session('operator_faculty_id'))) {
                    return;
                }
            }

            if ($readOnly && $activeRoleType === 'pimpinan') {
                $level = session('pimpinan_level') ?? 'university';
                if ($level === 'university' || ($level === 'faculty' && $achievement->student->faculty_id == session('pimpinan_faculty_id'))) {
                    return;
                }
            }
        }

        abort(403, 'Unauthorized.');
    }

    protected function authorizeDocumentView(AchievementDocument $document): void
    {
        $achievement = $document->studentAchievement;
        if (! $achievement) {
            abort(404);
        }

        if (! auth()->check()) {
            if (session('auth_role') === 'student' && session('student_id') === $achievement->student_id) {
                return;
            }

            abort(403, 'Unauthorized.');
        }

        $this->checkAchievementAccess($achievement, true);
    }

    protected function authorizeDocumentMutation(AchievementDocument $document, string $ability): void
    {
        if (! auth()->check()) {
            $achievement = $document->studentAchievement;
            if ($achievement && session('auth_role') === 'student' && session('student_id') === $achievement->student_id) {
                return;
            }

            abort(403, 'Unauthorized.');
        }

        $this->authorize($ability, $document);
    }

    protected function authorizeDocumentReview(StudentAchievement $achievement): void
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized.');
        }

        $activeRoleType = $this->resolveActiveRoleType(auth()->user());
        if (! in_array($activeRoleType, ['admin', 'operator'], true) && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses verifikasi dokumen hanya untuk admin atau operator aktif.');
        }

        $this->checkAchievementAccess($achievement, true);
    }

    protected function resolveActiveRoleType($user): ?string
    {
        $activeRoleType = session('active_role_type');
        if ($activeRoleType) {
            return $activeRoleType;
        }

        $activeRoles = $user->activeRoles()->get();
        if ($activeRoles->count() === 1) {
            return $activeRoles->first()->role;
        }

        return match (Str::lower((string) $user->role)) {
            'admin' => 'admin',
            'operator', 'validator' => 'operator',
            'pimpinan' => 'pimpinan',
            'mahasiswa' => 'mahasiswa',
            default => null,
        };
    }

    private function isAllowedExternalDomain(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            return false;
        }

        foreach (self::ALLOWED_EXTERNAL_DOMAINS as $allowedDomain) {
            if ($host === $allowedDomain || str_ends_with($host, '.'.$allowedDomain)) {
                return true;
            }
        }

        return false;
    }
}
