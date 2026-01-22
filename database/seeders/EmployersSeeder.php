<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employer;
use App\Models\Job;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->command->info('Creating 50 employers with jobs...');

        // Local companies in Santa Cruz, Davao del Sur and nearby areas
        $companyNames = [
            'Santa Cruz Municipal Office', 'Dole Philippines Inc', 'Del Monte Fresh Produce',
            'Lapanday Foods Corporation', 'Anflo Management Corp', 'DASSURECO',
            'Santa Cruz Rural Bank', 'Davao del Sur Provincial Hospital', 'Santa Cruz District Hospital',
            'Holy Cross of Davao College', 'Cor Jesu College Digos', 'UM Digos College',
            'Gaisano Grand Santa Cruz', 'NCCC Supermarket Digos', 'Fitmart Supermarket',
            'Jollibee Santa Cruz', 'Chowking Digos', 'Greenwich Digos',
            'BDO Santa Cruz Branch', 'Metrobank Digos', 'Land Bank Digos',
            'PNB Santa Cruz', 'Davao del Sur Electric Cooperative', 'Globe Store Digos',
            'Smart Communications Digos', 'Mercury Drug Santa Cruz', 'Watsons Digos',
            'Robinson\'s Supermarket Digos', 'Puregold Digos', 'SaveMore Market Santa Cruz',
            'Toyota Digos', 'Honda Digos', 'Yamaha Motors Santa Cruz',
            'Penshoppe Digos', 'Bench Digos', 'SM Savemore Santa Cruz',
            'Red Ribbon Bakeshop', 'Goldilocks Digos', 'Julie\'s Bakeshop Santa Cruz',
            'Mang Inasal Santa Cruz', 'Max\'s Restaurant Digos', 'Shakey\'s Digos',
            'Petron Santa Cruz', 'Shell Digos', 'Caltex Santa Cruz',
            'PAG-IBIG Fund Digos', 'SSS Digos Branch', 'PhilHealth Digos',
            'DepEd Division of Davao del Sur', 'TESDA Davao del Sur'
        ];

        $industries = [
            'Agriculture & Farming', 'Healthcare', 'Retail & Hospitality',
            'Banking & Finance', 'Education', 'Food & Beverage',
            'Telecommunications', 'Government Services', 'Manufacturing',
            'Transportation & Logistics', 'Construction', 'Professional Services'
        ];

        // Barangays in Santa Cruz, Davao del Sur with corrected coordinates
        // Santa Cruz town center is approximately at 6.8370, 125.4130
        $barangays = [
            'Poblacion' => ['lat' => 6.8375, 'lng' => 125.4125],
            'Zone I' => ['lat' => 6.8390, 'lng' => 125.4110],
            'Zone II' => ['lat' => 6.8365, 'lng' => 125.4140],
            'Zone III' => ['lat' => 6.8350, 'lng' => 125.4120],
            'Zone IV' => ['lat' => 6.8380, 'lng' => 125.4150],
            'Astorga' => ['lat' => 6.8520, 'lng' => 125.3980],
            'Bato' => ['lat' => 6.8150, 'lng' => 125.4050],
            'Coronon' => ['lat' => 6.8450, 'lng' => 125.3900],
            'Darong' => ['lat' => 6.8600, 'lng' => 125.4200],
            'Inawayan' => ['lat' => 6.7950, 'lng' => 125.4300],
            'Jose Rizal' => ['lat' => 6.8100, 'lng' => 125.4180],
            'Matutungan' => ['lat' => 6.8250, 'lng' => 125.3950],
            'Melilia' => ['lat' => 6.8550, 'lng' => 125.4050],
            'Saliducon' => ['lat' => 6.8650, 'lng' => 125.4100],
            'Sibulan' => ['lat' => 6.8000, 'lng' => 125.4150],
            'Sinoron' => ['lat' => 6.7900, 'lng' => 125.4200],
            'Tagabuli' => ['lat' => 6.8700, 'lng' => 125.4000],
            'Tibolo' => ['lat' => 6.8200, 'lng' => 125.4250],
            'Tuban' => ['lat' => 6.7850, 'lng' => 125.4100],
            'Santa Cruz Proper' => ['lat' => 6.8370, 'lng' => 125.4130],
        ];

        $companySizes = [
            '1-10' => 'startup',
            '11-50' => 'small',
            '51-200' => 'medium',
            '201-500' => 'large',
            '500+' => 'enterprise'
        ];

        for ($i = 1; $i <= 50; $i++) {
            $companyName = $companyNames[$i - 1];
            $companySlug = Str::slug($companyName) . '-' . $i;
            $industry = $industries[array_rand($industries)];
            $sizeKey = array_rand($companySizes);
            $barangayName = array_rand($barangays);
            $barangayCoords = $barangays[$barangayName];

            // Determine number of jobs based on company size
            $jobsToPost = [
                'startup' => rand(1, 3),
                'small' => rand(2, 5),
                'medium' => rand(4, 8),
                'large' => rand(6, 12),
                'enterprise' => rand(10, 15)
            ][$companySizes[$sizeKey]];

            // Create user account with unique email
            $email = strtolower(str_replace(' ', '', $companySlug)) . $i . '@company.com';
            $user = User::create([
                'name' => $companyName,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'employer',
                'email_verified_at' => now(),
                'is_active' => true,
                'remember_token' => Str::random(10),
            ]);

            // Create employer profile
            $employer = Employer::create([
                'user_id' => $user->id,
                'company_name' => $companyName,
                'company_slug' => $companySlug,
                'company_description' => $this->generateCompanyDescription($companyName, $industry),
                'company_website' => 'https://www.' . $companySlug . '.com',
                'industry' => $industry,
                'founded_year' => rand(1990, 2020),
                'company_size' => $sizeKey,

                // Business details
                'business_registration_number' => 'BRN-' . rand(100000, 999999),
                'tax_identification_number' => 'TIN-' . rand(100000000, 999999999),
                'business_address' => 'Brgy. ' . $barangayName . ', Santa Cruz, Davao del Sur',
                'city' => 'Santa Cruz, Davao del Sur',
                'country' => 'Philippines',
                'postal_code' => '8001',

                // Contact info
                'contact_person_name' => $this->getRandomContactName(),
                'contact_person_designation' => 'HR Manager',
                'business_email' => 'hr@' . $companySlug . '.com',
                'business_phone' => '082-' . rand(100, 999) . '-' . rand(1000, 9999),

                // Verification & status
                'is_verified' => rand(0, 100) > 20, // 80% verified
                'verified_at' => rand(0, 100) > 20 ? now()->subDays(rand(1, 365)) : null,
                'is_featured' => rand(0, 100) > 85, // 15% featured
                'status' => 'active',

                // Subscription
                'subscription_plan' => ['free', 'basic', 'premium', 'enterprise'][rand(0, 3)],
                'subscription_starts_at' => now()->subMonths(rand(0, 12)),
                'subscription_ends_at' => now()->addMonths(rand(1, 12)),
                'job_posts_limit' => [5, 10, 50, 999][$companySizes[$sizeKey] == 'enterprise' ? 3 : rand(0, 2)],
                'job_posts_used' => 0,

                // Social media
                'linkedin_url' => 'https://linkedin.com/company/' . $companySlug,
                'facebook_url' => 'https://facebook.com/' . $companySlug,
                'twitter_url' => rand(0, 1) ? 'https://twitter.com/' . $companySlug : null,

                // Company culture
                'company_culture' => $this->getCompanyCulture(),
                'benefits_offered' => $this->getBenefits(),
                'specialties' => $this->getSpecialties($industry),

                // Meta
                'meta_title' => $companyName . ' - Careers',
                'meta_description' => 'Join ' . $companyName . ' and build your career in ' . $industry,

                // Stats
                'total_jobs_posted' => $jobsToPost,
                'active_jobs' => $jobsToPost,
                'profile_views' => rand(50, 500),
                'average_rating' => round(rand(35, 50) / 10, 1),

                // Settings
                'notification_preferences' => ['email', 'application_alerts'],
                'settings' => ['auto_reply' => true, 'show_salary' => rand(0, 1)],
            ]);

            $this->command->info("Created employer: {$companyName} ({$sizeKey}, {$industry})");

            // Create jobs for this employer
            $this->createJobsForEmployer($employer, $user, $jobsToPost, $barangayName, $barangayCoords, $barangays);

            // Update job posts used
            $employer->update(['job_posts_used' => $jobsToPost]);
        }

        $this->command->info('Successfully created 50 employers with jobs!');
    }

    private function createJobsForEmployer($employer, $user, $numJobs, $barangayName, $barangayCoords, $allBarangays)
    {
        // Job templates by category with Philippine Peso salaries
        // Category IDs: 1=IT, 3=Sales/Marketing, 4=Accounting/Finance, 5=HR/Admin, 6=Engineering, 7=Healthcare, 9=Manufacturing, 12=Construction, 13=Transportation, 15=Agriculture
        $jobTemplates = [
            1 => [ // IT/Technology
                ['title' => 'Senior Software Developer', 'exp' => '5-7 years', 'skills' => ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Git'], 'salary' => [40000, 60000]],
                ['title' => 'Full Stack Developer', 'exp' => '3-5 years', 'skills' => ['React', 'Node.js', 'MongoDB', 'Express', 'TypeScript'], 'salary' => [35000, 50000]],
                ['title' => 'Frontend Developer', 'exp' => '2-4 years', 'skills' => ['React', 'Vue.js', 'HTML', 'CSS', 'JavaScript'], 'salary' => [25000, 40000]],
                ['title' => 'Backend Developer', 'exp' => '3-5 years', 'skills' => ['Python', 'Django', 'PostgreSQL', 'REST API', 'Docker'], 'salary' => [30000, 45000]],
                ['title' => 'IT Support Specialist', 'exp' => '1-3 years', 'skills' => ['Windows', 'Linux', 'Networking', 'Troubleshooting'], 'salary' => [18000, 28000]],
                ['title' => 'System Administrator', 'exp' => '3-5 years', 'skills' => ['Linux', 'Windows Server', 'Networking', 'Security'], 'salary' => [30000, 45000]],
                ['title' => 'Data Entry Clerk', 'exp' => '0-2 years', 'skills' => ['MS Office', 'Typing', 'Data Entry', 'Excel'], 'salary' => [15000, 20000]],
                ['title' => 'Computer Technician', 'exp' => '1-3 years', 'skills' => ['Hardware Repair', 'Software Installation', 'Networking'], 'salary' => [15000, 22000]],
            ],
            3 => [ // Sales/Marketing
                ['title' => 'Digital Marketing Specialist', 'exp' => '2-4 years', 'skills' => ['SEO', 'SEM', 'Google Analytics', 'Social Media'], 'salary' => [25000, 40000]],
                ['title' => 'Social Media Manager', 'exp' => '2-4 years', 'skills' => ['Instagram', 'Facebook', 'TikTok', 'Content Creation'], 'salary' => [20000, 35000]],
                ['title' => 'Marketing Assistant', 'exp' => '0-2 years', 'skills' => ['Marketing', 'Social Media', 'Content Writing'], 'salary' => [15000, 22000]],
                ['title' => 'Sales Representative', 'exp' => '1-3 years', 'skills' => ['Sales', 'Customer Service', 'Communication'], 'salary' => [18000, 30000]],
                ['title' => 'Brand Ambassador', 'exp' => '0-2 years', 'skills' => ['Communication', 'Sales', 'Customer Engagement'], 'salary' => [15000, 25000]],
            ],
            7 => [ // Healthcare/Medical
                ['title' => 'Registered Nurse', 'exp' => '2-4 years', 'skills' => ['Patient Care', 'Vital Signs', 'EMR', 'Medical Terminology'], 'salary' => [22000, 35000]],
                ['title' => 'Medical Technologist', 'exp' => '2-4 years', 'skills' => ['Laboratory Testing', 'Quality Control', 'Medical Equipment'], 'salary' => [20000, 32000]],
                ['title' => 'Pharmacist', 'exp' => '3-5 years', 'skills' => ['Drug Dispensing', 'Patient Counseling', 'Inventory Management'], 'salary' => [28000, 45000]],
                ['title' => 'Nursing Aide', 'exp' => '0-2 years', 'skills' => ['Patient Care', 'Basic Nursing', 'Vital Signs'], 'salary' => [12000, 18000]],
                ['title' => 'Midwife', 'exp' => '2-4 years', 'skills' => ['Maternal Care', 'Delivery Assistance', 'Prenatal Care'], 'salary' => [18000, 28000]],
            ],
            6 => [ // Engineering/Technical
                ['title' => 'Civil Engineer', 'exp' => '3-5 years', 'skills' => ['AutoCAD', 'Structural Design', 'Project Management', 'Construction'], 'salary' => [30000, 50000]],
                ['title' => 'Mechanical Engineer', 'exp' => '3-5 years', 'skills' => ['SolidWorks', 'CAD', 'Machine Design', 'Manufacturing'], 'salary' => [28000, 45000]],
                ['title' => 'Electrical Engineer', 'exp' => '3-5 years', 'skills' => ['Circuit Design', 'PLC Programming', 'Electrical Systems'], 'salary' => [28000, 45000]],
                ['title' => 'Construction Foreman', 'exp' => '5-7 years', 'skills' => ['Construction', 'Team Management', 'Safety'], 'salary' => [25000, 40000]],
                ['title' => 'Draftsman', 'exp' => '2-4 years', 'skills' => ['AutoCAD', 'Technical Drawing', 'Blueprint Reading'], 'salary' => [18000, 28000]],
            ],
            4 => [ // Accounting/Finance
                ['title' => 'Accountant', 'exp' => '3-5 years', 'skills' => ['QuickBooks', 'Financial Reporting', 'Tax Preparation', 'Excel'], 'salary' => [25000, 40000]],
                ['title' => 'Bookkeeper', 'exp' => '2-4 years', 'skills' => ['Bookkeeping', 'Excel', 'Accounting Software'], 'salary' => [18000, 28000]],
                ['title' => 'Cashier', 'exp' => '0-2 years', 'skills' => ['Cash Handling', 'Customer Service', 'POS Systems'], 'salary' => [12000, 18000]],
            ],
            5 => [ // Human Resources/Admin
                ['title' => 'HR Officer', 'exp' => '2-4 years', 'skills' => ['Recruitment', 'Employee Relations', 'HRIS', 'Labor Law'], 'salary' => [22000, 35000]],
                ['title' => 'Administrative Assistant', 'exp' => '1-3 years', 'skills' => ['MS Office', 'Communication', 'Organization', 'Data Entry'], 'salary' => [15000, 22000]],
                ['title' => 'Encoder', 'exp' => '0-2 years', 'skills' => ['Typing', 'Data Entry', 'MS Office'], 'salary' => [12000, 18000]],
                ['title' => 'Receptionist', 'exp' => '0-2 years', 'skills' => ['Communication', 'MS Office', 'Phone Etiquette'], 'salary' => [12000, 18000]],
            ],
            9 => [ // Manufacturing/Production
                ['title' => 'Production Supervisor', 'exp' => '3-5 years', 'skills' => ['Production Planning', 'Quality Control', 'Team Management'], 'salary' => [25000, 40000]],
                ['title' => 'Machine Operator', 'exp' => '1-3 years', 'skills' => ['Machine Operation', 'Maintenance', 'Safety'], 'salary' => [15000, 25000]],
                ['title' => 'Quality Inspector', 'exp' => '2-4 years', 'skills' => ['Quality Control', 'Inspection', 'Documentation'], 'salary' => [18000, 28000]],
            ],
            13 => [ // Transportation/Logistics
                ['title' => 'Driver', 'exp' => '2-4 years', 'skills' => ['Driving', 'Vehicle Maintenance', 'Route Navigation'], 'salary' => [15000, 25000]],
                ['title' => 'Warehouse Staff', 'exp' => '1-3 years', 'skills' => ['Inventory Management', 'Forklift Operation', 'Organization'], 'salary' => [15000, 22000]],
                ['title' => 'Delivery Rider', 'exp' => '0-2 years', 'skills' => ['Driving', 'Customer Service', 'Navigation'], 'salary' => [12000, 20000]],
            ],
            15 => [ // Agriculture/Farming
                ['title' => 'Farm Worker', 'exp' => '1-3 years', 'skills' => ['Farming', 'Harvesting', 'Equipment Operation'], 'salary' => [12000, 18000]],
                ['title' => 'Farm Supervisor', 'exp' => '3-5 years', 'skills' => ['Farm Management', 'Team Supervision', 'Crop Planning'], 'salary' => [20000, 30000]],
                ['title' => 'Agricultural Technician', 'exp' => '2-4 years', 'skills' => ['Crop Management', 'Pest Control', 'Irrigation'], 'salary' => [18000, 28000]],
            ],
            12 => [ // Construction/Architecture
                ['title' => 'Electrician', 'exp' => '2-4 years', 'skills' => ['Electrical Installation', 'Wiring', 'Troubleshooting'], 'salary' => [18000, 30000]],
                ['title' => 'Plumber', 'exp' => '2-4 years', 'skills' => ['Plumbing Installation', 'Pipe Fitting', 'Repairs'], 'salary' => [18000, 28000]],
                ['title' => 'Welder', 'exp' => '2-4 years', 'skills' => ['Welding', 'Metal Fabrication', 'Blueprint Reading'], 'salary' => [18000, 30000]],
                ['title' => 'Security Guard', 'exp' => '1-3 years', 'skills' => ['Security', 'Surveillance', 'Emergency Response'], 'salary' => [12000, 18000]],
                ['title' => 'Mason', 'exp' => '2-4 years', 'skills' => ['Masonry', 'Concrete Work', 'Blueprint Reading'], 'salary' => [18000, 28000]],
            ],
        ];

        $categories = array_keys($jobTemplates);
        $jobTypes = [1, 2, 3]; // Full-time, Part-time, Contract

        for ($j = 0; $j < $numJobs; $j++) {
            $categoryId = $categories[array_rand($categories)];
            $template = $jobTemplates[$categoryId][array_rand($jobTemplates[$categoryId])];

            $daysOld = rand(0, 60);
            $postedAt = now()->subDays($daysOld);

            // Randomly use the employer's barangay or a nearby one
            $jobBarangayName = (rand(0, 100) > 30) ? $barangayName : array_rand($allBarangays);
            $jobBarangayCoords = $allBarangays[$jobBarangayName];

            // Add slight variation to coordinates for visual spread on map
            $lat = $jobBarangayCoords['lat'] + (rand(-50, 50) / 10000);
            $lng = $jobBarangayCoords['lng'] + (rand(-50, 50) / 10000);

            $location = 'Brgy. ' . $jobBarangayName . ', Santa Cruz, Davao del Sur';

            // 30% chance to have no salary indicated (negotiable)
            $showSalary = rand(1, 100) > 30;
            $salaryMin = $showSalary ? $template['salary'][0] : null;
            $salaryMax = $showSalary ? $template['salary'][1] : null;

            Job::create([
                'employer_id' => $user->id,
                'category_id' => $categoryId,
                'job_type_id' => $jobTypes[array_rand($jobTypes)],
                'company_id' => null,
                'company_name' => $employer->company_name,
                'company_website' => $employer->company_website,

                // Job details
                'title' => $template['title'],
                'description' => $this->generateJobDescription($template['title'], $template['skills']),
                'requirements' => $this->generateRequirements($template['exp'], $template['skills']),
                'qualifications' => $this->generateQualifications($template['exp']),
                'benefits' => implode("\n• ", ['', ...$this->getBenefits()]),

                // Salary in Philippine Peso (some jobs have no salary - negotiable)
                // salary_range is computed dynamically via accessor in Job model
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,

                // Location in Santa Cruz, Davao del Sur with coordinates
                'location' => $location,
                'location_name' => 'Santa Cruz, Davao del Sur',
                'location_address' => $location,
                'address' => 'Santa Cruz, Davao del Sur, Philippines',
                'city' => 'Santa Cruz',
                'barangay' => $jobBarangayName,
                'latitude' => $lat,
                'longitude' => $lng,

                // Experience
                'experience_level' => $this->extractExpLevel($template['exp']),
                'vacancy' => rand(1, 5),

                // Status
                'status' => 1, // Approved
                'approved_at' => $postedAt,
                'featured' => rand(0, 100) > 90,

                // Dates
                'deadline' => now()->addDays(rand(15, 60)),
                'created_at' => $postedAt,
                'updated_at' => $postedAt,
            ]);

            $salaryInfo = $showSalary ? "₱" . number_format($salaryMin) . "-₱" . number_format($salaryMax) : "Negotiable";
            $this->command->info("  -> Posted: {$template['title']} ({$salaryInfo})");
        }
    }

    // Helper methods
    private function generateCompanyDescription($name, $industry)
    {
        return "{$name} is a reputable establishment in Santa Cruz, Davao del Sur serving the {$industry} sector. We are committed to providing excellent services to our community while fostering a positive work environment for our employees. Join our team and be part of our growing family.";
    }

    private function generateJobDescription($title, $skills)
    {
        return "We are looking for a talented {$title} to join our team in Santa Cruz, Davao del Sur. The ideal candidate should have strong expertise in " . implode(', ', array_slice($skills, 0, 3)) . " and a passion for delivering high-quality work. You will work on exciting projects and collaborate with our dedicated team to serve our local community.";
    }

    private function generateRequirements($exp, $skills)
    {
        return "• {$exp} of relevant experience\n• Proficiency in: " . implode(', ', $skills) . "\n• Strong problem-solving skills\n• Excellent communication abilities\n• Ability to work independently and in a team\n• Must be willing to work in Santa Cruz, Davao del Sur";
    }

    private function generateQualifications($exp)
    {
        return "• {$exp} of professional experience\n• Bachelor's degree or relevant vocational training\n• Strong analytical and technical skills\n• Excellent communication and interpersonal skills\n• Ability to manage multiple priorities";
    }

    private function getRandomContactName()
    {
        $names = [
            'Maria Santos', 'Juan Dela Cruz', 'Ana Reyes', 'Carlos Garcia',
            'Sofia Martinez', 'Miguel Torres', 'Carmen Lopez', 'Jose Hernandez',
            'Elena Bautista', 'Roberto Mendoza', 'Patricia Flores', 'Fernando Cruz'
        ];
        return $names[array_rand($names)];
    }

    private function getCompanyCulture()
    {
        return [
            'Work-life balance',
            'Family-oriented',
            'Collaborative environment',
            'Professional growth',
            'Community-focused'
        ];
    }

    private function getBenefits()
    {
        $allBenefits = [
            'SSS Contribution',
            'PhilHealth',
            'Pag-IBIG Fund',
            '13th Month Pay',
            'Paid Time Off',
            'Rice Allowance',
            'Transportation Allowance',
            'Uniform Allowance',
            'Performance Bonus',
            'Health Insurance',
            'Training & Development',
            'Employee Discounts'
        ];

        return array_slice($allBenefits, 0, rand(6, 10));
    }

    private function getSpecialties($industry)
    {
        $specialties = [
            'Agriculture & Farming' => ['Crop Production', 'Livestock', 'Agricultural Services'],
            'Healthcare' => ['Patient Care', 'Medical Services', 'Healthcare Management'],
            'Retail & Hospitality' => ['Retail Sales', 'Customer Service', 'Inventory Management'],
            'Banking & Finance' => ['Financial Services', 'Lending', 'Accounting'],
            'Education' => ['Teaching', 'Academic Support', 'Training'],
            'Food & Beverage' => ['Food Service', 'Restaurant Operations', 'Catering'],
        ];

        return $specialties[$industry] ?? ['Business Operations', 'Customer Service', 'Management'];
    }

    private function extractExpLevel($expString)
    {
        if (strpos($expString, '0-') !== false || strpos($expString, '1-') !== false) return 'entry';
        if (strpos($expString, '2-') !== false || strpos($expString, '3-') !== false) return 'entry';
        if (strpos($expString, '4-') !== false || strpos($expString, '5-') !== false) return 'intermediate';
        return 'expert';
    }
}
