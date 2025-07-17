<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing KYC Redirect Handling...\n\n";

// Get a test user
$user = \App\Models\User::first();

if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

echo "Testing with user: {$user->name} (ID: {$user->id})\n";
echo "Current KYC Status: {$user->kyc_status}\n\n";

// Simulate a session ID for testing
$testSessionId = 'test-session-' . time();

// Update user with test session ID
$user->update([
    'kyc_status' => 'in_progress',
    'kyc_session_id' => $testSessionId
]);

echo "1. Updated user with test session ID: {$testSessionId}\n";

// Test different redirect scenarios
$scenarios = [
    ['status' => 'completed', 'description' => 'Successful verification'],
    ['status' => 'failed', 'description' => 'Failed verification'],
    ['status' => 'expired', 'description' => 'Expired session'],
    ['status' => 'pending', 'description' => 'Pending verification']
];

foreach ($scenarios as $scenario) {
    echo "\n2. Testing scenario: {$scenario['description']}\n";
    
    // Simulate the redirect URL that Didit would send
    $redirectUrl = "https://c2cbfe9ac4f0.ngrok-free.app/kyc/success?session_id={$testSessionId}&status={$scenario['status']}";
    
    echo "   Redirect URL: {$redirectUrl}\n";
    
    // Test the route exists
    try {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->match(
            \Illuminate\Http\Request::create('/kyc/success', 'GET', [
                'session_id' => $testSessionId,
                'status' => $scenario['status']
            ])
        );
        
        echo "   ✅ Route exists: " . $route->getName() . "\n";
        
        // Check if route requires authentication
        $middleware = $route->gatherMiddleware();
        $requiresAuth = in_array('auth', $middleware);
        
        echo "   Auth required: " . ($requiresAuth ? 'Yes ❌' : 'No ✅') . "\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Route error: " . $e->getMessage() . "\n";
    }
}

// Reset user status
$user->update([
    'kyc_status' => 'pending',
    'kyc_session_id' => null
]);

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ KYC redirect test completed!\n";
echo "\nNext steps:\n";
echo "1. Complete a real verification on Didit\n";
echo "2. Check if you get redirected properly to: https://c2cbfe9ac4f0.ngrok-free.app/kyc/success\n";
echo "3. Check the Laravel logs for any errors\n";