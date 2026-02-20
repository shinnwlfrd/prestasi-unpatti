<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAchievementRequest;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use App\Services\Student\AchievementService;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {
    }

    public function create()
    {
        $categories = AchievementCategory::active()->get();
        $levels = AchievementLevel::active()->get();

        return view('student.achievement.create', compact('categories', 'levels'));
    }

    public function store(SubmitAchievementRequest $request)
    {
        $studentId = session('student_id');

        if (!$studentId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Debug: Check if certificate file exists
        if (!$request->hasFile('certificate')) {
            return back()->with('error', 'File sertifikat tidak ditemukan dalam request.')->withInput();
        }

        if (!$request->file('certificate')->isValid()) {
            return back()->with('error', 'File sertifikat tidak valid atau gagal diupload.')->withInput();
        }

        $achievement = $this->achievementService->submitAchievement(
            $request->validated(),
            $studentId,
            $request->file('certificate'),
            $request->file('additional_documents') ?? []
        );

        return redirect()->route('student.dashboard')
            ->with('success', 'Prestasi berhasil diajukan! Sertifikat sudah terupload dan menunggu validasi.');
    }

    public function getDocuments(StudentAchievement $achievement)
    {
        try {
            // Check if student owns this achievement
            $studentId = session('student_id');

            if (!$studentId) {
                return response()->json(['error' => 'Session expired'], 401);
            }

            if ($achievement->student_id !== $studentId) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            // Load documents
            $achievement->load(['documents', 'achievement.category']);

            // Prepare certificate data
            $certificate = null;
            if ($achievement->certificate) {
                $ext = pathinfo($achievement->certificate, PATHINFO_EXTENSION);
                $certificate = [
                    'url' => asset('storage/' . $achievement->certificate),
                    'type' => strtoupper($ext),
                ];
            }

            // Prepare documents data
            $documents = $achievement->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'type_label' => $doc->document_type_label ?? 'Dokumen',
                    'file_type' => strtoupper(pathinfo($doc->file_path, PATHINFO_EXTENSION)),
                    'url' => asset('storage/' . $doc->file_path),
                ];
            });

            // Check if can manage (only if status allows editing)
            $canManage = in_array($achievement->validation_status, [
                StudentAchievement::STATUS_DRAFT,
                StudentAchievement::STATUS_FACULTY_REVISION,
                    // Legacy statuses
                StudentAchievement::STATUS_PENDING,
                StudentAchievement::STATUS_NEED_REVISION,
            ]);

            return response()->json([
                'achievement' => [
                    'event_name' => $achievement->event_name,
                    'category' => $achievement->achievement->category->name ?? '-',
                    'level' => $achievement->level,
                ],
                'certificate' => $certificate,
                'documents' => $documents,
                'can_manage' => $canManage,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading documents: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load documents'], 500);
        }
    }

    /**
     * Request review ulang untuk prestasi yang ditolak/revisi
     */
    public function requestReview(Request $request, StudentAchievement $achievement)
    {
        try {
            // Check if student owns this achievement
            $studentId = session('student_id');

            if (!$studentId) {
                return redirect()->route('login')->with('error', 'Session expired. Please login again.');
            }

            if ($achievement->student_id !== $studentId) {
                return redirect()->route('student.dashboard')->with('error', 'Anda tidak memiliki akses ke prestasi ini.');
            }

            // Check if achievement can be resubmitted (only REVISION status)
            $allowedStatuses = [
                StudentAchievement::STATUS_FACULTY_REVISION,
                // Legacy statuses
                'Revisi',
            ];

            if (!in_array($achievement->validation_status, $allowedStatuses)) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'Prestasi dengan status ini tidak dapat diajukan review ulang.');
            }

            // Validate reason if provided
            $reason = $request->input('reason', 'Mahasiswa mengajukan review ulang');

            // Store old status for logging
            $oldStatus = $achievement->validation_status;

            // Update achievement
            $achievement->update([
                'validation_status' => StudentAchievement::STATUS_SUBMITTED,
                'is_resubmission' => true,
                'resubmission_count' => ($achievement->resubmission_count ?? 0) + 1,
                'last_resubmitted_at' => now(),
                'resubmission_reason' => $reason,
                'current_stage' => StudentAchievement::STAGE_FACULTY,
                'faculty_validator_id' => null,
                'faculty_validated_at' => null,
                'faculty_notes' => null,
            ]);

            // Create validation log
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => null,
                'old_status' => $oldStatus,
                'new_status' => StudentAchievement::STATUS_SUBMITTED,
                'notes' => "Review ulang ke-{$achievement->resubmission_count}: {$reason}",
                'validation_type' => 'resubmission',
                'validated_at' => now(),
            ]);

            return redirect()->route('student.dashboard')
                ->with('success', 'Review ulang berhasil diajukan! Prestasi Anda akan divalidasi kembali oleh validator.');
        } catch (\Exception $e) {
            \Log::error('Error requesting review: ' . $e->getMessage(), [
                'achievement_id' => $achievement->sa_id,
                'student_id' => $studentId ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('student.dashboard')
                ->with('error', 'Terjadi kesalahan saat mengajukan review ulang. Silakan coba lagi.');
        }
    }

    /**
     * Soft delete achievement (only for rejected status)
     */
    public function destroy(StudentAchievement $achievement)
    {
        try {
            // Check if student owns this achievement
            $studentId = session('student_id');

            if (!$studentId) {
                return redirect()->route('login')->with('error', 'Session expired. Please login again.');
            }

            if ($achievement->student_id !== $studentId) {
                return redirect()->route('student.dashboard')->with('error', 'Anda tidak memiliki akses ke prestasi ini.');
            }

            // Check if achievement can be deleted (only REJECTED status)
            $allowedStatuses = [
                StudentAchievement::STATUS_FACULTY_REJECTED,
                StudentAchievement::STATUS_UNIVERSITY_REJECTED,
                // Legacy statuses
                'Ditolak',
            ];

            if (!in_array($achievement->validation_status, $allowedStatuses)) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'Hanya prestasi yang ditolak yang dapat dihapus.');
            }

            // Soft delete the achievement
            $achievement->delete();

            // Create validation log for tracking
            ValidationLog::create([
                'sa_id' => $achievement->sa_id,
                'validator_id' => null,
                'old_status' => $achievement->validation_status,
                'new_status' => 'deleted',
                'notes' => 'Prestasi dihapus oleh mahasiswa',
                'validation_type' => 'deletion',
                'validated_at' => now(),
            ]);

            return redirect()->route('student.dashboard')
                ->with('success', 'Prestasi berhasil dihapus. Riwayat tetap tercatat untuk admin.');
        } catch (\Exception $e) {
            \Log::error('Error deleting achievement: ' . $e->getMessage(), [
                'achievement_id' => $achievement->sa_id ?? null,
                'student_id' => $studentId ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('student.dashboard')
                ->with('error', 'Terjadi kesalahan saat menghapus prestasi. Silakan coba lagi.');
        }
    }
}
