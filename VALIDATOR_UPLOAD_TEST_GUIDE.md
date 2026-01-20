# Testing Guide: Validator Document Upload

## Quick Test Steps

### Test 1: Validator Can Access Document Upload
1. Login as validator (validator@unpatti.ac.id / password)
2. Go to validator dashboard
3. Find any pending achievement
4. Click the **green upload button** (cloud icon) in the Actions column
5. ✅ Should see document upload page with green info box saying "Mode Validator/Admin"

### Test 2: Validator Can Upload SK Resmi
1. On the document upload page (as validator)
2. Drag & drop or select a PDF file
3. In the document type dropdown, check if **"SK Resmi"** option is available
4. Select "SK Resmi" as document type
5. Click "Upload Dokumen"
6. ✅ Should successfully upload SK Resmi

### Test 3: Student Cannot Upload SK Resmi
1. Login as student (use SSO or student session)
2. Go to student dashboard
3. Click "Upload Dokumen" on any achievement
4. Try to upload a document
5. In the document type dropdown, check that **"SK Resmi" is NOT available**
6. ✅ Should see blue info box saying SK will be uploaded by validator

### Test 4: Validator Submit Flow
1. Login as validator
2. Click "Ajukan Prestasi" button
3. Fill the form:
   - Select a student
   - Select achievement category
   - Fill event details
   - Upload certificate
4. Click "Ajukan Prestasi"
5. ✅ Should redirect to document upload page (not dashboard)
6. ✅ Should see success message about adding more documents
7. Upload additional documents (SK Resmi, etc.)
8. Click back button
9. ✅ Should return to validator dashboard

### Test 5: Back Button Works Correctly
1. As validator: Back button should go to validator dashboard
2. As student: Back button should go to student dashboard

### Test 6: Authorization Check
1. Try to access document upload URL directly without login
2. ✅ Should redirect to login page
3. As student, try to access another student's achievement documents
4. ✅ Should get 403 Forbidden error

## Expected Results Summary

| User Type | Can Access Upload Page | Can Upload SK Resmi | Back Button Destination |
|-----------|----------------------|-------------------|------------------------|
| Student | ✅ Yes (own achievements) | ❌ No | Student Dashboard |
| Validator | ✅ Yes (all achievements) | ✅ Yes | Validator Dashboard |
| Admin | ✅ Yes (all achievements) | ✅ Yes | Admin Dashboard |

## Document Types by Role

### Students Can Upload:
- ✅ Sertifikat
- ✅ Foto Dokumentasi
- ✅ Surat Keterangan
- ✅ Link Publikasi
- ✅ Dokumen Lainnya
- ❌ SK Resmi (restricted)

### Validators/Admins Can Upload:
- ✅ Sertifikat
- ✅ Foto Dokumentasi
- ✅ Surat Keterangan
- ✅ Link Publikasi
- ✅ Dokumen Lainnya
- ✅ SK Resmi (full access)

## Troubleshooting

### Issue: Validator sees "SK Resmi will be uploaded by validator" message
**Solution**: Clear browser cache and view cache (`php artisan view:clear`)

### Issue: 403 Forbidden when accessing document upload
**Solution**: Check that routes are using `auth.any` middleware, not `auth.student`

### Issue: Back button goes to wrong dashboard
**Solution**: Clear view cache and check that `$backRoute` variable is set correctly

### Issue: Document type dropdown doesn't show SK Resmi for validator
**Solution**: Check that `$isValidatorOrAdmin` is being passed to view and is true

## Files Modified

1. `routes/web.php` - Moved document routes to auth.any
2. `app/Http/Controllers/DocumentUploadController.php` - Added role detection
3. `app/Http/Controllers/ValidatorController.php` - Changed redirect after submit
4. `resources/views/achievements/documents/index.blade.php` - Dynamic UI based on role
5. `resources/views/validator/dashboard.blade.php` - Added upload button

## Success Criteria

✅ Validators can upload documents like students
✅ Validators can upload SK Resmi
✅ Students still cannot upload SK Resmi
✅ Back buttons work correctly for each role
✅ Authorization prevents unauthorized access
✅ After validator submits achievement, can immediately add more documents
