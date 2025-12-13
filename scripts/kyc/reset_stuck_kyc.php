<?php
/**
 * Reset stuck KYC sessions
 * This script resets users who are stuck in "in_progress" status for more than 30 minutes
 */

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Resetting Stuck KYC Sessions\n";
echo "============================\n\n";

try {
    // Find users stuck in in_progress status for more than 30 minutes
    $thirtyMinutesAgo = now()->subMinutes(30);
    
    $stuckUsers = \App\Models\User::where('kyc_status', 'in_progress')
        ->where('updated_at', '<', $thirtyMinutesAgo)
        ->get();
    
    echo "Found " . $stuckUsers->count() . " users stuck in 'in_progress' status\n\n";
    
    foreach ($stuckUsers as $user) {
        echo "Resetting user ID {$user->id} ({$user->email})\n";
        echo "  - Previous status: {$user->kyc_status}\n";
        echo "  - Last updated: {$user->updated_at}\n";
        
        // Reset to pending so they can try again
        $user->update([
            'kyc_status' => 'pending',
            'kyc_session_id' => null
        ]);
        
        echo "  - New status: pending\n";
        echo "  - Session ID cleared\n\n";
    }
    
    if ($stuckUsers->count() > 0) {
        echo "✅ Successfully reset " . $stuckUsers->count() . " stuck KYC sessions\n";
    } else {
        echo "✅ No stuck KYC sessions found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
?>