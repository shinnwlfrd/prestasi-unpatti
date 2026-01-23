@echo off
echo ========================================
echo PostgreSQL Database Setup
echo ========================================
echo.

echo Step 1: Creating database...
echo Please enter your PostgreSQL password when prompted
echo.

psql -U postgres -c "CREATE DATABASE prestasiunpatti;"

if %ERRORLEVEL% EQU 0 (
    echo Database created successfully!
    echo.
    echo Step 2: Running migrations...
    php artisan migrate:fresh --seed
    
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo ========================================
        echo Setup completed successfully!
        echo ========================================
        echo.
        echo You can now access the application.
        echo Default admin credentials:
        echo Email: admin@unpatti.ac.id
        echo Password: password
        echo.
    ) else (
        echo.
        echo Migration failed! Please check the error above.
        echo.
    )
) else (
    echo.
    echo Database creation failed!
    echo.
    echo Possible reasons:
    echo 1. PostgreSQL is not installed
    echo 2. PostgreSQL service is not running
    echo 3. Wrong password
    echo 4. Database already exists
    echo.
    echo To check if database exists, run:
    echo psql -U postgres -l
    echo.
)

pause
