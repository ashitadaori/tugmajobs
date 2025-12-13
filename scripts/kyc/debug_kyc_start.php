<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

echo "=== Testing KYC Start with Authentication ===\n\n";

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

// Simulate authentication
Auth::login($user);

// Create a proper request with session
$request = Request::create('/kyc/start', 'POST', [], [], [], [
    'HTTP_ACCEPT' => 'application/json',
    'HTTP_CONTENT_TYPE' => 'application/json',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

// Start session
$request->setLaravelSession(app('session.store'));
app('session.store')->start();

// Generate CSRF token
$token = app('session.store')->token();
$request->headers->set('X-CSRF-TOKEN', $token);

echo "Generated CSRF token: $token\n";
echo "Request headers:\n";
foreach ($request->headers->all() as $key => $value) {
    echo "  $key: " . implode(', ', $value) . "\n";
}
echo "\n";

try {
    // Test the KYC controller
    $controller = new App\Http\Controllers\KycController(app(App\Contracts\KycServiceInterface::class));
    
    echo "Testing startVerification method with proper authentication...\n";
    $response = $controller->startVerification($request);
    
    if ($response instanceof Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Response data:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        
        if (isset($data['url'])) {
            echo "✓ Verification URL generated successfully\n";
            echo "URL: " . $data['url'] . "\n";
        } elseif (isset($data['error'])) {
            echo "✗ Error: " . $data['error'] . "\n";
        }
    } else {
        echo "Non-JSON response received\n";
        echo "Response type: " . get_class($response) . "\n";
        if (method_exists($response, 'getTargetUrl')) {
            echo "Redirect URL: " . $response->getTargetUrl() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error testing KYC endpoint: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";