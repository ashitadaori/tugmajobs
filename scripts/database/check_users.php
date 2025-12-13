<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== User Database Check ===\n\n";

try {
    $userCount = \App\Models\User::count();
    echo "Total users: {$userCount}\n\n";
    
    if ($userCount > 0) {
        $users = \App\Models\User::take(5)->get();
        
        echo "Sample users:\n";
        foreach ($users as $user) {
            echo "- ID: {$user->id}, Name: {$user->name}, Role: {$user->role}, KYC Status: " . ($user->kyc_status ?? 'null') . "\n";
        }
        
        echo "\nKYC Status Distribution:\n";
        $statuses = \App\Models\User::selectRaw('kyc_status, COUNT(*) as count')
            ->groupBy('kyc_status')
            ->get();
            
        foreach ($statuses as $status) {
            echo "- " . ($status->kyc_status ?? 'null') . ": {$status->count}\n";
        }
    } else {
        echo "No users found in database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";