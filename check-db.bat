@echo off
echo ========================================
echo Database Configuration Checker
echo ========================================
echo.

echo Checking database connection...
php artisan migrate:status

if %errorlevel% neq 0 (
    echo.
    echo ❌ Database connection failed!
    echo.
    echo Please check:
    echo 1. XAMPP MySQL is running
    echo 2. Database 'spc_voting' exists
    echo 3. .env file has correct credentials
    echo.
    echo To create database:
    echo 1. Open phpMyAdmin: http://localhost/phpmyadmin
    echo 2. Click "New" to create database
    echo 3. Name it: spc_voting
    echo 4. Run this script again
    pause
) else (
    echo.
    echo ✅ Database connection successful!
    echo.
    pause
)
