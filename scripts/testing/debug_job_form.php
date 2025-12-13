<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\JobType;
use App\Models\Category;

echo "=== JOB FORM DEBUGGING ===\n\n";

// Check if required tables and data exist
echo "📊 CHECKING DATABASE REQUIREMENTS:\n";

// Check JobTypes
$jobTypes = JobType::where('status', 1)->get();
echo "   Job Types Available: " . $jobTypes->count() . "\n";
if ($jobTypes->count() > 0) {
    echo "      Examples: " . $jobTypes->pluck('name')->take(3)->join(', ') . "\n";
}

// Check Categories
$categories = Category::where('status', 1)->get();
echo "   Categories Available: " . $categories->count() . "\n";
if ($categories->count() > 0) {
    echo "      Examples: " . $categories->pluck('name')->take(3)->join(', ') . "\n";
}

// Check employers
$employers = User::where('role', 'employer')->get();
echo "   Employers: " . $employers->count() . "\n";
$verifiedEmployers = $employers->filter(fn($emp) => $emp->canPostJobs())->count();
echo "   Verified Employers: {$verifiedEmployers}\n";

echo "\n🔍 VALIDATION RULES CHECK:\n";

// Simulate validation requirements
$validationRules = [
    'title' => 'required|string|max:255',
    'description' => 'required|string|max:5000',
    'requirements' => 'required|string|max:3000',
    'benefits' => 'nullable|string|max:2000',
    'location' => 'required|string|max:255',
    'latitude' => 'nullable|numeric',
    'longitude' => 'nullable|numeric',
    'job_type_id' => 'required|exists:job_types,id',
    'category_id' => 'required|exists:categories,id',
    'vacancy' => 'required|integer|min:1|max:100',
    'experience_level' => 'required|in:entry,mid,senior,executive',
    'education_level' => 'nullable|in:high_school,vocational,associate,bachelor,master,doctorate',
    'salary_min' => 'nullable|numeric|min:0',
    'salary_max' => 'nullable|numeric|min:0',
    'deadline' => 'nullable|date|after:today',
    'is_remote' => 'boolean',
    'is_featured' => 'boolean',
    'skills' => 'nullable|string'
];

$requiredFields = [];
$optionalFields = [];

foreach ($validationRules as $field => $rule) {
    if (strpos($rule, 'required') !== false) {
        $requiredFields[] = $field;
    } else {
        $optionalFields[] = $field;
    }
}

echo "   Required Fields (" . count($requiredFields) . "): " . implode(', ', $requiredFields) . "\n";
echo "   Optional Fields (" . count($optionalFields) . "): " . implode(', ', $optionalFields) . "\n";

echo "\n📝 COMMON VALIDATION ISSUES:\n";

// Check for common issues
if ($jobTypes->isEmpty()) {
    echo "   ❌ No job types available - this will cause validation errors\n";
} else {
    echo "   ✅ Job types available\n";
}

if ($categories->isEmpty()) {
    echo "   ❌ No categories available - this will cause validation errors\n";
} else {
    echo "   ✅ Categories available\n";
}

echo "\n🧪 SAMPLE VALID FORM DATA:\n";
echo "This is what a valid form submission should look like:\n\n";

$sampleData = [
    'title' => 'Software Developer',
    'description' => 'We are looking for a skilled software developer to join our team...',
    'requirements' => 'Bachelor degree in Computer Science, 2+ years experience...',
    'benefits' => 'Health insurance, flexible hours, remote work options...',
    'location' => 'Poblacion, Sta. Cruz, Davao del Sur',
    'latitude' => '6.8340',
    'longitude' => '125.4154',
    'job_type_id' => $jobTypes->first()->id ?? 1,
    'category_id' => $categories->first()->id ?? 1,
    'vacancy' => 1,
    'experience_level' => 'entry',
    'education_level' => 'bachelor',
    'salary_min' => 25000,
    'salary_max' => 35000,
    'deadline' => date('Y-m-d', strtotime('+30 days')),
    'is_remote' => false,
    'is_featured' => false,
    'skills' => '["PHP", "Laravel", "JavaScript"]'
];

foreach ($sampleData as $field => $value) {
    echo "   {$field}: {$value}\n";
}

echo "\n🚨 TROUBLESHOOTING STEPS:\n";
echo "1. Open browser developer tools (F12)\n";
echo "2. Go to Console tab to check for JavaScript errors\n";
echo "3. Go to Network tab to see form submission requests\n";
echo "4. Fill out the form and click Submit\n";
echo "5. Check if the form data is being sent correctly\n";
echo "6. Look for any red validation errors on the form\n";
echo "7. Check Laravel logs at storage/logs/laravel.log\n\n";

echo "🔧 QUICK FIXES TO TRY:\n";
echo "1. Clear browser cache and cookies\n";
echo "2. Disable browser extensions\n";
echo "3. Try in incognito/private mode\n";
echo "4. Check that all required fields are filled\n";
echo "5. Ensure 'description' and 'requirements' fields have content\n";
echo "6. Make sure job type and category are selected\n\n";

echo "📋 CHECKLIST FOR DEBUGGING:\n";
echo "□ All required fields are filled\n";
echo "□ No JavaScript errors in browser console\n";
echo "□ Form submission shows in Network tab\n";
echo "□ Server returns proper response (not error)\n";
echo "□ User has proper permissions (KYC + documents approved)\n";
echo "□ Laravel logs don't show errors\n\n";

echo "If the issue persists, check the Laravel logs and browser console for specific error messages.\n";
