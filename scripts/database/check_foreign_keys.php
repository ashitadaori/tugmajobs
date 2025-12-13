<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Foreign Key Constraints\n";
echo "================================\n\n";

// Get foreign key constraints
$constraints = DB::select("
    SELECT 
        TABLE_NAME, 
        COLUMN_NAME, 
        CONSTRAINT_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, COLUMN_NAME
");

foreach ($constraints as $constraint) {
    echo "Table: {$constraint->TABLE_NAME}\n";
    echo "  Column: {$constraint->COLUMN_NAME}\n";
    echo "  References: {$constraint->REFERENCED_TABLE_NAME}.{$constraint->REFERENCED_COLUMN_NAME}\n";
    echo "  Constraint: {$constraint->CONSTRAINT_NAME}\n\n";
}

echo "\nTotal constraints found: " . count($constraints) . "\n";
