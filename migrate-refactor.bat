@echo off
echo ========================================
echo Migration Refactoring Script
echo ========================================
echo.

echo Step 1: Backup current migrations...
if not exist "database\migrations_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%" (
    mkdir "database\migrations_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%"
)
xcopy "database\migrations\*.php" "database\migrations_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%\" /Y /Q
echo Backup completed!
echo.

echo Step 2: Move old migrations to _old folder...
if not exist "database\migrations\_old" (
    mkdir "database\migrations\_old"
)
move "database\migrations\*.php" "database\migrations\_old\" >nul 2>&1
echo Old migrations moved!
echo.

echo Step 3: Copy new clean migrations...
xcopy "database\migrations_clean\*.php" "database\migrations\" /Y /Q
echo New migrations copied!
echo.

echo Step 4: Database backup reminder...
echo.
echo ========================================
echo IMPORTANT: Backup your database first!
echo ========================================
echo Run this command to backup:
echo pg_dump -U postgres beasiswa_unpatti ^> backup_before_refactor.sql
echo.
echo After backup, run:
echo php artisan migrate:fresh --seed
echo.
echo ========================================

pause
