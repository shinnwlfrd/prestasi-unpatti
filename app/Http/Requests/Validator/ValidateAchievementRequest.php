<?php

namespace App\Http\Requests\Validator;

use Illuminate\Foundation\Http\FormRequest;

class ValidateAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject,request_revision',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
            'revision_reason' => 'required_if:action,request_revision|nullable|string|max:1000',
            'sk_resmi' => 'required_if:action,approve|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'checklist' => 'nullable|array',
            'checklist.certificate_valid' => 'nullable|boolean',
            'checklist.event_date_valid' => 'nullable|boolean',
            'checklist.organizer_valid' => 'nullable|boolean',
            'checklist.level_appropriate' => 'nullable|boolean',
            'checklist.documents_complete' => 'nullable|boolean',
            'checklist.notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Aksi wajib dipilih.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi.',
            'revision_reason.required_if' => 'Alasan revisi wajib diisi.',
            'sk_resmi.required_if' => 'SK Resmi wajib diupload untuk approve prestasi.',
            'sk_resmi.mimes' => 'Format file harus PDF, JPG, JPEG, atau PNG.',
            'sk_resmi.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}
