# Fix: Edit dan Tambah User Tidak Bisa Dilakukan

## Masalah
- Tombol "Tambah User" tidak membuka modal
- Tombol "Edit" tidak membuka modal
- Form tidak bisa disubmit

## Penyebab
Alpine.js `x-data` didefinisikan di tempat yang salah, menyebabkan:
1. Tombol "Tambah User" tidak bisa akses `showModal`
2. Tombol "Edit" tidak bisa akses `editUser()`
3. Modal memiliki `x-data` duplikat yang conflict

## Struktur Sebelumnya (SALAH ❌)
```blade
<div class="space-y-6">  <!-- Tidak ada x-data -->
    <button @click="showModal = true">  <!-- ❌ showModal undefined -->
    
    <button @click="editUser(...)">  <!-- ❌ editUser undefined -->
    
</div>

<!-- Modal dengan x-data sendiri -->
<div x-data="{ showModal: false, ... }">  <!-- ❌ Terpisah dari parent -->
    <div x-show="showModal">
        <!-- Modal content -->
    </div>
</div>
```

## Struktur Setelah Diperbaiki (BENAR ✅)
```blade
<div class="space-y-6" x-data="{ showModal: false, editMode: false, ... }">  <!-- ✅ x-data di parent -->
    <button @click="showModal = true">  <!-- ✅ Bisa akses showModal -->
    
    <button @click="editUser(...)">  <!-- ✅ Bisa akses editUser() -->
    
    <!-- Modal TANPA x-data sendiri -->
    <div x-show="showModal">  <!-- ✅ Menggunakan showModal dari parent -->
        <!-- Modal content -->
    </div>
</div>
```

## Perubahan yang Dilakukan

### 1. Pindahkan x-data ke Parent Container
**Before**:
```blade
<div class="space-y-6">
```

**After**:
```blade
<div class="space-y-6" x-data="{
    showModal: {{ session('showModal') ? 'true' : 'false' }},
    editMode: {{ session('editUserId') ? 'true' : 'false' }},
    userId: {{ session('editUserId') ?? 'null' }},
    form: { ... },
    faculties: [ ... ],
    resetForm() { ... },
    editUser(user) { ... }
}" @keydown.escape.window="showModal = false">
```

### 2. Hapus x-data Duplikat dari Modal
**Before**:
```blade
<!-- Add/Edit Modal -->
<div x-data="{ showModal: false, ... }">  <!-- ❌ Duplikat -->
    <div x-show="showModal">
```

**After**:
```blade
<!-- Add/Edit Modal -->
<div x-show="showModal" x-cloak>  <!-- ✅ Langsung x-show -->
```

### 3. Ubah Escape Handler
**Before**:
```blade
@keydown.escape="showModal = false"  <!-- Hanya di modal -->
```

**After**:
```blade
@keydown.escape.window="showModal = false"  <!-- Di parent, global -->
```

## Cara Kerja Alpine.js Scope

### Scope Hierarchy:
```
<div x-data="{ showModal: false }">  ← Parent scope
    <button @click="showModal = true">  ← Bisa akses parent
    
    <div x-show="showModal">  ← Bisa akses parent
        <button @click="showModal = false">  ← Bisa akses parent
    </div>
</div>
```

### Scope Terpisah (SALAH):
```
<div>  ← Tidak ada x-data
    <button @click="showModal = true">  ← ❌ showModal undefined
</div>

<div x-data="{ showModal: false }">  ← Scope terpisah
    <div x-show="showModal">  ← Hanya bisa akses scope sendiri
    </div>
</div>
```

## Testing

### Test 1: Tambah User
1. Klik tombol "Tambah User"
2. **Expected**: Modal terbuka ✅
3. Isi form
4. Klik "Tambah"
5. **Expected**: User berhasil ditambahkan ✅

### Test 2: Edit User
1. Klik tombol "Edit" pada user
2. **Expected**: Modal terbuka dengan data user ✅
3. Ubah data
4. Klik "Update"
5. **Expected**: User berhasil diupdate ✅

### Test 3: Close Modal
1. Buka modal
2. Klik di luar modal (backdrop)
3. **Expected**: Modal tertutup ✅
4. Buka modal lagi
5. Tekan ESC
6. **Expected**: Modal tertutup ✅

### Test 4: Reset Form
1. Buka modal tambah user
2. Isi beberapa field
3. Klik "Batal"
4. Buka modal lagi
5. **Expected**: Form kosong (ter-reset) ✅

## Files Modified
- `resources/views/admin/users/index.blade.php`
  - Pindahkan x-data ke parent container
  - Hapus x-data duplikat dari modal
  - Ubah @keydown.escape ke @keydown.escape.window

## Status: FIXED ✅
- ✅ Tombol "Tambah User" berfungsi
- ✅ Tombol "Edit" berfungsi
- ✅ Modal bisa dibuka dan ditutup
- ✅ Form bisa disubmit
- ✅ Escape key berfungsi
- ✅ Click outside berfungsi
- ✅ Reset form berfungsi

---

**Fixed Date**: January 21, 2026  
**Root Cause**: Alpine.js scope issue  
**Solution**: Move x-data to parent container
