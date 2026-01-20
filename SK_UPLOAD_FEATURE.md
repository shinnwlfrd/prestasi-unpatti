# 📄 Fitur Upload SK Resmi - Dokumentasi

## Status: ✅ COMPLETE

Sistem upload dokumen telah diubah sehingga SK Resmi hanya dapat diupload oleh Validator/Admin.

---

## 🎯 Perubahan Sistem

### Sebelum:
- ❌ Mahasiswa bisa upload semua jenis dokumen termasuk SK Resmi
- ❌ SK Resmi diupload oleh mahasiswa (tidak sesuai prosedur)

### Sesudah:
- ✅ Mahasiswa hanya bisa upload: **Sertifikat** dan **Dokumen Pendukung**
- ✅ Validator/Admin upload: **SK Resmi** saat proses approval
- ✅ SK Resmi otomatis approved (tidak perlu verifikasi lagi)

---

## 📋 Detail Perubahan

### 1. Upload Dokumen Mahasiswa

**File**: `app/Http/Controllers/DocumentUploadController.php`

**Perubahan**:
```php
// Filter document types - mahasiswa tidak bisa upload SK Resmi
$documentTypes = collect(AchievementDocument::DOCUMENT_TYPES)
    ->except([AchievementDocument::TYPE_SK_RESMI])
    ->toArray();
```

**Jenis Dokumen yang Bisa Diupload Mahasiswa**:
1. ✅ Sertifikat
2. ✅ Foto Dokumentasi
3. ✅ Surat Keterangan
4. ✅ Link Publikasi
5. ❌ SK Resmi (TIDAK BISA)

### 2. View Upload Dokumen

**File**: `resources/views/achievements/documents/index.blade.php`

**Perubahan**:
- Ditambahkan info box biru yang menjelaskan bahwa SK Resmi akan diupload oleh Validator/Admin
- Dropdown jenis dokumen tidak menampilkan opsi "SK Resmi"

**Info yang Ditampilkan**:
> "Anda dapat mengupload **Sertifikat** dan **Dokumen Pendukung** lainnya. **SK Resmi** akan diupload oleh Validator/Admin saat proses approval."

### 3. Halaman Validasi Admin/Validator

**File**: `resources/views/admin/achievements/validation/show.blade.php`

**Perubahan**:
- Ditambahkan section upload SK Resmi (opsional) sebelum tombol action
- Upload hanya muncul di halaman validasi admin/validator
- File input accept PDF dengan maksimal 10MB

**UI Upload SK**:
```
┌─────────────────────────────────────────────────────┐
│ ⚠️ Upload SK Resmi *                                │
│ SK Resmi WAJIB diupload untuk approve prestasi ini. │
│                                                     │
│ [Choose File] No file chosen                        │
│ Format: PDF, Maksimal 10MB (WAJIB)                  │
└─────────────────────────────────────────────────────┘
```

### 4. Controller Validasi

**File**: `app/Http/Controllers/AchievementValidationController.php`

**Method Baru**: `uploadSkResmi()`

**Logic**:
1. Validasi file (PDF, max 10MB)
2. Upload ke storage: `achievements/{sa_id}/SK_Resmi_{sa_id}_{timestamp}.pdf`
3. Create document record dengan:
   - `document_type`: `sk_resmi`
   - `status`: `approved` (auto approved)
   - `verified_by`: ID validator/admin
   - `verified_at`: timestamp sekarang

**Kapan SK Diupload**:
- **WAJIB** saat action = `approve`
- Tidak bisa approve tanpa upload SK
- Validasi client-side (JavaScript) dan server-side (PHP)
- Otomatis approved tanpa perlu verifikasi lagi

---

## 🔄 Workflow Baru

### Mahasiswa:
1. Submit prestasi
2. Upload dokumen:
   - ✅ Sertifikat (wajib)
   - ✅ Foto dokumentasi (opsional)
   - ✅ Surat keterangan (opsional)
   - ✅ Link publikasi (opsional)
3. Submit untuk validasi

### Validator/Admin:
1. Review prestasi dan dokumen
2. Verifikasi dokumen yang diupload mahasiswa
3. Saat akan **Approve**:
   - Upload SK Resmi (**WAJIB**)
   - SK otomatis approved
4. Klik tombol "Approve"
   - Jika SK belum diupload → Alert error
   - Jika SK sudah diupload → Approve berhasil

---

## 📊 Status Dokumen

### Dokumen Mahasiswa:
- Status awal: `draft` atau `pending`
- Perlu verifikasi oleh validator
- Bisa: `approved`, `rejected`, `revision`

### SK Resmi (Upload Validator):
- Status: `approved` (otomatis)
- Tidak perlu verifikasi lagi
- Langsung valid

---

## 🧪 Testing

### Test 1: Upload Mahasiswa
1. Login sebagai mahasiswa
2. Buka halaman upload dokumen
3. ✅ Verify: Dropdown tidak ada opsi "SK Resmi"
4. ✅ Verify: Info box biru muncul

### Test 2: Upload Validator
1. Login sebagai validator/admin
2. Buka halaman validasi prestasi
3. ✅ Verify: Section upload SK Resmi muncul
4. Upload SK (PDF)
5. Klik "Approve"
6. ✅ Verify: SK tersimpan dengan status approved

### Test 3: Validasi
1. Cek database `achievement_documents`
2. ✅ Verify: SK Resmi ada dengan:
   - `document_type` = 'sk_resmi'
   - `status` = 'approved'
   - `verified_by` = ID validator
   - `verified_at` = timestamp

---

## 📁 File yang Diubah

1. ✅ `app/Http/Controllers/DocumentUploadController.php`
   - Filter document types untuk mahasiswa

2. ✅ `app/Http/Controllers/AchievementValidationController.php`
   - Method `validate()` - handle upload SK
   - Method `uploadSkResmi()` - logic upload SK

3. ✅ `resources/views/achievements/documents/index.blade.php`
   - Info box SK Resmi
   - Dropdown tanpa SK Resmi

4. ✅ `resources/views/admin/achievements/validation/show.blade.php`
   - Section upload SK Resmi
   - Form enctype multipart

---

## 💡 Catatan Penting

### Keamanan:
- ✅ Hanya validator/admin yang bisa upload SK
- ✅ Validasi file type (PDF only)
- ✅ Validasi file size (max 10MB)
- ✅ File disimpan dengan nama unik (timestamp)

### Opsional:
- Upload SK adalah **WAJIB** untuk approve
- Validator TIDAK bisa approve tanpa upload SK
- Validasi client-side dan server-side

### Auto Approved:
- SK yang diupload validator otomatis approved
- Tidak perlu verifikasi lagi
- Langsung valid dan bisa dilihat mahasiswa

---

## 🚀 Deployment

```bash
# Clear cache
php artisan view:clear
php artisan cache:clear

# Test upload
# 1. Login sebagai mahasiswa - test upload dokumen
# 2. Login sebagai validator - test upload SK
```

---

## 📞 Support

Jika ada masalah:
1. Check permission folder `storage/app/public/achievements`
2. Check symbolic link: `php artisan storage:link`
3. Check file size limit di `php.ini`: `upload_max_filesize` dan `post_max_size`

---

**Tanggal**: 20 Januari 2026  
**Developer**: Kiro AI Assistant  
**Status**: ✅ COMPLETE & TESTED
