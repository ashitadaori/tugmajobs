<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== Users with in_progress KYC status ===\n";
$users = User::where('kyc_status', 'in_progress')->get();

if ($users->count() > 0) {
    foreach ($users as $user) {
        echo "User ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "Status: {$user->kyc_status}\n";
        echo "Session ID: " . ($user->kyc_session_id ?? 'null') . "\n";
        echo "Updated At: {$user->updated_at}\n";
        echo "Can Start: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n";
        echo "Session Timed Out: " . ($user->hasKycSessionTimedOut() ? 'Yes' : 'No') . "\n";
        
        $now = now();
        $timeDiff = $now->diffInMinutes($user->updated_at);
        echo "Minutes since update: {$timeDiff}\n";
        echo "---\n";
    }
} else {
    echo "No users with in_progress status found.\n";
}

echo "\n=== KYC Status Distribution ===\n";
$statusCounts = User::selectRaw('kyc_status, COUNT(*) as count')
    ->groupBy('kyc_status')
    ->get();

foreach ($statusCounts as $status) {
    echo ($status->kyc_status ?? 'null') . ": {$status->count} users\n";
}
