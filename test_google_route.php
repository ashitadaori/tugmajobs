<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a fake request to /auth/google
$request = Illuminate\Http\Request::create('/auth/google', 'GET', ['role' => 'jobseeker']);

try {
    $response = $kernel->handle($request);

    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Redirect Location: " . ($response->headers->get('Location') ?? 'NONE') . "\n";
    echo "Response Content (first 500 chars):\n";
    echo substr($response->getContent(), 0, 500) . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

$kernel->terminate($request, $response ?? null);
