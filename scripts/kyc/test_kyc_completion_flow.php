<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request
$request = Illuminate\Http\Request::create('/');
$response = $kernel->handle($request);

// Set up the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\KycController;

echo "=== KYC Completion Flow Test ===\n\n";

// Find an employer user
$employer = User::where('role', 'employer')->first();
if (!$employer) {
    echo "❌ No employer found\n";
    exit;
}

echo "Testing with employer: ID {$employer->id}, Name: {$employer->name}\n";

// Update the employer's KYC status to simulate completion
$employer->update([
    'kyc_status' => 'verified',
    'kyc_verified_at' => now(),
    'kyc_session_id' => 'test-session-' . time()
]);

echo "Updated KYC status to verified\n";

// Simulate the KYC redirect request
$sessionId = $employer->kyc_session_id;
$status = 'completed';

echo "Simulating KYC redirect with session_id: {$sessionId}, status: {$status}\n\n";

// Create a mock request
$mockRequest = Request::create('/kyc/success', 'GET', [
    'session_id' => $sessionId,
    'status' => $status
]);

// Log in the user
Auth::login($employer);

echo "User logged in: " . (Auth::check() ? 'true' : 'false') . "\n";
echo "Auth user ID: " . Auth::id() . "\n";
echo "Auth user role: " . Auth::user()->role . "\n";
echo "Auth user isEmployer(): " . (Auth::user()->isEmployer() ? 'true' : 'false') . "\n\n";

// Test the redirect logic manually
$user = Auth::user();
$isEmployer = $user->isEmployer();
$dashboardRoute = $isEmployer ? 'employer.dashboard' : 'account.dashboard';

echo "Dashboard route determined: {$dashboardRoute}\n";

try {
    $dashboardUrl = route($dashboardRoute);
    echo "Dashboard URL: {$dashboardUrl}\n";
} catch (Exception $e) {
    echo "❌ Error generating dashboard URL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test if we can actually create the redirect response
try {
    $redirectResponse = redirect()->route($dashboardRoute)->with('success', 
        'Identity verification completed successfully! Your account is now verified.');
    
    echo "✅ Redirect response created successfully\n";
    echo "Target URL: " . $redirectResponse->getTargetUrl() . "\n";
    echo "Status Code: " . $redirectResponse->getStatusCode() . "\n";
    
    // Check if there are any session messages
    $session = $redirectResponse->getSession();
    if ($session) {
        echo "Session flash messages: " . json_encode($session->all()) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating redirect response: " . $e->getMessage() . "\n";
}

echo "\n";

// Test the actual controller method
echo "Testing KYC controller redirectHandler method:\n";
try {
    $controller = new KycController(app(\App\Contracts\KycServiceInterface::class));
    
    // Create a proper request with the session data
    $kycRequest = Request::create('/kyc/success', 'GET', [
        'session_id' => $sessionId,
        'status' => $status
    ]);
    
    // Set the request on the application
    app()->instance('request', $kycRequest);
    
    // Call the redirect handler
    $response = $controller->redirectHandler($kycRequest);
    
    echo "✅ Controller method executed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    
    if (method_exists($response, 'getTargetUrl')) {
        echo "Target URL: " . $response->getTargetUrl() . "\n";
    }
    
    if (method_exists($response, 'getStatusCode')) {
        echo "Status Code: " . $response->getStatusCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing controller method: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";