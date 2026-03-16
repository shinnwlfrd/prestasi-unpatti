<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAppealRequest;
use App\Models\StudentAchievement;
use App\Services\Student\AppealService;

class AppealController extends Controller
{
    public function __construct(
        protected AppealService $appealService
    ) {}

    public function create(StudentAchievement $achievement)
    {
        $studentId = session('student_id');

        if (! $studentId || $achievement->student_id !== $studentId) {
            abort(403, 'Unauthorized access.');
        }

        if (! $this->appealService->canAppeal($achievement)) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Prestasi ini tidak dapat dibanding.');
        }

        return view('achievements.appeal.create', compact('achievement'));
    }

    public function store(SubmitAppealRequest $request, StudentAchievement $achievement)
    {
        $studentId = session('student_id');

        if (! $studentId || $achievement->student_id !== $studentId) {
            abort(403, 'Unauthorized access.');
        }

        if (! $this->appealService->canAppeal($achievement)) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Prestasi ini tidak dapat dibanding.');
        }

        $this->appealService->submitAppeal(
            $achievement,
            $studentId,
            $request->reason,
            $request->publication_link,
            $request->file('documents')
        );

        return redirect()->route('student.dashboard')
            ->with('success', 'Banding berhasil diajukan. Menunggu review dari validator.');
    }
}
