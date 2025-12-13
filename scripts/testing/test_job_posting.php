<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EmployerDocument;
use Illuminate\Support\Facades\Auth;

echo "=== JOB POSTING TEST RESULTS ===\n\n";

// Test each employer
$employers = User::where('role', 'employer')->get();

echo "📊 CURRENT STATUS:\n";
foreach ($employers as $employer) {
    $canPost = $employer->canPostJobs() ? '✅ CAN POST' : '❌ CANNOT POST';
    echo "   {$employer->name} ({$employer->email}): {$canPost}\n";
    
    if (!$employer->canPostJobs()) {
        if (!$employer->isKycVerified()) {
            echo "      → Missing: KYC Verification\n";
        }
        if (!$employer->hasRequiredDocumentsApproved()) {
            echo "      → Missing: Required Documents\n";
        }
    }
}

echo "\n🔍 ANALYSIS:\n";

$canPost = $employers->filter(fn($emp) => $emp->canPostJobs())->count();
$cannotPost = $employers->count() - $canPost;

echo "   ✅ Can post jobs: {$canPost} employer(s)\n";
echo "   ❌ Cannot post jobs: {$cannotPost} employer(s)\n\n";

if ($cannotPost > 0) {
    echo "🎯 ISSUE IDENTIFIED:\n";
    echo "   The employers who cannot post jobs need to:\n";
    echo "   1. Complete KYC verification\n";
    echo "   2. Submit and get approval for required documents:\n";
    echo "      • Business Registration Certificate\n";
    echo "      • Tax Identification Certificate\n\n";
    
    echo "💡 SOLUTIONS:\n\n";
    
    echo "SOLUTION 1 - For Testing/Development:\n";
    echo "-----------\n";
    echo "Run this command to fix all employers:\n";
    echo "php fix_employer_job_posting.php\n";
    echo "Then choose option 3 (Both KYC and documents)\n\n";
    
    echo "SOLUTION 2 - Manual Fix via Admin Panel:\n";
    echo "-----------\n";
    echo "1. Login as admin\n";
    echo "2. Go to KYC management and approve pending verifications\n";
    echo "3. Go to Employer Documents and approve required documents\n\n";
    
    echo "SOLUTION 3 - Direct Database Update:\n";
    echo "-----------\n";
    echo "UPDATE users SET kyc_status='verified', kyc_verified_at=NOW() WHERE role='employer';\n";
    echo "INSERT INTO employer_documents (user_id, document_type, status, ...) VALUES (...);\n\n";
    
    echo "SOLUTION 4 - Temporarily Disable Middleware:\n";
    echo "-----------\n";
    echo "In routes/web.php, remove the 'employer.kyc' middleware temporarily:\n";
    echo "Change: Route::middleware(['employer.kyc'])->group(...)\n";
    echo "To:     // Route::middleware(['employer.kyc'])->group(...)\n\n";
    
} else {
    echo "✅ ALL EMPLOYERS CAN POST JOBS!\n";
    echo "   If you're still experiencing issues, check:\n";
    echo "   1. Make sure you're logged in as an employer\n";
    echo "   2. Clear browser cache and cookies\n";
    echo "   3. Check for JavaScript errors in browser console\n";
    echo "   4. Verify you're accessing the correct route: /employer/jobs/create\n\n";
}

echo "🛠️  QUICK STATUS CHECK:\n";
echo "URL to test: https://85bf53cc6497.ngrok-free.app/employer/jobs/create\n";
echo "Login as one of these verified employers:\n";

$verifiedEmployers = $employers->filter(fn($emp) => $emp->canPostJobs());
foreach ($verifiedEmployers as $emp) {
    echo "   • {$emp->name} ({$emp->email})\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed. If you need assistance, run the fix script!\n";
