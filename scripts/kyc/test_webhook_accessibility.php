<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🌐 Testing Webhook Accessibility\n";
echo "================================\n";

$webhookUrl = config('services.didit.callback_url');
echo "Webhook URL: {$webhookUrl}\n";
echo "\n";

// Test 1: Check if ngrok is running
echo "🔍 Test 1: Checking ngrok accessibility...\n";
try {
    $response = \Illuminate\Support\Facades\Http::timeout(10)->get($webhookUrl);
    echo "   Status: {$response->status()}\n";
    echo "   Response: " . substr($response->body(), 0, 100) . "...\n";
    
    if ($response->status() === 405) {
        echo "   ✅ Endpoint accessible (405 = Method Not Allowed for GET, which is expected)\n";
    } else {
        echo "   ⚠️  Unexpected status code\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   This suggests ngrok might not be running or accessible\n";
}

echo "\n";

// Test 2: Test POST request to webhook
echo "🔍 Test 2: Testing POST request to webhook...\n";
try {
    $testPayload = [
        'session_id' => 'test-accessibility-check',
        'status' => 'VERIFIED',
        'timestamp' => now()->toISOString(),
    ];
    
    $payloadJson = json_encode($testPayload);
    $signature = hash_hmac('sha256', $payloadJson, config('services.didit.webhook_secret'));
    
    $response = \Illuminate\Support\Facades\Http::timeout(10)
        ->withHeaders([
            'X-Didit-Signature' => $signature,
            'Content-Type' => 'application/json',
        ])
        ->withBody($payloadJson, 'application/json')
        ->post($webhookUrl);
    
    echo "   Status: {$response->status()}\n";
    echo "   Response: " . $response->body() . "\n";
    
    if ($response->status() === 404) {
        echo "   ❌ User not found (expected for test session ID)\n";
    } elseif ($response->status() === 200) {
        echo "   ✅ Webhook endpoint working correctly\n";
    } else {
        echo "   ⚠️  Unexpected response\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check if we can simulate the actual webhook
echo "🔍 Test 3: Simulating real webhook with actual session ID...\n";

$user = \App\Models\User::find(1);
if ($user && $user->kyc_session_id) {
    $realPayload = [
        'session_id' => $user->kyc_session_id,
        'status' => 'VERIFIED',
        'timestamp' => now()->toISOString(),
        'user_data' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
        'verification_result' => [
            'identity_verified' => true,
            'document_verified' => true,
            'biometric_verified' => true,
        ],
    ];
    
    $payloadJson = json_encode($realPayload);
    $signature = hash_hmac('sha256', $payloadJson, config('services.didit.webhook_secret'));
    
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders([
                'X-Didit-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])
            ->withBody($payloadJson, 'application/json')
            ->post($webhookUrl);
        
        echo "   Status: {$response->status()}\n";
        echo "   Response: " . $response->body() . "\n";
        
        if ($response->status() === 200) {
            echo "   ✅ Webhook processed successfully!\n";
            echo "   🔄 Checking user status...\n";
            
            $user->refresh();
            echo "   User KYC Status: {$user->kyc_status}\n";
            
            if ($user->kyc_status === 'verified') {
                echo "   🎉 SUCCESS! User status updated to verified!\n";
                echo "   💡 The page should now refresh and show verified status\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ No user or session ID found\n";
}

echo "\n";
echo "📋 Summary:\n";
echo "   - Webhook URL: {$webhookUrl}\n";
echo "   - The verification completed in Didit but webhook wasn't received\n";
echo "   - This is likely because Didit couldn't reach your ngrok URL\n";
echo "\n";
echo "🔧 Solutions:\n";
echo "   1. Make sure ngrok is running: ./start-ngrok.sh\n";
echo "   2. Check Didit dashboard webhook URL configuration\n";
echo "   3. Manually trigger webhook (done above)\n";
echo "   4. Or manually set user as verified: php artisan kyc:set-status 1 verified\n";