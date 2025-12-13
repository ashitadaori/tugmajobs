<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Job;
use App\Models\User;
use App\Models\JobApplication;

echo "=== ADMIN ANALYTICS DATA VERIFICATION ===\n\n";

// Job Statistics
echo "📊 JOB STATISTICS:\n";
echo "-------------------\n";
echo "Total Jobs: " . Job::count() . "\n";
echo "Approved (Status 1): " . Job::where('status', Job::STATUS_APPROVED)->count() . "\n";
echo "Pending (Status 0): " . Job::where('status', Job::STATUS_PENDING)->count() . "\n";
echo "Rejected (Status 2): " . Job::where('status', Job::STATUS_REJECTED)->count() . "\n\n";

// Company Statistics
echo "🏢 COMPANY STATISTICS:\n";
echo "----------------------\n";
$totalCompanies = User::where('role', 'employer')->count();
$activeCompanies = User::where('role', 'employer')->whereHas('jobs')->count();
$verifiedCompanies = User::where('role', 'employer')->whereNotNull('email_verified_at')->count();

echo "Total Companies: " . $totalCompanies . "\n";
echo "Active Companies (with jobs): " . $activeCompanies . "\n";
echo "Inactive Companies: " . ($totalCompanies - $activeCompanies) . "\n";
echo "Verified Companies: " . $verifiedCompanies . "\n";
echo "Unverified Companies: " . ($totalCompanies - $verifiedCompanies) . "\n\n";

// Application Statistics
echo "📝 APPLICATION STATISTICS:\n";
echo "--------------------------\n";
echo "Total Applications: " . JobApplication::count() . "\n";
echo "Pending: " . JobApplication::where('status', 'pending')->count() . "\n";
echo "Accepted: " . JobApplication::where('status', 'accepted')->count() . "\n";
echo "Rejected: " . JobApplication::where('status', 'rejected')->count() . "\n\n";

// Top Companies by Jobs
echo "🏆 TOP 5 COMPANIES BY JOBS:\n";
echo "----------------------------\n";
$topCompanies = User::where('role', 'employer')
    ->withCount('jobs')
    ->having('jobs_count', '>', 0)
    ->orderByDesc('jobs_count')
    ->limit(5)
    ->get();

foreach ($topCompanies as $index => $company) {
    echo ($index + 1) . ". " . $company->name . " - " . $company->jobs_count . " jobs\n";
}

echo "\n✅ Verification Complete!\n";
echo "\nIf the numbers above match what you see in the admin analytics page,\n";
echo "then the data is accurate and working correctly.\n";
