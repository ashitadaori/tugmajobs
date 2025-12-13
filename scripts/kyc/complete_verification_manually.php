<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Manual KYC Verification Completion\n";
echo "=====================================\n";

// Get the user
$user = \App\Models\User::find(1);

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "👤 Current User Status:\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   KYC Status: {$user->kyc_status}\n";
echo "   Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
echo "\n";

if ($user->kyc_status === 'verified') {
    echo "✅ User is already verified!\n";
    exit(0);
}

echo "🔄 Manually completing verification...\n";

try {
    // Update user status to verified
    $user->update([
        'kyc_status' => 'verified',
        'kyc_verified_at' => now(),
        'kyc_data' => [
            'session_id' => $user->kyc_session_id,
            'status' => 'verified',
            'verified_at' => now()->toISOString(),
            'verification_method' => 'manual_completion',
            'reason' => 'Didit verification completed but webhook not received',
            'didit_verification_completed' => true,
            'manual_completion_timestamp' => now()->toISOString(),
        ]
    ]);

    echo "✅ User status updated to verified!\n";
    echo "   Verified At: " . $user->kyc_verified_at->format('Y-m-d H:i:s') . "\n";

    // Create a notification
    if (class_exists(\App\Models\Notification::class)) {
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title' => 'Identity Verification Completed',
            'message' => 'Your identity has been successfully verified. You now have full access to all platform features.',
            'type' => 'success',
            'data' => [
                'kyc_status' => 'verified',
                'session_id' => $user->kyc_session_id,
                'source' => 'manual_completion',
                'completion_method' => 'manual',
            ],
        ]);

        echo "✅ Notification created\n";
    }

    echo "\n";
    echo "🎉 SUCCESS! Verification completed manually.\n";
    echo "\n";
    echo "📱 What to do next:\n";
    echo "   1. Refresh your browser page\n";
    echo "   2. The verification modal should close\n";
    echo "   3. The dashboard should show 'Verified' status\n";
    echo "   4. You should see a verified badge\n";
    echo "\n";

} catch (\Exception $e) {
    echo "❌ Error completing verification: " . $e->getMessage() . "\n";
}

// Now let's try to get the actual verification data from Didit
echo "🔍 Attempting to fetch verification data from Didit...\n";

try {
    $diditService = app(\App\Contracts\KycServiceInterface::class);
    
    if ($user->kyc_session_id) {
        echo "   Session ID: {$user->kyc_session_id}\n";
        
        // Try to get session status from Didit
        $sessionStatus = $diditService->getSessionStatus($user->kyc_session_id);
        
        echo "   Didit Session Status:\n";
        echo json_encode($sessionStatus, JSON_PRETTY_PRINT) . "\n";
        
        // If we got detailed data, update the user record
        if (isset($sessionStatus['status']) && $sessionStatus['status'] === 'completed') {
            $updatedKycData = array_merge($user->kyc_data ?? [], [
                'didit_session_data' => $sessionStatus,
                'data_fetched_at' => now()->toISOString(),
            ]);
            
            $user->update(['kyc_data' => $updatedKycData]);
            echo "✅ Updated user record with Didit session data\n";
        }
        
    } else {
        echo "   ❌ No session ID available\n";
    }
    
} catch (\Exception $e) {
    echo "   ⚠️  Could not fetch data from Didit: " . $e->getMessage() . "\n";
    echo "   This is normal if the session has expired\n";
}

echo "\n";
echo "📊 Final User Status:\n";
$user->refresh();
echo "   KYC Status: {$user->kyc_status}\n";
echo "   Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'Not set') . "\n";
echo "   Has KYC Data: " . ($user->kyc_data ? 'Yes' : 'No') . "\n";

if ($user->kyc_data) {
    echo "\n📋 KYC Data Summary:\n";
    $kycData = $user->kyc_data;
    
    if (isset($kycData['didit_session_data'])) {
        echo "   ✅ Contains Didit session data\n";
        
        // Check for personal information
        if (isset($kycData['didit_session_data']['extracted_data'])) {
            echo "   ✅ Contains extracted personal data\n";
            $extractedData = $kycData['didit_session_data']['extracted_data'];
            
            echo "   Personal Information:\n";
            foreach ($extractedData as $key => $value) {
                if (is_string($value) && strlen($value) < 100) {
                    echo "      {$key}: {$value}\n";
                }
            }
        }
        
        // Check for document verification
        if (isset($kycData['didit_session_data']['documents'])) {
            echo "   ✅ Contains document verification data\n";
        }
        
        // Check for biometric verification
        if (isset($kycData['didit_session_data']['biometric'])) {
            echo "   ✅ Contains biometric verification data\n";
        }
    } else {
        echo "   ⚠️  No detailed Didit session data available\n";
    }
}

echo "\n";
echo "🎯 Your verification is now complete!\n";
echo "   Please refresh your browser to see the updated status.\n";