<?php

require __DIR__ . '/vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Database Tables ===\n\n";

try {
    $tables = DB::select('SHOW TABLES');
    
    echo "📊 Found " . count($tables) . " tables:\n\n";
    
    foreach($tables as $table) {
        $tableName = array_values((array)$table)[0];
        
        // Get row count for each table
        try {
            $count = DB::table($tableName)->count();
            echo "📋 $tableName ($count rows)\n";
        } catch (Exception $e) {
            echo "📋 $tableName (error counting)\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Complete ===\n";
