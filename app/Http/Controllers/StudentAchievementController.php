<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Achievement;
use App\Models\StudentAchievement;

class StudentAchievementController extends Controller
{
    public function create()
    {
        $types = Achievement::with('category')->get();
        return view('student.submit', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'achievement_id' => 'required|exists:achievements,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:Universitas,Nasional,Internasional',
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'description' => 'nullable|string',
            'ranking' => 'nullable|string|max:100',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $achievement = StudentAchievement::create([
            'student_id' => session('student_id'),
            'achievement_id' => $request->achievement_id,
            'event_name' => $request->event_name,
            'level' => $request->level,
            'organizer' => $request->organizer,
            'event_date' => $request->event_date,
            'description' => $request->description,
            'ranking' => $request->ranking,
            'submitted_at' => now(),
            'validation_status' => 'Menunggu',
            'submitted_by' => 'student',
        ]);

        // If certificate uploaded, create document
        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('achievements/' . $achievement->sa_id, 'public');
            $file = $request->file('certificate');
            
            \App\Models\AchievementDocument::create([
                'sa_id' => $achievement->sa_id,
                'document_type' => 'sertifikat',
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'draft',
            ]);
        }

        return redirect()->route('achievements.documents.index', $achievement)
            ->with('success', 'Prestasi berhasil diajukan! Silakan upload dokumen pendukung.');
    }
}
