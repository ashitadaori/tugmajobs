<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Checking Webhook Payload from Real Didit Verification ===\n\n";

$user = User::where('email', 'khenrick.herana@gmail.com')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "✅ User Found: {$user->name}\n";
echo "Current Status: {$user->kyc_status}\n";
echo "Session ID: {$user->kyc_session_id}\n\n";

if ($user->kyc_data && isset($user->kyc_data['webhook_payload'])) {
    echo "📋 WEBHOOK PAYLOAD RECEIVED:\n";
    echo str_repeat("=", 50) . "\n";
    echo json_encode($user->kyc_data['webhook_payload'], JSON_PRETTY_PRINT) . "\n";
    echo str_repeat("=", 50) . "\n\n";
    
    $payload = $user->kyc_data['webhook_payload'];
    
    // Check what status was sent
    if (isset($payload['status'])) {
        echo "🔍 STATUS ANALYSIS:\n";
        echo "- Received Status: '" . $payload['status'] . "'\n";
        echo "- Status Type: " . gettype($payload['status']) . "\n";
        echo "- Lowercase Status: '" . strtolower($payload['status']) . "'\n\n";
        
        // Check what our system should recognize
        $recognizedStatuses = ['verified', 'completed', 'success', 'failed', 'rejected', 'error', 'expired'];
        $actualStatus = strtolower($payload['status']);
        
        if (in_array($actualStatus, $recognizedStatuses)) {
            echo "✅ Status SHOULD be recognized by our system\n";
        } else {
            echo "❌ Status NOT recognized by our system\n";
            echo "🔧 RECOGNIZED STATUSES: " . implode(', ', $recognizedStatuses) . "\n";
        }
        
    } else {
        echo "❌ No 'status' field found in webhook payload\n";
    }
    
    // Check for other potential status fields
    echo "\n🔍 CHECKING FOR OTHER STATUS FIELDS:\n";
    $statusFields = ['status', 'verification_status', 'state', 'result_status', 'outcome'];
    
    foreach ($statusFields as $field) {
        if (isset($payload[$field])) {
            echo "- {$field}: " . json_encode($payload[$field]) . "\n";
        }
    }
    
    // Check nested fields
    if (isset($payload['result'])) {
        echo "- result: " . json_encode($payload['result']) . "\n";
    }
    
    if (isset($payload['data'])) {
        echo "- data keys: " . implode(', ', array_keys($payload['data'])) . "\n";
    }
    
} else {
    echo "❌ No webhook payload found in user data\n";
    
    if ($user->kyc_data) {
        echo "Available KYC data keys: " . implode(', ', array_keys($user->kyc_data)) . "\n";
    }
}

echo "\n=== Analysis Complete ===\n";
