<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $targetUser = $this->route('user');
        
        // If updating an Admin or Super Admin, only another Super Admin can do it
        // (Admins can only update themselves)
        if ($targetUser && ($targetUser->isAdmin() || $targetUser->isSuperAdmin()) && !auth()->user()->isSuperAdmin() && $targetUser->id !== auth()->id()) {
            return false;
        }

        // Only super_admin can assign the 'Admin' role
        if ($this->input('role') === 'Admin') {
            return auth()->check() && auth()->user()->isSuperAdmin();
        }

        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? $this->route('user');

        return [
            'name' => 'nullable|string|max:255', // Changed to nullable for update
            'email' => [
                'required',
                'email',
                'unique:users,email,'.$userId, // Ignore current user's email
            ],
            'password' => 'nullable|min:6',
            'role' => 'required|in:Admin,Operator,Pimpinan',
            'faculty' => 'required_if:role,Operator|nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar sebagai Admin/Operator lain. Gunakan email lain.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'faculty.required_if' => 'Fakultas wajib dipilih untuk Operator.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $email = $this->input('email');
            $userId = $this->route('user')->id ?? $this->route('user');
            
            // Check if email exists in users table (excluding current user)
            $existingUser = \App\Models\User::where('email', $email)
                ->where('id', '!=', $userId)
                ->first();
            
            if ($existingUser) {
                // Email already exists in users table - this is an error
                $validator->errors()->add('email', 
                    'Email sudah terdaftar sebagai ' . $existingUser->role . ' (' . $existingUser->name . '). ' .
                    'Gunakan email lain.'
                );
            } else {
                // Check if email exists in students table (this is OK for multi-role)
                $student = \App\Models\Student::where('email', $email)->first();
                
                if ($student) {
                    // Log multi-role update
                    \Illuminate\Support\Facades\Log::info('Updating user with multi-role', [
                        'user_id' => $userId,
                        'email' => $email,
                        'student_id' => $student->student_id,
                        'student_name' => $student->name,
                        'role' => $this->input('role')
                    ]);
                }
            }
        });
    }
}
