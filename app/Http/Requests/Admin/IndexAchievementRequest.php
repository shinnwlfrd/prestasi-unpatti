<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:Menunggu,Disetujui,Ditolak,Revisi,pending_verification,processing_university,approved,revision,rejected',
            'level' => 'nullable|in:' . \App\Models\AchievementLevel::active()->pluck('name')->implode(','),
            'category' => 'nullable|exists:achievement_categories,id',
            'per_page' => 'nullable|integer|min:5|max:100',
            'faculty_id' => 'nullable|string',
            'department_id' => 'nullable|string',
            'program_study_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'category.exists' => 'Kategori tidak ditemukan.',
        ];
    }
}
