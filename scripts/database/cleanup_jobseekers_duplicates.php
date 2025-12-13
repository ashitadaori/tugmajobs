<?php

// JOBSEEKERS DUPLICATE CLEANUP SCRIPT
// This script removes duplicate jobseeker profiles, keeping the newest one

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connected to database\n\n";

    // User ID 3: Keep profile 1, remove 2
    $pdo->exec("DELETE FROM jobseekers WHERE id = 2");
    echo "🗑️  Removed duplicate jobseeker profile ID: 2\n";
    echo "👑 Kept jobseeker profile ID: 1 for user 3\n\n";

    echo "🎉 Cleanup completed successfully!\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
