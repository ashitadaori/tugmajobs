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

echo "═══════════════════════════════════════════════════════════\n";
echo "          UPDATING ALL CALLBACK URLs\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Current APP_URL: $appUrl\n\n";

$updates = [];

// 1. Update GOOGLE_REDIRECT_URI
$correctGoogleRedirectUri = $appUrl . '/auth/google/callback';
if (preg_match('/GOOGLE_REDIRECT_URI=(.+)/', $envContent, $matches)) {
    $oldValue = trim($matches[1]);
    if ($oldValue !== $correctGoogleRedirectUri) {
        $envContent = preg_replace(
            '/GOOGLE_REDIRECT_URI=.+/',
            'GOOGLE_REDIRECT_URI=' . $correctGoogleRedirectUri,
            $envContent
        );
        $updates[] = ['GOOGLE_REDIRECT_URI', $oldValue, $correctGoogleRedirectUri];
    }
} else {
    $envContent .= "\nGOOGLE_REDIRECT_URI=" . $correctGoogleRedirectUri . "\n";
    $updates[] = ['GOOGLE_REDIRECT_URI', '(not set)', $correctGoogleRedirectUri];
}

// 2. Update DIDIT_CALLBACK_URL
$correctDiditCallback = $appUrl . '/api/kyc/webhook';
if (preg_match('/DIDIT_CALLBACK_URL=(.+)/', $envContent, $matches)) {
    $oldValue = trim($matches[1]);
    if ($oldValue !== $correctDiditCallback) {
        $envContent = preg_replace(
            '/DIDIT_CALLBACK_URL=.+/',
            'DIDIT_CALLBACK_URL=' . $correctDiditCallback,
            $envContent
        );
        $updates[] = ['DIDIT_CALLBACK_URL', $oldValue, $correctDiditCallback];
    }
}

// 3. Update DIDIT_REDIRECT_URL
$correctDiditRedirect = $appUrl . '/kyc/success';
if (preg_match('/DIDIT_REDIRECT_URL=(.+)/', $envContent, $matches)) {
    $oldValue = trim($matches[1]);
    if ($oldValue !== $correctDiditRedirect) {
        $envContent = preg_replace(
            '/DIDIT_REDIRECT_URL=.+/',
            'DIDIT_REDIRECT_URL=' . $correctDiditRedirect,
            $envContent
        );
        $updates[] = ['DIDIT_REDIRECT_URL', $oldValue, $correctDiditRedirect];
    }
}

if (empty($updates)) {
    echo "✅ All callback URLs are already up to date!\n";
    exit(0);
}

// Write back
file_put_contents($envFile, $envContent);

echo "UPDATES MADE:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

foreach ($updates as $update) {
    list($key, $oldValue, $newValue) = $update;
    echo "✅ $key\n";
    echo "   Old: $oldValue\n";
    echo "   New: $newValue\n\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "✅ .env file updated successfully!\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "⚠️  IMPORTANT NEXT STEPS:\n\n";

echo "1. Clear Laravel config cache:\n";
echo "   php artisan config:clear\n\n";

echo "2. Update Google Cloud Console:\n";
echo "   • Go to: https://console.cloud.google.com/apis/credentials\n";
echo "   • Edit your OAuth 2.0 Client ID\n";
echo "   • Add this to 'Authorized redirect URIs':\n";
echo "     $correctGoogleRedirectUri\n\n";

echo "3. Update Didit Dashboard (if needed):\n";
echo "   • Go to: https://verification.didit.me\n";
echo "   • Update webhook URL to:\n";
echo "     $correctDiditCallback\n\n";

echo "═══════════════════════════════════════════════════════════\n";
