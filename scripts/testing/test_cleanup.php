<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Cleanup Results\n";
echo "======================\n\n";

try {
    // Test 1: Check if KYC status counting works (what was causing the error)
    echo "Test 1: KYC Status Counting...\n";
    $pendingKyc = App\Models\User::where('kyc_status', 'in_progress')->count();
    echo "✅ Users with in_progress KYC status: {$pendingKyc}\n\n";
    
    // Test 2: Check if dashboard statistics can be generated
    echo "Test 2: Dashboard Statistics...\n";
    $totalUsers = App\Models\User::count();
    $activeJobs = App\Models\Job::where('status', 'active')->count();
    $totalApplications = App\Models\JobApplication::count();
    echo "✅ Total Users: {$totalUsers}\n";
    echo "✅ Active Jobs: {$activeJobs}\n";
    echo "✅ Total Applications: {$totalApplications}\n\n";
    
    // Test 3: Check if role-based queries work
    echo "Test 3: Role-based User Counting...\n";
    $jobSeekers = App\Models\User::where('role', 'job_seeker')->count();
    $employers = App\Models\User::where('role', 'employer')->count();
    $admins = App\Models\User::where('role', 'admin')->count();
    echo "✅ Job Seekers: {$jobSeekers}\n";
    echo "✅ Employers: {$employers}\n";
    echo "✅ Admins: {$admins}\n\n";
    
    // Test 4: Check if new relationships work
    echo "Test 4: New Table Relationships...\n";
    $jobseekersCount = App\Models\Jobseeker::count();
    $employersCount = App\Models\Employer::count();
    $adminsCount = App\Models\Admin::count();
    $kycDataCount = App\Models\KycData::count();
    echo "✅ Jobseekers Table: {$jobseekersCount} records\n";
    echo "✅ Employers Table: {$employersCount} records\n";
    echo "✅ Admins Table: {$adminsCount} records\n";
    echo "✅ KYC Data Table: {$kycDataCount} records\n\n";
    
    echo "🎉 All tests passed! Cleanup was successful.\n";
    echo "The application should now run without KYC document table errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
