<?php
/**
 * Auto-cleanup script for stuck KYC verifications
 * This script automatically cleans up verification sessions that have been stuck for more than 30 minutes
 */

// Simple KYC cleanup without Laravel bootstrap to avoid hanging
$host = '127.0.0.1';
$dbname = 'job_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Auto KYC Cleanup ===\n\n";
    
    // Find users stuck in in_progress status for more than 30 minutes
    $thirtyMinutesAgo = date('Y-m-d H:i:s', strtotime('-30 minutes'));
    
    $stmt = $pdo->prepare("
        SELECT id, name, email, kyc_status, updated_at 
        FROM users 
        WHERE kyc_status = 'in_progress' 
        AND updated_at < ?
    ");
    
    $stmt->execute([$thirtyMinutesAgo]);
    $stuckUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($stuckUsers)) {
        echo "✅ No stuck KYC sessions found\n";
    } else {
        echo "Found " . count($stuckUsers) . " stuck KYC session(s):\n\n";
        
        foreach ($stuckUsers as $user) {
            echo "🔄 Resetting user ID {$user['id']}: {$user['name']} ({$user['email']})\n";
            echo "   - Status: {$user['kyc_status']} since {$user['updated_at']}\n";
            
            // Reset the stuck user
            $resetStmt = $pdo->prepare("
                UPDATE users SET 
                kyc_status = 'pending',
                kyc_session_id = NULL,
                updated_at = NOW()
                WHERE id = ?
            ");
            
            $resetStmt->execute([$user['id']]);
            
            echo "   ✅ Reset to pending status\n\n";
        }
        
        echo "✅ Successfully cleaned up " . count($stuckUsers) . " stuck verification(s)\n";
    }
    
    // Also check for any users with session IDs but pending status (orphaned sessions)
    $stmt = $pdo->prepare("
        SELECT id, name, email, kyc_session_id, updated_at 
        FROM users 
        WHERE kyc_status = 'pending' 
        AND kyc_session_id IS NOT NULL 
        AND kyc_session_id != ''
        AND updated_at < ?
    ");
    
    $stmt->execute([date('Y-m-d H:i:s', strtotime('-1 hour'))]);
    $orphanedSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($orphanedSessions)) {
        echo "\nFound " . count($orphanedSessions) . " orphaned session(s):\n\n";
        
        foreach ($orphanedSessions as $user) {
            echo "🧹 Cleaning session for user ID {$user['id']}: {$user['name']} ({$user['email']})\n";
            echo "   - Session ID: {$user['kyc_session_id']}\n";
            
            $cleanStmt = $pdo->prepare("
                UPDATE users SET 
                kyc_session_id = NULL,
                updated_at = NOW()
                WHERE id = ?
            ");
            
            $cleanStmt->execute([$user['id']]);
            
            echo "   ✅ Session ID cleared\n\n";
        }
        
        echo "✅ Successfully cleaned up " . count($orphanedSessions) . " orphaned session(s)\n";
    }
    
    // Show current status distribution
    echo "\n📊 Current KYC Status Distribution:\n";
    $statusStmt = $pdo->query("
        SELECT 
            COALESCE(kyc_status, 'null') as status, 
            COUNT(*) as count 
        FROM users 
        GROUP BY kyc_status 
        ORDER BY count DESC
    ");
    
    $statuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statuses as $status) {
        echo "   - {$status['status']}: {$status['count']} users\n";
    }
    
    echo "\n=== Cleanup Complete ===\n";
    echo "✨ All stuck KYC verifications have been cleared\n";
    echo "🚀 Users can now start fresh verification processes\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
