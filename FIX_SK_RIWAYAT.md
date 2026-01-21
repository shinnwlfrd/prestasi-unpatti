# Fix: Lihat SK pada Halaman Riwayat

## Masalah
Tombol "Lihat SK" pada halaman riwayat validator tidak berfungsi karena SK document path tidak disimpan di tabel `validation_logs`.

## Lokasi Masalah
- **File**: `resources/views/validator/history.blade.php`
- **Baris**: 119-127
- **Kode**:
```php
@if($log->sk_document)
    <a href="{{ asset('storage/' . $log->sk_document) }}" target="_blank">
        View
    </a>
@else
    <span class="text-gray-400 text-sm">-</span>
@endif
```

## Penyebab
SK Resmi disimpan sebagai dokumen di tabel `achievement_documents`, tetapi path-nya tidak disimpan di kolom `validation_logs.sk_document` saat approval.

## Solusi

### 1. Update AchievementApprovalService
**File**: `app/Services/AchievementApprovalService.php`

**Perubahan**:
- Tambah parameter `$skDocumentPath` di method `approve()`
- Tambah parameter `$skDocumentPath` di method `createValidationLog()`
- Simpan SK document path ke kolom `sk_document` di validation log

```php
public function approve(
    StudentAchievement $achievement, 
    User $validator, 
    ?string $notes = null, 
    ?string $skDocumentPath = null
): bool

protected function createValidationLog(
    StudentAchievement $achievement,
    User $validator,
    string $oldStatus,
    string $newStatus,
    ?string $notes = null,
    ?array $metadata = null,
    ?string $skDocumentPath = null
): ValidationLog {
    return ValidationLog::create([
        'sa_id' => $achievement->sa_id,
        'validator_id' => $validator->id,
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
        'notes' => $notes,
        'sk_document' => $skDocumentPath,  // ← ADDED
        'metadata' => $metadata,
        'validated_at' => now(),
    ]);
}
```

### 2. Update ValidatorController
**File**: `app/Http/Controllers/ValidatorController.php`

**Perubahan**:
- Method `uploadSkResmi()` sekarang return file path
- File path dikirim ke `approve()` service

```php
// Handle SK Resmi upload (WAJIB untuk approve)
$skDocumentPath = null;
if ($request->action === 'approve' && $request->hasFile('sk_resmi')) {
    $skDocumentPath = $this->uploadSkResmi($request, $achievement, $validator);
}

// Process action
$success = match($request->action) {
    'approve' => $this->approvalService->approve($achievement, $validator, $request->notes, $skDocumentPath),
    // ...
};

protected function uploadSkResmi(Request $request, StudentAchievement $achievement, $validator)
{
    // ... upload logic ...
    
    // Return file path untuk disimpan di validation log
    return $filePath;
}
```

### 3. Update AchievementValidationController (Admin)
**File**: `app/Http/Controllers/AchievementValidationController.php`

**Perubahan**: Sama seperti ValidatorController

### 4. Update AdminAchievementController
**File**: `app/Http/Controllers/Admin/AdminAchievementController.php`

**Perubahan**:
- Simpan file path ke variable `$skDocumentPath`
- Kirim ke `approve()` service

```php
if ($request->submit_action === 'approve') {
    // Upload SK Resmi
    $skDocumentPath = null;
    if ($request->hasFile('sk_resmi')) {
        // ... upload logic ...
        $skDocumentPath = $filePath;
    }

    // Log approval
    $this->approvalService->approve($achievement, auth()->user(), 'Disetujui langsung oleh admin saat submit', $skDocumentPath);
}
```

## Hasil
✅ SK document path sekarang disimpan di `validation_logs.sk_document`  
✅ Tombol "Lihat SK" di halaman riwayat akan menampilkan link ke file SK  
✅ Link SK akan membuka file di tab baru  
✅ Jika tidak ada SK, akan menampilkan "-"

## Testing
1. Login sebagai validator/admin
2. Approve prestasi dengan upload SK Resmi
3. Buka halaman riwayat (`/validator/history`)
4. Lihat kolom "SK" - seharusnya ada link "View"
5. Klik link "View" - file SK akan terbuka di tab baru

## Files Modified
- `app/Services/AchievementApprovalService.php`
- `app/Http/Controllers/ValidatorController.php`
- `app/Http/Controllers/AchievementValidationController.php`
- `app/Http/Controllers/Admin/AdminAchievementController.php`

## Database Schema
Tidak ada perubahan schema - kolom `validation_logs.sk_document` sudah ada sejak awal.

## Backward Compatibility
✅ Kompatibel dengan data lama - jika `sk_document` null, akan menampilkan "-"  
✅ Tidak memerlukan migration  
✅ Tidak memerlukan data migration
