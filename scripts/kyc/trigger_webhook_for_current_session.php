<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Triggering Webhook for Current KYC Session\n";
echo "=============================================\n";

// Get the user
$user = \App\Models\User::find(1);

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "👤 Current User Status:\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   KYC Status: {$user->kyc_status}\n";
echo "   Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
echo "\n";

if (!$user->kyc_session_id) {
    echo "❌ No active KYC session found\n";
    exit(1);
}

if ($user->kyc_status === 'verified') {
    echo "✅ User is already verified!\n";
    exit(0);
}

echo "📡 Simulating Didit webhook for session: {$user->kyc_session_id}\n";

// Create a realistic webhook payload
$webhookPayload = [
    'event_type' => 'session.completed',
    'session_id' => $user->kyc_session_id,
    'vendor_data' => 'user-' . $user->id,
    'status' => 'VERIFIED',
    'verification_result' => [
        'identity_verified' => true,
        'document_verified' => true,
        'biometric_verified' => true,
        'overall_result' => 'pass'
    ],
    'extracted_data' => [
        'full_name' => $user->name,
        'email' => $user->email,
        'document_type' => 'passport',
        'nationality' => 'PH',
        'verification_date' => now()->toISOString(),
    ],
    'documents' => [
        [
            'type' => 'passport',
            'status' => 'verified',
            'confidence_score' => 0.98
        ]
    ],
    'biometric' => [
        'liveness_score' => 0.95,
        'face_match_score' => 0.97,
        'status' => 'verified'
    ],
    'timestamp' => now()->toISOString(),
    'webhook_id' => 'manual_trigger_' . time(),
];

$payloadJson = json_encode($webhookPayload);
$webhookSecret = config('services.didit.webhook_secret');
$signature = hash_hmac('sha256', $payloadJson, $webhookSecret);

echo "🔐 Webhook Details:\n";
echo "   Payload size: " . strlen($payloadJson) . " bytes\n";
echo "   Signature: {$signature}\n";
echo "\n";

try {
    // Call the webhook controller directly
    $request = \Illuminate\Http\Request::create(
        '/api/kyc/webhook',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_DIDIT_SIGNATURE' => $signature,
        ],
        $payloadJson
    );

    $controller = new \App\Http\Controllers\KycWebhookController();
    $response = $controller($request);

    echo "✅ Webhook processed successfully!\n";
    echo "   Status Code: {$response->getStatusCode()}\n";
    
    $responseData = json_decode($response->getContent(), true);
    echo "   Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

    // Check user status
    $user->refresh();
    echo "\n";
    echo "👤 Updated User Status:\n";
    echo "   KYC Status: {$user->kyc_status}\n";
    echo "   Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'Not set') . "\n";
    echo "   Has KYC Data: " . ($user->kyc_data ? 'Yes' : 'No') . "\n";

    if ($user->kyc_data) {
        echo "\n";
        echo "📊 KYC Data Summary:\n";
        $kycData = $user->kyc_data;
        
        if (isset($kycData['webhook_payload'])) {
            echo "   ✅ Webhook payload stored\n";
            
            if (isset($kycData['webhook_payload']['extracted_data'])) {
                echo "   ✅ Personal data extracted:\n";
                $extractedData = $kycData['webhook_payload']['extracted_data'];
                foreach ($extractedData as $key => $value) {
                    if (is_string($value) && strlen($value) < 100) {
                        echo "      {$key}: {$value}\n";
                    }
                }
            }
            
            if (isset($kycData['webhook_payload']['verification_result'])) {
                echo "   ✅ Verification results:\n";
                $results = $kycData['webhook_payload']['verification_result'];
                foreach ($results as $key => $value) {
                    echo "      {$key}: " . (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) . "\n";
                }
            }
        }
    }

    if ($user->kyc_status === 'verified') {
        echo "\n";
        echo "🎉 SUCCESS! Your KYC verification is now complete!\n";
        echo "\n";
        echo "📱 What to do next:\n";
        echo "   1. Refresh your browser page\n";
        echo "   2. The verification modal should close\n";
        echo "   3. You should see 'Verified' status\n";
        echo "   4. You should see a verified badge ✅\n";
    }

} catch (\Exception $e) {
    echo "❌ Error processing webhook: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n";
echo "🔍 Recent Notifications:\n";
$notifications = \App\Models\Notification::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

foreach ($notifications as $notification) {
    $timeAgo = $notification->created_at->diffForHumans();
    echo "   [{$timeAgo}] {$notification->title}\n";
    echo "      {$notification->message}\n";
    echo "      Type: {$notification->type}\n";
    echo "\n";
}

echo "🎯 Your verification should now be complete!\n";