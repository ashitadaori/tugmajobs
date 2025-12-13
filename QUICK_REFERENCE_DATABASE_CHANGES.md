# Quick Reference: Database Schema Changes

**Last Updated**: November 14, 2025

---

## 🔄 What Changed - Quick Summary

### Users Table
**REMOVED** (30 fields deleted):
- ❌ skills, education, experience_years, bio
- ❌ job_title, designation, qualification, language
- ❌ preferred_job_types, preferred_categories, preferred_location
- ❌ salary, salary_type, salary_expectation_min, salary_expectation_max
- ❌ address, location, resume, mobile
- ❌ parent_id, is_verified, verification_document
- ❌ kyc_inquiry_id, two_factor_enabled, two_factor_secret

**KEPT** (20 fields):
- ✅ Auth: id, name, email, password, email_verified_at, role
- ✅ Contact: phone
- ✅ Images: image, profile_image
- ✅ Status: is_active
- ✅ KYC: kyc_status, kyc_session_id, kyc_data, kyc_completed_at, kyc_verified_at
- ✅ OAuth: google_id, google_token, google_refresh_token
- ✅ Settings: notification_preferences, privacy_settings

### JobSeekers Table
**ADDED**:
- ✅ current_salary
- ✅ current_salary_currency
- ✅ deleted_at (soft deletes)

### Employers Table
**ADDED**:
- ✅ gallery_images
- ✅ company_video
- ✅ hiring_process
- ✅ company_culture
- ✅ benefits_offered
- ✅ specialties
- ✅ meta_title
- ✅ meta_description
- ✅ profile_views
- ✅ active_jobs
- ✅ deleted_at (soft deletes)

**REMOVED**:
- ❌ `employer_profiles` table (completely dropped)

---

## 💻 Code Changes - What Developers Need to Know

### ❌ OLD CODE (Will Break)

```php
// OLD - Accessing job seeker data from User model
$skills = $user->skills; // ❌ Field no longer exists
$bio = $user->bio; // ❌ Field no longer exists
$resume = $user->resume; // ❌ Field no longer exists

// OLD - Using EmployerProfile model
use App\Models\EmployerProfile; // ❌ Model will be removed
$profile = EmployerProfile::where('user_id', $user->id)->first(); // ❌ Table dropped

// OLD - Creating employer profile
EmployerProfile::create(['user_id' => $user->id]); // ❌ Wrong model
```

### ✅ NEW CODE (Correct)

```php
// NEW - Accessing job seeker data
$skills = $user->jobSeekerProfile->skills; // ✅ From profile table
$bio = $user->jobSeekerProfile->professional_summary; // ✅ Renamed field
$resume = $user->jobSeekerProfile->resume_file; // ✅ From profile table

// NEW - Accessing employer data
use App\Models\Employer; // ✅ Correct model
$employer = Employer::where('user_id', $user->id)->first(); // ✅ Correct table
$employer = $user->employer; // ✅ Or use relationship

// NEW - Creating employer profile
Employer::create([
    'user_id' => $user->id,
    'company_name' => 'Company Name',
    'status' => 'draft'
]); // ✅ Correct model and required fields
```

---

## 🔍 Common Scenarios

### Scenario 1: User Registration

**Job Seeker Registration:**
```php
// Create user
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => 'jobseeker'
]);

// Create profile (REQUIRED!)
JobSeekerProfile::create(['user_id' => $user->id]);
```

**Employer Registration:**
```php
// Create user
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => 'employer'
]);

// Create profile (REQUIRED!)
Employer::create([
    'user_id' => $user->id,
    'company_name' => 'Company Name',
    'status' => 'draft'
]);
```

### Scenario 2: Displaying User Info

**Job Seeker:**
```php
// In controller
$user = Auth::user();
$profile = $user->jobSeekerProfile;

// In view
{{ $profile->skills }} // JSON array
{{ $profile->professional_summary }}
{{ $profile->current_job_title }}
{{ $profile->current_salary }} // NEW field!
```

**Employer:**
```php
// In controller
$user = Auth::user();
$employer = $user->employer;

// In view
{{ $employer->company_name }}
{{ $employer->company_description }}
{{ $employer->gallery_images }} // NEW field!
{{ $employer->company_video }} // NEW field!
```

### Scenario 3: Updating Profiles

**Updating Job Seeker:**
```php
$user->jobSeekerProfile->update([
    'professional_summary' => $request->bio,
    'skills' => $request->skills,
    'current_salary' => $request->salary, // NEW field!
    'current_salary_currency' => 'PHP'
]);
```

**Updating Employer:**
```php
$user->employer->update([
    'company_name' => $request->company_name,
    'company_description' => $request->description,
    'gallery_images' => $request->gallery, // NEW field!
    'company_video' => $request->video_url // NEW field!
]);
```

---

## 🗂️ Model Relationships

### User Model

```php
// Get job seeker profile
$profile = $user->jobSeekerProfile;

// Get employer profile
$employer = $user->employer;

// Check role
if ($user->isJobSeeker()) { }
if ($user->isEmployer()) { }
```

### JobSeekerProfile Model

```php
// Get user
$user = $jobSeekerProfile->user;
```

### Employer Model

```php
// Get user
$user = $employer->user;

// Get jobs
$jobs = $employer->jobs;
$activeJobs = $employer->activeJobs();
```

---

## 🚨 Breaking Changes Alert

### What Will Break

1. **Direct access to user fields:**
   - `$user->skills` ❌
   - `$user->bio` ❌
   - `$user->resume` ❌
   - `$user->job_title` ❌
   - etc.

2. **EmployerProfile model usage:**
   - `use App\Models\EmployerProfile` ❌
   - `EmployerProfile::create()` ❌
   - `$user->employerProfile` ❌

3. **Forms posting to removed fields:**
   - Forms submitting to `user.bio` need to change to `jobseeker_profile.professional_summary`
   - Forms submitting to `user.skills` need to change to `jobseeker_profile.skills`

### How to Fix

**Step 1**: Search your codebase for direct user field access:
```bash
grep -r "\$user->skills" app/
grep -r "\$user->bio" app/
grep -r "EmployerProfile" app/
```

**Step 2**: Update to use profile relationships:
```php
// Change:
$user->skills
// To:
$user->jobSeekerProfile->skills

// Change:
EmployerProfile::create()
// To:
Employer::create()
```

**Step 3**: Update form field names in views if needed

---

## ✅ Migration Execution

All migrations have been run successfully. If you need to rollback (development only):

```bash
# Rollback all 5 migrations (in reverse order)
php artisan migrate:rollback --step=5
```

**⚠️ WARNING**: Rollback restores table structures but NOT data!

---

## 📞 Quick Help

**Problem**: "Field 'skills' doesn't exist on users table"
**Solution**: Access via `$user->jobSeekerProfile->skills` instead

**Problem**: "Class EmployerProfile not found"
**Solution**: Use `App\Models\Employer` instead

**Problem**: "employer_profiles table doesn't exist"
**Solution**: Query `employers` table instead

**Problem**: "Registration not creating profile"
**Solution**: Check that controllers call `JobSeekerProfile::create()` or `Employer::create()` after user creation

---

## 📚 Full Documentation

- **Complete Details**: [FINAL_DATABASE_IMPLEMENTATION_SUMMARY.md](FINAL_DATABASE_IMPLEMENTATION_SUMMARY.md)
- **Technical Specs**: [DATABASE_SCHEMA_IMPROVEMENTS_COMPLETED.md](DATABASE_SCHEMA_IMPROVEMENTS_COMPLETED.md)

---

**Status**: ✅ ALL CHANGES COMPLETE - Ready for Testing
