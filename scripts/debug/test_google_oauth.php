<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Google OAuth Configuration Test ===\n\n";

// Check environment variables
$appUrl = env('APP_URL');
$googleClientId = env('GOOGLE_CLIENT_ID');
$googleClientSecret = env('GOOGLE_CLIENT_SECRET');
$googleRedirectUri = env('GOOGLE_REDIRECT_URI');

echo "Environment Variables:\n";
echo "  APP_URL: $appUrl\n";
echo "  GOOGLE_CLIENT_ID: $googleClientId\n";
echo "  GOOGLE_CLIENT_SECRET: " . (empty($googleClientSecret) ? 'NOT SET' : 'SET (' . strlen($googleClientSecret) . ' chars)') . "\n";
echo "  GOOGLE_REDIRECT_URI: $googleRedirectUri\n\n";

// Check config values
$configClientId = config('services.google.client_id');
$configClientSecret = config('services.google.client_secret');
$configRedirect = config('services.google.redirect');

echo "Config Values:\n";
echo "  services.google.client_id: $configClientId\n";
echo "  services.google.client_secret: " . (empty($configClientSecret) ? 'NOT SET' : 'SET (' . strlen($configClientSecret) . ' chars)') . "\n";
echo "  services.google.redirect: $configRedirect\n\n";

// Check if values match
$issues = [];

if ($appUrl !== parse_url($googleRedirectUri, PHP_URL_SCHEME) . '://' . parse_url($googleRedirectUri, PHP_URL_HOST)) {
    $appHost = parse_url($appUrl, PHP_URL_HOST);
    $redirectHost = parse_url($googleRedirectUri, PHP_URL_HOST);

    if ($appHost !== $redirectHost) {
        $issues[] = "⚠️  WARNING: APP_URL host ($appHost) doesn't match GOOGLE_REDIRECT_URI host ($redirectHost)";
    }
}

if (empty($googleClientId)) {
    $issues[] = "❌ ERROR: GOOGLE_CLIENT_ID is not set";
}

if (empty($googleClientSecret)) {
    $issues[] = "❌ ERROR: GOOGLE_CLIENT_SECRET is not set";
}

if (empty($googleRedirectUri)) {
    $issues[] = "❌ ERROR: GOOGLE_REDIRECT_URI is not set";
}

// Check database for social auth fields
try {
    $hasGoogleId = Schema::hasColumn('users', 'google_id');
    $hasGoogleToken = Schema::hasColumn('users', 'google_token');
    $hasProfileImage = Schema::hasColumn('users', 'profile_image');

    echo "Database Schema:\n";
    echo "  users.google_id: " . ($hasGoogleId ? '✅ EXISTS' : '❌ MISSING') . "\n";
    echo "  users.google_token: " . ($hasGoogleToken ? '✅ EXISTS' : '❌ MISSING') . "\n";
    echo "  users.profile_image: " . ($hasProfileImage ? '✅ EXISTS' : '❌ MISSING') . "\n\n";

    if (!$hasGoogleId || !$hasGoogleToken) {
        $issues[] = "❌ ERROR: Social auth fields missing from users table. Run migration: php artisan migrate";
    }
} catch (\Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n\n";
    $issues[] = "❌ ERROR: Cannot check database schema";
}

// Check if Socialite is installed
try {
    $socialiteInstalled = class_exists('Laravel\Socialite\Facades\Socialite');
    echo "Laravel Socialite:\n";
    echo "  Installed: " . ($socialiteInstalled ? '✅ YES' : '❌ NO') . "\n\n";

    if (!$socialiteInstalled) {
        $issues[] = "❌ ERROR: Laravel Socialite not installed. Run: composer require laravel/socialite";
    }
} catch (\Exception $e) {
    $issues[] = "❌ ERROR: Cannot check Socialite installation";
}

// Check routes
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $hasSocialRedirect = false;
    $hasSocialCallback = false;

    foreach ($routes as $route) {
        if ($route->getName() === 'social.redirect') {
            $hasSocialRedirect = true;
        }
        if ($route->getName() === 'social.callback') {
            $hasSocialCallback = true;
        }
    }

    echo "Routes:\n";
    echo "  social.redirect: " . ($hasSocialRedirect ? '✅ REGISTERED' : '❌ MISSING') . "\n";
    echo "  social.callback: " . ($hasSocialCallback ? '✅ REGISTERED' : '❌ MISSING') . "\n\n";

    if (!$hasSocialRedirect || !$hasSocialCallback) {
        $issues[] = "❌ ERROR: Social auth routes not registered in routes/web.php";
    }
} catch (\Exception $e) {
    echo "Route Error: " . $e->getMessage() . "\n\n";
    $issues[] = "❌ ERROR: Cannot check routes";
}

// Expected redirect URL
$expectedRedirectUrl = $appUrl . '/auth/google/callback';
echo "Expected Google Redirect URL:\n";
echo "  $expectedRedirectUrl\n\n";

if ($expectedRedirectUrl !== $googleRedirectUri) {
    $issues[] = "⚠️  WARNING: GOOGLE_REDIRECT_URI should be updated to: $expectedRedirectUrl";
}

// Google Console instructions
echo "Google Cloud Console Configuration:\n";
echo "  1. Go to: https://console.cloud.google.com/apis/credentials\n";
echo "  2. Select your OAuth 2.0 Client ID\n";
echo "  3. Add the following to 'Authorized redirect URIs':\n";
echo "     $expectedRedirectUrl\n\n";

// Summary
echo "═══════════════════════════════════════\n";
if (empty($issues)) {
    echo "✅ ALL CHECKS PASSED!\n";
    echo "Google OAuth should be working correctly.\n\n";
    echo "Test URL: $appUrl/auth/google?role=jobseeker\n";
} else {
    echo "❌ ISSUES FOUND:\n\n";
    foreach ($issues as $issue) {
        echo "  $issue\n";
    }
    echo "\n";
}
echo "═══════════════════════════════════════\n";
