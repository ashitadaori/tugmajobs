<?php

require_once 'vendor/autoload.php';

// Load Laravel properly
$app = require_once 'bootstrap/app.php';

// Boot the application
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a simple test request
$request = Illuminate\Http\Request::create('/kyc/start', 'POST', [], [], [], [
    'HTTP_ACCEPT' => 'application/json',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_CONTENT_TYPE' => 'application/json'
]);

// Set up authentication (you'll need to replace with a real user ID)
$userId = 1; // Change this to a real user ID from your database
$user = \App\Models\User::find($userId);

if (!$user) {
    echo "❌ User not found with ID: $userId\n";
    echo "Please update the \$userId variable with a valid user ID\n";
    exit(1);
}

// Authenticate the user
\Illuminate\Support\Facades\Auth::login($user);

echo "🧪 Testing KYC Endpoint\n";
echo "========================\n";
echo "User ID: {$user->id}\n";
echo "User Email: {$user->email}\n";
echo "Current KYC Status: {$user->kyc_status}\n";
echo "Can Start Verification: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n";
echo "\n";

// Test the endpoint with timeout
echo "📡 Making request to /kyc/start...\n";

try {
    // Set a timeout to prevent hanging
    set_time_limit(30);
    
    $startTime = microtime(true);
    
    // Process the request
    $response = $kernel->handle($request);
    
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "✅ Request completed in {$duration}ms\n";
    echo "Status Code: {$response->getStatusCode()}\n";
    echo "Content Type: " . $response->headers->get('Content-Type') . "\n";
    
    $content = $response->getContent();
    
    // Try to decode as JSON
    $jsonData = json_decode($content, true);
    if ($jsonData) {
        echo "Response (JSON):\n";
        echo json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";
        
        if (isset($jsonData['error'])) {
            echo "❌ Error: {$jsonData['error']}\n";
        } elseif (isset($jsonData['url'])) {
            echo "✅ Success: Got verification URL\n";
            echo "URL: {$jsonData['url']}\n";
        }
    } else {
        echo "Response (Raw):\n";
        echo substr($content, 0, 500) . (strlen($content) > 500 ? '...' : '') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    // Show the stack trace for debugging
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
echo "🔍 Checking Laravel logs...\n";

// Check recent log entries
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -20); // Last 20 lines
    
    echo "Recent log entries:\n";
    foreach ($recentLines as $line) {
        if (trim($line)) {
            echo $line . "\n";
        }
    }
} else {
    echo "No log file found at: $logFile\n";
}

echo "\n";
echo "💡 Troubleshooting Tips:\n";
echo "1. Check if your .env file has correct Didit configuration\n";
echo "2. Verify your internet connection\n";
echo "3. Check if the Didit API is accessible\n";
echo "4. Look at the Laravel logs above for detailed error messages\n";