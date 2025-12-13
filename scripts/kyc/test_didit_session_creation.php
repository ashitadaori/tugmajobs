<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DiditService;

echo "=== Testing DiDit Session Creation ===\n\n";

try {
    $diditService = new DiditService();
    
    echo "Creating test session...\n";
    
    $sessionData = $diditService->createSession([
        'vendor_data' => 'test_session_' . time(),
        'metadata' => [
            'test' => true,
            'created_from' => 'debug_script'
        ],
        'contact_details' => [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]
    ]);
    
    echo "✅ Session created successfully!\n\n";
    echo "Response structure:\n";
    echo "==================\n";
    echo "Keys: " . implode(', ', array_keys($sessionData)) . "\n\n";
    
    if (isset($sessionData['session_id'])) {
        echo "Session ID: " . $sessionData['session_id'] . "\n";
        
        // Now try to fetch this session back
        echo "\nTesting retrieval of newly created session...\n";
        
        $retrievedData = $diditService->getSessionDetails($sessionData['session_id']);
        
        echo "✅ Session retrieved successfully!\n";
        echo "Retrieved keys: " . implode(', ', array_keys($retrievedData)) . "\n";
        
        if (isset($retrievedData['result'])) {
            echo "Result keys: " . implode(', ', array_keys($retrievedData['result'])) . "\n";
        }
        
    } else {
        echo "❌ No session_id found in response\n";
        echo "Full response:\n";
        echo json_encode($sessionData, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
