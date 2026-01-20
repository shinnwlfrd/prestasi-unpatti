# Fix: Data Truncated for validation_status Column

## Error
```
SQLSTATE[01000]: Warning: 1265 Data truncated for column 'validation_status' at row 1
SQL: update `student_achievements` set `validation_status` = approved, ...
```

## Root Cause
Mismatch antara nilai ENUM di database dengan konstanta di model:

### Database ENUM (Bahasa Indonesia):
```sql
ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Revisi')
```

### Model Constants (Bahasa Inggris - SALAH):
```php
const STATUS_PENDING = 'pending';
const STATUS_APPROVED = 'approved';
const STATUS_REJECTED = 'rejected';
const STATUS_NEED_REVISION = 'need_revision';
```

Saat service mencoba update dengan nilai `'approved'`, database menolak karena tidak ada dalam ENUM.

## Solution
Updated semua konstanta dan query untuk menggunakan bahasa Indonesia sesuai ENUM database.

### 1. Model Constants (Fixed)
```php
// app/Models/StudentAchievement.php
const STATUS_PENDING = 'Menunggu';
const STATUS_APPROVED = 'Disetujui';
const STATUS_REJECTED = 'Ditolak';
const STATUS_NEED_REVISION = 'Revisi';
```

### 2. ValidatorController (Fixed)
```php
// Before
'validation_status' => 'approved',  // ❌ Error

// After
'validation_status' => 'Disetujui',  // ✅ OK
```

### 3. StudentController Stats (Fixed)
```php
// Before
$approved = $achievements->where('validation_status', 'approved')->count();

// After
$approved = $achievements->where('validation_status', 'Disetujui')->count();
```

### 4. Student Dashboard View (Fixed)
```php
// Before
$approved = $studentAchievements->where('validation_status', 'approved')->count();
@if($item->validation_status === 'rejected')

// After
$approved = $studentAchievements->where('validation_status', 'Disetujui')->count();
@if($item->validation_status === 'Ditolak')
```

## Files Modified

1. **app/Models/StudentAchievement.php**
   - Updated STATUS constants to Indonesian

2. **app/Http/Controllers/ValidatorController.php**
   - Changed 'approved' → 'Disetujui'
   - Changed 'rejected' → 'Ditolak'

3. **app/Http/Controllers/StudentController.php**
   - Updated stats query to use Indonesian status

4. **resources/views/student/dashboard.blade.php**
   - Updated stats calculation
   - Updated status comparison

## Status Mapping

| English (Old) | Indonesian (New) | Badge Color |
|---------------|------------------|-------------|
| pending | Menunggu | warning (yellow) |
| approved | Disetujui | success (green) |
| rejected | Ditolak | danger (red) |
| need_revision | Revisi | info (blue) |

## Why Indonesian?

Database ENUM sudah menggunakan bahasa Indonesia sejak awal. Mengubah ENUM database akan lebih kompleks karena:
1. Perlu migration untuk alter column
2. Perlu update semua existing data
3. Risk data corruption

Lebih mudah dan aman mengubah kode untuk match dengan database.

## Testing

### Test 1: Approve Achievement
1. Login sebagai validator
2. Buka halaman validasi
3. Klik "Approve"
4. **Expected**: ✅ Success, no SQL error
5. Check database: `validation_status` = 'Disetujui'

### Test 2: Reject Achievement
1. Login sebagai validator
2. Buka halaman validasi
3. Isi alasan penolakan
4. Klik "Reject"
5. **Expected**: ✅ Success, no SQL error
6. Check database: `validation_status` = 'Ditolak'

### Test 3: Request Revision
1. Login sebagai validator
2. Buka halaman validasi
3. Isi alasan revisi
4. Klik "Revisi"
5. **Expected**: ✅ Success, no SQL error
6. Check database: `validation_status` = 'Revisi'

### Test 4: Student Dashboard Stats
1. Login sebagai student
2. View dashboard
3. **Expected**: ✅ Stats show correct counts
4. No SQL errors in log

### Test 5: Status Badge Display
1. View any achievement list
2. **Expected**: ✅ Status badges show correct colors
3. Labels show Indonesian text

## Database Verification

Check current ENUM values:
```sql
SHOW COLUMNS FROM student_achievements LIKE 'validation_status';
```

Expected result:
```
Type: enum('Menunggu','Disetujui','Ditolak','Revisi')
```

Check existing data:
```sql
SELECT validation_status, COUNT(*) 
FROM student_achievements 
GROUP BY validation_status;
```

Expected values:
- Menunggu
- Disetujui
- Ditolak
- Revisi

## Impact

### Before Fix
- ❌ SQL error saat approve/reject
- ❌ Data truncated warning
- ❌ Status tidak tersimpan dengan benar
- ❌ Stats tidak akurat

### After Fix
- ✅ No SQL errors
- ✅ Status tersimpan dengan benar
- ✅ Stats akurat
- ✅ Badge colors correct
- ✅ Labels in Indonesian

## Additional Notes

### Service Layer
`AchievementApprovalService` sudah menggunakan konstanta dari model:
```php
$achievement->update([
    'validation_status' => StudentAchievement::STATUS_APPROVED,
]);
```

Karena konstanta sudah diperbaiki, service otomatis menggunakan nilai yang benar.

### Accessor Methods
Status badge dan label accessors sudah benar karena menggunakan konstanta:
```php
public function getStatusBadgeAttribute(): string
{
    return match($this->validation_status) {
        self::STATUS_PENDING => 'warning',
        self::STATUS_APPROVED => 'success',
        // ...
    };
}
```

### Backward Compatibility
Jika ada data lama dengan status bahasa Inggris, perlu migration untuk update:
```sql
UPDATE student_achievements 
SET validation_status = CASE 
    WHEN validation_status = 'pending' THEN 'Menunggu'
    WHEN validation_status = 'approved' THEN 'Disetujui'
    WHEN validation_status = 'rejected' THEN 'Ditolak'
    WHEN validation_status = 'need_revision' THEN 'Revisi'
    ELSE validation_status
END;
```

## Prevention

To prevent similar issues in the future:

1. **Always check ENUM values** in migration before using
2. **Use constants** instead of hardcoded strings
3. **Test with actual database** not just in-memory
4. **Document ENUM values** in model comments
5. **Use database seeder** to verify ENUM values work

## Related Issues

This fix also resolves:
- Stats not showing correct counts
- Status filters not working
- Badge colors not matching status
- Appeal button not showing for rejected achievements

---

**Date**: 20 Januari 2026
**Status**: ✅ FIXED
**Issue**: ENUM mismatch between database and code
**Solution**: Updated code to match database ENUM values
