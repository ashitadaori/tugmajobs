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

echo "=== Actual KYC Redirect Test ===\n\n";

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

// Simulate the actual HTTP request that would come from Didit
$sessionId = $employer->kyc_session_id;
$status = 'completed';

// Create the actual request that would be made to /kyc/success
$request = Request::create('/kyc/success', 'GET', [
    'session_id' => $sessionId,
    'status' => $status
]);

// Set up the request in the application
$app->instance('request', $request);

echo "Simulating GET request to /kyc/success with:\n";
echo "- session_id: {$sessionId}\n";
echo "- status: {$status}\n\n";

// Process the request through the full middleware stack
try {
    echo "Processing request through middleware stack...\n";
    
    // Handle the request through the kernel
    $response = $kernel->handle($request);
    
    echo "✅ Request processed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    echo "Status code: " . $response->getStatusCode() . "\n";
    
    if (method_exists($response, 'getTargetUrl')) {
        echo "Target URL: " . $response->getTargetUrl() . "\n";
    }
    
    // Check if there are any headers
    $headers = $response->headers->all();
    if (isset($headers['location'])) {
        echo "Location header: " . implode(', ', $headers['location']) . "\n";
    }
    
    // Check the response content if it's not a redirect
    if ($response->getStatusCode() !== 302) {
        $content = $response->getContent();
        if (strlen($content) > 0) {
            echo "Response content (first 200 chars): " . substr($content, 0, 200) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error processing request: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Check if the user is authenticated after the request
echo "Authentication status after request:\n";
echo "- Auth::check(): " . (Auth::check() ? 'true' : 'false') . "\n";
if (Auth::check()) {
    echo "- Auth::id(): " . Auth::id() . "\n";
    echo "- User role: " . Auth::user()->role . "\n";
    echo "- isEmployer(): " . (Auth::user()->isEmployer() ? 'true' : 'false') . "\n";
}

echo "\n=== Test Complete ===\n";