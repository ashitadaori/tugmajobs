<?php

require __DIR__ . '/vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== User KYC Status Check ===\n\n";

$user = \App\Models\User::where('email', 'khenrick.herana@gmail.com')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "✅ User Found: {$user->name}\n";
echo "📧 Email: {$user->email}\n";
echo "🆔 User ID: {$user->id}\n";
echo "🔐 KYC Status: {$user->kyc_status}\n";
echo "✅ Is KYC Verified: " . ($user->isKycVerified() ? 'YES' : 'NO') . "\n";
echo "📅 Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->toDateTimeString() : 'NULL') . "\n";
echo "🔗 Session ID: " . ($user->kyc_session_id ?? 'NULL') . "\n";

echo "\n=== Status Check Complete ===\n";
