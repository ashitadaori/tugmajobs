<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Job;
use Illuminate\Support\Facades\DB;

// Enable query logging
DB::enableQueryLog();

// Find a job to test
$job = Job::first();

if (!$job) {
    echo "No jobs found in database\n";
    exit;
}

echo "Testing Job Vacancy Update\n";
echo "==========================\n\n";

echo "Job ID: {$job->id}\n";
echo "Current Title: {$job->title}\n";
echo "Current Vacancy: {$job->vacancy}\n";
echo "Current Status: {$job->status}\n\n";

// Test 1: Direct assignment and save
echo "Test 1: Direct assignment and save\n";
$oldVacancy = $job->vacancy;
$newVacancy = ($oldVacancy ?? 1) + 1;

$job->vacancy = $newVacancy;
echo "Set vacancy to: {$newVacancy}\n";

$saved = $job->save();
echo "Save result: " . ($saved ? 'SUCCESS' : 'FAILED') . "\n";

// Refresh from database
$job->refresh();
echo "Vacancy after refresh: {$job->vacancy}\n";

if ($job->vacancy == $newVacancy) {
    echo "✓ Vacancy saved correctly!\n\n";
} else {
    echo "✗ Vacancy NOT saved! Expected {$newVacancy}, got {$job->vacancy}\n\n";
}

// Show queries
echo "Queries executed:\n";
foreach (DB::getQueryLog() as $query) {
    echo "SQL: {$query['query']}\n";
    echo "Bindings: " . json_encode($query['bindings']) . "\n";
    echo "Time: {$query['time']}ms\n\n";
}

// Test 2: Using update method
DB::flushQueryLog();
echo "\nTest 2: Using update method\n";
$newVacancy2 = $newVacancy + 1;

$updated = $job->update(['vacancy' => $newVacancy2]);
echo "Update result: " . ($updated ? 'SUCCESS' : 'FAILED') . "\n";

$job->refresh();
echo "Vacancy after update: {$job->vacancy}\n";

if ($job->vacancy == $newVacancy2) {
    echo "✓ Vacancy updated correctly!\n\n";
} else {
    echo "✗ Vacancy NOT updated! Expected {$newVacancy2}, got {$job->vacancy}\n\n";
}

// Show queries
echo "Queries executed:\n";
foreach (DB::getQueryLog() as $query) {
    echo "SQL: {$query['query']}\n";
    echo "Bindings: " . json_encode($query['bindings']) . "\n";
    echo "Time: {$query['time']}ms\n\n";
}

// Reset to original value
$job->update(['vacancy' => $oldVacancy]);
echo "\nReset vacancy back to original value: {$oldVacancy}\n";
