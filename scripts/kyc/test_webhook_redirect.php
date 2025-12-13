<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Http;

echo "=== Testing Webhook Redirect Handling ===\n\n";

try {
    // Find the user
    $user = User::where('email', 'khenrick.herana@gmail.com')->first();
    
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "✅ User Found: {$user->name} (ID: {$user->id})\n";
    
    // Set up a test session
    $testSession = 'redirect_test_' . time();
    $user->update([
        'kyc_status' => 'in_progress',
        'kyc_session_id' => $testSession,
        'kyc_verified_at' => null,
    ]);
    
    echo "📝 Set up test session: {$testSession}\n";
    echo "Current status: {$user->kyc_status}\n\n";
    
    // Test different redirect scenarios
    $testCases = [
        [
            'name' => 'Redirect with no parameters (assumes success)',
            'url' => env('DIDIT_CALLBACK_URL'),
            'expected_status' => 'verified'
        ],
        [
            'name' => 'Redirect with session_id and success status',
            'url' => env('DIDIT_CALLBACK_URL') . '?session_id=' . $testSession . '&status=completed',
            'expected_status' => 'verified'
        ],
        [
            'name' => 'Redirect with failure status',
            'url' => env('DIDIT_CALLBACK_URL') . '?session_id=' . $testSession . '&status=failed',
            'expected_status' => 'failed'
        ],
    ];
    
    foreach ($testCases as $index => $testCase) {
        echo ($index + 1) . ". 🧪 Testing: {$testCase['name']}\n";
        
        // Reset user status for each test
        $user->update([
            'kyc_status' => 'in_progress',
            'kyc_session_id' => $testSession,
            'kyc_verified_at' => null,
        ]);
        
        try {
            // Make GET request to webhook URL (simulating user redirect)
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->timeout(15)->get($testCase['url']);
            
            echo "   Response Status: {$response->status()}\n";
            
            if ($response->status() >= 200 && $response->status() < 400) {
                // Check if user status was updated
                $user->refresh();
                echo "   User Status After: {$user->kyc_status}\n";
                
                if ($user->kyc_status === $testCase['expected_status']) {
                    echo "   ✅ PASS - Status updated correctly\n";
                } else {
                    echo "   ❌ FAIL - Expected '{$testCase['expected_status']}', got '{$user->kyc_status}'\n";
                }
                
                // Check if redirect happened
                if ($response->status() >= 300 && $response->status() < 400) {
                    echo "   🔀 Redirected to: " . ($response->header('Location') ?? 'unknown') . "\n";
                }
                
            } else {
                echo "   ❌ Request failed with status: {$response->status()}\n";
                echo "   Response: " . substr($response->body(), 0, 200) . "\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Request failed: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Final status check
    echo "📊 Final User Status:\n";
    $user->refresh();
    echo "- KYC Status: {$user->kyc_status}\n";
    echo "- Session ID: {$user->kyc_session_id}\n";
    echo "- Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "- Is Verified: " . ($user->isKycVerified() ? 'Yes' : 'No') . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "=== Test Complete ===\n\n";

echo "📋 WHAT THIS TEST SHOWS:\n";
echo "- How Didit redirects users to your webhook URL after verification\n";
echo "- How your system handles different redirect scenarios\n";
echo "- Whether user status gets properly updated from redirects\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. If tests pass, your webhook redirect handling is working\n";
echo "2. When you complete real KYC verification on Didit, your status should update\n";
echo "3. Check Laravel logs for detailed information about the redirect\n";
echo "4. If status still doesn't update, check the Didit webhook configuration\n\n";
