<?php

namespace App\Http\Requests;

use App\Models\AchievementDocument;
use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'documents' => 'required|array|min:1',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_types' => 'required|array|min:1',
            'document_types.*' => 'required|in:'.implode(',', array_keys(AchievementDocument::DOCUMENT_TYPES)),
            'external_links' => 'nullable|array',
            'external_links.*.url' => 'nullable|url',
            'external_links.*.title' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'documents.required' => 'Minimal satu dokumen harus diunggah.',
            'documents.*.file' => 'File tidak valid.',
            'documents.*.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'documents.*.max' => 'Ukuran file maksimal 10MB.',
            'document_types.required' => 'Jenis dokumen harus dipilih.',
            'document_types.*.required' => 'Pilih jenis untuk setiap dokumen.',
            'document_types.*.in' => 'Jenis dokumen tidak valid.',
            'external_links.*.url.url' => 'Format URL tidak valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if documents and types count match
            $documents = $this->file('documents', []);
            $types = $this->input('document_types', []);

            if (count($documents) !== count($types)) {
                $validator->errors()->add('documents', 'Jumlah dokumen dan jenis dokumen harus sama.');
            }
        });
    }
}
