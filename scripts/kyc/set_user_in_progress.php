<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Setting User to In Progress ===\n\n";

$user = User::find(3);
if ($user) {
    $user->update([
        'kyc_status' => 'in_progress',
        'kyc_session_id' => 'test-session-' . time()
    ]);
    
    echo "User: {$user->name}\n";
    echo "KYC Status updated to: {$user->fresh()->kyc_status}\n";
    echo "Session ID: {$user->fresh()->kyc_session_id}\n";
    echo "Can Start Verification: " . ($user->fresh()->canStartKycVerification() ? 'Yes' : 'No') . "\n";
} else {
    echo "User not found\n";
}

echo "\n=== Update Complete ===\n";