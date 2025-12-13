<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set up the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Current KYC Status Debug ===\n\n";

// Find all employer users and their KYC status
$employers = User::where('role', 'employer')->get();

echo "Found " . $employers->count() . " employer(s):\n\n";

foreach ($employers as $employer) {
    echo "Employer ID: {$employer->id}\n";
    echo "Name: {$employer->name}\n";
    echo "Email: {$employer->email}\n";
    echo "Role: {$employer->role}\n";
    echo "KYC Status: {$employer->kyc_status}\n";
    echo "KYC Session ID: " . ($employer->kyc_session_id ?? 'null') . "\n";
    echo "KYC Verified At: " . ($employer->kyc_verified_at ? $employer->kyc_verified_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "KYC Data: " . ($employer->kyc_data ? json_encode($employer->kyc_data) : 'null') . "\n";
    echo "isKycVerified(): " . ($employer->isKycVerified() ? 'true' : 'false') . "\n";
    echo "isEmployer(): " . ($employer->isEmployer() ? 'true' : 'false') . "\n";
    echo "Created At: " . $employer->created_at->format('Y-m-d H:i:s') . "\n";
    echo "Updated At: " . $employer->updated_at->format('Y-m-d H:i:s') . "\n";
    echo "---\n\n";
}

echo "=== Debug Complete ===\n";