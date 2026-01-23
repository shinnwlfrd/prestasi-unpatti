<?php

namespace App\Http\Requests\Validator;

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
            'student_id' => 'required|exists:students,student_id',
            'achievement_id' => 'required|exists:achievements,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:Universitas,Nasional,Internasional',
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'ranking' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'submit_action' => 'required|in:pending,approve',
            'skip_sk' => 'nullable|boolean',
            'sk_resmi' => 'required_if:submit_action,approve|required_unless:skip_sk,1|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sk_waiver_reason' => 'required_if:skip_sk,1|nullable|in:tingkat_universitas,sk_dalam_proses,dokumen_alternatif,lainnya',
            'sk_waiver_notes' => 'required_if:sk_waiver_reason,lainnya|nullable|string|max:1000',
            'alternative_document' => 'required_if:sk_waiver_reason,dokumen_alternatif|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Mahasiswa wajib dipilih.',
            'student_id.exists' => 'Mahasiswa tidak ditemukan.',
            'achievement_id.required' => 'Jenis prestasi wajib dipilih.',
            'achievement_id.exists' => 'Jenis prestasi tidak ditemukan.',
            'event_name.required' => 'Nama kegiatan wajib diisi.',
            'level.required' => 'Tingkat wajib dipilih.',
            'organizer.required' => 'Penyelenggara wajib diisi.',
            'event_date.required' => 'Tanggal kegiatan wajib diisi.',
            'certificate.required' => 'Sertifikat wajib diupload.',
            'certificate.mimes' => 'Format sertifikat harus PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => 'Ukuran sertifikat maksimal 5MB.',
            'sk_resmi.required_if' => 'SK Resmi wajib diupload untuk approve.',
            'sk_resmi.mimes' => 'Format SK Resmi harus PDF, JPG, JPEG, atau PNG.',
            'sk_resmi.max' => 'Ukuran SK Resmi maksimal 10MB.',
            'sk_waiver_reason.required_if' => 'Alasan pengecualian SK wajib dipilih.',
            'alternative_document.required_if' => 'Dokumen alternatif wajib diupload.',
        ];
    }
}
