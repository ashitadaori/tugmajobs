<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== JOB ROUTES TEST ===\n\n";

// Test if routes exist
$routes = [
    'employer.jobs.create' => 'Job Creation Form',
    'employer.jobs.store' => 'Job Store (POST)',
    'employer.jobs.index' => 'Jobs List'
];

foreach ($routes as $routeName => $description) {
    try {
        $url = route($routeName);
        echo "✅ {$description}: {$url}\n";
    } catch (Exception $e) {
        echo "❌ {$description}: Route not found - {$e->getMessage()}\n";
    }
}

echo "\n=== ROUTE DETAILS ===\n";

// Get all employer job routes
$allRoutes = Route::getRoutes();
$employerJobRoutes = [];

foreach ($allRoutes as $route) {
    $name = $route->getName();
    if ($name && strpos($name, 'employer.jobs') === 0) {
        $employerJobRoutes[] = [
            'name' => $name,
            'uri' => $route->uri(),
            'methods' => implode('|', $route->methods()),
            'action' => $route->getActionName()
        ];
    }
}

echo "Found " . count($employerJobRoutes) . " employer job routes:\n";
foreach ($employerJobRoutes as $route) {
    echo "- {$route['name']}: {$route['methods']} {$route['uri']} -> {$route['action']}\n";
}

echo "\nDone.\n";