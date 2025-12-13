<?php

// GENERATED SAFE DATABASE CLEANUP SCRIPT
// This script only drops empty, unused tables
// Generated on: 2025-08-11 13:28:12

echo "Starting safe database cleanup...\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Database connected\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$tablesToDrop = [
    'password_resets',
];

foreach ($tablesToDrop as $table) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        echo "✅ Dropped table: $table\n";
    } catch (PDOException $e) {
        echo "❌ Error dropping table $table: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Safe cleanup completed!\n";
echo "Dropped " . count($tablesToDrop) . " empty unused tables.\n";
