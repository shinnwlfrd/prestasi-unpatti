# Validator Faculty Restriction & User Management Enhancement

## Overview
Implemented faculty-based access control for validators and enhanced user management interface.

## Changes Made

### 1. Database Schema
**Migration**: `database/migrations/2026_01_21_060000_add_faculty_to_users_table.php`
- Added `faculty` field to users table (nullable string)
- Allows validators to be assigned to specific faculties

### 2. User Model
**File**: `app/Models/User.php`
- Added `faculty` to fillable fields
- Validators can now have faculty assignment

### 3. Validator Controller - Faculty Filtering
**File**: `app/Http/Controllers/ValidatorController.php`

#### dashboard()
- Filters pending achievements by validator's faculty
- Only shows achievements from students in the same faculty
- If faculty is null, validator can see all faculties (super validator)

#### history()
- Filters validation logs by validator's faculty
- Only shows validation history for achievements from the same faculty

#### show()
- Checks faculty access before displaying achievement detail
- Returns 403 error if validator tries to access other faculty's achievement
- Error message: "Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain."

#### validateAchievement()
- Checks faculty access before processing validation
- Prevents validators from approving/rejecting achievements from other faculties
- Returns 403 error if access denied

### 4. Admin Controller - User Management
**File**: `app/Http/Controllers/AdminController.php`

#### storeUser()
- Added validation for `faculty` field (nullable)
- Added validation for `is_active` field (boolean)
- Creates user with faculty assignment

#### updateUser() - NEW METHOD
- Validates all user fields including faculty
- Handles password update (only if provided)
- Updates user data including faculty assignment
- Returns success message

### 5. Routes
**File**: `routes/web.php`
- Added PUT route: `/admin/users/{user}` → `AdminController@updateUser`

### 6. User Management View
**File**: `resources/views/admin/users/index.blade.php`

#### Features:
- Modern table design with user avatars
- Add/Edit modal with Alpine.js
- Faculty dropdown for validators with 9 options:
  1. Fakultas Teknik
  2. Fakultas Ekonomi dan Bisnis
  3. Fakultas Hukum
  4. Fakultas Ilmu Sosial dan Ilmu Politik
  5. Fakultas Pertanian
  6. Fakultas Kedokteran
  7. Fakultas Keguruan dan Ilmu Pendidikan
  8. Fakultas Perikanan dan Ilmu Kelautan
  9. Fakultas MIPA
- "Semua Fakultas" option (faculty = null) for super validators
- Status toggle (is_active)
- SSO indicator badge
- Edit and Delete actions
- Responsive design

#### Modal Behavior:
- Add mode: Empty form, password required
- Edit mode: Pre-filled form, password optional
- Faculty field only shown for Validator role
- Auto-hides faculty field when Admin role selected

## Faculty Options
```php
$faculties = [
    'Fakultas Teknik',
    'Fakultas Ekonomi dan Bisnis',
    'Fakultas Hukum',
    'Fakultas Ilmu Sosial dan Ilmu Politik',
    'Fakultas Pertanian',
    'Fakultas Kedokteran',
    'Fakultas Keguruan dan Ilmu Pendidikan',
    'Fakultas Perikanan dan Ilmu Kelautan',
    'Fakultas MIPA',
];
```

## Access Control Logic

### For Validators with Faculty Assignment:
```php
if ($user->role === 'Validator' && $user->faculty) {
    // Filter achievements by student's faculty
    $query->whereHas('student', function($q) use ($user) {
        $q->where('faculty', $user->faculty);
    });
}
```

### For Super Validators (faculty = null):
- Can validate achievements from all faculties
- No filtering applied
- Full access to all pending achievements

### 403 Error Handling:
```php
if ($user->role === 'Validator' && $user->faculty) {
    if ($achievement->student->faculty !== $user->faculty) {
        abort(403, 'Anda tidak memiliki akses untuk validasi prestasi dari fakultas lain.');
    }
}
```

## Testing Scenarios

### 1. Validator with Faculty Assignment
- Create validator with faculty = "Fakultas Teknik"
- Login as that validator
- Should only see achievements from Fakultas Teknik students
- Attempting to access other faculty's achievement should return 403

### 2. Super Validator (No Faculty)
- Create validator with faculty = null
- Login as that validator
- Should see all achievements from all faculties
- Can validate any achievement

### 3. User Management
- Admin can create new validator with faculty
- Admin can edit existing validator's faculty
- Admin can change validator to super validator (set faculty to null)
- Admin can toggle user active status

## Security Notes
- Faculty check is performed at controller level
- 403 errors prevent unauthorized access
- Faculty filtering applied to all validator queries
- Super validators (faculty = null) have full access

## UI/UX Improvements
- Modern card-based layout
- Avatar placeholders for users
- Color-coded role badges (Admin = blue, Validator = emerald)
- SSO indicator badge
- Status toggle (Active/Inactive)
- Responsive modal with Alpine.js
- Faculty dropdown with clear "Semua Fakultas" option

## Status
✅ Migration created and run
✅ User model updated
✅ ValidatorController faculty filtering implemented
✅ AdminController storeUser updated
✅ AdminController updateUser created
✅ Routes added
✅ User management view enhanced
✅ Faculty access control working

## Next Steps (Optional Enhancements)
1. Add faculty field to student registration/import
2. Create faculty management page (master data)
3. Add faculty statistics to admin dashboard
4. Add faculty filter to admin achievement list
5. Create faculty-based reports
