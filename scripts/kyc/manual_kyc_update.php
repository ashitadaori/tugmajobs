<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set up the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Manual KYC Status Update ===\n\n";

// Find the employer with in_progress status
$employer = User::where('role', 'employer')
    ->where('email', 'khenrick.herana@gmail.com')
    ->first();

if (!$employer) {
    echo "❌ Employer not found\n";
    exit;
}

echo "Found employer:\n";
echo "- ID: {$employer->id}\n";
echo "- Name: {$employer->name}\n";
echo "- Email: {$employer->email}\n";
echo "- Current KYC Status: {$employer->kyc_status}\n\n";

// Update the KYC status to verified
echo "Updating KYC status to verified...\n";

$employer->update([
    'kyc_status' => 'verified',
    'kyc_verified_at' => now(),
    'kyc_data' => [
        'session_id' => $employer->kyc_session_id ?? 'manual-' . time(),
        'status' => 'completed',
        'completed_at' => now()->toIso8601String(),
        'manual_update' => true,
        'updated_by' => 'manual_script',
        'updated_at' => now()->toIso8601String()
    ]
]);

echo "✅ KYC status updated successfully!\n";
echo "- New Status: {$employer->kyc_status}\n";
echo "- Verified At: " . $employer->kyc_verified_at->format('Y-m-d H:i:s') . "\n";
echo "- isKycVerified(): " . ($employer->isKycVerified() ? 'true' : 'false') . "\n\n";

echo "Now refresh your browser page and the status should be updated automatically.\n";
echo "The KYC completion handler will detect the change and redirect you to the employer dashboard.\n";