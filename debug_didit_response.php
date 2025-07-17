<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debug Didit API Response...\n\n";

$apiKey = config('services.didit.api_key');
$baseUrl = config('services.didit.base_url');
$workflowId = config('services.didit.workflow_id');
$callbackUrl = config('services.didit.callback_url');

echo "Configuration:\n";
echo "API Key: " . substr($apiKey, 0, 10) . "...\n";
echo "Base URL: {$baseUrl}\n";
echo "Workflow ID: {$workflowId}\n";
echo "Callback URL: {$callbackUrl}\n\n";

$sessionPayload = [
    'workflow_id' => $workflowId,
    'callback' => $callbackUrl,
    'vendor_data' => 'debug-test-' . time(),
    'metadata' => [
        'test' => true,
        'debug' => true
    ],
    'contact_details' => [
        'email' => 'test@example.com',
        'email_lang' => 'en'
    ]
];

echo "Request Payload:\n";
print_r($sessionPayload);
echo "\n";

try {
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Content-Type' => 'application/json',
        'X-Api-Key' => $apiKey,
    ])->post($baseUrl . '/v2/session', $sessionPayload);
    
    echo "Response Status: " . $response->status() . "\n";
echo "Is Successful: " . ($response->successful() ? 'Yes' : 'No') . "\n";
echo "Is Client Error: " . ($response->clientError() ? 'Yes' : 'No') . "\n";
echo "Is Server Error: " . ($response->serverError() ? 'Yes' : 'No') . "\n";
    echo "Response Headers:\n";
    foreach ($response->headers() as $key => $value) {
        echo "  {$key}: " . (is_array($value) ? implode(', ', $value) : $value) . "\n";
    }
    echo "\n";
    
    echo "Raw Response Body:\n";
    echo $response->body() . "\n\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "Parsed JSON Response:\n";
        print_r($data);
        
        if (isset($data['url'])) {
            echo "\n🔗 Verification URL: " . $data['url'] . "\n";
            
            // Check if this URL looks like a Didit verification URL or our redirect URL
            if (strpos($data['url'], 'didit.me') !== false) {
                echo "✅ This looks like a proper Didit verification URL\n";
            } else {
                echo "❌ This looks like our redirect URL - something is wrong\n";
            }
        }
    } else {
        echo "❌ Request failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}