# Pemisahan Form Upload Dokumen dan Form Approval

## Status: ✅ SUDAH BENAR

Sistem sudah memiliki 2 form yang terpisah dengan fungsi berbeda:

---

## 1. Form Upload Dokumen Tambahan

**Lokasi**: `resources/views/achievements/documents/index.blade.php`

**Route**: `/achievements/{achievement}/documents`

**Akses**: 
- Mahasiswa (untuk prestasi sendiri)
- Validator (untuk semua prestasi)
- Admin (untuk semua prestasi)

**Fungsi**:
- Upload dokumen pendukung (Sertifikat, Foto, Surat Keterangan, dll)
- Mahasiswa TIDAK bisa upload SK Resmi
- Validator/Admin BISA upload SK Resmi
- Drag & drop file
- Upload multiple files
- Tambah link publikasi

**Tombol yang Ada**:
- ✅ "Upload Dokumen" - untuk upload file
- ✅ "Batal" - kembali ke dashboard
- ❌ TIDAK ADA tombol "Approve"

**Karakteristik**:
- Form sederhana untuk upload file
- Tidak ada validasi checklist
- Tidak ada keputusan approve/reject
- Hanya untuk menambah dokumen pendukung

---

## 2. Form Approval/Validasi

**Lokasi**: `resources/views/admin/achievements/validation/show.blade.php`

**Route**: `/admin/achievements/validation/{achievement}`

**Akses**:
- Admin only
- Validator (via route validator.achievements.show)

**Fungsi**:
- Validasi prestasi mahasiswa
- Checklist validasi (6 item)
- Upload SK Resmi (WAJIB untuk approve)
- Approve/Reject/Minta Revisi prestasi

**Tombol yang Ada**:
- ✅ "Approve" - setujui prestasi (WAJIB upload SK)
- ✅ "Reject" - tolak prestasi
- ✅ "Minta Revisi" - minta dokumen tambahan

**Karakteristik**:
- Form kompleks dengan checklist
- Upload SK Resmi WAJIB untuk approve
- Validasi 3 layer (HTML5, JavaScript, PHP)
- Catatan keseluruhan
- Riwayat validasi
- Progress bar validasi

---

## Perbedaan Utama

| Aspek | Upload Dokumen Tambahan | Form Approval |
|-------|------------------------|---------------|
| **Tujuan** | Menambah dokumen pendukung | Memvalidasi dan approve prestasi |
| **Tombol Approve** | ❌ Tidak ada | ✅ Ada (dengan upload SK wajib) |
| **Upload SK Resmi** | ✅ Opsional (validator/admin) | ✅ WAJIB (saat approve) |
| **Checklist** | ❌ Tidak ada | ✅ Ada (6 item) |
| **Keputusan** | ❌ Tidak ada | ✅ Approve/Reject/Revisi |
| **Akses** | Mahasiswa + Validator + Admin | Admin + Validator only |
| **Lokasi** | `/achievements/{id}/documents` | `/admin/achievements/validation/{id}` |

---

## Alur Kerja

### Alur Mahasiswa
```
1. Login sebagai mahasiswa
2. Submit prestasi
3. Upload dokumen tambahan (tanpa SK Resmi)
   → Halaman: /achievements/{id}/documents
   → Tombol: "Upload Dokumen"
4. Tunggu validasi dari validator/admin
```

### Alur Validator/Admin
```
1. Login sebagai validator/admin
2. Lihat prestasi pending di dashboard
3. Bisa upload dokumen tambahan (termasuk SK Resmi)
   → Halaman: /achievements/{id}/documents
   → Tombol: "Upload Dokumen"
4. Validasi prestasi
   → Halaman: /admin/achievements/validation/{id}
   → Checklist validasi
   → Upload SK Resmi (WAJIB)
   → Tombol: "Approve" / "Reject" / "Minta Revisi"
```

---

## Validasi SK Resmi

### Di Form Upload Dokumen Tambahan
- SK Resmi bisa diupload oleh validator/admin
- Bersifat OPSIONAL
- Tidak ada tombol approve
- Hanya menambahkan dokumen ke database

### Di Form Approval
- SK Resmi WAJIB diupload saat klik "Approve"
- Validasi 3 layer:
  1. HTML5: `required` attribute
  2. JavaScript: Alert jika file kosong
  3. PHP: Server-side validation
- Jika tidak upload SK → Error dan tidak bisa approve

---

## Kode Validasi SK di Form Approval

### HTML
```html
<input type="file" name="sk_resmi" accept=".pdf" required 
    class="w-full text-sm text-gray-900 dark:text-white border border-red-300">
```

### JavaScript (pada tombol Approve)
```javascript
@click="
    selectedAction = 'approve';
    const skFile = document.querySelector('input[name=sk_resmi]');
    if (!skFile.files.length) {
        alert('SK Resmi wajib diupload untuk approve prestasi!');
        skFile.focus();
        return;
    }
    $el.closest('form').submit();
"
```

### PHP (di Controller)
```php
$request->validate([
    'sk_resmi' => 'required|file|mimes:pdf|max:10240',
], [
    'sk_resmi.required' => 'SK Resmi wajib diupload untuk approve prestasi.',
]);
```

---

## Kesimpulan

✅ **Form sudah terpisah dengan benar**

1. **Form Upload Dokumen Tambahan**
   - Untuk upload dokumen pendukung
   - Tidak ada tombol approve
   - Bisa diakses mahasiswa, validator, admin

2. **Form Approval**
   - Untuk validasi dan approve prestasi
   - Ada tombol approve dengan upload SK WAJIB
   - Hanya bisa diakses validator dan admin

**Tidak perlu perubahan** - sistem sudah bekerja sesuai requirement!

---

## Testing

### Test 1: Upload Dokumen Tambahan
1. Login sebagai validator
2. Klik "Upload Dokumen" (hijau) di dashboard
3. ✅ Tidak ada tombol "Approve"
4. ✅ Hanya ada tombol "Upload Dokumen" dan "Batal"

### Test 2: Form Approval
1. Login sebagai admin/validator
2. Klik "Detail & Validasi" (ungu) di dashboard
3. ✅ Ada tombol "Approve", "Reject", "Minta Revisi"
4. ✅ Ada upload SK Resmi dengan label WAJIB
5. Klik "Approve" tanpa upload SK
6. ✅ Muncul alert: "SK Resmi wajib diupload untuk approve prestasi!"

---

**Tanggal**: 20 Januari 2026
**Status**: ✅ VERIFIED - Form sudah terpisah dengan benar
