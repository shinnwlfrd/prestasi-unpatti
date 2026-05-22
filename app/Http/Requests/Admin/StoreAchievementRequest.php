<?php

namespace App\Http\Requests\Admin;

use App\Models\AchievementLevel;
use App\Services\DocumentUploadService;
use Illuminate\Foundation\Http\FormRequest;

class StoreAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxFileSize = (int) (DocumentUploadService::MAX_FILE_SIZE / 1024);

        return [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|string',
            'category_id' => 'required|exists:achievement_categories,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:'.AchievementLevel::active()->pluck('name')->implode(',') ?: 'Universitas,Nasional,Internasional',
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'ranking' => 'nullable|string|max:100',
            'description' => 'nullable|string',

            // Per-student attachments
            'attachments' => 'required|array',
            'attachments.*.certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:'.$maxFileSize,
            'attachments.*.additional_documents' => 'nullable|array|max:2',
            'attachments.*.additional_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:'.$maxFileSize,

            'submit_action' => 'required|in:pending,approve,reject',
            'skip_sk' => 'nullable|boolean',
            'sk_id' => 'nullable|exists:sk_documents,id',
            'sk_waiver_reason' => 'nullable|in:tingkat_universitas,sk_dalam_proses,dokumen_alternatif,lainnya',
            'sk_waiver_notes' => 'required_if:sk_waiver_reason,lainnya|nullable|string|max:1000',
            'alternative_document' => 'required_if:sk_waiver_reason,dokumen_alternatif|nullable|file|mimes:pdf,jpg,jpeg,png|max:'.($maxFileSize * 2),
            'rejection_reason' => 'required_if:submit_action,reject|nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.*.certificate.required' => 'Sertifikat wajib diupload untuk setiap mahasiswa.',
        ];
    }
}
