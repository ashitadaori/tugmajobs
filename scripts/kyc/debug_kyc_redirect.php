<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request
$request = Illuminate\Http\Request::create('/');
$response = $kernel->handle($request);

// Set up the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

echo "=== KYC Redirect Debug ===\n\n";

// Test 1: Check if employer.dashboard route exists
echo "1. Checking if employer.dashboard route exists:\n";
try {
    $url = route('employer.dashboard');
    echo "✅ Route exists: {$url}\n";
} catch (Exception $e) {
    echo "❌ Route does not exist: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Find an employer user
echo "2. Finding an employer user:\n";
$employer = User::where('role', 'employer')->first();
if ($employer) {
    echo "✅ Found employer: ID {$employer->id}, Name: {$employer->name}, Role: {$employer->role}\n";
    echo "   - isEmployer(): " . ($employer->isEmployer() ? 'true' : 'false') . "\n";
    echo "   - KYC Status: {$employer->kyc_status}\n";
} else {
    echo "❌ No employer found\n";
}

echo "\n";

// Test 3: Test the redirect logic
if ($employer) {
    echo "3. Testing redirect logic:\n";
    
    // Simulate login
    Auth::login($employer);
    echo "   - User logged in: " . (Auth::check() ? 'true' : 'false') . "\n";
    echo "   - Auth user ID: " . Auth::id() . "\n";
    echo "   - Auth user role: " . Auth::user()->role . "\n";
    echo "   - Auth user isEmployer(): " . (Auth::user()->isEmployer() ? 'true' : 'false') . "\n";
    
    // Test the dashboard route determination
    $user = Auth::user();
    $isEmployer = $user->isEmployer();
    $dashboardRoute = $isEmployer ? 'employer.dashboard' : 'account.dashboard';
    
    echo "   - Dashboard route determined: {$dashboardRoute}\n";
    
    try {
        $dashboardUrl = route($dashboardRoute);
        echo "   - Dashboard URL: {$dashboardUrl}\n";
    } catch (Exception $e) {
        echo "   - ❌ Error generating dashboard URL: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test 4: Check all available routes with 'employer' in the name
echo "4. Available employer routes:\n";
$routes = Route::getRoutes();
foreach ($routes as $route) {
    $name = $route->getName();
    if ($name && strpos($name, 'employer') !== false) {
        echo "   - {$name}: " . $route->uri() . "\n";
    }
}

echo "\n";

// Test 5: Check middleware on employer.dashboard route
echo "5. Checking middleware on employer.dashboard route:\n";
try {
    $route = Route::getRoutes()->getByName('employer.dashboard');
    if ($route) {
        $middleware = $route->middleware();
        echo "   - Middleware: " . implode(', ', $middleware) . "\n";
        echo "   - URI: " . $route->uri() . "\n";
        echo "   - Methods: " . implode(', ', $route->methods()) . "\n";
    } else {
        echo "   - ❌ Route not found\n";
    }
} catch (Exception $e) {
    echo "   - ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";