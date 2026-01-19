<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'appeal_reason' => 'required|string|min:50|max:2000',
            'additional_documents' => 'nullable|array',
            'additional_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_types' => 'nullable|array',
            'document_types.*' => 'string|in:sk_resmi,sertifikat,foto_dokumentasi,surat_keterangan,link_publikasi',
        ];
    }

    public function messages(): array
    {
        return [
            'appeal_reason.required' => 'Alasan banding wajib diisi.',
            'appeal_reason.min' => 'Alasan banding minimal 50 karakter.',
            'appeal_reason.max' => 'Alasan banding maksimal 2000 karakter.',
            'additional_documents.*.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'additional_documents.*.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}
