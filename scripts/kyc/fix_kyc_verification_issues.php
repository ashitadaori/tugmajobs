<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;
use App\Models\EmployerDocument;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

echo "🚀 Starting Comprehensive KYC Verification Fix\n";
echo "=============================================\n\n";

try {
    // Step 1: Clean up all existing KYC data
    echo "📊 Step 1: Analyzing current KYC data...\n";
    
    $totalUsers = User::count();
    $verifiedUsers = User::where('kyc_status', 'verified')->count();
    $pendingUsers = User::where('kyc_status', 'pending')->count();
    $inProgressUsers = User::where('kyc_status', 'in_progress')->count();
    $failedUsers = User::where('kyc_status', 'failed')->count();
    
    echo "   - Total users: {$totalUsers}\n";
    echo "   - Verified: {$verifiedUsers}\n";
    echo "   - Pending: {$pendingUsers}\n";
    echo "   - In Progress: {$inProgressUsers}\n";
    echo "   - Failed: {$failedUsers}\n\n";
    
    // Step 2: Clean up orphaned KYC records
    echo "🧹 Step 2: Cleaning up orphaned KYC records...\n";
    
    $kycVerifications = KycVerification::count();
    $kycDataRecords = KycData::count();
    $employerDocuments = EmployerDocument::count();
    
    echo "   - KYC Verification records: {$kycVerifications}\n";
    echo "   - KYC Data records: {$kycDataRecords}\n";
    echo "   - Employer Document records: {$employerDocuments}\n";
    
    // Delete all KYC verification records
    if ($kycVerifications > 0) {
        KycVerification::query()->delete();
        echo "   ✅ Deleted all KYC verification records\n";
    }
    
    // Delete all KYC data records
    if ($kycDataRecords > 0) {
        KycData::query()->delete();
        echo "   ✅ Deleted all KYC data records\n";
    }
    
    // Delete all employer documents and their files
    if ($employerDocuments > 0) {
        $documents = EmployerDocument::all();
        foreach ($documents as $document) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        }
        EmployerDocument::query()->delete();
        echo "   ✅ Deleted all employer documents and files\n";
    }
    
    echo "\n";
    
    // Step 3: Reset all users to clean state
    echo "🔄 Step 3: Resetting all users to clean KYC state...\n";
    
    DB::transaction(function () {
        $users = User::all();
        
        foreach ($users as $user) {
            $oldStatus = $user->kyc_status;
            
            // Reset all KYC fields to clean state
            $user->update([
                'kyc_status' => 'pending',
                'kyc_session_id' => null,
                'kyc_completed_at' => null,
                'kyc_verified_at' => null,
                'kyc_data' => null
            ]);
            
            echo "   - User #{$user->id} ({$user->name}): {$oldStatus} → pending\n";
        }
        
        echo "   ✅ All users reset to pending status\n";
    });
    
    echo "\n";
    
    // Step 4: Clean up KYC-related notifications
    echo "🔔 Step 4: Cleaning up old KYC notifications...\n";
    
    $kycNotifications = Notification::where('data->source', 'kyc_webhook')
        ->orWhere('title', 'LIKE', '%Verification%')
        ->orWhere('title', 'LIKE', '%Identity%')
        ->count();
    
    if ($kycNotifications > 0) {
        Notification::where('data->source', 'kyc_webhook')
            ->orWhere('title', 'LIKE', '%Verification%')
            ->orWhere('title', 'LIKE', '%Identity%')
            ->delete();
        echo "   ✅ Deleted {$kycNotifications} old KYC notifications\n";
    } else {
        echo "   ℹ️  No old KYC notifications to clean up\n";
    }
    
    echo "\n";
    
    // Step 5: Test webhook route accessibility
    echo "🌐 Step 5: Testing webhook route accessibility...\n";
    
    $webhookUrl = env('APP_URL') . '/api/kyc/webhook';
    echo "   - Webhook URL: {$webhookUrl}\n";
    
    // Test if the webhook route responds
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);
        
        $response = file_get_contents($webhookUrl, false, $context);
        $httpCode = null;
        
        if (isset($http_response_header[0])) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
            $httpCode = $matches[1] ?? null;
        }
        
        if ($httpCode) {
            echo "   - Webhook route responds with HTTP {$httpCode}\n";
            if (in_array($httpCode, ['200', '405'])) { // 405 is expected for GET on POST route
                echo "   ✅ Webhook route is accessible\n";
            } else {
                echo "   ⚠️  Webhook route may have issues (HTTP {$httpCode})\n";
            }
        }
    } catch (Exception $e) {
        echo "   ❌ Webhook route test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Step 6: Verify Didit configuration
    echo "⚙️  Step 6: Verifying Didit configuration...\n";
    
    $diditApiKey = env('DIDIT_API_KEY');
    $diditWebhookSecret = env('DIDIT_WEBHOOK_SECRET');
    $appUrl = env('APP_URL');
    
    echo "   - API Key: " . ($diditApiKey ? "Set (" . strlen($diditApiKey) . " chars)" : "❌ NOT SET") . "\n";
    echo "   - Webhook Secret: " . ($diditWebhookSecret ? "Set (" . strlen($diditWebhookSecret) . " chars)" : "❌ NOT SET") . "\n";
    echo "   - App URL: " . ($appUrl ? $appUrl : "❌ NOT SET") . "\n";
    
    if (!$diditApiKey || !$diditWebhookSecret || !$appUrl) {
        echo "   ⚠️  Some Didit configuration values are missing!\n";
    } else {
        echo "   ✅ Didit configuration looks complete\n";
    }
    
    echo "\n";
    
    // Step 7: Create test notifications for verification
    echo "🧪 Step 7: Creating test notifications to verify system...\n";
    
    $firstUser = User::first();
    if ($firstUser) {
        Notification::create([
            'user_id' => $firstUser->id,
            'title' => 'KYC System Reset Complete',
            'message' => 'Your KYC verification system has been reset and is ready for use. You can now start a fresh verification process.',
            'type' => 'success',
            'data' => [
                'source' => 'kyc_reset',
                'reset_timestamp' => now()->toISOString(),
            ],
        ]);
        
        echo "   ✅ Created test notification for user #{$firstUser->id}\n";
    }
    
    echo "\n";
    
    // Step 8: Final status report
    echo "📈 Step 8: Final status report...\n";
    
    $finalStats = [
        'total_users' => User::count(),
        'pending_users' => User::where('kyc_status', 'pending')->count(),
        'verified_users' => User::where('kyc_status', 'verified')->count(),
        'other_status_users' => User::whereNotIn('kyc_status', ['pending', 'verified'])->count(),
        'users_with_session_ids' => User::whereNotNull('kyc_session_id')->count(),
        'total_notifications' => Notification::count(),
        'kyc_verifications' => KycVerification::count(),
        'kyc_data_records' => KycData::count(),
        'employer_documents' => EmployerDocument::count(),
    ];
    
    foreach ($finalStats as $key => $value) {
        echo "   - " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
    }
    
    echo "\n";
    
    // Step 9: Instructions for users
    echo "📝 Step 9: Next steps and instructions...\n";
    echo "   1. All users can now start fresh KYC verification\n";
    echo "   2. Visit: " . env('APP_URL') . "/kyc/start\n";
    echo "   3. Ensure ngrok is running if using local development\n";
    echo "   4. Check that Didit webhook URL is set correctly in Didit dashboard\n";
    echo "   5. Monitor logs for any new verification attempts\n";
    
    echo "\n✅ KYC Verification Fix Complete!\n";
    echo "================================================\n";
    echo "🎉 All KYC data has been cleaned and reset.\n";
    echo "🔄 Users can now start fresh verification processes.\n";
    echo "🔍 Monitor the system for any new issues.\n\n";
    
    if ($finalStats['users_with_session_ids'] > 0) {
        echo "⚠️  WARNING: " . $finalStats['users_with_session_ids'] . " users still have session IDs.\n";
        echo "   This might indicate a database issue. Run this script again if needed.\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during KYC fix: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Script completed successfully! 🎯\n";
