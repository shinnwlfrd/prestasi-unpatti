# Implementasi SK Waiver System

## Overview
Sistem untuk mengakomodasi prestasi yang tidak memiliki SK Resmi dengan opsi approve tanpa SK disertai alasan dan dokumen alternatif.

## Database Changes

### Migration: `2026_01_21_050000_add_sk_waiver_fields_to_student_achievements.php`

**Fields Added:**
```php
$table->boolean('sk_required')->default(true);
$table->enum('sk_waiver_reason', [
    'tingkat_universitas',
    'sk_dalam_proses',
    'dokumen_alternatif',
    'lainnya'
])->nullable();
$table->text('sk_waiver_notes')->nullable();
$table->string('alternative_document_path')->nullable();
```

## Model Updates

### StudentAchievement Model

**New Fillable Fields:**
- `sk_required`
- `sk_waiver_reason`
- `sk_waiver_notes`
- `alternative_document_path`

**New Constants:**
```php
const SK_WAIVER_TINGKAT_UNIVERSITAS = 'tingkat_universitas';
const SK_WAIVER_SK_DALAM_PROSES = 'sk_dalam_proses';
const SK_WAIVER_DOKUMEN_ALTERNATIF = 'dokumen_alternatif';
const SK_WAIVER_LAINNYA = 'lainnya';
```

**New Method:**
```php
public static function getSkWaiverReasons(): array
{
    return [
        self::SK_WAIVER_TINGKAT_UNIVERSITAS => 'Prestasi tingkat universitas tidak memerlukan SK',
        self::SK_WAIVER_SK_DALAM_PROSES => 'SK sedang dalam proses',
        self::SK_WAIVER_DOKUMEN_ALTERNATIF => 'Menggunakan dokumen alternatif',
        self::SK_WAIVER_LAINNYA => 'Lainnya (jelaskan di catatan)',
    ];
}
```

## UI Changes

### Admin Submit Form (`resources/views/admin/achievements/submit.blade.php`)

**New Features:**
1. **Checkbox "Approve tanpa SK Resmi"**
   - Muncul saat action = "approve"
   - Jika dicentang, SK Resmi tidak wajib

2. **Conditional SK Upload**
   - Jika checkbox tidak dicentang: SK Resmi wajib (seperti sebelumnya)
   - Jika checkbox dicentang: SK Resmi tidak ditampilkan

3. **Waiver Options (muncul jika skip SK)**
   - Dropdown alasan:
     * Prestasi tingkat universitas tidak memerlukan SK
     * SK sedang dalam proses
     * Menggunakan dokumen alternatif
     * Lainnya
   
   - Textarea catatan (wajib jika "Lainnya")
   
   - Upload dokumen alternatif (wajib jika pilih "dokumen_alternatif")
     * Contoh: Surat Keterangan Fakultas, Surat Tugas, Berita Acara

### Validator Submit Form (`resources/views/validator/submit.blade.php`)

**Same features as Admin form** dengan warna emerald (bukan purple)

## Controller Updates

### AdminAchievementController::store()

**New Validation Rules:**
```php
'skip_sk' => 'nullable|boolean',
'sk_resmi' => 'required_if:submit_action,approve|required_unless:skip_sk,1|nullable|file|...',
'sk_waiver_reason' => 'required_if:skip_sk,1|nullable|in:...',
'sk_waiver_notes' => 'required_if:sk_waiver_reason,lainnya|nullable|string|...',
'alternative_document' => 'required_if:sk_waiver_reason,dokumen_alternatif|nullable|file|...',
```

**Logic:**
1. Determine `sk_required = !$request->boolean('skip_sk')`
2. Save waiver reason & notes to achievement
3. Upload SK Resmi if provided
4. Upload alternative document if provided (saved to `alternative_document_path`)
5. Create document record for alternative document
6. Log approval with notes indicating SK waiver

### ValidatorController::submitStore()

**Same logic as Admin** with validator role

## Business Rules

### SK Required by Level (Recommended Implementation)

**Internasional & Nasional:**
- SK Resmi WAJIB (tidak bisa skip)
- Checkbox "Approve tanpa SK" disabled atau tidak muncul

**Universitas:**
- SK Resmi OPSIONAL
- Bisa approve tanpa SK dengan alasan
- Bisa gunakan dokumen alternatif

### Waiver Reasons

1. **Tingkat Universitas**
   - Untuk prestasi internal kampus
   - Tidak memerlukan SK formal
   - Cukup sertifikat/piagam

2. **SK Dalam Proses**
   - SK sedang diproses oleh pihak terkait
   - Prestasi bisa diapprove dulu
   - SK bisa diupload kemudian

3. **Dokumen Alternatif**
   - Menggunakan dokumen pengganti SK
   - Wajib upload dokumen alternatif
   - Contoh: Surat Keterangan Fakultas, Surat Tugas, Berita Acara

4. **Lainnya**
   - Alasan khusus lainnya
   - Wajib isi catatan detail

## Document Types

### New Document Type: `dokumen_alternatif`

Ditambahkan ke `AchievementDocument::DOCUMENT_TYPES`:
```php
'dokumen_alternatif' => 'Dokumen Alternatif (Pengganti SK)',
```

## Validation Log

**Notes Format:**
- Dengan SK: "Disetujui langsung oleh admin saat submit"
- Tanpa SK: "Disetujui tanpa SK: [Alasan]"

Example:
- "Disetujui tanpa SK: Prestasi tingkat universitas tidak memerlukan SK"
- "Disetujui tanpa SK: Menggunakan dokumen alternatif"

## Display Changes

### Achievement Detail View

**Show SK Waiver Info:**
```blade
@if(!$achievement->sk_required)
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600">...</svg>
            <div>
                <p class="font-medium text-amber-800">Disetujui Tanpa SK Resmi</p>
                <p class="text-sm text-amber-700 mt-1">
                    Alasan: {{ $achievement->sk_waiver_reason_label }}
                </p>
                @if($achievement->sk_waiver_notes)
                    <p class="text-sm text-amber-600 mt-1">
                        Catatan: {{ $achievement->sk_waiver_notes }}
                    </p>
                @endif
                @if($achievement->alternative_document_path)
                    <a href="{{ Storage::url($achievement->alternative_document_path) }}" 
                       class="text-sm text-amber-600 hover:text-amber-700 underline mt-2 inline-block">
                        Lihat Dokumen Alternatif
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
```

### History/Riwayat View

**Badge Indicator:**
```blade
@if(!$achievement->sk_required)
    <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs rounded-full">
        Tanpa SK
    </span>
@endif
```

## Testing Scenarios

### Test Case 1: Approve dengan SK Resmi (Normal Flow)
1. Pilih action "Approve"
2. Jangan centang "Approve tanpa SK"
3. Upload SK Resmi (wajib)
4. Submit
5. ✅ Prestasi approved dengan SK

### Test Case 2: Approve Tanpa SK - Tingkat Universitas
1. Pilih action "Approve"
2. Centang "Approve tanpa SK Resmi"
3. Pilih alasan "Prestasi tingkat universitas"
4. Submit (tanpa SK)
5. ✅ Prestasi approved tanpa SK

### Test Case 3: Approve Tanpa SK - Dokumen Alternatif
1. Pilih action "Approve"
2. Centang "Approve tanpa SK Resmi"
3. Pilih alasan "Menggunakan dokumen alternatif"
4. Upload dokumen alternatif (wajib)
5. Submit
6. ✅ Prestasi approved dengan dokumen alternatif

### Test Case 4: Approve Tanpa SK - Lainnya
1. Pilih action "Approve"
2. Centang "Approve tanpa SK Resmi"
3. Pilih alasan "Lainnya"
4. Isi catatan (wajib)
5. Submit
6. ✅ Prestasi approved dengan catatan

### Test Case 5: Validation Error
1. Pilih action "Approve"
2. Centang "Approve tanpa SK Resmi"
3. Pilih alasan "Lainnya"
4. Tidak isi catatan
5. Submit
6. ❌ Error: Catatan wajib diisi

## Future Enhancements

### Phase 2: Auto-determine SK Required
```php
// In controller before save
$skRequired = match($validated['level']) {
    'Internasional', 'Nasional' => true,
    'Universitas' => false,
    default => true,
};
```

### Phase 3: Notification System
- Email ke mahasiswa jika approved tanpa SK
- Reminder untuk upload SK jika "SK dalam proses"

### Phase 4: Reporting
- Dashboard: Jumlah prestasi tanpa SK
- Filter: Prestasi dengan/tanpa SK
- Export: Include SK waiver info

## Files Modified

**Migrations (1):**
- `database/migrations/2026_01_21_050000_add_sk_waiver_fields_to_student_achievements.php`

**Models (1):**
- `app/Models/StudentAchievement.php`

**Controllers (2):**
- `app/Http/Controllers/Admin/AdminAchievementController.php`
- `app/Http/Controllers/ValidatorController.php` (TODO)

**Views (2):**
- `resources/views/admin/achievements/submit.blade.php`
- `resources/views/validator/submit.blade.php` (TODO)

## Migration Command

```bash
php artisan migrate
```

---

**Status:** ✅ Partially Complete (Admin form done, Validator form TODO)
**Tanggal:** 21 Januari 2026
