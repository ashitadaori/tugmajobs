<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Testing Dashboard Routes ===\n\n";

try {
    // Test user 1 (employer)
    $user = User::find(1);
    if ($user) {
        echo "User 1 Details:\n";
        echo "- Name: {$user->name}\n";
        echo "- Role: {$user->role}\n";
        echo "- isEmployer(): " . ($user->isEmployer() ? 'true' : 'false') . "\n";
        echo "- isJobSeeker(): " . ($user->isJobSeeker() ? 'true' : 'false') . "\n";
        
        $dashboardRoute = $user->isEmployer() ? 'employer.dashboard' : 'account.dashboard';
        echo "- Dashboard Route: {$dashboardRoute}\n";
        
        // Test if route exists
        try {
            $url = route($dashboardRoute);
            echo "- Route URL: {$url}\n";
            echo "- Route exists: ✅\n";
        } catch (Exception $e) {
            echo "- Route error: ❌ " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // Test user 3 (jobseeker)
    $user = User::find(3);
    if ($user) {
        echo "User 3 Details:\n";
        echo "- Name: {$user->name}\n";
        echo "- Role: {$user->role}\n";
        echo "- isEmployer(): " . ($user->isEmployer() ? 'true' : 'false') . "\n";
        echo "- isJobSeeker(): " . ($user->isJobSeeker() ? 'true' : 'false') . "\n";
        
        $dashboardRoute = $user->isEmployer() ? 'employer.dashboard' : 'account.dashboard';
        echo "- Dashboard Route: {$dashboardRoute}\n";
        
        // Test if route exists
        try {
            $url = route($dashboardRoute);
            echo "- Route URL: {$url}\n";
            echo "- Route exists: ✅\n";
        } catch (Exception $e) {
            echo "- Route error: ❌ " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Available Dashboard Routes ===\n";
    $routes = ['employer.dashboard', 'account.dashboard', 'admin.dashboard'];
    foreach ($routes as $routeName) {
        try {
            $url = route($routeName);
            echo "✅ {$routeName}: {$url}\n";
        } catch (Exception $e) {
            echo "❌ {$routeName}: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";