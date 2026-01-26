<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['Admin', 'Validator']);
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject,request_revision',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|string|max:1000',
            'revision_reason' => 'required_if:action,request_revision|string|max:1000',
            'required_documents' => 'nullable|array',
            'required_documents.*' => 'string|in:sk_resmi,sertifikat,foto_dokumentasi,surat_keterangan,link_publikasi',
            'sk_id' => 'required_if:action,approve|exists:sk_documents,id',

            // Checklist items - updated to match migration
            'checklist.certificate_valid' => 'boolean',
            'checklist.event_date_valid' => 'boolean',
            'checklist.organizer_valid' => 'boolean',
            'checklist.level_appropriate' => 'boolean',
            'checklist.documents_complete' => 'boolean',
            'checklist.notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Pilih tindakan validasi.',
            'action.in' => 'Tindakan tidak valid.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi.',
            'revision_reason.required_if' => 'Alasan permintaan revisi wajib diisi.',
        ];
    }
}
