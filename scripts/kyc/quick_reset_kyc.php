<?php

// Simple KYC reset without Laravel bootstrap to avoid hanging
$host = '127.0.0.1';
$dbname = 'job_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Quick KYC Reset ===\n\n";
    
    // Get command line arguments
    $userId = $argv[1] ?? null;
    $resetAll = isset($argv[1]) && $argv[1] === 'all';
    
    if (!$userId && !$resetAll) {
        echo "Usage:\n";
        echo "  php quick_reset_kyc.php [user_id]  - Reset specific user\n";
        echo "  php quick_reset_kyc.php all        - Reset all users\n";
        echo "  php quick_reset_kyc.php list       - List all users\n\n";
        
        // Show current users
        $stmt = $pdo->query("SELECT id, name, email, role, kyc_status FROM users ORDER BY id");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Current users:\n";
        foreach ($users as $user) {
            $status = $user['kyc_status'] ?? 'null';
            echo "  ID: {$user['id']} | {$user['name']} ({$user['email']}) | Role: {$user['role']} | KYC: {$status}\n";
        }
        exit;
    }
    
    if ($argv[1] === 'list') {
        $stmt = $pdo->query("SELECT id, name, email, role, kyc_status, kyc_verified_at FROM users ORDER BY id");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "All users:\n";
        foreach ($users as $user) {
            $status = $user['kyc_status'] ?? 'null';
            $verified = $user['kyc_verified_at'] ?? 'Never';
            echo "  ID: {$user['id']} | {$user['name']} ({$user['email']})\n";
            echo "    Role: {$user['role']} | KYC: {$status} | Verified: {$verified}\n\n";
        }
        exit;
    }
    
    if ($resetAll) {
        echo "Resetting KYC status for ALL users...\n";
        
        $stmt = $pdo->prepare("UPDATE users SET 
            kyc_status = 'pending',
            kyc_session_id = NULL,
            kyc_completed_at = NULL,
            kyc_verified_at = NULL,
            kyc_data = NULL
        ");
        
        $stmt->execute();
        $count = $stmt->rowCount();
        
        echo "✅ Reset KYC status for {$count} users\n";
        
    } else {
        // Reset specific user
        $stmt = $pdo->prepare("SELECT id, name, email, kyc_status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "❌ User with ID {$userId} not found\n";
            exit(1);
        }
        
        echo "Resetting KYC status for user: {$user['name']} ({$user['email']})\n";
        echo "Current status: " . ($user['kyc_status'] ?? 'null') . "\n";
        
        $stmt = $pdo->prepare("UPDATE users SET 
            kyc_status = 'pending',
            kyc_session_id = NULL,
            kyc_completed_at = NULL,
            kyc_verified_at = NULL,
            kyc_data = NULL
            WHERE id = ?
        ");
        
        $stmt->execute([$userId]);
        
        echo "✅ KYC status reset to 'pending' for {$user['name']}\n";
    }
    
    echo "\nUsers can now start fresh KYC verification.\n";
    echo "=== Reset Complete ===\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}