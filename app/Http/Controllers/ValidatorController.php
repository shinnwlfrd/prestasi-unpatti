<?php

namespace App\Http\Controllers;

use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidatorController extends Controller
{
    public function dashboard()
    {
        $pendingAchievements = StudentAchievement::with(['student', 'achievement'])
            ->where('validation_status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('validator.dashboard', compact('pendingAchievements'));
    }

    public function history()
    {
        $logs = ValidationLog::with(['studentAchievement.student', 'validator'])
            ->orderBy('validated_at', 'desc')
            ->paginate(10);

        return view('validator.history', compact('logs'));
    }

    public function approve($sa_id)
    {
        $ach = StudentAchievement::findOrFail($sa_id);
        $oldStatus = $ach->validation_status;

        $ach->update([
            'validation_status' => 'Approved',
            'validator_id' => Auth::id(),
        ]);

        ValidationLog::create([
            'sa_id' => $ach->sa_id,
            'validator_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'Approved',
            'validated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Prestasi disetujui.');
    }

    public function reject(Request $request, $sa_id)
    {
        $request->validate(['notes' => 'required|string|max:500']);

        $ach = StudentAchievement::findOrFail($sa_id);
        $oldStatus = $ach->validation_status;

        $ach->update([
            'validation_status' => 'Rejected',
            'validator_id' => Auth::id(),
        ]);

        ValidationLog::create([
            'sa_id' => $ach->sa_id,
            'validator_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'Rejected',
            'notes' => $request->notes,
            'validated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}