<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedJobsForClustering extends Command
{
    protected $signature = 'seed:jobs-clustering';
    protected $description = 'Seed jobs across multiple categories for effective k-means clustering (5+ jobs per category)';

    public function handle()
    {
        $this->info('🌱 Seeding Jobs Across Categories for K-Means Clustering');
        $this->line(str_repeat('=', 60));

        try {
            // Get or create employer
            $employer = $this->getOrCreateEmployer();
            
            // Get or create job types
            $jobTypes = $this->getOrCreateJobTypes();
            
            // Seed jobs for each major category
            $this->seedITJobs($employer, $jobTypes);
            $this->seedFinanceJobs($employer, $jobTypes);
            $this->seedMarketingJobs($employer, $jobTypes);
            $this->seedHealthcareJobs($employer, $jobTypes);
            $this->seedEducationJobs($employer, $jobTypes);
            $this->seedEngineeringJobs($employer, $jobTypes);
            $this->seedBPOJobs($employer, $jobTypes);
            $this->seedHRJobs($employer, $jobTypes);
            $this->seedManufacturingJobs($employer, $jobTypes);
            $this->seedRetailJobs($employer, $jobTypes);
            
            // Final report
            $this->generateReport();
            
            $this->info("\n✅ Job seeding completed successfully!");
            $this->comment("The k-means clustering now has diverse job data across categories.");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function getOrCreateEmployer()
    {
        $employer = User::where('role', 'employer')->first();
        
        if (!$employer) {
            $employer = User::create([
                'name' => 'TechCorp Industries',
                'email' => 'hr@techcorp.com',
                'password' => bcrypt('password'),
                'role' => 'employer',
                'email_verified_at' => now()
            ]);
            $this->line("✓ Created sample employer: {$employer->name}");
        } else {
            $this->line("✓ Using existing employer: {$employer->name}");
        }
        
        return $employer;
    }

    private function getOrCreateJobTypes()
    {
        return [
            'full_time' => JobType::firstOrCreate(['name' => 'Full Time'], ['status' => 1]),
            'part_time' => JobType::firstOrCreate(['name' => 'Part Time'], ['status' => 1]),
            'contract' => JobType::firstOrCreate(['name' => 'Contract'], ['status' => 1]),
            'freelance' => JobType::firstOrCreate(['name' => 'Freelance'], ['status' => 1]),
        ];
    }

    private function seedITJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Information Technology (IT) / Software Development'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Senior Full Stack Developer',
                'description' => 'Lead development of web applications using modern technologies. Work with cross-functional teams to deliver high-quality software solutions.',
                'requirements' => 'PHP, Laravel, React, MySQL, 5+ years experience, team leadership skills',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'expert'
            ],
            [
                'title' => 'Junior Frontend Developer',
                'description' => 'Develop user interfaces for web applications. Learn and grow with experienced developers in a collaborative environment.',
                'requirements' => 'HTML, CSS, JavaScript, React basics, fresh graduate or 1 year experience',
                'location' => 'Cebu, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'entry'
            ],
            [
                'title' => 'Backend API Developer',
                'description' => 'Design and implement REST APIs and microservices. Focus on scalable backend architecture and database optimization.',
                'requirements' => 'Python, Django, PostgreSQL, Docker, API design, 3+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'intermediate'
            ],
            [
                'title' => 'DevOps Engineer',
                'description' => 'Manage cloud infrastructure and CI/CD pipelines. Ensure system reliability and automated deployment processes.',
                'requirements' => 'AWS, Docker, Kubernetes, Jenkins, Linux, 4+ years experience',
                'location' => 'BGC, Taguig',
                'job_type' => 'full_time',
                'experience_level' => 'expert'
            ],
            [
                'title' => 'Mobile App Developer (Flutter)',
                'description' => 'Create cross-platform mobile applications. Work on both iOS and Android app development using Flutter framework.',
                'requirements' => 'Flutter, Dart, Mobile UI/UX, Firebase, 2+ years experience',
                'location' => 'Quezon City, Philippines',
                'job_type' => 'contract',
                'experience_level' => 'intermediate'
            ],
            [
                'title' => 'Data Scientist',
                'description' => 'Analyze complex datasets and build machine learning models. Generate insights to drive business decisions.',
                'requirements' => 'Python, R, Machine Learning, SQL, Statistics, 3+ years experience',
                'location' => 'Alabang, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'intermediate'
            ],
            [
                'title' => 'Cybersecurity Specialist',
                'description' => 'Protect company systems and data from security threats. Implement security policies and conduct vulnerability assessments.',
                'requirements' => 'Network Security, Ethical Hacking, CISSP, Security Auditing, 4+ years experience',
                'location' => 'Ortigas, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'expert'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'IT');
    }

    private function seedFinanceJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Accounting / Finance'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Senior Financial Analyst',
                'description' => 'Analyze financial data and create comprehensive reports. Support strategic planning and budgeting processes.',
                'requirements' => 'CPA, Financial Modeling, Excel Advanced, SAP, 5+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'expert'
            ],
            [
                'title' => 'Junior Accountant',
                'description' => 'Handle daily accounting operations including journal entries and reconciliations. Learn under senior accounting staff.',
                'requirements' => 'Accounting degree, Basic Excel, Fresh graduate, willingness to learn',
                'location' => 'Mandaluyong, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'entry'
            ],
            [
                'title' => 'Tax Specialist',
                'description' => 'Manage corporate tax compliance and planning. Ensure adherence to local and international tax regulations.',
                'requirements' => 'Tax Law, BIR regulations, CPA preferred, 3+ years experience',
                'location' => 'BGC, Taguig',
                'job_type' => 'full_time',
                'experience_level' => 'intermediate'
            ],
            [
                'title' => 'Audit Manager',
                'description' => 'Lead internal and external audit processes. Ensure compliance with accounting standards and regulations.',
                'requirements' => 'CPA, Audit experience, Team management, Risk assessment, 6+ years experience',
                'location' => 'Ortigas, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'expert'
            ],
            [
                'title' => 'Budget Analyst',
                'description' => 'Prepare and monitor organizational budgets. Analyze spending patterns and provide cost optimization recommendations.',
                'requirements' => 'Financial Planning, Budget Management, Excel, PowerBI, 2+ years experience',
                'location' => 'Quezon City, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'intermediate'
            ],
            [
                'title' => 'Payroll Specialist',
                'description' => 'Process employee payroll and benefits. Maintain accurate payroll records and ensure compliance with labor laws.',
                'requirements' => 'Payroll software, Labor Law knowledge, Attention to detail, 2+ years experience',
                'location' => 'Pasig, Philippines',
                'job_type' => 'part_time',
                'experience_level' => 'intermediate'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'Finance');
    }

    private function seedMarketingJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Sales / Marketing'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Digital Marketing Manager',
                'description' => 'Develop and execute digital marketing strategies. Manage social media campaigns and analyze marketing performance.',
                'requirements' => 'Google Ads, Facebook Ads, SEO, Analytics, Content Marketing, 4+ years experience',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Social Media Specialist',
                'description' => 'Create engaging content for social media platforms. Build brand awareness and community engagement.',
                'requirements' => 'Social Media Management, Content Creation, Canva, Photography, 2+ years experience',
                'location' => 'Cebu, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Sales Representative',
                'description' => 'Generate leads and close sales deals. Build relationships with clients and achieve sales targets.',
                'requirements' => 'Sales experience, Communication skills, CRM software, Target-driven, 1+ years experience',
                'location' => 'Davao, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Content Marketing Writer',
                'description' => 'Create compelling content for blogs, websites, and marketing materials. Develop content strategies to drive engagement.',
                'requirements' => 'Creative Writing, SEO Writing, WordPress, Marketing knowledge, 2+ years experience',
                'location' => 'Quezon City, Philippines',
                'job_type' => 'freelance',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Brand Manager',
                'description' => 'Develop and maintain brand identity and positioning. Coordinate marketing campaigns and brand initiatives.',
                'requirements' => 'Brand Management, Marketing Strategy, Creative Direction, 5+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Email Marketing Specialist',
                'description' => 'Design and execute email marketing campaigns. Analyze email performance and optimize conversion rates.',
                'requirements' => 'Email Marketing Tools, HTML/CSS, A/B Testing, Analytics, 2+ years experience',
                'location' => 'BGC, Taguig',
                'job_type' => 'contract',
                'experience_level' => 'Mid-level'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'Marketing');
    }

    private function seedHealthcareJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Healthcare / Medical'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Registered Nurse',
                'description' => 'Provide direct patient care in hospital setting. Monitor patient conditions and administer medications.',
                'requirements' => 'RN License, BSN degree, Hospital experience, 2+ years experience',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Medical Technologist',
                'description' => 'Perform laboratory tests and analyses. Operate laboratory equipment and maintain quality control.',
                'requirements' => 'Medical Technology degree, Laboratory experience, Attention to detail, 1+ years experience',
                'location' => 'Cebu, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Physical Therapist',
                'description' => 'Provide rehabilitation services to patients. Develop treatment plans and monitor patient progress.',
                'requirements' => 'PT License, Physical Therapy degree, Patient care, 3+ years experience',
                'location' => 'Davao, Philippines',
                'job_type' => 'part_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Healthcare Administrator',
                'description' => 'Manage healthcare facility operations. Oversee staff, budgets, and compliance with health regulations.',
                'requirements' => 'Healthcare Management, Leadership, Budget Management, 5+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Medical Assistant',
                'description' => 'Support physicians in clinical tasks. Handle patient records and assist with medical procedures.',
                'requirements' => 'Medical Assistant certification, Basic medical knowledge, Fresh graduate acceptable',
                'location' => 'Quezon City, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Clinical Research Coordinator',
                'description' => 'Coordinate clinical trials and research studies. Ensure compliance with research protocols and regulations.',
                'requirements' => 'Research experience, GCP certification, Attention to detail, 3+ years experience',
                'location' => 'BGC, Taguig',
                'job_type' => 'contract',
                'experience_level' => 'Mid-level'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'Healthcare');
    }

    private function seedEducationJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Education / Teaching'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'High School Math Teacher',
                'description' => 'Teach mathematics to high school students. Develop lesson plans and assess student progress.',
                'requirements' => 'Education degree, LET License, Math major, Teaching experience, 2+ years experience',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Elementary Teacher',
                'description' => 'Provide comprehensive education to elementary students. Create engaging learning environments.',
                'requirements' => 'Elementary Education degree, LET License, Classroom management, 1+ years experience',
                'location' => 'Cebu, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'ESL Online Tutor',
                'description' => 'Teach English to international students online. Conduct one-on-one and group lessons.',
                'requirements' => 'English proficiency, TESOL/TEFL, Online teaching, Flexible schedule',
                'location' => 'Remote, Philippines',
                'job_type' => 'part_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'University Professor (Computer Science)',
                'description' => 'Teach computer science courses at university level. Conduct research and publish academic papers.',
                'requirements' => 'Masters/PhD in CS, Research experience, Academic writing, 5+ years experience',
                'location' => 'Quezon City, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Training Specialist',
                'description' => 'Develop and deliver corporate training programs. Assess training needs and measure effectiveness.',
                'requirements' => 'Training & Development, Curriculum design, Presentation skills, 3+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'contract',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'School Principal',
                'description' => 'Lead educational institution administration. Oversee curriculum, staff, and student development.',
                'requirements' => 'Education Leadership, Administrative experience, MA in Education, 8+ years experience',
                'location' => 'Davao, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'Education');
    }

    private function seedEngineeringJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Engineering / Technical'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Civil Engineer',
                'description' => 'Design and oversee construction projects. Ensure structural integrity and compliance with building codes.',
                'requirements' => 'Civil Engineering degree, PRC License, AutoCAD, Project management, 3+ years experience',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Mechanical Engineer',
                'description' => 'Design mechanical systems and equipment. Analyze and test mechanical devices.',
                'requirements' => 'Mechanical Engineering degree, SolidWorks, Manufacturing knowledge, 2+ years experience',
                'location' => 'Laguna, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Electrical Engineer',
                'description' => 'Design electrical systems and power distribution. Troubleshoot electrical problems and ensure safety.',
                'requirements' => 'Electrical Engineering degree, PRC License, Electrical design, 4+ years experience',
                'location' => 'Cavite, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Quality Assurance Engineer',
                'description' => 'Ensure product quality through testing and inspection. Develop quality control procedures.',
                'requirements' => 'Engineering degree, Quality Management, ISO standards, 3+ years experience',
                'location' => 'Batangas, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Project Engineer',
                'description' => 'Coordinate engineering projects from conception to completion. Manage resources and timelines.',
                'requirements' => 'Engineering degree, Project Management, Communication skills, 2+ years experience',
                'location' => 'Cebu, Philippines',
                'job_type' => 'contract',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Manufacturing Engineer',
                'description' => 'Optimize manufacturing processes and improve efficiency. Design production systems and workflows.',
                'requirements' => 'Industrial Engineering, Lean Manufacturing, Process improvement, 4+ years experience',
                'location' => 'Laguna, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'Engineering');
    }

    private function seedBPOJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'BPO / Call Center / Customer Service'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Customer Service Representative',
                'description' => 'Handle customer inquiries via phone, email, and chat. Resolve issues and provide excellent service.',
                'requirements' => 'Excellent English, Communication skills, Computer literacy, Fresh graduate acceptable',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Technical Support Specialist',
                'description' => 'Provide technical assistance to customers. Troubleshoot software and hardware issues.',
                'requirements' => 'Technical background, Problem-solving, Communication skills, 1+ years experience',
                'location' => 'Cebu, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Team Leader - Customer Service',
                'description' => 'Lead customer service team and ensure quality standards. Coach agents and handle escalations.',
                'requirements' => 'Leadership skills, BPO experience, Performance management, 3+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Quality Analyst',
                'description' => 'Monitor and evaluate customer interactions for quality. Provide feedback and training recommendations.',
                'requirements' => 'Quality Assurance, Analytical skills, BPO experience, 2+ years experience',
                'location' => 'BGC, Taguig',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Sales Agent - Outbound',
                'description' => 'Generate sales through outbound calls. Meet sales targets and build customer relationships.',
                'requirements' => 'Sales skills, Persuasion, Target-oriented, Communication skills, 1+ years experience',
                'location' => 'Quezon City, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Chat Support Agent',
                'description' => 'Provide customer support through live chat platforms. Handle multiple conversations simultaneously.',
                'requirements' => 'Fast typing, Multitasking, Customer service, English proficiency, Fresh graduate acceptable',
                'location' => 'Pasig, Philippines',
                'job_type' => 'part_time',
                'experience_level' => 'Entry Level'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'BPO');
    }

    private function seedHRJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Human Resources / Admin'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'HR Manager',
                'description' => 'Oversee human resources operations including recruitment, employee relations, and policy development.',
                'requirements' => 'HR degree, Leadership, Employee Relations, Labor Law, 5+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Recruitment Specialist',
                'description' => 'Source and screen candidates for various positions. Coordinate interviews and hiring processes.',
                'requirements' => 'Recruitment experience, Communication skills, ATS systems, 2+ years experience',
                'location' => 'BGC, Taguig',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'HR Assistant',
                'description' => 'Support HR operations with documentation and administrative tasks. Assist in employee onboarding.',
                'requirements' => 'HR knowledge, Administrative skills, MS Office, Fresh graduate acceptable',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Training and Development Specialist',
                'description' => 'Design and implement employee training programs. Assess training needs and measure effectiveness.',
                'requirements' => 'Training Design, Learning & Development, Facilitation skills, 3+ years experience',
                'location' => 'Quezon City, Philippines',
                'job_type' => 'contract',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Compensation and Benefits Analyst',
                'description' => 'Analyze and manage employee compensation and benefits programs. Ensure competitive packages.',
                'requirements' => 'Compensation analysis, Benefits administration, HRIS, 3+ years experience',
                'location' => 'Ortigas, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Administrative Assistant',
                'description' => 'Provide administrative support to management. Handle correspondence and office operations.',
                'requirements' => 'Administrative skills, MS Office, Organization, Communication, 1+ years experience',
                'location' => 'Pasig, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'HR');
    }

    private function seedManufacturingJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Manufacturing / Production'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Production Supervisor',
                'description' => 'Supervise production line operations and ensure quality standards. Manage production schedules and staff.',
                'requirements' => 'Manufacturing experience, Leadership, Quality Control, 4+ years experience',
                'location' => 'Laguna, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Machine Operator',
                'description' => 'Operate manufacturing equipment and machinery. Monitor production output and maintain equipment.',
                'requirements' => 'Technical skills, Equipment operation, Safety awareness, 1+ years experience',
                'location' => 'Batangas, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Quality Control Inspector',
                'description' => 'Inspect products for quality compliance. Document quality issues and ensure standards are met.',
                'requirements' => 'Quality Control, Attention to detail, Inspection tools, 2+ years experience',
                'location' => 'Cavite, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Production Planner',
                'description' => 'Plan and schedule production activities. Coordinate with various departments to optimize efficiency.',
                'requirements' => 'Production Planning, ERP systems, Supply Chain knowledge, 3+ years experience',
                'location' => 'Laguna, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Warehouse Associate',
                'description' => 'Handle inventory management and warehouse operations. Pick, pack, and ship products.',
                'requirements' => 'Physical fitness, Inventory systems, Forklift operation, Fresh graduate acceptable',
                'location' => 'Bulacan, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Maintenance Technician',
                'description' => 'Maintain and repair manufacturing equipment. Perform preventive maintenance and troubleshooting.',
                'requirements' => 'Mechanical skills, Electrical knowledge, Troubleshooting, 2+ years experience',
                'location' => 'Batangas, Philippines',
                'job_type' => 'contract',
                'experience_level' => 'Mid-level'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'Manufacturing');
    }

    private function seedRetailJobs($employer, $jobTypes)
    {
        $category = Category::firstOrCreate(
            ['name' => 'Retail / Hospitality'],
            ['status' => 1]
        );

        $jobs = [
            [
                'title' => 'Store Manager',
                'description' => 'Manage retail store operations including sales, staff, and customer service. Achieve sales targets.',
                'requirements' => 'Retail management, Leadership, Sales experience, Customer service, 4+ years experience',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Senior'
            ],
            [
                'title' => 'Sales Associate',
                'description' => 'Assist customers with purchases and provide product information. Maintain store appearance and inventory.',
                'requirements' => 'Customer service, Sales skills, Communication, Fresh graduate acceptable',
                'location' => 'Cebu, Philippines',
                'job_type' => 'part_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Cashier',
                'description' => 'Process customer transactions and handle payments. Provide excellent customer service at checkout.',
                'requirements' => 'Cash handling, Customer service, Attention to detail, Fresh graduate acceptable',
                'location' => 'Davao, Philippines',
                'job_type' => 'part_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Hotel Front Desk Agent',
                'description' => 'Handle hotel guest check-in/out and reservations. Provide information and resolve guest issues.',
                'requirements' => 'Hospitality experience, Communication skills, Computer literacy, 1+ years experience',
                'location' => 'Boracay, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Entry Level'
            ],
            [
                'title' => 'Restaurant Manager',
                'description' => 'Oversee restaurant operations including staff, inventory, and customer satisfaction.',
                'requirements' => 'Restaurant management, Food service, Leadership, Budget management, 3+ years experience',
                'location' => 'Makati, Philippines',
                'job_type' => 'full_time',
                'experience_level' => 'Mid-level'
            ],
            [
                'title' => 'Visual Merchandiser',
                'description' => 'Create attractive product displays and store layouts. Plan promotional displays and seasonal setups.',
                'requirements' => 'Visual Merchandising, Creativity, Design sense, Retail experience, 2+ years experience',
                'location' => 'BGC, Taguig',
                'job_type' => 'contract',
                'experience_level' => 'Mid-level'
            ]
        ];

        $this->createJobs($jobs, $category, $employer, $jobTypes, 'Retail');
    }

    private function createJobs($jobs, $category, $employer, $jobTypes, $categoryName)
    {
        $this->line("\n📝 Creating {$categoryName} Jobs:");
        $this->line(str_repeat('-', 30));

        $createdCount = 0;
        foreach ($jobs as $jobData) {
            $existingJob = Job::where('title', $jobData['title'])->first();
            if ($existingJob) {
                $this->line("  ⚠ Job '{$jobData['title']}' already exists");
                continue;
            }

            try {
                $jobType = $jobTypes[$jobData['job_type']] ?? $jobTypes['full_time'];
                
                Job::create([
                    'title' => $jobData['title'],
                    'description' => $jobData['description'],
                    'requirements' => $jobData['requirements'],
                    'category_id' => $category->id,
                    'job_type_id' => $jobType->id,
                    'location' => $jobData['location'],
                    'experience_level' => $jobData['experience_level'] ?? 'intermediate',
                    'employer_id' => $employer->id,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $this->line("  ✓ Created: {$jobData['title']}");
                $createdCount++;
                
            } catch (\Exception $e) {
                $this->warn("  ❌ Failed to create '{$jobData['title']}': " . $e->getMessage());
            }
        }
        
        $this->info("  📊 Created {$createdCount} jobs in {$categoryName} category");
    }

    private function generateReport()
    {
        $this->line("\n📊 Final Job Distribution Report:");
        $this->line(str_repeat('=', 50));

        $totalJobs = Job::where('status', 1)->count();
        $this->info("Total Active Jobs: {$totalJobs}");

        $jobsByCategory = DB::table('jobs')
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->where('jobs.status', 1)
            ->select('categories.name', DB::raw('COUNT(*) as job_count'))
            ->groupBy('categories.name')
            ->orderBy('job_count', 'desc')
            ->get();

        $this->line("\nJobs per Category:");
        foreach ($jobsByCategory as $category) {
            $this->line("  - {$category->name}: {$category->job_count} jobs");
        }

        $this->line("\nJob Types Distribution:");
        $jobsByType = DB::table('jobs')
            ->join('job_types', 'jobs.job_type_id', '=', 'job_types.id')
            ->where('jobs.status', 1)
            ->select('job_types.name', DB::raw('COUNT(*) as job_count'))
            ->groupBy('job_types.name')
            ->get();

        foreach ($jobsByType as $type) {
            $this->line("  - {$type->name}: {$type->job_count} jobs");
        }
    }
}
