<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KycVerification;

echo "=== KYC Verification Data Check for khenrick.herana@gmail.com ===\n\n";

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
    
    // Check KYC verifications
    $verifications = $user->kycVerifications()->orderBy('created_at', 'desc')->get();
    
    echo "📋 KYC Verifications Count: " . $verifications->count() . "\n\n";
    
    if ($verifications->count() > 0) {
        foreach ($verifications as $index => $verification) {
            echo "🔍 Verification #" . ($index + 1) . ":\n";
            echo "- ID: {$verification->id}\n";
            echo "- Session ID: {$verification->session_id}\n";
            echo "- Status: {$verification->status}\n";
            echo "- Document Type: " . ($verification->document_type ?? 'Not set') . "\n";
            echo "- Document Number: " . ($verification->document_number ?? 'Not set') . "\n";
            echo "- Full Name: " . ($verification->full_name ?? 'Not set') . "\n";
            echo "- Date of Birth: " . ($verification->date_of_birth ? $verification->date_of_birth->format('Y-m-d') : 'Not set') . "\n";
            echo "- Gender: " . ($verification->gender ?? 'Not set') . "\n";
            echo "- Address: " . ($verification->address ?? 'Not set') . "\n";
            echo "- Nationality: " . ($verification->nationality ?? 'Not set') . "\n";
            echo "- Created At: " . $verification->created_at->format('Y-m-d H:i:s') . "\n";
            echo "- Completed At: " . ($verification->completed_at ? (is_string($verification->completed_at) ? $verification->completed_at : $verification->completed_at->format('Y-m-d H:i:s')) : 'Not set') . "\n";
            echo "- Verified At: " . ($verification->verified_at ? (is_string($verification->verified_at) ? $verification->verified_at : $verification->verified_at->format('Y-m-d H:i:s')) : 'Not set') . "\n";
            
            echo "\n📊 Status Methods:\n";
            try {
                echo "- isCompleted(): " . ($verification->isCompleted() ? 'true' : 'false') . "\n";
                echo "- isVerified(): " . ($verification->isVerified() ? 'true' : 'false') . "\n";
                echo "- isFailed(): " . ($verification->isFailed() ? 'true' : 'false') . "\n";
                echo "- isPending(): " . ($verification->isPending() ? 'true' : 'false') . "\n";
            } catch (Exception $e) {
                echo "- Status methods error: " . $e->getMessage() . "\n";
            }
            
            if ($verification->raw_data) {
                echo "\n📄 Raw Data Available: " . count($verification->raw_data) . " fields\n";
                echo "Raw Data Keys: " . implode(', ', array_keys($verification->raw_data)) . "\n";
            } else {
                echo "\n📄 Raw Data: Not available\n";
            }
            
            if ($verification->verification_data) {
                echo "🔍 Verification Data Available: " . count($verification->verification_data) . " fields\n";
                echo "Verification Data Keys: " . implode(', ', array_keys($verification->verification_data)) . "\n";
            } else {
                echo "🔍 Verification Data: Not available\n";
            }
            
            echo "\n" . str_repeat("-", 50) . "\n\n";
        }
        
        // Show latest verification details
        $latest = $verifications->first();
        echo "🎯 Latest Verification Details:\n";
        
        if ($latest->raw_data) {
            echo "\n📋 Complete Raw Data:\n";
            echo json_encode($latest->raw_data, JSON_PRETTY_PRINT) . "\n";
        }
        
        if ($latest->verification_data) {
            echo "\n🔍 Complete Verification Data:\n";
            echo json_encode($latest->verification_data, JSON_PRETTY_PRINT) . "\n";
        }
        
    } else {
        echo "❌ No KYC verifications found for this user.\n";
        echo "This means the KycVerificationService hasn't processed any webhook data yet.\n";
    }
    
    // Also show the old KYC data from users table for comparison
    echo "\n📊 Legacy KYC Data (from users table):\n";
    echo "- KYC Status: " . ($user->kyc_status ?? 'null') . "\n";
    echo "- KYC Session ID: " . ($user->kyc_session_id ?? 'null') . "\n";
    echo "- KYC Verified At: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
    
    if ($user->kyc_data) {
        echo "\n📄 Legacy KYC Data:\n";
        echo json_encode($user->kyc_data, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";