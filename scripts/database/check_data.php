<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmployerProfile;
use App\Models\Job;

echo "Employer Profiles: " . EmployerProfile::count() . "\n";
echo "Jobs: " . Job::count() . "\n";

$profilesWithJobs = EmployerProfile::whereHas('jobs', function($query) {
    $query->where('status', 1);
})->count();

echo "Profiles with active jobs: " . $profilesWithJobs . "\n";

// Get sample employer profile data
$sampleProfile = EmployerProfile::with('jobs')->first();
if ($sampleProfile) {
    echo "Sample profile company name: " . ($sampleProfile->company_name ?? 'NULL') . "\n";
    echo "Sample profile description: " . ($sampleProfile->company_description ? 'EXISTS' : 'NULL') . "\n";
    echo "Sample profile jobs count: " . $sampleProfile->jobs->count() . "\n";
}
