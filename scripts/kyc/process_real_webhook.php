<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\KycWebhookController;
use Illuminate\Http\Request;

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Processing Real Webhook Payload ===\n\n";

// Find the user with the session ID
$sessionId = '18bc4b12-aff1-4938-b168-dfbc69c49dfd';
$user = \App\Models\User::where('kyc_session_id', $sessionId)->first();

if (!$user) {
    echo "❌ No user found with session ID: $sessionId\n";
    exit(1);
}

echo "✅ User Found: {$user->name}\n";
echo "Current Status: {$user->kyc_status}\n";
echo "Session ID: {$user->kyc_session_id}\n\n";

// Get the real payload from the user's kyc_data
$kycData = $user->kyc_data;
if (is_string($kycData)) {
    $kycData = json_decode($kycData, true);
}

if (!$kycData || !isset($kycData['webhook_payload'])) {
    echo "❌ No webhook payload found in user's kyc_data\n";
    echo "Current kyc_data: " . json_encode($kycData, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

$realPayload = $kycData['webhook_payload'];
echo "📋 Real Webhook Payload Status: " . ($realPayload['status'] ?? 'NOT FOUND') . "\n\n";

// Create a mock request with the real payload
$jsonPayload = json_encode($realPayload);

// Create webhook controller
$webhookController = new KycWebhookController();

// Create a mock request
$request = new \Illuminate\Http\Request();
$request->initialize([], [], [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'CONTENT_TYPE' => 'application/json',
    'HTTP_X_DIDIT_SIGNATURE' => 'test-signature-bypass'
], $jsonPayload);

echo "🔄 Processing webhook with real payload...\n";

try {
    // Temporarily set bypass for testing
    putenv('DIDIT_WEBHOOK_BYPASS_SIGNATURE=true');
    
    $response = $webhookController($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "✅ Webhook processed successfully!\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Check updated user status
    $user->refresh();
    echo "📊 UPDATED USER STATUS:\n";
    echo "- KYC Status: {$user->kyc_status}\n";
    echo "- Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->toDateTimeString() : 'NULL') . "\n";
    echo "- Session ID: {$user->kyc_session_id}\n\n";
    
    if ($user->kyc_status === 'verified') {
        echo "🎉 SUCCESS! User is now verified!\n";
    } else {
        echo "⚠️ User status was not updated to verified. Check logs for details.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error processing webhook: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Processing Complete ===\n";
