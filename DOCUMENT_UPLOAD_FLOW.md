# Document Upload Flow Diagram

## Overview
This document explains how document uploads work for different user roles.

---

## User Role Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    DOCUMENT UPLOAD SYSTEM                    │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   STUDENT    │         │  VALIDATOR   │         │    ADMIN     │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                        │                        │
       │ Login via SSO          │ Login via Auth         │ Login via Auth
       │                        │                        │
       ▼                        ▼                        ▼
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   Student    │         │  Validator   │         │    Admin     │
│  Dashboard   │         │  Dashboard   │         │  Dashboard   │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                        │                        │
       │ Click "Upload          │ Click "Upload          │ Click "Upload
       │ Dokumen"               │ Dokumen" (green)       │ Dokumen"
       │                        │                        │
       ▼                        ▼                        ▼
┌─────────────────────────────────────────────────────────────┐
│              DOCUMENT UPLOAD INTERFACE                       │
│              (achievements/documents/index)                  │
│                                                              │
│  Route: /achievements/{achievement}/documents                │
│  Middleware: auth.any (allows all authenticated users)       │
└─────────────────────────────────────────────────────────────┘
       │                        │                        │
       │ Role Detection         │ Role Detection         │ Role Detection
       │ in Controller          │ in Controller          │ in Controller
       │                        │                        │
       ▼                        ▼                        ▼
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│ STUDENT VIEW │         │VALIDATOR VIEW│         │  ADMIN VIEW  │
├──────────────┤         ├──────────────┤         ├──────────────┤
│ Blue Info    │         │ Green Info   │         │ Green Info   │
│ Box          │         │ Box          │         │ Box          │
│              │         │              │         │              │
│ Document     │         │ Document     │         │ Document     │
│ Types:       │         │ Types:       │         │ Types:       │
│ ✅ Sertifikat│         │ ✅ Sertifikat│         │ ✅ Sertifikat│
│ ✅ Foto      │         │ ✅ Foto      │         │ ✅ Foto      │
│ ✅ Surat Ket │         │ ✅ Surat Ket │         │ ✅ Surat Ket │
│ ✅ Link Pub  │         │ ✅ Link Pub  │         │ ✅ Link Pub  │
│ ✅ Lainnya   │         │ ✅ Lainnya   │         │ ✅ Lainnya   │
│ ❌ SK Resmi  │         │ ✅ SK Resmi  │         │ ✅ SK Resmi  │
│              │         │              │         │              │
│ Back to:     │         │ Back to:     │         │ Back to:     │
│ Student      │         │ Validator    │         │ Admin        │
│ Dashboard    │         │ Dashboard    │         │ Dashboard    │
└──────────────┘         └──────────────┘         └──────────────┘
```

---

## Validator Submit Achievement Flow

```
┌─────────────────────────────────────────────────────────────┐
│              VALIDATOR SUBMIT ACHIEVEMENT                    │
└─────────────────────────────────────────────────────────────┘

Step 1: Click "Ajukan Prestasi"
┌──────────────────────────────────────┐
│   Validator Dashboard                │
│                                      │
│   [Ajukan Prestasi] Button          │
└──────────────┬───────────────────────┘
               │
               ▼
Step 2: Fill Form
┌──────────────────────────────────────┐
│   Submit Form                        │
│   (validator/submit)                 │
│                                      │
│   - Select Student                   │
│   - Select Achievement Category      │
│   - Fill Event Details               │
│   - Upload Certificate (required)    │
│                                      │
│   [Ajukan Prestasi] Button          │
└──────────────┬───────────────────────┘
               │
               │ POST /validator/submit
               │
               ▼
Step 3: Create Achievement
┌──────────────────────────────────────┐
│   ValidatorController::submitStore() │
│                                      │
│   - Validate input                   │
│   - Store certificate file           │
│   - Create StudentAchievement        │
│   - Set status: "Menunggu"           │
│                                      │
└──────────────┬───────────────────────┘
               │
               │ Redirect to document upload
               │
               ▼
Step 4: Upload More Documents
┌──────────────────────────────────────┐
│   Document Upload Interface          │
│   (achievements/documents/index)     │
│                                      │
│   ✅ Success message shown           │
│   "Prestasi berhasil diajukan.      │
│    Anda dapat menambahkan dokumen   │
│    tambahan di bawah ini."          │
│                                      │
│   - Upload SK Resmi                  │
│   - Upload additional documents      │
│   - Upload supporting files          │
│                                      │
│   [Back to Dashboard] Button         │
└──────────────────────────────────────┘
```

---

## Document Type Filtering Logic

```php
// In DocumentUploadController::index()

┌─────────────────────────────────────┐
│   Check User Role                   │
└─────────────┬───────────────────────┘
              │
              ▼
        Is Validator/Admin?
              │
      ┌───────┴───────┐
      │               │
     YES             NO
      │               │
      ▼               ▼
┌─────────────┐  ┌─────────────┐
│ ALL TYPES   │  │ FILTERED    │
│ INCLUDING   │  │ TYPES       │
│ SK RESMI    │  │ (NO SK)     │
└─────────────┘  └─────────────┘
      │               │
      └───────┬───────┘
              │
              ▼
┌─────────────────────────────────────┐
│   Pass to View                      │
│   - $documentTypes                  │
│   - $isValidatorOrAdmin             │
└─────────────────────────────────────┘
```

---

## Authorization Flow

```
┌─────────────────────────────────────────────────────────────┐
│              AUTHORIZATION CHECK                             │
│         (DocumentUploadController::authorizeDocumentAccess)  │
└─────────────────────────────────────────────────────────────┘

Step 1: Check Regular Auth
┌──────────────────────────────────────┐
│   auth()->check()                    │
└──────────────┬───────────────────────┘
               │
               ▼
         Is Authenticated?
               │
       ┌───────┴───────┐
       │               │
      YES             NO
       │               │
       ▼               │
  Check Role           │
       │               │
  ┌────┴────┐          │
  │         │          │
Admin/   Regular       │
Validator  User        │
  │         │          │
  │         ▼          │
  │    Has Student     │
  │    Relation?       │
  │         │          │
  │    ┌────┴────┐     │
  │   YES       NO     │
  │    │         │     │
  │    ▼         ▼     │
  │  Check    403      │
  │  Owner  Forbidden  │
  │    │               │
  ▼    ▼               │
ALLOW ALLOW            │
  │    │               │
  └────┴───────────────┘
               │
               ▼
Step 2: Check Student Session (if not regular auth)
┌──────────────────────────────────────┐
│   session('auth_role') === 'student' │
└──────────────┬───────────────────────┘
               │
               ▼
         Is Student Session?
               │
       ┌───────┴───────┐
       │               │
      YES             NO
       │               │
       ▼               ▼
  Check Owner      403
       │          Forbidden
  ┌────┴────┐
  │         │
 YES       NO
  │         │
  ▼         ▼
ALLOW     403
        Forbidden
```

---

## Middleware Chain

```
Request → auth.any → AuthenticateAny Middleware
                            │
                            ▼
                    Check auth()->check()
                            │
                    ┌───────┴───────┐
                    │               │
                  TRUE            FALSE
                    │               │
                    │               ▼
                    │       Check student session
                    │               │
                    │       ┌───────┴───────┐
                    │       │               │
                    │     TRUE            FALSE
                    │       │               │
                    ▼       ▼               ▼
                  ALLOW   ALLOW         REDIRECT
                    │       │            to login
                    └───────┴───────┐
                                    │
                                    ▼
                            Controller Action
                                    │
                                    ▼
                        authorizeDocumentAccess()
                                    │
                                    ▼
                            Process Request
```

---

## SK Resmi Upload Methods

### Method 1: During Approval (MANDATORY)
```
Validator/Admin → Validation Page → Approve Action
                                         │
                                         ▼
                                  Upload SK Resmi
                                  (REQUIRED)
                                         │
                                         ▼
                                  Achievement Approved
```

### Method 2: Via Upload Interface (OPTIONAL)
```
Validator/Admin → Dashboard → Upload Dokumen Button
                                         │
                                         ▼
                                  Document Upload Page
                                         │
                                         ▼
                                  Select SK Resmi Type
                                         │
                                         ▼
                                  Upload File
                                         │
                                         ▼
                                  SK Resmi Added
```

---

## Key Points

### Students
- ✅ Can access document upload via `auth.any` middleware
- ✅ Can upload multiple document types
- ❌ Cannot upload SK Resmi (filtered out)
- ✅ See blue info box explaining SK will be uploaded by validator
- ✅ Back button routes to student dashboard

### Validators
- ✅ Can access document upload via `auth.any` middleware
- ✅ Can upload ALL document types including SK Resmi
- ✅ See green info box explaining full access
- ✅ Back button routes to validator dashboard
- ✅ After submitting achievement, redirected to upload more documents
- ✅ Upload button available on dashboard for each achievement

### Admins
- ✅ Same permissions as validators
- ✅ Can access all achievements
- ✅ Can upload all document types

---

## Security

### Access Control
1. **Middleware**: `auth.any` ensures user is authenticated
2. **Authorization**: `authorizeDocumentAccess()` checks ownership/role
3. **Document Types**: Filtered based on role in controller

### Validation
1. **File Type**: PDF, JPG, PNG only
2. **File Size**: Max 10MB per file
3. **Document Type**: Must select valid type from dropdown
4. **Ownership**: Can only access own achievements (students) or all (validators/admins)

---

**Last Updated**: January 20, 2026
**Related Documentation**: 
- `VALIDATOR_DOCUMENT_UPLOAD.md`
- `SK_UPLOAD_FEATURE.md`
- `PROJECT_SUMMARY.md`
