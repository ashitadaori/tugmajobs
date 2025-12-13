# Scripts Directory

This directory contains utility scripts, test files, and maintenance tools.

## Directory Structure

### `/kyc`
KYC (Know Your Customer) verification scripts.
- KYC status checking and debugging
- Verification flow testing
- Webhook handling
- Session management
- Data extraction and validation

### `/database`
Database management and maintenance scripts.
- Database cleanup utilities
- Data analysis tools
- Migration helpers
- Backup scripts
- Foreign key checks

### `/testing`
Test scripts and HTML test files.
- Feature testing scripts
- Integration tests
- Route testing
- Controller testing
- HTML test interfaces

### `/utilities`
General utility scripts.
- Ngrok configuration and startup scripts
- Cache management
- File conversion tools
- Backup utilities

## Usage Guidelines

### Running Scripts
Most PHP scripts can be run from the project root:
```bash
php scripts/database/check_data.php
php scripts/kyc/check_kyc_status.php
```

### PowerShell Scripts
PowerShell scripts should be run from the project root:
```powershell
.\scripts\utilities\start-ngrok.ps1
.\scripts\utilities\clear-cache.bat
```

### Safety Notes
- Always backup your database before running cleanup scripts
- Test scripts in development environment first
- Review script contents before execution
- Some scripts may require environment variables or configuration

## Common Tasks

### Database Maintenance
- Check data: `scripts/database/check_data.php`
- Analyze tables: `scripts/database/analyze_unused_tables.php`
- Backup: `scripts/database/backup_before_cleanup.php`

### KYC Testing
- Check status: `scripts/kyc/check_kyc_status.php`
- Test flow: `scripts/kyc/test_kyc_completion_flow.php`
- Debug issues: `scripts/kyc/debug_kyc_fix.php`

### Development Tools
- Start ngrok: `scripts/utilities/start-ngrok.ps1`
- Clear cache: `scripts/utilities/clear-cache.bat`
