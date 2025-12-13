<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DiditService;
use App\Models\User;

echo "=== Fetching Complete Didit Verification Data ===\n\n";

try {
    // Find the user
    $user = User::where('email', 'khenrick.herana@gmail.com')->first();
    
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "👤 User: {$user->name} ({$user->email})\n";
    echo "🔑 Session ID: {$user->kyc_session_id}\n\n";
    
    if (!$user->kyc_session_id) {
        echo "❌ No KYC session ID found\n";
        exit(1);
    }
    
    // Initialize Didit service
    $diditService = new DiditService();
    
    echo "📡 Fetching complete verification data from Didit...\n\n";
    
    // Get session status with full details
    $sessionData = $diditService->getSessionStatus($user->kyc_session_id);
    
    echo "📋 Complete Session Data:\n";
    echo json_encode($sessionData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Check if there are any additional endpoints we can call
    echo "🔍 Analyzing available data:\n";
    
    if (isset($sessionData['status'])) {
        echo "- Status: " . $sessionData['status'] . "\n";
    }
    
    if (isset($sessionData['documents'])) {
        echo "- Documents: " . count($sessionData['documents']) . " found\n";
        foreach ($sessionData['documents'] as $index => $doc) {
            echo "  Document " . ($index + 1) . ":\n";
            if (isset($doc['type'])) echo "    Type: " . $doc['type'] . "\n";
            if (isset($doc['status'])) echo "    Status: " . $doc['status'] . "\n";
            if (isset($doc['images'])) echo "    Images: " . count($doc['images']) . " files\n";
        }
    }
    
    if (isset($sessionData['biometric'])) {
        echo "- Biometric data: Available\n";
        if (isset($sessionData['biometric']['selfie'])) {
            echo "  Selfie: Available\n";
        }
        if (isset($sessionData['biometric']['liveness'])) {
            echo "  Liveness: " . $sessionData['biometric']['liveness'] . "\n";
        }
    }
    
    if (isset($sessionData['verification_result'])) {
        echo "- Verification Result:\n";
        $result = $sessionData['verification_result'];
        if (isset($result['identity_verified'])) {
            echo "  Identity Verified: " . ($result['identity_verified'] ? 'Yes' : 'No') . "\n";
        }
        if (isset($result['document_verified'])) {
            echo "  Document Verified: " . ($result['document_verified'] ? 'Yes' : 'No') . "\n";
        }
        if (isset($result['biometric_verified'])) {
            echo "  Biometric Verified: " . ($result['biometric_verified'] ? 'Yes' : 'No') . "\n";
        }
    }
    
    if (isset($sessionData['extracted_data'])) {
        echo "- Extracted Data:\n";
        $extracted = $sessionData['extracted_data'];
        if (isset($extracted['full_name'])) {
            echo "  Full Name: " . $extracted['full_name'] . "\n";
        }
        if (isset($extracted['date_of_birth'])) {
            echo "  Date of Birth: " . $extracted['date_of_birth'] . "\n";
        }
        if (isset($extracted['document_number'])) {
            echo "  Document Number: " . $extracted['document_number'] . "\n";
        }
        if (isset($extracted['nationality'])) {
            echo "  Nationality: " . $extracted['nationality'] . "\n";
        }
    }
    
    // Check if we should update the database with this complete data
    echo "\n💾 Current database KYC data:\n";
    echo json_encode($user->kyc_data, JSON_PRETTY_PRINT) . "\n";
    
    echo "\n🤔 Should we update the database with complete Didit data? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
        echo "\n💾 Updating database with complete verification data...\n";
        
        $user->update([
            'kyc_data' => array_merge($user->kyc_data ?? [], [
                'complete_session_data' => $sessionData,
                'updated_at' => now()->toIso8601String(),
                'data_source' => 'didit_api_fetch'
            ])
        ]);
        
        echo "✅ Database updated with complete verification data!\n";
    } else {
        echo "⏭️  Skipping database update.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fetch Complete ===\n";