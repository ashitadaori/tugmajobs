<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DiditService;
use Illuminate\Support\Facades\Log;

echo "=== KYC Integration Test ===\n\n";

// Test 1: Configuration Check
echo "1. Checking Configuration...\n";
$config = [
    'DIDIT_BASE_URL' => env('DIDIT_BASE_URL'),
    'DIDIT_API_KEY' => env('DIDIT_API_KEY') ? '***configured***' : 'NOT SET',
    'DIDIT_WORKFLOW_ID' => env('DIDIT_WORKFLOW_ID'),
    'DIDIT_CALLBACK_URL' => env('DIDIT_CALLBACK_URL'),
    'DIDIT_REDIRECT_URL' => env('DIDIT_REDIRECT_URL'),
    'DIDIT_WEBHOOK_SECRET' => env('DIDIT_WEBHOOK_SECRET') ? '***configured***' : 'NOT SET',
];

foreach ($config as $key => $value) {
    $status = $value ? '✅' : '❌';
    echo "   {$status} {$key}: {$value}\n";
}

// Test 2: Service Initialization
echo "\n2. Testing DiditService initialization...\n";
try {
    $diditService = new DiditService();
    echo "   ✅ DiditService initialized successfully\n";
} catch (Exception $e) {
    echo "   ❌ DiditService initialization failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Session Creation Test
echo "\n3. Testing session creation...\n";
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
        echo "   ✅ Session created successfully\n";
        echo "   Session ID: " . $response['session_id'] . "\n";
        echo "   Verification URL: " . $response['url'] . "\n";
    } else {
        echo "   ❌ Session creation returned unexpected response\n";
        echo "   Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Session creation failed: " . $e->getMessage() . "\n";
}

// Test 4: Database Check
echo "\n4. Checking database structure...\n";
try {
    $user = \App\Models\User::first();
    if ($user) {
        $hasKycFields = isset($user->kyc_status);
        echo "   " . ($hasKycFields ? '✅' : '❌') . " KYC fields exist in users table\n";
        
        if ($hasKycFields) {
            echo "   Current KYC status: " . ($user->kyc_status ?? 'null') . "\n";
        }
    } else {
        echo "   ⚠️  No users found in database\n";
    }
} catch (Exception $e) {
    echo "   ❌ Database check failed: " . $e->getMessage() . "\n";
}

// Test 5: Routes Check
echo "\n5. Checking KYC routes...\n";
$routes = [
    '/kyc/start',
    '/kyc/webhook',
    '/kyc/success',
    '/kyc/failure'
];

foreach ($routes as $route) {
    try {
        $routeExists = \Illuminate\Support\Facades\Route::has(ltrim($route, '/'));
        echo "   " . ($routeExists ? '✅' : '❌') . " Route: {$route}\n";
    } catch (Exception $e) {
        echo "   ❌ Route check failed for {$route}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Test Complete ===\n";