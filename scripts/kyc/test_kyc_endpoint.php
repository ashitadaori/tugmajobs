<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;

echo "=== Testing KYC Endpoint ===\n\n";

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

// Test the KYC controller directly
try {
    $controller = new App\Http\Controllers\KycController(app(App\Contracts\KycServiceInterface::class));
    
    // Create a mock request
    $request = new Illuminate\Http\Request();
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');
    
    echo "Testing startVerification method...\n";
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
    }
    
} catch (Exception $e) {
    echo "Error testing KYC endpoint: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";