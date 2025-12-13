<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DiditService;

echo "=== Testing Didit Session Creation ===\n\n";

try {
    $diditService = new DiditService();
    
    $testPayload = [
        'vendor_data' => 'test-user-' . time(),
        'metadata' => [
            'user_id' => 'test',
            'user_type' => 'jobseeker',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ],
        'contact_details' => [
            'email' => 'test@example.com',
            'email_lang' => 'en',
        ]
    ];
    
    echo "Creating session with payload:\n";
    echo json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";
    
    $response = $diditService->createSession($testPayload);
    
    echo "Didit Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($response['url'])) {
        echo "Verification URL: " . $response['url'] . "\n";
        
        // Parse the URL to see what parameters it contains
        $parsedUrl = parse_url($response['url']);
        echo "URL Host: " . $parsedUrl['host'] . "\n";
        echo "URL Path: " . $parsedUrl['path'] . "\n";
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            echo "URL Parameters:\n";
            foreach ($queryParams as $key => $value) {
                echo "  {$key}: {$value}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";