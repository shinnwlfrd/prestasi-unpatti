<?php

namespace App\Http\Requests\Validator;

use Illuminate\Foundation\Http\FormRequest;

class IndexHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:Disetujui,Ditolak,Revisi',
            'category' => 'nullable|exists:achievement_categories,id',
            'level' => 'nullable|in:' . \App\Models\AchievementLevel::active()->pluck('name')->implode(','),
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:5|max:100',
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
