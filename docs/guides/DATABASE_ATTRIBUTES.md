# Job Portal Database Attributes

## admins

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| admin_level | varchar(255) | Administrative level |
| department | varchar(255) | Department |
| position | varchar(255) | Position title |
| responsibilities | text | Job responsibilities |
| permissions | longtext | Permission settings |
| accessible_modules | longtext | Accessible system modules |
| can_manage_users | tinyint(1) | Can manage users permission |
| can_manage_jobs | tinyint(1) | Can manage jobs permission |
| can_manage_employers | tinyint(1) | Can manage employers permission |
| can_view_analytics | tinyint(1) | Can view analytics permission |
| can_manage_settings | tinyint(1) | Can manage settings permission |
| can_manage_admins | tinyint(1) | Can manage admins permission |
| status | enum('active','inactive','suspended') | Record status |
| last_login_at | timestamp | Last login timestamp |
| last_login_ip | varchar(255) | Last login IP address |
| login_history | longtext | Login history array |
| actions_performed | int(11) | Actions performed count |
| promoted_at | timestamp | Promotion timestamp |
| promoted_by | bigint(20) unsigned | Promoted by admin ID |
| force_password_change | tinyint(1) | Force password change flag |
| password_changed_at | timestamp | Password change timestamp |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## categories

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| name | varchar(255) | Name field |
| slug | varchar(255) | SEO-friendly URL identifier |
| status | tinyint(1) | Record status |
| icon | varchar(255) | Category icon |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

## company_sizes

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| range | varchar(255) | Size range |
| min_employees | int(11) | Minimum employees |
| max_employees | int(11) | Maximum employees |
| label | varchar(255) | Display label |
| sort_order | int(11) | Sort order |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## employer_documents

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| document_type | varchar(255) | Document type |
| document_name | varchar(255) | Document name |
| file_path | varchar(255) | File storage path |
| file_name | varchar(255) | Original file name |
| file_size | varchar(255) | File size |
| mime_type | varchar(255) | File MIME type |
| status | enum('pending','approved','rejected') | Record status |
| admin_notes | text | Admin review notes |
| submitted_at | timestamp | Submission timestamp |
| reviewed_at | timestamp | Review timestamp |
| reviewed_by | bigint(20) unsigned | Reviewing admin ID |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## employer_profiles

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| company_name | varchar(255) | Company name |
| company_description | text | Company description |
| industry | varchar(255) | Industry sector |
| company_size | varchar(255) | Company size category |
| website | varchar(255) | Company website |
| company_logo | varchar(255) | Company logo path |
| location | varchar(255) | Job location |
| social_links | longtext | Data field |
| status | varchar(255) | Record status |
| company_culture | longtext | Company culture description |
| benefits_offered | longtext | Data field |
| total_jobs_posted | int(11) | Data field |
| active_jobs | int(11) | Number of active jobs |
| total_applications_received | int(11) | Data field |
| profile_views | int(11) | Profile view count |
| is_verified | tinyint(1) | Verification flag |
| is_featured | tinyint(1) | Featured profile flag |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |
| deleted_at | timestamp | Soft delete timestamp |
| founded_year | year(4) | Company founding year |
| headquarters | varchar(255) | Company headquarters |
| specialties | longtext | Data field |
| company_video | varchar(255) | Data field |
| gallery_images | longtext | Data field |
| hiring_process | longtext | Data field |
| contact_email | varchar(255) | Contact email address |
| contact_phone | varchar(255) | Contact phone number |
| meta_title | varchar(255) | Data field |
| meta_description | text | Data field |

## employers

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| company_name | varchar(255) | Company name |
| company_slug | varchar(255) | Data field |
| company_description | text | Company description |
| company_website | varchar(255) | Data field |
| company_logo | varchar(255) | Company logo path |
| company_size | varchar(255) | Company size category |
| industry | varchar(255) | Business industry sector |
| founded_year | year(4) | Company founding year |
| contact_person_name | varchar(255) | Data field |
| contact_person_designation | varchar(255) | Data field |
| business_email | varchar(255) | Data field |
| business_phone | varchar(255) | Data field |
| business_address | text | Data field |
| city | varchar(255) | City |
| state | varchar(255) | State/Province |
| country | varchar(255) | Country |
| postal_code | varchar(255) | Postal code |
| latitude | decimal(10,8) | Geographic latitude |
| longitude | decimal(11,8) | Geographic longitude |
| business_registration_number | varchar(255) | Data field |
| tax_identification_number | varchar(255) | Data field |
| business_documents | longtext | Data field |
| linkedin_url | varchar(255) | LinkedIn profile URL |
| facebook_url | varchar(255) | Facebook profile URL |
| twitter_url | varchar(255) | Twitter profile URL |
| instagram_url | varchar(255) | Instagram profile URL |
| subscription_plan | varchar(255) | Data field |
| subscription_starts_at | timestamp | Data field |
| subscription_ends_at | timestamp | Data field |
| job_posts_limit | int(11) | Data field |
| job_posts_used | int(11) | Data field |
| status | enum('active','inactive','suspended','pending') | Record status |
| is_verified | tinyint(1) | Verification flag |
| is_featured | tinyint(1) | Featured profile flag |
| verified_at | timestamp | Verification timestamp |
| notification_preferences | longtext | Notification preferences |
| settings | longtext | Data field |
| total_jobs_posted | int(11) | Data field |
| total_applications_received | int(11) | Data field |
| total_hires | int(11) | Total successful hires |
| average_rating | decimal(3,2) | Average rating |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## industries

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| name | varchar(255) | Name field |
| slug | varchar(255) | URL-friendly identifier |
| description | text | Job description |
| sort_order | int(11) | Sort order |
| is_active | tinyint(1) | Active status flag |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## job_application_status_histories

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| job_application_id | bigint(20) unsigned | Job application reference |
| status | varchar(255) | Record status |
| notes | text | Status change notes |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## job_applications

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| job_id | bigint(20) unsigned | Job reference |
| user_id | bigint(20) unsigned | Reference to users table |
| employer_id | bigint(20) unsigned | Employer user reference |
| status | varchar(255) | Application status: pending, approved, rejected |
| shortlisted | tinyint(1) | Shortlisted flag |
| applied_date | timestamp | Data field |
| cover_letter | text | Cover letter text |
| resume | varchar(255) | Resume file path |
| interview_type | varchar(255) | Data field |
| interview_date | timestamp | Data field |
| interview_details | text | Data field |
| notes | text | Status change notes |
| preliminary_answers | longtext | Answers to pre-screening questions |
| application_step | enum('basic_info','screening','documents','review','submitted') | Data field |
| profile_updated | tinyint(1) | Data field |
| source | varchar(255) | Data field |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

## job_categories

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| name | varchar(255) | Name field |
| slug | varchar(255) | URL-friendly identifier |
| description | text | Job description |
| icon | varchar(255) | Category icon |
| sort_order | int(11) | Sort order |
| is_active | tinyint(1) | Active status flag |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## job_skills

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| name | varchar(255) | Name field |
| slug | varchar(255) | URL-friendly identifier |
| category | varchar(255) | Skill category |
| description | text | Job description |
| popularity_score | int(11) | Popularity score |
| is_active | tinyint(1) | Active status flag |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## job_types

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| name | varchar(255) | Name field |
| slug | varchar(255) | URL-friendly identifier |
| status | tinyint(1) | Record status |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

## job_user

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## job_views

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| job_id | bigint(20) unsigned | Job reference |
| ip_address | varchar(255) | Viewer IP address |
| user_agent | text | Browser user agent |
| device_type | varchar(255) | Data field |
| referrer | text | Referrer URL |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## jobs

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| employer_id | bigint(20) unsigned | Employer user reference |
| job_type_id | bigint(20) unsigned | Job type reference |
| category_id | bigint(20) unsigned | Job category reference |
| title | varchar(255) | Job title |
| description | text | Job description |
| address | varchar(255) | Data field |
| requirements | text | Job requirements |
| benefits | text | Job benefits |
| salary_min | decimal(12,2) | Minimum salary |
| salary_max | decimal(12,2) | Maximum salary |
| experience_level | enum('entry','intermediate','expert') | Experience level |
| vacancies | int(11) | Data field |
| location_name | varchar(255) | Data field |
| location_address | varchar(255) | Data field |
| barangay | varchar(255) | Data field |
| city | varchar(255) | City |
| province | varchar(255) | Data field |
| latitude | decimal(10,8) | Geographic latitude |
| longitude | decimal(11,8) | Geographic longitude |
| location | varchar(255) | Job location |
| status | tinyint(1) | Record status |
| featured | tinyint(1) | Data field |
| deadline | timestamp | Data field |
| views | int(11) | Data field |
| source | varchar(255) | Data field |
| meta_data | longtext | Data field |
| preliminary_questions | longtext | Pre-screening questions |
| requires_screening | tinyint(1) | Data field |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |
| rejection_reason | text | Data field |
| approved_at | timestamp | Data field |
| rejected_at | timestamp | Data field |

## jobseekers

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| first_name | varchar(255) | First name |
| last_name | varchar(255) | Last name |
| middle_name | varchar(255) | Middle name |
| date_of_birth | date | Date of birth |
| gender | varchar(255) | Gender |
| nationality | varchar(255) | Nationality |
| marital_status | varchar(255) | Marital status |
| phone | varchar(255) | Phone number |
| alternate_phone | varchar(255) | Alternative phone |
| linkedin_url | varchar(255) | LinkedIn profile URL |
| portfolio_url | varchar(255) | Portfolio website URL |
| github_url | varchar(255) | GitHub profile URL |
| current_address | text | Current address |
| permanent_address | text | Permanent address |
| city | varchar(255) | City |
| state | varchar(255) | State/Province |
| country | varchar(255) | Country of residence |
| postal_code | varchar(255) | Postal code |
| current_job_title | varchar(255) | Current job title |
| current_company | varchar(255) | Current company |
| professional_summary | text | Professional summary |
| total_experience_years | int(11) | Total experience years |
| total_experience_months | int(11) | Total experience months |
| skills | longtext | JSON array of skills |
| soft_skills | longtext | Soft skills array |
| languages | longtext | Languages spoken |
| certifications | longtext | Certifications array |
| education | longtext | JSON array of education |
| courses | longtext | Courses completed |
| work_experience | longtext | Work history array |
| projects | longtext | Projects array |
| preferred_job_types | longtext | Preferred job types |
| preferred_categories | longtext | Preferred job categories |
| preferred_locations | longtext | Preferred work locations |
| open_to_remote | tinyint(1) | Open to remote work |
| open_to_relocation | tinyint(1) | Open to relocation |
| expected_salary_min | decimal(10,2) | Expected minimum salary |
| expected_salary_max | decimal(10,2) | Expected maximum salary |
| salary_currency | varchar(255) | Salary currency |
| salary_period | enum('hourly','daily','weekly','monthly','yearly') | Salary period (hourly, monthly, yearly) |
| availability | enum('immediate','1_week','2_weeks','1_month','2_months','3_months') | Availability to start |
| available_from | date | Available start date |
| currently_employed | tinyint(1) | Currently employed flag |
| notice_period_days | int(11) | Notice period in days |
| resume_file | varchar(255) | Resume file path |
| cover_letter_file | varchar(255) | Cover letter file path |
| profile_photo | varchar(255) | Profile photo path |
| portfolio_files | longtext | Portfolio files array |
| notification_preferences | longtext | Notification preferences |
| privacy_settings | longtext | Privacy settings |
| profile_visibility | tinyint(1) | Profile visibility flag |
| allow_recruiter_contact | tinyint(1) | Allow recruiter contact |
| job_alert_preferences | longtext | Job alert preferences |
| facebook_url | varchar(255) | Facebook profile URL |
| twitter_url | varchar(255) | Twitter profile URL |
| instagram_url | varchar(255) | Instagram profile URL |
| profile_status | enum('incomplete','complete','verified','suspended') | Profile completion status |
| is_featured | tinyint(1) | Featured profile flag |
| is_premium | tinyint(1) | Premium account flag |
| premium_expires_at | timestamp | Premium expiration date |
| profile_completion_percentage | decimal(5,2) | Profile completion percentage |
| profile_views | int(11) | Profile view count |
| total_applications | int(11) | Total applications sent |
| interviews_attended | int(11) | Interviews attended count |
| jobs_offered | int(11) | Job offers received |
| average_rating | decimal(3,2) | Average rating |
| search_keywords | text | Search keywords |
| search_score | decimal(8,2) | Search relevance score |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## kyc_data

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| session_id | varchar(255) | KYC session ID |
| status | varchar(255) | Record status |
| didit_status | varchar(255) | Data field |
| first_name | varchar(255) | First name |
| last_name | varchar(255) | Last name |
| full_name | varchar(255) | Data field |
| date_of_birth | date | Date of birth |
| gender | varchar(255) | Gender |
| nationality | varchar(255) | Nationality |
| place_of_birth | varchar(255) | Data field |
| marital_status | varchar(255) | Marital status |
| document_type | varchar(255) | Document type |
| document_number | varchar(255) | Data field |
| document_issue_date | date | Data field |
| document_expiration_date | date | Data field |
| issuing_state | varchar(255) | Data field |
| issuing_state_name | varchar(255) | Data field |
| address | text | Data field |
| formatted_address | text | Data field |
| city | varchar(255) | City |
| region | varchar(255) | Data field |
| country | varchar(255) | Country |
| postal_code | varchar(255) | Postal code |
| latitude | decimal(10,8) | Geographic latitude |
| longitude | decimal(11,8) | Geographic longitude |
| face_match_score | decimal(5,2) | Data field |
| face_match_status | varchar(255) | Data field |
| liveness_score | decimal(5,2) | Data field |
| liveness_status | varchar(255) | Data field |
| id_verification_status | varchar(255) | Data field |
| ip_analysis_status | varchar(255) | Data field |
| age_estimation | decimal(5,2) | Data field |
| ip_address | varchar(255) | Viewer IP address |
| ip_country | varchar(255) | Data field |
| ip_city | varchar(255) | Data field |
| is_vpn_or_tor | tinyint(1) | Data field |
| device_brand | varchar(255) | Data field |
| device_model | varchar(255) | Data field |
| browser_family | varchar(255) | Data field |
| os_family | varchar(255) | Data field |
| front_image_url | text | Data field |
| back_image_url | text | Data field |
| portrait_image_url | text | Data field |
| liveness_video_url | text | Data field |
| raw_payload | longtext | Data field |
| warnings | longtext | Data field |
| verification_method | varchar(255) | Data field |
| didit_created_at | timestamp | Data field |
| verified_at | timestamp | Verification timestamp |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## kyc_verifications

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| session_id | varchar(255) | KYC session ID |
| status | varchar(255) | Record status |
| document_type | varchar(255) | Document type |
| document_number | varchar(255) | Data field |
| firstname | varchar(255) | Data field |
| lastname | varchar(255) | Data field |
| date_of_birth | date | Date of birth |
| gender | varchar(255) | Gender |
| address | text | Data field |
| nationality | varchar(255) | Nationality |
| raw_data | longtext | Data field |
| verification_data | longtext | Verification data JSON |
| completed_at | timestamp | Completion timestamp |
| verified_at | timestamp | Verification timestamp |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## locations

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| name | varchar(255) | Name field |
| type | varchar(255) | Notification type |
| country | varchar(255) | Country |
| state_province | varchar(255) | Data field |
| latitude | decimal(10,8) | Geographic latitude |
| longitude | decimal(11,8) | Geographic longitude |
| job_count | int(11) | Related jobs count |
| is_active | tinyint(1) | Active status flag |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## migrations

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | int(10) unsigned | Primary key identifier |
| migration | varchar(255) | Data field |
| batch | int(11) | Data field |

## notifications

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| user_id | bigint(20) unsigned | Reference to users table |
| title | varchar(255) | Job title |
| message | text | Notification message |
| type | varchar(255) | Notification type |
| data | longtext | Additional notification data |
| action_url | varchar(255) | Action URL |
| read_at | timestamp | Read timestamp |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## personal_access_tokens

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| tokenable_type | varchar(255) | Data field |
| tokenable_id | bigint(20) unsigned | Data field |
| name | varchar(255) | Name field |
| token | varchar(64) | Data field |
| abilities | text | Data field |
| last_used_at | timestamp | Data field |
| expires_at | timestamp | Expiration timestamp |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## saved_jobs

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| job_id | bigint(20) unsigned | Job reference |
| user_id | bigint(20) unsigned | Reference to users table |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## users

| Attribute | Data Type | Description |
|-----------|-----------|-------------|
| id | bigint(20) unsigned | Primary key identifier |
| parent_id | bigint(20) unsigned | Data field |
| name | varchar(255) | Name field |
| email | varchar(255) | Email address |
| email_verified_at | timestamp | Data field |
| password | varchar(255) | Encrypted password |
| mobile | varchar(255) | Mobile phone number |
| designation | varchar(255) | Job title or position |
| job_title | varchar(255) | Data field |
| location | varchar(255) | Job location |
| salary | decimal(10,2) | Salary amount |
| salary_type | varchar(255) | Data field |
| qualification | varchar(255) | Data field |
| language | varchar(255) | Data field |
| categories | varchar(255) | Data field |
| image | varchar(255) | Profile image path |
| role | varchar(255) | User role: admin, employer, jobseeker |
| skills | longtext | JSON array of skills |
| education | text | JSON array of education |
| experience_years | int(11) | Years of experience |
| bio | text | Biography or description |
| address | text | Data field |
| is_verified | tinyint(1) | Verification flag |
| verification_document | varchar(255) | Data field |
| preferred_job_types | longtext | Preferred job types |
| preferred_categories | longtext | Preferred job categories |
| preferred_location | varchar(255) | Data field |
| preferred_salary_range | varchar(255) | Data field |
| phone | varchar(255) | Phone number |
| is_active | tinyint(1) | Active status flag |
| notification_preferences | longtext | Notification preferences |
| privacy_settings | longtext | Privacy settings |
| remember_token | varchar(100) | Data field |
| google_id | varchar(255) | Google OAuth identifier |
| google_token | text | Google OAuth token |
| google_refresh_token | text | Google refresh token |
| profile_image | varchar(255) | Profile image URL |
| two_factor_enabled | tinyint(1) | Data field |
| two_factor_secret | varchar(255) | Data field |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |
| deleted_at | timestamp | Soft delete timestamp |
| kyc_status | varchar(255) | KYC verification status |
| kyc_session_id | varchar(255) | KYC session identifier |
| kyc_inquiry_id | varchar(255) | Data field |
| kyc_completed_at | timestamp | KYC completion timestamp |
| kyc_verified_at | timestamp | KYC verification timestamp |
| kyc_data | longtext | KYC verification data |
| experience_level | varchar(255) | Experience level |
| salary_expectation_min | int(10) unsigned | Minimum salary expectation |
| salary_expectation_max | int(10) unsigned | Maximum salary expectation |

