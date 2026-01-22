<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Azure ML Clustering Service...\n";
echo "========================================\n\n";

$service = app(\App\Services\AzureMLClusteringService::class);

// Test health check
echo "1. Health Check:\n";
$health = $service->healthCheck();
print_r($health);
echo "\n";

echo "2. Configuration Status:\n";
echo "   Endpoint URL: " . (config('azure-ml.endpoint_url') ?: '(empty - using local clustering)') . "\n";
echo "   Fallback Enabled: " . (config('azure-ml.fallback.enabled') ? 'Yes' : 'No') . "\n";
echo "   Cache Enabled: " . (config('azure-ml.cache.enabled') ? 'Yes' : 'No') . "\n";
echo "\n";

echo "3. System Status:\n";
if (empty(config('azure-ml.endpoint_url'))) {
    echo "   ✓ Azure ML is DISABLED\n";
    echo "   ✓ Using FREE local clustering\n";
    echo "   ✓ Monthly cost: $0\n";
} else {
    echo "   ✓ Azure ML is ENABLED\n";
    echo "   ✓ Using Azure ML clustering\n";
    echo "   ✓ Monthly cost: ~$102\n";
}

echo "\n========================================\n";
echo "Test complete!\n";
