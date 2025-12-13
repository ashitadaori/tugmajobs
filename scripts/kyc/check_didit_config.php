<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DiDit Configuration Check ===\n\n";

$configs = [
    'services.didit.auth_url',
    'services.didit.base_url', 
    'services.didit.api_key',
    'services.didit.client_id',
    'services.didit.client_secret',
    'services.didit.workflow_id',
    'services.didit.callback_url',
    'services.didit.redirect_url',
    'services.didit.webhook_secret'
];

foreach ($configs as $config) {
    $value = config($config);
    $displayValue = $value;
    
    // Mask sensitive values
    if (strpos($config, 'secret') !== false || strpos($config, 'key') !== false) {
        if ($value) {
            $displayValue = substr($value, 0, 8) . '...' . substr($value, -4);
        }
    }
    
    echo "- {$config}: " . ($displayValue ?? 'null') . "\n";
}

echo "\n=== Test API Connection ===\n";

try {
    $baseUrl = config('services.didit.base_url');
    $apiKey = config('services.didit.api_key');
    
    if (!$baseUrl) {
        echo "❌ Base URL not configured\n";
        exit(1);
    }
    
    if (!$apiKey) {
        echo "❌ API Key not configured\n";
        exit(1);
    }
    
    echo "Testing connection to: {$baseUrl}\n";
    
    // Test basic API connectivity
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'X-Api-Key' => $apiKey,
    ])->get($baseUrl . '/v2/health');
    
    echo "Health check response: " . $response->status() . "\n";
    
    if ($response->successful()) {
        echo "✅ API connection successful\n";
        echo "Response: " . $response->body() . "\n";
    } else {
        echo "❌ API connection failed\n";
        echo "Response: " . $response->body() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
