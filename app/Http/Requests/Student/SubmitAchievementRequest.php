<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'achievement_id' => 'required|exists:achievements,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:Universitas,Nasional,Internasional',
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'ranking' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'achievement_id.required' => 'Jenis prestasi wajib dipilih.',
            'achievement_id.exists' => 'Jenis prestasi tidak ditemukan.',
            'event_name.required' => 'Nama kegiatan wajib diisi.',
            'level.required' => 'Tingkat wajib dipilih.',
            'organizer.required' => 'Penyelenggara wajib diisi.',
            'event_date.required' => 'Tanggal kegiatan wajib diisi.',
            'certificate.required' => 'Sertifikat wajib diupload.',
            'certificate.mimes' => 'Format sertifikat harus PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => 'Ukuran sertifikat maksimal 5MB.',
        ];
    }
}
