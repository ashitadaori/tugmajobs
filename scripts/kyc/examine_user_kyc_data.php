<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;

$userEmail = 'khenrick.herana@gmail.com';

echo "=== Examining KYC Data for {$userEmail} ===\n\n";

try {
    $user = User::where('email', $userEmail)->first();
    
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "User Info:\n";
    echo "==========\n";
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "KYC Status: {$user->kyc_status}\n";
    echo "KYC Session ID: " . ($user->kyc_session_id ?? 'null') . "\n";
    echo "KYC Verified At: " . ($user->kyc_verified_at ?? 'null') . "\n";
    
    echo "\n\nKYC Verification Records:\n";
    echo "=========================\n";
    $verifications = KycVerification::where('user_id', $user->id)->get();
    
    if ($verifications->count() > 0) {
        foreach ($verifications as $i => $v) {
            echo "Record " . ($i + 1) . ":\n";
            echo "  ID: {$v->id}\n";
            echo "  Session ID: {$v->session_id}\n";
            echo "  Status: {$v->status}\n";
            echo "  Created: {$v->created_at}\n";
            echo "  Raw Data: " . (isset($v->raw_data) && $v->raw_data ? 'Present (' . strlen(json_encode($v->raw_data)) . ' chars)' : 'null') . "\n";
            
            if (isset($v->raw_data) && is_array($v->raw_data)) {
                echo "  Raw Data Keys: " . implode(', ', array_keys($v->raw_data)) . "\n";
            }
            echo "\n";
        }
    } else {
        echo "No records found\n";
    }
    
    echo "\nKYC Data Records:\n";
    echo "=================\n";
    $kycDataRecords = KycData::where('user_id', $user->id)->get();
    
    if ($kycDataRecords->count() > 0) {
        foreach ($kycDataRecords as $i => $d) {
            echo "Record " . ($i + 1) . ":\n";
            echo "  ID: {$d->id}\n";
            echo "  Session ID: {$d->session_id}\n";
            echo "  Status: {$d->status}\n";
            echo "  Verification Method: {$d->verification_method}\n";
            echo "  Created: {$d->created_at}\n";
            echo "  Verified At: " . ($d->verified_at ?? 'null') . "\n";
            
            // Personal Information
            echo "  Name: " . ($d->full_name ?? $d->first_name . ' ' . $d->last_name) . "\n";
            echo "  Date of Birth: " . ($d->date_of_birth ?? 'null') . "\n";
            echo "  Document Type: " . ($d->document_type ?? 'null') . "\n";
            echo "  Document Number: " . ($d->document_number ?? 'null') . "\n";
            
            // Image URLs
            echo "  Front Image: " . ($d->front_image_url ? 'Present' : 'null') . "\n";
            echo "  Back Image: " . ($d->back_image_url ? 'Present' : 'null') . "\n";
            echo "  Portrait Image: " . ($d->portrait_image_url ? 'Present' : 'null') . "\n";
            
            // Raw payload
            echo "  Raw Payload: " . (isset($d->raw_payload) && $d->raw_payload ? 'Present (' . strlen(json_encode($d->raw_payload)) . ' chars)' : 'null') . "\n";
            
            if (isset($d->raw_payload) && is_array($d->raw_payload)) {
                echo "  Raw Payload Keys: " . implode(', ', array_keys($d->raw_payload)) . "\n";
                
                // Check for decision structure
                if (isset($d->raw_payload['decision'])) {
                    $decision = $d->raw_payload['decision'];
                    echo "  Decision Keys: " . implode(', ', array_keys($decision)) . "\n";
                    
                    if (isset($decision['id_verification'])) {
                        $idVerif = $decision['id_verification'];
                        echo "  ID Verification Keys: " . implode(', ', array_keys($idVerif)) . "\n";
                        
                        // Check for images in the raw payload
                        if (isset($idVerif['front_image'])) {
                            echo "  ✅ Front image URL found in raw payload: " . substr($idVerif['front_image'], 0, 60) . "...\n";
                        }
                        if (isset($idVerif['back_image'])) {
                            echo "  ✅ Back image URL found in raw payload: " . substr($idVerif['back_image'], 0, 60) . "...\n";
                        }
                        if (isset($idVerif['portrait_image'])) {
                            echo "  ✅ Portrait image URL found in raw payload: " . substr($idVerif['portrait_image'], 0, 60) . "...\n";
                        }
                    }
                }
            }
            echo "\n";
        }
    } else {
        echo "No records found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== Examination Complete ===\n";
