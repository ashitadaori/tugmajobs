<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EmployerProfile;
use App\Models\Job;

echo "Company Statistics:\n";
echo "==================\n";

$totalCompanies = EmployerProfile::count();
echo "Total companies in database: $totalCompanies\n";

$companiesWithActiveJobs = EmployerProfile::whereHas('jobs', function($query) {
    $query->where('status', 'active');
})->count();
echo "Companies with active jobs: $companiesWithActiveJobs\n";

$totalActiveJobs = Job::where('status', 'active')->count();
echo "Total active jobs: $totalActiveJobs\n";

// Check job statuses
echo "\nJob Status Distribution:\n";
echo "=====================\n";
$jobStatuses = Job::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
foreach ($jobStatuses as $status) {
    echo "- Status '{$status->status}': {$status->count} jobs\n";
}

// Show first few companies for reference
echo "\nFirst 5 companies:\n";
echo "==================\n";
$companies = EmployerProfile::with('jobs')->take(5)->get();
foreach ($companies as $company) {
    $totalJobs = $company->jobs()->count();
    $activeJobsCount = $company->jobs()->active()->count();
    echo "- {$company->company_name} (Total jobs: $totalJobs, Active jobs: $activeJobsCount)\n";
}

// Fix job statuses - update jobs with status '1' to 'approved'
echo "\nFixing job statuses...\n";
$jobsToFix = Job::where('status', '1')->get();
foreach ($jobsToFix as $job) {
    $job->update(['status' => Job::STATUS_APPROVED]);
    echo "- Updated job '{$job->title}' status from '1' to 'approved'\n";
}

// Re-check after fix
echo "\nAfter status fix:\n";
echo "=================\n";
$companiesWithActiveJobs = EmployerProfile::whereHas('jobs', function($query) {
    $query->active();
})->count();
echo "Companies with active jobs: $companiesWithActiveJobs\n";

$totalActiveJobs = Job::where('status', Job::STATUS_APPROVED)->count();
echo "Total approved jobs: $totalActiveJobs\n";
