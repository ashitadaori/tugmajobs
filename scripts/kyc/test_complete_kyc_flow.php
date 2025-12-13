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

echo "=== Complete KYC Flow Test ===\n\n";

// Find an employer user
$employer = User::where('role', 'employer')->first();
if (!$employer) {
    echo "❌ No employer found\n";
    exit;
}

echo "Testing complete KYC flow with employer: ID {$employer->id}, Name: {$employer->name}\n\n";

// Step 1: Set user to in_progress status (simulating KYC start)
$employer->update([
    'kyc_status' => 'in_progress',
    'kyc_session_id' => 'test-session-' . time(),
    'kyc_verified_at' => null
]);

echo "Step 1: Set KYC status to in_progress\n";
echo "- KYC Status: {$employer->kyc_status}\n";
echo "- Session ID: {$employer->kyc_session_id}\n\n";

// Step 2: Simulate the redirect from Didit (user returns from external verification)
$sessionId = $employer->kyc_session_id;
$status = 'completed';

echo "Step 2: Simulating redirect from Didit\n";
echo "- Session ID: {$sessionId}\n";
echo "- Status: {$status}\n\n";

// Create the request that Didit would make
$request = Request::create('/kyc/success', 'GET', [
    'session_id' => $sessionId,
    'status' => $status
]);

// Set up the request in the application
$app->instance('request', $request);

echo "Step 3: Processing KYC success redirect...\n";

try {
    // Process the request through the full middleware stack
    $response = $kernel->handle($request);
    
    echo "✅ KYC redirect processed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    echo "Status code: " . $response->getStatusCode() . "\n";
    
    if (method_exists($response, 'getTargetUrl')) {
        $targetUrl = $response->getTargetUrl();
        echo "Target URL: " . $targetUrl . "\n";
        
        // Check if it's redirecting to the employer dashboard
        if (strpos($targetUrl, '/employer/dashboard') !== false) {
            echo "✅ Correctly redirecting to employer dashboard\n";
        } elseif (strpos($targetUrl, '/') !== false && strpos($targetUrl, '/employer') === false) {
            echo "❌ Redirecting to home page instead of employer dashboard: " . $targetUrl . "\n";
        } else {
            echo "❌ Unexpected redirect target: " . $targetUrl . "\n";
        }
    }
    
    // Check if there are any headers
    $headers = $response->headers->all();
    if (isset($headers['location'])) {
        echo "Location header: " . implode(', ', $headers['location']) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error processing KYC redirect: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Step 4: Check the user's status after the redirect
$employer->refresh();
echo "Step 4: User status after KYC completion\n";
echo "- KYC Status: {$employer->kyc_status}\n";
echo "- KYC Verified At: " . ($employer->kyc_verified_at ? $employer->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
echo "- isKycVerified(): " . ($employer->isKycVerified() ? 'true' : 'false') . "\n";

echo "\n";

// Step 5: Test if the user can now access the employer dashboard
echo "Step 5: Testing access to employer dashboard after KYC completion\n";

// Log in the user
Auth::login($employer);

$dashboardRequest = Request::create('/employer/dashboard', 'GET');
$app->instance('request', $dashboardRequest);

try {
    $dashboardResponse = $kernel->handle($dashboardRequest);
    
    echo "✅ Dashboard request processed successfully\n";
    echo "Status code: " . $dashboardResponse->getStatusCode() . "\n";
    
    if ($dashboardResponse->getStatusCode() === 200) {
        echo "✅ Successfully accessed employer dashboard\n";
    } elseif ($dashboardResponse->getStatusCode() === 302) {
        if (method_exists($dashboardResponse, 'getTargetUrl')) {
            $redirectUrl = $dashboardResponse->getTargetUrl();
            echo "❌ Dashboard access redirected to: " . $redirectUrl . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error accessing dashboard: " . $e->getMessage() . "\n";
}

echo "\n=== Complete Flow Test Finished ===\n";