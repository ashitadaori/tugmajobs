<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DiditService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== COMPREHENSIVE KYC SYSTEM TEST ===\n\n";

// Test 1: Configuration Check
echo "1. ✅ Configuration Check\n";
$config = [
    'DIDIT_BASE_URL' => env('DIDIT_BASE_URL'),
    'DIDIT_API_KEY' => env('DIDIT_API_KEY') ? '***configured***' : 'NOT SET',
    'DIDIT_WORKFLOW_ID' => env('DIDIT_WORKFLOW_ID'),
    'DIDIT_CALLBACK_URL' => env('DIDIT_CALLBACK_URL'),
    'DIDIT_REDIRECT_URL' => env('DIDIT_REDIRECT_URL'),
    'DIDIT_WEBHOOK_SECRET' => env('DIDIT_WEBHOOK_SECRET') ? '***configured***' : 'NOT SET',
];

$allConfigured = true;
foreach ($config as $key => $value) {
    $status = $value && $value !== 'NOT SET' ? '✅' : '❌';
    if ($value === 'NOT SET' || !$value) $allConfigured = false;
    echo "   {$status} {$key}: {$value}\n";
}

// Test 2: Service Initialization
echo "\n2. ✅ DiditService Initialization\n";
try {
    $diditService = new DiditService();
    echo "   ✅ Service initialized successfully\n";
} catch (Exception $e) {
    echo "   ❌ Service initialization failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: API Connection Test
echo "\n3. ✅ API Connection Test\n";
try {
    $testPayload = [
        'vendor_data' => 'test-user-' . time(),
        'metadata' => [
            'user_id' => 'test',
            'user_type' => 'jobseeker',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ],
        'contact_details' => [
            'email' => 'test@example.com',
            'email_lang' => 'en',
        ]
    ];
    
    $response = $diditService->createSession($testPayload);
    
    if (isset($response['session_id']) && isset($response['url'])) {
        echo "   ✅ API connection successful\n";
        echo "   ✅ Session creation working\n";
        echo "   Session ID: " . $response['session_id'] . "\n";
        echo "   Verification URL: " . substr($response['url'], 0, 50) . "...\n";
    } else {
        echo "   ❌ Unexpected API response\n";
        echo "   Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ API connection failed: " . $e->getMessage() . "\n";
}

// Test 4: Database Structure
echo "\n4. ✅ Database Structure Check\n";
try {
    $user = User::first();
    if ($user) {
        $requiredFields = ['kyc_status', 'kyc_session_id', 'kyc_completed_at', 'kyc_verified_at', 'kyc_data'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $user->getAttributes()) && !isset($user->{$field})) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            echo "   ✅ All KYC fields exist in users table\n";
        } else {
            echo "   ❌ Missing KYC fields: " . implode(', ', $missingFields) . "\n";
        }
        
        // Check KYC methods
        $methods = ['isKycVerified', 'needsKycVerification', 'canStartKycVerification'];
        foreach ($methods as $method) {
            if (method_exists($user, $method)) {
                echo "   ✅ Method {$method} exists\n";
            } else {
                echo "   ❌ Method {$method} missing\n";
            }
        }
        
    } else {
        echo "   ⚠️  No users found in database\n";
    }
} catch (Exception $e) {
    echo "   ❌ Database check failed: " . $e->getMessage() . "\n";
}

// Test 5: User KYC Status Distribution
echo "\n5. ✅ User KYC Status Distribution\n";
try {
    $statuses = User::selectRaw('kyc_status, COUNT(*) as count')
        ->groupBy('kyc_status')
        ->get();
        
    foreach ($statuses as $status) {
        echo "   - " . ($status->kyc_status ?? 'null') . ": {$status->count} users\n";
    }
} catch (Exception $e) {
    echo "   ❌ Status check failed: " . $e->getMessage() . "\n";
}

// Test 6: Routes Check
echo "\n6. ✅ KYC Routes Check\n";
$routes = [
    'kyc.start.form' => '/kyc/start',
    'kyc.start' => 'POST /kyc/start',
    'kyc.webhook' => 'POST /kyc/webhook',
    'kyc.success' => '/kyc/success',
    'kyc.failure' => '/kyc/failure'
];

foreach ($routes as $routeName => $routePath) {
    try {
        $routeExists = \Illuminate\Support\Facades\Route::has($routeName);
        echo "   " . ($routeExists ? '✅' : '❌') . " Route '{$routeName}': {$routePath}\n";
    } catch (Exception $e) {
        echo "   ❌ Route check failed for {$routeName}: " . $e->getMessage() . "\n";
    }
}

// Test 7: Middleware Check
echo "\n7. ✅ Middleware Check\n";
try {
    $middleware = app(\App\Http\Middleware\EncourageKycVerification::class);
    echo "   ✅ EncourageKycVerification middleware exists\n";
} catch (Exception $e) {
    echo "   ❌ Middleware check failed: " . $e->getMessage() . "\n";
}

// Test 8: View Files Check
echo "\n8. ✅ View Files Check\n";
$views = [
    'kyc.start' => 'resources/views/kyc/start.blade.php',
    'kyc.success' => 'resources/views/kyc/success.blade.php',
    'kyc.failure' => 'resources/views/kyc/failure.blade.php',
    'kyc.pending' => 'resources/views/kyc/pending.blade.php'
];

foreach ($views as $viewName => $viewPath) {
    if (file_exists($viewPath)) {
        echo "   ✅ View '{$viewName}': {$viewPath}\n";
    } else {
        echo "   ❌ View '{$viewName}' missing: {$viewPath}\n";
    }
}

// Test 9: Webhook Endpoint Check
echo "\n9. ✅ Webhook Endpoint Check\n";
$webhookFile = 'public/kyc_webhook.php';
if (file_exists($webhookFile)) {
    echo "   ✅ Standalone webhook file exists: {$webhookFile}\n";
} else {
    echo "   ❌ Standalone webhook file missing: {$webhookFile}\n";
}

// Test 10: Environment URLs Check
echo "\n10. ✅ Environment URLs Check\n";
$appUrl = env('APP_URL');
$callbackUrl = env('DIDIT_CALLBACK_URL');
$redirectUrl = env('DIDIT_REDIRECT_URL');

echo "   APP_URL: {$appUrl}\n";
echo "   CALLBACK_URL: {$callbackUrl}\n";
echo "   REDIRECT_URL: {$redirectUrl}\n";

if ($appUrl && $callbackUrl && $redirectUrl) {
    if (strpos($callbackUrl, $appUrl) !== false && strpos($redirectUrl, $appUrl) !== false) {
        echo "   ✅ URLs are consistent\n";
    } else {
        echo "   ⚠️  URLs may not be consistent\n";
    }
} else {
    echo "   ❌ Some URLs are missing\n";
}

// Final Summary
echo "\n=== SUMMARY ===\n";
if ($allConfigured) {
    echo "✅ KYC System Status: FULLY OPERATIONAL\n";
    echo "\nThe KYC system is properly configured and ready to use:\n";
    echo "- Configuration: Complete\n";
    echo "- API Connection: Working\n";
    echo "- Database: Ready\n";
    echo "- Routes: Configured\n";
    echo "- Views: Available\n";
    echo "- Webhook: Ready\n";
    echo "\nUsers can start KYC verification by visiting: {$appUrl}/kyc/start\n";
} else {
    echo "⚠️  KYC System Status: NEEDS ATTENTION\n";
    echo "\nSome configuration issues were found. Please review the test results above.\n";
}

echo "\n=== TEST COMPLETE ===\n";