<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementDocument;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Services\AchievementApprovalService;
use Illuminate\Http\Request;

class AdminAchievementController extends Controller
{
    protected AchievementApprovalService $approvalService;

    public function __construct(AchievementApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Show form for submitting achievement on behalf of student.
     */
    public function create()
    {
        $students = Student::orderBy('name')->get();
        $categories = AchievementCategory::active()->get();
        $levels = AchievementLevel::active()->get();
        $skDocuments = \App\Models\SKDocument::orderBy('issued_date', 'desc')->get();

        return view('admin.submit', compact('students', 'categories', 'levels', 'skDocuments'));
    }

    /**
     * Store achievement submitted by admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,student_id',
            'category_id' => 'required|exists:achievement_categories,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:Universitas,Nasional,Internasional',
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'ranking' => 'nullable|string|max:100',
            'description' => 'nullable|string',

            // Per-student attachments
            'attachments' => 'required|array',
            'attachments.*.certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'attachments.*.additional_documents' => 'nullable|array|max:2',
            'attachments.*.additional_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'submit_action' => 'required|in:pending,approve,reject',
            'skip_sk' => 'nullable|boolean',
            'sk_id' => 'nullable|exists:sk_documents,id',
            'sk_waiver_reason' => 'nullable|in:tingkat_universitas,sk_dalam_proses,dokumen_alternatif,lainnya',
            'sk_waiver_notes' => 'required_if:sk_waiver_reason,lainnya|nullable|string|max:1000',
            'alternative_document' => 'required_if:sk_waiver_reason,dokumen_alternatif|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rejection_reason' => 'required_if:submit_action,reject|nullable|string|max:1000',
        ], [
            'attachments.*.certificate.required' => 'Sertifikat wajib diupload untuk setiap mahasiswa.',
        ]);

        $achievementId = Achievement::where('category_id', $validated['category_id'])
            ->where('is_active', true)
            ->value('id');

        if (!$achievementId) {
            return redirect()->back()
                ->withErrors(['category_id' => 'Kategori yang dipilih belum memiliki template prestasi. Hubungi admin.'])
                ->withInput();
        }

        $studentIds = $validated['student_ids'];
        $createdAchievements = [];
        $errors = [];

        // Attachments from request
        $attachments = $request->file('attachments', []);

        // Process each student
        foreach ($studentIds as $studentId) {
            try {
                // Get files for this specific student
                $studentFiles = $attachments[$studentId] ?? null;

                if (!$studentFiles || !isset($studentFiles['certificate'])) {
                    throw new \Exception("Sertifikat untuk mahasiswa ID {$studentId} tidak ditemukan.");
                }

                $certificate = $studentFiles['certificate'];
                $additionalDocs = $studentFiles['additional_documents'] ?? [];

                // Store certificate for this student
                $certificatePath = $certificate->store('certificates', 'public');

                // Determine initial status based on action
                $initialStatus = match ($request->submit_action) {
                    'approve' => 'Disetujui',
                    'reject' => 'Ditolak',
                    default => 'Menunggu',
                };

                // Determine SK required
                $skRequired = !$request->boolean('skip_sk');

                $achievement = StudentAchievement::create([
                    'student_id' => $studentId,
                    'achievement_id' => $achievementId,
                    'event_name' => $validated['event_name'],
                    'level' => $validated['level'],
                    'organizer' => $validated['organizer'],
                    'event_date' => $validated['event_date'],
                    'ranking' => $validated['ranking'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'certificate' => $certificatePath,
                    'validation_status' => $initialStatus,
                    'validator_id' => $request->submit_action !== 'pending' ? auth()->id() : null,
                    'submitted_by' => 'admin',
                    'submitted_at' => now(),
                    'sk_required' => $skRequired,
                    'sk_waiver_reason' => $request->sk_waiver_reason,
                    'sk_waiver_notes' => $request->sk_waiver_notes,
                ]);

                // Process additional documents if any
                foreach ($additionalDocs as $file) {
                    if ($file->isValid()) {
                        $fileName = 'SuppDoc_' . $achievement->sa_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $filePath = $file->storeAs('achievements/' . $achievement->sa_id, $fileName, 'public');

                        $achievement->documents()->create([
                            'document_type' => AchievementDocument::TYPE_FOTO_DOKUMENTASI,
                            'file_path' => $filePath,
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                            'status' => AchievementDocument::STATUS_PENDING,
                        ]);
                    }
                }

                // Handle different actions
                if ($request->submit_action === 'approve') {
                    $skDocumentPath = null;

                    // Assign SK Document if provided
                    if ($request->sk_id) {
                        $skDocument = \App\Models\SKDocument::find($request->sk_id);
                        if ($skDocument) {
                            // Create SK assignment
                            \App\Models\SKAssignment::create([
                                'sk_id' => $skDocument->id,
                                'sa_id' => $achievement->sa_id,
                                'assigned_by' => auth()->id(),
                                'assigned_at' => now(),
                            ]);

                            $skDocumentPath = $skDocument->file_path;
                        }
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
                        ? 'Disetujui langsung oleh admin saat submit'
                        : 'Disetujui tanpa SK: ' . ($request->sk_waiver_reason ? StudentAchievement::getSkWaiverReasons()[$request->sk_waiver_reason] : 'N/A');

                    $this->approvalService->approve($achievement, auth()->user(), $notes, $skDocumentPath);
                } elseif ($request->submit_action === 'reject') {
                    // Log rejection
                    $this->approvalService->reject($achievement, auth()->user(), $request->rejection_reason);
                }

                $createdAchievements[] = $achievement;
            } catch (\Exception $e) {
                $student = Student::find($studentId);
                $errors[] = "Gagal membuat prestasi untuk {$student->name} ({$studentId}): {$e->getMessage()}";
            }
        }

        // Prepare success message
        $successCount = count($createdAchievements);
        $totalCount = count($studentIds);

        if ($successCount === 0) {
            return redirect()->back()
                ->withErrors(['error' => 'Gagal membuat prestasi untuk semua mahasiswa.'])
                ->withInput();
        }

        $message = "Berhasil membuat prestasi untuk {$successCount} dari {$totalCount} mahasiswa.";

        if (count($errors) > 0) {
            $message .= ' Beberapa gagal: ' . implode(', ', $errors);
        }

        // Redirect based on action
        if ($request->submit_action === 'approve' || $request->submit_action === 'reject') {
            return redirect()->route('admin.student-achievements')
                ->with('success', $message);
        }

        // Default: Pending - redirect to first achievement's document upload
        $firstAchievement = $createdAchievements[0];
        return redirect()->route('achievements.documents.index', $firstAchievement)
            ->with('success', $message . ' Anda dapat menambahkan dokumen tambahan.');
    }
}
