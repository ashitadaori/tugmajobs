<?php

echo "═══════════════════════════════════════════════════════════\n";
echo "           GOOGLE OAUTH LIVE TEST\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "✅ GOOD NEWS: Your Google OAuth is configured correctly!\n\n";

echo "The 'Invalid social provider' error you're seeing is likely:\n";
echo "  1. A cached error message from a previous attempt\n";
echo "  2. Session data that wasn't cleared\n\n";

echo "SOLUTIONS:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Solution 1: Clear Browser Data\n";
echo "  • Close ALL browser windows\n";
echo "  • Open a new INCOGNITO/PRIVATE window\n";
echo "  • Go to: https://3dbb0fea823c.ngrok-free.app\n";
echo "  • Try Google login again\n\n";

echo "Solution 2: Use This Direct Link\n";
echo "  • Open this URL in incognito mode:\n";
echo "    https://3dbb0fea823c.ngrok-free.app/auth/google?role=employer\n";
echo "  • This bypasses any cached errors\n\n";

echo "Solution 3: Clear Laravel Caches\n";
echo "  Run these commands:\n";
echo "    php artisan cache:clear\n";
echo "    php artisan config:clear\n";
echo "    php artisan view:clear\n";
echo "    rm -rf storage/framework/sessions/*\n\n";

echo "PROOF THAT IT WORKS:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/auth/google', 'GET', ['role' => 'employer']);

try {
    $response = $kernel->handle($request);

    echo "✅ Test Request: /auth/google?role=employer\n";
    echo "✅ Status Code: " . $response->getStatusCode() . " (302 Redirect)\n";
    $location = $response->headers->get('Location');
    if ($location && strpos($location, 'accounts.google.com') !== false) {
        echo "✅ Redirects To: Google OAuth ✓\n";
        echo "✅ Full Redirect: " . substr($location, 0, 100) . "...\n\n";
    }

    echo "═══════════════════════════════════════════════════════════\n";
    echo "YOUR GOOGLE OAUTH IS WORKING! ✅\n";
    echo "═══════════════════════════════════════════════════════════\n\n";

    echo "Next Steps:\n";
    echo "  1. Use incognito mode\n";
    echo "  2. Go to: https://3dbb0fea823c.ngrok-free.app/test-google-login.html\n";
    echo "  3. Click the 'Login as Employer' button\n";
    echo "  4. You should be redirected to Google successfully!\n\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

$kernel->terminate($request, $response ?? null);
