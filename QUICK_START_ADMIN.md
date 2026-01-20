# Quick Start Guide - Admin Features

## Login
- URL: `/login`
- Email: `admin@unpatti.ac.id`
- Password: `password`

## Main Features

### 1. Submit Achievement (Ajukan Prestasi)
**Path:** Sidebar → Aksi → Ajukan Prestasi  
**URL:** `/admin/submit-achievement`

**Quick Steps:**
1. Select student
2. Select category (from achievements table)
3. Fill event details
4. Upload certificate
5. Choose action:
   - **Pending** → Upload documents later
   - **Approve** → Upload SK Resmi now
   - **Reject** → Enter rejection reason
6. Submit

---

### 2. Manage Categories (Kategori Prestasi)
**Path:** Sidebar → Master Data → Kategori Prestasi  
**URL:** `/admin/categories`

**Actions:**
- View all categories
- Add new category
- Edit category
- Delete category
- Toggle active/inactive

**Default Categories:**
- Akademik
- Non-Akademik

---

### 3. Manage Levels (Level Prestasi)
**Path:** Sidebar → Master Data → Level Prestasi  
**URL:** `/admin/levels`

**Actions:**
- View all levels
- Add new level with points
- Edit level
- Delete level
- Toggle active/inactive

**Default Levels:**
- Universitas (10 points)
- Nasional (20 points)
- Internasional (30 points)

---

### 4. Validate Achievements (Validasi Prestasi)
**Path:** Sidebar → Validasi → Validasi Prestasi  
**URL:** `/admin/achievements/validation`

**Actions:**
- View pending achievements
- Click achievement to validate
- Fill checklist (6 items)
- Choose action:
  - **Approve** → Upload SK Resmi (modal)
  - **Reject** → Enter reason
  - **Revisi** → Enter revision notes

---

## Important Notes

### Status Values (Indonesian)
- `Menunggu` = Pending
- `Disetujui` = Approved
- `Ditolak` = Rejected
- `Revisi` = Need Revision

### File Upload Limits
- Certificate: Max 5MB (PDF, JPG, PNG)
- SK Resmi: Max 10MB (PDF, JPG, PNG)

### SK Resmi Rules
- Only admin/validator can upload
- Mandatory for approval
- Auto-approved when uploaded
- Not visible to students in document types

### Validation Rules
- Approve requires SK Resmi upload
- Reject requires rejection reason
- Revisi requires revision notes
- All fields validated client and server-side

---

## Troubleshooting

### Categories/Levels not showing?
```bash
php artisan db:seed --class=AchievementCategorySeeder
php artisan db:seed --class=AchievementLevelSeeder
```

### Views not updating?
```bash
php artisan view:clear
```

### File upload not working?
```bash
php artisan storage:link
```

---

## Testing Checklist

- [ ] Login as admin
- [ ] Access submit achievement form
- [ ] Submit with Pending action
- [ ] Submit with Approve action (with SK)
- [ ] Submit with Reject action (with reason)
- [ ] Create new category
- [ ] Edit category
- [ ] Create new level
- [ ] Edit level
- [ ] Validate pending achievement
- [ ] Approve with SK upload
- [ ] Reject with reason
- [ ] Request revision

---

**For detailed testing:** See `ADMIN_SUBMIT_TESTING.md`  
**For complete documentation:** See `ADMIN_FEATURES_COMPLETE.md`
