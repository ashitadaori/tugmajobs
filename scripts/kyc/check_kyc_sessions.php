<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;

echo "=== Recent KYC Sessions ===\n\n";

echo "KYC Verifications Table:\n";
echo "========================\n";
$verifications = KycVerification::latest()->take(10)->get(['id', 'user_id', 'session_id', 'status', 'created_at']);
foreach ($verifications as $v) {
    $user = User::find($v->user_id);
    $userEmail = $user ? $user->email : 'Unknown';
    echo "ID: {$v->id}, User: {$userEmail}, Session: {$v->session_id}, Status: {$v->status}, Created: {$v->created_at}\n";
}

echo "\nKYC Data Table:\n";
echo "===============\n";
$kycDataRecords = KycData::latest()->take(10)->get(['id', 'user_id', 'session_id', 'status', 'created_at']);
foreach ($kycDataRecords as $d) {
    $user = User::find($d->user_id);
    $userEmail = $user ? $user->email : 'Unknown';
    echo "ID: {$d->id}, User: {$userEmail}, Session: {$d->session_id}, Status: {$d->status}, Created: {$d->created_at}\n";
}

echo "\n=== Test Most Recent Session ===\n";

// Get the most recent session ID
$recentVerification = KycVerification::latest()->first();
$recentKycData = KycData::latest()->first();

$testSessionId = null;
if ($recentVerification && $recentVerification->session_id) {
    $testSessionId = $recentVerification->session_id;
    echo "Testing session from KYC Verifications: {$testSessionId}\n";
} elseif ($recentKycData && $recentKycData->session_id) {
    $testSessionId = $recentKycData->session_id;
    echo "Testing session from KYC Data: {$testSessionId}\n";
}

if ($testSessionId) {
    echo "Attempting to fetch session data from DiDit API...\n";
    
    try {
        $baseUrl = config('services.didit.base_url');
        $apiKey = config('services.didit.api_key');
        
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'X-Api-Key' => $apiKey,
        ])->get($baseUrl . '/v2/session/' . $testSessionId);
        
        echo "Status: " . $response->status() . "\n";
        if ($response->successful()) {
            $data = $response->json();
            echo "✅ Success! Keys: " . implode(', ', array_keys($data)) . "\n";
            
            // Check for document images
            if (isset($data['result']['documents'])) {
                echo "Documents found: " . count($data['result']['documents']) . "\n";
            }
            if (isset($data['images'])) {
                echo "Images found: " . count($data['images']) . "\n";
            }
        } else {
            echo "❌ Failed: " . $response->body() . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No session IDs found in database\n";
}

echo "\n=== Check Complete ===\n";
