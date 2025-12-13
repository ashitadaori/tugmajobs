<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;
use App\Models\Notification;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 FIXING KYC VERIFICATION ISSUES\n";
echo "==================================\n\n";

// Fix 1: Reset users stuck in in_progress status
echo "1. 🔄 Fixing users stuck in 'in_progress' status:\n";
$stuckUsers = DB::table('users')
    ->where('kyc_status', 'in_progress')
    ->where('updated_at', '<', now()->subMinutes(30))
    ->get();

foreach ($stuckUsers as $user) {
    echo "   - Resetting user {$user->id} ({$user->email}) from in_progress to pending\n";
    
    DB::table('users')
        ->where('id', $user->id)
        ->update([
            'kyc_status' => 'pending',
            'kyc_session_id' => null,
            'updated_at' => now()
        ]);
        
    // Also clean up any orphaned verification records
    DB::table('kyc_verifications')
        ->where('user_id', $user->id)
        ->where('status', 'in_progress')
        ->update(['status' => 'expired', 'updated_at' => now()]);
}

if (count($stuckUsers) == 0) {
    echo "   ✅ No users stuck in 'in_progress' status\n";
}

// Fix 2: Sync verification data for verified users without verification records
echo "\n2. 🔗 Syncing legacy KYC data with new verification system:\n";
$usersWithoutVerifications = DB::table('users')
    ->whereIn('kyc_status', ['verified', 'failed'])
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
              ->from('kyc_verifications')
              ->whereRaw('kyc_verifications.user_id = users.id');
    })
    ->get();

foreach ($usersWithoutVerifications as $user) {
    echo "   - Creating verification record for user {$user->id} ({$user->email})\n";
    
    $verificationData = [
        'user_id' => $user->id,
        'session_id' => $user->kyc_session_id ?? 'legacy_' . $user->id,
        'status' => $user->kyc_status,
        'created_at' => $user->kyc_verified_at ?? $user->updated_at,
        'updated_at' => $user->kyc_verified_at ?? $user->updated_at,
    ];
    
    if ($user->kyc_status === 'verified') {
        $verificationData['verified_at'] = $user->kyc_verified_at ?? $user->updated_at;
    }
    
    DB::table('kyc_verifications')->insertOrIgnore($verificationData);
}

if (count($usersWithoutVerifications) == 0) {
    echo "   ✅ All verified users have verification records\n";
}

// Fix 3: Clean up orphaned session IDs for pending users
echo "\n3. 🧹 Cleaning up orphaned session IDs:\n";
$orphanedSessions = DB::table('users')
    ->whereNotNull('kyc_session_id')
    ->where('kyc_session_id', '!=', '')
    ->where('kyc_status', 'pending')
    ->where('updated_at', '<', now()->subHours(2))
    ->get();

foreach ($orphanedSessions as $user) {
    echo "   - Cleaning session ID for user {$user->id} ({$user->email})\n";
    
    DB::table('users')
        ->where('id', $user->id)
        ->update([
            'kyc_session_id' => null,
            'updated_at' => now()
        ]);
}

if (count($orphanedSessions) == 0) {
    echo "   ✅ No orphaned session IDs found\n";
}

// Fix 4: Update user statuses based on verification records
echo "\n4. 🔄 Synchronizing user statuses with verification records:\n";
$verifications = DB::table('kyc_verifications')
    ->join('users', 'kyc_verifications.user_id', '=', 'users.id')
    ->select('kyc_verifications.*', 'users.kyc_status as user_kyc_status')
    ->where('kyc_verifications.status', '!=', DB::raw('users.kyc_status'))
    ->get();

foreach ($verifications as $verification) {
    echo "   - Updating user {$verification->user_id} status from '{$verification->user_kyc_status}' to '{$verification->status}'\n";
    
    $updateData = ['kyc_status' => $verification->status];
    
    if ($verification->status === 'verified' && $verification->verified_at) {
        $updateData['kyc_verified_at'] = $verification->verified_at;
    }
    
    DB::table('users')
        ->where('id', $verification->user_id)
        ->update($updateData);
}

if (count($verifications) == 0) {
    echo "   ✅ User statuses are in sync with verification records\n";
}

// Fix 5: Create missing notifications for verified users
echo "\n5. 📢 Creating missing notifications for KYC status changes:\n";
$usersWithoutNotifications = DB::table('users')
    ->whereIn('kyc_status', ['verified', 'failed'])
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
              ->from('notifications')
              ->whereRaw('notifications.user_id = users.id')
              ->where('notifications.data->kyc_status', DB::raw('users.kyc_status'));
    })
    ->get();

foreach ($usersWithoutNotifications as $user) {
    echo "   - Creating notification for user {$user->id} ({$user->email}) - Status: {$user->kyc_status}\n";
    
    $notificationTitle = '';
    $notificationMessage = '';
    $notificationType = 'info';
    
    switch ($user->kyc_status) {
        case 'verified':
            $notificationTitle = 'Identity Verification Completed';
            $notificationMessage = 'Your identity has been successfully verified. You now have full access to all platform features.';
            $notificationType = 'success';
            break;
        case 'failed':
            $notificationTitle = 'Identity Verification Failed';
            $notificationMessage = 'Your identity verification was unsuccessful. Please try again with clear documents and good lighting.';
            $notificationType = 'error';
            break;
    }
    
    if ($notificationTitle) {
        try {
            DB::table('notifications')->insert([
                'user_id' => $user->id,
                'title' => $notificationTitle,
                'message' => $notificationMessage,
                'type' => $notificationType,
                'data' => json_encode([
                    'kyc_status' => $user->kyc_status,
                    'source' => 'kyc_fix_script',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            echo "     ❌ Failed to create notification: " . $e->getMessage() . "\n";
        }
    }
}

if (count($usersWithoutNotifications) == 0) {
    echo "   ✅ All users have appropriate notifications\n";
}

// Fix 6: Validate webhook endpoint
echo "\n6. 🌐 Testing webhook endpoint:\n";
try {
    $webhookUrl = env('DIDIT_CALLBACK_URL');
    echo "   - Webhook URL: {$webhookUrl}\n";
    
    // Test with a GET request first to check if route is accessible
    $response = file_get_contents($webhookUrl . '?test=1', false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]));
    
    if ($response !== false) {
        echo "   ✅ Webhook endpoint is accessible\n";
    } else {
        echo "   ❌ Webhook endpoint is not accessible\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Failed to test webhook: " . $e->getMessage() . "\n";
}

// Summary
echo "\n7. 📊 Final Status Summary:\n";
$finalStatus = DB::table('users')
    ->select('kyc_status', DB::raw('count(*) as count'))
    ->groupBy('kyc_status')
    ->get();

foreach ($finalStatus as $status) {
    echo "   - {$status->kyc_status}: {$status->count} users\n";
}

$verificationCount = DB::table('kyc_verifications')->count();
$kycDataCount = DB::table('kyc_data')->count();

echo "\n   Database records:\n";
echo "   - Verification records: {$verificationCount}\n";
echo "   - KYC data records: {$kycDataCount}\n";

echo "\n8. 💡 Next Steps:\n";
echo "   - Test KYC flow: Visit " . route('kyc.start.form') . "\n";
echo "   - Check logs: tail -f storage/logs/laravel.log\n";
echo "   - Clear cache: php artisan cache:clear\n";
echo "   - Restart queue workers if using queues: php artisan queue:restart\n";

echo "\n=== KYC ISSUES FIXED SUCCESSFULLY ===\n";

?>
