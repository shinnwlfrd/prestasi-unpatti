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
        $query = StudentAchievement::with(['student', 'achievement', 'documents', 'validator'])
            ->latest('submitted_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('credibility')) {
            match($request->credibility) {
                'high' => $query->where('credibility_score', '>=', 80),
                'medium' => $query->whereBetween('credibility_score', [70, 79.99]),
                'low' => $query->where('credibility_score', '<', 70),
                default => null,
            };
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

        if ($request->filled('extra_review')) {
            $query->where('requires_extra_review', true);
        }

        $achievements = $query->paginate(15)->withQueryString();
        $statistics = $this->approvalService->getApprovalStatistics();

        return view('admin.achievements.validation.index', compact('achievements', 'statistics'));
    }

    public function show(StudentAchievement $achievement)
    {
        $achievement->load([
            'student',
            'achievement',
            'documents',
            'validationLogs.validator',
            'checklist',
            'appeals.reviewer',
        ]);

        $checklist = $achievement->checklist ?? new ValidationChecklist([
            'sa_id' => $achievement->sa_id,
            'validator_id' => auth()->id(),
        ]);

        return view('admin.achievements.validation.show', compact('achievement', 'checklist'));
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

        // Process action
        $success = match($request->action) {
            'approve' => $this->approvalService->approve($achievement, $validator, $request->notes),
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
            $message = match($request->action) {
                'approve' => 'Prestasi berhasil disetujui.',
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

    public function saveChecklist(Request $request, StudentAchievement $achievement)
    {
        $validated = $request->validate([
            'nama_peserta_valid' => 'boolean',
            'nama_peserta_notes' => 'nullable|string|max:500',
            'nama_lomba_valid' => 'boolean',
            'nama_lomba_notes' => 'nullable|string|max:500',
            'tanggal_valid' => 'boolean',
            'tanggal_notes' => 'nullable|string|max:500',
            'peringkat_valid' => 'boolean',
            'peringkat_notes' => 'nullable|string|max:500',
            'penyelenggara_valid' => 'boolean',
            'penyelenggara_notes' => 'nullable|string|max:500',
            'keaslian_dokumen_valid' => 'boolean',
            'keaslian_dokumen_notes' => 'nullable|string|max:500',
            'overall_notes' => 'nullable|string|max:1000',
        ]);

        $checklist = ValidationChecklist::updateOrCreate(
            ['sa_id' => $achievement->sa_id, 'validator_id' => auth()->id()],
            $validated
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
            ->with('validator')
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
}
