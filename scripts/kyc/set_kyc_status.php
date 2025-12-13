<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== KYC Status Setter ===\n\n";

// Get command line arguments
$userId = $argv[1] ?? null;
$status = $argv[2] ?? null;

$validStatuses = ['pending', 'in_progress', 'verified', 'failed', 'expired'];

if (!$userId || !$status) {
    echo "Usage: php set_kyc_status.php [user_id] [status]\n\n";
    echo "Valid statuses: " . implode(', ', $validStatuses) . "\n\n";
    
    echo "Current users:\n";
    $users = User::select('id', 'name', 'email', 'role', 'kyc_status')->get();
    foreach ($users as $user) {
        echo "  ID: {$user->id} | {$user->name} ({$user->email}) | Role: {$user->role} | KYC: " . ($user->kyc_status ?? 'null') . "\n";
    }
    exit;
}

if (!in_array($status, $validStatuses)) {
    echo "❌ Invalid status. Valid statuses: " . implode(', ', $validStatuses) . "\n";
    exit(1);
}

try {
    $user = User::find($userId);
    
    if (!$user) {
        echo "❌ User with ID {$userId} not found\n";
        exit(1);
    }
    
    echo "Setting KYC status for user: {$user->name} ({$user->email})\n";
    echo "Current status: " . ($user->kyc_status ?? 'null') . "\n";
    echo "New status: {$status}\n";
    
    $updateData = ['kyc_status' => $status];
    
    // Set additional fields based on status
    switch ($status) {
        case 'pending':
            $updateData['kyc_session_id'] = null;
            $updateData['kyc_completed_at'] = null;
            $updateData['kyc_verified_at'] = null;
            $updateData['kyc_data'] = null;
            break;
            
        case 'in_progress':
            $updateData['kyc_session_id'] = 'test-session-' . time();
            $updateData['kyc_completed_at'] = null;
            $updateData['kyc_verified_at'] = null;
            break;
            
        case 'verified':
            $updateData['kyc_session_id'] = 'test-session-' . time();
            $updateData['kyc_completed_at'] = now();
            $updateData['kyc_verified_at'] = now();
            $updateData['kyc_data'] = [
                'session_id' => 'test-session-' . time(),
                'status' => 'completed',
                'completed_at' => now()->toIso8601String(),
                'test' => true
            ];
            break;
            
        case 'failed':
        case 'expired':
            $updateData['kyc_session_id'] = 'test-session-' . time();
            $updateData['kyc_completed_at'] = now();
            $updateData['kyc_verified_at'] = null;
            $updateData['kyc_data'] = [
                'session_id' => 'test-session-' . time(),
                'status' => $status,
                'completed_at' => now()->toIso8601String(),
                'test' => true
            ];
            break;
    }
    
    $user->update($updateData);
    
    echo "✅ KYC status updated to '{$status}' for {$user->name}\n";
    
    // Show what the user will see
    echo "\nUser will see:\n";
    echo "- Status: " . $user->fresh()->kyc_status_text . "\n";
    echo "- Badge: " . strip_tags($user->fresh()->kyc_status_badge) . "\n";
    echo "- Can start verification: " . ($user->fresh()->canStartKycVerification() ? 'Yes' : 'No') . "\n";
    echo "- Needs verification: " . ($user->fresh()->needsKycVerification() ? 'Yes' : 'No') . "\n";
    echo "- Is verified: " . ($user->fresh()->isKycVerified() ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Status Set Complete ===\n";