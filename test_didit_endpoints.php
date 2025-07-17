<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test different Didit API endpoints
$endpoints = [
    'verification.didit.me (CORRECT)' => [
        'base' => 'https://verification.didit.me',
        'auth' => 'https://verification.didit.me'
    ],
    'business.didit.me' => [
        'base' => 'https://business.didit.me',
        'auth' => 'https://business.didit.me'
    ],
    'api.didit.me' => [
        'base' => 'https://api.didit.me', 
        'auth' => 'https://auth.didit.me'
    ]
];

$apiKey = 'eAGnZjD0oiOtJUPlHgkzElCQlReHRpBhVmZJ1OXPGhY';

echo "Testing Didit API Endpoints...\n\n";

foreach ($endpoints as $name => $urls) {
    echo "Testing: {$name}\n";
    echo "Base URL: {$urls['base']}\n";
    echo "Auth URL: {$urls['auth']}\n";
    
    // Test session creation endpoint
    $sessionUrl = $urls['base'] . '/v2/session';
    echo "Session URL: {$sessionUrl}\n";
    
    try {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Api-Key' => $apiKey,
        ])->timeout(10)->post($sessionUrl, [
            'vendor_data' => 'test-' . time(),
            'metadata' => ['test' => true]
        ]);
        
        echo "Status: " . $response->status() . "\n";
        
        if ($response->successful()) {
            echo "✅ SUCCESS - This endpoint works!\n";
            $data = $response->json();
            if (isset($data['session_id'])) {
                echo "Session ID: " . $data['session_id'] . "\n";
            }
        } else {
            echo "❌ Failed - Status: " . $response->status() . "\n";
            echo "Response: " . substr($response->body(), 0, 200) . "...\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}