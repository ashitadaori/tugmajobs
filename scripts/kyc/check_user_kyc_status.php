<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Checking User KYC Status ===\n\n";

$user = User::find(3);
if ($user) {
    echo "User: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "KYC Status: {$user->kyc_status}\n";
    echo "Can Start Verification: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n";
    echo "Updated At: {$user->updated_at}\n";
    echo "Session ID: " . ($user->kyc_session_id ?? 'null') . "\n";
    
    if ($user->kyc_status === 'in_progress') {
        $thirtyMinutesAgo = now()->subMinutes(30);
        echo "30 minutes ago: {$thirtyMinutesAgo}\n";
        echo "Is session old? " . ($user->updated_at->lt($thirtyMinutesAgo) ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "User not found\n";
}

echo "\n=== Status Complete ===\n";