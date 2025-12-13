<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuration
$webhookUrl = 'http://localhost:8000/api/kyc/webhook'; // Update with your local URL
$webhookSecret = $_ENV['DIDIT_WEBHOOK_SECRET'] ?? '9eklYxwQ_S4P2AFeiPSCEkmIZYgLPzvieiRgF3yUlD8';

echo "Testing KYC Webhook with Detailed Data Structure\n";
echo "================================================\n\n";

// Sample webhook payload with detailed KYC information
$samplePayload = [
    'session_id' => 'test-session-' . time(),
    'status' => 'verified', // Change to 'failed' or 'expired' to test different scenarios
    'personal_info' => [
        'first_name' => 'Juan',
        'last_name' => 'dela Cruz',
        'full_name' => 'Juan A. dela Cruz',
        'date_of_birth' => '1990-05-15',
        'gender' => 'Male',
        'nationality' => 'Filipino',
        'place_of_birth' => 'Manila',
        'marital_status' => 'Single'
    ],
    'document_info' => [
        'type' => 'National ID',
        'number' => '1234-5678-9012',
        'issue_date' => '2020-01-15',
        'expiration_date' => '2030-01-15',
        'issuing_state' => 'PH',
        'issuing_state_name' => 'Philippines'
    ],
    'address_info' => [
        'address' => '123 Sample Street, Barangay Sample',
        'formatted_address' => '123 Sample Street, Barangay Sample, Manila, Metro Manila, Philippines',
        'city' => 'Manila',
        'region' => 'Metro Manila',
        'country' => 'Philippines',
        'postal_code' => '1000',
        'latitude' => 14.5995,
        'longitude' => 120.9842
    ],
    'verification_results' => [
        'face_match_score' => 95.67,
        'face_match_status' => 'pass',
        'liveness_score' => 98.23,
        'liveness_status' => 'pass',
        'id_verification_status' => 'verified',
        'ip_analysis_status' => 'pass',
        'age_estimation' => 33.5
    ],
    'device_info' => [
        'ip_address' => '203.177.51.123',
        'ip_country' => 'Philippines',
        'ip_city' => 'Manila',
        'is_vpn_or_tor' => false,
        'device_brand' => 'Samsung',
        'device_model' => 'Galaxy S21',
        'browser_family' => 'Chrome Mobile',
        'os_family' => 'Android'
    ],
    'images' => [
        'front_image_url' => 'https://example.com/images/front_123.jpg',
        'back_image_url' => 'https://example.com/images/back_123.jpg',
        'portrait_image_url' => 'https://example.com/images/portrait_123.jpg',
        'liveness_video_url' => 'https://example.com/videos/liveness_123.mp4'
    ],
    'warnings' => [
        'document_quality' => 'Slight blur detected on document corners'
    ],
    'created_at' => date('c'), // ISO 8601 format
    'processed_at' => date('c'),
    'vendor' => 'didit',
    'version' => '1.0'
];

// Function to send webhook
function sendWebhook($url, $payload, $secret) {
    $payloadJson = json_encode($payload);
    
    // Create HMAC signature (matching what we expect in the webhook controller)
    $signature = hash_hmac('sha256', $payloadJson, $secret);
    
    echo "Payload JSON length: " . strlen($payloadJson) . " bytes\n";
    echo "Generated signature: " . $signature . "\n";
    echo "Session ID: " . $payload['session_id'] . "\n";
    echo "Status: " . $payload['status'] . "\n\n";
    
    // Initialize cURL
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payloadJson,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Didit-Signature: ' . $signature,
            'User-Agent: Didit-Webhook/1.0'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'curl_error' => $curlError
    ];
}

// Before sending, we need to create a test user with the session ID
echo "Step 1: Creating test user with session ID...\n";

// You'll need to manually create a user in your database with this session ID for testing
// Or modify this script to use your application's user creation logic
$testSessionId = $samplePayload['session_id'];

echo "Please create a test user in your database with:\n";
echo "- Email: test@example.com\n";
echo "- kyc_session_id: {$testSessionId}\n";
echo "- kyc_status: in_progress\n\n";

echo "Press Enter when ready to send webhook...";
fgets(STDIN);

echo "\nStep 2: Sending webhook...\n";

// Send the webhook
$result = sendWebhook($webhookUrl, $samplePayload, $webhookSecret);

echo "HTTP Status Code: " . $result['http_code'] . "\n";

if ($result['curl_error']) {
    echo "cURL Error: " . $result['curl_error'] . "\n";
} else {
    echo "Response:\n";
    echo $result['response'] . "\n\n";
    
    // Try to decode and pretty print the response
    $responseData = json_decode($result['response'], true);
    if ($responseData) {
        echo "Formatted Response:\n";
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
}

echo "\nStep 3: Check your database...\n";
echo "- Check the 'users' table for updated kyc_status\n";
echo "- Check the 'kyc_data' table for detailed KYC information\n";
echo "- Check Laravel logs for processing details\n";

echo "\nTest different scenarios by changing the 'status' in the payload:\n";
echo "- 'verified' or 'approved' for successful verification\n";
echo "- 'failed' or 'rejected' for failed verification\n";
echo "- 'expired' for expired sessions\n";

echo "\nDone!\n";
