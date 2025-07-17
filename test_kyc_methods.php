<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing KYC User Methods:\n";

$user = \App\Models\User::first();

if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n";
    echo "KYC Status: " . $user->kyc_status . "\n";
    echo "Can Start KYC: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n";
    echo "Needs KYC: " . ($user->needsKycVerification() ? 'Yes' : 'No') . "\n";
    echo "Is Verified: " . ($user->isKycVerified() ? 'Yes' : 'No') . "\n";
    echo "Is In Progress: " . ($user->isKycInProgress() ? 'Yes' : 'No') . "\n";
    echo "Is Failed: " . ($user->isKycFailed() ? 'Yes' : 'No') . "\n";
} else {
    echo "No users found\n";
}