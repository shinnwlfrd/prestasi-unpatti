<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'faculty' => 'nullable|string',
            'angkatan' => 'nullable|integer|min:2000|max:2030',
            'faculty_id' => 'nullable|string',
            'department_id' => 'nullable|string',
            'program_study_id' => 'nullable|string',
        ];
    }
}
