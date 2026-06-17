@echo off
echo ==========================================
echo User Types Separation - Migration Script
echo ==========================================
echo.

echo [1/3] Running database migration...
php artisan migrate --force

if %errorlevel% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)

echo.
echo [2/3] Migration completed successfully!
echo.
echo Do you want to migrate existing users to the new tables? (Y/N)
set /p SEED_CHOICE=

if /i "%SEED_CHOICE%"=="Y" (
    echo.
    echo [3/3] Migrating existing user data...
    echo This will create profile records for your existing users.
    echo Your admin and department_head credentials will stay unchanged.
    echo.
    php artisan db:seed --class=UserTypesSeeder
    
    if %errorlevel% neq 0 (
        echo ERROR: Migration failed!
        pause
        exit /b 1
    )
    
    echo.
    echo User data migrated successfully!
) else (
    echo.
    echo [3/3] Skipping migration...
)

echo.
echo ==========================================
echo Migration Complete!
echo ==========================================
echo.
echo Next steps:
echo 1. Run migrate-user-types.bat if skipped above
echo 2. Update your controllers to use the new models
echo 3. See SEPARATED_USER_TYPES.md for usage guide
echo.
pause
