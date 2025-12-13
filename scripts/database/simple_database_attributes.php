<?php

echo "Generating simple database attributes documentation...\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $output = "# Job Portal Database Attributes\n\n";
    
    foreach ($tables as $tableName) {
        $output .= "## $tableName\n\n";
        $output .= "| Attribute | Data Type | Description |\n";
        $output .= "|-----------|-----------|-------------|\n";
        
        // Get columns for this table
        $stmt = $pdo->query("DESCRIBE `$tableName`");
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $column) {
            $attribute = $column['Field'];
            $dataType = $column['Type'];
            $description = getDescription($tableName, $attribute);
            
            $output .= "| $attribute | $dataType | $description |\n";
        }
        
        $output .= "\n";
    }
    
    file_put_contents('DATABASE_ATTRIBUTES.md', $output);
    echo "✅ Simple attributes documentation saved to: DATABASE_ATTRIBUTES.md\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

function getDescription($table, $column) {
    $descriptions = [
        // Common fields
        'id' => 'Primary key identifier',
        'user_id' => 'Reference to users table',
        'created_at' => 'Record creation timestamp',
        'updated_at' => 'Record last update timestamp',
        'deleted_at' => 'Soft delete timestamp',
        'name' => 'Name field',
        'email' => 'Email address',
        'password' => 'Encrypted password',
        'status' => 'Record status',
        
        // Users table
        'role' => 'User role (admin, employer, jobseeker)',
        'mobile' => 'Mobile phone number',
        'designation' => 'Job title or position',
        'image' => 'Profile image path',
        'skills' => 'JSON array of skills',
        'education' => 'JSON array of education',
        'experience_years' => 'Years of experience',
        'bio' => 'Biography or description',
        'kyc_status' => 'KYC verification status',
        'kyc_session_id' => 'KYC session identifier',
        'kyc_completed_at' => 'KYC completion timestamp',
        'kyc_verified_at' => 'KYC verification timestamp',
        'kyc_data' => 'KYC verification data',
        'google_id' => 'Google OAuth identifier',
        'google_token' => 'Google OAuth token',
        'google_refresh_token' => 'Google refresh token',
        'profile_image' => 'Profile image URL',
        'preferred_categories' => 'Preferred job categories',
        'preferred_job_types' => 'Preferred job types',
        'experience_level' => 'Experience level',
        'salary_expectation_min' => 'Minimum salary expectation',
        'salary_expectation_max' => 'Maximum salary expectation',
        
        // Jobs table
        'title' => 'Job title',
        'company' => 'Company name',
        'location' => 'Job location',
        'description' => 'Job description',
        'requirements' => 'Job requirements',
        'salary' => 'Salary amount',
        'salary_min' => 'Minimum salary',
        'salary_max' => 'Maximum salary',
        'salary_currency' => 'Salary currency',
        'salary_period' => 'Salary period (hourly, monthly, yearly)',
        'category_id' => 'Job category reference',
        'job_type_id' => 'Job type reference',
        'employer_id' => 'Employer user reference',
        'is_featured' => 'Featured job flag',
        'is_active' => 'Active status flag',
        'expires_at' => 'Job expiration date',
        'views_count' => 'Number of views',
        'applications_count' => 'Number of applications',
        'slug' => 'URL-friendly identifier',
        'remote_work' => 'Remote work option',
        'experience_required' => 'Required experience',
        'application_deadline' => 'Application deadline',
        'benefits' => 'Job benefits',
        'preliminary_questions' => 'Pre-screening questions',
        
        // Job Applications
        'job_id' => 'Reference to job',
        'application_status' => 'Application status',
        'cover_letter' => 'Cover letter text',
        'resume' => 'Resume file path',
        'applied_at' => 'Application submission time',
        'shortlisted' => 'Shortlisted flag',
        'preliminary_answers' => 'Answers to pre-screening questions',
        
        // Categories
        'icon' => 'Category icon',
        
        // Jobseekers
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'middle_name' => 'Middle name',
        'date_of_birth' => 'Date of birth',
        'gender' => 'Gender',
        'nationality' => 'Nationality',
        'marital_status' => 'Marital status',
        'phone' => 'Phone number',
        'alternate_phone' => 'Alternative phone',
        'linkedin_url' => 'LinkedIn profile URL',
        'portfolio_url' => 'Portfolio website URL',
        'github_url' => 'GitHub profile URL',
        'current_address' => 'Current address',
        'permanent_address' => 'Permanent address',
        'city' => 'City',
        'state' => 'State/Province',
        'country' => 'Country',
        'postal_code' => 'Postal code',
        'current_job_title' => 'Current job title',
        'current_company' => 'Current company',
        'professional_summary' => 'Professional summary',
        'total_experience_years' => 'Total experience years',
        'total_experience_months' => 'Total experience months',
        'soft_skills' => 'Soft skills array',
        'languages' => 'Languages spoken',
        'certifications' => 'Certifications array',
        'courses' => 'Courses completed',
        'work_experience' => 'Work history array',
        'projects' => 'Projects array',
        'preferred_locations' => 'Preferred work locations',
        'open_to_remote' => 'Open to remote work',
        'open_to_relocation' => 'Open to relocation',
        'expected_salary_min' => 'Expected minimum salary',
        'expected_salary_max' => 'Expected maximum salary',
        'availability' => 'Availability to start',
        'available_from' => 'Available start date',
        'currently_employed' => 'Currently employed flag',
        'notice_period_days' => 'Notice period in days',
        'resume_file' => 'Resume file path',
        'cover_letter_file' => 'Cover letter file path',
        'profile_photo' => 'Profile photo path',
        'portfolio_files' => 'Portfolio files array',
        'notification_preferences' => 'Notification preferences',
        'privacy_settings' => 'Privacy settings',
        'profile_visibility' => 'Profile visibility flag',
        'allow_recruiter_contact' => 'Allow recruiter contact',
        'job_alert_preferences' => 'Job alert preferences',
        'facebook_url' => 'Facebook profile URL',
        'twitter_url' => 'Twitter profile URL',
        'instagram_url' => 'Instagram profile URL',
        'profile_status' => 'Profile completion status',
        'is_featured' => 'Featured profile flag',
        'is_premium' => 'Premium account flag',
        'premium_expires_at' => 'Premium expiration date',
        'profile_completion_percentage' => 'Profile completion percentage',
        'profile_views' => 'Profile view count',
        'total_applications' => 'Total applications sent',
        'interviews_attended' => 'Interviews attended count',
        'jobs_offered' => 'Job offers received',
        'average_rating' => 'Average rating',
        'search_keywords' => 'Search keywords',
        'search_score' => 'Search relevance score',
        
        // Employers
        'company_name' => 'Company name',
        'company_description' => 'Company description',
        'company_size' => 'Company size category',
        'industry' => 'Industry sector',
        'website' => 'Company website',
        'founded_year' => 'Company founding year',
        'headquarters' => 'Company headquarters',
        'company_logo' => 'Company logo path',
        'verification_status' => 'Verification status',
        'is_verified' => 'Verification flag',
        'verified_at' => 'Verification timestamp',
        'business_license' => 'Business license number',
        'tax_id' => 'Tax identification number',
        'contact_person' => 'Primary contact person',
        'contact_phone' => 'Contact phone number',
        'contact_email' => 'Contact email address',
        'billing_address' => 'Billing address',
        'company_benefits' => 'Company benefits',
        'company_culture' => 'Company culture description',
        'social_media_links' => 'Social media links',
        'jobs_posted' => 'Number of jobs posted',
        'active_jobs' => 'Number of active jobs',
        'total_hires' => 'Total successful hires',
        'company_rating' => 'Company rating',
        'subscription_type' => 'Subscription plan type',
        'subscription_expires_at' => 'Subscription expiration',
        
        // Admins
        'admin_level' => 'Administrative level',
        'department' => 'Department',
        'position' => 'Position title',
        'responsibilities' => 'Job responsibilities',
        'permissions' => 'Permission settings',
        'accessible_modules' => 'Accessible system modules',
        'can_manage_users' => 'Can manage users permission',
        'can_manage_jobs' => 'Can manage jobs permission',
        'can_manage_employers' => 'Can manage employers permission',
        'can_view_analytics' => 'Can view analytics permission',
        'can_manage_settings' => 'Can manage settings permission',
        'can_manage_admins' => 'Can manage admins permission',
        'last_login_at' => 'Last login timestamp',
        'last_login_ip' => 'Last login IP address',
        'login_history' => 'Login history array',
        'actions_performed' => 'Actions performed count',
        'promoted_at' => 'Promotion timestamp',
        'promoted_by' => 'Promoted by admin ID',
        'force_password_change' => 'Force password change flag',
        'password_changed_at' => 'Password change timestamp',
        
        // Notifications
        'recipient_id' => 'Notification recipient',
        'sender_id' => 'Notification sender',
        'type' => 'Notification type',
        'message' => 'Notification message',
        'data' => 'Additional notification data',
        'read_at' => 'Read timestamp',
        'action_url' => 'Action URL',
        
        // Job Views
        'job_id' => 'Job reference',
        'viewer_id' => 'Viewer user ID',
        'ip_address' => 'Viewer IP address',
        'user_agent' => 'Browser user agent',
        'referrer' => 'Referrer URL',
        'session_id' => 'Session identifier',
        'viewed_at' => 'View timestamp',
        
        // KYC Data
        'session_id' => 'KYC session ID',
        'verification_data' => 'Verification data JSON',
        'document_type' => 'Document type',
        'document_data' => 'Document data JSON',
        'extracted_data' => 'Extracted information',
        'verification_result' => 'Verification result',
        
        // KYC Verifications
        'didit_session_id' => 'Didit session identifier',
        'workflow_id' => 'Workflow identifier',
        'verification_status' => 'Verification status',
        'callback_data' => 'Callback data',
        'webhook_data' => 'Webhook response data',
        'completed_at' => 'Completion timestamp',
        'expires_at' => 'Expiration timestamp',
        
        // Employer Documents
        'document_name' => 'Document name',
        'file_path' => 'File storage path',
        'file_name' => 'Original file name',
        'file_size' => 'File size',
        'mime_type' => 'File MIME type',
        'admin_notes' => 'Admin review notes',
        'submitted_at' => 'Submission timestamp',
        'reviewed_at' => 'Review timestamp',
        'reviewed_by' => 'Reviewing admin ID',
        
        // Saved Jobs
        'saved_at' => 'Save timestamp',
        
        // Job Application Status Histories
        'job_application_id' => 'Job application reference',
        'old_status' => 'Previous status',
        'new_status' => 'New status',
        'notes' => 'Status change notes',
        'changed_by' => 'Changed by user ID',
        'changed_at' => 'Change timestamp',
        
        // Reference tables
        'range' => 'Size range',
        'min_employees' => 'Minimum employees',
        'max_employees' => 'Maximum employees',
        'label' => 'Display label',
        'sort_order' => 'Sort order',
        'is_active' => 'Active status flag',
        'latitude' => 'Geographic latitude',
        'longitude' => 'Geographic longitude',
        'job_count' => 'Related jobs count',
        'popularity_score' => 'Popularity score',
        'category' => 'Skill category'
    ];
    
    // Check specific table.column combinations first
    $key = "$table.$column";
    $specific = [
        'users.role' => 'User role: admin, employer, jobseeker',
        'jobs.slug' => 'SEO-friendly URL identifier',
        'categories.slug' => 'SEO-friendly URL identifier',
        'job_applications.status' => 'Application status: pending, approved, rejected',
        'employers.industry' => 'Business industry sector',
        'jobseekers.country' => 'Country of residence'
    ];
    
    if (isset($specific[$key])) {
        return $specific[$key];
    }
    
    return $descriptions[$column] ?? 'Data field';
}

echo "\n======================================================================\n";
