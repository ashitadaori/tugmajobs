<?php

echo "==============================================\n";
echo "  DATABASE DOCUMENTATION GENERATOR\n";
echo "==============================================\n\n";

class DatabaseDocumentationGenerator
{
    private $pdo;
    private $documentation = [];
    
    public function __construct()
    {
        $this->connectToDatabase();
    }
    
    private function connectToDatabase()
    {
        try {
            $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            echo "✅ Connected to database\n\n";
        } catch (PDOException $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function generateDocumentation()
    {
        echo "📋 Generating comprehensive database documentation...\n\n";
        
        $this->getAllTables();
        $this->getTableStructures();
        $this->getRelationships();
        $this->generateMarkdownDocumentation();
        $this->generateHtmlDocumentation();
        
        echo "🎉 Documentation generated successfully!\n";
    }
    
    private function getAllTables()
    {
        echo "🔍 Discovering all tables...\n";
        
        $stmt = $this->pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $this->documentation[$table] = [
                'name' => $table,
                'columns' => [],
                'indexes' => [],
                'foreign_keys' => [],
                'referenced_by' => [],
                'row_count' => 0,
                'purpose' => $this->getTablePurpose($table)
            ];
        }
        
        echo "   Found " . count($tables) . " tables\n\n";
    }
    
    private function getTableStructures()
    {
        echo "📊 Analyzing table structures...\n";
        
        foreach ($this->documentation as $tableName => &$tableInfo) {
            // Get columns
            $stmt = $this->pdo->query("DESCRIBE `$tableName`");
            $columns = $stmt->fetchAll();
            
            foreach ($columns as $column) {
                $tableInfo['columns'][] = [
                    'name' => $column['Field'],
                    'type' => $column['Type'],
                    'null' => $column['Null'] === 'YES',
                    'key' => $column['Key'],
                    'default' => $column['Default'],
                    'extra' => $column['Extra'],
                    'description' => $this->getColumnDescription($tableName, $column['Field'])
                ];
            }
            
            // Get row count
            try {
                $stmt = $this->pdo->query("SELECT COUNT(*) FROM `$tableName`");
                $tableInfo['row_count'] = $stmt->fetchColumn();
            } catch (Exception $e) {
                $tableInfo['row_count'] = 0;
            }
            
            // Get indexes
            $stmt = $this->pdo->query("SHOW INDEX FROM `$tableName`");
            $indexes = $stmt->fetchAll();
            
            $indexGroups = [];
            foreach ($indexes as $index) {
                $indexGroups[$index['Key_name']][] = $index;
            }
            
            foreach ($indexGroups as $indexName => $indexColumns) {
                $tableInfo['indexes'][] = [
                    'name' => $indexName,
                    'type' => $indexColumns[0]['Index_type'],
                    'unique' => !$indexColumns[0]['Non_unique'],
                    'columns' => array_column($indexColumns, 'Column_name')
                ];
            }
        }
        
        echo "   Analyzed " . count($this->documentation) . " table structures\n\n";
    }
    
    private function getRelationships()
    {
        echo "🔗 Mapping relationships...\n";
        
        $stmt = $this->pdo->query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_SCHEMA = 'job_portal'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $foreignKeys = $stmt->fetchAll();
        
        foreach ($foreignKeys as $fk) {
            $tableName = $fk['TABLE_NAME'];
            $referencedTable = $fk['REFERENCED_TABLE_NAME'];
            
            // Add foreign key to source table
            if (isset($this->documentation[$tableName])) {
                $this->documentation[$tableName]['foreign_keys'][] = [
                    'column' => $fk['COLUMN_NAME'],
                    'references_table' => $fk['REFERENCED_TABLE_NAME'],
                    'references_column' => $fk['REFERENCED_COLUMN_NAME'],
                    'constraint_name' => $fk['CONSTRAINT_NAME']
                ];
            }
            
            // Add reference to target table
            if (isset($this->documentation[$referencedTable])) {
                $this->documentation[$referencedTable]['referenced_by'][] = [
                    'table' => $fk['TABLE_NAME'],
                    'column' => $fk['COLUMN_NAME'],
                    'references_column' => $fk['REFERENCED_COLUMN_NAME']
                ];
            }
        }
        
        echo "   Mapped " . count($foreignKeys) . " relationships\n\n";
    }
    
    private function getTablePurpose($tableName)
    {
        $purposes = [
            'users' => 'Core user accounts table storing authentication and basic profile information for all user types (admin, employer, jobseeker)',
            'jobs' => 'Job postings created by employers with all job details, requirements, and status information',
            'job_applications' => 'Applications submitted by job seekers for specific job postings, tracking application status',
            'categories' => 'Job categories/industries for organizing and filtering job postings',
            'job_types' => 'Employment types (full-time, part-time, contract, etc.) for job classification',
            'employers' => 'Extended profile information for users with employer role, company details and verification',
            'jobseekers' => 'Comprehensive profile information for job seekers including skills, experience, and preferences',
            'admins' => 'Administrative user profiles with permissions and access control settings',
            'notifications' => 'System notifications and alerts for users about various events and updates',
            'job_views' => 'Tracking table for job post views and analytics, helps with recommendations',
            'kyc_verifications' => 'Know Your Customer verification sessions and status for user identity verification',
            'kyc_data' => 'Stored KYC verification data and documents for verified users',
            'employer_documents' => 'Business documents and certificates uploaded by employers for verification',
            'saved_jobs' => 'User saved/bookmarked jobs for later viewing or application',
            'job_user' => 'Many-to-many pivot table for saved jobs relationship between users and jobs',
            'employer_profiles' => 'Legacy employer profile table, may contain additional employer information',
            'migrations' => 'Laravel migration history table tracking database schema changes',
            'personal_access_tokens' => 'API tokens for authenticated access to the application',
            'password_reset_tokens' => 'Temporary tokens for password reset functionality',
            'failed_jobs' => 'Laravel queue failed jobs tracking table',
            'sessions' => 'User session data storage table',
            'job_application_status_histories' => 'Audit trail of job application status changes over time'
        ];
        
        return $purposes[$tableName] ?? 'Purpose not documented - requires manual review';
    }
    
    private function getColumnDescription($tableName, $columnName)
    {
        $descriptions = [
            // Common fields
            'id' => 'Primary key - unique identifier',
            'user_id' => 'Foreign key reference to users table',
            'created_at' => 'Timestamp when record was created',
            'updated_at' => 'Timestamp when record was last updated',
            'name' => 'Name or title field',
            'email' => 'Email address',
            'password' => 'Encrypted password hash',
            'status' => 'Current status of the record',
            'is_active' => 'Boolean flag indicating if record is active',
            
            // Users table specific
            'role' => 'User role: admin, employer, jobseeker',
            'mobile' => 'Mobile phone number',
            'designation' => 'Job title or position',
            'image' => 'Profile image file path',
            'skills' => 'JSON array of user skills',
            'education' => 'JSON array of education history',
            'experience_years' => 'Years of work experience',
            'bio' => 'User biography or description',
            'kyc_status' => 'KYC verification status',
            'kyc_session_id' => 'Reference to KYC verification session',
            'kyc_completed_at' => 'Timestamp when KYC was completed',
            'kyc_verified_at' => 'Timestamp when KYC was verified',
            'kyc_data' => 'JSON data from KYC verification',
            'google_id' => 'Google OAuth user ID',
            'google_token' => 'Google OAuth access token',
            'profile_image' => 'Profile image URL or path',
            
            // Jobs table specific
            'title' => 'Job title or position name',
            'company' => 'Company or employer name',
            'location' => 'Job location (city, state, country)',
            'job_type' => 'Type of employment (full-time, part-time, etc.)',
            'description' => 'Detailed job description',
            'requirements' => 'Job requirements and qualifications',
            'salary' => 'Salary or compensation amount',
            'salary_min' => 'Minimum salary range',
            'salary_max' => 'Maximum salary range',
            'salary_currency' => 'Currency for salary (USD, PHP, etc.)',
            'salary_period' => 'Salary period (hourly, monthly, yearly)',
            'category_id' => 'Foreign key to categories table',
            'job_type_id' => 'Foreign key to job_types table',
            'employer_id' => 'Foreign key to users table (employer)',
            'is_featured' => 'Boolean flag for featured job posts',
            'expires_at' => 'Job posting expiration date',
            'application_deadline' => 'Last date to apply',
            'remote_work' => 'Boolean flag for remote work option',
            'experience_level' => 'Required experience level',
            'benefits' => 'Job benefits and perks',
            
            // Jobseekers specific
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'middle_name' => 'Middle name',
            'date_of_birth' => 'Date of birth',
            'gender' => 'Gender',
            'nationality' => 'Nationality',
            'marital_status' => 'Marital status',
            'phone' => 'Primary phone number',
            'alternate_phone' => 'Secondary phone number',
            'linkedin_url' => 'LinkedIn profile URL',
            'portfolio_url' => 'Portfolio website URL',
            'github_url' => 'GitHub profile URL',
            'current_address' => 'Current residential address',
            'permanent_address' => 'Permanent address',
            'city' => 'City of residence',
            'state' => 'State or province',
            'country' => 'Country',
            'postal_code' => 'ZIP or postal code',
            'current_job_title' => 'Current job position',
            'current_company' => 'Current employer',
            'professional_summary' => 'Professional summary or objective',
            'total_experience_years' => 'Total years of experience',
            'total_experience_months' => 'Total months of experience',
            'soft_skills' => 'JSON array of soft skills',
            'languages' => 'JSON array of spoken languages',
            'certifications' => 'JSON array of certifications',
            'courses' => 'JSON array of completed courses',
            'work_experience' => 'JSON array of work history',
            'projects' => 'JSON array of projects',
            'preferred_job_types' => 'JSON array of preferred employment types',
            'preferred_categories' => 'JSON array of preferred job categories',
            'preferred_locations' => 'JSON array of preferred work locations',
            'open_to_remote' => 'Boolean flag for remote work preference',
            'open_to_relocation' => 'Boolean flag for relocation willingness',
            'expected_salary_min' => 'Minimum expected salary',
            'expected_salary_max' => 'Maximum expected salary',
            'availability' => 'Job start availability',
            'available_from' => 'Date available to start work',
            'currently_employed' => 'Boolean flag for current employment status',
            'notice_period_days' => 'Notice period in days for current job',
            'resume_file' => 'Resume file path',
            'cover_letter_file' => 'Cover letter file path',
            'profile_photo' => 'Profile photo file path',
            'portfolio_files' => 'JSON array of portfolio file paths',
            
            // Admin specific
            'admin_level' => 'Administrative level or hierarchy',
            'department' => 'Administrative department',
            'position' => 'Administrative position',
            'responsibilities' => 'List of admin responsibilities',
            'permissions' => 'JSON array of detailed permissions',
            'accessible_modules' => 'JSON array of accessible system modules',
            'can_manage_users' => 'Permission to manage users',
            'can_manage_jobs' => 'Permission to manage jobs',
            'can_manage_employers' => 'Permission to manage employers',
            'can_view_analytics' => 'Permission to view analytics',
            'can_manage_settings' => 'Permission to manage system settings',
            'can_manage_admins' => 'Permission to manage other admins',
            'last_login_at' => 'Last login timestamp',
            'last_login_ip' => 'IP address of last login',
            'login_history' => 'JSON array of login history',
            'actions_performed' => 'Count of administrative actions performed',
            'promoted_at' => 'Timestamp when user was promoted to admin',
            'promoted_by' => 'ID of admin who promoted this user',
            'force_password_change' => 'Boolean flag to force password change',
            'password_changed_at' => 'Timestamp of last password change',
            
            // Notifications specific
            'recipient_id' => 'User ID who receives the notification',
            'sender_id' => 'User ID who triggered the notification',
            'type' => 'Notification type or category',
            'message' => 'Notification message content',
            'data' => 'JSON data associated with notification',
            'read_at' => 'Timestamp when notification was read',
            'action_url' => 'URL to redirect when notification is clicked'
        ];
        
        // Check specific table-column combinations
        $specificKey = "$tableName.$columnName";
        $specificDescriptions = [
            'jobs.slug' => 'SEO-friendly URL identifier for the job post',
            'jobs.views_count' => 'Number of times this job has been viewed',
            'jobs.applications_count' => 'Number of applications received for this job',
            'categories.icon' => 'Icon class name for displaying category icon',
            'categories.slug' => 'SEO-friendly URL identifier for the category',
            'job_applications.application_status' => 'Current status: pending, approved, rejected, etc.',
            'job_views.ip_address' => 'IP address of the viewer for analytics',
            'job_views.referrer' => 'Source website or page that referred the view',
            'employers.company_size' => 'Size category of the company',
            'employers.industry' => 'Industry or business sector',
            'employers.website' => 'Company website URL',
            'employers.verification_status' => 'Company verification status'
        ];
        
        if (isset($specificDescriptions[$specificKey])) {
            return $specificDescriptions[$specificKey];
        }
        
        return $descriptions[$columnName] ?? 'No description available';
    }
    
    private function generateMarkdownDocumentation()
    {
        $markdown = "# Job Portal Database Documentation\n\n";
        $markdown .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $markdown .= "## Database Overview\n\n";
        $markdown .= "This database powers a comprehensive job portal system with the following main components:\n\n";
        $markdown .= "- **User Management**: Multi-role user system (Admin, Employer, Job Seeker)\n";
        $markdown .= "- **Job Management**: Job postings, applications, and categorization\n";
        $markdown .= "- **Profile Management**: Detailed profiles for employers and job seekers\n";
        $markdown .= "- **Verification System**: KYC verification for users\n";
        $markdown .= "- **Notification System**: Real-time notifications and alerts\n";
        $markdown .= "- **Analytics**: Job views and application tracking\n\n";
        
        $markdown .= "## Database Statistics\n\n";
        $totalTables = count($this->documentation);
        $totalRows = array_sum(array_column($this->documentation, 'row_count'));
        $markdown .= "- **Total Tables**: {$totalTables}\n";
        $markdown .= "- **Total Records**: {$totalRows}\n\n";
        
        $markdown .= "## Tables Overview\n\n";
        $markdown .= "| Table | Records | Purpose |\n";
        $markdown .= "|-------|---------|----------|\n";
        
        foreach ($this->documentation as $table) {
            $markdown .= "| `{$table['name']}` | {$table['row_count']} | {$table['purpose']} |\n";
        }
        
        $markdown .= "\n## Detailed Table Documentation\n\n";
        
        foreach ($this->documentation as $table) {
            $markdown .= "### `{$table['name']}` Table\n\n";
            $markdown .= "**Purpose**: {$table['purpose']}\n\n";
            $markdown .= "**Records**: {$table['row_count']}\n\n";
            
            // Columns
            $markdown .= "#### Columns\n\n";
            $markdown .= "| Column | Type | Null | Key | Default | Description |\n";
            $markdown .= "|--------|------|------|-----|---------|-------------|\n";
            
            foreach ($table['columns'] as $column) {
                $null = $column['null'] ? '✅' : '❌';
                $key = $column['key'] ?: '-';
                $default = $column['default'] ?: '-';
                $markdown .= "| `{$column['name']}` | {$column['type']} | {$null} | {$key} | {$default} | {$column['description']} |\n";
            }
            
            // Foreign Keys
            if (!empty($table['foreign_keys'])) {
                $markdown .= "\n#### Foreign Keys\n\n";
                $markdown .= "| Column | References | Description |\n";
                $markdown .= "|--------|------------|-------------|\n";
                
                foreach ($table['foreign_keys'] as $fk) {
                    $markdown .= "| `{$fk['column']}` | `{$fk['references_table']}`.`{$fk['references_column']}` | Links to {$fk['references_table']} table |\n";
                }
            }
            
            // Referenced By
            if (!empty($table['referenced_by'])) {
                $markdown .= "\n#### Referenced By\n\n";
                $markdown .= "| Table | Column | Description |\n";
                $markdown .= "|-------|--------|-------------|\n";
                
                foreach ($table['referenced_by'] as $ref) {
                    $markdown .= "| `{$ref['table']}` | `{$ref['column']}` | {$ref['table']} references this table |\n";
                }
            }
            
            // Indexes
            if (!empty($table['indexes'])) {
                $markdown .= "\n#### Indexes\n\n";
                $markdown .= "| Name | Type | Unique | Columns |\n";
                $markdown .= "|------|------|--------|----------|\n";
                
                foreach ($table['indexes'] as $index) {
                    $unique = $index['unique'] ? '✅' : '❌';
                    $columns = implode(', ', $index['columns']);
                    $markdown .= "| `{$index['name']}` | {$index['type']} | {$unique} | {$columns} |\n";
                }
            }
            
            $markdown .= "\n---\n\n";
        }
        
        $markdown .= "## Entity Relationship Diagram\n\n";
        $markdown .= "```mermaid\n";
        $markdown .= "erDiagram\n";
        
        // Generate basic ERD
        foreach ($this->documentation as $table) {
            foreach ($table['foreign_keys'] as $fk) {
                $markdown .= "    {$fk['references_table']} ||--o{ {$table['name']} : \"{$fk['column']}\"\n";
            }
        }
        
        $markdown .= "```\n\n";
        
        $markdown .= "---\n\n";
        $markdown .= "*This documentation was automatically generated from the database schema.*\n";
        
        file_put_contents('DATABASE_DOCUMENTATION.md', $markdown);
        echo "✅ Markdown documentation saved to: DATABASE_DOCUMENTATION.md\n";
    }
    
    private function generateHtmlDocumentation()
    {
        $html = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
        $html .= "<meta charset=\"UTF-8\">\n";
        $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $html .= "<title>Job Portal Database Documentation</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }\n";
        $html .= "h1, h2, h3 { color: #333; }\n";
        $html .= "table { border-collapse: collapse; width: 100%; margin: 20px 0; }\n";
        $html .= "th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n";
        $html .= "th { background-color: #f2f2f2; font-weight: bold; }\n";
        $html .= "tr:nth-child(even) { background-color: #f9f9f9; }\n";
        $html .= "code { background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px; }\n";
        $html .= ".table-section { margin: 30px 0; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }\n";
        $html .= ".stats { background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0; }\n";
        $html .= "</style>\n</head>\n<body>\n";
        
        $html .= "<h1>Job Portal Database Documentation</h1>\n";
        $html .= "<p><strong>Generated on:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        
        $html .= "<div class=\"stats\">\n";
        $html .= "<h2>Database Statistics</h2>\n";
        $totalTables = count($this->documentation);
        $totalRows = array_sum(array_column($this->documentation, 'row_count'));
        $html .= "<ul>\n";
        $html .= "<li><strong>Total Tables:</strong> {$totalTables}</li>\n";
        $html .= "<li><strong>Total Records:</strong> {$totalRows}</li>\n";
        $html .= "</ul>\n";
        $html .= "</div>\n";
        
        $html .= "<h2>Tables Overview</h2>\n";
        $html .= "<table>\n<tr><th>Table</th><th>Records</th><th>Purpose</th></tr>\n";
        
        foreach ($this->documentation as $table) {
            $html .= "<tr><td><code>{$table['name']}</code></td><td>{$table['row_count']}</td><td>{$table['purpose']}</td></tr>\n";
        }
        $html .= "</table>\n";
        
        $html .= "<h2>Detailed Table Documentation</h2>\n";
        
        foreach ($this->documentation as $table) {
            $html .= "<div class=\"table-section\">\n";
            $html .= "<h3><code>{$table['name']}</code> Table</h3>\n";
            $html .= "<p><strong>Purpose:</strong> {$table['purpose']}</p>\n";
            $html .= "<p><strong>Records:</strong> {$table['row_count']}</p>\n";
            
            // Columns
            $html .= "<h4>Columns</h4>\n";
            $html .= "<table>\n<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Description</th></tr>\n";
            
            foreach ($table['columns'] as $column) {
                $null = $column['null'] ? '✅' : '❌';
                $key = $column['key'] ?: '-';
                $default = $column['default'] ?: '-';
                $html .= "<tr><td><code>{$column['name']}</code></td><td>{$column['type']}</td><td>{$null}</td><td>{$key}</td><td>{$default}</td><td>{$column['description']}</td></tr>\n";
            }
            $html .= "</table>\n";
            
            // Foreign Keys
            if (!empty($table['foreign_keys'])) {
                $html .= "<h4>Foreign Keys</h4>\n";
                $html .= "<table>\n<tr><th>Column</th><th>References</th><th>Description</th></tr>\n";
                
                foreach ($table['foreign_keys'] as $fk) {
                    $html .= "<tr><td><code>{$fk['column']}</code></td><td><code>{$fk['references_table']}.{$fk['references_column']}</code></td><td>Links to {$fk['references_table']} table</td></tr>\n";
                }
                $html .= "</table>\n";
            }
            
            $html .= "</div>\n";
        }
        
        $html .= "</body>\n</html>\n";
        
        file_put_contents('database_documentation.html', $html);
        echo "✅ HTML documentation saved to: database_documentation.html\n";
    }
}

// Run the documentation generator
$generator = new DatabaseDocumentationGenerator();
$generator->generateDocumentation();

echo "\n" . str_repeat("=", 70) . "\n";
