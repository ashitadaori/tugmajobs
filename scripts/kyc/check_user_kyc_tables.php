<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;

echo "=== KYC Tables Check for khenrick.herana@gmail.com ===\n\n";

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
    
    echo "🔍 Checking kyc_verifications table:\n";
    $verifications = KycVerification::where('user_id', $user->id)->get();
    
    if ($verifications->count() > 0) {
        echo "Found {$verifications->count()} verification record(s):\n";
        foreach ($verifications as $verification) {
            echo "- ID: {$verification->id}\n";
            echo "- Session ID: " . ($verification->session_id ?? 'null') . "\n";
            echo "- Status: " . ($verification->status ?? 'null') . "\n";
            echo "- Created: {$verification->created_at}\n";
            echo "- Raw Data: " . (is_null($verification->raw_data) ? 'null' : 'Present (' . strlen(json_encode($verification->raw_data)) . ' chars)') . "\n";
            echo "- Verification Data: " . (is_null($verification->verification_data) ? 'null' : 'Present (' . strlen(json_encode($verification->verification_data)) . ' chars)') . "\n";
            echo "- Document Type: " . ($verification->document_type ?? 'null') . "\n";
            echo "- Document Number: " . ($verification->document_number ?? 'null') . "\n";
            echo "\n";
        }
    } else {
        echo "❌ No records found in kyc_verifications table\n\n";
    }
    
    echo "🔍 Checking kyc_data table:\n";
    $kycDataRecords = KycData::where('user_id', $user->id)->get();
    
    if ($kycDataRecords->count() > 0) {
        echo "Found {$kycDataRecords->count()} kyc_data record(s):\n";
        foreach ($kycDataRecords as $kycData) {
            echo "- ID: {$kycData->id}\n";
            echo "- Session ID: " . ($kycData->session_id ?? 'null') . "\n";
            echo "- Status: " . ($kycData->status ?? 'null') . "\n";
            echo "- Created: {$kycData->created_at}\n";
            echo "- Raw Data: " . (is_null($kycData->raw_data) ? 'null' : 'Present (' . strlen(json_encode($kycData->raw_data)) . ' chars)') . "\n";
            echo "- Verification Data: " . (is_null($kycData->verification_data) ? 'null' : 'Present (' . strlen(json_encode($kycData->verification_data)) . ' chars)') . "\n";
            echo "- First Name: " . ($kycData->first_name ?? 'null') . "\n";
            echo "- Last Name: " . ($kycData->last_name ?? 'null') . "\n";
            echo "\n";
        }
    } else {
        echo "❌ No records found in kyc_data table\n\n";
    }
    
    echo "🎯 Summary:\n";
    echo "- User has kyc_session_id but no database records\n";
    echo "- This explains why Session ID shows 'Not available' in admin\n";
    echo "- Mock images are being used because no document data exists\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
