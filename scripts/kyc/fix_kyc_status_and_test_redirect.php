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

echo "=== Fix KYC Status and Test Redirect ===\n\n";

// Find the employer with in_progress status
$employer = User::where('role', 'employer')
    ->where('kyc_status', 'in_progress')
    ->first();

if (!$employer) {
    echo "❌ No employer with in_progress status found\n";
    exit;
}

echo "Found employer with in_progress status:\n";
echo "- ID: {$employer->id}\n";
echo "- Name: {$employer->name}\n";
echo "- Email: {$employer->email}\n";
echo "- Current KYC Status: {$employer->kyc_status}\n";
echo "- Session ID: {$employer->kyc_session_id}\n\n";

// Step 1: Manually update the KYC status to verified
echo "Step 1: Updating KYC status to verified...\n";

$employer->update([
    'kyc_status' => 'verified',
    'kyc_verified_at' => now(),
    'kyc_data' => [
        'session_id' => $employer->kyc_session_id,
        'status' => 'completed',
        'completed_at' => now()->toIso8601String(),
        'manual_fix' => true,
        'fixed_at' => now()->toIso8601String()
    ]
]);

echo "✅ KYC status updated successfully\n";
echo "- New Status: {$employer->kyc_status}\n";
echo "- Verified At: " . $employer->kyc_verified_at->format('Y-m-d H:i:s') . "\n";
echo "- isKycVerified(): " . ($employer->isKycVerified() ? 'true' : 'false') . "\n\n";

// Step 2: Test the redirect flow
echo "Step 2: Testing KYC completion redirect...\n";

// Simulate the KYC success redirect
$request = Request::create('/kyc/success', 'GET', [
    'session_id' => $employer->kyc_session_id,
    'status' => 'completed'
]);

// Set up the request in the application
$app->instance('request', $request);

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
            echo "❌ Still redirecting to home page: " . $targetUrl . "\n";
            echo "This indicates there's still an issue with the redirect logic.\n";
        } else {
            echo "❌ Unexpected redirect target: " . $targetUrl . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error processing KYC redirect: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Step 3: Test direct access to employer dashboard
echo "Step 3: Testing direct access to employer dashboard...\n";

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

echo "\n=== Fix and Test Complete ===\n";
echo "\nNow try logging in as this employer and you should be able to access the dashboard.\n";
echo "Email: {$employer->email}\n";
echo "The KYC status has been manually set to 'verified'.\n";