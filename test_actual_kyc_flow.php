<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Actual KYC Flow...\n\n";

// Get a test user
$user = \App\Models\User::first();

if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

echo "Testing with user: {$user->name} (ID: {$user->id})\n";
echo "Current KYC Status: {$user->kyc_status}\n\n";

// Test the DiditService directly (same as KYC controller)
try {
    // Clear any cached config first
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    
    $diditService = app(\App\Services\DiditService::class);
    
    echo "1. Testing session creation with user data...\n";
    
    $sessionData = [
        'vendor_data' => 'user-' . $user->id,
        'metadata' => [
            'user_id' => $user->id,
            'user_type' => $user->role,
            'account_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
        'contact_details' => [
            'email' => $user->email,
            'email_lang' => 'en',
        ]
    ];

    // Add phone if available
    if (!empty($user->mobile)) {
        $sessionData['contact_details']['phone'] = $user->mobile;
    }

    echo "Session data:\n";
    print_r($sessionData);
    echo "\n";

    $response = $diditService->createSession($sessionData);
    
    echo "✅ Session created successfully!\n";
    echo "Full Response:\n";
    print_r($response);
    echo "\nSession ID: " . ($response['session_id'] ?? 'N/A') . "\n";
    echo "Session URL: " . ($response['url'] ?? 'N/A') . "\n";
    echo "Status: " . ($response['status'] ?? 'N/A') . "\n";
    
    if (isset($response['url'])) {
        echo "\n🔗 User should be redirected to: " . $response['url'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Testing complete!\n";