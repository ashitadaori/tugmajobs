<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set up the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== Cross-Device KYC Test ===\n\n";

// Find the employer
$employer = User::where('role', 'employer')
    ->where('email', 'khenrick.herana@gmail.com')
    ->first();

if (!$employer) {
    echo "❌ Employer not found\n";
    exit;
}

echo "Testing cross-device KYC flow with employer: {$employer->name}\n\n";

// Step 1: Set user to in_progress (simulating desktop KYC start)
$employer->update([
    'kyc_status' => 'in_progress',
    'kyc_session_id' => 'cross-device-test-' . time(),
    'kyc_verified_at' => null
]);

echo "Step 1: Set KYC status to in_progress (desktop session)\n";
echo "- Status: {$employer->kyc_status}\n";
echo "- Session ID: {$employer->kyc_session_id}\n\n";

// Step 2: Simulate mobile redirect from Didit
$sessionId = $employer->kyc_session_id;
$status = 'completed';

echo "Step 2: Simulating mobile redirect from Didit\n";

// Create mobile request (with mobile user agent)
$mobileRequest = Request::create('/kyc/success', 'GET', [
    'session_id' => $sessionId,
    'status' => $status
]);

// Set mobile user agent
$mobileRequest->headers->set('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1');

// Set up the request
$app->instance('request', $mobileRequest);

echo "Processing mobile redirect...\n";

try {
    $response = $kernel->handle($mobileRequest);
    
    echo "✅ Mobile redirect processed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    echo "Status code: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Mobile success page displayed\n";
        $content = $response->getContent();
        if (strpos($content, 'Verification Complete') !== false) {
            echo "✅ Mobile success page contains correct content\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error processing mobile redirect: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 3: Test mobile completion notification
echo "Step 3: Testing mobile completion notification\n";

$notificationRequest = Request::create('/kyc/mobile-completion-notify', 'POST', [], [], [], [], json_encode([
    'session_id' => $sessionId,
    'user_id' => $employer->id,
    'mobile_completion' => true,
    'timestamp' => now()->toISOString()
]));

$notificationRequest->headers->set('Content-Type', 'application/json');
$notificationRequest->headers->set('X-CSRF-TOKEN', 'test-token');
$notificationRequest->headers->set('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)');

$app->instance('request', $notificationRequest);

try {
    $notificationResponse = $kernel->handle($notificationRequest);
    
    echo "✅ Mobile notification processed\n";
    echo "Status code: " . $notificationResponse->getStatusCode() . "\n";
    
    // Check if user status was updated
    $employer->refresh();
    echo "User status after notification: " . $employer->kyc_status . "\n";
    
    if ($employer->kyc_status === 'verified') {
        echo "✅ User status correctly updated to verified\n";
    } else {
        echo "❌ User status not updated\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error processing mobile notification: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 4: Test desktop status check
echo "Step 4: Testing desktop status check\n";

$statusRequest = Request::create('/kyc/check-status', 'POST', [], [], [], [], json_encode([
    'user_id' => $employer->id
]));

$statusRequest->headers->set('Content-Type', 'application/json');
$statusRequest->headers->set('X-CSRF-TOKEN', 'test-token');

$app->instance('request', $statusRequest);

try {
    $statusResponse = $kernel->handle($statusRequest);
    
    echo "✅ Desktop status check processed\n";
    echo "Status code: " . $statusResponse->getStatusCode() . "\n";
    
    if ($statusResponse->getStatusCode() === 200) {
        $statusData = json_decode($statusResponse->getContent(), true);
        echo "Status data: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
        
        if ($statusData['kyc_status'] === 'verified') {
            echo "✅ Desktop would detect verification completion\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error checking desktop status: " . $e->getMessage() . "\n";
}

echo "\n=== Cross-Device Test Complete ===\n";
echo "\nHow it works:\n";
echo "1. User starts KYC on desktop (status: in_progress)\n";
echo "2. User scans QR code with mobile device\n";
echo "3. Mobile device completes verification and gets redirected to mobile success page\n";
echo "4. Mobile page notifies server of completion\n";
echo "5. Desktop page polls server and detects completion\n";
echo "6. Desktop page redirects to dashboard\n";