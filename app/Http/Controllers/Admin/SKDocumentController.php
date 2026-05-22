<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementLevel;
use App\Models\SKDocument;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SKDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = SKDocument::with(['creator'])
            ->withCount('assignments');

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sk_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('assignments.achievement', function ($qa) use ($search) {
                        $qa->where('event_name', 'like', "%{$search}%")
                            ->orWhereHas('student', function ($qs) use ($search) {
                                $qs->where('name', 'like', "%{$search}%")
                                    ->orWhere('student_id', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $skDocuments = $query->orderBy('issued_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sk.index', compact('skDocuments'));
    }

    public function store(Request $request)
    {
        $rules = [
            'sk_number' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'upload_type' => 'required|in:file,link',
            'issued_date' => 'required|date',
            'issued_by' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // Add conditional validation based on upload_type
        if ($request->upload_type === 'file') {
            $rules['sk_file'] = 'required|file|mimes:pdf|max:10240';
        } else {
            $rules['external_link'] = 'required|url|max:500';
        }

        $validated = $request->validate($rules);

        $existing = SKDocument::withTrashed()->where('sk_number', $request->sk_number)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();

                $filePath = $existing->file_path;
                $externalLink = $existing->external_link;

                if ($request->upload_type === 'file' && $request->hasFile('sk_file')) {
                    if ($filePath && Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                    $filePath = $request->file('sk_file')->store('sk_documents', 'public');
                    $externalLink = null;
                } elseif ($request->upload_type === 'link') {
                    if ($filePath && Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                    $externalLink = $request->external_link;
                    $filePath = null;
                }

                $existing->update([
                    'title' => $validated['title'],
                    'file_path' => $filePath,
                    'external_link' => $externalLink,
                    'issued_date' => $validated['issued_date'],
                    'issued_by' => $validated['issued_by'],
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                return redirect()->route('admin.sk.index')
                    ->with('success', 'SK yang sebelumnya dihapus telah dipulihkan dan diperbarui.');
            }

            return back()->withErrors(['sk_number' => 'Nomor SK sudah digunakan.'])->withInput();
        }

        $filePath = null;
        $externalLink = null;

        if ($request->upload_type === 'file' && $request->hasFile('sk_file')) {
            $filePath = $request->file('sk_file')->store('sk_documents', 'public');
        } elseif ($request->upload_type === 'link') {
            $externalLink = $request->external_link;
        }

        SKDocument::create([
            'sk_number' => $validated['sk_number'],
            'title' => $validated['title'],
            'file_path' => $filePath,
            'external_link' => $externalLink,
            'issued_date' => $validated['issued_date'],
            'issued_by' => $validated['issued_by'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.sk.index')
            ->with('success', 'SK berhasil diupload');
    }

    public function show(SKDocument $sk)
    {
        $sk->load(['creator', 'assignments.achievement.student', 'assignments.assignedBy']);

        return view('admin.sk.show', compact('sk'));
    }

    public function destroy(SKDocument $sk)
    {
        // Check if SK has assignments
        if ($sk->assignments()->count() > 0) {
            return back()->with('error', 'SK tidak dapat dihapus karena sudah di-assign ke prestasi');
        }

        // Delete file if exists
        if ($sk->file_path && Storage::disk('public')->exists($sk->file_path)) {
            Storage::disk('public')->delete($sk->file_path);
        }

        $sk->delete();

        return redirect()->route('admin.sk.index')
            ->with('success', 'SK berhasil dihapus');
    }

    public function getAchievements(Request $request, SKDocument $sk)
    {
        $query = StudentAchievement::with(['student', 'achievement.category', 'academicPeriod'])
            ->whereIn('validation_status', [
                StudentAchievement::STATUS_FACULTY_APPROVED,
                StudentAchievement::STATUS_UNIVERSITY_REVIEW,
            ])
            ->whereDoesntHave('skAssignment');

        if ($request->has('q') && ! empty($request->q)) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    });
            });
        }

        $achievements = $query->orderBy('submitted_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'achievements' => $achievements,
        ]);
    }

    public function processAssignment(Request $request, SKDocument $sk)
    {
        $validated = $request->validate([
            'achievement_ids' => 'required|array|min:1',
            'achievement_ids.*' => 'exists:student_achievements,sa_id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignedCount = DB::transaction(function () use ($validated, $sk) {
            $assignedCount = 0;
            $assignedAt = now();
            $userId = auth()->id();

            foreach ($validated['achievement_ids'] as $saId) {
                $achievement = StudentAchievement::with('academicPeriod')->find($saId);

                if (! $achievement || ! $achievement->isInUniversityStage() || $achievement->skAssignment()->exists()) {
                    continue;
                }

                $period = $achievement->academicPeriod;
                if ($period && ! $period->isValidationOpen()) {
                    throw new \Exception('Batas waktu validasi untuk periode "'.$period->name.'" telah berakhir.');
                }

                $sk->assignments()->create([
                    'sa_id' => $saId,
                    'assigned_by' => $userId,
                    'assigned_at' => $assignedAt,
                    'assignment_type' => 'batch',
                    'notes' => $validated['notes'] ?? null,
                ]);

                $oldStatus = $achievement->validation_status;
                $notes = 'Disetujui universitas via batch assignment SK: '.$sk->sk_number;
                if (! empty($validated['notes'])) {
                    $notes .= '. '.$validated['notes'];
                }

                $levelName = $achievement->level;
                $levelRecord = AchievementLevel::where('name', $levelName)->first();
                $categoryName = $achievement->achievement?->category?->name;

                $pointsSnapshot = [
                    'level' => $levelName,
                    'points' => $levelRecord?->points ?? 0,
                    'category' => $categoryName,
                    'level_id' => $levelRecord?->id,
                    'captured_at' => $assignedAt->toIso8601String(),
                ];

                $achievement->update([
                    'validation_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
                    'current_stage' => StudentAchievement::STAGE_COMPLETED,
                    'validator_id' => $userId,
                    'university_validator_id' => $userId,
                    'university_validated_at' => $assignedAt,
                    'university_notes' => $notes,
                    'points_snapshot' => $pointsSnapshot,
                ]);

                $achievement->validationLogs()->create([
                    'validator_id' => $userId,
                    'old_status' => $oldStatus,
                    'new_status' => StudentAchievement::STATUS_UNIVERSITY_APPROVED,
                    'notes' => $notes,
                    'sk_document' => (string) $sk->id,
                    'validation_type' => 'batch_sk_assignment',
                    'validation_stage' => StudentAchievement::STAGE_UNIVERSITY,
                    'stage_action' => 'final_approve',
                    'is_stage_transition' => true,
                    'validated_at' => $assignedAt,
                ]);

                $assignedCount++;
            }

            return $assignedCount;
        });

        return redirect()->route('admin.sk.show', $sk)
            ->with('success', "SK berhasil di-assign ke {$assignedCount} prestasi");
    }

    public function preview(SKDocument $sk)
    {
        if ($sk->file_path && Storage::disk('public')->exists($sk->file_path)) {
            return response()->file(storage_path('app/public/'.$sk->file_path));
        } elseif ($sk->external_link) {
            return redirect($sk->external_link);
        }

        abort(404, 'File SK tidak ditemukan');
    }
}
