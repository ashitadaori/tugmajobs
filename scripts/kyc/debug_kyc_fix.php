<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;

echo "=== KYC Debug & Fix Tool ===\n\n";

// Get user ID from command line or default to 3
$userId = $argv[1] ?? 3;

echo "Checking user ID: {$userId}\n";
echo "==============================\n\n";

$user = User::find($userId);

if (!$user) {
    echo "❌ User with ID {$userId} not found\n";
    exit(1);
}

echo "👤 User Information:\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Role: {$user->role}\n";
echo "   KYC Status: {$user->kyc_status}\n";
echo "   Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
echo "   Updated At: " . $user->updated_at->format('Y-m-d H:i:s') . "\n";
echo "   Can Start Verification: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n";
echo "\n";

// Check KYC verification records
$kycVerifications = KycVerification::where('user_id', $user->id)->get();
echo "📋 KYC Verification Records: " . $kycVerifications->count() . "\n";
foreach ($kycVerifications as $verification) {
    echo "   - ID: {$verification->id}, Session: {$verification->session_id}, Status: {$verification->status}\n";
}
echo "\n";

// Check KYC data records
$kycDataRecords = KycData::where('user_id', $user->id)->get();
echo "📊 KYC Data Records: " . $kycDataRecords->count() . "\n";
foreach ($kycDataRecords as $data) {
    echo "   - ID: {$data->id}, Session: {$data->session_id}, Status: {$data->status}\n";
}
echo "\n";

// Check if user is stuck in 'in_progress' state
if ($user->kyc_status === 'in_progress') {
    $timeDiff = now()->diffInMinutes($user->updated_at);
    echo "⚠️  User is stuck in 'in_progress' status for {$timeDiff} minutes\n";
    
    if ($timeDiff > 30) {
        echo "🔧 Auto-fixing: Resetting expired session...\n";
        
        // Delete KYC records
        KycVerification::where('user_id', $user->id)->delete();
        KycData::where('user_id', $user->id)->delete();
        
        // Reset user status
        $user->update([
            'kyc_status' => 'pending',
            'kyc_session_id' => null,
            'kyc_completed_at' => null,
            'kyc_verified_at' => null,
            'kyc_data' => null
        ]);
        
        echo "✅ User reset to 'pending' status\n";
    } else {
        echo "⏳ Session is still within the 30-minute timeout period\n";
    }
}

echo "\n🔧 Available Actions:\n";
echo "1. To reset this user: php reset_kyc.php {$user->id}\n";
echo "2. To reset all users: php reset_kyc.php all\n";
echo "3. To test the frontend: Open test_kyc_verification_debug.html\n";
echo "4. To check user status: php check_user_kyc_status.php\n";

echo "\n💡 Troubleshooting Tips:\n";
echo "- If modal closes immediately: Check browser console for errors\n";
echo "- If 'Verification failed' appears: Check Laravel logs and network tab\n";
echo "- If iframe doesn't load: Check DiDit configuration and API keys\n";
echo "- If status doesn't update: Check webhook configuration\n";

echo "\n🌐 Quick Network Test:\n";
try {
    $response = file_get_contents(env('APP_URL') . '/kyc/check-status', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode(['user_id' => $user->id])
        ]
    ]));
    
    if ($response) {
        $data = json_decode($response, true);
        echo "✅ Network connectivity OK\n";
        echo "   Status endpoint response: " . json_encode($data) . "\n";
    } else {
        echo "❌ Network test failed\n";
    }
} catch (Exception $e) {
    echo "❌ Network error: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
