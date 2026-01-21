# Panduan Validator per Fakultas

## Aturan Baru: 1 Validator = 1 Fakultas

Mulai sekarang, **setiap fakultas hanya boleh memiliki SATU validator aktif**.

---

## Cara Menambah Validator

### 1. Login sebagai Admin
Masuk ke sistem dengan akun Admin.

### 2. Buka Menu "Kelola User"
Klik menu "Kelola User" di sidebar.

### 3. Klik "Tambah User"
Klik tombol "Tambah User" di pojok kanan atas.

### 4. Isi Form
- **Nama**: Nama lengkap validator
- **Email**: Email validator (harus unik)
- **Password**: Password untuk login
- **Role**: Pilih "Validator"
- **Fakultas**: **WAJIB DIPILIH** ⚠️

### 5. Pilih Fakultas
Pilih salah satu dari 9 fakultas:
1. Fakultas Teknik
2. Fakultas Ekonomi dan Bisnis
3. Fakultas Hukum
4. Fakultas Ilmu Sosial dan Ilmu Politik
5. Fakultas Pertanian
6. Fakultas Kedokteran
7. Fakultas Keguruan dan Ilmu Pendidikan
8. Fakultas Perikanan dan Ilmu Kelautan
9. Fakultas MIPA

**ATAU**

Pilih "Semua Fakultas (Super Validator)" jika validator boleh validasi semua fakultas.

### 6. Klik "Simpan"

---

## Pesan Error

### Jika Fakultas Sudah Ada Validator
Anda akan melihat pesan error:
```
Fakultas [Nama Fakultas] sudah memiliki validator aktif ([Nama Validator]). 
Satu fakultas hanya boleh memiliki satu validator.
```

### Solusi:
1. **Pilih fakultas lain** yang belum ada validatornya, ATAU
2. **Non-aktifkan validator lama** terlebih dahulu, ATAU
3. **Pilih "Semua Fakultas"** untuk membuat super validator

---

## Super Validator

### Apa itu Super Validator?
Validator yang bisa validasi prestasi dari **SEMUA fakultas**.

### Cara Membuat Super Validator:
Pilih "Semua Fakultas (Super Validator)" pada dropdown Fakultas.

### Keuntungan:
- Tidak ada batasan fakultas
- Bisa validasi prestasi dari fakultas manapun
- Tidak ada konflik dengan validator fakultas lain

### Kapan Digunakan?
- Untuk backup validator
- Untuk admin yang juga bertugas sebagai validator
- Untuk koordinator validasi

---

## Edit Validator

### 1. Klik Tombol "Edit"
Klik tombol "Edit" pada baris validator yang ingin diubah.

### 2. Ubah Data
Ubah data yang diperlukan (nama, email, fakultas, dll).

### 3. Ganti Fakultas (Opsional)
Jika ingin mengganti fakultas:
- Pastikan fakultas baru belum ada validatornya
- Atau pilih "Semua Fakultas"

### 4. Klik "Update"

---

## Non-aktifkan Validator

### Cara:
1. Klik tombol "Edit" pada validator
2. **Hilangkan centang** pada "User Aktif"
3. Klik "Update"

### Efek:
- Validator tidak bisa login
- Fakultas menjadi tersedia untuk validator baru
- Data validasi lama tetap tersimpan

---

## Daftar Validator Saat Ini

Berikut validator yang sudah terdaftar:

| Fakultas | Validator |
|----------|-----------|
| Fakultas Teknik | Validator Fakultas Teknik |
| Fakultas Ekonomi dan Bisnis | Validator Fakultas Ekonomi dan Bisnis |
| Fakultas Hukum | Validator Fakultas Hukum |
| Fakultas Ilmu Sosial dan Ilmu Politik | Validator Fakultas Ilmu Sosial dan Ilmu Politik |
| Fakultas Pertanian | Validator Fakultas Pertanian |
| Fakultas Kedokteran | Validator Fakultas Kedokteran |
| Fakultas Keguruan dan Ilmu Pendidikan | Validator Fakultas Keguruan dan Ilmu Pendidikan |
| Fakultas Perikanan dan Ilmu Kelautan | Validator Fakultas Perikanan dan Ilmu Kelautan |
| Fakultas MIPA | Validator Fakultas MIPA |
| Semua Fakultas | Validator Prestasi (Super Validator) |

---

## Akses Validator

### Validator Fakultas
- Hanya bisa lihat prestasi dari **fakultas sendiri**
- Tidak bisa akses prestasi fakultas lain
- Dashboard hanya menampilkan prestasi fakultas sendiri
- Riwayat validasi hanya dari fakultas sendiri

### Super Validator
- Bisa lihat prestasi dari **SEMUA fakultas**
- Tidak ada batasan akses
- Dashboard menampilkan semua prestasi
- Riwayat validasi dari semua fakultas

---

## FAQ

### Q: Bagaimana jika saya lupa fakultas mana yang sudah ada validator?
**A**: Lihat tabel di halaman "Kelola User". Setiap validator menampilkan badge fakultasnya.

### Q: Bisa tidak satu fakultas punya 2 validator?
**A**: Tidak bisa. Sistem akan menolak dengan pesan error.

### Q: Bagaimana jika validator resign?
**A**: Non-aktifkan validator lama, lalu buat validator baru untuk fakultas tersebut.

### Q: Apakah bisa membuat banyak super validator?
**A**: Ya, bisa. Tidak ada batasan untuk super validator.

### Q: Bagaimana jika validator pindah fakultas?
**A**: Edit validator tersebut dan ganti fakultasnya (pastikan fakultas baru belum ada validatornya).

### Q: Apakah validator lama bisa diaktifkan kembali?
**A**: Ya, tapi pastikan fakultasnya tidak bentrok dengan validator aktif lain.

---

## Data Testing

Sistem sudah dilengkapi dengan data testing:
- **155 mahasiswa** dari 9 fakultas
- **193 prestasi** dengan berbagai status
- **9 validator** (1 per fakultas)
- **1 super validator**

Data ini bisa digunakan untuk testing dan training.

---

## Kontak Support

Jika ada masalah atau pertanyaan:
1. Hubungi admin sistem
2. Baca dokumentasi teknis di `FACULTY_VALIDATOR_UNIQUE_CONSTRAINT.md`
3. Lihat summary lengkap di `IMPLEMENTATION_COMPLETE_SUMMARY.md`

---

**Terakhir Diperbarui**: 21 Januari 2026  
**Versi**: 1.0  
**Status**: Aktif ✅
