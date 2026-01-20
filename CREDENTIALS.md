# 🔐 Login Credentials

## Admin Account
- **Email**: `admin@unpatti.ac.id`
- **Password**: `password`
- **Role**: Admin
- **Access**: `/admin`

## Validator Account
- **Email**: `validator@unpatti.ac.id`
- **Password**: `password`
- **Role**: Validator
- **Access**: `/validator`

## Login URL
```
http://localhost/login
```

## Cara Login
1. Buka browser dan akses `http://localhost/login`
2. Pilih tab sesuai role (Admin atau Validator)
3. Masukkan email dan password
4. Klik tombol "Masuk"

## Troubleshooting
Jika login gagal, jalankan:
```bash
# Verifikasi setup
php verify_login_setup.php

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reset user jika perlu
php artisan db:seed --class=AchievementValidationSeeder
```

## Catatan Penting
- ✅ Local login sudah **ENABLED**
- ✅ SSO force sudah **DISABLED**
- ✅ User sudah dibuat dengan benar
- ✅ Password sudah di-hash dengan benar
- ✅ Field `is_active` sudah di-set `true`
- ✅ Semua konfigurasi sudah benar

Untuk detail troubleshooting lengkap, lihat file `LOGIN_TROUBLESHOOTING.md`
