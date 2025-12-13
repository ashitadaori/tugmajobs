<?php

echo "═══════════════════════════════════════════════════════════\n";
echo "          UPDATE APP_URL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

if ($argc < 2) {
    echo "Usage: php update_app_url.php <new-ngrok-url>\n";
    echo "Example: php update_app_url.php https://abc123.ngrok-free.app\n\n";

    // Show current APP_URL
    $envFile = __DIR__ . '/../../.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        if (preg_match('/APP_URL=(.+)/', $envContent, $matches)) {
            $currentUrl = trim($matches[1]);
            echo "Current APP_URL: $currentUrl\n\n";
        }
    }

    exit(1);
}

$newUrl = trim($argv[1]);

// Validate URL
if (!filter_var($newUrl, FILTER_VALIDATE_URL)) {
    echo "❌ Error: Invalid URL format\n";
    echo "   Please provide a valid URL like: https://abc123.ngrok-free.app\n";
    exit(1);
}

// Ensure it's HTTPS for ngrok
if (!str_starts_with($newUrl, 'https://')) {
    echo "⚠️  Warning: URL should start with https:// for ngrok\n";
    echo "   Using: $newUrl\n\n";
}

// Remove trailing slash if present
$newUrl = rtrim($newUrl, '/');

$envFile = __DIR__ . '/../../.env';

if (!file_exists($envFile)) {
    echo "❌ Error: .env file not found\n";
    exit(1);
}

echo "New ngrok URL: $newUrl\n\n";

// Read .env file
$envContent = file_get_contents($envFile);

// Get old APP_URL
$oldUrl = '';
if (preg_match('/APP_URL=(.+)/', $envContent, $matches)) {
    $oldUrl = trim($matches[1]);
}

if ($oldUrl === $newUrl) {
    echo "✅ APP_URL is already set to: $newUrl\n";
    echo "   No changes needed.\n";
    exit(0);
}

echo "Current APP_URL: $oldUrl\n";
echo "New APP_URL: $newUrl\n\n";

// Update APP_URL
$envContent = preg_replace(
    '/APP_URL=.+/',
    'APP_URL=' . $newUrl,
    $envContent
);

// Update GOOGLE_REDIRECT_URI
$newGoogleRedirect = $newUrl . '/auth/google/callback';
$envContent = preg_replace(
    '/GOOGLE_REDIRECT_URI=.+/',
    'GOOGLE_REDIRECT_URI=' . $newGoogleRedirect,
    $envContent
);

// Update DIDIT_CALLBACK_URL
$newDiditCallback = $newUrl . '/api/kyc/webhook';
$envContent = preg_replace(
    '/DIDIT_CALLBACK_URL=.+/',
    'DIDIT_CALLBACK_URL=' . $newDiditCallback,
    $envContent
);

// Update DIDIT_REDIRECT_URL
$newDiditRedirect = $newUrl . '/kyc/success';
$envContent = preg_replace(
    '/DIDIT_REDIRECT_URL=.+/',
    'DIDIT_REDIRECT_URL=' . $newDiditRedirect,
    $envContent
);

// Write back to file
file_put_contents($envFile, $envContent);

echo "═══════════════════════════════════════════════════════════\n";
echo "✅ UPDATED SUCCESSFULLY!\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Updated:\n";
echo "  ✅ APP_URL → $newUrl\n";
echo "  ✅ GOOGLE_REDIRECT_URI → $newGoogleRedirect\n";
echo "  ✅ DIDIT_CALLBACK_URL → $newDiditCallback\n";
echo "  ✅ DIDIT_REDIRECT_URL → $newDiditRedirect\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "NEXT STEPS:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "1. Clear Laravel cache:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";

echo "2. Update Google Cloud Console:\n";
echo "   • Go to: https://console.cloud.google.com/apis/credentials\n";
echo "   • Add this redirect URI:\n";
echo "     $newGoogleRedirect\n\n";

echo "3. Clear browser cache or use incognito mode\n\n";

echo "═══════════════════════════════════════════════════════════\n";
