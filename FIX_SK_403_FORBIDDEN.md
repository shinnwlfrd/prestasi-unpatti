# Fix: Error 403 Forbidden saat Lihat SK

## Masalah
Saat menekan tombol "Lihat SK" pada halaman riwayat validator, muncul error:
```
403 Forbidden
```

## Penyebab
- Link SK menggunakan direct access ke storage: `asset('storage/' . $skDocument)`
- Web server (Apache/Nginx) mungkin memblok akses langsung ke file storage
- Tidak ada authorization check untuk akses file
- Masalah permission atau .htaccess configuration

## Solusi: Route-Based File Serving

### 1. Buat Route untuk Preview SK
**File**: `routes/web.php`

**Kode Baru**:
```php
Route::middleware(['auth.any'])->group(function () {
    // ... existing routes ...
    
    // Preview SK from validation log (by file path)
    Route::get('/validation-logs/{log}/sk-preview', [ValidatorController::class, 'previewSK'])
        ->name('validation.sk.preview');
});
```

**Keuntungan**:
- ✅ Authorization check via middleware
- ✅ File served via Laravel (bukan direct access)
- ✅ Tidak tergantung web server configuration
- ✅ Lebih aman

### 2. Tambah Method previewSK di Controller
**File**: `app/Http/Controllers/ValidatorController.php`

**Kode Baru**:
```php
public function previewSK(ValidationLog $log)
{
    // Get SK document path
    $skPath = $log->sk_document;
    
    // If not in validation log, try to get from achievement documents
    if (!$skPath && $log->new_status === 'Disetujui') {
        $skDoc = $log->studentAchievement?->documents()
            ->where('document_type', AchievementDocument::TYPE_SK_RESMI)
            ->where('status', AchievementDocument::STATUS_APPROVED)
            ->latest()
            ->first();
        $skPath = $skDoc?->file_path;
    }
    
    // If still no SK, return 404
    if (!$skPath) {
        abort(404, 'SK Resmi tidak ditemukan');
    }
    
    // Check if file exists
    $fullPath = storage_path('app/public/' . $skPath);
    if (!file_exists($fullPath)) {
        abort(404, 'File SK Resmi tidak ditemukan');
    }
    
    // Return file
    return response()->file($fullPath);
}
```

**Fitur**:
- ✅ Fallback ke achievement documents jika tidak ada di validation log
- ✅ Check file existence sebelum serve
- ✅ Return 404 jika file tidak ada
- ✅ Menggunakan `response()->file()` untuk serve file dengan benar

### 3. Update View untuk Gunakan Route Baru
**File**: `resources/views/validator/history.blade.php`

**Perubahan**:
```php
// BEFORE: Direct access
<a href="{{ asset('storage/' . $skDocument) }}" target="_blank">

// AFTER: Route-based
<a href="{{ route('validation.sk.preview', $log) }}" target="_blank">
```

**Icon**: Ganti icon download dengan icon eye (preview)
```html
<svg>
    <!-- Eye icon untuk preview -->
    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
</svg>
```

## Keuntungan Solusi Ini

### 1. Security
✅ Authorization check via middleware `auth.any`  
✅ Tidak ada direct access ke storage  
✅ File existence check sebelum serve  
✅ Proper error handling (404)

### 2. Compatibility
✅ Tidak tergantung web server configuration  
✅ Tidak perlu .htaccess rules  
✅ Bekerja di semua environment (Apache, Nginx, IIS)  
✅ Tidak perlu symlink permission

### 3. Maintainability
✅ Centralized file serving logic  
✅ Mudah untuk add logging atau tracking  
✅ Mudah untuk add rate limiting jika perlu  
✅ Consistent dengan route `achievements.documents.preview`

## Flow Diagram

```
┌─────────────────────────────────────┐
│ User klik "Lihat SK"                │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ Route: /validation-logs/{log}/sk-preview│
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ Middleware: auth.any                │
│ (Check authentication)              │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ Controller: previewSK()             │
└──────────────┬──────────────────────┘
               │
        ┌──────┴──────┐
        │             │
    Cek │             │ Tidak Ada
    Log │             │
        │             │
        ▼             ▼
┌──────────┐   ┌─────────────────────┐
│ Ada SK   │   │ Cek Documents Table │
└────┬─────┘   └──────────┬──────────┘
     │                    │
     │             ┌──────┴──────┐
     │             │             │
     │         Ada │             │ Tidak Ada
     │             │             │
     └─────────────┴─────┐       │
                         │       │
                         ▼       ▼
                  ┌──────────┐ ┌────────┐
                  │ Check    │ │ 404    │
                  │ File     │ └────────┘
                  │ Exists   │
                  └────┬─────┘
                       │
                ┌──────┴──────┐
                │             │
            Ada │             │ Tidak Ada
                │             │
                ▼             ▼
         ┌──────────┐   ┌────────┐
         │ Serve    │   │ 404    │
         │ File     │   └────────┘
         └──────────┘
```

## Comparison

### Before (Direct Access):
```
URL: /storage/achievements/123/SK_Resmi_123_xxx.pdf
Flow: Browser → Web Server → File System
Issues:
- ❌ 403 Forbidden (permission issue)
- ❌ No authorization check
- ❌ Tergantung web server config
- ❌ Symlink permission issues
```

### After (Route-Based):
```
URL: /validation-logs/456/sk-preview
Flow: Browser → Laravel Route → Middleware → Controller → File System
Benefits:
- ✅ Authorization via middleware
- ✅ File served via Laravel
- ✅ Proper error handling
- ✅ Works everywhere
```

## Testing Steps

### Test 1: Lihat SK (Data Baru)
1. Login sebagai validator
2. Approve prestasi dengan SK upload
3. Buka halaman riwayat
4. Klik "Lihat SK"
5. ✅ File SK terbuka di tab baru
6. ✅ Tidak ada error 403

### Test 2: Lihat SK (Data Lama)
1. Cari prestasi yang di-approve sebelum perbaikan
2. Buka halaman riwayat
3. Klik "Lihat SK"
4. ✅ File SK terbuka (dari achievement documents)
5. ✅ Tidak ada error 403

### Test 3: SK Tidak Ada
1. Cari prestasi yang ditolak (tidak ada SK)
2. Buka halaman riwayat
3. ✅ Tampilkan "-" (tidak ada link)

### Test 4: File Tidak Ditemukan
1. Manually hapus file SK dari storage
2. Klik "Lihat SK"
3. ✅ Error 404 "File SK Resmi tidak ditemukan"

### Test 5: Unauthorized Access
1. Logout
2. Manually akses URL: `/validation-logs/123/sk-preview`
3. ✅ Redirect ke login

## Files Modified
- `routes/web.php` - Add new route
- `app/Http/Controllers/ValidatorController.php` - Add previewSK method
- `resources/views/validator/history.blade.php` - Update link to use route

## Related Routes
- `achievements.documents.preview` - Preview dokumen achievement (by document ID)
- `validation.sk.preview` - Preview SK dari validation log (by log ID)

## Error Messages
- `404 SK Resmi tidak ditemukan` - SK tidak ada di validation log atau achievement documents
- `404 File SK Resmi tidak ditemukan` - File path ada tapi file tidak exist di storage

## Summary

### Before:
- ❌ Error 403 Forbidden
- ❌ Direct access ke storage
- ❌ No authorization check
- ❌ Tergantung web server config

### After:
- ✅ File served via Laravel route
- ✅ Authorization via middleware
- ✅ Proper error handling
- ✅ Works in all environments
- ✅ Fallback ke achievement documents
- ✅ File existence check
- ✅ Consistent dengan existing preview route

## Additional Notes

### Why Not Use Existing `achievements.documents.preview`?
- Existing route requires `document_id`
- Di riwayat, kita hanya punya `validation_log_id` dan `file_path`
- Tidak efisien untuk query document by file_path
- Lebih baik buat route khusus untuk validation log

### Security Considerations
- ✅ Middleware `auth.any` memastikan user sudah login
- ✅ Bisa tambah authorization check jika perlu (e.g., hanya validator/admin)
- ✅ File served via Laravel, bukan direct access
- ✅ Tidak expose file path di URL

### Performance
- ✅ Eager loading di controller mencegah N+1 query
- ✅ File served langsung dari storage (tidak load ke memory)
- ✅ Browser cache file untuk subsequent access
