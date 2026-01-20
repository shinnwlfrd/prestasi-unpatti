# Perbaikan Tampilan Mahasiswa

## Tanggal: 20 Januari 2026

### Perubahan yang Dilakukan

#### 1. ✅ Alasan Penolakan/Revisi Muncul di Dashboard Mahasiswa

**Masalah:** Mahasiswa tidak bisa melihat alasan kenapa prestasi mereka ditolak atau diminta revisi.

**Solusi:**
- Menambahkan query untuk mengambil validation log terbaru
- Menampilkan alasan di bawah status badge di tabel dashboard
- Alasan ditampilkan dengan format italic dan dibatasi 50 karakter
- Jika lebih panjang, akan ada "..." untuk indikasi ada lebih banyak teks

**File yang Diubah:**
- `resources/views/student/dashboard.blade.php`

**Implementasi:**
```php
// Get latest validation log for rejection/revision reason
$latestLog = $item->validationLogs()
    ->whereIn('new_status', ['Ditolak', 'Revisi'])
    ->latest('validated_at')
    ->first();
```

**Tampilan:**
```
Status: [Ditolak]
"Dokumen sertifikat tidak jelas, mohon upload ulang..."
```

---

#### 2. ✅ Status Validasi Diperbaiki

**Masalah:** Status menggunakan nilai English ('approved', 'rejected') padahal database menggunakan Indonesian.

**Solusi:**
- Update status config untuk menggunakan nilai Indonesian yang sesuai database:
  - 'Disetujui' (bukan 'approved')
  - 'Ditolak' (bukan 'rejected')
  - 'Menunggu' (bukan 'pending')
  - 'Revisi' (bukan 'need_revision')

**File yang Diubah:**
- `resources/views/student/dashboard.blade.php`

---

#### 3. ✅ SK Resmi Dihilangkan dari Jenis Dokumen Tambahan

**Masalah:** SK Resmi masih muncul sebagai pilihan saat mahasiswa mengajukan banding.

**Solusi:**
- Menambahkan filter `@if($type !== 'sk_resmi')` di loop document types
- Menambahkan catatan bahwa SK Resmi hanya bisa diupload oleh Validator/Admin

**File yang Diubah:**
- `resources/views/achievements/appeal/create.blade.php`

**Tampilan:**
```
Jenis Dokumen Tambahan:
☐ Sertifikat
☐ Foto Dokumentasi
☐ Surat Keterangan
☐ Link Publikasi
☐ Dokumen Lainnya

Catatan: SK Resmi hanya dapat diupload oleh Validator/Admin saat proses approval.
```

---

#### 4. ✅ Field Link Publikasi Ditambahkan di Form Banding

**Masalah:** Mahasiswa tidak bisa menambahkan link publikasi saat mengajukan banding.

**Solusi:**
- Menambahkan field `publication_link` di form banding
- Field ini opsional dan menerima URL
- Link akan disimpan di database dan ditambahkan sebagai dokumen eksternal

**File yang Diubah:**
- `resources/views/achievements/appeal/create.blade.php`
- `app/Http/Controllers/AchievementAppealController.php`
- `app/Models/AchievementAppeal.php`
- `database/migrations/2026_01_20_045551_add_publication_link_to_achievement_appeals_table.php`

**Database Changes:**
```sql
ALTER TABLE achievement_appeals 
ADD COLUMN publication_link TEXT NULL 
AFTER appeal_reason;
```

**Controller Logic:**
```php
// Add publication link as document if provided
if ($request->filled('publication_link')) {
    $this->uploadService->addExternalLink(
        $achievement,
        $request->publication_link,
        'Link Publikasi (Banding)',
        false // Not draft, submit directly
    );
}
```

**Form Field:**
```html
<input 
    type="url" 
    name="publication_link" 
    placeholder="https://contoh.com/publikasi-prestasi"
>
```

---

#### 5. ✅ Alasan Penolakan/Revisi di Halaman Banding

**Masalah:** Halaman banding hanya menampilkan alasan penolakan, tidak menampilkan alasan revisi.

**Solusi:**
- Update query untuk mencari log dengan status 'Ditolak' atau 'Revisi'
- Menampilkan judul yang sesuai dengan status (Penolakan vs Revisi)
- Menampilkan teks yang sesuai dengan aksi (Ditolak vs Diminta revisi)

**File yang Diubah:**
- `resources/views/achievements/appeal/create.blade.php`

---

#### 6. ✅ Logika Banding Diubah: Revisi Bisa Banding, Ditolak Tidak Bisa

**Masalah:** Sebelumnya hanya prestasi yang ditolak yang bisa banding.

**Solusi Baru:**
- **Status REVISI** → BISA mengajukan banding (untuk minta langsung disetujui)
- **Status DITOLAK** → TIDAK BISA mengajukan banding (final)

**Alasan:**
- Prestasi yang diminta revisi masih ada harapan untuk diperbaiki
- Mahasiswa bisa mengajukan banding jika merasa sudah memenuhi persyaratan
- Prestasi yang ditolak adalah keputusan final dan tidak bisa dibanding

**File yang Diubah:**
- `app/Models/StudentAchievement.php` - Method `canBeAppealed()`
- `resources/views/student/dashboard.blade.php` - Tombol banding hanya muncul untuk status 'Revisi'
- `resources/views/achievements/appeal/create.blade.php` - UI disesuaikan untuk revisi

**Implementasi:**
```php
// StudentAchievement.php
public function canBeAppealed(): bool
{
    return $this->validation_status === self::STATUS_NEED_REVISION 
        && !$this->appeals()->where('status', 'pending')->exists();
}
```

**Dashboard:**
```blade
@if($item->validation_status === 'Revisi')
    <a href="{{ route('achievements.appeal.create', $item) }}" 
        title="Ajukan Banding">
        <!-- Icon Banding -->
    </a>
@endif
```

**UI Changes:**
- Header icon berubah dari merah (warning) ke biru (info)
- Info box menjelaskan bahwa ini untuk status revisi
- Placeholder textarea disesuaikan untuk konteks revisi
- Warna box alasan berubah dari merah ke kuning (warning)

---

## Testing

### 1. Test Alasan Muncul di Dashboard
1. Login sebagai mahasiswa
2. Lihat dashboard
3. Prestasi yang ditolak/revisi harus menampilkan alasan di bawah status badge

### 2. Test Status Validasi
1. Login sebagai mahasiswa
2. Lihat dashboard
3. Status harus menampilkan: Menunggu, Disetujui, Ditolak, atau Perlu Revisi

### 3. Test SK Resmi Tidak Muncul
1. Login sebagai mahasiswa
2. Klik "Ajukan Banding" pada prestasi yang diminta revisi
3. Di bagian "Jenis Dokumen Tambahan", SK Resmi tidak boleh ada

### 4. Test Link Publikasi
1. Login sebagai mahasiswa
2. Klik "Ajukan Banding" pada prestasi yang diminta revisi
3. Isi form banding
4. Masukkan link publikasi (contoh: https://instagram.com/p/xxx)
5. Submit banding
6. Link harus tersimpan dan muncul sebagai dokumen

### 5. Test Alasan di Halaman Banding
1. Login sebagai mahasiswa
2. Klik "Ajukan Banding" pada prestasi yang diminta revisi
3. Harus muncul box kuning dengan alasan permintaan revisi
4. Harus menampilkan nama validator dan tanggal

### 6. Test Logika Banding Baru
1. Login sebagai mahasiswa
2. Lihat prestasi dengan status **Revisi** → Harus ada tombol banding
3. Lihat prestasi dengan status **Ditolak** → TIDAK ada tombol banding
4. Klik banding pada prestasi revisi → Form harus terbuka
5. Submit banding → Prestasi kembali ke status "Menunggu"

---

## Cara Menjalankan

```bash
# Clear cache
php artisan view:clear

# Run migration (jika belum)
php artisan migrate

# Test di browser
# Login sebagai mahasiswa dan cek dashboard
```

---

## Summary

Semua 6 perbaikan telah diimplementasikan:

1. ✅ **Alasan muncul di mahasiswa** - Ditampilkan di bawah status badge
2. ✅ **Status muncul** - Diperbaiki untuk menggunakan nilai Indonesian
3. ✅ **SK Resmi dihilangkan** - Tidak muncul di pilihan dokumen banding
4. ✅ **Link publikasi ditambahkan** - Field baru di form banding
5. ✅ **Alasan di halaman banding** - Menampilkan alasan revisi/penolakan
6. ✅ **Logika banding diubah** - Revisi bisa banding, Ditolak tidak bisa

## Logika Banding Baru

| Status | Bisa Banding? | Alasan |
|--------|---------------|--------|
| Menunggu | ❌ | Masih dalam proses validasi |
| Disetujui | ❌ | Sudah disetujui |
| **Revisi** | ✅ | Bisa banding untuk minta langsung disetujui |
| **Ditolak** | ❌ | Keputusan final, tidak bisa dibanding |

Mahasiswa sekarang dapat:
- Melihat alasan kenapa prestasi ditolak/revisi langsung di dashboard
- Melihat status validasi dengan benar
- Mengajukan banding HANYA untuk prestasi yang diminta revisi
- Menambahkan link publikasi saat banding
- Prestasi yang ditolak tidak bisa dibanding (final)

---

**Status:** COMPLETE ✅
**Tested:** Ready for testing
**Migration:** Completed
**Logic:** Revisi = Bisa Banding | Ditolak = Tidak Bisa Banding
