<?php

namespace App\Http\Requests\Student;

use App\Models\AchievementLevel;
use App\Services\DocumentUploadService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxFileSize = (int) (DocumentUploadService::MAX_FILE_SIZE / 1024);
        $validLevels = AchievementLevel::active()->pluck('name')->toArray();

        return [
            'category_id' => 'required|exists:achievement_categories,id',
            'event_name' => 'required|string|max:255',
            'level' => 'required|in:'.implode(',', $validLevels),
            'organizer' => 'required|string|max:255',
            'event_date' => 'required|date',
            'ranking' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:'.$maxFileSize,
            'additional_documents' => 'nullable|array|max:2',
            'additional_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:'.$maxFileSize,
        ];
    }

    public function messages(): array
    {
        $maxSizeMB = DocumentUploadService::MAX_FILE_SIZE / 1024 / 1024;

        return [
            'category_id.required' => 'Kategori prestasi wajib dipilih.',
            'category_id.exists' => 'Kategori prestasi tidak ditemukan.',
            'event_name.required' => 'Nama kegiatan wajib diisi.',
            'level.required' => 'Tingkat wajib dipilih.',
            'organizer.required' => 'Penyelenggara wajib diisi.',
            'event_date.required' => 'Tanggal kegiatan wajib diisi.',
            'certificate.required' => 'Sertifikat wajib diupload.',
            'certificate.mimes' => 'Format sertifikat harus PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => "Ukuran sertifikat maksimal {$maxSizeMB}MB.",
            'additional_documents.max' => 'Maksimal 2 file pendukung yang diperbolehkan.',
        ];
    }
}
