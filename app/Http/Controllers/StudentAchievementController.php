<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\StudentAchievement;

class StudentAchievementController extends Controller
{
    public function create()
    {
        $types = Achievement::all();
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
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('certificate')->store('certificates', 'public');

        StudentAchievement::create([
            'student_id' => session('student_id'),
            'achievement_id' => $request->achievement_id,
            'event_name' => $request->event_name,
            'level' => $request->level,
            'organizer' => $request->organizer,
            'event_date' => $request->event_date,
            'description' => $request->description,
            'certificate_path' => $path,
            'validation_status' => 'Menunggu',
        ]);

        return redirect()->route('student.dashboard')->with('success', 'Prestasi berhasil diajukan!');
    }
}
