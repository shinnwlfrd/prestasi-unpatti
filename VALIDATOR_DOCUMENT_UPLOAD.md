# Validator Document Upload Feature

## Overview
Validators can now upload documents for student achievements just like students can. This allows validators to add additional supporting documents, certificates, and SK Resmi when needed.

## Changes Made

### 1. Route Updates (`routes/web.php`)
- **Moved document upload routes** from `auth.student` middleware to `auth.any` middleware
- This allows both students AND validators/admins to access document upload functionality
- Routes affected:
  - `GET /achievements/{achievement}/documents` - View document upload page
  - `POST /achievements/{achievement}/documents` - Upload multiple documents
  - `POST /achievements/{achievement}/documents/upload` - Upload single document (AJAX)
  - `POST /achievements/{achievement}/documents/submit` - Submit all draft documents
  - `POST /documents/{document}/replace` - Replace existing document
  - `POST /documents/{document}/submit` - Submit single document
  - `DELETE /documents/{document}` - Delete document

### 2. Controller Updates (`app/Http/Controllers/DocumentUploadController.php`)

#### `index()` Method
- Added detection for validator/admin users
- **Students**: Can upload all document types EXCEPT SK Resmi
- **Validators/Admins**: Can upload ALL document types INCLUDING SK Resmi
- Passes `$isValidatorOrAdmin` flag to view

#### `authorizeDocumentAccess()` Method
- Already supports both regular auth (validators/admins) and student session auth
- Validators/admins can access all achievement documents
- Students can only access their own achievement documents

### 3. View Updates (`resources/views/achievements/documents/index.blade.php`)

#### Info Box
- Shows different messages based on user role:
  - **Students**: Blue info box explaining SK Resmi will be uploaded by validator
  - **Validators/Admins**: Green info box explaining they can upload all document types

#### Back Button
- Dynamically determines back route based on user role:
  - **Validators**: Returns to `validator.dashboard`
  - **Students**: Returns to `student.dashboard`

#### Cancel Button
- Also uses dynamic back route for consistency

### 4. Validator Dashboard Updates (`resources/views/validator/dashboard.blade.php`)
- Added new "Upload Dokumen" button (green) next to existing action buttons
- Button links to `achievements.documents.index` route
- Icon: Upload cloud icon
- Allows validators to quickly access document upload for any achievement

### 5. Validator Controller Updates (`app/Http/Controllers/ValidatorController.php`)

#### `submitStore()` Method
- Changed redirect destination after creating achievement
- **Before**: Redirected to `validator.dashboard`
- **After**: Redirects to `achievements.documents.index` with success message
- This allows validators to immediately add more documents after initial submission

## User Flow

### For Validators

1. **Submit New Achievement**
   - Go to "Ajukan Prestasi" from validator dashboard
   - Fill form and upload initial certificate
   - Click "Ajukan Prestasi"
   - **Automatically redirected to document upload page**
   - Can add more documents (SK Resmi, additional certificates, etc.)

2. **Add Documents to Existing Achievement**
   - From validator dashboard, find the achievement
   - Click the green "Upload Dokumen" button (cloud icon)
   - Upload any document type including SK Resmi
   - Documents are available for verification

3. **Document Types Available to Validators**
   - ✅ Sertifikat
   - ✅ SK Resmi (ONLY validators/admins)
   - ✅ Foto Dokumentasi
   - ✅ Surat Keterangan
   - ✅ Link Publikasi
   - ✅ Dokumen Lainnya

### For Students

1. **Upload Documents**
   - From student dashboard, click "Upload Dokumen" on achievement
   - Upload certificates and supporting documents
   - **Cannot upload SK Resmi** (reserved for validators)

2. **Document Types Available to Students**
   - ✅ Sertifikat
   - ❌ SK Resmi (validators only)
   - ✅ Foto Dokumentasi
   - ✅ Surat Keterangan
   - ✅ Link Publikasi
   - ✅ Dokumen Lainnya

## Technical Details

### Middleware Chain
```
auth.any → AuthenticateAny middleware
  ├─ Checks auth()->check() first (validators/admins)
  └─ Falls back to session('auth_role') === 'student'
```

### Authorization Logic
```php
// In DocumentUploadController::authorizeDocumentAccess()
1. Check if user is authenticated (auth()->check())
   - If Admin or Validator → Allow access
   - If regular user with student relation → Check ownership
2. If not authenticated, check student session
   - If student session → Check ownership
3. Otherwise → Deny access (403)
```

### Document Type Filtering
```php
// In DocumentUploadController::index()
if (auth()->check() && in_array(auth()->user()->role, ['Admin', 'Validator'])) {
    $documentTypes = AchievementDocument::DOCUMENT_TYPES; // All types
} else {
    $documentTypes = collect(AchievementDocument::DOCUMENT_TYPES)
        ->except([AchievementDocument::TYPE_SK_RESMI])
        ->toArray(); // Exclude SK Resmi
}
```

## Benefits

1. **Flexibility**: Validators can add documents at any time, not just during approval
2. **Completeness**: Validators can ensure all required documents are present before approval
3. **Efficiency**: Same interface for both students and validators reduces confusion
4. **Control**: SK Resmi upload still restricted to validators/admins only

## Testing Checklist

- [x] Validators can access document upload page
- [x] Validators can upload all document types including SK Resmi
- [x] Students cannot upload SK Resmi
- [x] Students can still upload other document types
- [x] Back button works correctly for both roles
- [x] After validator submits achievement, redirects to document upload
- [x] Upload button appears on validator dashboard
- [x] Authorization prevents unauthorized access

## Related Files

- `routes/web.php` - Route definitions
- `app/Http/Controllers/DocumentUploadController.php` - Document upload logic
- `app/Http/Controllers/ValidatorController.php` - Validator submission logic
- `app/Http/Middleware/AuthenticateAny.php` - Multi-role authentication
- `resources/views/achievements/documents/index.blade.php` - Upload interface
- `resources/views/validator/dashboard.blade.php` - Validator dashboard

## Notes

- SK Resmi upload during approval (in validation show page) is still MANDATORY
- This feature provides an ADDITIONAL way for validators to upload documents
- The document verification workflow remains unchanged
- All existing document upload features (drag & drop, preview, history) work for validators
