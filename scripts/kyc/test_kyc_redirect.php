<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Testing KYC Redirect Logic ===\n\n";

try {
    // Test with user 1 (employer)
    $user = User::find(1);
    if ($user) {
        echo "Testing with User 1 (Employer):\n";
        echo "- Name: {$user->name}\n";
        echo "- Role: {$user->role}\n";
        echo "- isEmployer(): " . ($user->isEmployer() ? 'true' : 'false') . "\n";
        
        // Simulate login
        Auth::login($user);
        echo "- Auth::check(): " . (Auth::check() ? 'true' : 'false') . "\n";
        echo "- Auth::id(): " . Auth::id() . "\n";
        echo "- Auth::user()->role: " . Auth::user()->role . "\n";
        echo "- Auth::user()->isEmployer(): " . (Auth::user()->isEmployer() ? 'true' : 'false') . "\n";
        
        $isEmployer = $user->isEmployer();
        $dashboardRoute = $isEmployer ? 'employer.dashboard' : 'account.dashboard';
        echo "- Dashboard Route: {$dashboardRoute}\n";
        
        try {
            $url = route($dashboardRoute);
            echo "- Route URL: {$url}\n";
            echo "- Route Resolution: ✅\n";
        } catch (Exception $e) {
            echo "- Route Error: ❌ " . $e->getMessage() . "\n";
        }
        
        // Test role middleware logic
        echo "\nTesting Role Middleware Logic:\n";
        if ($user->role === 'employer' && $user->isEmployer()) {
            echo "- Role check for employer: ✅ PASS\n";
        } else {
            echo "- Role check for employer: ❌ FAIL\n";
            echo "  - user->role: '{$user->role}'\n";
            echo "  - isEmployer(): " . ($user->isEmployer() ? 'true' : 'false') . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    
    // Test with user 3 (jobseeker)
    $user = User::find(3);
    if ($user) {
        echo "Testing with User 3 (Job Seeker):\n";
        echo "- Name: {$user->name}\n";
        echo "- Role: {$user->role}\n";
        echo "- isJobSeeker(): " . ($user->isJobSeeker() ? 'true' : 'false') . "\n";
        
        // Simulate login
        Auth::login($user);
        echo "- Auth::check(): " . (Auth::check() ? 'true' : 'false') . "\n";
        echo "- Auth::id(): " . Auth::id() . "\n";
        echo "- Auth::user()->role: " . Auth::user()->role . "\n";
        echo "- Auth::user()->isJobSeeker(): " . (Auth::user()->isJobSeeker() ? 'true' : 'false') . "\n";
        
        $isEmployer = $user->isEmployer();
        $dashboardRoute = $isEmployer ? 'employer.dashboard' : 'account.dashboard';
        echo "- Dashboard Route: {$dashboardRoute}\n";
        
        try {
            $url = route($dashboardRoute);
            echo "- Route URL: {$url}\n";
            echo "- Route Resolution: ✅\n";
        } catch (Exception $e) {
            echo "- Route Error: ❌ " . $e->getMessage() . "\n";
        }
        
        // Test role middleware logic
        echo "\nTesting Role Middleware Logic:\n";
        if ($user->role === 'jobseeker' && $user->isJobSeeker()) {
            echo "- Role check for jobseeker: ✅ PASS\n";
        } else {
            echo "- Role check for jobseeker: ❌ FAIL\n";
            echo "  - user->role: '{$user->role}'\n";
            echo "  - isJobSeeker(): " . ($user->isJobSeeker() ? 'true' : 'false') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";