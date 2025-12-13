<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Current Webhook Configuration ===\n\n";

$webhookSecret = env('DIDIT_WEBHOOK_SECRET');
echo "Webhook Secret Length: " . strlen($webhookSecret) . "\n";
echo "Webhook Secret (first 10 chars): " . substr($webhookSecret, 0, 10) . "...\n";
echo "Webhook Secret (last 10 chars): ..." . substr($webhookSecret, -10) . "\n\n";

echo "For Didit configuration, use this EXACT secret:\n";
echo $webhookSecret . "\n\n";

echo "Copy the secret above and paste it into your Didit webhook configuration.\n";
echo "Make sure there are no extra spaces or characters.\n";
