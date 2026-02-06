<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexValidationLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'decision' => 'nullable|in:Disetujui,Ditolak,Revisi',
            'validator' => 'nullable|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:5|max:100',
            'faculty_id' => 'nullable|string',
            'department_id' => 'nullable|string',
            'program_study_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal awal.',
            'validator.exists' => 'Validator tidak ditemukan.',
        ];
    }
}
