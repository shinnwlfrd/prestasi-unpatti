# Login Troubleshooting Guide

## Status Pemeriksaan

### ✅ User Database
- **Admin User**: ✓ Sudah dibuat dengan benar
  - Email: `admin@unpatti.ac.id`
  - Password: `password`
  - Role: `Admin`
  - Is Active: `true`
  - Has Password: `true`

- **Validator User**: ✓ Sudah dibuat dengan benar
  - Email: `validator@unpatti.ac.id`
  - Password: `password`
  - Role: `Validator`
  - Is Active: `true`
  - Has Password: `true`

### ✅ Password Verification
- Admin password hash: **VALID** ✓
- Validator password hash: **VALID** ✓

### ✅ AuthService
- `authenticateLocal()` method: **WORKING** ✓
- Admin login test: **SUCCESS** ✓
- Validator login test: **SUCCESS** ✓

### ✅ Configuration
- `.env` file:
  - `AUTH_LOCAL_ENABLED=true` ✓
  - `AUTH_SSO_FORCE=false` ✓
- `config/sso.php`: **CORRECT** ✓

### ✅ Routes
- Login routes: **CONFIGURED** ✓
- POST `/login`: **AVAILABLE** ✓

### ✅ Middleware
- `EnsureUserIsAdmin`: **CORRECT** ✓
- `EnsureUserIsValidator`: **CORRECT** ✓

### ✅ Login Form
- Form fields: **CORRECT** ✓
- Field names match controller: **YES** ✓
- Role selector: **WORKING** ✓

## Cara Login

### Login sebagai Admin
1. Buka halaman login: `http://localhost/login`
2. Pilih tab **"Admin"**
3. Masukkan:
   - Email: `admin@unpatti.ac.id`
   - Password: `password`
4. Klik **"Masuk"**
5. Akan redirect ke: `/admin`

### Login sebagai Validator
1. Buka halaman login: `http://localhost/login`
2. Pilih tab **"Validator"**
3. Masukkan:
   - Email: `validator@unpatti.ac.id`
   - Password: `password`
4. Klik **"Masuk"**
5. Akan redirect ke: `/validator`

## Jika Masih Gagal Login

### 1. Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Periksa Session
Pastikan session driver berfungsi:
```bash
# Di .env
SESSION_DRIVER=file
```

Pastikan folder `storage/framework/sessions` ada dan writable:
```bash
mkdir -p storage/framework/sessions
chmod -R 775 storage/framework/sessions
```

### 3. Periksa Database
Jalankan query manual untuk memastikan user ada:
```sql
SELECT id, name, email, role, is_active, 
       CASE WHEN password IS NULL THEN 'NO' ELSE 'YES' END as has_password
FROM users 
WHERE email IN ('admin@unpatti.ac.id', 'validator@unpatti.ac.id');
```

### 4. Reset Password (Jika Perlu)
Jika password tidak cocok, reset dengan:
```bash
php artisan tinker
```

Kemudian di tinker:
```php
$admin = App\Models\User::where('email', 'admin@unpatti.ac.id')->first();
$admin->password = Hash::make('password');
$admin->is_active = true;
$admin->save();

$validator = App\Models\User::where('email', 'validator@unpatti.ac.id')->first();
$validator->password = Hash::make('password');
$validator->is_active = true;
$validator->save();
```

### 5. Jalankan Ulang Seeder
```bash
php artisan db:seed --class=AchievementValidationSeeder
```

### 6. Periksa Error Log
Lihat log Laravel untuk error detail:
```bash
tail -f storage/logs/laravel.log
```

### 7. Test dengan Browser Developer Tools
1. Buka browser Developer Tools (F12)
2. Pergi ke tab **Network**
3. Coba login
4. Periksa request POST ke `/login`
5. Lihat response status dan redirect

## Kemungkinan Masalah

### Rate Limiting
Jika terlalu banyak percobaan login gagal, tunggu 15 menit atau clear cache:
```bash
php artisan cache:clear
```

### CSRF Token
Pastikan form memiliki `@csrf` token. Sudah ada di form ✓

### Browser Cache
Clear browser cache atau coba di incognito/private mode.

### Session Cookie
Periksa di browser Developer Tools > Application > Cookies
Pastikan cookie Laravel session ada.

## Verifikasi Manual

Jalankan script verifikasi:
```bash
php check_users.php
```

Output yang benar:
```
=== ADMIN USER ===
Email: admin@unpatti.ac.id
Name: Admin Sistem
Role: Admin
Is Active: true
Has Password: true

=== VALIDATOR USER ===
Email: validator@unpatti.ac.id
Name: Validator Prestasi
Role: Validator
Is Active: true
Has Password: true

=== PASSWORD TEST ===
Admin password 'password' check: VALID
Validator password 'password' check: VALID
```

## Kontak Support

Jika masih ada masalah, sertakan informasi berikut:
1. Screenshot error message
2. Output dari `php check_users.php`
3. Isi file `.env` (tanpa sensitive data)
4. Laravel log (`storage/logs/laravel.log`)
