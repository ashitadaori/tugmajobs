<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'khenrick.herana@gmail.com')->first();
if ($user) {
    echo "Current User Status:\n";
    echo "- KYC Status: {$user->kyc_status}\n";
    echo "- Session ID: {$user->kyc_session_id}\n";
    echo "- Updated: {$user->updated_at}\n";
    echo "- Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
    
    if ($user->kyc_data) {
        echo "- KYC Data Keys: " . implode(', ', array_keys($user->kyc_data)) . "\n";
    }
} else {
    echo "User not found\n";
}
