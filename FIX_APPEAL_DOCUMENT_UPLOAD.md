# Fix: Upload Dokumen Tambahan pada Form Ajukan Banding

## Masalah
Form ajukan banding memiliki input untuk upload dokumen tambahan, tetapi:
1. Tidak ada cara untuk menentukan jenis dokumen untuk setiap file yang diupload
2. Checkbox jenis dokumen tidak terhubung dengan file yang diupload
3. User tidak bisa melihat file yang sudah dipilih sebelum submit
4. Tidak ada validasi file di frontend

## Lokasi Masalah
- **File**: `resources/views/achievements/appeal/create.blade.php`
- **Controller**: `app/Http/Controllers/AchievementAppealController.php`

## Solusi

### 1. Update Form View dengan Alpine.js
**File**: `resources/views/achievements/appeal/create.blade.php`

**Perubahan**:
- Tambah Alpine.js component `documentUploader()`
- Setiap file yang diupload ditampilkan dalam list
- Setiap file memiliki dropdown untuk memilih jenis dokumen
- User bisa menghapus file sebelum submit
- Validasi file size (10MB) dan type (PDF, JPG, PNG) di frontend

**Fitur Baru**:
```html
<div x-data="documentUploader()">
    <!-- Upload Area -->
    <input type="file" @change="addFiles($event)" multiple>
    
    <!-- File List -->
    <template x-for="(file, index) in files">
        <div>
            <!-- File info -->
            <p x-text="file.name"></p>
            <p x-text="formatFileSize(file.size)"></p>
            
            <!-- Document Type Selector -->
            <select :name="'document_types[' + index + ']'" required>
                <option value="">Pilih Jenis Dokumen</option>
                <!-- Options -->
            </select>
            
            <!-- Remove button -->
            <button @click="removeFile(index)">Remove</button>
        </div>
    </template>
</div>
```

**JavaScript Functions**:
- `addFiles(event)` - Menambah file dengan validasi
- `removeFile(index)` - Menghapus file dari list
- `updateFormFiles()` - Update hidden input dengan DataTransfer
- `formatFileSize(bytes)` - Format ukuran file

### 2. Update Controller
**File**: `app/Http/Controllers/AchievementAppealController.php`

**Perubahan**:
- Loop through setiap file dan upload satu per satu
- Ambil document type dari array `document_types` berdasarkan index
- Fallback ke 'dokumen_lainnya' jika type tidak ada
- Upload langsung (tidak sebagai draft)
- Error handling untuk setiap file

**Kode Baru**:
```php
if ($request->hasFile('additional_documents')) {
    $files = $request->file('additional_documents');
    $types = $request->input('document_types', []);
    
    foreach ($files as $index => $file) {
        $documentType = $types[$index] ?? 'dokumen_lainnya';
        
        try {
            $this->uploadService->uploadDocument(
                $achievement,
                $file,
                $documentType,
                false // Not draft
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to upload document in appeal: ' . $e->getMessage());
        }
    }
}
```

## Validasi

### Frontend Validation (Alpine.js)
- ✅ File size max 10MB
- ✅ File type: PDF, JPG, PNG only
- ✅ Alert jika file tidak valid
- ✅ Document type required untuk setiap file

### Backend Validation (DocumentUploadService)
- ✅ File size validation
- ✅ MIME type validation
- ✅ Extension validation
- ✅ Error handling dengan try-catch

## User Experience Improvements

### Before:
- ❌ User tidak tahu file apa yang sudah dipilih
- ❌ Tidak bisa menentukan jenis dokumen per file
- ❌ Tidak bisa menghapus file yang salah
- ❌ Tidak ada validasi di frontend

### After:
- ✅ User melihat list file yang dipilih
- ✅ Setiap file memiliki dropdown jenis dokumen
- ✅ User bisa menghapus file dengan tombol X
- ✅ Validasi real-time di frontend
- ✅ File size dan name ditampilkan
- ✅ Visual feedback dengan icon dan styling

## Testing Steps

1. **Login sebagai mahasiswa**
2. **Buka prestasi dengan status "Revisi"**
3. **Klik "Ajukan Banding"**
4. **Upload dokumen tambahan**:
   - Klik area upload
   - Pilih multiple files (PDF, JPG, PNG)
   - File akan muncul di list
5. **Pilih jenis dokumen** untuk setiap file dari dropdown
6. **Test validasi**:
   - Upload file > 10MB → Alert error
   - Upload file .txt → Alert error
   - Submit tanpa pilih jenis dokumen → HTML5 validation error
7. **Hapus file** dengan klik tombol X
8. **Submit form**
9. **Verifikasi** dokumen tersimpan di database dengan jenis yang benar

## Files Modified
- `resources/views/achievements/appeal/create.blade.php`
- `app/Http/Controllers/AchievementAppealController.php`

## Database Schema
Tidak ada perubahan schema - menggunakan struktur yang sudah ada.

## Backward Compatibility
✅ Kompatibel dengan data lama  
✅ Tidak memerlukan migration  
✅ Fallback ke 'dokumen_lainnya' jika type tidak dipilih

## Additional Notes

### Document Types Available
- Sertifikat
- Foto Dokumentasi
- Surat Keterangan
- Link Publikasi
- Dokumen Lainnya

**Excluded**: SK Resmi (hanya bisa diupload oleh Validator/Admin)

### File Constraints
- Max size: 10MB per file
- Allowed formats: PDF, JPG, JPEG, PNG
- Multiple files: Yes
- Required: No (optional)

### Error Handling
- Frontend: Alert untuk user
- Backend: Log warning, continue dengan file lain
- Tidak menghentikan proses jika satu file gagal
