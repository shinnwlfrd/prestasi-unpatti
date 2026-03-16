<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if student is authenticated via session
        if (session('auth_role') !== 'student' || ! session('student_id')) {
            return false;
        }

        // Get the achievement from route parameter
        $achievement = $this->route('achievement');

        // Check if achievement exists
        if (! $achievement) {
            return false;
        }

        // Check if the logged-in student is the owner of the achievement
        return $achievement->student_id === session('student_id');
    }

    public function rules(): array
    {
        return [
            'appeal_reason' => 'required|string|min:50|max:2000',
            'publication_link' => 'nullable|url|max:500',
            'additional_documents' => 'nullable|array',
            'additional_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|in:sertifikat,foto_dokumentasi,surat_keterangan,dokumen_lainnya',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Jika tidak ada link publikasi dan tidak ada dokumen, berikan error
            if (! $this->filled('publication_link') && ! $this->hasFile('additional_documents')) {
                $validator->errors()->add(
                    'additional_documents',
                    'Anda harus mengupload minimal 1 dokumen tambahan atau memasukkan link publikasi.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'appeal_reason.required' => 'Alasan banding wajib diisi.',
            'appeal_reason.min' => 'Alasan banding minimal 50 karakter.',
            'appeal_reason.max' => 'Alasan banding maksimal 2000 karakter.',
            'publication_link.url' => 'Link publikasi harus berupa URL yang valid.',
            'publication_link.max' => 'Link publikasi maksimal 500 karakter.',
            'additional_documents.*.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'additional_documents.*.max' => 'Ukuran file maksimal 10MB.',
            'document_types.*.in' => 'Jenis dokumen tidak valid.',
        ];
    }
}
