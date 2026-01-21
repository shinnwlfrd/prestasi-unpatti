# Fix: Link Publikasi pada Form Banding

## Perubahan yang Diminta
1. Hilangkan "Link Publikasi" dari pilihan jenis dokumen
2. Jika sudah memasukkan link publikasi, maka upload dokumen menjadi opsional (tidak wajib)

## Implementasi

### 1. Hilangkan "Link Publikasi" dari Dropdown Jenis Dokumen
**File**: `resources/views/achievements/appeal/create.blade.php`

**Perubahan**:
```php
// BEFORE: Exclude hanya sk_resmi
@if($type !== 'sk_resmi')

// AFTER: Exclude sk_resmi DAN link_publikasi
@if($type !== 'sk_resmi' && $type !== 'link_publikasi')
```

**Alasan**:
- Link publikasi sudah ada field tersendiri di form
- Tidak perlu muncul di dropdown jenis dokumen
- Menghindari duplikasi

### 2. Upload Dokumen Menjadi Opsional Jika Ada Link Publikasi
**File**: `app/Http/Requests/SubmitAppealRequest.php`

**Validation Rules**:
```php
public function rules(): array
{
    return [
        'appeal_reason' => 'required|string|min:50|max:2000',
        'publication_link' => 'nullable|url|max:500',
        'additional_documents' => 'nullable|array',  // ← Nullable
        'additional_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        'document_types' => 'nullable|array',
        'document_types.*' => 'nullable|string|in:sertifikat,foto_dokumentasi,surat_keterangan,dokumen_lainnya',
    ];
}
```

**Custom Validation**:
```php
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Jika tidak ada link publikasi DAN tidak ada dokumen, berikan error
        if (!$this->filled('publication_link') && !$this->hasFile('additional_documents')) {
            $validator->errors()->add(
                'additional_documents',
                'Anda harus mengupload minimal 1 dokumen tambahan atau memasukkan link publikasi.'
            );
        }
    });
}
```

**Logika**:
- ✅ Link publikasi saja → Valid
- ✅ Dokumen saja → Valid
- ✅ Link publikasi + Dokumen → Valid
- ❌ Tidak ada keduanya → Error

### 3. Update UI dengan Info Box
**File**: `resources/views/achievements/appeal/create.blade.php`

**Perubahan**:
- Hapus "(Opsional)" dari label "Link Publikasi"
- Hapus "(Opsional)" dari label "Dokumen Tambahan"
- Tambah info box yang menjelaskan user harus mengisi minimal salah satu

**Info Box**:
```html
<div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
    <p class="font-medium text-blue-800 dark:text-blue-200 text-sm">Catatan Penting</p>
    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
        Anda harus mengisi <strong>minimal salah satu</strong>: 
        Link Publikasi <strong>ATAU</strong> Upload Dokumen Tambahan (atau keduanya).
    </p>
</div>
```

### 4. Tambah Error Display
```html
<label>Dokumen Tambahan</label>
@error('additional_documents')
    <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
@enderror
```

## Validation Scenarios

### Scenario 1: Hanya Link Publikasi
**Input**:
- Appeal reason: ✅ (min 50 char)
- Publication link: ✅ https://example.com/prestasi
- Documents: ❌ (tidak upload)

**Result**: ✅ **VALID** - Form berhasil submit

### Scenario 2: Hanya Upload Dokumen
**Input**:
- Appeal reason: ✅ (min 50 char)
- Publication link: ❌ (kosong)
- Documents: ✅ (1 file PDF)

**Result**: ✅ **VALID** - Form berhasil submit

### Scenario 3: Keduanya Ada
**Input**:
- Appeal reason: ✅ (min 50 char)
- Publication link: ✅ https://example.com/prestasi
- Documents: ✅ (2 files)

**Result**: ✅ **VALID** - Form berhasil submit

### Scenario 4: Tidak Ada Keduanya
**Input**:
- Appeal reason: ✅ (min 50 char)
- Publication link: ❌ (kosong)
- Documents: ❌ (tidak upload)

**Result**: ❌ **INVALID** - Error: "Anda harus mengupload minimal 1 dokumen tambahan atau memasukkan link publikasi."

### Scenario 5: Link Publikasi Invalid
**Input**:
- Appeal reason: ✅ (min 50 char)
- Publication link: ❌ "bukan-url" (bukan URL valid)
- Documents: ❌ (tidak upload)

**Result**: ❌ **INVALID** - Error: "Link publikasi harus berupa URL yang valid."

## Jenis Dokumen yang Tersedia

### Sebelum:
- Sertifikat
- Foto Dokumentasi
- Surat Keterangan
- **Link Publikasi** ← Dihapus
- Dokumen Lainnya

### Sesudah:
- Sertifikat
- Foto Dokumentasi
- Surat Keterangan
- Dokumen Lainnya

**Catatan**: SK Resmi tetap tidak muncul (hanya untuk Validator/Admin)

## Testing Steps

### Test 1: Submit dengan Link Publikasi Saja
1. Login sebagai mahasiswa
2. Buka form banding
3. Isi alasan banding (min 50 char)
4. Isi link publikasi: `https://example.com/prestasi`
5. **Jangan upload dokumen**
6. Submit form
7. ✅ Form berhasil submit
8. ✅ Link publikasi tersimpan sebagai dokumen

### Test 2: Submit dengan Dokumen Saja
1. Isi alasan banding
2. **Jangan isi link publikasi**
3. Upload 1 file PDF
4. Pilih jenis dokumen: "Sertifikat"
5. Submit form
6. ✅ Form berhasil submit
7. ✅ Dokumen tersimpan

### Test 3: Submit dengan Keduanya
1. Isi alasan banding
2. Isi link publikasi
3. Upload 2 files
4. Pilih jenis dokumen untuk setiap file
5. Submit form
6. ✅ Form berhasil submit
7. ✅ Link publikasi + 2 dokumen tersimpan

### Test 4: Submit Tanpa Keduanya
1. Isi alasan banding
2. **Jangan isi link publikasi**
3. **Jangan upload dokumen**
4. Submit form
5. ✅ Error muncul: "Anda harus mengupload minimal 1 dokumen tambahan atau memasukkan link publikasi."

### Test 5: Link Publikasi Invalid
1. Isi alasan banding
2. Isi link publikasi: "bukan-url"
3. Submit form
4. ✅ Error: "Link publikasi harus berupa URL yang valid."

### Test 6: Dropdown Jenis Dokumen
1. Upload 1 file
2. Buka dropdown jenis dokumen
3. ✅ "Link Publikasi" tidak muncul di pilihan
4. ✅ Hanya ada: Sertifikat, Foto Dokumentasi, Surat Keterangan, Dokumen Lainnya

## Files Modified
- `app/Http/Requests/SubmitAppealRequest.php` - Validation logic
- `resources/views/achievements/appeal/create.blade.php` - UI changes

## Backward Compatibility
✅ Kompatibel dengan data lama  
✅ Tidak memerlukan migration  
✅ Link publikasi yang sudah ada tetap berfungsi

## Error Messages

### Validation Errors:
- `appeal_reason.required`: "Alasan banding wajib diisi."
- `appeal_reason.min`: "Alasan banding minimal 50 karakter."
- `publication_link.url`: "Link publikasi harus berupa URL yang valid."
- `additional_documents`: "Anda harus mengupload minimal 1 dokumen tambahan atau memasukkan link publikasi."
- `additional_documents.*.mimes`: "Format file harus PDF, JPG, atau PNG."
- `additional_documents.*.max`: "Ukuran file maksimal 10MB."

## Summary

### Before:
- ❌ Link publikasi muncul di dropdown jenis dokumen (duplikasi)
- ❌ Upload dokumen wajib meskipun sudah ada link publikasi
- ❌ User bingung harus pilih apa

### After:
- ✅ Link publikasi hanya di field tersendiri
- ✅ Upload dokumen opsional jika ada link publikasi
- ✅ User bisa pilih salah satu atau keduanya
- ✅ Info box menjelaskan dengan jelas
- ✅ Validation yang lebih fleksibel
