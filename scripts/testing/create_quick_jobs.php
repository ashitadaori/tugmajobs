&lt;?php

require_once 'vendor/autoload.php';

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;

// Create a few test jobs with correct enum values
$employer = User::where('role', 'employer')->first();
$category = Category::where('name', 'Information Technology (IT) / Software Development')->first();
$jobType = JobType::where('name', 'Full Time')->first();

if (!$employer || !$category || !$jobType) {
    echo "Missing required data (employer, category, or job type)\n";
    exit;
}

$testJobs = [
    [
        'title' => 'PHP Developer - Test',
        'description' => 'Develop web applications using PHP and Laravel framework.',
        'requirements' => 'PHP, Laravel, MySQL, 2+ years experience',
        'category_id' => $category->id,
        'job_type_id' => $jobType->id,
        'location' => 'Manila, Philippines',
        'experience_level' => 'intermediate',
        'employer_id' => $employer->id,
        'status' => 1,
    ],
    [
        'title' => 'Junior Web Developer - Test',
        'description' => 'Learn and contribute to web development projects.',
        'requirements' => 'HTML, CSS, JavaScript, Fresh graduate acceptable',
        'category_id' => $category->id,
        'job_type_id' => $jobType->id,
        'location' => 'Cebu, Philippines',
        'experience_level' => 'entry',
        'employer_id' => $employer->id,
        'status' => 1,
    ],
    [
        'title' => 'Senior Full Stack Developer - Test',
        'description' => 'Lead complex web development projects.',
        'requirements' => 'PHP, Laravel, React, MySQL, 5+ years experience',
        'category_id' => $category->id,
        'job_type_id' => $jobType->id,
        'location' => 'BGC, Taguig',
        'experience_level' => 'expert',
        'employer_id' => $employer->id,
        'status' => 1,
    ]
];

foreach ($testJobs as $jobData) {
    try {
        Job::create($jobData);
        echo "✓ Created: {$jobData['title']}\n";
    } catch (Exception $e) {
        echo "❌ Failed to create '{$jobData['title']}': " . $e->getMessage() . "\n";
    }
}
