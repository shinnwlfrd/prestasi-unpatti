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
            // Support both single and multiple students
            'student_id' => 'nullable|exists:students,student_id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,student_id',
            
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
            'sk_id' => 'required_if:submit_action,approve|required_unless:skip_sk,1|nullable|exists:sk_documents,id',
            'sk_waiver_reason' => 'required_if:skip_sk,1|nullable|in:tingkat_universitas,sk_dalam_proses,dokumen_alternatif,lainnya',
            'sk_waiver_notes' => 'required_if:sk_waiver_reason,lainnya|nullable|string|max:1000',
            'alternative_document' => 'required_if:sk_waiver_reason,dokumen_alternatif|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.exists' => 'Mahasiswa tidak ditemukan.',
            'student_ids.required' => 'Pilih minimal satu mahasiswa.',
            'student_ids.min' => 'Pilih minimal satu mahasiswa.',
            'student_ids.*.required' => 'ID mahasiswa tidak valid.',
            'student_ids.*.exists' => 'Salah satu mahasiswa tidak ditemukan.',
            
            'achievement_id.required' => 'Jenis prestasi wajib dipilih.',
            'achievement_id.exists' => 'Jenis prestasi tidak ditemukan.',
            'event_name.required' => 'Nama kegiatan wajib diisi.',
            'level.required' => 'Tingkat wajib dipilih.',
            'organizer.required' => 'Penyelenggara wajib diisi.',
            'event_date.required' => 'Tanggal kegiatan wajib diisi.',
            'certificate.required' => 'Sertifikat wajib diupload.',
            'certificate.mimes' => 'Format sertifikat harus PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => 'Ukuran sertifikat maksimal 5MB.',
            'sk_id.required_if' => 'SK Resmi wajib dipilih untuk approve.',
            'sk_id.exists' => 'SK yang dipilih tidak ditemukan.',
            'sk_waiver_reason.required_if' => 'Alasan pengecualian SK wajib dipilih.',
            'alternative_document.required_if' => 'Dokumen alternatif wajib diupload.',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation()
    {
        // If student_ids is provided but empty, ensure validation fails
        if ($this->has('student_ids') && empty($this->input('student_ids'))) {
            $this->merge(['student_ids' => null]);
        }
    }
}
