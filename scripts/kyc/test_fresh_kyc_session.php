<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

echo "=== Testing Fresh KYC Session ===\n\n";

try {
    // Find the user
    $user = User::where('email', 'khenrick.herana@gmail.com')->first();
    
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "✅ User Found: {$user->name} (ID: {$user->id})\n";
    echo "Current KYC Status: {$user->kyc_status}\n\n";
    
    // Reset user for testing (simulate starting fresh)
    echo "🔄 Resetting KYC status for testing...\n";
    $user->update([
        'kyc_status' => 'pending',
        'kyc_session_id' => null,
        'kyc_verified_at' => null,
        'kyc_completed_at' => null,
    ]);
    
    echo "✅ User reset to pending status\n\n";
    
    // Now let's test the webhook with a fresh session ID
    echo "📝 Creating new test session...\n";
    $newSessionId = 'test_session_' . time();
    
    // Update user with new session (simulating starting verification)
    $user->update([
        'kyc_status' => 'in_progress',
        'kyc_session_id' => $newSessionId,
    ]);
    
    echo "✅ User updated with new session ID: {$newSessionId}\n\n";
    
    // Test the webhook with mock completed verification
    echo "🧪 Testing webhook with successful verification...\n";
    
    $mockWebhookData = [
        'session_id' => $newSessionId,
        'status' => 'verified',
        'event_type' => 'verification.completed',
        'timestamp' => now()->toISOString(),
        'data' => [
            'verification_id' => 'mock_verification_' . time(),
            'user_data' => [
                'first_name' => 'khenrick',
                'last_name' => 'herana',
                'date_of_birth' => '1990-01-01',
                'document_type' => 'passport',
                'document_number' => 'MOCK123456',
                'nationality' => 'US',
                'address' => [
                    'street' => '123 Test Street',
                    'city' => 'Test City',
                    'country' => 'United States'
                ]
            ],
            'verification_result' => [
                'status' => 'verified',
                'confidence_score' => 0.95,
                'checks' => [
                    'document_authenticity' => 'passed',
                    'face_match' => 'passed',
                    'liveness_check' => 'passed'
                ]
            ]
        ]
    ];
    
    // Create signature
    $webhookSecret = env('DIDIT_WEBHOOK_SECRET');
    $payload = json_encode($mockWebhookData);
    $signature = hash_hmac('sha256', $payload, $webhookSecret);
    
    echo "Generated payload for session: {$newSessionId}\n";
    echo "Payload size: " . strlen($payload) . " bytes\n";
    echo "Generated signature: sha256={$signature}\n\n";
    
    // Send the webhook
    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Didit-Signature' => 'sha256=' . $signature,
            'User-Agent' => 'Didit-Webhook/1.0',
        ])->timeout(15)->post(env('DIDIT_CALLBACK_URL'), $mockWebhookData);
        
        echo "Webhook Response Status: " . $response->status() . "\n";
        echo "Response Body: " . $response->body() . "\n";
        
        if ($response->successful()) {
            echo "✅ Webhook call successful!\n\n";
            
            // Check if user was updated
            $user->refresh();
            echo "📊 User Status After Webhook:\n";
            echo "- KYC Status: {$user->kyc_status}\n";
            echo "- Session ID: {$user->kyc_session_id}\n";
            echo "- Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
            echo "- Is Verified: " . ($user->isKycVerified() ? 'Yes' : 'No') . "\n";
            
            if ($user->kyc_status === 'verified') {
                echo "🎉 SUCCESS! KYC verification completed via webhook!\n";
            } else {
                echo "⚠️ Webhook processed but status not updated to verified\n";
            }
            
        } else {
            echo "❌ Webhook call failed with status: " . $response->status() . "\n";
            echo "Response: " . $response->body() . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Webhook call failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";

// Instructions
echo "\n📋 NEXT STEPS:\n\n";
echo "1. If the test above was successful, your webhook is now working!\n";
echo "2. To test with a real Didit verification:\n";
echo "   - Go to your KYC start page\n";
echo "   - Start a new verification\n";
echo "   - Complete the verification on Didit\n";
echo "   - Check if your status updates automatically\n\n";
echo "3. If you're still having issues:\n";
echo "   - Check the Laravel logs: storage/logs/laravel.log\n";
echo "   - Verify ngrok is running and accessible\n";
echo "   - Make sure the webhook secret in Didit matches your .env file\n\n";
echo "4. To disable signature bypass for production:\n";
echo "   - Remove DIDIT_WEBHOOK_BYPASS_SIGNATURE=true from .env\n";
echo "   - Or set it to false\n\n";
