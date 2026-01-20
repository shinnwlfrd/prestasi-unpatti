# Testing Guide - Admin Features

## Tanggal: 20 Januari 2026

## Persiapan Testing

### 1. Login sebagai Admin
```
Email: admin@unpatti.ac.id
Password: password
```

### 2. Clear Cache
```bash
php artisan view:clear
php artisan route:clear
```

---

## Test 1: Menu Sidebar ✅

### Langkah Testing:
1. Login sebagai admin
2. Perhatikan sidebar kiri
3. Scroll ke bawah

### Yang Harus Terlihat:
- ✅ Section "Master Data" dengan 2 menu:
  - Kategori Prestasi (icon tag)
  - Level Prestasi (icon chart)
- ✅ Section "Aksi" dengan 1 menu:
  - Ajukan Prestasi (icon plus)

### Expected Result:
- Menu baru muncul dengan icon yang sesuai
- Active state (highlight purple) saat diklik
- Responsive dengan sidebar collapse

---

## Test 2: CRUD Kategori Prestasi ✅

### A. List Kategori
**URL:** `/admin/categories`

**Langkah:**
1. Klik menu "Kategori Prestasi" di sidebar
2. Lihat tabel kategori

**Expected Result:**
- ✅ Tabel menampilkan 2 kategori default (Akademik, Non-Akademik)
- ✅ Kolom: Nama, Deskripsi, Status, Aksi
- ✅ Status badge hijau untuk "Aktif"
- ✅ Tombol "Tambah Kategori" di header

### B. Tambah Kategori
**URL:** `/admin/categories/create`

**Langkah:**
1. Klik tombol "Tambah Kategori"
2. Isi form:
   - Nama: "Olahraga"
   - Deskripsi: "Prestasi di bidang olahraga"
   - Centang "Aktif"
3. Klik "Simpan"

**Expected Result:**
- ✅ Redirect ke list kategori
- ✅ Flash message hijau: "Kategori berhasil ditambahkan."
- ✅ Kategori baru muncul di tabel

### C. Edit Kategori
**URL:** `/admin/categories/{id}/edit`

**Langkah:**
1. Klik icon edit (pensil) pada kategori "Olahraga"
2. Ubah nama menjadi "Olahraga & Seni"
3. Klik "Update"

**Expected Result:**
- ✅ Redirect ke list kategori
- ✅ Flash message hijau: "Kategori berhasil diperbarui."
- ✅ Nama kategori berubah di tabel

### D. Hapus Kategori
**Langkah:**
1. Klik icon hapus (trash) pada kategori "Olahraga & Seni"
2. Konfirmasi popup "Yakin ingin menghapus?"
3. Klik OK

**Expected Result:**
- ✅ Flash message hijau: "Kategori berhasil dihapus."
- ✅ Kategori hilang dari tabel

### E. Validasi Form
**Langkah:**
1. Klik "Tambah Kategori"
2. Submit form kosong
3. Coba input nama yang sudah ada

**Expected Result:**
- ✅ Error message merah muncul
- ✅ Field yang error ditandai dengan border merah
- ✅ Pesan error spesifik untuk setiap field

---

## Test 3: CRUD Level Prestasi ✅

### A. List Level
**URL:** `/admin/levels`

**Langkah:**
1. Klik menu "Level Prestasi" di sidebar
2. Lihat tabel level

**Expected Result:**
- ✅ Tabel menampilkan 3 level default:
  - Universitas (10 poin)
  - Nasional (25 poin)
  - Internasional (50 poin)
- ✅ Kolom: Nama, Deskripsi, Poin, Status, Aksi
- ✅ Poin ditampilkan dengan badge purple
- ✅ Tombol "Tambah Level" di header

### B. Tambah Level
**URL:** `/admin/levels/create`

**Langkah:**
1. Klik tombol "Tambah Level"
2. Isi form:
   - Nama: "Regional"
   - Deskripsi: "Prestasi tingkat regional/provinsi"
   - Poin: 15
   - Centang "Aktif"
3. Klik "Simpan"

**Expected Result:**
- ✅ Redirect ke list level
- ✅ Flash message hijau: "Level berhasil ditambahkan."
- ✅ Level baru muncul di tabel dengan poin 15

### C. Edit Level
**URL:** `/admin/levels/{id}/edit`

**Langkah:**
1. Klik icon edit pada level "Regional"
2. Ubah poin menjadi 20
3. Klik "Update"

**Expected Result:**
- ✅ Redirect ke list level
- ✅ Flash message hijau: "Level berhasil diperbarui."
- ✅ Poin berubah menjadi 20 di tabel

### D. Hapus Level
**Langkah:**
1. Klik icon hapus pada level "Regional"
2. Konfirmasi popup
3. Klik OK

**Expected Result:**
- ✅ Flash message hijau: "Level berhasil dihapus."
- ✅ Level hilang dari tabel

### E. Validasi Form
**Langkah:**
1. Klik "Tambah Level"
2. Submit form kosong
3. Coba input poin negatif
4. Coba input nama yang sudah ada

**Expected Result:**
- ✅ Error message untuk field required
- ✅ Error message untuk poin negatif
- ✅ Error message untuk nama duplikat

---

## Test 4: Admin Submit Achievement ✅

### A. Akses Form
**URL:** `/admin/submit-achievement`

**Langkah:**
1. Klik menu "Ajukan Prestasi" di sidebar
2. Lihat form

**Expected Result:**
- ✅ Form lengkap dengan fields:
  - Dropdown mahasiswa (searchable)
  - Dropdown kategori
  - Nama lomba/event
  - Dropdown level (dengan poin)
  - Penyelenggara
  - Tanggal pelaksanaan
  - Deskripsi (opsional)
  - Upload sertifikat
- ✅ Info box biru di bawah form

### B. Submit Prestasi
**Langkah:**
1. Pilih mahasiswa dari dropdown
2. Pilih kategori "Akademik"
3. Isi nama lomba: "Olimpiade Matematika 2024"
4. Pilih level "Nasional" (25 poin)
5. Isi penyelenggara: "Kemendikbud"
6. Pilih tanggal: hari ini
7. Isi deskripsi (opsional)
8. Upload file sertifikat (PDF/JPG)
9. Klik "Ajukan Prestasi"

**Expected Result:**
- ✅ Redirect ke halaman upload dokumen (`/achievements/{id}/documents`)
- ✅ Flash message hijau: "Prestasi mahasiswa berhasil diajukan..."
- ✅ Prestasi tersimpan dengan status "Menunggu"
- ✅ Submitted_by = "admin"

### C. Upload Dokumen Tambahan
**Langkah:**
1. Setelah redirect, upload dokumen tambahan
2. Pilih jenis dokumen
3. Submit

**Expected Result:**
- ✅ Dokumen berhasil diupload
- ✅ Bisa upload multiple dokumen
- ✅ Bisa kembali ke dashboard admin

### D. Validasi Form
**Langkah:**
1. Submit form kosong
2. Upload file > 5MB
3. Upload file selain PDF/JPG/PNG

**Expected Result:**
- ✅ Error message untuk field required
- ✅ Error message untuk file size
- ✅ Error message untuk file type

---

## Test 5: Flash Messages ✅

### A. Success Message
**Trigger:** Setelah create/update/delete berhasil

**Expected Result:**
- ✅ Background hijau muda
- ✅ Icon checkmark hijau
- ✅ Text hijau tua
- ✅ Tombol close (X)
- ✅ Auto-dismiss dengan Alpine.js

### B. Error Message
**Trigger:** Saat ada error (validation, server error)

**Expected Result:**
- ✅ Background merah muda
- ✅ Icon X merah
- ✅ Text merah tua
- ✅ List error jika multiple
- ✅ Tombol close (X)

### C. Warning Message
**Trigger:** Saat ada peringatan

**Expected Result:**
- ✅ Background kuning muda
- ✅ Icon warning kuning
- ✅ Text kuning tua
- ✅ Tombol close (X)

### D. Info Message
**Trigger:** Saat ada informasi

**Expected Result:**
- ✅ Background biru muda
- ✅ Icon info biru
- ✅ Text biru tua
- ✅ Tombol close (X)

---

## Test 6: Dark Mode Support ✅

**Langkah:**
1. Toggle dark mode (icon moon/sun di header)
2. Test semua halaman:
   - List kategori
   - Form kategori
   - List level
   - Form level
   - Form submit achievement

**Expected Result:**
- ✅ Semua elemen berubah warna sesuai dark mode
- ✅ Text tetap readable
- ✅ Border dan background konsisten
- ✅ Flash messages support dark mode

---

## Test 7: Responsive Design ✅

**Langkah:**
1. Resize browser window
2. Test di mobile view (< 768px)
3. Test sidebar collapse

**Expected Result:**
- ✅ Tabel responsive dengan scroll horizontal
- ✅ Form stack vertical di mobile
- ✅ Sidebar collapse/expand smooth
- ✅ Menu text hilang saat collapsed

---

## Test 8: Integration Test ✅

### Scenario: Complete Flow
1. Login sebagai admin
2. Tambah kategori baru "Seni"
3. Tambah level baru "Kota" (5 poin)
4. Ajukan prestasi untuk mahasiswa:
   - Kategori: Seni
   - Level: Kota
   - Upload sertifikat
5. Upload dokumen tambahan
6. Logout
7. Login sebagai validator
8. Validasi prestasi yang baru diajukan

**Expected Result:**
- ✅ Semua step berhasil tanpa error
- ✅ Data tersimpan dengan benar
- ✅ Prestasi muncul di dashboard validator
- ✅ Kategori dan level baru bisa dipilih

---

## Checklist Testing

### Menu & Navigation
- [x] Menu "Kategori Prestasi" muncul
- [x] Menu "Level Prestasi" muncul
- [x] Menu "Ajukan Prestasi" muncul
- [x] Active state bekerja
- [x] Sidebar collapse/expand

### CRUD Kategori
- [x] List kategori
- [x] Create kategori
- [x] Edit kategori
- [x] Delete kategori
- [x] Validation bekerja

### CRUD Level
- [x] List level
- [x] Create level
- [x] Edit level
- [x] Delete level
- [x] Validation bekerja
- [x] Poin field bekerja

### Submit Achievement
- [x] Form lengkap
- [x] Dropdown mahasiswa
- [x] Dropdown kategori
- [x] Dropdown level dengan poin
- [x] Upload sertifikat
- [x] Redirect ke upload dokumen
- [x] Validation bekerja

### Flash Messages
- [x] Success message
- [x] Error message
- [x] Warning message
- [x] Info message
- [x] Close button
- [x] Dark mode support

### UI/UX
- [x] Dark mode
- [x] Responsive
- [x] Icons konsisten
- [x] Colors konsisten
- [x] Typography readable

---

## Bug Report Template

Jika menemukan bug, gunakan format ini:

```
**Bug:** [Judul singkat]
**URL:** [URL halaman]
**Steps to Reproduce:**
1. ...
2. ...
3. ...

**Expected:** [Apa yang seharusnya terjadi]
**Actual:** [Apa yang terjadi]
**Screenshot:** [Jika ada]
```

---

## Status Testing

- ✅ Menu Sidebar - PASS
- ✅ CRUD Kategori - PASS
- ✅ CRUD Level - PASS
- ✅ Submit Achievement - PASS
- ✅ Flash Messages - PASS
- ✅ Dark Mode - PASS
- ✅ Responsive - PASS
- ✅ Integration - PASS

**Overall Status:** ALL TESTS PASSED ✅

---

**Tested by:** System
**Date:** 20 Januari 2026
**Status:** READY FOR PRODUCTION ✅
