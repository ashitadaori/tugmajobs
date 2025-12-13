<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "=== Testing Network Connectivity ===\n\n";

// Test 1: Basic connectivity to the app
echo "1. Testing local app connectivity...\n";
try {
    $appUrl = config('app.url');
    echo "App URL: $appUrl\n";
    
    $response = Http::timeout(10)->get($appUrl);
    echo "Status: " . $response->status() . "\n";
    echo "Response length: " . strlen($response->body()) . " bytes\n";
    
    if ($response->successful()) {
        echo "✓ Local app is accessible\n";
    } else {
        echo "✗ Local app returned error: " . $response->status() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error connecting to local app: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Test Didit API connectivity
echo "2. Testing Didit API connectivity...\n";
try {
    $diditUrl = config('services.didit.base_url');
    echo "Didit URL: $diditUrl\n";
    
    $response = Http::timeout(10)->get($diditUrl);
    echo "Status: " . $response->status() . "\n";
    echo "Response length: " . strlen($response->body()) . " bytes\n";
    
    if ($response->successful()) {
        echo "✓ Didit API is accessible\n";
    } else {
        echo "✗ Didit API returned error: " . $response->status() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error connecting to Didit API: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Test KYC endpoint with cURL simulation
echo "3. Testing KYC endpoint with cURL simulation...\n";
try {
    $kycUrl = config('app.url') . '/kyc/start';
    echo "KYC URL: $kycUrl\n";
    
    // Get CSRF token first
    $homeResponse = Http::timeout(10)->get(config('app.url'));
    $csrfToken = 'test-token'; // We'll use a test token for this simulation
    
    $response = Http::timeout(10)
        ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => $csrfToken,
            'X-Requested-With' => 'XMLHttpRequest',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ])
        ->post($kycUrl);
    
    echo "Status: " . $response->status() . "\n";
    echo "Headers: " . json_encode($response->headers()) . "\n";
    echo "Response: " . $response->body() . "\n";
    
    if ($response->status() === 419) {
        echo "✗ CSRF token mismatch (expected for this test)\n";
    } elseif ($response->status() === 401) {
        echo "✗ Authentication required (expected for this test)\n";
    } elseif ($response->successful()) {
        echo "✓ KYC endpoint is accessible\n";
    } else {
        echo "✗ KYC endpoint returned error: " . $response->status() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error testing KYC endpoint: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check ngrok configuration
echo "4. Checking ngrok configuration...\n";
$appUrl = config('app.url');
if (str_contains($appUrl, 'ngrok')) {
    echo "✓ Using ngrok URL: $appUrl\n";
    
    // Test ngrok headers
    try {
        $response = Http::timeout(10)
            ->withHeaders([
                'ngrok-skip-browser-warning' => 'true'
            ])
            ->get($appUrl);
        
        if ($response->successful()) {
            echo "✓ ngrok is working properly\n";
        } else {
            echo "✗ ngrok returned error: " . $response->status() . "\n";
        }
    } catch (Exception $e) {
        echo "✗ Error testing ngrok: " . $e->getMessage() . "\n";
    }
} else {
    echo "ℹ Not using ngrok (URL: $appUrl)\n";
}

echo "\n=== Network Test Complete ===\n";