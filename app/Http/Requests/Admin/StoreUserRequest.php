<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                // Remove unique constraint - allow existing emails for multi-role
            ],
            'role' => 'required|in:Admin,Validator,Pimpinan',
            'faculty' => 'required_if:role,Validator|nullable|string|max:255',
            'pimpinan_level' => 'required_if:role,Pimpinan|nullable|in:university,faculty,department,program_study,graduate_program',
            'pimpinan_position' => 'required_if:role,Pimpinan|nullable|in:rektor,dekan,ketua_jurusan,kaprodi,direktur_pps',
            'pimpinan_faculty' => 'nullable|string|max:255',
            'pimpinan_department' => 'nullable|string|max:255',
            'pimpinan_program_study' => 'nullable|string|max:255',
            'faculty_id' => 'nullable|string|max:255',
            'department_id' => 'nullable|string|max:255',
            'program_study_id' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'role.required' => 'Role wajib dipilih.',
            'faculty.required_if' => 'Fakultas wajib dipilih untuk Validator.',
            'pimpinan_level.required_if' => 'Level pimpinan wajib dipilih.',
            'pimpinan_position.required_if' => 'Posisi pimpinan wajib dipilih.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $email = $this->input('email');

            // Check if email exists in users table
            $existingUser = \App\Models\User::where('email', $email)->first();

            if ($existingUser) {
                // Email already exists in users table - this is OK for multi-role
                // Just log it
                \Illuminate\Support\Facades\Log::info('Adding additional role to existing user', [
                    'email' => $email,
                    'existing_user_id' => $existingUser->id,
                    'existing_user_name' => $existingUser->name,
                    'new_role' => $this->input('role'),
                    'new_faculty' => $this->input('faculty')
                ]);
                return;
            }

            // Check if email exists in students table (required for new user)
            $student = \App\Models\Student::where('email', $email)->first();

            if (!$student) {
                // Email not found in students or users table
                $validator->errors()->add(
                    'email',
                    'Email tidak ditemukan. Pastikan user/mahasiswa sudah terdaftar di sistem.'
                );
                return;
            }

            // Log new user creation from student
            \Illuminate\Support\Facades\Log::info('Creating new user from student', [
                'email' => $email,
                'student_id' => $student->student_id,
                'student_name' => $student->name,
                'new_role' => $this->input('role')
            ]);
        });
    }

    /**
     * Get validated data with student name (only for new users)
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Check if user already exists
        $existingUser = \App\Models\User::where('email', $data['email'])->first();

        if (!$existingUser) {
            // New user - get student data to fill name and password
            $student = \App\Models\Student::where('email', $data['email'])->first();

            if ($student) {
                $data['name'] = $student->name;
                // Auto-generate password from student_id
                $data['password'] = $student->student_id;
            }
        }

        return $data;
    }
}
