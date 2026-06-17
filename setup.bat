@echo off
echo ========================================
echo SPC Voting System - Quick Setup
echo ========================================
echo.

echo Step 1: Running migrations...
php artisan migrate:fresh
echo.

echo Step 2: Seeding database with initial data...
php artisan db:seed
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Default Accounts:
echo.
echo ADMIN:
echo   Email: admin@spc.edu
echo   Password: admin123
echo.
echo STUDENT 1:
echo   Email: juan@spc.edu
echo   Password: student123
echo   Department: IT
echo.
echo STUDENT 2:
echo   Email: maria@spc.edu
echo   Password: student123
echo   Department: BSBA
echo.
echo ========================================
echo Starting development server...
echo Visit: http://localhost:8000
echo ========================================
echo.

php artisan serve
