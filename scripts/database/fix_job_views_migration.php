<?php

/**
 * Quick script to fix the job_views table referrer column issue
 * Run this script from the project root: php fix_job_views_migration.php
 */

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Fixing job_views table referrer column...\n";
    
    // Run the migration
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2025_07_31_000000_fix_job_views_referrer_column.php',
        '--force' => true
    ]);
    
    echo "Migration completed successfully!\n";
    echo Artisan::output();
    
} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
    
    // Alternative: Run the SQL directly
    echo "Attempting to run SQL directly...\n";
    
    try {
        DB::statement('ALTER TABLE job_views MODIFY COLUMN referrer TEXT NULL');
        DB::statement('ALTER TABLE job_views MODIFY COLUMN user_agent TEXT NULL');
        echo "SQL executed successfully!\n";
    } catch (Exception $sqlError) {
        echo "SQL Error: " . $sqlError->getMessage() . "\n";
        echo "Please run manually: php artisan migrate\n";
    }
}