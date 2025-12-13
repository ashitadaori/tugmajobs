<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== KYC Test Monitoring for khenrick.herana@gmail.com ===\n\n";

$user = User::where('email', 'khenrick.herana@gmail.com')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "👤 User: {$user->name} ({$user->email})\n";
echo "📊 Current KYC Status: {$user->kyc_status}\n";
echo "🔑 Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
echo "📅 Last Updated: " . $user->updated_at->format('Y-m-d H:i:s') . "\n\n";

echo "🔍 Monitoring for changes... (Press Ctrl+C to stop)\n";
echo "📝 What to watch for:\n";
echo "  - Status change from 'pending' to 'in_progress' (when verification starts)\n";
echo "  - Status change to 'verified' (when verification completes)\n";
echo "  - Session ID assignment\n";
echo "  - Detailed KYC data capture\n";
echo "  - Webhook events\n\n";

$lastStatus = $user->kyc_status;
$lastSessionId = $user->kyc_session_id;
$lastUpdated = $user->updated_at;
$lastDataSize = $user->kyc_data ? strlen(json_encode($user->kyc_data)) : 0;

while (true) {
    sleep(2); // Check every 2 seconds
    
    $user = $user->fresh(); // Reload from database
    
    $hasChanges = false;
    
    // Check for status change
    if ($user->kyc_status !== $lastStatus) {
        echo "📊 Status changed: {$lastStatus} → {$user->kyc_status} (" . date('H:i:s') . ")\n";
        $lastStatus = $user->kyc_status;
        $hasChanges = true;
    }
    
    // Check for session ID change
    if ($user->kyc_session_id !== $lastSessionId) {
        echo "🔑 Session ID assigned: {$user->kyc_session_id} (" . date('H:i:s') . ")\n";
        $lastSessionId = $user->kyc_session_id;
        $hasChanges = true;
    }
    
    // Check for data updates
    $currentDataSize = $user->kyc_data ? strlen(json_encode($user->kyc_data)) : 0;
    if ($currentDataSize !== $lastDataSize) {
        echo "💾 KYC data updated: {$lastDataSize} → {$currentDataSize} bytes (" . date('H:i:s') . ")\n";
        
        if ($user->kyc_data) {
            $data = $user->kyc_data;
            
            // Check for specific data types
            if (isset($data['webhook_event'])) {
                echo "  📡 Webhook event received!\n";
            }
            if (isset($data['detailed_verification_data'])) {
                echo "  🔍 Detailed verification data captured!\n";
            }
            if (isset($data['documents'])) {
                echo "  📄 Document data available!\n";
            }
            if (isset($data['biometric'])) {
                echo "  🤳 Biometric data available!\n";
            }
            if (isset($data['extracted_data'])) {
                echo "  📝 Extracted personal data available!\n";
            }
        }
        
        $lastDataSize = $currentDataSize;
        $hasChanges = true;
    }
    
    // Check for general updates
    if ($user->updated_at > $lastUpdated) {
        if (!$hasChanges) {
            echo "🔄 User record updated (" . date('H:i:s') . ")\n";
        }
        $lastUpdated = $user->updated_at;
    }
    
    // Show current status every 30 seconds if no changes
    static $lastStatusShow = 0;
    if (time() - $lastStatusShow > 30) {
        echo "⏰ " . date('H:i:s') . " - Current status: {$user->kyc_status}\n";
        $lastStatusShow = time();
    }
    
    // Exit if verification is complete
    if ($user->kyc_status === 'verified') {
        echo "\n✅ Verification completed! Final data:\n";
        echo "📊 Status: {$user->kyc_status}\n";
        echo "🔑 Session ID: {$user->kyc_session_id}\n";
        echo "📅 Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'Not set') . "\n";
        echo "💾 Data Size: " . strlen(json_encode($user->kyc_data ?? [])) . " bytes\n";
        
        if ($user->kyc_data) {
            echo "\n📋 Data Structure:\n";
            $data = $user->kyc_data;
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    echo "  - {$key}: " . count($value) . " items\n";
                } else {
                    echo "  - {$key}: " . (is_string($value) ? substr($value, 0, 50) . '...' : $value) . "\n";
                }
            }
        }
        
        echo "\n🎉 Test completed successfully!\n";
        break;
    }
    
    // Exit if verification failed
    if (in_array($user->kyc_status, ['failed', 'expired'])) {
        echo "\n❌ Verification failed with status: {$user->kyc_status}\n";
        break;
    }
}