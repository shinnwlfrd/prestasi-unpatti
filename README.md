# Sistem Prestasi Mahasiswa - Universitas Pattimura

Sistem manajemen prestasi mahasiswa dengan validasi dua tahap (Two-Stage Validation) yang terintegrasi dengan SIGAP dan SSO Unpatti.

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Seeding Database](#seeding-database)
- [Login Credentials](#login-credentials)
- [Alur Kerja Sistem](#alur-kerja-sistem)
- [Status Prestasi](#status-prestasi)
- [Dokumentasi Lengkap](#dokumentasi-lengkap)

---

## 🎯 Fitur Utama

### 1. Two-Stage Validation System
- **Tahap 1: Validasi Fakultas** - Operator Fakultas memvalidasi prestasi mahasiswa
- **Tahap 2: Validasi Universitas** - Admin Universitas memberikan persetujuan final dan menerbitkan SK
- **Sistem Banding** - Mahasiswa dapat mengajukan banding jika prestasi perlu revisi

### 2. Integrasi SIGAP
- Sinkronisasi data mahasiswa dari SIGAP
- Filter cascade (Fakultas → Jurusan → Program Studi)
- Data hierarki organisasi yang lengkap

### 3. SSO Unpatti
- Login menggunakan akun SSO Unpatti
- Auto-detection role berdasarkan email
- Multi-role support (Student + Validator/Admin)

### 4. Manajemen Dokumen
- Upload multiple documents
- Document verification
- Document revision history
- Preview dokumen

### 5. SK (Surat Keputusan)
- Penerbitan SK untuk prestasi yang disetujui
- Bulk assignment SK
- Preview dan download SK

---

## 🛠 Teknologi

- **Framework**: Laravel 11.x
- **Database**: PostgreSQL
- **Frontend**: Blade Templates, TailwindCSS, Alpine.js
- **Authentication**: Laravel Sanctum + SSO
- **API Integration**: SIGAP API

---

## 📦 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd beasiswa-unpatti
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
# Buat database PostgreSQL
createdb prestasi_unpatti

# Update .env dengan kredensial database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=prestasi_unpatti
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```

### 6. Build Assets
```bash
npm run build
```

### 7. Start Server
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

---

## ⚙️ Konfigurasi

### SSO Configuration
Edit `config/sso.php`:
```php
return [
    'base_url' => env('SSO_BASE_URL', 'https://sso.unpatti.ac.id'),
    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'redirect_uri' => env('SSO_REDIRECT_URI'),
];
```

Update `.env`:
```env
SSO_BASE_URL=https://sso.unpatti.ac.id
SSO_CLIENT_ID=your_client_id
SSO_CLIENT_SECRET=your_client_secret
SSO_REDIRECT_URI=http://localhost:8000/sso/callback
```

### SIGAP API Configuration
Update `.env`:
```env
SIGAP_API_URL=https://sigap.unpatti.ac.id/api
SIGAP_API_TOKEN=your_api_token
```

---

## 🌱 Seeding Database

Sistem menggunakan 2 seeder utama:

### 1. SigapBasedDataSeeder
Membuat data dasar dari SIGAP:
- Academic Periods
- Achievement Categories & Levels
- Students (dari SIGAP)
- Validators & Admin
- Achievement Templates
- Student Achievements (legacy format)

### 2. TwoStageValidationSeeder
Membuat data dengan two-stage validation:
- SK Documents
- Student Achievements dengan status bervariasi
- Validation Logs lengkap
- SK Assignments

**Jalankan Seeder:**
```bash
php artisan migrate:fresh --seed
```

**Hasil Seeding:**
- ✅ 1600 students
- ✅ 24 validators (1 per fakultas)
- ✅ 1 admin
- ✅ 40 achievement templates
- ✅ ~9000 student achievements
- ✅ 3 SK documents

---

## 🔐 Login Credentials

### Admin
```
Email: admin@unpatti.ac.id
Password: password
```

### Validator
```
Email: [faculty_code][number]@unpatti.ac.id
Password: password

Contoh:
- ft1@unpatti.ac.id / password (Fakultas Teknik)
- fh1@unpatti.ac.id / password (Fakultas Hukum)
```

### Student
```
Email: [NIM]@student.unpatti.ac.id
Password: [NIM]

Contoh:
- 2021010001@student.unpatti.ac.id / 2021010001
```

---

## 🔄 Alur Kerja Sistem

### 1. Mahasiswa Submit Prestasi
```
draft → submitted
```
- Mahasiswa mengisi form prestasi
- Upload dokumen pendukung
- Submit untuk validasi

### 2. Validasi Fakultas (Operator Fakultas)
```
submitted → faculty_review → faculty_approved
                           → faculty_rejected (FINAL)
                           → faculty_revision (dapat banding)
```
- Validator fakultas mereview prestasi
- Approve: Dikirim ke universitas
- Reject: Ditolak final
- Revision: Mahasiswa dapat mengajukan banding

### 3. Validasi Universitas (Admin)
```
faculty_approved → university_review → university_approved (FINAL + SK)
                                    → university_rejected (FINAL)
```
- Admin universitas mereview prestasi yang sudah disetujui fakultas
- Approve: Terbitkan SK (FINAL)
- Reject: Ditolak final (rare case)

### 4. Proses Banding
```
faculty_revision → appeal_submitted → appeal_approved → university_approved
                                   → appeal_rejected (FINAL)
```
- Mahasiswa ajukan banding dari status `faculty_revision`
- Admin review banding
- Approve: Lanjut ke validasi universitas
- Reject: Ditolak final

---

## 📊 Status Prestasi

### Faculty Stage (60%)
| Status | Label | Keterangan |
|--------|-------|------------|
| `submitted` | Diajukan | Menunggu review fakultas |
| `faculty_review` | Review Fakultas | Sedang direview fakultas |
| `faculty_approved` | Disetujui Fakultas | Menunggu review universitas |
| `faculty_revision` | Revisi Fakultas | Perlu revisi (dapat banding) |
| `faculty_rejected` | Ditolak Fakultas | Ditolak final |

### University Stage (30%)
| Status | Label | Keterangan |
|--------|-------|------------|
| `university_review` | Review Universitas | Sedang direview universitas |
| `university_approved` | Disetujui Universitas | Approved final + SK |
| `university_rejected` | Ditolak Universitas | Ditolak final |

### Appeal Stage (10%)
| Status | Label | Keterangan |
|--------|-------|------------|
| `appeal_submitted` | Banding Diajukan | Menunggu review admin |
| `appeal_approved` | Banding Diterima | Lanjut ke universitas |
| `appeal_rejected` | Banding Ditolak | Ditolak final |

---

## 📚 Dokumentasi Lengkap

### Dokumentasi Utama
1. **README.md** (file ini) - Overview dan quick start
2. **SISTEM_PRESTASI_MAHASISWA_WORKFLOW.md** - Alur kerja detail sistem
3. **STATUS_REFERENCE_GUIDE.md** - Panduan lengkap semua status
4. **DEPLOYMENT_CHECKLIST.md** - Checklist deployment
5. **INTEGRATION_COMPLETE_SUMMARY.md** - Summary integrasi lengkap

### Dokumentasi Fitur
- **SSO_QUICK_START.md** - Setup SSO Unpatti
- **SETUP_SSO_UNPATTI.md** - Konfigurasi SSO detail
- **DOKUMENTASI_ALUR_KERJA_APLIKASI.md** - Alur kerja aplikasi
- **EVALUASI_FITUR_PRESTASI.md** - Evaluasi fitur
- **LAPORAN_INTEGRASI_SIAKAD.md** - Integrasi SIAKAD/SIGAP

---

## 🎨 Tingkat Prestasi

| Tingkat | Poin | Keterangan |
|---------|------|------------|
| Internasional | 100 | Kompetisi/event internasional |
| Nasional | 75 | Kompetisi/event nasional |
| Provinsi | 50 | Kompetisi/event tingkat provinsi |
| Universitas | 25 | Kompetisi/event tingkat universitas |

---

## 🔑 Prinsip Utama Sistem

### 1. Two-Stage Separation
- Fakultas validasi pertama
- Universitas validasi kedua (final)
- Tidak ada bypass tahapan

### 2. SK Assignment
- SK HANYA di-assign oleh Admin Universitas
- Validator tidak melihat/memilih SK
- SK wajib untuk approval final

### 3. Final Statuses
- `faculty_rejected` - Ditolak fakultas (tidak lanjut)
- `university_approved` - Approved final dengan SK
- `university_rejected` - Ditolak universitas (rare)

### 4. Appeal Process
- Hanya dari status `faculty_revision`
- Direview oleh Admin (bukan Validator)
- Keputusan banding adalah final

---

## 🧪 Testing

### Manual Testing
```bash
# Login sebagai student
Email: 2021010001@student.unpatti.ac.id
Password: 2021010001

# Login sebagai validator
Email: ft1@unpatti.ac.id
Password: password

# Login sebagai admin
Email: admin@unpatti.ac.id
Password: password
```

### Test Flow
1. ✅ Student submit prestasi
2. ✅ Validator approve (tanpa SK)
3. ✅ Admin approve dengan SK
4. ✅ Verifikasi status updates
5. ✅ Test appeal process

---

## 🐛 Troubleshooting

### Database Connection Error
```bash
# Check PostgreSQL service
sudo systemctl status postgresql

# Restart PostgreSQL
sudo systemctl restart postgresql
```

### Migration Error
```bash
# Reset database
php artisan migrate:fresh

# With seeding
php artisan migrate:fresh --seed
```

### SSO Login Error
- Periksa konfigurasi SSO di `.env`
- Pastikan `SSO_CLIENT_ID` dan `SSO_CLIENT_SECRET` benar
- Cek log di `storage/logs/laravel.log`

### SIGAP API Error
- Periksa `SIGAP_API_URL` dan `SIGAP_API_TOKEN`
- Test koneksi ke SIGAP API
- Cek log error

---

## 📝 Catatan Penting

### Untuk Developer
- Gunakan `php artisan migrate:fresh --seed` untuk reset database
- Jangan commit file `.env`
- Selalu test di local sebelum deploy
- Ikuti Laravel best practices

### Untuk Admin
- Backup database secara berkala
- Monitor validation logs
- Periksa SK assignments
- Review appeal submissions

### Untuk Validator
- Hanya validasi prestasi dari fakultas sendiri
- Tidak perlu pilih SK (dilakukan oleh admin)
- Gunakan fitur revision untuk minta perbaikan dokumen

### Untuk Mahasiswa
- Upload dokumen yang lengkap dan jelas
- Periksa status prestasi secara berkala
- Gunakan fitur banding jika diperlukan

---

## 🤝 Kontribusi

Untuk berkontribusi pada project ini:
1. Fork repository
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📄 Lisensi

Project ini adalah milik Universitas Pattimura.

---

## 👥 Tim Pengembang

- **Developer**: Tim IT Unpatti
- **Project Manager**: [Nama]
- **System Analyst**: [Nama]

---

## 📞 Kontak

Untuk pertanyaan atau bantuan:
- **Email**: it@unpatti.ac.id
- **Website**: https://unpatti.ac.id
- **Support**: [Link ke sistem support]

---

**Versi**: 2.0.0 (Two-Stage Validation System)  
**Last Updated**: 30 Januari 2026  
**Status**: ✅ Production Ready
