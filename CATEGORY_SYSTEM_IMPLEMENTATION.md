# Implementasi Sistem Kategori Prestasi

## Overview
Sistem kategori prestasi telah diubah dari ENUM sederhana menjadi sistem master data yang fleksibel menggunakan foreign key ke tabel `achievement_categories`.

## Perubahan Struktur Database

### Tabel `achievements`
**SEBELUM:**
```php
$table->enum('category', ['Akademik', 'Non-Akademik']);
```

**SESUDAH:**
```php
$table->foreignId('category_id')->constrained('achievement_categories')->onDelete('cascade');
```

### Migrasi Data
- Data lama `'Akademik'` → `category_id = 1`
- Data lama `'Non-Akademik'` → `category_id = 2`

## Kategori Prestasi Baru

Sistem sekarang mendukung 8 kategori prestasi dengan icon dan warna:

1. **Akademik** (#3b82f6 - Biru)
   - Olimpiade sains, kompetisi matematika, penelitian

2. **Olahraga** (#10b981 - Hijau)
   - Atletik, sepak bola, basket, bulu tangkis

3. **Seni & Budaya** (#ec4899 - Pink)
   - Musik, tari, teater, seni rupa, fotografi

4. **Teknologi & Inovasi** (#6366f1 - Indigo)
   - Programming, robotika, IoT, AI, aplikasi

5. **Kepemimpinan & Organisasi** (#8b5cf6 - Ungu)
   - BEM, HIMA, UKM, organisasi kemahasiswaan

6. **Penelitian & Karya Ilmiah** (#06b6d4 - Cyan)
   - Penelitian, publikasi jurnal, poster ilmiah

7. **Kewirausahaan** (#f97316 - Orange)
   - Bisnis, startup, kompetisi bisnis plan

8. **Pengabdian Masyarakat** (#14b8a6 - Teal)
   - Volunteer, sosial, kemanusiaan

## Perubahan Model

### Achievement Model
```php
// SEBELUM
protected $fillable = ['category'];

// SESUDAH
protected $fillable = ['category_id'];

// Relasi baru
public function category()
{
    return $this->belongsTo(AchievementCategory::class, 'category_id');
}
```

### AchievementCategory Model
```php
// Relasi baru
public function achievements()
{
    return $this->hasMany(Achievement::class, 'category_id');
}
```

## Perubahan Controller

Semua controller yang query Achievement sekarang eager load category:

```php
// SEBELUM
$achievements = Achievement::all();

// SESUDAH
$achievements = Achievement::with('category')->get();
```

**File yang diupdate:**
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/Admin/AdminAchievementController.php`
- `app/Http/Controllers/ValidatorController.php`
- `app/Http/Controllers/StudentController.php`
- `app/Http/Controllers/StudentAchievementController.php`

## Perubahan Logic Non-Akademik

### SEBELUM
```php
if ($achievement->achievement?->category === 'Non-Akademik') {
    // Requires 2 document types
}
```

### SESUDAH
```php
// Non-academic = category_id != 1 (Akademik)
if ($achievement->achievement && $achievement->achievement->category_id !== 1) {
    // Requires 2 document types
}
```

**File yang diupdate:**
- `app/Models/StudentAchievement.php`
- `app/Services/DocumentVerificationService.php`
- `app/Services/CredibilityService.php`
- `app/Http/Controllers/DocumentUploadController.php`

## Perubahan View

### Display Kategori dengan Icon & Color
```blade
<div class="flex items-center gap-2">
    @if($achievement->category && $achievement->category->icon)
    <svg class="w-5 h-5" style="color: {{ $achievement->category->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $achievement->category->icon }}"/>
    </svg>
    @endif
    <span class="px-2.5 py-1 rounded-full text-xs font-medium" 
          style="background-color: {{ $achievement->category->color }}20; color: {{ $achievement->category->color }}">
        {{ $achievement->category->name }}
    </span>
</div>
```

**File yang diupdate:**
- `resources/views/admin/achievements/index.blade.php`

## Perubahan Seeder

### SEBELUM
```php
$akademik = Achievement::create(['category' => 'Akademik']);
$nonAkademik = Achievement::create(['category' => 'Non-Akademik']);
```

### SESUDAH
```php
$akademik = Achievement::firstOrCreate(['category_id' => 1]); // Akademik
$nonAkademik = Achievement::firstOrCreate(['category_id' => 2]); // Olahraga
```

**File yang diupdate:**
- `database/seeders/PrestasiMahasiswaSeeder.php`
- `database/seeders/AchievementValidationSeeder.php`

## Manajemen Kategori

Admin dapat mengelola kategori melalui:
- **URL:** `/admin/categories`
- **Menu:** Master Data → Kategori Prestasi

Fitur:
- ✅ Tambah kategori baru
- ✅ Edit nama, deskripsi, icon, warna, urutan
- ✅ Aktifkan/nonaktifkan kategori
- ✅ Hapus kategori (jika tidak ada prestasi terkait)

## Migration Files

1. `2026_01_21_040000_convert_achievements_to_use_category_id.php`
   - Mengubah struktur achievements table
   - Migrasi data lama ke struktur baru

2. `2026_01_21_040100_increase_icon_column_size.php`
   - Mengubah kolom icon dari VARCHAR(255) ke TEXT
   - Untuk menampung SVG path yang panjang

## Testing

### Verifikasi Migrasi
```bash
php artisan migrate
```

### Seed Kategori Baru
```bash
php artisan db:seed --class=AchievementCategorySeeder
```

### Cek Data
```sql
-- Lihat semua kategori
SELECT * FROM achievement_categories ORDER BY `order`;

-- Lihat prestasi dengan kategori
SELECT a.id, ac.name as category, COUNT(sa.sa_id) as total_prestasi
FROM achievements a
LEFT JOIN achievement_categories ac ON a.category_id = ac.id
LEFT JOIN student_achievements sa ON a.id = sa.achievement_id
GROUP BY a.id, ac.name;
```

## Backward Compatibility

✅ Data lama otomatis dimigrasikan
✅ Logic non-akademik tetap berfungsi (category_id != 1)
✅ Rollback tersedia via migration down()

## Keuntungan Sistem Baru

1. **Fleksibilitas**: Admin bisa tambah/edit kategori tanpa ubah code
2. **Visual**: Setiap kategori punya icon & warna untuk UX lebih baik
3. **Scalable**: Mudah tambah kategori baru (Olahraga, Seni, dll)
4. **Konsisten**: Menggunakan master data seperti levels & periods
5. **Maintainable**: Tidak perlu hardcode kategori di code

## Catatan Penting

⚠️ **Non-Akademik Logic**: Semua kategori selain "Akademik" (category_id != 1) dianggap non-akademik dan memerlukan minimal 2 jenis dokumen berbeda.

⚠️ **Icon Format**: Icon menggunakan SVG path dari Heroicons. Pastikan format path valid saat menambah kategori baru.

⚠️ **Color Format**: Warna menggunakan hex code (contoh: #3b82f6). Pastikan format valid untuk styling.

## Troubleshooting

### Error: "category field not found"
Pastikan migration sudah dijalankan dan data sudah dimigrasikan.

### Error: "icon too long"
Pastikan migration `2026_01_21_040100_increase_icon_column_size.php` sudah dijalankan.

### Kategori tidak muncul di form
Pastikan seeder sudah dijalankan dan kategori `is_active = true`.

---

**Tanggal Implementasi:** 21 Januari 2026
**Status:** ✅ Complete
