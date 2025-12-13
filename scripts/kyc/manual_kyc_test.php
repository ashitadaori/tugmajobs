<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\KycVerification;

echo "🧪 Manual KYC Test - Set User to Verified\n";
echo "========================================\n\n";

if (!isset($argv[1])) {
    echo "Usage: php manual_kyc_test.php <user_id>\n";
    echo "Example: php manual_kyc_test.php 1\n\n";
    
    echo "Available users:\n";
    $users = User::take(10)->get();
    foreach ($users as $user) {
        echo "  #{$user->id} - {$user->name} ({$user->email}) - Status: {$user->kyc_status}\n";
    }
    exit(1);
}

$userId = intval($argv[1]);
$user = User::find($userId);

if (!$user) {
    echo "❌ User #{$userId} not found!\n";
    exit(1);
}

echo "Found user: {$user->name} ({$user->email})\n";
echo "Current KYC Status: {$user->kyc_status}\n\n";

echo "Setting user to 'verified' status...\n";

// Create a KYC verification record if it doesn't exist
$kycVerification = $user->kycVerifications()->first();
if (!$kycVerification) {
    $kycVerification = KycVerification::create([
        'user_id' => $user->id,
        'session_id' => 'manual-test-' . time(),
        'status' => 'verified',
        'verified_at' => now(),
        'completed_at' => now(),
        'verification_data' => [
            'source' => 'manual_test',
            'timestamp' => now()->toISOString()
        ],
        'firstname' => explode(' ', $user->name)[0] ?? 'Test',
        'lastname' => explode(' ', $user->name)[1] ?? 'User',
    ]);
    echo "✅ Created KYC verification record\n";
}

// Update user status
$user->update([
    'kyc_status' => 'verified',
    'kyc_verified_at' => now(),
    'kyc_completed_at' => now(),
    'kyc_session_id' => null
]);

echo "✅ User updated successfully!\n\n";

echo "New status:\n";
echo "- User KYC Status: {$user->fresh()->kyc_status}\n";
echo "- Verified At: {$user->fresh()->kyc_verified_at}\n";
echo "- Is Verified: " . ($user->fresh()->isKycVerified() ? 'Yes' : 'No') . "\n\n";

echo "🎯 Now test the application:\n";
echo "1. Log in as this user\n";
echo "2. The KYC verification popup should NOT appear\n";
echo "3. Check browser console for '[KYC]' logs\n";
echo "4. The status should show as 'Verified'\n\n";

echo "To reset back to pending:\n";
echo "php manual_kyc_test.php {$userId} reset\n\n";

if (isset($argv[2]) && $argv[2] === 'reset') {
    echo "Resetting user back to pending...\n";
    $user->update([
        'kyc_status' => 'pending',
        'kyc_verified_at' => null,
        'kyc_completed_at' => null,
        'kyc_session_id' => null
    ]);
    echo "✅ User reset to pending status\n";
}

echo "✅ Manual test complete!\n";
