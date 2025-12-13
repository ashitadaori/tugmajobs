<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

echo "=== KYC Webhook Diagnostic Tool ===\n\n";

// 1. Check webhook endpoint accessibility
echo "1. 🔍 Testing Webhook Endpoint Accessibility\n";
echo "   Webhook URL: " . env('DIDIT_CALLBACK_URL') . "\n";

try {
    $response = Http::timeout(10)->get(env('DIDIT_CALLBACK_URL'));
    
    echo "   Status Code: " . $response->status() . "\n";
    echo "   Response: " . $response->body() . "\n";
    
    if ($response->successful()) {
        echo "   ✅ Webhook endpoint is accessible\n";
    } else {
        echo "   ❌ Webhook endpoint returned error: " . $response->status() . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Failed to reach webhook endpoint: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Test webhook with mock data
echo "2. 🧪 Testing Webhook with Mock Didit Data\n";

$mockWebhookData = [
    'session_id' => '8a5064fc-924b-4067-951d-51311dfe3145', // Your actual session ID
    'status' => 'verified',
    'event_type' => 'verification.completed',
    'user_id' => 1,
    'timestamp' => now()->toISOString(),
    'data' => [
        'verification_id' => 'test_verification_' . time(),
        'user_data' => [
            'first_name' => 'khenrick',
            'last_name' => 'herana',
            'date_of_birth' => '1990-01-01',
            'document_type' => 'passport',
            'document_number' => 'TEST123456'
        ]
    ]
];

echo "   Mock data prepared for session: " . $mockWebhookData['session_id'] . "\n";

// Create HMAC signature
$webhookSecret = env('DIDIT_WEBHOOK_SECRET');
$payload = json_encode($mockWebhookData);
$signature = hash_hmac('sha256', $payload, $webhookSecret);

echo "   Generated signature: sha256=" . $signature . "\n";

try {
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'X-Didit-Signature' => 'sha256=' . $signature,
        'User-Agent' => 'Didit-Webhook/1.0',
    ])->timeout(10)->post(env('DIDIT_CALLBACK_URL'), $mockWebhookData);
    
    echo "   Webhook POST Status: " . $response->status() . "\n";
    echo "   Response Body: " . $response->body() . "\n";
    
    if ($response->successful()) {
        echo "   ✅ Mock webhook call successful\n";
    } else {
        echo "   ❌ Mock webhook call failed with status: " . $response->status() . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Mock webhook call failed: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Check user status after mock webhook
echo "3. 👤 Checking User Status After Mock Webhook\n";

try {
    $user = \App\Models\User::find(1);
    if ($user) {
        echo "   User ID: " . $user->id . "\n";
        echo "   Email: " . $user->email . "\n";
        echo "   KYC Status: " . $user->kyc_status . "\n";
        echo "   KYC Session ID: " . $user->kyc_session_id . "\n";
        echo "   KYC Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
        echo "   Is Verified: " . ($user->isKycVerified() ? 'Yes' : 'No') . "\n";
        
        if ($user->kyc_data) {
            echo "   KYC Data Keys: " . implode(', ', array_keys($user->kyc_data)) . "\n";
        }
    } else {
        echo "   ❌ User not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking user: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Check logs for webhook activity
echo "4. 📋 Checking Recent Webhook Logs\n";

$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    
    // Get recent webhook-related logs
    $webhookLogs = array_filter($lines, function($line) {
        return strpos($line, 'webhook') !== false || 
               strpos($line, 'KYC') !== false ||
               strpos($line, 'Didit') !== false;
    });
    
    $recentLogs = array_slice(array_reverse($webhookLogs), 0, 10);
    
    if (!empty($recentLogs)) {
        echo "   Recent webhook/KYC logs:\n";
        foreach ($recentLogs as $log) {
            echo "   " . trim($log) . "\n";
        }
    } else {
        echo "   ⚠️  No recent webhook/KYC logs found\n";
    }
} else {
    echo "   ❌ Log file not found: " . $logFile . "\n";
}

echo "\n";

// 5. Configuration check
echo "5. ⚙️  Configuration Verification\n";
$config = [
    'DIDIT_BASE_URL' => env('DIDIT_BASE_URL'),
    'DIDIT_API_KEY' => env('DIDIT_API_KEY') ? '***configured***' : 'NOT SET',
    'DIDIT_WORKFLOW_ID' => env('DIDIT_WORKFLOW_ID'),
    'DIDIT_CALLBACK_URL' => env('DIDIT_CALLBACK_URL'),
    'DIDIT_REDIRECT_URL' => env('DIDIT_REDIRECT_URL'),
    'DIDIT_WEBHOOK_SECRET' => env('DIDIT_WEBHOOK_SECRET') ? '***configured***' : 'NOT SET',
    'APP_URL' => env('APP_URL'),
];

foreach ($config as $key => $value) {
    $status = $value && $value !== 'NOT SET' ? '✅' : '❌';
    echo "   {$status} {$key}: {$value}\n";
}

echo "\n";

// 6. Test ngrok accessibility
echo "6. 🌐 Testing Ngrok Accessibility\n";

$ngrokUrl = env('APP_URL');
if ($ngrokUrl && strpos($ngrokUrl, 'ngrok') !== false) {
    echo "   Ngrok URL: " . $ngrokUrl . "\n";
    
    try {
        $response = Http::timeout(10)->get($ngrokUrl);
        echo "   Ngrok Status: " . $response->status() . "\n";
        
        if ($response->successful()) {
            echo "   ✅ Ngrok tunnel is accessible\n";
        } else {
            echo "   ❌ Ngrok tunnel returned error: " . $response->status() . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Failed to reach Ngrok tunnel: " . $e->getMessage() . "\n";
        echo "   💡 Make sure ngrok is running: ngrok http 80\n";
    }
} else {
    echo "   ⚠️  Not using ngrok or APP_URL not set\n";
}

echo "\n";

// 7. Webhook route verification
echo "7. 🛣️  Webhook Route Verification\n";

try {
    $routeExists = \Illuminate\Support\Facades\Route::has('kyc.webhook');
    echo "   Route 'kyc.webhook' exists: " . ($routeExists ? '✅ Yes' : '❌ No') . "\n";
    
    // Check if route is accessible
    $webhookPath = '/api/kyc/webhook';
    echo "   Webhook path: " . $webhookPath . "\n";
    
    // Try to resolve the route
    $request = \Illuminate\Http\Request::create($webhookPath, 'POST');
    $route = \Illuminate\Support\Facades\Route::getRoutes()->match($request);
    
    if ($route) {
        echo "   ✅ Route resolves to: " . $route->getActionName() . "\n";
    } else {
        echo "   ❌ Route does not resolve properly\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Route verification failed: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";

// Summary and recommendations
echo "\n🔧 TROUBLESHOOTING RECOMMENDATIONS:\n\n";

echo "1. If webhook endpoint is not accessible:\n";
echo "   - Make sure ngrok is running: ngrok http 80\n";
echo "   - Check that your local server is running on port 80\n";
echo "   - Verify the ngrok URL in your .env file matches the active tunnel\n\n";

echo "2. If webhook signature verification fails:\n";
echo "   - Double-check the DIDIT_WEBHOOK_SECRET in your .env file\n";
echo "   - Make sure it matches exactly what's configured in Didit\n\n";

echo "3. If webhook receives data but doesn't update user:\n";
echo "   - Check the session_id in webhook data matches user's kyc_session_id\n";
echo "   - Verify user exists in database\n";
echo "   - Check Laravel logs for any processing errors\n\n";

echo "4. Test with a fresh KYC session:\n";
echo "   - Reset your KYC status to 'pending'\n";
echo "   - Start a new verification session\n";
echo "   - Monitor logs during the process\n\n";

echo "5. Enable detailed logging:\n";
echo "   - Set LOG_LEVEL=debug in .env\n";
echo "   - Monitor storage/logs/laravel.log during webhook calls\n\n";
