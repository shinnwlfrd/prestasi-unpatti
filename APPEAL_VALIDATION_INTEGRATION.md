# Integrasi Sistem Banding ke Validasi Prestasi

## Overview
Sistem banding telah diintegrasikan penuh ke dalam sistem validasi prestasi. Sekarang admin/validator dapat melihat dan memproses semua prestasi (termasuk banding) dalam satu halaman dengan sistem tab yang terorganisir.

## Perubahan Database

### Migration: `add_appeal_fields_to_student_achievements_table`
Menambahkan kolom baru ke tabel `student_achievements`:
- `is_appeal` (boolean): Menandai apakah prestasi adalah banding
- `appeal_reason` (text): Alasan pengajuan banding
- `publication_link` (text): Link publikasi tambahan untuk banding
- `appealed_at` (timestamp): Waktu pengajuan banding

## Fitur Utama

### 1. Sistem Tab di Halaman Validasi

**Tab yang Tersedia:**
- **Pending** - Prestasi baru yang menunggu review (bukan banding)
- **Banding** - Prestasi yang diajukan banding oleh mahasiswa
- **Revisi** - Prestasi yang perlu revisi
- **History** - Prestasi yang sudah disetujui/ditolak

### 2. Badge "BANDING"
- Prestasi banding ditandai dengan badge orange "BANDING"
- Muncul di:
  - Daftar prestasi (kolom nama lomba)
  - Halaman detail prestasi (header)

### 3. Info Box Banding
Di halaman detail prestasi, jika prestasi adalah banding, akan muncul info box orange yang menampilkan:
- Alasan banding
- Link publikasi (jika ada)
- Tanggal pengajuan banding

### 4. Statistik Terintegrasi
Dashboard menampilkan:
- Total Prestasi
- Pending Review
- **Banding** (baru)
- Approved
- Approval Rate

## Workflow Banding

### Untuk Mahasiswa:
1. Prestasi ditolak
2. Mahasiswa mengajukan banding melalui form banding
3. Mengisi alasan banding dan upload dokumen tambahan
4. Prestasi otomatis kembali ke status "Pending" dengan flag `is_appeal = true`

### Untuk Admin/Validator:
1. Buka halaman Validasi Prestasi
2. Klik tab "Banding" untuk melihat semua prestasi banding
3. Prestasi banding ditandai dengan badge orange
4. Review prestasi seperti biasa (approve/reject/revision)
5. Lihat alasan banding di info box orange

## Perubahan Kode

### Model: StudentAchievement
```php
// Kolom baru di fillable
'is_appeal',
'appeal_reason',
'publication_link',
'appealed_at',

// Cast baru
'appealed_at' => 'datetime',
'is_appeal' => 'boolean',
```

### Controller: AchievementValidationController
```php
// Tab filtering di method index()
switch ($tab) {
    case 'appeal':
        $query->where('is_appeal', true)
              ->where('validation_status', StudentAchievement::STATUS_PENDING);
        break;
    case 'revision':
        $query->where('validation_status', StudentAchievement::STATUS_NEED_REVISION);
        break;
    case 'history':
        $query->whereIn('validation_status', [STATUS_APPROVED, STATUS_REJECTED]);
        break;
    case 'pending':
    default:
        $query->where('validation_status', StudentAchievement::STATUS_PENDING)
              ->where('is_appeal', false);
        break;
}

// Tambah statistik banding
$statistics['appeals'] = StudentAchievement::where('is_appeal', true)
    ->where('validation_status', StudentAchievement::STATUS_PENDING)
    ->count();
```

### Controller: AchievementAppealController
```php
// Update achievement dengan data banding (bukan create appeal record)
$achievement->update([
    'is_appeal' => true,
    'appeal_reason' => $request->appeal_reason,
    'publication_link' => $request->publication_link,
    'appealed_at' => now(),
    'validation_status' => StudentAchievement::STATUS_PENDING,
]);
```

## Keuntungan Integrasi

### 1. Efisiensi
- ✅ Satu halaman untuk semua review
- ✅ Tidak perlu pindah-pindah menu
- ✅ Workflow lebih sederhana

### 2. Konsistensi
- ✅ Semua prestasi dalam satu sistem
- ✅ History lengkap dalam satu tempat
- ✅ Statistik terpadu

### 3. User Experience
- ✅ Interface lebih intuitif
- ✅ Badge visual yang jelas
- ✅ Info banding mudah diakses

### 4. Maintenance
- ✅ Tidak perlu maintain 2 sistem terpisah
- ✅ Query lebih efisien
- ✅ Kode lebih clean

## Backward Compatibility

### Tabel achievement_appeals
- Tabel lama masih ada untuk data historis
- Data baru disimpan langsung di student_achievements
- Bisa migrasi data lama jika diperlukan

### Routes
- Route banding lama masih berfungsi
- Form banding tetap sama untuk mahasiswa
- Hanya backend yang berubah

## Testing

### Test Case 1: Mahasiswa Ajukan Banding
1. Login sebagai mahasiswa
2. Pilih prestasi yang ditolak
3. Klik "Ajukan Banding"
4. Isi form dan submit
5. ✅ Prestasi muncul di tab "Banding" dengan badge orange

### Test Case 2: Admin Review Banding
1. Login sebagai admin
2. Buka Validasi Prestasi
3. Klik tab "Banding"
4. ✅ Lihat prestasi dengan badge "BANDING"
5. Klik detail prestasi
6. ✅ Lihat info box orange dengan alasan banding
7. Review dan approve/reject
8. ✅ Prestasi pindah ke history

### Test Case 3: Filter dan Search
1. Buka halaman validasi
2. Switch antar tab
3. ✅ Data ter-filter dengan benar
4. Gunakan search
5. ✅ Search berfungsi di semua tab

## Migration Path

### Untuk Data Existing
Jika ada data di tabel `achievement_appeals` yang perlu dimigrasi:

```php
// Migration script (opsional)
$appeals = AchievementAppeal::where('status', 'pending')->get();
foreach ($appeals as $appeal) {
    StudentAchievement::where('sa_id', $appeal->sa_id)->update([
        'is_appeal' => true,
        'appeal_reason' => $appeal->appeal_reason,
        'publication_link' => $appeal->publication_link,
        'appealed_at' => $appeal->created_at,
    ]);
}
```

## Future Enhancements

### Possible Improvements:
1. **Auto-notification** - Email/notif saat banding diajukan
2. **Deadline banding** - Batas waktu pengajuan banding
3. **Banding history** - Track berapa kali prestasi di-banding
4. **Bulk review** - Review multiple banding sekaligus
5. **Analytics** - Statistik banding (approval rate, common reasons, etc.)

## Kesimpulan

Integrasi sistem banding ke validasi prestasi berhasil dilakukan dengan:
- ✅ Minimal perubahan database (4 kolom baru)
- ✅ Backward compatible dengan sistem lama
- ✅ UI/UX yang lebih baik dengan sistem tab
- ✅ Workflow yang lebih efisien
- ✅ Maintenance yang lebih mudah

Sistem sekarang lebih terintegrasi, efisien, dan user-friendly untuk admin/validator dalam memproses semua jenis review prestasi.
