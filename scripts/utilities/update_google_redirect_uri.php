<?php

$envFile = __DIR__ . '/../../.env';

if (!file_exists($envFile)) {
    echo "❌ Error: .env file not found\n";
    exit(1);
}

// Read .env file
$envContent = file_get_contents($envFile);

// Get current APP_URL
preg_match('/APP_URL=(.+)/', $envContent, $matches);
$appUrl = trim($matches[1] ?? '');

if (empty($appUrl)) {
    echo "❌ Error: APP_URL not found in .env file\n";
    exit(1);
}

// Create the correct redirect URI
$correctRedirectUri = $appUrl . '/auth/google/callback';

echo "Current APP_URL: $appUrl\n";
echo "Updating GOOGLE_REDIRECT_URI to: $correctRedirectUri\n\n";

// Update GOOGLE_REDIRECT_URI
if (preg_match('/GOOGLE_REDIRECT_URI=(.+)/', $envContent)) {
    // Update existing
    $envContent = preg_replace(
        '/GOOGLE_REDIRECT_URI=.+/',
        'GOOGLE_REDIRECT_URI=' . $correctRedirectUri,
        $envContent
    );
    echo "✅ GOOGLE_REDIRECT_URI updated\n";
} else {
    // Add new
    $envContent .= "\nGOOGLE_REDIRECT_URI=" . $correctRedirectUri . "\n";
    echo "✅ GOOGLE_REDIRECT_URI added\n";
}

// Write back
file_put_contents($envFile, $envContent);

echo "\n✅ .env file updated successfully!\n\n";
echo "⚠️  IMPORTANT: You must also update Google Cloud Console:\n";
echo "   1. Go to: https://console.cloud.google.com/apis/credentials\n";
echo "   2. Edit your OAuth 2.0 Client ID\n";
echo "   3. Add this to 'Authorized redirect URIs':\n";
echo "      $correctRedirectUri\n\n";
echo "Then run: php artisan config:clear\n";
