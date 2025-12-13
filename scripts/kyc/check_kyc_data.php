<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== KYC Data Check for khenrick.herana@gmail.com ===\n\n";

try {
    // Find the user by email
    $user = User::where('email', 'khenrick.herana@gmail.com')->first();
    
    if (!$user) {
        echo "❌ User not found with email: khenrick.herana@gmail.com\n";
        exit(1);
    }
    
    echo "✅ User Found:\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- Email: {$user->email}\n";
    echo "- Role: {$user->role}\n";
    echo "\n";
    
    echo "📋 KYC Information:\n";
    echo "- KYC Status: " . ($user->kyc_status ?? 'null') . "\n";
    echo "- KYC Session ID: " . ($user->kyc_session_id ?? 'null') . "\n";
    echo "- KYC Completed At: " . ($user->kyc_completed_at ? $user->kyc_completed_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "- KYC Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "\n";
    
    echo "📊 KYC Status Methods:\n";
    echo "- isKycVerified(): " . ($user->isKycVerified() ? 'true' : 'false') . "\n";
    echo "- needsKycVerification(): " . ($user->needsKycVerification() ? 'true' : 'false') . "\n";
    echo "- canStartKycVerification(): " . ($user->canStartKycVerification() ? 'true' : 'false') . "\n";
    echo "\n";
    
    echo "🔍 KYC Data (JSON):\n";
    if ($user->kyc_data) {
        echo json_encode($user->kyc_data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "No KYC data stored\n";
    }
    echo "\n";
    
    // Check if there are any recent logs related to this user
    echo "📝 Recent Activity:\n";
    echo "- Created At: " . $user->created_at->format('Y-m-d H:i:s') . "\n";
    echo "- Updated At: " . $user->updated_at->format('Y-m-d H:i:s') . "\n";
    
    // Show verification badge status
    echo "\n🏆 Verification Status:\n";
    echo "- Status Badge: " . strip_tags($user->kyc_status_badge) . "\n";
    echo "- Status Text: " . $user->kyc_status_text . "\n";
    echo "- Verified Badge: " . ($user->isKycVerified() ? 'Has verified badge' : 'No verified badge') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";