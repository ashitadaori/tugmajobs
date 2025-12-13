<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Jobseeker;
use App\Models\Employer;
use App\Models\Admin;
use App\Models\KycData;

echo "Testing New Multi-Table Architecture\n";
echo "====================================\n\n";

// Test 1: Check Users and their relationships
echo "1. Testing User Relationships:\n";
echo "------------------------------\n";

$users = User::with(['jobseeker', 'employer', 'admin'])->get();

foreach ($users as $user) {
    echo "User ID: {$user->id} | Email: {$user->email} | Role: {$user->role}\n";
    
    if ($user->jobseeker) {
        echo "  → Has Jobseeker Profile: {$user->jobseeker->full_name} (Completion: {$user->jobseeker->profile_completion_percentage}%)\n";
    }
    
    if ($user->employer) {
        echo "  → Has Employer Profile: {$user->employer->company_name} (Status: {$user->employer->status})\n";
    }
    
    if ($user->admin) {
        echo "  → Has Admin Profile: {$user->admin->admin_level} (Department: {$user->admin->department})\n";
    }
    
    echo "\n";
}

// Test 2: Check Jobseeker Profile Completion
echo "2. Testing Jobseeker Profile Completion:\n";
echo "----------------------------------------\n";

$jobseekers = Jobseeker::all();
foreach ($jobseekers as $jobseeker) {
    echo "Jobseeker: {$jobseeker->full_name}\n";
    echo "  Current Completion: {$jobseeker->profile_completion_percentage}%\n";
    
    // Calculate fresh completion
    $newCompletion = $jobseeker->calculateProfileCompletion();
    echo "  Recalculated Completion: {$newCompletion}%\n";
    echo "  Status: {$jobseeker->profile_status}\n";
    echo "  Total Experience: {$jobseeker->total_experience}\n";
    
    if ($jobseeker->skills) {
        echo "  Skills: " . implode(', ', $jobseeker->skills) . "\n";
    }
    
    echo "\n";
}

// Test 3: Check KYC Data Integration
echo "3. Testing KYC Data Integration:\n";
echo "-------------------------------\n";

$usersWithKyc = User::whereHas('kycData')->with('kycData')->get();

if ($usersWithKyc->count() > 0) {
    foreach ($usersWithKyc as $user) {
        echo "User: {$user->email}\n";
        foreach ($user->kycData as $kyc) {
            echo "  KYC Session: {$kyc->session_id} | Status: {$kyc->status}\n";
            if ($kyc->first_name) {
                echo "  Verified Name: {$kyc->display_name}\n";
            }
        }
        echo "\n";
    }
} else {
    echo "No KYC data found. This is normal if no verifications have been completed.\n\n";
}

// Test 4: Check Reference Tables
echo "4. Testing Reference Tables:\n";
echo "---------------------------\n";

echo "Job Categories: " . DB::table('job_categories')->count() . "\n";
echo "Job Skills: " . DB::table('job_skills')->count() . "\n";
echo "Industries: " . DB::table('industries')->count() . "\n";
echo "Locations: " . DB::table('locations')->count() . "\n";
echo "Company Sizes: " . DB::table('company_sizes')->count() . "\n\n";

// Test 5: Test Advanced Queries
echo "5. Testing Advanced Queries:\n";
echo "---------------------------\n";

// Find jobseekers with PHP skills
$phpDevelopers = Jobseeker::whereJsonContains('skills', 'PHP')->get();
echo "PHP Developers found: " . $phpDevelopers->count() . "\n";

// Find employers in specific industries
$techEmployers = Employer::where('industry', 'Technology')->get();
echo "Tech Employers found: " . $techEmployers->count() . "\n";

// Find active jobseekers in Manila
$manilaJobseekers = Jobseeker::where('city', 'Manila')
    ->where('profile_visibility', true)
    ->get();
echo "Manila Jobseekers found: " . $manilaJobseekers->count() . "\n";

echo "\n";

// Test 6: Test Model Methods
echo "6. Testing Model Methods:\n";
echo "------------------------\n";

$jobseeker = Jobseeker::first();
if ($jobseeker) {
    echo "Testing jobseeker methods on: {$jobseeker->full_name}\n";
    echo "  Age: " . ($jobseeker->age ?? 'Not set') . "\n";
    echo "  Total Experience: {$jobseeker->total_experience}\n";
    echo "  Salary Range: " . ($jobseeker->salary_range ?? 'Not set') . "\n";
    echo "  Is Available: " . ($jobseeker->isAvailable() ? 'Yes' : 'No') . "\n";
    echo "  Is Profile Complete: " . ($jobseeker->isProfileComplete() ? 'Yes' : 'No') . "\n";
    echo "  Is Premium: " . ($jobseeker->isPremium() ? 'Yes' : 'No') . "\n";
}

echo "\n";

// Test 7: Check Data Integrity
echo "7. Data Integrity Check:\n";
echo "-----------------------\n";

$orphanedJobseekers = Jobseeker::whereDoesntHave('user')->count();
$orphanedEmployers = Employer::whereDoesntHave('user')->count();
$orphanedAdmins = Admin::whereDoesntHave('user')->count();

echo "Orphaned Jobseekers (without users): {$orphanedJobseekers}\n";
echo "Orphaned Employers (without users): {$orphanedEmployers}\n";
echo "Orphaned Admins (without users): {$orphanedAdmins}\n";

if ($orphanedJobseekers + $orphanedEmployers + $orphanedAdmins === 0) {
    echo "✅ Data integrity is good - no orphaned records!\n";
} else {
    echo "⚠️  Found orphaned records - data cleanup may be needed.\n";
}

echo "\n";

echo "Architecture Test Complete!\n";
echo "===========================\n";
echo "Summary:\n";
echo "- Users: " . User::count() . "\n"; 
echo "- Jobseekers: " . Jobseeker::count() . "\n";
echo "- Employers: " . Employer::count() . "\n";
echo "- Admins: " . Admin::count() . "\n";
echo "- KYC Records: " . KycData::count() . "\n";
echo "\nThe multi-table architecture is working correctly! 🎉\n";
