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
use Illuminate\Support\Facades\Storage;

echo "=== KYC Reset Tool ===\n\n";

// Get command line arguments
$userId = $argv[1] ?? null;
$resetAll = isset($argv[1]) && $argv[1] === 'all';

if (!$userId && !$resetAll) {
    echo "Usage:\n";
    echo "  php reset_kyc.php [user_id]     - Reset specific user\n";
    echo "  php reset_kyc.php all           - Reset all users\n";
    echo "  php reset_kyc.php list          - List all users\n\n";
    
    echo "Current users:\n";
    $users = User::select('id', 'name', 'email', 'role', 'kyc_status')->get();
    foreach ($users as $user) {
        echo "  ID: {$user->id} | {$user->name} ({$user->email}) | Role: {$user->role} | KYC: " . ($user->kyc_status ?? 'null') . "\n";
    }
    exit;
}

if ($argv[1] === 'list') {
    echo "All users:\n";
    $users = User::select('id', 'name', 'email', 'role', 'kyc_status', 'kyc_verified_at')->get();
    
    // Count KYC records
    $totalKycVerifications = KycVerification::count();
    $totalKycData = KycData::count();
    $totalEmployerDocuments = EmployerDocument::count();
    
    echo "\n📊 KYC Database Summary:\n";
    echo "  - KYC Verification records: {$totalKycVerifications}\n";
    echo "  - KYC Data records: {$totalKycData}\n";
    echo "  - Employer Document records: {$totalEmployerDocuments}\n\n";
    
    foreach ($users as $user) {
        $verifiedDate = $user->kyc_verified_at ? $user->kyc_verified_at->format('Y-m-d H:i') : 'Never';
        $userKycVerifications = KycVerification::where('user_id', $user->id)->count();
        $userKycData = KycData::where('user_id', $user->id)->count();
        $userDocuments = EmployerDocument::where('user_id', $user->id)->count();
        
        echo "  ID: {$user->id} | {$user->name} ({$user->email})\n";
        echo "    Role: {$user->role} | KYC Status: " . ($user->kyc_status ?? 'null') . " | Verified: {$verifiedDate}\n";
        echo "    KYC Records: {$userKycVerifications} verifications, {$userKycData} data entries, {$userDocuments} documents\n\n";
    }
    exit;
}

try {
    if ($resetAll) {
        echo "Resetting KYC status for ALL users...\n";
        
        // Delete all KYC verification records
        $deletedVerifications = KycVerification::query()->count();
        if ($deletedVerifications > 0) {
            KycVerification::query()->delete();
            echo "🗑️  Deleted {$deletedVerifications} KYC verification records\n";
        }
        
        // Delete all KYC data records
        $deletedKycData = KycData::query()->count();
        if ($deletedKycData > 0) {
            KycData::query()->delete();
            echo "🗑️  Deleted {$deletedKycData} KYC data records\n";
        }
        
        // Delete all employer documents and their files
        $employerDocuments = EmployerDocument::all();
        $deletedDocuments = $employerDocuments->count();
        if ($deletedDocuments > 0) {
            foreach ($employerDocuments as $document) {
                // Delete the physical file if it exists
                if ($document->file_path && Storage::exists($document->file_path)) {
                    Storage::delete($document->file_path);
                }
            }
            EmployerDocument::query()->delete();
            echo "🗑️  Deleted {$deletedDocuments} employer documents and their files\n";
        }
        
        // Reset user KYC fields
        $updated = User::query()->update([
            'kyc_status' => 'pending',
            'kyc_session_id' => null,
            'kyc_completed_at' => null,
            'kyc_verified_at' => null,
            'kyc_data' => null
        ]);
        
        echo "✅ Reset KYC status for {$updated} users\n";
        
    } else {
        $user = User::find($userId);
        
        if (!$user) {
            echo "❌ User with ID {$userId} not found\n";
            exit(1);
        }
        
        echo "Resetting KYC status for user: {$user->name} ({$user->email})\n";
        echo "Current status: " . ($user->kyc_status ?? 'null') . "\n";
        
        // Delete user's KYC verification records
        $deletedVerifications = $user->kycVerifications()->count();
        if ($deletedVerifications > 0) {
            $user->kycVerifications()->delete();
            echo "🗑️  Deleted {$deletedVerifications} KYC verification records for user\n";
        }
        
        // Delete user's KYC data records
        $deletedKycData = $user->kycData()->count();
        if ($deletedKycData > 0) {
            $user->kycData()->delete();
            echo "🗑️  Deleted {$deletedKycData} KYC data records for user\n";
        }
        
        // Delete user's employer documents (only if user is an employer)
        if ($user->isEmployer()) {
            $userDocuments = $user->employerDocuments;
            $deletedUserDocuments = $userDocuments->count();
            if ($deletedUserDocuments > 0) {
                foreach ($userDocuments as $document) {
                    // Delete the physical file if it exists
                    if ($document->file_path && Storage::exists($document->file_path)) {
                        Storage::delete($document->file_path);
                    }
                }
                $user->employerDocuments()->delete();
                echo "🗑️  Deleted {$deletedUserDocuments} employer documents and their files for user\n";
            }
        }
        
        // Reset user KYC fields
        $user->update([
            'kyc_status' => 'pending',
            'kyc_session_id' => null,
            'kyc_completed_at' => null,
            'kyc_verified_at' => null,
            'kyc_data' => null
        ]);
        
        echo "✅ KYC status reset to 'pending' for {$user->name}\n";
    }
    
    echo "\nUsers can now start fresh KYC verification at: " . env('APP_URL') . "/kyc/start\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Reset Complete ===\n";