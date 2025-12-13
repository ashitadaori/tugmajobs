<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EmployerDocument;
use Illuminate\Support\Facades\DB;

echo "=== EMPLOYER JOB POSTING DIAGNOSTIC ===\n\n";

// Get all employers
$employers = User::where('role', 'employer')->get();

if ($employers->isEmpty()) {
    echo "❌ No employers found in the database.\n";
    echo "Please register as an employer first.\n";
    exit;
}

echo "Total employers found: " . $employers->count() . "\n\n";

foreach ($employers as $employer) {
    echo "👤 EMPLOYER: {$employer->name} (ID: {$employer->id})\n";
    echo "   Email: {$employer->email}\n";
    echo "   Role: {$employer->role}\n\n";
    
    // Check KYC Status
    echo "🔍 KYC VERIFICATION:\n";
    echo "   KYC Status: {$employer->kyc_status}\n";
    echo "   KYC Verified: " . ($employer->isKycVerified() ? '✅ Yes' : '❌ No') . "\n";
    
    if ($employer->kyc_verified_at) {
        echo "   Verified At: {$employer->kyc_verified_at}\n";
    }
    echo "\n";
    
    // Check Documents
    echo "📄 REQUIRED DOCUMENTS:\n";
    $requiredTypes = collect(EmployerDocument::getDocumentTypes())
        ->filter(fn($config) => $config['required'])
        ->keys();
    
    $hasAllRequiredDocs = true;
    
    foreach ($requiredTypes as $type) {
        $config = EmployerDocument::getDocumentTypes()[$type];
        $document = $employer->employerDocuments()
            ->where('document_type', $type)
            ->first();
        
        echo "   • {$config['label']}:\n";
        
        if (!$document) {
            echo "     Status: ❌ Not submitted\n";
            $hasAllRequiredDocs = false;
        } else {
            $statusIcon = match($document->status) {
                'approved' => '✅',
                'rejected' => '❌',
                default => '⏳'
            };
            echo "     Status: {$statusIcon} " . ucfirst($document->status) . "\n";
            echo "     Submitted: {$document->submitted_at}\n";
            
            if ($document->status !== 'approved') {
                $hasAllRequiredDocs = false;
            }
            
            if ($document->admin_notes) {
                echo "     Notes: {$document->admin_notes}\n";
            }
        }
        echo "\n";
    }
    
    // Check overall posting ability
    echo "🎯 JOB POSTING ABILITY:\n";
    echo "   Has Required Documents Approved: " . ($employer->hasRequiredDocumentsApproved() ? '✅ Yes' : '❌ No') . "\n";
    echo "   Can Post Jobs: " . ($employer->canPostJobs() ? '✅ Yes' : '❌ No') . "\n";
    
    $verificationStatus = $employer->getEmployerVerificationStatus();
    echo "   Verification Status: {$verificationStatus['status']}\n";
    echo "   Message: {$verificationStatus['message']}\n\n";
    
    // Provide actionable recommendations
    echo "💡 RECOMMENDATIONS:\n";
    
    if (!$employer->isKycVerified()) {
        echo "   1. ⚠️  Complete KYC verification first\n";
        echo "      → Go to your dashboard and click 'Complete KYC Verification'\n\n";
    }
    
    if (!$employer->hasRequiredDocumentsApproved()) {
        $missingDocs = [];
        foreach ($requiredTypes as $type) {
            $document = $employer->employerDocuments()
                ->where('document_type', $type)
                ->where('status', 'approved')
                ->first();
            
            if (!$document) {
                $config = EmployerDocument::getDocumentTypes()[$type];
                $missingDocs[] = $config['label'];
            }
        }
        
        if (!empty($missingDocs)) {
            echo "   2. 📋 Upload and get approval for missing documents:\n";
            foreach ($missingDocs as $docName) {
                echo "      → {$docName}\n";
            }
            echo "      → Go to 'Employer > Documents' to upload\n\n";
        } else {
            echo "   2. ⏳ Wait for document approval from admin\n";
            echo "      → Contact admin to review your submitted documents\n\n";
        }
    }
    
    if ($employer->canPostJobs()) {
        echo "   ✅ You're all set! You can now post jobs.\n\n";
    }
    
    echo str_repeat("-", 60) . "\n\n";
}

// Check if there are any issues with middleware registration
echo "🔧 MIDDLEWARE CHECK:\n";
$middlewares = app('router')->getMiddleware();
echo "   employer.kyc middleware: " . (isset($middlewares['employer.kyc']) ? '✅ Registered' : '❌ Not registered') . "\n";

// Check routes
echo "\n🛣️  ROUTE CHECK:\n";
try {
    $createRoute = route('employer.jobs.create');
    echo "   Job creation route: ✅ Available at {$createRoute}\n";
} catch (Exception $e) {
    echo "   Job creation route: ❌ Error - " . $e->getMessage() . "\n";
}

try {
    $storeRoute = route('employer.jobs.store');
    echo "   Job store route: ✅ Available at {$storeRoute}\n";
} catch (Exception $e) {
    echo "   Job store route: ❌ Error - " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";

// Quick fix options
echo "\n🚀 QUICK FIX OPTIONS:\n";
echo "1. To bypass KYC for testing (NOT recommended for production):\n";
echo "   → Update user kyc_status to 'verified' in database\n";
echo "   → OR temporarily disable the middleware in routes/web.php\n\n";

echo "2. To approve all documents for testing:\n";
echo "   → Update employer_documents status to 'approved' in database\n\n";

echo "3. To create test documents for an employer:\n";
echo "   → Run the create_test_documents.php script (if available)\n\n";

echo "For production, ensure proper KYC and document verification processes are followed.\n";
