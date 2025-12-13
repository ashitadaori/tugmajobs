# Multi-Table Architecture Implementation

## Overview

We have successfully implemented a comprehensive multi-table architecture for the job portal application. Instead of removing the `users` table (which would break Laravel's authentication system), we've created a **role-based separation of concerns** approach.

## New Architecture

### Core Authentication Table
- **`users`** - Maintains Laravel authentication and basic user info
  - Handles login, password, email verification
  - Stores basic info: name, email, role, mobile, etc.
  - Contains KYC status and session info for security
  - **NEVER REMOVE THIS TABLE** - Required for Laravel Auth

### Role-Specific Tables

#### 1. **`jobseekers`** Table - Comprehensive Jobseeker Profiles
- **Purpose**: Store detailed jobseeker information and preferences
- **Relationship**: `belongsTo(User::class)`
- **Key Features**:
  - Personal info (first_name, last_name, date_of_birth, etc.)
  - Professional details (experience, skills, certifications)
  - Job preferences (salary range, locations, job types)
  - Documents (resume, portfolio, cover letter)
  - Statistics (profile views, applications, interviews)
  - Profile completion tracking
  - Availability and employment status

#### 2. **`employers`** Table - Company Information
- **Purpose**: Store employer/company-specific data
- **Relationship**: `belongsTo(User::class)`
- **Key Features**:
  - Company details (name, size, industry, description)
  - Contact information and addresses
  - Business registration details  
  - Subscription and plan management
  - Company statistics and ratings
  - Social media presence

#### 3. **`admins`** Table - Administrative Roles
- **Purpose**: Store admin-specific permissions and activity
- **Relationship**: `belongsTo(User::class)`
- **Key Features**:
  - Admin levels (super_admin, admin, moderator)
  - Granular permissions system
  - Login history and activity tracking
  - Department and position info

#### 4. **`kyc_data`** Table - KYC Verification Details
- **Purpose**: Store detailed KYC verification results from Didit
- **Relationship**: `belongsTo(User::class)`
- **Key Features**:
  - Complete personal information from ID verification
  - Document details and verification scores
  - Device and IP information
  - Image URLs and verification timestamps
  - Raw payload storage for audit

### Supporting Reference Tables

#### 5. **`job_categories`** - Job Classification
- Standardized job categories (IT, Marketing, Finance, etc.)
- Icons, descriptions, and sorting

#### 6. **`job_skills`** - Skills Management  
- Technical, soft skills, and languages
- Popularity scoring and categorization

#### 7. **`industries`** - Industry Classification
- Company industry categorization
- Structured industry data

#### 8. **`locations`** - Geographic Data
- Philippine cities and regions
- GPS coordinates for location-based features

#### 9. **`company_sizes`** - Company Size Standards
- Standardized company size ranges
- Employee count categorization

## Benefits of This Architecture

### 1. **Separation of Concerns**
```php
// Clean separation of user types
$user = User::find(1);
$jobseeker = $user->jobseeker;  // Jobseeker-specific data
$employer = $user->employer;    // Employer-specific data
$admin = $user->admin;          // Admin-specific data
```

### 2. **Scalable Data Structure**
- Each role has dedicated, optimized tables
- Easy to add role-specific features
- Better database performance with proper indexing

### 3. **Enhanced Querying**
```php
// Find jobseekers with specific skills in Manila
$jobseekers = Jobseeker::whereJsonContains('skills', 'PHP')
    ->where('city', 'Manila')
    ->where('profile_visibility', true)
    ->get();

// Find employers in tech industry
$employers = Employer::where('industry', 'Technology')
    ->where('is_verified', true)
    ->get();
```

### 4. **Profile Completion Tracking**
```php
$jobseeker = Jobseeker::find(1);
$completionPercentage = $jobseeker->calculateProfileCompletion();
$jobseeker->updateProfileStatus(); // Auto-update based on completion
```

### 5. **Advanced Matching**
```php
$jobseeker = Jobseeker::find(1);
$job = Job::find(1);
$matchData = $jobseeker->matchesJob($job);
// Returns: ['matches' => [...], 'score' => 85.5, 'is_good_match' => true]
```

## Data Migration

### ✅ Completed Migrations
1. **Created new table structures** - All tables created successfully
2. **Migrated existing data** - Transferred user data to appropriate role tables
3. **Added reference data** - Populated categories, skills, locations, etc.
4. **Updated models** - Added relationships and business logic

### Migration Summary
- **Jobseekers**: Migrated from `users` table (name → first_name/last_name, skills, education, etc.)
- **Employers**: Created basic profiles for existing employer users  
- **Admins**: Created admin profiles with appropriate permissions
- **KYC Data**: Enhanced webhook processing to save detailed verification data

## Model Relationships

### User Model (Hub)
```php
class User extends Authenticatable 
{
    // Role-specific relationships
    public function jobseeker(): HasOne
    public function employer(): HasOne  
    public function admin(): HasOne
    
    // KYC relationships
    public function kycData(): HasMany
    public function latestKycData(): HasOne
    
    // Role checking methods
    public function isJobSeeker(): bool
    public function isEmployer(): bool
    public function isAdmin(): bool
}
```

### Jobseeker Model
```php
class Jobseeker extends Model
{
    // Relationships
    public function user(): BelongsTo
    public function jobApplications(): HasMany
    public function savedJobs(): BelongsToMany
    
    // Business logic
    public function calculateProfileCompletion(): float
    public function getMatchingJobs(): Builder
    public function matchesJob(Job $job): array
    public function isAvailable(): bool
}
```

## Enhanced Features

### 1. **Profile Completion System**
- Automatic calculation based on filled fields
- Weighted scoring (resume = 15 points, skills = 10 points, etc.)
- Status updates (incomplete → complete → verified)

### 2. **Advanced Job Matching**
- Skills compatibility scoring
- Salary range matching
- Location preference matching
- Experience level matching
- Overall match score calculation

### 3. **Comprehensive KYC Storage**
- Detailed verification data storage
- Document information tracking
- Device and security data
- Audit trail maintenance

### 4. **Enhanced Search & Filtering**
- Skills-based search with JSON queries
- Location-based filtering with GPS data
- Salary range filtering
- Experience level filtering
- Profile completion filtering

## Usage Examples

### Creating a Complete Jobseeker Profile
```php
// Create user account
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'role' => 'jobseeker',
    'password' => Hash::make('password')
]);

// Create detailed jobseeker profile  
$jobseeker = Jobseeker::create([
    'user_id' => $user->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'professional_summary' => 'Experienced PHP Developer...',
    'skills' => ['PHP', 'Laravel', 'JavaScript', 'MySQL'],
    'total_experience_years' => 5,
    'expected_salary_min' => 50000,
    'expected_salary_max' => 80000,
    'preferred_locations' => ['Manila', 'Makati', 'Taguig'],
    'open_to_remote' => true
]);

// Calculate and update profile completion
$completion = $jobseeker->calculateProfileCompletion();
```

### Finding Matching Candidates
```php
// Find candidates for a PHP developer job in Manila
$candidates = Jobseeker::whereJsonContains('skills', 'PHP')
    ->where('city', 'Manila')
    ->where('total_experience_years', '>=', 3)
    ->where('expected_salary_max', '<=', 70000)
    ->where('profile_visibility', true)
    ->orderBy('profile_completion_percentage', 'desc')
    ->get();
```

### Enhanced KYC Processing
```php
// After webhook processes KYC data
$user = User::find(1);
$kycData = $user->latestKycData;

if ($kycData && $kycData->isVerified()) {
    $personalInfo = [
        'name' => $kycData->display_name,
        'age' => $kycData->age,
        'address' => $kycData->full_address
    ];
}
```

## Next Steps & Recommendations

### 1. **Frontend Updates**
- Update registration forms to collect role-specific data
- Create dedicated profile management interfaces
- Implement profile completion progress bars

### 2. **API Enhancements**
- Create role-specific API endpoints
- Implement advanced search APIs
- Add job matching APIs

### 3. **Business Logic**
- Implement job recommendation algorithms
- Add profile scoring and ranking
- Create employer-jobseeker matching system

### 4. **Analytics & Reporting**
- Track profile completion rates
- Monitor job application success rates  
- Analyze user engagement by role

## Important Notes

⚠️ **DO NOT REMOVE the `users` table** - It's required for Laravel authentication

✅ **Keep existing authentication flow** - Login, registration, and sessions work unchanged

✅ **Maintain backward compatibility** - Existing code that accesses user data still works

✅ **Progressive enhancement** - New features use the enhanced tables, existing features continue to work

This architecture provides a solid foundation for scaling the job portal with proper separation of concerns while maintaining the integrity of Laravel's authentication system.
