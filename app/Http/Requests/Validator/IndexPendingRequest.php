<?php

namespace App\Http\Requests\Validator;

use Illuminate\Foundation\Http\FormRequest;

class IndexPendingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'level' => 'nullable|in:' . \App\Models\AchievementLevel::active()->pluck('name')->implode(','),
            'category' => 'nullable|exists:achievement_categories,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'submitted_by' => 'nullable|in:student,validator,admin',
        ];
    }

    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal awal.',
            'category.exists' => 'Kategori tidak ditemukan.',
        ];
    }
}
