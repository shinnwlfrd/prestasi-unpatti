# Fix: Validator Session Berubah Saat Membuka Dokumen

## Masalah
Ketika validator menekan tombol "Lihat Dokumen", akun validator berubah menjadi akun user/student.

## Penyebab
1. **Urutan Pengecekan Authentication**: Kode sebelumnya memeriksa `session('auth_role')` SEBELUM `auth()->check()`, yang bisa menyebabkan konflik jika ada session student yang tersisa
2. **Layout Logic**: Layout `app.blade.php` menggunakan `session('auth_role') ?? auth()->user()->role` yang bisa menyebabkan ambiguitas

## Solusi

### 1. Update Middleware `AuthenticateAny` (app/Http/Middleware/AuthenticateAny.php)
```php
// Check auth()->check() FIRST untuk mencegah konflik session
if (auth()->check()) {
    return $next($request);
}

// Hanya check student session jika TIDAK authenticated sebagai regular user
if (session('auth_role') === 'student' && session('student_id')) {
    return $next($request);
}
```

### 2. Update Authorization Logic (app/Http/Controllers/DocumentUploadController.php)
```php
protected function authorizeDocumentAccess(StudentAchievement $achievement): void
{
    // PENTING: Check regular user auth FIRST
    if (auth()->check()) {
        $user = auth()->user();
        
        // Admin dan validator bisa akses semua dokumen
        if (in_array($user->role, ['Admin', 'Validator'])) {
            return;
        }
        // ... rest of logic
    }

    // Hanya check student session jika TIDAK authenticated sebagai regular user
    if (session('auth_role') === 'student' && session('student_id')) {
        // ... student logic
    }
}
```

### 3. Update Layout Logic (resources/views/layouts/app.blade.php)
```php
// PENTING: Check auth()->check() FIRST untuk mencegah role confusion
if (auth()->check()) {
    $role = auth()->user()->role;
    $userName = auth()->user()->name;
} elseif (session('auth_role') === 'student') {
    $role = 'student';
    $userName = session('student_name', 'Mahasiswa');
} else {
    $role = 'guest';
    $userName = 'Guest';
}
```

## Prinsip Utama
**SELALU periksa `auth()->check()` TERLEBIH DAHULU** sebelum memeriksa `session('auth_role')` untuk mencegah konflik antara:
- **Validator/Admin**: Menggunakan Laravel Auth (`Auth::login()`)
- **Student**: Menggunakan Session (`session(['auth_role' => 'student'])`)

## Testing
1. Login sebagai Validator
2. Buka halaman validasi prestasi
3. Klik tombol "Lihat Dokumen" pada dokumen
4. Verifikasi bahwa:
   - Dokumen terbuka dengan benar
   - Session tetap sebagai Validator (tidak berubah ke student/user)
   - Navbar masih menampilkan nama dan role Validator
   - Tombol logout masih berfungsi dengan benar

## Files Modified
- `app/Http/Middleware/AuthenticateAny.php`
- `app/Http/Controllers/DocumentUploadController.php`
- `resources/views/layouts/app.blade.php`
