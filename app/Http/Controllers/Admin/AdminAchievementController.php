<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use App\Models\AchievementLevel;
use App\Models\Student;
use App\Models\SKDocument;
use App\Services\Admin\AdminAchievementService;
use App\Http\Requests\Admin\StoreAchievementRequest;
use Illuminate\Http\Request;

class AdminAchievementController extends Controller
{
    protected AdminAchievementService $achievementService;

    public function __construct(AdminAchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Show form for submitting achievement on behalf of student.
     */
    public function create()
    {
        $students = Student::orderBy('name')->get();
        $categories = AchievementCategory::active()->get();
        $levels = AchievementLevel::active()->get();
        $skDocuments = SKDocument::orderBy('issued_date', 'desc')->get();

        // Handle old student_ids to preserve UI state after validation errors
        $selectedStudentsJson = $this->achievementService->resolveStudentDataForOldInput(old('student_ids', []));

        return view('admin.submit', compact('students', 'categories', 'levels', 'skDocuments', 'selectedStudentsJson'));
    }

    /**
     * Store achievement submitted by admin.
     */
    public function store(StoreAchievementRequest $request)
    {
        $validated = $request->validated();
        
        $requestData = $request->only([
            'submit_action', 'skip_sk', 'sk_waiver_reason', 'sk_waiver_notes', 'sk_id', 'rejection_reason'
        ]);
        
        if ($request->hasFile('alternative_document')) {
            $requestData['alternative_document_file'] = $request->file('alternative_document');
        }

        try {
            $result = $this->achievementService->processBatchSubmission(
                $validated,
                $validated['student_ids'],
                $request->file('attachments', []),
                $requestData,
                auth()->user()
            );
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['category_id' => $e->getMessage()])
                ->withInput();
        }

        if ($result['success_count'] === 0) {
            return redirect()->back()
                ->withErrors(['error' => 'Gagal membuat prestasi untuk semua mahasiswa.'])
                ->withInput();
        }

        $message = "Berhasil membuat prestasi untuk {$result['success_count']} dari {$result['total_count']} mahasiswa.";

        if (count($result['errors']) > 0) {
            $message .= ' Beberapa gagal: ' . implode(', ', $result['errors']);
        }

        // Redirect based on action
        if ($request->submit_action === 'approve' || $request->submit_action === 'reject') {
            return redirect()->route('admin.student-achievements')
                ->with('success', $message);
        }

        // Default: Pending - redirect to first achievement's document upload
        $firstAchievement = $result['created_achievements'][0];
        return redirect()->route('achievements.documents.index', $firstAchievement)
            ->with('success', $message . ' Anda dapat menambahkan dokumen tambahan.');
    }
}
