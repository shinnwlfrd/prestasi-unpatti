# Testing Guide: Admin Submit Achievement Feature

## Overview
Admin can now submit achievements on behalf of students with three action options:
1. **Pending** - Save as "Menunggu" and redirect to document upload
2. **Approve** - Directly approve with SK Resmi upload (required)
3. **Reject** - Directly reject with reason (required)

## Prerequisites
✅ Database migrations run
✅ Categories seeded (Akademik, Non-Akademik)
✅ Levels seeded (Universitas, Nasional, Internasional)
✅ Admin user exists (admin@unpatti.ac.id / password)
✅ At least one student exists in database

## Test Cases

### 1. Access Admin Submit Form
**Steps:**
1. Login as admin (admin@unpatti.ac.id / password)
2. Navigate to sidebar → "Aksi" section → "Ajukan Prestasi"
3. Or directly access: `/admin/submit-achievement`

**Expected Result:**
- Form displays with all fields
- Student dropdown populated
- Category dropdown populated (from achievements table)
- Level radio buttons show: Universitas, Nasional, Internasional with points
- Three action options visible: Pending, Approve, Reject

---

### 2. Test Pending Action (Upload Documents Later)
**Steps:**
1. Fill all required fields:
   - Select a student
   - Select achievement category
   - Enter event name
   - Select level (e.g., Nasional)
   - Enter organizer
   - Select event date
   - Upload certificate (PDF/JPG/PNG, max 5MB)
2. Select action: **Pending**
3. Click "Ajukan Prestasi"

**Expected Result:**
- Success message: "Prestasi mahasiswa berhasil diajukan. Anda dapat menambahkan dokumen tambahan di bawah ini."
- Redirected to document upload page
- Achievement saved with status "Menunggu"
- Can upload additional documents (Sertifikat, Foto Dokumentasi, etc.)
- SK Resmi NOT available in document types (admin/validator only)

---

### 3. Test Approve Action (Direct Approval with SK)
**Steps:**
1. Fill all required fields (same as Test 2)
2. Select action: **Approve**
3. Notice SK Resmi upload field appears (green background)
4. Upload SK Resmi file (PDF/JPG/PNG, max 10MB)
5. Click "Ajukan Prestasi"

**Expected Result:**
- Success message: "Prestasi berhasil diajukan dan langsung disetujui."
- Redirected to admin validation index
- Achievement saved with status "Disetujui"
- SK Resmi document created with status "approved"
- Validation log created with action "approve"

**Validation:**
- SK Resmi is REQUIRED - form should not submit without it
- HTML5 validation: `required_if:submit_action,approve`
- Server-side validation enforces this

---

### 4. Test Reject Action (Direct Rejection)
**Steps:**
1. Fill all required fields (same as Test 2)
2. Select action: **Reject**
3. Notice rejection reason textarea appears (red background)
4. Enter rejection reason (e.g., "Dokumen tidak lengkap")
5. Click "Ajukan Prestasi"

**Expected Result:**
- Success message: "Prestasi berhasil diajukan dan langsung ditolak."
- Redirected to admin validation index
- Achievement saved with status "Ditolak"
- Validation log created with rejection reason

**Validation:**
- Rejection reason is REQUIRED - form should not submit without it
- HTML5 validation: `required_if:submit_action,reject`
- Server-side validation enforces this

---

### 5. Test Form Validation
**Test Missing Required Fields:**
1. Try to submit without selecting student → Error
2. Try to submit without certificate → Error
3. Try Approve without SK Resmi → Error
4. Try Reject without reason → Error

**Test File Upload Validation:**
1. Try uploading certificate > 5MB → Error
2. Try uploading SK Resmi > 10MB → Error
3. Try uploading invalid file type (e.g., .txt) → Error

---

### 6. Test Alpine.js Conditional Fields
**Steps:**
1. Open form
2. Select "Pending" → No additional fields
3. Select "Approve" → Green box with SK upload appears
4. Select "Reject" → Red box with rejection reason appears
5. Switch between options → Fields show/hide correctly

**Expected Result:**
- Conditional fields appear/disappear smoothly
- Required validation adjusts based on selected action
- No JavaScript errors in console

---

### 7. Verify Admin Validation Page (Same as Validator)
**Steps:**
1. Go to "Validasi" → "Validasi Prestasi"
2. Click on any pending achievement
3. Verify form has:
   - Checklist validation (6 items)
   - Three buttons: Approve, Reject, Revisi
   - Approve button opens modal for SK upload
   - Reject button focuses to rejection reason textarea
   - Revisi button focuses to revision reason textarea

**Expected Result:**
- Admin validation page identical to validator page
- All three actions work correctly
- SK upload modal appears when clicking Approve
- No alert popups for Reject/Revisi (just focus to textarea)

---

### 8. Test CRUD Categories
**Steps:**
1. Navigate to "Master Data" → "Kategori Prestasi"
2. Click "Tambah Kategori"
3. Create new category (e.g., "Olahraga")
4. Edit existing category
5. Toggle active/inactive status
6. Try to delete (if implemented)

**Expected Result:**
- All CRUD operations work
- Flash messages appear for success/error
- Categories appear in admin submit form dropdown

---

### 9. Test CRUD Levels
**Steps:**
1. Navigate to "Master Data" → "Level Prestasi"
2. Click "Tambah Level"
3. Create new level with points (e.g., "Regional - 50 poin")
4. Edit existing level
5. Toggle active/inactive status

**Expected Result:**
- All CRUD operations work
- Flash messages appear for success/error
- Levels appear in admin submit form as radio buttons
- Points display correctly

---

### 10. Integration Test: Full Workflow
**Scenario: Admin submits achievement and approves it**

1. Admin submits achievement with "Pending" action
2. Redirected to document upload page
3. Upload additional documents (Foto Dokumentasi, Surat Keterangan)
4. Go back to validation page
5. Click on the achievement
6. Fill checklist validation
7. Click "Approve" button
8. Modal opens for SK upload
9. Upload SK Resmi
10. Add optional notes
11. Click "Approve Prestasi"

**Expected Result:**
- Achievement status changes from "Menunggu" to "Disetujui"
- SK Resmi document created and approved
- Validation log created
- Student can see approved achievement in their dashboard
- All documents visible with correct status

---

## Common Issues & Solutions

### Issue: Categories/Levels not showing
**Solution:** Run seeders:
```bash
php artisan db:seed --class=AchievementCategorySeeder
php artisan db:seed --class=AchievementLevelSeeder
```

### Issue: Views not updating
**Solution:** Clear view cache:
```bash
php artisan view:clear
```

### Issue: SK upload not working
**Solution:** 
- Check storage permissions
- Verify `storage/app/public` is linked: `php artisan storage:link`
- Check file size limits in php.ini

### Issue: Form validation not working
**Solution:**
- Check browser console for JavaScript errors
- Verify Alpine.js is loaded
- Check CSRF token is present

---

## Success Criteria
✅ All three action options work correctly
✅ SK upload is mandatory for approve action
✅ Rejection reason is mandatory for reject action
✅ Conditional fields show/hide based on selected action
✅ Flash messages display correctly
✅ Redirects work as expected
✅ Admin validation page matches validator page
✅ CRUD operations for categories and levels work
✅ No JavaScript errors in console
✅ No PHP errors in logs

---

## Notes
- Admin submit form uses same validation rules as student/validator
- SK Resmi can only be uploaded by admin/validator (not students)
- Status values use Indonesian: "Menunggu", "Disetujui", "Ditolak", "Revisi"
- All forms support dark mode
- Flash messages auto-dismiss with close button
