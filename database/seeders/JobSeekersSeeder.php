<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Jobseeker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JobSeekersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->command->info('Creating 50 job seekers...');

        // Filipino names common in Davao del Sur
        $firstNames = [
            'Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Carmen', 'Luis', 'Rosa',
            'Miguel', 'Elena', 'Carlos', 'Sofia', 'Ramon', 'Isabel', 'Fernando',
            'Patricia', 'Roberto', 'Teresa', 'Antonio', 'Angela', 'Manuel', 'Gloria',
            'Francisco', 'Diana', 'Ricardo', 'Lucia', 'Alejandro', 'Monica', 'Jorge',
            'Cristina', 'Sergio', 'Beatriz', 'Daniel', 'Margarita', 'Andres', 'Pilar',
            'Diego', 'Victoria', 'Rafael', 'Gabriela', 'Pablo', 'Adriana', 'Javier',
            'Natalia', 'Oscar', 'Camila', 'Eduardo', 'Valentina', 'Raul', 'Daniela'
        ];

        $lastNames = [
            'Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Ramos', 'Aquino', 'Mendoza',
            'Torres', 'Lopez', 'Gonzales', 'Fernandez', 'Martinez', 'Castillo', 'Rivera',
            'Bautista', 'Santiago', 'Villanueva', 'Castro', 'Flores', 'Cruz',
            'Hernandez', 'Morales', 'Gomez', 'Jimenez', 'Alvarez', 'Romero', 'Perez',
            'Sanchez', 'Ramirez', 'Diaz', 'Rodriguez', 'Vargas', 'Ortiz', 'Silva',
            'Aguilar', 'Domingo', 'Medina', 'Valencia', 'Salazar', 'Navarro',
            'Velasco', 'Pascual', 'Delos Santos', 'San Jose', 'Del Rosario'
        ];

        // Comprehensive skills by category
        $skillsByCategory = [
            'IT/Technology' => [
                ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Git', 'HTML', 'CSS', 'REST API'],
                ['Python', 'Django', 'PostgreSQL', 'Docker', 'AWS', 'Linux', 'Redis'],
                ['Java', 'Spring Boot', 'Hibernate', 'Maven', 'JUnit', 'Microservices'],
                ['JavaScript', 'React', 'Node.js', 'MongoDB', 'Express', 'TypeScript'],
                ['C#', '.NET Core', 'ASP.NET', 'SQL Server', 'Azure', 'Entity Framework'],
                ['Vue.js', 'Vuex', 'Nuxt.js', 'Tailwind CSS', 'Firebase', 'GraphQL'],
                ['Angular', 'TypeScript', 'RxJS', 'NgRx', 'Material Design'],
                ['Flutter', 'Dart', 'Firebase', 'REST API', 'SQLite', 'Provider'],
                ['React Native', 'Redux', 'Expo', 'AsyncStorage', 'Navigation'],
                ['DevOps', 'Kubernetes', 'Jenkins', 'Terraform', 'Ansible', 'CI/CD']
            ],
            'Marketing' => [
                ['Digital Marketing', 'SEO', 'SEM', 'Google Analytics', 'Facebook Ads'],
                ['Content Marketing', 'Copywriting', 'Social Media', 'Email Marketing'],
                ['Brand Management', 'Market Research', 'Campaign Strategy', 'Analytics'],
                ['Influencer Marketing', 'Instagram', 'TikTok', 'Video Marketing'],
                ['Marketing Automation', 'HubSpot', 'Mailchimp', 'Lead Generation']
            ],
            'Healthcare' => [
                ['Patient Care', 'Medical Terminology', 'EMR Systems', 'HIPAA Compliance'],
                ['Nursing', 'Vital Signs', 'Medication Administration', 'Patient Assessment'],
                ['Laboratory', 'Medical Testing', 'Quality Control', 'Lab Equipment'],
                ['Radiology', 'X-Ray', 'CT Scan', 'MRI', 'Image Analysis'],
                ['Pharmacy', 'Drug Dispensing', 'Medication Safety', 'Inventory Management']
            ],
            'Finance' => [
                ['Accounting', 'QuickBooks', 'Excel', 'Financial Reporting', 'Tax Preparation'],
                ['Financial Analysis', 'Forecasting', 'Budgeting', 'Financial Modeling'],
                ['Auditing', 'Internal Controls', 'Risk Assessment', 'Compliance'],
                ['Banking', 'Loan Processing', 'Credit Analysis', 'Customer Service'],
                ['Investment', 'Portfolio Management', 'Market Analysis', 'Trading']
            ],
            'Engineering' => [
                ['CAD', 'AutoCAD', 'SolidWorks', 'Technical Drawing', 'Blueprint Reading'],
                ['Project Management', 'Construction', 'Site Planning', 'Safety Compliance'],
                ['Electrical Engineering', 'Circuit Design', 'PLC Programming', 'Troubleshooting'],
                ['Mechanical Engineering', 'Machine Design', 'Thermodynamics', 'Materials Science'],
                ['Civil Engineering', 'Structural Analysis', 'Surveying', 'Construction Management']
            ]
        ];

        $categories = [61, 63, 64, 66, 68, 72]; // IT, Marketing, Healthcare, Engineering, Admin, Trade
        $jobTypes = [1, 2, 3]; // Full-time, Part-time, Contract

        // Barangays in Santa Cruz, Davao del Sur
        $barangays = [
            'Poblacion', 'Zone I', 'Zone II', 'Zone III', 'Zone IV',
            'Astorga', 'Bato', 'Coronon', 'Darong', 'Inawayan',
            'Jose Rizal', 'Matutungan', 'Melilia', 'Saliducon', 'Sibulan',
            'Sinoron', 'Tagabuli', 'Tibolo', 'Tuban', 'Santa Cruz Proper'
        ];

        for ($i = 1; $i <= 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $email = strtolower($firstName . '.' . str_replace(' ', '', $lastName) . $i . '@example.com');

            // Determine career level
            $experience = rand(0, 15);
            if ($experience <= 2) {
                $experienceLevel = 'entry';
                $salaryMin = rand(12000, 18000);
                $salaryMax = rand(18000, 25000);
            } elseif ($experience <= 5) {
                $experienceLevel = 'junior';
                $salaryMin = rand(18000, 25000);
                $salaryMax = rand(25000, 35000);
            } elseif ($experience <= 10) {
                $experienceLevel = 'mid';
                $salaryMin = rand(25000, 40000);
                $salaryMax = rand(40000, 55000);
            } else {
                $experienceLevel = 'senior';
                $salaryMin = rand(40000, 60000);
                $salaryMax = rand(60000, 80000);
            }

            // Create user account
            $user = User::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'jobseeker',
                'email_verified_at' => now(),
                'is_active' => true,
                'remember_token' => Str::random(10),
            ]);

            // Select random skills based on preferred category
            $preferredCategoryIndex = array_rand($categories);
            $preferredCategory = $categories[$preferredCategoryIndex];

            $skillsArray = $skillsByCategory['IT/Technology']; // Default
            if ($preferredCategory == 63) $skillsArray = $skillsByCategory['Marketing'];
            elseif ($preferredCategory == 64) $skillsArray = $skillsByCategory['Healthcare'];
            elseif ($preferredCategory == 66) $skillsArray = $skillsByCategory['Engineering'];
            elseif ($preferredCategory == 68) $skillsArray = $skillsByCategory['Finance'];

            $skills = $skillsArray[array_rand($skillsArray)];

            // Work experience based on years
            $workExperience = [];
            $numJobs = min(ceil($experience / 2), 5);

            for ($j = 0; $j < $numJobs; $j++) {
                $startYear = date('Y') - $experience + ($j * 2);
                $endYear = $j == 0 ? 'Present' : $startYear + rand(1, 3);

                $workExperience[] = [
                    'company' => $this->getRandomCompany(),
                    'position' => $this->getRandomPosition($experienceLevel, $preferredCategory),
                    'start_date' => $startYear . '-01',
                    'end_date' => $endYear,
                    'description' => 'Responsible for ' . implode(', ', array_slice($skills, 0, 3))
                ];
            }

            // Education
            $education = [];
            $degrees = ['Bachelor of Science', 'Bachelor of Arts', 'Associate Degree', 'Master of Science'];
            $majors = [
                61 => ['Computer Science', 'Information Technology', 'Software Engineering'],
                63 => ['Marketing', 'Business Administration', 'Communication'],
                64 => ['Nursing', 'Medicine', 'Health Sciences'],
                66 => ['Engineering', 'Mechanical Engineering', 'Civil Engineering'],
                68 => ['Accounting', 'Finance', 'Business Administration']
            ];

            $major = $majors[$preferredCategory] ?? ['Business Administration'];

            $education[] = [
                'school' => $this->getRandomUniversity(),
                'degree' => $degrees[array_rand($degrees)],
                'field' => $major[array_rand($major)],
                'start_year' => date('Y') - $experience - 4,
                'end_year' => date('Y') - $experience,
                'gpa' => round(rand(250, 400) / 100, 2)
            ];

            $barangay = $barangays[array_rand($barangays)];

            // Create job seeker profile
            Jobseeker::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => date('Y-m-d', strtotime('-' . rand(22, 45) . ' years')),
                'gender' => rand(0, 1) ? 'male' : 'female',
                'phone' => '09' . rand(100000000, 999999999),
                'current_address' => 'Brgy. ' . $barangay . ', Santa Cruz, Davao del Sur',
                'city' => 'Santa Cruz',
                'country' => 'Philippines',
                'postal_code' => '8001',

                // Professional info
                'current_job_title' => $workExperience[0]['position'] ?? null,
                'current_company' => $workExperience[0]['company'] ?? null,
                'professional_summary' => $this->generateProfessionalSummary($experience, $skills),
                'total_experience_years' => $experience,
                'total_experience_months' => $experience * 12 + rand(0, 11),
                'experience_level' => $experienceLevel,

                // Skills and qualifications
                'skills' => $skills,
                'soft_skills' => ['Communication', 'Teamwork', 'Problem Solving', 'Time Management', 'Leadership'],
                'languages' => ['English', 'Filipino', 'Bisaya'],
                'certifications' => $this->getRandomCertifications($preferredCategory),
                'education' => $education,
                'work_experience' => $workExperience,

                // Job preferences
                'preferred_job_types' => array_slice($jobTypes, 0, rand(1, 2)),
                'preferred_categories' => [$preferredCategory],
                'preferred_locations' => ['Santa Cruz', 'Digos City', 'Davao City'],
                'open_to_remote' => rand(0, 1),
                'open_to_relocation' => rand(0, 1),

                // Salary expectations (Philippine Peso)
                'expected_salary_min' => $salaryMin,
                'expected_salary_max' => $salaryMax,
                'salary_currency' => 'PHP',
                'salary_period' => 'monthly',

                // Availability
                'availability' => ['immediate', '1_month', '2_months'][rand(0, 2)],
                'available_from' => now()->addDays(rand(0, 60)),
                'currently_employed' => $experience > 0 ? rand(0, 1) : 0,
                'notice_period_days' => rand(0, 1) ? 30 : 15,

                // Settings
                'notification_preferences' => ['email', 'sms'],
                'privacy_settings' => ['show_profile' => true, 'show_contact' => false],
                'job_alert_preferences' => ['frequency' => 'daily', 'categories' => [$preferredCategory]],
                'profile_visibility' => true,
                'allow_recruiter_contact' => true,

                // Status
                'profile_status' => 'complete',
                'is_featured' => rand(0, 100) > 90, // 10% featured
                'profile_completion_percentage' => rand(80, 100),

                // Stats
                'profile_views' => rand(0, 100),
                'total_applications' => rand(0, 20),
                'interviews_attended' => rand(0, 5),
                'jobs_offered' => rand(0, 2),
            ]);

            $this->command->info("Created: {$user->name} ({$experienceLevel}, {$experience} years exp)");
        }

        $this->command->info('Successfully created 50 job seekers!');
    }

    private function getRandomCompany()
    {
        // Local companies in Santa Cruz and nearby areas in Davao del Sur
        $companies = [
            'Santa Cruz Municipal Hall', 'Dole Philippines', 'Del Monte Philippines',
            'Lapanday Foods Corporation', 'Tadeco (Tagum Agricultural Development Company)',
            'Anflo Management & Investment Corp', 'Davao del Sur Electric Cooperative',
            'Santa Cruz Rural Bank', 'Davao Doctors Hospital', 'Southern Philippines Medical Center',
            'Holy Cross of Davao College', 'Cor Jesu College', 'University of Mindanao',
            'Gaisano Mall Digos', 'NCCC Mall Digos', 'Robinson\'s Place Digos',
            'Metro Retail Stores Group', 'Jollibee Santa Cruz', 'McDonald\'s Digos',
            'BDO Unibank Davao', 'Metrobank Digos', 'Land Bank of the Philippines',
            'Philippine National Bank', 'Davao Light & Power Co.', 'Globe Telecom Davao',
            'PLDT Davao', 'Davao Gulf Lumber Corporation', 'Alsons Development Corp'
        ];

        return $companies[array_rand($companies)];
    }

    private function getRandomUniversity()
    {
        // Universities in Davao and nearby areas
        $universities = [
            'University of the Philippines Mindanao', 'Ateneo de Davao University',
            'University of Mindanao', 'Holy Cross of Davao College', 'Cor Jesu College',
            'University of Southeastern Philippines', 'Davao Doctors College',
            'San Pedro College', 'Philippine Women\'s College of Davao',
            'Assumption College of Davao', 'John Paul II College of Davao',
            'Rizal Memorial Colleges', 'Davao Central College', 'STI College Davao',
            'AMA Computer University Davao', 'University of Immaculate Conception'
        ];

        return $universities[array_rand($universities)];
    }

    private function getRandomPosition($level, $category)
    {
        $positions = [
            61 => [ // IT
                'entry' => ['Junior Developer', 'IT Support', 'QA Tester', 'Junior Programmer'],
                'junior' => ['Software Developer', 'Web Developer', 'System Administrator', 'Database Administrator'],
                'mid' => ['Senior Developer', 'Full Stack Developer', 'DevOps Engineer', 'Solutions Architect'],
                'senior' => ['Lead Developer', 'Engineering Manager', 'Technical Architect', 'IT Director']
            ],
            63 => [ // Marketing
                'entry' => ['Marketing Assistant', 'Social Media Coordinator', 'Content Writer'],
                'junior' => ['Marketing Specialist', 'Digital Marketing Associate', 'SEO Specialist'],
                'mid' => ['Marketing Manager', 'Brand Manager', 'Digital Marketing Manager'],
                'senior' => ['Marketing Director', 'VP Marketing', 'Chief Marketing Officer']
            ],
            66 => [ // Engineering
                'entry' => ['Junior Engineer', 'Engineering Assistant', 'CAD Operator'],
                'junior' => ['Project Engineer', 'Design Engineer', 'Quality Engineer'],
                'mid' => ['Senior Engineer', 'Project Manager', 'Engineering Manager'],
                'senior' => ['Chief Engineer', 'Engineering Director', 'VP Engineering']
            ],
        ];

        $categoryPositions = $positions[$category] ?? $positions[61];
        return $categoryPositions[$level][array_rand($categoryPositions[$level])];
    }

    private function generateProfessionalSummary($experience, $skills)
    {
        $summaries = [
            "Experienced professional with {$experience} years of expertise in " . implode(', ', array_slice($skills, 0, 3)) . ". Proven track record of delivering high-quality results and driving business growth.",
            "Results-driven specialist with {$experience} years of hands-on experience. Skilled in " . implode(', ', array_slice($skills, 0, 3)) . ". Strong problem-solving abilities and excellent team collaboration skills.",
            "Dynamic professional with {$experience} years in the industry. Proficient in " . implode(', ', array_slice($skills, 0, 3)) . ". Passionate about continuous learning and innovation.",
            "Dedicated expert with {$experience} years of experience. Specializes in " . implode(', ', array_slice($skills, 0, 3)) . ". Known for delivering projects on time and exceeding expectations."
        ];

        return $summaries[array_rand($summaries)];
    }

    private function getRandomCertifications($category)
    {
        $certs = [
            61 => ['AWS Certified Developer', 'Oracle Certified Professional', 'Microsoft Certified', 'ITIL Foundation'],
            63 => ['Google Analytics Certified', 'HubSpot Inbound Marketing', 'Facebook Blueprint Certified'],
            64 => ['Registered Nurse License', 'BLS Certification', 'ACLS Certification'],
            66 => ['Professional Engineer License', 'PMP Certification', 'Six Sigma Green Belt'],
            68 => ['CPA License', 'CFA Charter', 'Certified Internal Auditor']
        ];

        $categoryCerts = $certs[$category] ?? ['Professional Certification'];
        return array_slice($categoryCerts, 0, rand(1, 2));
    }
}
