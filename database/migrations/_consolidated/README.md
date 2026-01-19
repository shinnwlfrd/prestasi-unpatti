# Consolidated Migrations

File migrasi telah digabungkan untuk menyederhanakan struktur database.

## Migrasi Baru (di folder `_consolidated/`)

| File | Tabel | Merged From |
|------|-------|-------------|
| `000000_create_users_table.php` | users, sessions, password_reset_tokens | 3 files (users + role + photo) |
| `000002_create_validator_profiles_table.php` | validator_profiles | 1 file (unchanged) |
| `000003_create_students_table.php` | students | 2 files (students + photo) |
| `000004_create_sikad_credentials_table.php` | sikad_credentials | 1 file (unchanged) |
| `000005_create_achievements_table.php` | achievements | 3 files (simplified) |
| `000006_create_student_achievements_table.php` | student_achievements | 3 files (+ certificate, submitted_by) |
| `000007_create_validation_logs_table.php` | validation_logs | 3 files (+ sk_document, metadata) |

## Cara Menggunakan

### Fresh Install (Database Baru)
```bash
# Hapus semua file lama di migrations/ (kecuali folder _consolidated)
# Pindahkan isi _consolidated ke migrations/
# Jalankan:
php artisan migrate:fresh --seed
```

### Existing Database
Jika sudah ada data, tetap gunakan file migrasi yang lama.
Folder `_consolidated` hanya sebagai referensi struktur yang lebih bersih.

## File yang Bisa Dihapus (Empty/Superseded)
- `2026_01_06_100544_add_sk_to_validator_profiles_table.php` (empty)
