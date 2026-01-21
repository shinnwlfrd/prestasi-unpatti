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
        $achievements = Achievement::all();
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
            'sk_resmi' => 'required_if:submit_action,approve|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rejection_reason' => 'required_if:submit_action,reject|nullable|string|max:1000',
        ]);

        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        // Determine initial status based on action
        $initialStatus = match($request->submit_action) {
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            default => 'Menunggu',
        };

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
        ]);

        // Handle different actions
        if ($request->submit_action === 'approve') {
            // Upload SK Resmi
            $skDocumentPath = null;
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

            // Log approval
            $this->approvalService->approve($achievement, auth()->user(), 'Disetujui langsung oleh admin saat submit', $skDocumentPath);

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
