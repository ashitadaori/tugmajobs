<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🌐 Testing Webhook Route Accessibility\n";
echo "=====================================\n\n";

$webhookUrl = env('APP_URL') . '/api/kyc/webhook';
echo "Testing URL: {$webhookUrl}\n\n";

// Test GET request (should return 405 Method Not Allowed)
echo "1. Testing GET request (expecting 405 Method Not Allowed):\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true,
        'header' => "User-Agent: KYC-Test-Script/1.0\r\n"
    ]
]);

try {
    $response = file_get_contents($webhookUrl, false, $context);
    
    if (isset($http_response_header)) {
        $statusLine = $http_response_header[0];
        echo "   Status: {$statusLine}\n";
        
        // Extract status code
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches);
        $statusCode = $matches[1] ?? 'unknown';
        
        if ($statusCode === '405') {
            echo "   ✅ Correct: GET requests properly rejected\n";
        } elseif ($statusCode === '302') {
            echo "   ⚠️  Redirect detected - this was the problem!\n";
        } else {
            echo "   ❓ Status: {$statusCode}\n";
        }
        
        if ($response) {
            echo "   Response: " . substr($response, 0, 100) . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test POST request (this is what Didit will use)
echo "2. Testing POST request (what Didit webhook will use):\n";

$testPayload = json_encode([
    'session_id' => 'test-session-' . time(),
    'status' => 'completed',
    'decision' => [
        'id_verification' => ['status' => 'approved'],
        'face_match' => ['status' => 'approved'],
        'liveness' => ['status' => 'approved']
    ],
    'created_at' => date('c')
]);

$signature = hash_hmac('sha256', $testPayload, env('DIDIT_WEBHOOK_SECRET', ''));

$postContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            "Content-Type: application/json",
            "X-Didit-Signature: {$signature}",
            "User-Agent: KYC-Test-Script/1.0"
        ],
        'content' => $testPayload,
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

try {
    $postResponse = file_get_contents($webhookUrl, false, $postContext);
    
    if (isset($http_response_header)) {
        $statusLine = $http_response_header[0];
        echo "   Status: {$statusLine}\n";
        
        // Extract status code
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches);
        $statusCode = $matches[1] ?? 'unknown';
        
        if (in_array($statusCode, ['200', '201', '404'])) {
            echo "   ✅ POST request reaches the webhook controller\n";
        } else {
            echo "   ❓ Status: {$statusCode}\n";
        }
        
        if ($postResponse) {
            $responseData = json_decode($postResponse, true);
            if ($responseData) {
                echo "   Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "   Response: " . substr($postResponse, 0, 200) . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test route registration
echo "3. Testing route registration:\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $webhookRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (str_contains($uri, 'kyc/webhook')) {
            $webhookRoutes[] = [
                'method' => implode('|', $route->methods()),
                'uri' => $uri,
                'name' => $route->getName(),
                'action' => $route->getActionName()
            ];
        }
    }
    
    if (empty($webhookRoutes)) {
        echo "   ❌ No webhook routes found!\n";
    } else {
        echo "   ✅ Found webhook routes:\n";
        foreach ($webhookRoutes as $route) {
            echo "      {$route['method']} {$route['uri']} -> {$route['action']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n✅ Webhook Route Test Complete!\n";
echo "==============================\n";
echo "If GET returns 405 and POST works, the webhook is ready for Didit.\n";
