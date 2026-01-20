# Testing Guide: Modal Upload SK Resmi

## Quick Test Steps

### Test 1: Modal Muncul Saat Klik Approve ✅
1. Login sebagai admin/validator
2. Buka halaman validasi prestasi: `/admin/achievements/validation`
3. Klik tombol ungu "Detail & Validasi" pada salah satu prestasi
4. Scroll ke bawah, cari tombol hijau "Approve"
5. Klik tombol "Approve"
6. **Expected**: Modal popup muncul dengan judul "Approve Prestasi"

### Test 2: Modal Bisa Ditutup ✅
**Cara 1: Tombol X**
1. Klik tombol X di pojok kanan atas modal
2. **Expected**: Modal tertutup

**Cara 2: Klik di Luar Modal**
1. Klik area gelap di luar modal
2. **Expected**: Modal tertutup

**Cara 3: Tombol Batal**
1. Klik tombol "Batal" di footer modal
2. **Expected**: Modal tertutup

### Test 3: Upload File SK Resmi ✅
1. Buka modal approve
2. Klik area upload (kotak dengan icon cloud)
3. Pilih file PDF dari komputer
4. **Expected**: 
   - File preview muncul dengan nama file
   - Ukuran file ditampilkan
   - Border berubah jadi hijau
   - Tombol "Approve Prestasi" jadi enabled (hijau)

### Test 4: Validasi Format File ✅
1. Buka modal approve
2. Coba upload file dengan format .docx atau .txt
3. **Expected**: Alert muncul "Format file tidak didukung. Gunakan PDF, JPG, atau PNG."

### Test 5: Validasi Ukuran File ✅
1. Buka modal approve
2. Coba upload file PDF > 10MB
3. **Expected**: Alert muncul "Ukuran file terlalu besar. Maksimal 10MB."

### Test 6: Hapus File yang Sudah Dipilih ✅
1. Buka modal approve
2. Upload file SK
3. Klik tombol X di file preview (kotak hijau)
4. **Expected**: 
   - File preview hilang
   - Upload area kembali ke state awal
   - Tombol "Approve Prestasi" jadi disabled (abu-abu)

### Test 7: Submit Approval dengan SK ✅
1. Buka modal approve
2. Upload file SK Resmi (PDF)
3. (Opsional) Isi catatan approval
4. Klik tombol "Approve Prestasi"
5. **Expected**:
   - Tombol berubah jadi "Memproses..."
   - Loading state aktif
   - Redirect ke `/admin/achievements/validation`
   - Success message: "Prestasi berhasil disetujui dan SK Resmi telah diupload."
   - Status prestasi berubah jadi "Approved"

### Test 8: Submit Tanpa Upload File ✅
1. Buka modal approve
2. JANGAN upload file
3. Coba klik tombol "Approve Prestasi"
4. **Expected**: Tombol disabled (abu-abu), tidak bisa diklik

### Test 9: Catatan Approval (Opsional) ✅
1. Buka modal approve
2. Upload file SK
3. Isi textarea "Catatan Approval" dengan teks
4. Klik "Approve Prestasi"
5. **Expected**: Catatan tersimpan di database (cek di validation logs)

### Test 10: Responsive Mobile ✅
1. Buka di browser mobile atau resize window
2. Klik tombol "Approve"
3. **Expected**:
   - Modal full width dengan padding
   - Semua elemen terlihat dengan baik
   - Tombol mudah diklik
   - Scrollable jika konten panjang

---

## Visual Checklist

### Modal Appearance
- [ ] Header dengan icon hijau dan judul "Approve Prestasi"
- [ ] Info box hijau dengan informasi prestasi
- [ ] Ringkasan prestasi (Mahasiswa, NIM, Kategori, Level)
- [ ] Upload area dengan border dashed
- [ ] Icon cloud upload
- [ ] Text "Klik untuk pilih file atau drag & drop"
- [ ] Text "PDF, JPG, PNG (Maks. 10MB)"
- [ ] Textarea untuk catatan (opsional)
- [ ] Tombol "Batal" (abu-abu)
- [ ] Tombol "Approve Prestasi" (abu-abu/disabled awalnya)

### After File Selected
- [ ] Upload area border jadi hijau solid
- [ ] Icon cloud jadi hijau
- [ ] File preview box muncul (hijau)
- [ ] Nama file ditampilkan
- [ ] Ukuran file ditampilkan
- [ ] Tombol X untuk hapus file
- [ ] Tombol "Approve Prestasi" jadi hijau (enabled)

### During Submit
- [ ] Tombol text berubah jadi "Memproses..."
- [ ] Tombol disabled
- [ ] Cursor wait/loading

---

## Error Scenarios

### Scenario 1: Network Error
**Steps:**
1. Disconnect internet
2. Upload file dan klik approve
3. **Expected**: Alert "Terjadi kesalahan saat approve prestasi"

### Scenario 2: Server Error
**Steps:**
1. Upload file corrupt atau invalid
2. Klik approve
3. **Expected**: Error message dari server

### Scenario 3: Session Expired
**Steps:**
1. Biarkan halaman terbuka > 2 jam
2. Upload file dan klik approve
3. **Expected**: Redirect ke login page

---

## Browser Compatibility

Test di berbagai browser:
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari
- [ ] Mobile Chrome
- [ ] Mobile Safari

---

## Performance Test

### File Upload Speed
- Small file (< 1MB): Should upload instantly
- Medium file (1-5MB): Should upload in 1-3 seconds
- Large file (5-10MB): Should upload in 3-10 seconds

### Modal Animation
- Modal should appear smoothly
- No lag or jank
- Backdrop fade-in smooth

---

## Accessibility Test

### Keyboard Navigation
- [ ] Tab key navigates through elements
- [ ] Enter key submits form
- [ ] ESC key closes modal
- [ ] Space key on buttons triggers action

### Screen Reader
- [ ] Modal title announced
- [ ] File input labeled properly
- [ ] Button states announced (enabled/disabled)
- [ ] Error messages announced

---

## Database Verification

After successful approval, check database:

### Table: `student_achievements`
```sql
SELECT validation_status, validator_id, updated_at 
FROM student_achievements 
WHERE sa_id = {achievement_id};
```
**Expected**: 
- `validation_status` = 'approved'
- `validator_id` = {current_user_id}
- `updated_at` = recent timestamp

### Table: `achievement_documents`
```sql
SELECT document_type, file_name, status, verified_by 
FROM achievement_documents 
WHERE sa_id = {achievement_id} AND document_type = 'SK Resmi';
```
**Expected**:
- `document_type` = 'SK Resmi'
- `file_name` = 'SK_Resmi_{sa_id}_{timestamp}.pdf'
- `status` = 'approved'
- `verified_by` = {validator_id}

### Table: `validation_logs`
```sql
SELECT old_status, new_status, notes, validated_at 
FROM validation_logs 
WHERE sa_id = {achievement_id} 
ORDER BY validated_at DESC LIMIT 1;
```
**Expected**:
- `old_status` = 'pending' or 'Menunggu'
- `new_status` = 'approved'
- `notes` = catatan approval (jika diisi)
- `validated_at` = recent timestamp

---

## File System Verification

Check uploaded file exists:

```bash
# Path: storage/app/public/achievements/{sa_id}/SK_Resmi_{sa_id}_{timestamp}.pdf
ls -lh storage/app/public/achievements/{sa_id}/
```

**Expected**: File SK Resmi ada dengan ukuran sesuai

---

## Common Issues & Solutions

### Issue 1: Modal Tidak Muncul
**Solution**: 
- Clear browser cache
- Run `php artisan view:clear`
- Check browser console for JavaScript errors

### Issue 2: File Tidak Terupload
**Solution**:
- Check file permissions: `storage/app/public` harus writable
- Check `php.ini`: `upload_max_filesize` dan `post_max_size`
- Check disk space

### Issue 3: Tombol Approve Tetap Disabled
**Solution**:
- Pastikan file sudah dipilih
- Check browser console untuk error
- Refresh halaman dan coba lagi

### Issue 4: Redirect Tidak Berfungsi
**Solution**:
- Check route `admin.achievements.validation.index` exists
- Check user permissions
- Check session

---

## Success Criteria

✅ **All tests passed if:**
1. Modal muncul saat klik approve
2. File bisa diupload dengan validasi benar
3. Submit berhasil dengan SK terupload
4. Prestasi status berubah jadi approved
5. SK Resmi muncul di daftar dokumen
6. Success message ditampilkan
7. Redirect ke validation index
8. No JavaScript errors in console
9. No PHP errors in log
10. Database records created correctly

---

## Test Report Template

```
Date: _______________
Tester: _______________
Browser: _______________
Device: _______________

Test Results:
[ ] Test 1: Modal Muncul - PASS/FAIL
[ ] Test 2: Modal Tutup - PASS/FAIL
[ ] Test 3: Upload File - PASS/FAIL
[ ] Test 4: Validasi Format - PASS/FAIL
[ ] Test 5: Validasi Ukuran - PASS/FAIL
[ ] Test 6: Hapus File - PASS/FAIL
[ ] Test 7: Submit Approval - PASS/FAIL
[ ] Test 8: Submit Tanpa File - PASS/FAIL
[ ] Test 9: Catatan Approval - PASS/FAIL
[ ] Test 10: Responsive Mobile - PASS/FAIL

Issues Found:
1. _______________
2. _______________

Notes:
_______________
```

---

**Last Updated**: 20 Januari 2026
**Status**: Ready for Testing
