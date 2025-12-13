<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating Backup Before Table Cleanup\n";
echo "===================================\n\n";

$backupDir = 'database/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$timestamp = date('Y-m-d_H-i-s');
$backupFile = $backupDir . '/pre_cleanup_backup_' . $timestamp . '.json';

$backupData = [];

// Backup tables that will be dropped
$tablesToBackup = [
    'job_seeker_profiles',
    'application_status_histories',
    'application_status_history',
    'job_alerts',
    'job_alert_categories',
    'job_alert_job_types',
    'kyc_documents',
    'kyc_verifications',
    'team_members',
    'categories' // In case it gets dropped
];

foreach ($tablesToBackup as $table) {
    try {
        if (Schema::hasTable($table)) {
            $data = DB::table($table)->get();
            $backupData[$table] = [
                'count' => $data->count(),
                'data' => $data->toArray()
            ];
            echo "✅ Backed up {$table}: {$data->count()} records\n";
        } else {
            echo "⚠️  Table {$table} does not exist\n";
        }
    } catch (Exception $e) {
        echo "❌ Error backing up {$table}: " . $e->getMessage() . "\n";
        $backupData[$table] = [
            'error' => $e->getMessage()
        ];
    }
}

// Save backup to file
file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));

echo "\n";
echo "Backup completed!\n";
echo "================\n";
echo "Backup file: {$backupFile}\n";
echo "Total tables backed up: " . count($backupData) . "\n";

// Show summary
echo "\nBackup Summary:\n";
echo "---------------\n";
foreach ($backupData as $table => $info) {
    if (isset($info['error'])) {
        echo "❌ {$table}: Error - {$info['error']}\n";
    } else {
        echo "✅ {$table}: {$info['count']} records\n";
    }
}

echo "\nYou can now safely run the cleanup migration.\n";
echo "If anything goes wrong, restore from: {$backupFile}\n";
