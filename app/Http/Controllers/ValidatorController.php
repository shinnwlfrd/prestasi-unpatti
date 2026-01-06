<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\ValidationLog;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidatorController extends Controller
{
    public function dashboard()
    {
        $pendingAchievements = StudentAchievement::with(['student', 'achievement'])
            ->where('validation_status', 'Menunggu')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('validator.dashboard', compact('pendingAchievements'));
    }

    public function history()
    {
        $logs = ValidationLog::with(['studentAchievement.student', 'studentAchievement.achievement', 'validator'])
            ->orderBy('validated_at', 'desc')
            ->paginate(10);

        return view('validator.history', compact('logs'));
    }

    // Halaman untuk submit prestasi mahasiswa
    public function submitForm()
    {
        $students = Student::all();
        $achievements = Achievement::all();
        return view('validator.submit', compact('students', 'achievements'));
    }

    // Store prestasi dari validator
    public function submitStore(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'achievement_id' => 'required|exists:achievements,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:Universitas,Nasional,Internasional',
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'description' => 'nullable|string',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Upload certificate
        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        StudentAchievement::create([
            'student_id' => $request->student_id,
            'achievement_id' => $request->achievement_id,
            'event_name' => $request->event_name,
            'level' => $request->level,
            'organizer' => $request->organizer,
            'event_date' => $request->event_date,
            'description' => $request->description,
            'certificate' => $certificatePath,
            'validation_status' => 'Menunggu',
            'submitted_by' => 'validator',
            'submitted_at' => now(),
        ]);

        return redirect()->route('validator.dashboard')->with('success', 'Prestasi mahasiswa berhasil diajukan.');
    }

    public function approve(Request $request, $sa_id)
    {
        $request->validate([
            'sk_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'sk_document.required' => 'Surat SK wajib diupload untuk menyetujui prestasi.',
            'sk_document.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'sk_document.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $ach = StudentAchievement::findOrFail($sa_id);
        $oldStatus = $ach->validation_status;

        // Upload SK document
        $skPath = $request->file('sk_document')->store('sk_documents', 'public');

        $ach->update([
            'validation_status' => 'Disetujui',
            'validator_id' => Auth::id(),
        ]);

        ValidationLog::create([
            'sa_id' => $ach->sa_id,
            'validator_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'Disetujui',
            'sk_document' => $skPath,
            'validated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Prestasi berhasil disetujui dengan Surat SK.');
    }

    public function reject(Request $request, $sa_id)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
            'sk_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'notes.required' => 'Alasan penolakan wajib diisi.',
            'sk_document.required' => 'Surat SK wajib diupload untuk menolak prestasi.',
        ]);

        $ach = StudentAchievement::findOrFail($sa_id);
        $oldStatus = $ach->validation_status;

        // Upload SK document
        $skPath = $request->file('sk_document')->store('sk_documents', 'public');

        $ach->update([
            'validation_status' => 'Ditolak',
            'validator_id' => Auth::id(),
        ]);

        ValidationLog::create([
            'sa_id' => $ach->sa_id,
            'validator_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'Ditolak',
            'notes' => $request->notes,
            'sk_document' => $skPath,
            'validated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Prestasi ditolak dengan Surat SK.');
    }
}
