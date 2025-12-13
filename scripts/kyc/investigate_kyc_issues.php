<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 INVESTIGATING KYC VERIFICATION ISSUES\n";
echo "========================================\n\n";

// Check 1: Users table KYC status
echo "1. 📊 Users Table KYC Status:\n";
$usersByStatus = DB::table('users')
    ->select('kyc_status', DB::raw('count(*) as count'))
    ->groupBy('kyc_status')
    ->get();

foreach ($usersByStatus as $status) {
    echo "   - {$status->kyc_status}: {$status->count} users\n";
}

// Check 2: Users with session IDs but no verification data
echo "\n2. 🔗 Users with session IDs but missing verification data:\n";
$usersWithSessionIds = DB::table('users')
    ->whereNotNull('kyc_session_id')
    ->where('kyc_session_id', '!=', '')
    ->get();

if ($usersWithSessionIds->count() > 0) {
    foreach ($usersWithSessionIds as $user) {
        $hasVerificationData = DB::table('kyc_verifications')
            ->where('user_id', $user->id)
            ->exists();
        
        $hasKycData = DB::table('kyc_data')
            ->where('user_id', $user->id)
            ->exists();
        
        echo "   - User {$user->id} ({$user->email}): Session {$user->kyc_session_id}\n";
        echo "     Status: {$user->kyc_status}, Has Verification: " . ($hasVerificationData ? 'Yes' : 'No') . 
             ", Has KYC Data: " . ($hasKycData ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "   ✅ No users with session IDs found\n";
}

// Check 3: KYC Verifications table
echo "\n3. 📋 KYC Verifications Table:\n";
try {
    $verificationCount = DB::table('kyc_verifications')->count();
    echo "   - Total verifications: {$verificationCount}\n";
    
    if ($verificationCount > 0) {
        $verificationsByStatus = DB::table('kyc_verifications')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        foreach ($verificationsByStatus as $status) {
            echo "   - Status {$status->status}: {$status->count} verifications\n";
        }
        
        // Show recent verifications
        echo "\n   Recent verifications:\n";
        $recentVerifications = DB::table('kyc_verifications')
            ->join('users', 'kyc_verifications.user_id', '=', 'users.id')
            ->select('kyc_verifications.*', 'users.email')
            ->orderBy('kyc_verifications.created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentVerifications as $verification) {
            echo "   - User {$verification->user_id} ({$verification->email}): {$verification->status} - {$verification->created_at}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error accessing kyc_verifications table: " . $e->getMessage() . "\n";
}

// Check 4: KYC Data table
echo "\n4. 💾 KYC Data Table:\n";
try {
    $kycDataCount = DB::table('kyc_data')->count();
    echo "   - Total KYC data records: {$kycDataCount}\n";
    
    if ($kycDataCount > 0) {
        $recentKycData = DB::table('kyc_data')
            ->join('users', 'kyc_data.user_id', '=', 'users.id')
            ->select('kyc_data.*', 'users.email')
            ->orderBy('kyc_data.created_at', 'desc')
            ->limit(5)
            ->get();
        
        echo "   Recent KYC data entries:\n";
        foreach ($recentKycData as $data) {
            echo "   - User {$data->user_id} ({$data->email}): {$data->created_at}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error accessing kyc_data table: " . $e->getMessage() . "\n";
}

// Check 5: Recent webhook logs
echo "\n5. 📝 Recent KYC Webhook Logs (last 10):\n";
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        
        $kycWebhookLines = [];
        foreach (array_reverse($lines) as $line) {
            if (stripos($line, 'kyc') !== false && stripos($line, 'webhook') !== false) {
                $kycWebhookLines[] = $line;
                if (count($kycWebhookLines) >= 10) break;
            }
        }
        
        if (count($kycWebhookLines) > 0) {
            foreach ($kycWebhookLines as $line) {
                echo "   " . substr($line, 0, 120) . "...\n";
            }
        } else {
            echo "   ✅ No recent webhook logs found\n";
        }
    } else {
        echo "   ❌ Log file not found\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error reading logs: " . $e->getMessage() . "\n";
}

// Check 6: Current environment configuration
echo "\n6. ⚙️ Current Environment Configuration:\n";
$config = [
    'APP_ENV' => env('APP_ENV'),
    'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false',
    'DIDIT_BASE_URL' => env('DIDIT_BASE_URL'),
    'DIDIT_CALLBACK_URL' => env('DIDIT_CALLBACK_URL'),
    'DIDIT_REDIRECT_URL' => env('DIDIT_REDIRECT_URL'),
];

foreach ($config as $key => $value) {
    echo "   - {$key}: " . ($value ?: 'NOT SET') . "\n";
}

// Check 7: Route accessibility
echo "\n7. 🌐 KYC Route Accessibility:\n";
$routes = [
    'kyc.start.form' => '/kyc/start',
    'kyc.webhook' => '/api/kyc/webhook',
    'kyc.success' => '/kyc/success',
    'kyc.failure' => '/kyc/failure',
];

foreach ($routes as $routeName => $routePath) {
    try {
        $url = route($routeName);
        echo "   ✅ Route '{$routeName}': {$url}\n";
    } catch (\Exception $e) {
        echo "   ❌ Route '{$routeName}': ERROR - " . $e->getMessage() . "\n";
    }
}

// Check 8: Potential Issues
echo "\n8. ⚠️ Potential Issues Identified:\n";

// Issue 1: Users with kyc_status but no verification records
$usersWithoutVerifications = DB::table('users')
    ->whereIn('kyc_status', ['verified', 'failed'])
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
              ->from('kyc_verifications')
              ->whereRaw('kyc_verifications.user_id = users.id');
    })
    ->count();

if ($usersWithoutVerifications > 0) {
    echo "   ⚠️ {$usersWithoutVerifications} users have KYC status but no verification records\n";
    echo "      This suggests data inconsistency between old and new KYC systems\n";
}

// Issue 2: Orphaned session IDs
$orphanedSessions = DB::table('users')
    ->whereNotNull('kyc_session_id')
    ->where('kyc_session_id', '!=', '')
    ->where('kyc_status', 'pending')
    ->count();

if ($orphanedSessions > 0) {
    echo "   ⚠️ {$orphanedSessions} users have session IDs but status is still 'pending'\n";
    echo "      This might indicate failed webhook processing\n";
}

// Recommendations
echo "\n9. 💡 Recommendations:\n";

if ($usersWithoutVerifications > 0) {
    echo "   - Run migration to sync legacy KYC data with new verification system\n";
}

if ($orphanedSessions > 0) {
    echo "   - Reset stuck KYC sessions: php artisan kyc:reset --all\n";
}

echo "   - Test webhook endpoint: curl -X POST " . env('DIDIT_CALLBACK_URL', '') . "\n";
echo "   - Clear application cache: php artisan cache:clear\n";
echo "   - Check ngrok tunnel is active and accessible\n";

echo "\n=== INVESTIGATION COMPLETE ===\n";

?>
