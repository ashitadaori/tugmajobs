<?php

// Simple test to check KYC functionality
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the user
$user = \App\Models\User::find(1);

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "🧪 KYC Simple Test\n";
echo "==================\n";
echo "User: {$user->name} ({$user->email})\n";
echo "Current KYC Status: {$user->kyc_status}\n";
echo "Can Start Verification: " . ($user->canStartKycVerification() ? 'Yes' : 'No') . "\n";
echo "Session ID: " . ($user->kyc_session_id ?? 'None') . "\n";
echo "\n";

if (!$user->canStartKycVerification()) {
    echo "❌ User cannot start verification. Current status: {$user->kyc_status}\n";
    echo "💡 Try running: php artisan kyc:reset {$user->id}\n";
    exit(1);
}

echo "✅ User can start verification!\n";
echo "\n";

// Test the Didit service directly
echo "🔧 Testing Didit Service...\n";

try {
    $diditService = app(\App\Contracts\KycServiceInterface::class);
    
    $sessionData = [
        'vendor_data' => 'user-' . $user->id,
        'metadata' => [
            'user_id' => $user->id,
            'user_type' => $user->role,
            'name' => $user->name,
            'email' => $user->email,
        ],
        'contact_details' => [
            'email' => $user->email,
            'email_lang' => 'en',
        ]
    ];
    
    echo "📡 Creating Didit session...\n";
    $response = $diditService->createSession($sessionData);
    
    echo "✅ Session created successfully!\n";
    echo "Session ID: " . ($response['session_id'] ?? 'Not provided') . "\n";
    echo "URL: " . ($response['url'] ?? 'Not provided') . "\n";
    
    if (isset($response['url'])) {
        echo "\n";
        echo "🎉 SUCCESS! KYC verification URL generated:\n";
        echo $response['url'] . "\n";
        echo "\n";
        echo "💡 You can now:\n";
        echo "1. Open this URL in your browser to test verification\n";
        echo "2. Or use the modal in your application\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n";
    echo "🔍 Possible issues:\n";
    echo "1. Check your .env file for correct Didit configuration\n";
    echo "2. Verify internet connection\n";
    echo "3. Check if Didit API is accessible\n";
    echo "4. Verify API keys are correct\n";
}

echo "\n";
echo "🔧 Current Didit Configuration:\n";
echo "Base URL: " . config('services.didit.base_url') . "\n";
echo "API Key: " . (config('services.didit.api_key') ? 'Set (' . strlen(config('services.didit.api_key')) . ' chars)' : 'Not set') . "\n";
echo "Workflow ID: " . (config('services.didit.workflow_id') ?? 'Not set') . "\n";
echo "Callback URL: " . (config('services.didit.callback_url') ?? 'Not set') . "\n";
echo "Redirect URL: " . (config('services.didit.redirect_url') ?? 'Not set') . "\n";