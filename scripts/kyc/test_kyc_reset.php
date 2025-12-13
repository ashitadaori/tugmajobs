<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

echo "=== Testing KYC Reset Functionality ===\n\n";

// Find a test user
$user = User::where('role', 'jobseeker')->first();
if (!$user) {
    echo "No jobseeker user found. Creating test user...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'jobseeker',
        'kyc_status' => 'pending'
    ]);
}

echo "Testing with user: {$user->name} (ID: {$user->id})\n";
echo "Current KYC status: {$user->kyc_status}\n\n";

// Set user to in_progress status to test reset
$user->update([
    'kyc_status' => 'in_progress',
    'kyc_session_id' => 'test-session-' . time(),
    'kyc_data' => ['test' => 'data']
]);

echo "Set user status to 'in_progress' for testing\n";
echo "Updated KYC status: {$user->fresh()->kyc_status}\n\n";

// Simulate authentication
Auth::login($user);

// Create a proper request
$request = Request::create('/kyc/reset', 'POST', [], [], [], [
    'HTTP_ACCEPT' => 'application/json',
    'HTTP_CONTENT_TYPE' => 'application/json',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Test Browser)'
]);

// Start session
$request->setLaravelSession(app('session.store'));
app('session.store')->start();

// Generate CSRF token
$token = app('session.store')->token();
$request->headers->set('X-CSRF-TOKEN', $token);

echo "Generated CSRF token: $token\n\n";

try {
    // Test the KYC reset controller
    $controller = new App\Http\Controllers\KycController(app(App\Contracts\KycServiceInterface::class));
    
    echo "Testing resetVerification method...\n";
    $response = $controller->resetVerification($request);
    
    if ($response instanceof Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Response data:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        
        // Check if user status was actually reset
        $updatedUser = $user->fresh();
        echo "User status after reset: {$updatedUser->kyc_status}\n";
        echo "Session ID after reset: " . ($updatedUser->kyc_session_id ?? 'null') . "\n";
        echo "KYC data after reset: " . ($updatedUser->kyc_data ? 'present' : 'null') . "\n";
        
        if ($updatedUser->kyc_status === 'pending' && !$updatedUser->kyc_session_id) {
            echo "✓ Reset successful - user can start fresh verification\n";
        } else {
            echo "✗ Reset may not have worked properly\n";
        }
        
    } else {
        echo "Non-JSON response received\n";
        echo "Response type: " . get_class($response) . "\n";
        if (method_exists($response, 'getTargetUrl')) {
            echo "Redirect URL: " . $response->getTargetUrl() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error testing KYC reset: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Testing Reset with Invalid Status ===\n";

// Test reset with verified status (should fail)
$user->update(['kyc_status' => 'verified']);
echo "Set user status to 'verified' (should not allow reset)\n";

try {
    $response = $controller->resetVerification($request);
    
    if ($response instanceof Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Response data:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
        
        if ($response->getStatusCode() === 400 && isset($data['error'])) {
            echo "✓ Correctly rejected reset for verified status\n";
        } else {
            echo "✗ Should have rejected reset for verified status\n";
        }
    }
} catch (Exception $e) {
    echo "Error (expected): " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";