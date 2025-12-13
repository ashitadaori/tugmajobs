<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing DiDit API Endpoints ===\n\n";

$baseUrl = config('services.didit.base_url');
$apiKey = config('services.didit.api_key');

if (!$baseUrl || !$apiKey) {
    echo "❌ Missing configuration\n";
    exit(1);
}

$sessionId = '89a7d56d-43a9-414d-8a8d-e905676da782';

$endpoints = [
    // Test root endpoint
    '/' => 'GET',
    '/v2' => 'GET',
    
    // Test session endpoints
    '/v2/session' => 'GET',
    "/v2/session/{$sessionId}" => 'GET',
    "/v2/sessions/{$sessionId}" => 'GET', // try plural
    
    // Test other possible endpoints
    '/v1/session/' . $sessionId => 'GET',
    '/session/' . $sessionId => 'GET',
    
    // Try different API versions
    '/api/v2/session/' . $sessionId => 'GET',
    '/api/session/' . $sessionId => 'GET',
];

foreach ($endpoints as $endpoint => $method) {
    echo "Testing: {$method} {$baseUrl}{$endpoint}\n";
    
    try {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'X-Api-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->get($baseUrl . $endpoint);
        
        $status = $response->status();
        $body = $response->body();
        
        echo "  Status: {$status}\n";
        
        if ($status === 200) {
            echo "  ✅ SUCCESS!\n";
            echo "  Response: " . substr($body, 0, 200) . (strlen($body) > 200 ? '...' : '') . "\n";
        } elseif ($status === 404) {
            echo "  ❌ Not Found\n";
        } elseif ($status === 401) {
            echo "  🔐 Unauthorized - API key might be invalid\n";
        } elseif ($status === 403) {
            echo "  🚫 Forbidden - Access denied\n";
        } else {
            echo "  ⚠️  Unexpected status\n";
            echo "  Response: " . substr($body, 0, 100) . (strlen($body) > 100 ? '...' : '') . "\n";
        }
        
    } catch (Exception $e) {
        echo "  💥 Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Testing Complete ===\n";
