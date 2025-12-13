<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Checking Current KYC Status\n";
echo "==============================\n";

// Get the user
$user = \App\Models\User::find(1);

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "👤 User Information:\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   KYC Status: {$user->kyc_status}\n";
echo "   Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
echo "   Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'Not set') . "\n";
echo "   Updated At: " . $user->updated_at->format('Y-m-d H:i:s') . "\n";
echo "\n";

echo "📊 KYC Data Stored:\n";
if ($user->kyc_data) {
    echo json_encode($user->kyc_data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   No KYC data stored\n";
}
echo "\n";

echo "🔔 Recent Notifications:\n";
$notifications = \App\Models\Notification::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($notifications->count() > 0) {
    foreach ($notifications as $notification) {
        echo "   [{$notification->created_at->format('Y-m-d H:i:s')}] {$notification->title}\n";
        echo "      {$notification->message}\n";
        echo "      Type: {$notification->type}, Read: " . ($notification->read_at ? 'Yes' : 'No') . "\n";
        echo "\n";
    }
} else {
    echo "   No notifications found\n";
}

echo "📝 Recent Laravel Logs (KYC related):\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    
    // Filter for KYC-related logs
    $kycLogs = array_filter($lines, function($line) {
        return stripos($line, 'kyc') !== false || 
               stripos($line, 'webhook') !== false || 
               stripos($line, 'didit') !== false;
    });
    
    $recentKycLogs = array_slice($kycLogs, -10); // Last 10 KYC-related logs
    
    if (!empty($recentKycLogs)) {
        foreach ($recentKycLogs as $log) {
            echo "   " . trim($log) . "\n";
        }
    } else {
        echo "   No recent KYC-related logs found\n";
    }
} else {
    echo "   Log file not found\n";
}

echo "\n";
echo "🌐 Current Configuration:\n";
echo "   Callback URL: " . config('services.didit.callback_url') . "\n";
echo "   Redirect URL: " . config('services.didit.redirect_url') . "\n";
echo "   Webhook Secret: " . (config('services.didit.webhook_secret') ? 'Set (' . strlen(config('services.didit.webhook_secret')) . ' chars)' : 'Not set') . "\n";

echo "\n";
echo "🧪 Testing Webhook Route:\n";
try {
    $response = \Illuminate\Support\Facades\Http::get(url('/api/kyc/webhook'));
    echo "   GET request status: " . $response->status() . "\n";
    echo "   Route exists: ✅\n";
} catch (\Exception $e) {
    echo "   Error testing route: " . $e->getMessage() . "\n";
}

echo "\n";
echo "💡 Recommendations:\n";
if ($user->kyc_status !== 'verified') {
    echo "   1. The user status is '{$user->kyc_status}' - webhook may not have been received\n";
    echo "   2. Check if ngrok is running and accessible\n";
    echo "   3. Verify webhook URL is correct in Didit dashboard\n";
    echo "   4. Check if webhook signature is valid\n";
} else {
    echo "   1. User is verified in database - frontend may need refresh\n";
    echo "   2. Check if status polling is working correctly\n";
    echo "   3. Clear browser cache and refresh page\n";
}

echo "\n";
echo "🔧 Quick Fixes:\n";
echo "   To manually set user as verified: php artisan kyc:set-status {$user->id} verified\n";
echo "   To reset and try again: php artisan kyc:reset {$user->id}\n";
echo "   To test webhook manually: php test_new_webhook.php\n";