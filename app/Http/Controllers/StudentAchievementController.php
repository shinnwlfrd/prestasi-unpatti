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
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_types' => 'required|array|min:1',
            'document_types.*' => 'required|in:certificate,supporting_document,photo,other',
        ], [
            'documents.required' => 'Minimal 1 dokumen harus diupload.',
            'documents.*.required' => 'File dokumen wajib diisi.',
            'documents.*.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'documents.*.max' => 'Ukuran file maksimal 5MB.',
            'document_types.required' => 'Jenis dokumen harus dipilih.',
        ]);

        // Validate at least one certificate
        $hasCertificate = false;
        foreach ($request->document_types as $type) {
            if ($type === 'certificate') {
                $hasCertificate = true;
                break;
            }
        }

        if (!$hasCertificate) {
            return back()->withErrors(['documents' => 'Minimal 1 sertifikat wajib diupload.'])->withInput();
        }

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

        // Upload all documents
        $uploadedCount = 0;
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('achievements/' . $achievement->sa_id, 'public');
                    
                    // Map document type to AchievementDocument type
                    $docType = match($request->document_types[$index] ?? 'other') {
                        'certificate' => \App\Models\AchievementDocument::TYPE_SERTIFIKAT,
                        'supporting_document' => \App\Models\AchievementDocument::TYPE_SUPPORTING_DOCUMENT,
                        'photo' => \App\Models\AchievementDocument::TYPE_PHOTO,
                        default => \App\Models\AchievementDocument::TYPE_OTHER,
                    };
                    
                    \App\Models\AchievementDocument::create([
                        'sa_id' => $achievement->sa_id,
                        'document_type' => $docType,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'status' => 'pending',
                        'uploaded_by' => session('student_id'),
                    ]);
                    
                    $uploadedCount++;
                }
            }
        }

        $message = "Prestasi berhasil diajukan dengan {$uploadedCount} dokumen!";
        if ($uploadedCount < count($request->file('documents'))) {
            $message .= " Beberapa file gagal diupload.";
        }

        return redirect()->route('student.dashboard')
            ->with('success', $message);
    }
}
