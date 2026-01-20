# Changelog: Penghapusan Fitur Kredibilitas

## Tanggal: 20 Januari 2026

### 🎯 Tujuan
Menghapus semua fitur kredibilitas dari sistem prestasi mahasiswa dan menyamakan desain tombol logout.

---

## ✅ Perubahan yang Dilakukan

### 1. Database & Migration
- ✅ **Dihapus**: `database/migrations/2026_01_09_000003_add_credibility_fields_to_student_achievements_table.php`
- ✅ **Dibuat**: `database/migrations/2026_01_20_012237_remove_credibility_fields_from_student_achievements_table.php`
  - Menghapus kolom: `credibility_score`, `requires_extra_review`, `approval_level`, `current_approver_id`
- ✅ **Dijalankan**: Migration berhasil dieksekusi

### 2. Backend (PHP)

#### Services
- ✅ **app/Services/AchievementApprovalService.php**
  - Dihapus dependency `CredibilityService`
  - Dihapus constructor injection
  - Disederhanakan method `submitForReview()` - tidak lagi menghitung kredibilitas
  - **DIPERBAIKI**: Error SQL `JULIANDAY` diganti dengan `DATEDIFF` untuk MySQL

#### Seeders
- ✅ **database/seeders/AchievementValidationSeeder.php**
  - Dihapus variabel `$credibilityScore`
  - Dihapus field `credibility_score`, `requires_extra_review`, `approval_level` dari data seeder

### 3. Frontend (Blade Views)

#### Validator Dashboard
- ✅ **resources/views/validator/dashboard.blade.php**
  - Dihapus stat card "Perlu Review Ekstra"
  - Dihapus stat card "Kredibilitas Rendah"
  - Dihapus kolom "Kredibilitas" dari tabel
  - Diubah grid dari 4 kolom menjadi 3 kolom
  - Diubah colspan dari 6 menjadi 5

#### Student Dashboard
- ✅ **resources/views/student/dashboard.blade.php**
  - Dihapus kolom "Kredibilitas" dari tabel
  - Dihapus variabel `$score` dan `$scoreColor` dari PHP
  - Dihapus progress bar kredibilitas

#### Submit Form
- ✅ **resources/views/student/submit.blade.php**
  - Diubah judul dari "Tips untuk Kredibilitas Tinggi" menjadi "Tips Pengajuan Prestasi"
  - Diperbarui isi tips menjadi lebih umum

#### Documents Upload
- ✅ **resources/views/achievements/documents/index.blade.php**
  - Dihapus section "Skor Kredibilitas" lengkap dengan card-nya
  - Dihapus variabel `credibilityScore` dari Alpine.js data
  - Dihapus computed property `credibilityColor`

### 4. Layout & UI

#### Tombol Logout
- ✅ **resources/views/layouts/admin.blade.php**
  - Disamakan desain dengan `app.blade.php`
  - Menggunakan icon SVG yang sama
  - Menggunakan style rounded-xl dengan hover effect
  - Menambahkan tooltip

---

## 🔧 Perbaikan Bug

### SQL Error: JULIANDAY
**Error**: `SQLSTATE[42000]: Syntax error or access violation: 1305 FUNCTION prestasiunpatti.JULIANDAY does not exist`

**Penyebab**: Fungsi `JULIANDAY` adalah fungsi SQLite, tidak tersedia di MySQL

**Solusi**: Diganti dengan `DATEDIFF` yang merupakan fungsi MySQL standar
```php
// Sebelum
->selectRaw('AVG(JULIANDAY(validation_logs.validated_at) - JULIANDAY(student_achievements.submitted_at)) as avg_days')

// Sesudah
->selectRaw('AVG(DATEDIFF(validation_logs.validated_at, student_achievements.submitted_at)) as avg_days')
```

---

## 📊 Dampak Perubahan

### Kolom Database yang Dihapus
- `student_achievements.credibility_score` (DECIMAL)
- `student_achievements.requires_extra_review` (BOOLEAN)
- `student_achievements.approval_level` (STRING)
- `student_achievements.current_approver_id` (FOREIGN KEY)

### Fitur yang Dihapus
- ❌ Perhitungan skor kredibilitas otomatis
- ❌ Indikator "Perlu Review Ekstra"
- ❌ Multi-level approval system
- ❌ Progress bar kredibilitas di dashboard
- ❌ Stat cards kredibilitas di validator dashboard

### Fitur yang Tetap Ada
- ✅ Upload dokumen
- ✅ Validasi dokumen oleh validator
- ✅ Status approval (Menunggu, Disetujui, Ditolak, Revisi)
- ✅ Riwayat validasi
- ✅ Notifikasi status

---

## 🧪 Testing

### Checklist Testing
- [ ] Login sebagai admin - tombol logout berfungsi dengan desain baru
- [ ] Login sebagai validator - tombol logout berfungsi dengan desain baru
- [ ] Dashboard validator - tidak ada error, stat cards tampil 3 kolom
- [ ] Dashboard student - tidak ada error, tabel tanpa kolom kredibilitas
- [ ] Submit prestasi - form berfungsi tanpa kredibilitas
- [ ] Upload dokumen - tidak ada section kredibilitas
- [ ] Validasi prestasi - proses approval berjalan normal

### Command Testing
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Verify migration
php artisan migrate:status

# Test database
php artisan tinker
>>> \App\Models\StudentAchievement::first()
```

---

## 📝 Catatan

1. **Backup Database**: Pastikan backup database sebelum menjalankan migration
2. **Data Lama**: Data kredibilitas yang sudah ada akan hilang setelah migration
3. **Service Kredibilitas**: File `app/Services/CredibilityService.php` masih ada tapi tidak digunakan (bisa dihapus jika diperlukan)
4. **Compiled Views**: Cache views sudah dibersihkan

---

## 🚀 Deployment

### Langkah Deployment
1. Pull perubahan dari repository
2. Backup database
3. Jalankan migration:
   ```bash
   php artisan migrate
   ```
4. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   ```
5. Test semua fitur

---

## 👥 Tim
- Developer: Kiro AI Assistant
- Tanggal: 20 Januari 2026
- Status: ✅ Selesai
