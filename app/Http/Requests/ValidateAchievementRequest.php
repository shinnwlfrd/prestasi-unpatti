<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['Admin', 'Validator']);
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject,request_revision',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|string|max:1000',
            'revision_reason' => 'required_if:action,request_revision|string|max:1000',
            'required_documents' => 'nullable|array',
            'required_documents.*' => 'string|in:sk_resmi,sertifikat,foto_dokumentasi,surat_keterangan,link_publikasi',
            
            // Checklist items
            'checklist.nama_peserta_valid' => 'boolean',
            'checklist.nama_peserta_notes' => 'nullable|string|max:500',
            'checklist.nama_lomba_valid' => 'boolean',
            'checklist.nama_lomba_notes' => 'nullable|string|max:500',
            'checklist.tanggal_valid' => 'boolean',
            'checklist.tanggal_notes' => 'nullable|string|max:500',
            'checklist.peringkat_valid' => 'boolean',
            'checklist.peringkat_notes' => 'nullable|string|max:500',
            'checklist.penyelenggara_valid' => 'boolean',
            'checklist.penyelenggara_notes' => 'nullable|string|max:500',
            'checklist.keaslian_dokumen_valid' => 'boolean',
            'checklist.keaslian_dokumen_notes' => 'nullable|string|max:500',
            'checklist.overall_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Pilih tindakan validasi.',
            'action.in' => 'Tindakan tidak valid.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi.',
            'revision_reason.required_if' => 'Alasan permintaan revisi wajib diisi.',
        ];
    }
}
