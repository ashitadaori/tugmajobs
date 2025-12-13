<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Job;
use App\Models\User;

echo "=== Testing Job Update ===\n\n";

// Get a job
$job = Job::first();

if (!$job) {
    echo "❌ No jobs found in database\n";
    exit;
}

echo "Job ID: {$job->id}\n";
echo "Title: {$job->title}\n";
echo "Current Vacancy: {$job->vacancy}\n";
echo "Status: {$job->status}\n";
echo "Employer ID: {$job->employer_id}\n\n";

// Try to update vacancy
echo "Attempting to update vacancy to 2...\n";
$job->vacancy = 2;
$saved = $job->save();

if ($saved) {
    echo "✅ Update successful!\n";
    echo "New Vacancy: {$job->vacancy}\n";
} else {
    echo "❌ Update failed!\n";
}

// Check if it persisted
$job->refresh();
echo "\nAfter refresh:\n";
echo "Vacancy: {$job->vacancy}\n";

echo "\n=== Test Complete ===\n";
