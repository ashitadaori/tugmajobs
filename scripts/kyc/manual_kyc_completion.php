<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== Manual KYC Completion for khenrick.herana@gmail.com ===\n\n";

try {
    // Find the user
    $user = User::where('email', 'khenrick.herana@gmail.com')->first();
    
    if (!$user) {
        echo "❌ User not found with email: khenrick.herana@gmail.com\n";
        exit(1);
    }
    
    echo "✅ User Found:\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- Email: {$user->email}\n";
    echo "- Current KYC Status: {$user->kyc_status}\n";
    echo "- Session ID: {$user->kyc_session_id}\n\n";
    
    // Check if already verified
    if ($user->kyc_status === 'verified') {
        echo "⚠️  User is already verified. No changes needed.\n";
        exit(0);
    }
    
    // Manual completion based on your successful Didit verification
    echo "🔧 Manually completing KYC verification...\n";
    
    $user->update([
        'kyc_status' => 'verified',
        'kyc_verified_at' => now(),
        'kyc_completed_at' => now(),
        'kyc_data' => array_merge($user->kyc_data ?? [], [
            'manual_completion' => true,
            'completion_reason' => 'Didit verification completed successfully but webhook failed',
            'completed_by' => 'manual_script',
            'completed_at' => now()->toISOString(),
            'original_session_id' => $user->kyc_session_id,
            'didit_verification_status' => 'completed',
            'verification_method' => 'manual_completion_script',
            'notes' => 'User successfully completed Didit verification but webhook was not received. Manual completion based on successful verification screenshot.'
        ])
    ]);
    
    // Log the manual completion
    Log::info('Manual KYC verification completion', [
        'user_id' => $user->id,
        'user_email' => $user->email,
        'previous_status' => 'in_progress',
        'new_status' => 'verified',
        'completion_method' => 'manual_script',
        'reason' => 'Didit webhook failed but verification was successful'
    ]);
    
    echo "✅ KYC verification completed successfully!\n\n";
    
    // Verify the update
    $user->refresh();
    echo "📊 Updated User Status:\n";
    echo "- KYC Status: {$user->kyc_status}\n";
    echo "- KYC Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "- KYC Completed At: " . ($user->kyc_completed_at ? $user->kyc_completed_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "- Is KYC Verified: " . ($user->isKycVerified() ? 'Yes' : 'No') . "\n";
    echo "- Needs KYC Verification: " . ($user->needsKycVerification() ? 'Yes' : 'No') . "\n";
    echo "- Can Start KYC Verification: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n\n";
    
    echo "🎉 Success! Your KYC verification is now complete.\n";
    echo "When you reload your dashboard, you should see the verification as completed.\n\n";
    
    // Create a notification if the Notification model exists
    if (class_exists(\App\Models\Notification::class)) {
        try {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'title' => 'Identity Verification Completed',
                'message' => 'Your identity has been successfully verified. You now have full access to all platform features.',
                'type' => 'success',
                'data' => [
                    'kyc_status' => 'verified',
                    'session_id' => $user->kyc_session_id,
                    'source' => 'manual_completion',
                    'completion_method' => 'script',
                ],
            ]);
            
            echo "📬 Notification created for user.\n";
        } catch (\Exception $e) {
            echo "⚠️  Could not create notification: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== Manual Completion Complete ===\n";
