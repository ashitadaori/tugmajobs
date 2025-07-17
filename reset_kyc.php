<?php

// This script resets a user's KYC status to allow trying the live verification

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the authenticated user or specify a user ID
$userId = $argv[1] ?? null;
$email = $argv[2] ?? null;

// If no user ID provided, but email is provided
if (!$userId && $email) {
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        $userId = $user->id;
    }
}

// If still no user ID, try to get the currently authenticated user
if (!$userId) {
    // List recent users to choose from
    $users = \App\Models\User::select('id', 'name', 'email', 'role', 'kyc_status')
        ->latest()
        ->take(10)
        ->get();
    
    echo "Available users:\n";
    echo "ID\tName\tEmail\tRole\tKYC Status\n";
    echo "----------------------------------------\n";
    
    foreach ($users as $user) {
        echo "{$user->id}\t{$user->name}\t{$user->email}\t{$user->role}\t{$user->kyc_status}\n";
    }
    
    echo "\nPlease provide a user ID as the first argument.\n";
    echo "Usage: php reset_kyc.php [user_id] [or email]\n";
    exit(1);
}

try {
    // Find the user
    $user = \App\Models\User::find($userId);
    
    if (!$user) {
        echo "User with ID {$userId} not found.\n";
        exit(1);
    }
    
    // Store the current status for reporting
    $oldStatus = $user->kyc_status;
    
    // Reset KYC status
    $user->update([
        'kyc_status' => 'pending',
        'kyc_session_id' => null,
        'kyc_completed_at' => null,
        'kyc_verified_at' => null,
        'kyc_data' => null
    ]);
    
    echo "KYC status reset successfully for user {$user->name} (ID: {$user->id}).\n";
    echo "Previous status: {$oldStatus}\n";
    echo "New status: {$user->kyc_status}\n";
    echo "\nYou can now start the live verification process by visiting /kyc/start\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}