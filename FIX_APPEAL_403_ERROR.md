# Fix: Error 403 "This action is unauthorized" pada Form Banding

## Masalah
Saat mahasiswa menekan tombol "Ajukan Banding", muncul error:
```
403 This action is unauthorized.
```

## Penyebab
Method `authorize()` di `SubmitAppealRequest` menggunakan `auth()->check()` dan `auth()->user()` yang tidak bekerja untuk mahasiswa karena:
- Mahasiswa login menggunakan **session-based authentication** (bukan Laravel Auth)
- Session keys: `auth_role` = 'student' dan `student_id`
- `auth()->check()` return false untuk student session
- `auth()->user()` return null untuk student session

## Kode Lama (Bermasalah)
```php
public function authorize(): bool
{
    return auth()->check();
}
```

**Masalah:**
- Hanya mengecek apakah user login via Laravel Auth
- Tidak mengecek session-based student authentication
- Tidak memvalidasi ownership (apakah student adalah pemilik prestasi)

## Solusi

### File: `app/Http/Requests/SubmitAppealRequest.php`

**Kode Baru:**
```php
public function authorize(): bool
{
    // Check if student is authenticated via session
    if (session('auth_role') !== 'student' || !session('student_id')) {
        return false;
    }

    // Get the achievement from route parameter
    $achievement = $this->route('achievement');
    
    // Check if achievement exists
    if (!$achievement) {
        return false;
    }

    // Check if the logged-in student is the owner of the achievement
    return $achievement->student_id === session('student_id');
}
```

**Penjelasan:**
1. ✅ Cek session `auth_role` === 'student'
2. ✅ Cek session `student_id` exists
3. ✅ Ambil achievement dari route parameter
4. ✅ Validasi achievement exists
5. ✅ Validasi ownership: `achievement->student_id` === `session('student_id')`

## Validasi yang Dilakukan

### 1. Authentication Check
```php
if (session('auth_role') !== 'student' || !session('student_id')) {
    return false;
}
```
- Memastikan user login sebagai student
- Memastikan student_id ada di session

### 2. Achievement Existence Check
```php
$achievement = $this->route('achievement');
if (!$achievement) {
    return false;
}
```
- Memastikan achievement ada di database
- Mencegah error jika achievement tidak ditemukan

### 3. Ownership Check
```php
return $achievement->student_id === session('student_id');
```
- Memastikan student yang login adalah pemilik prestasi
- Mencegah student lain mengajukan banding untuk prestasi orang lain

## Testing

### Test 1: Student yang Benar
**Scenario:** Student login dan ajukan banding untuk prestasi miliknya sendiri

**Steps:**
1. Login sebagai mahasiswa (NIM: 123456)
2. Buka prestasi dengan student_id = 123456 dan status "Revisi"
3. Klik "Ajukan Banding"
4. Isi form dan submit

**Expected Result:**
- ✅ Form berhasil submit
- ✅ Banding tersimpan di database
- ✅ Redirect ke dashboard dengan success message

### Test 2: Student Salah (Ownership)
**Scenario:** Student A mencoba ajukan banding untuk prestasi Student B

**Steps:**
1. Login sebagai Student A (NIM: 123456)
2. Manually akses URL: `/achievements/{achievement_id}/appeal`
   - achievement_id milik Student B (NIM: 789012)
3. Submit form

**Expected Result:**
- ✅ Error 403 "This action is unauthorized"
- ✅ Banding tidak tersimpan

### Test 3: Tidak Login
**Scenario:** User belum login mencoba akses form banding

**Steps:**
1. Logout
2. Akses URL: `/achievements/{achievement_id}/appeal`

**Expected Result:**
- ✅ Redirect ke halaman login
- ✅ Error message: "Anda harus login sebagai mahasiswa"

### Test 4: Login sebagai Admin/Validator
**Scenario:** Admin/Validator mencoba akses form banding

**Steps:**
1. Login sebagai admin/validator
2. Akses URL: `/achievements/{achievement_id}/appeal`

**Expected Result:**
- ✅ Redirect ke halaman login
- ✅ Error message: "Anda harus login sebagai mahasiswa"

## Middleware Flow

```
Request → auth.student middleware → SubmitAppealRequest authorize()
   ↓              ↓                           ↓
   ↓         Check session              Check ownership
   ↓         auth_role='student'        achievement->student_id
   ↓         student_id exists          === session('student_id')
   ↓              ↓                           ↓
   ↓         ✅ Pass                      ✅ Pass
   ↓              ↓                           ↓
   ↓         Controller store()          Process appeal
```

## Related Files
- `app/Http/Requests/SubmitAppealRequest.php` - Authorization logic
- `app/Http/Middleware/AuthStudent.php` - Student authentication middleware
- `routes/web.php` - Route definition with middleware
- `app/Http/Controllers/AchievementAppealController.php` - Controller

## Session Keys Used
```php
session('auth_role')   // 'student'
session('student_id')  // e.g., '123456'
```

## Security Considerations

### ✅ Implemented
- Session-based authentication check
- Ownership validation
- Achievement existence check
- Middleware protection (auth.student)

### ⚠️ Additional Security (Optional)
- Rate limiting untuk prevent spam appeals
- CSRF protection (sudah ada via @csrf)
- File upload validation (sudah ada)
- XSS protection (sudah ada via Blade escaping)

## Backward Compatibility
✅ Tidak ada breaking changes  
✅ Tidak memerlukan migration  
✅ Tidak memerlukan perubahan di tempat lain

## Notes
- Student authentication menggunakan **session**, bukan Laravel Auth
- Admin/Validator menggunakan **Laravel Auth** (`auth()->user()`)
- Jangan gunakan `auth()->check()` atau `auth()->user()` untuk student
- Gunakan `session('auth_role')` dan `session('student_id')` untuk student
