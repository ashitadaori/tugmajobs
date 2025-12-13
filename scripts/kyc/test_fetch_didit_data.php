<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\DiditService;

echo "=== Testing DiDit Data Fetch for khenrick.herana@gmail.com ===\n\n";

try {
    // Find the user by email
    $user = User::where('email', 'khenrick.herana@gmail.com')->first();
    
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "✅ User Found:\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- KYC Session ID: " . ($user->kyc_session_id ?? 'null') . "\n";
    echo "- KYC Status: " . ($user->kyc_status ?? 'null') . "\n";
    echo "\n";
    
    if (!$user->kyc_session_id) {
        echo "❌ No session ID found\n";
        exit(1);
    }
    
    echo "🔍 Attempting to fetch data from DiDit API...\n";
    
    $diditService = new DiditService();
    $sessionDetails = $diditService->getSessionDetails($user->kyc_session_id);
    
    echo "📋 DiDit API Response:\n";
    echo "- Response status: " . ($sessionDetails ? 'Success' : 'Failed') . "\n";
    
    if ($sessionDetails) {
        echo "- Response keys: " . implode(', ', array_keys($sessionDetails)) . "\n";
        
        if (isset($sessionDetails['result'])) {
            echo "- Result keys: " . implode(', ', array_keys($sessionDetails['result'])) . "\n";
            
            // Check for document images
            $result = $sessionDetails['result'];
            $imageFound = false;
            
            if (isset($result['document_images']) && is_array($result['document_images'])) {
                echo "- Document images found: " . count($result['document_images']) . " images\n";
                $imageFound = true;
            }
            
            if (isset($result['images']) && is_array($result['images'])) {
                echo "- Images found: " . count($result['images']) . " images\n";
                $imageFound = true;
            }
            
            if (isset($result['documents']) && is_array($result['documents'])) {
                echo "- Documents array found with " . count($result['documents']) . " documents\n";
                foreach ($result['documents'] as $i => $doc) {
                    if (isset($doc['images']) && is_array($doc['images'])) {
                        echo "  - Document {$i} has " . count($doc['images']) . " images\n";
                        $imageFound = true;
                    }
                }
            }
            
            if (!$imageFound) {
                echo "- No document images found in response\n";
                echo "- Full result structure:\n";
                echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            }
        } else {
            echo "- No 'result' key in response\n";
            echo "- Full response:\n";
            echo json_encode($sessionDetails, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ Failed to fetch data from DiDit API\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
