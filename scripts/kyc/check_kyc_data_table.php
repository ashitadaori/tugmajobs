<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KycData;
use App\Models\User;

echo "=== Checking KYC Data Table ===\n\n";

$user = User::where('email', 'khenrick.herana@gmail.com')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "✅ User Found: {$user->name}\n";
echo "User ID: {$user->id}\n";
echo "Session ID: {$user->kyc_session_id}\n\n";

// Check KYC data table
$kycData = KycData::where('user_id', $user->id)->first();

if ($kycData) {
    echo "✅ KYC Data found in kyc_data table:\n";
    echo "- ID: {$kycData->id}\n";
    echo "- Status: {$kycData->status}\n";
    echo "- Session ID: {$kycData->session_id}\n";
    echo "- First Name: " . ($kycData->first_name ?? 'null') . "\n";
    echo "- Last Name: " . ($kycData->last_name ?? 'null') . "\n";
    echo "- Full Name: " . ($kycData->full_name ?? 'null') . "\n";
    echo "- Document Type: " . ($kycData->document_type ?? 'null') . "\n";
    echo "- Document Number: " . ($kycData->document_number ?? 'null') . "\n";
    echo "- Verified At: " . ($kycData->verified_at ? $kycData->verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "- Created At: " . $kycData->created_at->format('Y-m-d H:i:s') . "\n";
    echo "\n";
    
    if ($kycData->raw_payload) {
        echo "📋 Raw Payload Structure:\n";
        echo str_repeat("=", 50) . "\n";
        echo json_encode($kycData->raw_payload, JSON_PRETTY_PRINT) . "\n";
        echo str_repeat("=", 50) . "\n";
    } else {
        echo "❌ No raw payload stored\n";
    }
} else {
    echo "❌ No KYC data found in kyc_data table for user ID {$user->id}\n";
    
    // Check if there are any KYC data records at all
    $totalKycData = KycData::count();
    echo "Total KYC data records in database: {$totalKycData}\n";
    
    // Check with session ID
    if ($user->kyc_session_id) {
        $kycDataBySession = KycData::where('session_id', $user->kyc_session_id)->first();
        if ($kycDataBySession) {
            echo "✅ Found KYC data by session ID, but for different user: {$kycDataBySession->user_id}\n";
        } else {
            echo "❌ No KYC data found by session ID either\n";
        }
    }
}

echo "\n=== Check Complete ===\n";
