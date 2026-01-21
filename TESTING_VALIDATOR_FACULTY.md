# Testing Guide: Validator Faculty Restriction

## Prerequisites
- System has 12 users (11 validators, 1 admin)
- Migration for faculty field has been run
- All controllers and views updated

## Test Scenarios

### Test 1: User Management Interface
**Objective**: Verify admin can manage users with faculty assignment

#### Steps:
1. Login as Admin
2. Navigate to "Kelola User" menu
3. Click "Tambah User" button
4. Fill form:
   - Nama: Test Validator FT
   - Email: validator.ft@test.com
   - Password: password123
   - Role: Validator
   - Fakultas: Fakultas Teknik
   - Status: Active
5. Click "Simpan"

**Expected Result**:
- User created successfully
- User appears in table with "Fakultas Teknik" badge
- Success message displayed

#### Steps (Edit):
1. Click "Edit" button on the newly created user
2. Change Fakultas to "Fakultas Ekonomi dan Bisnis"
3. Click "Simpan"

**Expected Result**:
- User updated successfully
- Faculty badge changes to "Fakultas Ekonomi dan Bisnis"
- Success message displayed

#### Steps (Super Validator):
1. Click "Edit" button on a validator
2. Change Fakultas to "Semua Fakultas"
3. Click "Simpan"

**Expected Result**:
- User updated successfully
- Faculty badge shows "Semua Fakultas"
- Validator can now access all faculties

---

### Test 2: Faculty Filtering on Dashboard
**Objective**: Verify validators only see achievements from their faculty

#### Setup:
1. Create 2 validators:
   - Validator A: faculty = "Fakultas Teknik"
   - Validator B: faculty = "Fakultas Ekonomi dan Bisnis"
2. Create 2 pending achievements:
   - Achievement 1: Student from Fakultas Teknik
   - Achievement 2: Student from Fakultas Ekonomi dan Bisnis

#### Steps (Validator A):
1. Login as Validator A (Fakultas Teknik)
2. Navigate to Dashboard

**Expected Result**:
- Only Achievement 1 (Fakultas Teknik) is visible
- Achievement 2 (Fakultas Ekonomi dan Bisnis) is NOT visible
- Counter shows correct number

#### Steps (Validator B):
1. Login as Validator B (Fakultas Ekonomi dan Bisnis)
2. Navigate to Dashboard

**Expected Result**:
- Only Achievement 2 (Fakultas Ekonomi dan Bisnis) is visible
- Achievement 1 (Fakultas Teknik) is NOT visible
- Counter shows correct number

---

### Test 3: 403 Error on Unauthorized Access
**Objective**: Verify validators cannot access other faculty's achievements

#### Setup:
1. Login as Validator A (Fakultas Teknik)
2. Get URL of Achievement 2 (Fakultas Ekonomi dan Bisnis)
   - Example: `/validator/achievements/123`

#### Steps:
1. Manually navigate to Achievement 2 URL
2. Try to access the detail page

**Expected Result**:
- 403 Forbidden error displayed
- Error message: "Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain."
- Cannot view achievement details
- Cannot validate the achievement

---

### Test 4: Faculty Filtering on History
**Objective**: Verify validators only see validation history from their faculty

#### Setup:
1. Validator A validates Achievement 1 (Fakultas Teknik)
2. Validator B validates Achievement 2 (Fakultas Ekonomi dan Bisnis)

#### Steps (Validator A):
1. Login as Validator A
2. Navigate to "Riwayat Validasi"

**Expected Result**:
- Only validation log for Achievement 1 is visible
- Validation log for Achievement 2 is NOT visible

#### Steps (Validator B):
1. Login as Validator B
2. Navigate to "Riwayat Validasi"

**Expected Result**:
- Only validation log for Achievement 2 is visible
- Validation log for Achievement 1 is NOT visible

---

### Test 5: Super Validator Access
**Objective**: Verify super validators can access all faculties

#### Setup:
1. Create Validator C with faculty = null (Semua Fakultas)
2. Ensure there are achievements from multiple faculties

#### Steps:
1. Login as Validator C
2. Navigate to Dashboard

**Expected Result**:
- All pending achievements from ALL faculties are visible
- No filtering applied
- Can access any achievement detail
- Can validate any achievement

#### Steps (Validation):
1. Click on Achievement 1 (Fakultas Teknik)
2. Validate the achievement

**Expected Result**:
- Can access detail page (no 403 error)
- Can validate successfully
- Validation log created

---

### Test 6: Validation Action with Faculty Check
**Objective**: Verify validation actions respect faculty restrictions

#### Setup:
1. Login as Validator A (Fakultas Teknik)
2. Get Achievement 2 ID (Fakultas Ekonomi dan Bisnis)

#### Steps:
1. Try to POST validation to Achievement 2
   - URL: `/validator/achievements/123/validate`
   - Method: POST
   - Data: action=approve, notes=test

**Expected Result**:
- 403 Forbidden error
- Validation NOT processed
- Achievement status unchanged
- No validation log created

---

### Test 7: Modal Behavior
**Objective**: Verify faculty field shows/hides based on role

#### Steps (Add User):
1. Login as Admin
2. Click "Tambah User"
3. Select Role: Admin

**Expected Result**:
- Faculty field is HIDDEN

#### Steps:
1. Change Role to: Validator

**Expected Result**:
- Faculty field is VISIBLE
- Dropdown shows 9 faculties + "Semua Fakultas"

#### Steps (Edit User):
1. Click "Edit" on an Admin user

**Expected Result**:
- Faculty field is HIDDEN
- Cannot assign faculty to Admin

#### Steps:
1. Click "Edit" on a Validator user

**Expected Result**:
- Faculty field is VISIBLE
- Current faculty is pre-selected
- Can change faculty

---

## Database Verification

### Check User Faculty Assignment:
```sql
SELECT id, name, email, role, faculty, is_active 
FROM users 
WHERE role = 'Validator';
```

### Check Achievement Distribution:
```sql
SELECT s.faculty, COUNT(*) as total
FROM student_achievements sa
JOIN students s ON sa.student_id = s.student_id
WHERE sa.validation_status = 'Menunggu'
GROUP BY s.faculty;
```

### Check Validation Logs by Faculty:
```sql
SELECT s.faculty, COUNT(*) as total
FROM validation_logs vl
JOIN student_achievements sa ON vl.sa_id = sa.sa_id
JOIN students s ON sa.student_id = s.student_id
GROUP BY s.faculty;
```

---

## Common Issues & Solutions

### Issue 1: Faculty field not showing in modal
**Solution**: Check Alpine.js is loaded, verify x-show="modalData.role === 'Validator'" syntax

### Issue 2: All achievements visible to faculty validator
**Solution**: Check validator has faculty assigned (not null), verify whereHas query in controller

### Issue 3: 403 error not showing
**Solution**: Verify abort(403) is called, check if validator has faculty assigned

### Issue 4: Super validator cannot see all achievements
**Solution**: Verify faculty is null (not empty string), check if condition in controller

### Issue 5: Faculty not saving on user create/update
**Solution**: Check faculty is in fillable array, verify validation rules include faculty

---

## Success Criteria
✅ Admin can create/edit users with faculty assignment
✅ Faculty dropdown shows 9 faculties + "Semua Fakultas"
✅ Faculty field shows/hides based on role
✅ Validators only see achievements from their faculty
✅ Validators cannot access other faculty's achievements (403)
✅ Validators only see validation history from their faculty
✅ Super validators (faculty = null) can access all faculties
✅ Validation actions respect faculty restrictions
✅ No errors in browser console
✅ No PHP errors in logs

---

## Quick Test Commands

### Create test validator with faculty:
```bash
php artisan tinker
```
```php
User::create([
    'name' => 'Test Validator FT',
    'email' => 'validator.ft@test.com',
    'password' => Hash::make('password123'),
    'role' => 'Validator',
    'faculty' => 'Fakultas Teknik',
    'is_active' => true,
]);
```

### Create super validator:
```bash
php artisan tinker
```
```php
User::create([
    'name' => 'Super Validator',
    'email' => 'validator.super@test.com',
    'password' => Hash::make('password123'),
    'role' => 'Validator',
    'faculty' => null,
    'is_active' => true,
]);
```

### Check validator's accessible achievements:
```bash
php artisan tinker
```
```php
$validator = User::where('email', 'validator.ft@test.com')->first();
$count = StudentAchievement::whereIn('validation_status', ['pending', 'Menunggu'])
    ->whereHas('student', function($q) use ($validator) {
        $q->where('faculty', $validator->faculty);
    })->count();
echo "Accessible achievements: $count";
```

---

## Notes
- Faculty field is nullable (allows super validators)
- Faculty filtering only applies to Validator role
- Admin users do not have faculty restrictions
- Students are not affected by this feature
- Faculty names must match exactly between users and students
