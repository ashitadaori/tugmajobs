<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set up the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\KycController;

echo "=== KYC Redirect Fix Test ===\n\n";

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

echo "Updated KYC status to verified\n\n";

// Simulate the KYC redirect request
$sessionId = $employer->kyc_session_id;
$status = 'completed';

// Create the actual request that would be made to /kyc/success
$request = Request::create('/kyc/success', 'GET', [
    'session_id' => $sessionId,
    'status' => $status
]);

// Set up the request in the application
$app->instance('request', $request);

// Log in the user
Auth::login($employer);

echo "User logged in: " . (Auth::check() ? 'true' : 'false') . "\n";
echo "Auth user ID: " . Auth::id() . "\n";
echo "Auth user role: " . Auth::user()->role . "\n";
echo "Auth user isEmployer(): " . (Auth::user()->isEmployer() ? 'true' : 'false') . "\n\n";

// Test the controller method directly
echo "Testing KYC controller redirectHandler method with fix:\n";
try {
    $controller = new KycController(app(\App\Contracts\KycServiceInterface::class));
    
    // Call the redirect handler
    $response = $controller->redirectHandler($request);
    
    echo "✅ Controller method executed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    
    if (method_exists($response, 'getTargetUrl')) {
        $targetUrl = $response->getTargetUrl();
        echo "Target URL: " . $targetUrl . "\n";
        
        // Check if it's redirecting to the employer dashboard
        if (strpos($targetUrl, '/employer/dashboard') !== false) {
            echo "✅ Correctly redirecting to employer dashboard\n";
        } else {
            echo "❌ Not redirecting to employer dashboard\n";
        }
    }
    
    if (method_exists($response, 'getStatusCode')) {
        echo "Status Code: " . $response->getStatusCode() . "\n";
    }
    
    // Check session flash messages
    $session = $response->getSession();
    if ($session && $session->has('success')) {
        echo "✅ Success message set: " . $session->get('success') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing controller method: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test route generation
echo "Testing route generation:\n";
try {
    $employerDashboardUrl = route('employer.dashboard');
    echo "✅ employer.dashboard route: " . $employerDashboardUrl . "\n";
} catch (Exception $e) {
    echo "❌ Error generating employer.dashboard route: " . $e->getMessage() . "\n";
}

try {
    $accountDashboardUrl = route('account.dashboard');
    echo "✅ account.dashboard route: " . $accountDashboardUrl . "\n";
} catch (Exception $e) {
    echo "❌ Error generating account.dashboard route: " . $e->getMessage() . "\n";
}

try {
    $homeUrl = route('home');
    echo "✅ home route: " . $homeUrl . "\n";
} catch (Exception $e) {
    echo "❌ Error generating home route: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";