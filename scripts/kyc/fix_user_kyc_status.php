<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KycData;
use App\Models\Notification;

echo "🔧 Fixing User KYC Status After Webhook Issues\n";
echo "===============================================\n\n";

try {
    // Get the user that was having issues (user ID 1 - khenrick herana)
    $user = User::find(1);
    
    if (!$user) {
        echo "❌ User not found!\n";
        exit(1);
    }
    
    echo "👤 User: {$user->name} ({$user->email})\n";
    echo "📊 Current Status: {$user->kyc_status}\n";
    echo "📅 Last Updated: {$user->updated_at}\n";
    echo "🔑 Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
    echo "✅ Verified At: " . ($user->kyc_verified_at ?? 'Not verified') . "\n\n";
    
    // Check if user has valid KYC data showing successful verification
    $kycData = KycData::where('user_id', $user->id)
        ->where('session_id', $user->kyc_session_id)
        ->where('status', 'verified')
        ->first();
    
    if ($kycData) {
        echo "🎯 Found valid KYC data record!\n";
        echo "   - Status in KYC Data: {$kycData->status}\n";
        echo "   - Didit Status: {$kycData->didit_status}\n";
        echo "   - Verified At: {$kycData->verified_at}\n";
        echo "   - Has personal data: " . ($kycData->first_name ? 'Yes' : 'No') . "\n";
        echo "   - Has images: " . (($kycData->front_image_url && $kycData->back_image_url) ? 'Yes' : 'No') . "\n\n";
        
        // User has valid verification data but status might be wrong
        if ($user->kyc_status !== 'verified') {
            echo "🔄 Fixing user status to match KYC data...\n";
            $user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => $kycData->verified_at ?? $kycData->created_at ?? now(),
            ]);
            echo "✅ User status updated to 'verified'\n";
        } else {
            echo "✅ User status is already correct\n";
        }
    } else {
        echo "⚠️  No valid KYC data found\n";
        echo "🔍 Checking for any KYC data records...\n";
        
        $allKycData = KycData::where('user_id', $user->id)->get();
        if ($allKycData->count() > 0) {
            echo "   Found {$allKycData->count()} KYC data record(s):\n";
            foreach ($allKycData as $data) {
                echo "   - Session: {$data->session_id}, Status: {$data->status}, Created: {$data->created_at}\n";
            }
        } else {
            echo "   No KYC data records found\n";
        }
        
        // Check if user status should be reset to pending
        if (in_array($user->kyc_status, ['failed', 'in_progress']) && !$user->kyc_verified_at) {
            echo "\n🔄 Resetting user to pending status...\n";
            $user->update([
                'kyc_status' => 'pending',
                'kyc_session_id' => null,
                'kyc_verified_at' => null,
                'kyc_data' => null
            ]);
            echo "✅ User reset to pending status\n";
        }
    }
    
    // Clean up old failed notifications
    echo "\n🔔 Cleaning up old failed notifications...\n";
    $failedNotifications = Notification::where('user_id', $user->id)
        ->where('type', 'error')
        ->where('title', 'LIKE', '%Verification Failed%')
        ->where('created_at', '<', now()->subMinutes(5)) // Only old ones
        ->get();
    
    if ($failedNotifications->count() > 0) {
        foreach ($failedNotifications as $notification) {
            $notification->delete();
        }
        echo "   Deleted {$failedNotifications->count()} old failed notifications\n";
    } else {
        echo "   No old failed notifications to clean up\n";
    }
    
    // Check current notification status
    $recentNotifications = Notification::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();
    
    echo "\n🔔 Recent notifications:\n";
    if ($recentNotifications->count() > 0) {
        foreach ($recentNotifications as $notification) {
            $readStatus = $notification->read_at ? 'Read' : 'Unread';
            echo "   [{$notification->created_at}] {$notification->title} - {$notification->type} ({$readStatus})\n";
        }
    } else {
        echo "   No recent notifications\n";
    }
    
    // Final status
    $user->refresh();
    echo "\n📈 Final Status:\n";
    echo "   User Status: {$user->kyc_status}\n";
    echo "   Verified At: " . ($user->kyc_verified_at ?? 'Not verified') . "\n";
    echo "   Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
    echo "   Can Start Verification: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n";
    echo "   Needs Verification: " . ($user->needsKycVerification() ? 'Yes' : 'No') . "\n";
    
    if ($user->kyc_status === 'verified') {
        echo "\n🎉 SUCCESS: User is properly verified!\n";
        echo "   The user can now access all platform features.\n";
        echo "   No further action needed.\n";
    } elseif ($user->kyc_status === 'pending') {
        echo "\n🔄 RESET: User status reset to pending.\n";
        echo "   The user can start a new verification process.\n";
        echo "   Visit: " . env('APP_URL') . "/kyc/start\n";
    } else {
        echo "\n⚠️  ATTENTION: User status is '{$user->kyc_status}'\n";
        echo "   This may require manual intervention.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ KYC Status Fix Complete!\n";
