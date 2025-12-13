<?php
/**
 * Debug script for KYC inline verification
 */

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "KYC Inline Verification Debug\n";
echo "=============================\n\n";

// Test 1: Check if routes are accessible
echo "1. Testing KYC routes:\n";

$routes_to_test = [
    '/kyc/start' => 'POST',
    '/kyc/check-status' => 'POST',
    '/kyc/dismiss-banner' => 'POST'
];

foreach ($routes_to_test as $route => $method) {
    try {
        $request = Illuminate\Http\Request::create($route, $method);
        $response = $kernel->handle($request);
        
        if ($response->getStatusCode() === 419) {
            echo "✅ Route $method $route - EXISTS (CSRF required)\n";
        } elseif ($response->getStatusCode() === 401) {
            echo "✅ Route $method $route - EXISTS (Auth required)\n";
        } elseif ($response->getStatusCode() < 500) {
            echo "✅ Route $method $route - EXISTS (Status: {$response->getStatusCode()})\n";
        } else {
            echo "❌ Route $method $route - ERROR (Status: {$response->getStatusCode()})\n";
        }
    } catch (Exception $e) {
        echo "❌ Route $method $route - ERROR: {$e->getMessage()}\n";
    }
}

echo "\n2. Testing DiditService availability:\n";

try {
    $diditService = app()->make(\App\Contracts\KycServiceInterface::class);
    echo "✅ DiditService - AVAILABLE\n";
    
    // Check if service has required methods
    $methods_to_check = ['createSession', 'verifySignature', 'processWebhookEvent', 'getSessionStatus'];
    foreach ($methods_to_check as $method) {
        if (method_exists($diditService, $method)) {
            echo "✅ Method $method - EXISTS\n";
        } else {
            echo "❌ Method $method - MISSING\n";
        }
    }
} catch (Exception $e) {
    echo "❌ DiditService - ERROR: {$e->getMessage()}\n";
}

echo "\n3. Testing User model KYC methods:\n";

try {
    $userModel = new \App\Models\User();
    $methods_to_check = ['canStartKycVerification', 'needsKycVerification', 'isKycVerified'];
    
    foreach ($methods_to_check as $method) {
        if (method_exists($userModel, $method)) {
            echo "✅ User method $method - EXISTS\n";
        } else {
            echo "❌ User method $method - MISSING\n";
        }
    }
} catch (Exception $e) {
    echo "❌ User model - ERROR: {$e->getMessage()}\n";
}

echo "\n4. Testing environment configuration:\n";

$env_vars_to_check = [
    'DIDIT_API_KEY',
    'DIDIT_WEBHOOK_SECRET',
    'DIDIT_BASE_URL'
];

foreach ($env_vars_to_check as $var) {
    $value = env($var);
    if ($value) {
        echo "✅ $var - SET (length: " . strlen($value) . ")\n";
    } else {
        echo "❌ $var - NOT SET\n";
    }
}

echo "\n5. Testing JavaScript file accessibility:\n";

$js_file = 'public/assets/js/kyc-inline-verification.js';
if (file_exists($js_file)) {
    echo "✅ JavaScript file - EXISTS\n";
    
    $js_content = file_get_contents($js_file);
    $functions_to_check = ['startInlineVerification', 'openVerificationModal', 'checkVerificationComplete'];
    
    foreach ($functions_to_check as $function) {
        if (strpos($js_content, "function $function") !== false) {
            echo "✅ JS Function $function - FOUND\n";
        } else {
            echo "❌ JS Function $function - MISSING\n";
        }
    }
} else {
    echo "❌ JavaScript file - MISSING\n";
}

echo "\n6. Common issues and solutions:\n";
echo "- If CSRF token errors: Make sure meta tag is present in layout\n";
echo "- If DiditService errors: Check .env configuration\n";
echo "- If route errors: Run 'php artisan route:clear'\n";
echo "- If JavaScript errors: Check browser console for details\n";
echo "- If authentication errors: Make sure user is logged in\n";

echo "\n7. Testing with a sample user (if available):\n";

try {
    $user = \App\Models\User::first();
    if ($user) {
        echo "✅ Sample user found (ID: {$user->id})\n";
        echo "   - KYC Status: {$user->kyc_status}\n";
        echo "   - Can start verification: " . ($user->canStartKycVerification() ? 'YES' : 'NO') . "\n";
        echo "   - Needs verification: " . ($user->needsKycVerification() ? 'YES' : 'NO') . "\n";
        echo "   - Is verified: " . ($user->isKycVerified() ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ No users found in database\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing user: {$e->getMessage()}\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
echo "Check the browser console for JavaScript errors when clicking 'Verify Now'\n";
echo "Enable Laravel logging to see server-side errors in storage/logs/laravel.log\n";
?>