<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════════════════════════\n";
echo "       GOOGLE OAUTH URL GENERATOR\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Get configuration
$clientId = config('services.google.client_id');
$redirectUri = config('services.google.redirect');
$appUrl = config('app.url');

echo "Configuration Values:\n";
echo "  APP_URL: $appUrl\n";
echo "  Client ID: $clientId\n";
echo "  Redirect URI: $redirectUri\n\n";

// Build the OAuth URL
$params = [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
];

$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

echo "OAuth URL that will be sent to Google:\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "$googleAuthUrl\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Redirect URI (extracted from URL):\n";
echo "  " . urldecode($params['redirect_uri']) . "\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "COPY THIS EXACT URL TO GOOGLE CLOUD CONSOLE:\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "$redirectUri\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Steps to fix in Google Cloud Console:\n";
echo "1. Go to: https://console.cloud.google.com/apis/credentials\n";
echo "2. Click on Client ID: $clientId\n";
echo "3. In 'Authorized redirect URIs', add EXACTLY:\n";
echo "   $redirectUri\n";
echo "4. Click SAVE\n";
echo "5. Wait 30-60 seconds for changes to propagate\n";
echo "6. Clear your browser cache/cookies\n";
echo "7. Try again in incognito/private mode\n\n";

// Check if it's already correct
echo "Double-check these match:\n";
echo "  From .env: " . env('GOOGLE_REDIRECT_URI') . "\n";
echo "  From config: " . config('services.google.redirect') . "\n";
echo "  Expected: {$appUrl}/auth/google/callback\n\n";

if (env('GOOGLE_REDIRECT_URI') === config('services.google.redirect')) {
    echo "✅ .env and config match!\n";
} else {
    echo "❌ WARNING: .env and config don't match! Run: php artisan config:clear\n";
}

if (config('services.google.redirect') === "{$appUrl}/auth/google/callback") {
    echo "✅ Redirect URI matches APP_URL!\n";
} else {
    echo "❌ WARNING: Redirect URI doesn't match APP_URL!\n";
}
