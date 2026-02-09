<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAchievementRequest;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\StudentAchievement;
use App\Services\Student\AchievementService;

class AchievementController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {
    }

    public function create()
    {
        $categories = AchievementCategory::active()->get();
        $achievements = Achievement::with('category')->get();
        $levels = AchievementLevel::active()->get();

        return view('student.achievement.create', compact('categories', 'achievements', 'levels'));
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
            $request->file('certificate')
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
}
