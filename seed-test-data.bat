@echo off
echo ============================================
echo   Database Seeder: 50 Job Seekers + 50 Employers
echo ============================================
echo.

set /p confirm="This will add 50 job seekers and 50 employers to your database. Continue? (Y/N): "
if /i not "%confirm%"=="Y" (
    echo Seeding cancelled.
    exit /b
)

echo.
echo Starting database seeding...
echo.

echo Step 1: Clearing cache...
php artisan cache:clear

echo.
echo Step 2: Seeding job seekers (50)...
php artisan db:seed --class=JobSeekersSeeder

echo.
echo Step 3: Seeding employers and jobs (50 employers + ~260 jobs)...
php artisan db:seed --class=EmployersSeeder

echo.
echo Step 4: Clearing cache again...
php artisan cache:clear

echo.
echo ============================================
echo   Seeding Complete!
echo ============================================
echo.

echo Verifying data...
php artisan tinker --execute="echo 'Job Seekers: ' . App\Models\User::where('role', 'jobseeker')->count() . PHP_EOL; echo 'Employers: ' . App\Models\User::where('role', 'employer')->count() . PHP_EOL; echo 'Active Jobs: ' . App\Models\Job::where('status', 1)->count() . PHP_EOL;"

echo.
echo ============================================
echo   Next Steps:
echo ============================================
echo 1. Test clustering: php demo-clustering-example.php
echo 2. Verify system: php verify-clustering.php
echo 3. Login as job seeker:
echo    Email: juan.delacruz1@example.com
echo    Password: password123
echo.

pause
