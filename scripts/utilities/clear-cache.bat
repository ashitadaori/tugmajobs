@echo off
echo Clearing Laravel cache...
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
echo Done! Please refresh your browser.
pause
