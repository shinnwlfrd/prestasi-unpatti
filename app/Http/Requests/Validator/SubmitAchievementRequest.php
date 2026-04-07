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

            'category_id' => 'required|exists:achievement_categories,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:' . \App\Models\AchievementLevel::active()->pluck('name')->implode(','),
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'ranking' => 'nullable|string|max:100',
            'description' => 'nullable|string',

            // Per-student attachments
            'attachments' => 'required|array',
            'attachments.*.certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'attachments.*.additional_documents' => 'nullable|array|max:2',
            'attachments.*.additional_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'submit_action' => 'required|in:pending,approve',
            'skip_sk' => 'nullable|boolean',
            'sk_id' => 'nullable|exists:sk_documents,id',
            'sk_waiver_reason' => 'nullable|in:tingkat_universitas,sk_dalam_proses,dokumen_alternatif,lainnya',
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

            'category_id.required' => 'Kategori prestasi wajib dipilih.',
            'category_id.exists' => 'Kategori prestasi tidak ditemukan.',
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
            'additional_documents.max' => 'Maksimal 2 file pendukung yang diperbolehkan.',
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
