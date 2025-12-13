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

echo "=== Middleware Flow Test ===\n\n";

// Find an employer user
$employer = User::where('role', 'employer')->first();
if (!$employer) {
    echo "❌ No employer found\n";
    exit;
}

echo "Testing with employer: ID {$employer->id}, Name: {$employer->name}\n";
echo "User role: {$employer->role}\n";
echo "isEmployer(): " . ($employer->isEmployer() ? 'true' : 'false') . "\n\n";

// Test the actual request to employer dashboard
$request = Request::create('/employer/dashboard', 'GET');

// Set up the request in the application
$app->instance('request', $request);

// Log in the user
Auth::login($employer);

echo "User logged in: " . (Auth::check() ? 'true' : 'false') . "\n";
echo "Auth user ID: " . Auth::id() . "\n";
echo "Auth user role: " . Auth::user()->role . "\n";
echo "Auth user isEmployer(): " . (Auth::user()->isEmployer() ? 'true' : 'false') . "\n\n";

// Process the request through the full middleware stack
try {
    echo "Processing request to /employer/dashboard through middleware stack...\n";
    
    // Handle the request through the kernel
    $response = $kernel->handle($request);
    
    echo "✅ Request processed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    echo "Status code: " . $response->getStatusCode() . "\n";
    
    if (method_exists($response, 'getTargetUrl')) {
        $targetUrl = $response->getTargetUrl();
        echo "Target URL: " . $targetUrl . "\n";
        
        // Check if it's being redirected somewhere else
        if ($response->getStatusCode() === 302) {
            if (strpos($targetUrl, '/employer/dashboard') !== false) {
                echo "✅ Staying on employer dashboard\n";
            } else {
                echo "❌ Being redirected away from employer dashboard to: " . $targetUrl . "\n";
            }
        }
    }
    
    // Check if there are any headers
    $headers = $response->headers->all();
    if (isset($headers['location'])) {
        echo "Location header: " . implode(', ', $headers['location']) . "\n";
    }
    
    // Check the response content if it's not a redirect
    if ($response->getStatusCode() === 200) {
        echo "✅ Successfully reached employer dashboard (200 OK)\n";
        $content = $response->getContent();
        if (strpos($content, 'Dashboard') !== false) {
            echo "✅ Dashboard content detected\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error processing request: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";