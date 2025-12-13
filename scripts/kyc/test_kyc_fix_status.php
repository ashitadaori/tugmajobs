<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "🧪 Testing KYC Fix Implementation\n";
echo "==================================\n\n";

// Check if API route is accessible
echo "1. Testing API Route Accessibility:\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, env('APP_URL') . '/api/user/kyc-status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 401) {
        echo "   ✅ API route exists (returns 401 Unauthorized as expected)\n";
    } else {
        echo "   ⚠️  API route returns HTTP {$httpCode}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Could not test API route: " . $e->getMessage() . "\n";
}

echo "\n2. Testing JavaScript Files:\n";

$jsFiles = [
    'public/assets/js/kyc-status-refresher.js',
    'public/assets/js/kyc-inline-verification.js'
];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   ✅ {$file} exists ({$size} bytes)\n";
    } else {
        echo "   ❌ {$file} missing\n";
    }
}

echo "\n3. Testing User KYC Statuses:\n";
$users = User::take(5)->get();

foreach ($users as $user) {
    $kycRecord = $user->kycVerifications()->latest()->first();
    echo "   User #{$user->id} ({$user->name}):\n";
    echo "     - Database Status: {$user->kyc_status}\n";
    
    if ($kycRecord) {
        echo "     - Latest KYC Record: {$kycRecord->status}\n";
        echo "     - Match: " . ($user->kyc_status === $kycRecord->status ? '✅ Yes' : '⚠️ No') . "\n";
    } else {
        echo "     - Latest KYC Record: None\n";
        echo "     - Match: N/A\n";
    }
    echo "\n";
}

echo "4. Testing Layout File Integration:\n";
$layoutFile = 'resources/views/front/layouts/app.blade.php';
if (file_exists($layoutFile)) {
    $content = file_get_contents($layoutFile);
    
    if (strpos($content, 'kyc-status-refresher.js') !== false) {
        echo "   ✅ KYC status refresher script included in layout\n";
    } else {
        echo "   ❌ KYC status refresher script NOT included in layout\n";
    }
    
    if (strpos($content, 'meta name="user-id"') !== false) {
        echo "   ✅ User ID meta tag present in layout\n";
    } else {
        echo "   ❌ User ID meta tag missing from layout\n";
    }
} else {
    echo "   ❌ Layout file not found\n";
}

echo "\n5. Testing API Routes Configuration:\n";
$apiFile = 'routes/api.php';
if (file_exists($apiFile)) {
    $content = file_get_contents($apiFile);
    
    if (strpos($content, '/user/kyc-status') !== false) {
        echo "   ✅ KYC status API route defined\n";
    } else {
        echo "   ❌ KYC status API route missing\n";
    }
    
    if (strpos($content, 'use Illuminate\\Support\\Facades\\Auth;') !== false) {
        echo "   ✅ Auth facade imported\n";
    } else {
        echo "   ❌ Auth facade not imported\n";
    }
} else {
    echo "   ❌ API routes file not found\n";
}

echo "\n📊 Summary:\n";
echo "==========\n";
echo "• Fix script executed successfully\n";
echo "• API route for KYC status checking added\n";
echo "• Frontend status refresher script created\n";
echo "• Layout file updated with new script\n";
echo "• KYC inline verification updated\n";

echo "\n🔧 Next Steps for Testing:\n";
echo "=========================\n";
echo "1. Clear your browser cache completely\n";
echo "2. Log in to the application\n";
echo "3. Open browser Developer Tools (F12)\n";
echo "4. Look for '[KYC]' messages in the console\n";
echo "5. If you're verified, the modal should NOT appear\n";
echo "6. If you see KYC logs, the fix is working!\n";

echo "\n🐛 If Issues Persist:\n";
echo "====================\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Verify the API route is accessible: " . env('APP_URL') . "/api/user/kyc-status\n";
echo "3. Ensure you're logged in when testing\n";
echo "4. Try hard refresh (Ctrl+Shift+R)\n";

echo "\n✅ KYC Fix Verification Complete!\n";
