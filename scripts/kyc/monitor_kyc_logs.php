<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Real-time KYC Log Monitor\n";
echo "============================\n";
echo "Monitoring Laravel logs for KYC activity...\n";
echo "Press Ctrl+C to stop monitoring\n";
echo "\n";

$logFile = storage_path('logs/laravel.log');
$lastSize = 0;

if (file_exists($logFile)) {
    $lastSize = filesize($logFile);
    echo "📁 Log file: {$logFile}\n";
    echo "📊 Current size: " . number_format($lastSize) . " bytes\n";
} else {
    echo "❌ Log file not found: {$logFile}\n";
    exit(1);
}

echo "\n";
echo "🎯 Ready to monitor! Start your KYC verification now...\n";
echo "Looking for: webhook requests, KYC status changes, errors\n";
echo "========================================================\n";

$lastLogTime = time();

while (true) {
    if (file_exists($logFile)) {
        $currentSize = filesize($logFile);
        
        if ($currentSize > $lastSize) {
            // New content added to log
            $handle = fopen($logFile, 'r');
            fseek($handle, $lastSize);
            $newContent = fread($handle, $currentSize - $lastSize);
            fclose($handle);
            
            $lines = explode("\n", $newContent);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Filter for KYC-related logs
                if (stripos($line, 'kyc') !== false || 
                    stripos($line, 'webhook') !== false || 
                    stripos($line, 'didit') !== false ||
                    stripos($line, 'verification') !== false) {
                    
                    $timestamp = date('H:i:s');
                    
                    // Color code different types of logs
                    if (stripos($line, 'error') !== false || stripos($line, 'failed') !== false) {
                        echo "🔴 [{$timestamp}] {$line}\n";
                    } elseif (stripos($line, 'warning') !== false) {
                        echo "🟡 [{$timestamp}] {$line}\n";
                    } elseif (stripos($line, 'webhook') !== false) {
                        echo "📡 [{$timestamp}] {$line}\n";
                    } elseif (stripos($line, 'verified') !== false || stripos($line, 'success') !== false) {
                        echo "🟢 [{$timestamp}] {$line}\n";
                    } else {
                        echo "ℹ️  [{$timestamp}] {$line}\n";
                    }
                    
                    $lastLogTime = time();
                }
            }
            
            $lastSize = $currentSize;
        }
    }
    
    // Show periodic status updates
    if (time() - $lastLogTime > 30) {
        echo "⏰ [" . date('H:i:s') . "] Still monitoring... (no KYC activity in last 30s)\n";
        $lastLogTime = time();
    }
    
    // Check user status periodically
    static $lastStatusCheck = 0;
    if (time() - $lastStatusCheck > 10) {
        $user = \App\Models\User::find(1);
        if ($user) {
            static $lastKnownStatus = null;
            if ($lastKnownStatus !== $user->kyc_status) {
                echo "👤 [" . date('H:i:s') . "] User status changed: {$lastKnownStatus} → {$user->kyc_status}\n";
                $lastKnownStatus = $user->kyc_status;
                
                if ($user->kyc_status === 'verified') {
                    echo "🎉 [" . date('H:i:s') . "] SUCCESS! User is now verified!\n";
                    echo "📊 Verification completed at: " . ($user->kyc_verified_at ? $user->kyc_verified_at->format('H:i:s') : 'Unknown') . "\n";
                    
                    if ($user->kyc_data) {
                        echo "📋 KYC Data available: Yes\n";
                        
                        // Check if we have webhook data
                        if (isset($user->kyc_data['webhook_payload'])) {
                            echo "📡 Webhook data received: Yes\n";
                            echo "🔍 Webhook status: " . ($user->kyc_data['webhook_payload']['status'] ?? 'Unknown') . "\n";
                        } else {
                            echo "📡 Webhook data received: No\n";
                        }
                        
                        // Check if we have extracted personal data
                        if (isset($user->kyc_data['extracted_data']) || 
                            isset($user->kyc_data['webhook_payload']['extracted_data'])) {
                            echo "👤 Personal data extracted: Yes\n";
                        } else {
                            echo "👤 Personal data extracted: No\n";
                        }
                    } else {
                        echo "📋 KYC Data available: No\n";
                    }
                }
            }
            $lastStatusCheck = time();
        }
    }
    
    usleep(500000); // Sleep for 0.5 seconds
}