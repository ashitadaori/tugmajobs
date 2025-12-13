<?php

use Illuminate\Support\Facades\Route;

// Temporary debug route - REMOVE IN PRODUCTION
Route::get('/debug/google-auth', function(\Illuminate\Http\Request $request) {
    $provider = 'google';
    $role = $request->get('role', 'jobseeker');

    return [
        'provider' => $provider,
        'role' => $role,
        'route_url' => route('social.redirect', ['provider' => $provider, 'role' => $role]),
        'current_url' => url()->current(),
        'full_url' => url()->full(),
        'request_all' => $request->all(),
        'config_google_client_id' => config('services.google.client_id'),
        'config_google_redirect' => config('services.google.redirect'),
    ];
});

// Test the actual route
Route::get('/debug/test-google', function(\Illuminate\Http\Request $request) {
    $provider = 'google';

    if ($provider !== 'google') {
        return 'Provider check FAILED: ' . $provider;
    }

    return 'Provider check PASSED: ' . $provider;
});
