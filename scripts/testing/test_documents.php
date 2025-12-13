<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$employer = App\Models\User::where('role', 'employer')->first();

if (!$employer) {
    echo "No employer found.\n";
    exit;
}

echo "Testing employer: {$employer->name} (ID: {$employer->id})\n";
echo "KYC Status: {$employer->kyc_status}\n";
echo "Is KYC Verified: " . ($employer->isKycVerified() ? 'YES' : 'NO') . "\n";

try {
    $hasDocuments = $employer->hasRequiredDocumentsApproved();
    echo "Has Required Documents: " . ($hasDocuments ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "Error checking documents: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

try {
    $canPost = $employer->canPostJobs();
    echo "Can Post Jobs: " . ($canPost ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "Error checking canPostJobs: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Check if EmployerDocument class exists
if (class_exists('App\Models\EmployerDocument')) {
    echo "EmployerDocument class exists.\n";
    try {
        $documentTypes = App\Models\EmployerDocument::getDocumentTypes();
        echo "Document types configured: " . count($documentTypes) . "\n";
        foreach ($documentTypes as $type => $config) {
            $required = $config['required'] ?? false;
            echo "  - {$type}: " . ($required ? 'REQUIRED' : 'optional') . "\n";
        }
    } catch (Exception $e) {
        echo "Error getting document types: " . $e->getMessage() . "\n";
    }
} else {
    echo "EmployerDocument class does NOT exist.\n";
}
