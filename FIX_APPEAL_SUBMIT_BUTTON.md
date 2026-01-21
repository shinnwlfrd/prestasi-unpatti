# Fix: Tombol Ajukan Banding

## Masalah yang Diperbaiki
1. File upload tidak berfungsi dengan baik karena konflik antara input asli dan hidden input
2. Tidak ada loading state saat submit form
3. Validation rules tidak sesuai dengan form (termasuk sk_resmi, tidak termasuk dokumen_lainnya)
4. Tidak ada publication_link validation
5. User bisa double-submit form

## Solusi

### 1. Perbaikan Alpine.js File Upload
**File**: `resources/views/achievements/appeal/create.blade.php`

**Perubahan**:
- Pisahkan array `files` (untuk display) dan `fileObjects` (untuk actual File objects)
- Perbaiki method `updateFormFiles()` untuk menghapus input lama sebelum membuat yang baru
- Gunakan DataTransfer API dengan benar
- Append hidden input ke form, bukan ke parent element

**Kode Baru**:
```javascript
function documentUploader() {
    return {
        files: [],          // For display (name, size, type)
        fileObjects: [],    // Actual File objects
        
        addFiles(event) {
            const newFiles = Array.from(event.target.files);
            
            newFiles.forEach(file => {
                // Validation...
                
                // Add to display list
                this.files.push({
                    name: file.name,
                    size: file.size,
                    type: file.type
                });
                
                // Store actual file object
                this.fileObjects.push(file);
            });
            
            this.updateFormFiles();
        },
        
        updateFormFiles() {
            // Remove existing hidden inputs
            const existingInputs = document.querySelectorAll('input[name="additional_documents[]"][type="file"]:not(#additional-docs)');
            existingInputs.forEach(input => input.remove());
            
            // Create new hidden input with all files
            if (this.fileObjects.length > 0) {
                const form = document.querySelector('form');
                const dt = new DataTransfer();
                
                this.fileObjects.forEach(file => {
                    dt.items.add(file);
                });
                
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'file';
                hiddenInput.name = 'additional_documents[]';
                hiddenInput.multiple = true;
                hiddenInput.style.display = 'none';
                hiddenInput.files = dt.files;
                
                form.appendChild(hiddenInput);
            }
        }
    }
}
```

### 2. Loading State pada Submit Button
**File**: `resources/views/achievements/appeal/create.blade.php`

**Perubahan**:
- Tambah Alpine.js data `submitting: false`
- Set `submitting = true` saat form di-submit
- Disable button saat submitting
- Tampilkan spinner dan text "Memproses..."

**Kode**:
```html
<form x-data="{ submitting: false }" @submit="submitting = true">
    <!-- Form fields -->
    
    <button type="submit" 
            :disabled="submitting"
            :class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
        <svg x-show="submitting" class="animate-spin">...</svg>
        <span x-text="submitting ? 'Memproses...' : 'Ajukan Banding'"></span>
    </button>
</form>
```

### 3. Perbaikan Validation Rules
**File**: `app/Http/Requests/SubmitAppealRequest.php`

**Perubahan**:
- Tambah validation untuk `publication_link`
- Hapus 'sk_resmi' dari allowed document types
- Tambah 'dokumen_lainnya' ke allowed document types
- Ubah `document_types.*` menjadi nullable (karena optional)
- Tambah custom error messages

**Rules Baru**:
```php
public function rules(): array
{
    return [
        'appeal_reason' => 'required|string|min:50|max:2000',
        'publication_link' => 'nullable|url|max:500',
        'additional_documents' => 'nullable|array',
        'additional_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        'document_types' => 'nullable|array',
        'document_types.*' => 'nullable|string|in:sertifikat,foto_dokumentasi,surat_keterangan,link_publikasi,dokumen_lainnya',
    ];
}
```

## Testing Steps

### Test 1: Upload File Tanpa Dokumen
1. Login sebagai mahasiswa
2. Buka prestasi dengan status "Revisi"
3. Klik "Ajukan Banding"
4. Isi alasan banding (min 50 karakter)
5. **Jangan upload file**
6. Klik "Ajukan Banding"
7. ✅ Form berhasil submit
8. ✅ Redirect ke dashboard dengan success message

### Test 2: Upload Single File
1. Ulangi langkah 1-4
2. Upload 1 file (PDF/JPG/PNG)
3. File muncul di list
4. Pilih jenis dokumen dari dropdown
5. Klik "Ajukan Banding"
6. ✅ Button berubah jadi "Memproses..." dengan spinner
7. ✅ Button disabled
8. ✅ Form berhasil submit
9. ✅ File tersimpan dengan jenis dokumen yang benar

### Test 3: Upload Multiple Files
1. Ulangi langkah 1-4
2. Upload 3 files sekaligus
3. Semua file muncul di list
4. Pilih jenis dokumen untuk setiap file
5. Klik "Ajukan Banding"
6. ✅ Semua file berhasil diupload
7. ✅ Setiap file memiliki jenis dokumen yang sesuai

### Test 4: Remove File
1. Upload beberapa file
2. Klik tombol X pada salah satu file
3. ✅ File hilang dari list
4. Submit form
5. ✅ Hanya file yang tidak dihapus yang diupload

### Test 5: Validation
1. Upload file > 10MB
2. ✅ Alert error muncul
3. Upload file .txt
4. ✅ Alert error muncul
5. Submit tanpa pilih jenis dokumen
6. ✅ HTML5 validation error
7. Isi alasan < 50 karakter
8. ✅ Server validation error

### Test 6: Publication Link
1. Isi link publikasi dengan URL valid
2. Submit form
3. ✅ Link tersimpan sebagai dokumen
4. Isi link publikasi dengan text biasa (bukan URL)
5. ✅ Validation error

### Test 7: Double Submit Prevention
1. Isi form dengan benar
2. Klik "Ajukan Banding"
3. Coba klik lagi sebelum redirect
4. ✅ Button disabled, tidak bisa klik lagi
5. ✅ Hanya 1 banding yang tersimpan

## Files Modified
- `resources/views/achievements/appeal/create.blade.php`
- `app/Http/Requests/SubmitAppealRequest.php`

## Hasil
✅ File upload berfungsi dengan benar  
✅ Loading state mencegah double submit  
✅ Validation rules sesuai dengan form  
✅ User experience lebih baik dengan feedback visual  
✅ Error handling yang lebih baik  
✅ Publication link validation

## Backward Compatibility
✅ Kompatibel dengan data lama  
✅ Tidak memerlukan migration  
✅ Tidak memerlukan perubahan database

## Additional Notes

### Browser Compatibility
- DataTransfer API: Chrome 60+, Firefox 52+, Safari 14+
- Alpine.js: All modern browsers
- File API: All modern browsers

### Known Limitations
- DataTransfer API tidak support di IE11
- Maximum file size: 10MB per file
- Maximum files: Tidak ada limit (tapi perhatikan server upload_max_filesize)

### Server Configuration
Pastikan php.ini memiliki setting:
```ini
upload_max_filesize = 10M
post_max_size = 50M
max_file_uploads = 20
```
