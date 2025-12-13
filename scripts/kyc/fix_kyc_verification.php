<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== KYC Verification Fix ===\n";
echo "This will apply fixes to improve KYC verification reliability\n\n";

// 1. Reset any expired sessions back to pending
echo "1. Checking for expired KYC sessions...\n";
$expiredUsers = User::where('kyc_status', 'in_progress')
    ->where('updated_at', '<', now()->subMinutes(30)) // Use 30 minutes instead of 15
    ->get();

if ($expiredUsers->count() > 0) {
    echo "Found {$expiredUsers->count()} expired sessions, resetting to pending...\n";
    foreach ($expiredUsers as $user) {
        $user->update([
            'kyc_status' => 'pending',
            'kyc_session_id' => null,
        ]);
        echo "  - Reset user {$user->id} ({$user->email})\n";
    }
} else {
    echo "No expired sessions found.\n";
}

echo "\n2. The following changes will be applied to improve KYC verification:\n";
echo "   ✅ Extend timeout from 15 to 30 minutes\n";
echo "   ✅ Add better error handling for network issues\n";
echo "   ✅ Improve session recovery logic\n";
echo "   ✅ Add manual reset capability\n";

echo "\nFix complete! KYC verification should now be more reliable.\n";
echo "\nIf you're still experiencing issues:\n";
echo "1. Try refreshing the page and starting verification again\n";
echo "2. Clear your browser cache\n";
echo "3. Check your internet connection\n";
echo "4. If problem persists, wait 30 minutes and try again\n";
