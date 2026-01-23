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
        $achievements = Achievement::with('category')->get();
        $categories = AchievementCategory::active()->get();
        $levels = AchievementLevel::active()->get();

        return view('admin.achievements.submit', compact('students', 'achievements', 'categories', 'levels'));
    }

    /**
     * Store achievement submitted by admin.
     */
    public function store(Request $request)
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
            'submit_action' => 'required|in:pending,approve,reject',
            'skip_sk' => 'nullable|boolean',
            'sk_resmi' => 'required_if:submit_action,approve|required_unless:skip_sk,1|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sk_waiver_reason' => 'required_if:skip_sk,1|nullable|in:tingkat_universitas,sk_dalam_proses,dokumen_alternatif,lainnya',
            'sk_waiver_notes' => 'required_if:sk_waiver_reason,lainnya|nullable|string|max:1000',
            'alternative_document' => 'required_if:sk_waiver_reason,dokumen_alternatif|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rejection_reason' => 'required_if:submit_action,reject|nullable|string|max:1000',
        ]);

        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        // Determine initial status based on action
        $initialStatus = match ($request->submit_action) {
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            default => 'Menunggu',
        };

        // Determine SK required
        $skRequired = ! $request->boolean('skip_sk');

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
            'validator_id' => $request->submit_action !== 'pending' ? auth()->id() : null,
            'submitted_by' => 'admin',
            'submitted_at' => now(),
            'sk_required' => $skRequired,
            'sk_waiver_reason' => $request->sk_waiver_reason,
            'sk_waiver_notes' => $request->sk_waiver_notes,
        ]);

        // Handle different actions
        if ($request->submit_action === 'approve') {
            $skDocumentPath = null;

            // Upload SK Resmi if provided
            if ($request->hasFile('sk_resmi')) {
                $file = $request->file('sk_resmi');
                $fileName = 'SK_Resmi_'.$achievement->sa_id.'_'.time().'.'.$file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

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
                $fileName = 'Alt_Doc_'.$achievement->sa_id.'_'.time().'.'.$file->getClientOriginalExtension();
                $filePath = $file->storeAs('achievements/'.$achievement->sa_id, $fileName, 'public');

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
                : 'Disetujui tanpa SK: '.($request->sk_waiver_reason ? StudentAchievement::getSkWaiverReasons()[$request->sk_waiver_reason] : 'N/A');

            $this->approvalService->approve($achievement, auth()->user(), $notes, $skDocumentPath);

            return redirect()->route('admin.achievements.validation.index')
                ->with('success', 'Prestasi berhasil diajukan dan langsung disetujui.');
        }

        if ($request->submit_action === 'reject') {
            // Log rejection
            $this->approvalService->reject($achievement, auth()->user(), $request->rejection_reason);

            return redirect()->route('admin.achievements.validation.index')
                ->with('success', 'Prestasi berhasil diajukan dan langsung ditolak.');
        }

        // Default: Pending - redirect to document upload
        return redirect()->route('achievements.documents.index', $achievement)
            ->with('success', 'Prestasi mahasiswa berhasil diajukan. Anda dapat menambahkan dokumen tambahan di bawah ini.');
    }
}
