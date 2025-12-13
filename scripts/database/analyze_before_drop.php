<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Analyzing Data Before Dropping Tables\n";
echo "====================================\n\n";

// Tables to analyze
$tablesToCheck = [
    'job_seeker_profiles',
    'application_status_histories', 
    'application_status_history',
    'job_alerts',
    'job_alert_categories',
    'job_alert_job_types',
    'kyc_documents',
    'kyc_verifications',
    'team_members'
];

foreach ($tablesToCheck as $table) {
    try {
        $count = DB::table($table)->count();
        echo "Table: {$table}\n";
        echo "  Records: {$count}\n";
        
        if ($count > 0) {
            echo "  ⚠️  Has data - need to review before dropping\n";
            
            // Show first few records for important tables
            if (in_array($table, ['job_seeker_profiles', 'application_status_histories'])) {
                $sample = DB::table($table)->limit(3)->get();
                echo "  Sample data:\n";
                foreach ($sample as $record) {
                    $recordArray = (array) $record;
                    echo "    - " . json_encode(array_slice($recordArray, 0, 3, true)) . "\n";
                }
            }
        } else {
            echo "  ✅ Empty - safe to drop\n";
        }
        echo "\n";
        
    } catch (Exception $e) {
        echo "Table: {$table}\n";
        echo "  ❌ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "Analysis Complete!\n";
echo "==================\n";
echo "Review the above data before proceeding with table drops.\n";
echo "Tables with data may need migration to new structure.\n";
