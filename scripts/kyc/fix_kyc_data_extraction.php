<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KycData;
use App\Models\User;

echo "=== Fixing KYC Data Extraction ===\n\n";

$user = User::where('email', 'khenrick.herana@gmail.com')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "✅ User Found: {$user->name}\n";
echo "User ID: {$user->id}\n";
echo "Session ID: {$user->kyc_session_id}\n\n";

// Get KYC data
$kycData = KycData::where('user_id', $user->id)->first();

if (!$kycData) {
    echo "❌ No KYC data found\n";
    exit(1);
}

echo "✅ KYC Data found. Processing payload...\n\n";

// Extract the payload
$payload = $kycData->raw_payload;

if (!$payload) {
    echo "❌ No raw payload found\n";
    exit(1);
}

// Extract data from the Didit payload
$decision = $payload['decision'] ?? [];
$idVerification = $decision['id_verification'] ?? [];
$faceMatch = $decision['face_match'] ?? [];
$liveness = $decision['liveness'] ?? [];
$ipAnalysis = $decision['ip_analysis'] ?? [];

echo "📋 Extracting Data from Payload:\n";

// Personal Information
if (!empty($idVerification)) {
    echo "- Extracting personal information...\n";
    $kycData->first_name = $idVerification['first_name'] ?? null;
    $kycData->last_name = $idVerification['last_name'] ?? null;
    $kycData->full_name = $idVerification['full_name'] ?? null;
    $kycData->date_of_birth = !empty($idVerification['date_of_birth']) ? 
        \Carbon\Carbon::parse($idVerification['date_of_birth']) : null;
    $kycData->gender = $idVerification['gender'] ?? null;
    $kycData->place_of_birth = $idVerification['place_of_birth'] ?? null;
    $kycData->marital_status = $idVerification['marital_status'] ?? null;
    $kycData->nationality = $idVerification['nationality'] ?? null;
    
    echo "  ✓ Name: {$kycData->first_name} {$kycData->last_name}\n";
    echo "  ✓ Date of Birth: " . ($kycData->date_of_birth ? $kycData->date_of_birth->format('Y-m-d') : 'null') . "\n";
    echo "  ✓ Gender: {$kycData->gender}\n";
    echo "  ✓ Place of Birth: {$kycData->place_of_birth}\n";
    echo "  ✓ Marital Status: {$kycData->marital_status}\n";
}

// Document Information
if (!empty($idVerification)) {
    echo "- Extracting document information...\n";
    $kycData->document_type = $idVerification['document_type'] ?? null;
    $kycData->document_number = $idVerification['document_number'] ?? null;
    $kycData->document_issue_date = !empty($idVerification['date_of_issue']) ? 
        \Carbon\Carbon::parse($idVerification['date_of_issue']) : null;
    $kycData->document_expiration_date = !empty($idVerification['expiration_date']) ? 
        \Carbon\Carbon::parse($idVerification['expiration_date']) : null;
    $kycData->issuing_state = $idVerification['issuing_state'] ?? null;
    $kycData->issuing_state_name = $idVerification['issuing_state_name'] ?? null;
    
    echo "  ✓ Document Type: {$kycData->document_type}\n";
    echo "  ✓ Document Number: {$kycData->document_number}\n";
    echo "  ✓ Issue Date: " . ($kycData->document_issue_date ? $kycData->document_issue_date->format('Y-m-d') : 'null') . "\n";
    echo "  ✓ Issuing State: {$kycData->issuing_state_name} ({$kycData->issuing_state})\n";
}

// Address Information
if (!empty($idVerification)) {
    echo "- Extracting address information...\n";
    $kycData->address = $idVerification['address'] ?? null;
    $kycData->formatted_address = $idVerification['formatted_address'] ?? null;
    
    // Extract from parsed address if available
    $parsedAddress = $idVerification['parsed_address'] ?? [];
    if (!empty($parsedAddress)) {
        $kycData->city = $parsedAddress['city'] ?? null;
        $kycData->region = $parsedAddress['region'] ?? null;
        $kycData->country = $parsedAddress['country'] ?? null;
        $kycData->postal_code = $parsedAddress['postal_code'] ?? null;
        
        // Extract coordinates
        if (!empty($parsedAddress['document_location'])) {
            $location = $parsedAddress['document_location'];
            $kycData->latitude = $location['latitude'] ?? null;
            $kycData->longitude = $location['longitude'] ?? null;
        }
    }
    
    echo "  ✓ Address: {$kycData->address}\n";
    echo "  ✓ Formatted Address: {$kycData->formatted_address}\n";
    echo "  ✓ City: {$kycData->city}\n";
    echo "  ✓ Region: {$kycData->region}\n";
    echo "  ✓ Country: {$kycData->country}\n";
    echo "  ✓ Coordinates: {$kycData->latitude}, {$kycData->longitude}\n";
}

// Verification Scores and Results
if (!empty($faceMatch)) {
    echo "- Extracting face match data...\n";
    $kycData->face_match_score = $faceMatch['score'] ?? null;
    $kycData->face_match_status = $faceMatch['status'] ?? null;
    
    echo "  ✓ Face Match Score: {$kycData->face_match_score}\n";
    echo "  ✓ Face Match Status: {$kycData->face_match_status}\n";
}

if (!empty($liveness)) {
    echo "- Extracting liveness data...\n";
    $kycData->liveness_score = $liveness['score'] ?? null;
    $kycData->liveness_status = $liveness['status'] ?? null;
    $kycData->age_estimation = $liveness['age_estimation'] ?? null;
    
    echo "  ✓ Liveness Score: {$kycData->liveness_score}\n";
    echo "  ✓ Liveness Status: {$kycData->liveness_status}\n";
    echo "  ✓ Age Estimation: {$kycData->age_estimation}\n";
}

// Verification statuses
$kycData->id_verification_status = $idVerification['status'] ?? null;
$kycData->ip_analysis_status = $ipAnalysis['status'] ?? null;

echo "- Verification statuses:\n";
echo "  ✓ ID Verification Status: {$kycData->id_verification_status}\n";
echo "  ✓ IP Analysis Status: {$kycData->ip_analysis_status}\n";

// IP and Device Information
if (!empty($ipAnalysis)) {
    echo "- Extracting IP and device information...\n";
    $kycData->ip_address = $ipAnalysis['ip_address'] ?? null;
    $kycData->ip_country = $ipAnalysis['ip_country'] ?? null;
    $kycData->ip_city = $ipAnalysis['ip_city'] ?? null;
    $kycData->is_vpn_or_tor = $ipAnalysis['is_vpn_or_tor'] ?? false;
    $kycData->device_brand = $ipAnalysis['device_brand'] ?? null;
    $kycData->device_model = $ipAnalysis['device_model'] ?? null;
    $kycData->browser_family = $ipAnalysis['browser_family'] ?? null;
    $kycData->os_family = $ipAnalysis['os_family'] ?? null;
    
    echo "  ✓ IP Address: {$kycData->ip_address}\n";
    echo "  ✓ IP Location: {$kycData->ip_city}, {$kycData->ip_country}\n";
    echo "  ✓ VPN/Tor: " . ($kycData->is_vpn_or_tor ? 'Yes' : 'No') . "\n";
    echo "  ✓ Device: {$kycData->device_brand} {$kycData->device_model}\n";
    echo "  ✓ Browser: {$kycData->browser_family}\n";
    echo "  ✓ OS: {$kycData->os_family}\n";
}

// Image URLs
if (!empty($idVerification)) {
    echo "- Extracting image URLs...\n";
    $kycData->front_image_url = $idVerification['front_image'] ?? null;
    $kycData->back_image_url = $idVerification['back_image'] ?? null;
    $kycData->portrait_image_url = $idVerification['portrait_image'] ?? null;
    
    echo "  ✓ Front Image: " . ($kycData->front_image_url ? 'Available' : 'null') . "\n";
    echo "  ✓ Back Image: " . ($kycData->back_image_url ? 'Available' : 'null') . "\n";
    echo "  ✓ Portrait Image: " . ($kycData->portrait_image_url ? 'Available' : 'null') . "\n";
}

if (!empty($liveness['video_url'])) {
    $kycData->liveness_video_url = $liveness['video_url'];
    echo "  ✓ Liveness Video: Available\n";
}

// Extract warnings
$warnings = [];
if (!empty($idVerification['warnings'])) {
    $warnings = array_merge($warnings, $idVerification['warnings']);
}
if (!empty($faceMatch['warnings'])) {
    $warnings = array_merge($warnings, $faceMatch['warnings']);
}
if (!empty($liveness['warnings'])) {
    $warnings = array_merge($warnings, $liveness['warnings']);
}
if (!empty($ipAnalysis['warnings'])) {
    $warnings = array_merge($warnings, $ipAnalysis['warnings']);
}

if (!empty($warnings)) {
    $kycData->warnings = $warnings;
    echo "- Extracted " . count($warnings) . " warnings\n";
}

// Set Didit creation timestamp
if (!empty($payload['created_at'])) {
    $kycData->didit_created_at = \Carbon\Carbon::createFromTimestamp($payload['created_at']);
    echo "- Didit Created At: " . $kycData->didit_created_at->format('Y-m-d H:i:s') . "\n";
}

// Save the updated KYC data
echo "\n💾 Saving updated KYC data...\n";

try {
    $kycData->save();
    echo "✅ KYC data updated successfully!\n\n";
    
    echo "📊 SUMMARY:\n";
    echo "- Name: {$kycData->first_name} {$kycData->last_name}\n";
    echo "- Document: {$kycData->document_type} - {$kycData->document_number}\n";
    echo "- Address: {$kycData->formatted_address}\n";
    echo "- Face Match: {$kycData->face_match_score}% ({$kycData->face_match_status})\n";
    echo "- Liveness: {$kycData->liveness_score}% ({$kycData->liveness_status})\n";
    echo "- Age: {$kycData->age_estimation} years\n";
    echo "- Device: {$kycData->device_brand} {$kycData->device_model}\n";
    echo "- Location: {$kycData->ip_city}, {$kycData->ip_country}\n";
    
} catch (Exception $e) {
    echo "❌ Error saving KYC data: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Fix Complete ===\n";
