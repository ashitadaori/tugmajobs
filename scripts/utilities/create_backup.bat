@echo off
REM Database backup script for Windows
REM Generated on: 2025-08-11 13:28:12

echo Creating database backup...
set BACKUP_FILE=job_portal_backup_%date:~-4,4%-%date:~-10,2%-%date:~-7,2%_%time:~0,2%-%time:~3,2%-%time:~6,2%.sql
"C:\xampp\mysql\bin\mysqldump.exe" -u root -p job_portal > %BACKUP_FILE%
echo Backup created: %BACKUP_FILE%
pause
