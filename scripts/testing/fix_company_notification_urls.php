<?php

/**
 * Script to fix company notification URLs
 * Run this once to update existing notifications
 * 
 * Usage: php fix_company_notification_urls.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing company notification URLs...\n\n";

// Get all new_company notifications
$notifications = DB::table('notifications')
    ->where('type', 'App\Notifications\NewCompanyJoinedNotification')
    ->whereRaw("JSON_EXTRACT(data, '$.type') = 'new_company'")
    ->get();

echo "Found " . $notifications->count() . " company notifications\n\n";

$updated = 0;

foreach ($notifications as $notification) {
    $data = json_decode($notification->data, true);
    
    if (isset($data['company_id'])) {
        $companyId = $data['company_id'];
        
        // Update the URL to use the correct route
        $data['url'] = url('/companies/' . $companyId);
        $data['action_url'] = url('/companies/' . $companyId);
        
        DB::table('notifications')
            ->where('id', $notification->id)
            ->update([
                'data' => json_encode($data)
            ]);
        
        $updated++;
        echo "✓ Updated notification {$notification->id} for company {$companyId}\n";
    }
}

echo "\n✅ Done! Updated {$updated} notifications\n";
