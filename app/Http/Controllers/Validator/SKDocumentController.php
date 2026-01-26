<?php

namespace App\Http\Controllers\Validator;

use App\Http\Controllers\Controller;
use App\Models\SKDocument;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;

class SKDocumentController extends Controller
{
    public function index()
    {
        $skDocuments = SKDocument::with('creator')
            ->withCount('assignments')
            ->orderBy('issued_date', 'desc')
            ->paginate(20);

        return view('validator.sk.index', compact('skDocuments'));
    }

    public function show(SKDocument $sk)
    {
        $sk->load(['creator', 'assignments.achievement.student', 'assignments.assignedBy']);
        
        return view('validator.sk.show', compact('sk'));
    }

    public function getAchievements(SKDocument $sk)
    {
        // Get pending achievements for AJAX request
        $achievements = StudentAchievement::with(['student', 'achievement.category', 'academicPeriod'])
            ->where('validation_status', StudentAchievement::STATUS_PENDING)
            ->whereDoesntHave('skAssignment')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return response()->json([
            'achievements' => $achievements
        ]);
    }

    public function processAssignment(Request $request, SKDocument $sk)
    {
        $validated = $request->validate([
            'achievement_ids' => 'required|array|min:1',
            'achievement_ids.*' => 'exists:student_achievements,sa_id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignedCount = 0;
        $assignedAt = now();

        foreach ($validated['achievement_ids'] as $saId) {
            $achievement = StudentAchievement::find($saId);
            
            // Skip if already has SK or not pending
            if ($achievement->validation_status !== StudentAchievement::STATUS_PENDING) {
                continue;
            }

            // Create assignment
            $sk->assignments()->create([
                'sa_id' => $saId,
                'assigned_by' => auth()->id(),
                'assigned_at' => $assignedAt,
                'assignment_type' => 'batch',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update achievement status to approved
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_APPROVED,
                'validator_id' => auth()->id(),
            ]);

            // Create validation log
            $achievement->validationLogs()->create([
                'validator_id' => auth()->id(),
                'old_status' => StudentAchievement::STATUS_PENDING,
                'new_status' => StudentAchievement::STATUS_APPROVED,
                'notes' => 'Disetujui via batch assignment SK: ' . $sk->sk_number,
                'validation_type' => 'batch_sk_assignment',
                'validated_at' => $assignedAt,
            ]);

            $assignedCount++;
        }

        return redirect()->route('validator.sk.show', $sk)
            ->with('success', "SK berhasil di-assign ke {$assignedCount} prestasi");
    }

    public function preview(SKDocument $sk)
    {
        if ($sk->file_path && \Storage::disk('public')->exists($sk->file_path)) {
            return response()->file(storage_path('app/public/' . $sk->file_path));
        } elseif ($sk->external_link) {
            return redirect($sk->external_link);
        }

        abort(404, 'File SK tidak ditemukan');
    }
}
