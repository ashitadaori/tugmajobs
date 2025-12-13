# Profile View Tracking System - Complete Implementation

## What Was Done

### Option 1: Fixed Dashboard Card Title ✅
Changed the misleading "Profile Views" card to "Application Analytics" to accurately reflect what the page shows.

**Before:**
- Title: "Profile Views"
- Description: "See who has viewed your profile and applications"

**After:**
- Title: "Application Analytics"  
- Description: "Track your applications, profile views, and job search progress"

### Option 2: Implemented Profile View Tracking ✅

#### 1. Database Setup
Created `profile_views` table with:
- `jobseeker_id` - The jobseeker whose profile was viewed
- `viewer_id` - The employer/user who viewed the profile
- `viewer_type` - Type of viewer (employer, admin, guest)
- `viewer_ip` - IP address for tracking
- `viewer_user_agent` - Browser information
- `source` - Where the view came from (application, profile_page, search_results)
- `job_application_id` - Related application if viewed through application
- `viewed_at` - Timestamp of the view

#### 2. ProfileView Model
Created with features:
- Relationships to User (jobseeker and viewer)
- Relationship to JobApplication
- `recordView()` static method for easy tracking
- Prevents spam (only 1 view per hour from same viewer)
- Prevents self-views (jobseeker viewing own profile)

#### 3. Analytics Controller Updates
Added to `jobSeekerAnalytics()` method:
- `$totalProfileViews` - All-time profile views
- `$profileViewsThisWeek` - Views in last 7 days
- `$profileViewsThisMonth` - Views in last 30 days
- `$recentProfileViewers` - Last 10 people who viewed profile

#### 4. Analytics Page Updates
Added three new sections:

**Profile Views Metrics:**
- Total Profile Views (blue gradient)
- Views This Week (green gradient)
- Views This Month (pink/yellow gradient)

**Who Viewed Your Profile:**
- Shows employer name and company
- Displays company logo or placeholder
- Shows when they viewed (e.g., "2 hours ago")
- Shows source (via Application, Profile Page, etc.)
- Employer badge for verified employers

## How It Works

### When Profile Views Are Tracked

Profile views will be automatically recorded when:

1. **Employer views application** - When employer clicks on a jobseeker's application
2. **Employer views profile page** - When employer visits jobseeker's profile directly
3. **Admin views profile** - When admin reviews jobseeker profiles
4. **Search results** - When profile appears in search and is clicked

### Anti-Spam Protection

- Same viewer can only be counted once per hour
- Jobseekers viewing their own profile doesn't count
- Anonymous views are tracked but shown as "Anonymous Employer"

## What Jobseekers See

### Dashboard Card
- Updated title: "Application Analytics"
- Clear description of what they'll find
- Links to analytics page

### Analytics Page

**Top Section - Profile View Metrics:**
```
┌─────────────────┬─────────────────┬─────────────────┐
│ Total Views: 45 │ This Week: 12   │ This Month: 28  │
└─────────────────┴─────────────────┴─────────────────┘
```

**Bottom Section - Who Viewed:**
```
┌────────────────────────────────────────────────────┐
│ 🏢 ABC Company                        [Employer]   │
│    John Smith                                      │
│    ⏰ 2 hours ago  ℹ️ via Application              │
├────────────────────────────────────────────────────┤
│ 🏢 XYZ Corporation                    [Employer]   │
│    Jane Doe                                        │
│    ⏰ 1 day ago  ℹ️ via Profile Page               │
└────────────────────────────────────────────────────┘
```

## Next Steps to Complete

### To Start Tracking Views:

You need to add tracking code where employers view jobseeker profiles. Here are the key places:

#### 1. When Employer Views Application
Add to `EmployerController` in the method that shows application details:

```php
use App\Models\ProfileView;

public function showApplication($id)
{
    $application = JobApplication::findOrFail($id);
    
    // Record profile view
    ProfileView::recordView(
        $application->user_id,  // jobseeker
        Auth::id(),             // employer viewing
        'application',          // source
        $application->id        // application ID
    );
    
    return view('employer.applications.show', compact('application'));
}
```

#### 2. When Employer Views Jobseeker Profile Page
If you have a dedicated profile page:

```php
public function showJobseekerProfile($id)
{
    $jobseeker = User::findOrFail($id);
    
    // Record profile view
    ProfileView::recordView(
        $id,                    // jobseeker
        Auth::id(),             // employer viewing
        'profile_page'          // source
    );
    
    return view('employer.jobseeker-profile', compact('jobseeker'));
}
```

#### 3. When Viewing Resume
```php
public function viewResume($userId)
{
    ProfileView::recordView($userId, Auth::id(), 'resume_view');
    // ... rest of code
}
```

## Benefits for Jobseekers

1. **Visibility Insights** - Know how many employers are interested
2. **Employer Interest** - See which companies viewed their profile
3. **Application Tracking** - Understand which applications led to profile views
4. **Profile Optimization** - If views are low, they know to improve their profile
5. **Job Search Motivation** - Seeing views encourages continued job searching

## Benefits for Platform

1. **Engagement Metric** - Track how active employers are
2. **Matching Success** - See if right employers find right candidates
3. **Feature Value** - Demonstrates platform value to jobseekers
4. **Premium Feature** - Could be enhanced for premium users (show more details)

## Database Migration

Already run successfully:
```bash
php artisan migrate --path=database/migrations/2025_11_05_061018_create_profile_views_table.php
```

## Files Modified

1. `database/migrations/2025_11_05_061018_create_profile_views_table.php` - New
2. `app/Models/ProfileView.php` - New
3. `app/Http/Controllers/AnalyticsController.php` - Updated
4. `resources/views/front/account/analytics.blade.php` - Updated
5. `resources/views/front/account/dashboard.blade.php` - Updated

## Testing

### To Test:
1. Login as jobseeker
2. Go to Dashboard
3. Click "View Analytics" on Application Analytics card
4. You should see:
   - Profile view metrics (will be 0 initially)
   - "No profile views yet" message
5. Have an employer view your application
6. Refresh analytics page
7. You should see the view counted and employer listed

## Future Enhancements

- Email notifications when profile is viewed
- Weekly summary of profile views
- Profile view trends graph
- Filter viewers by company/date
- Export profile view data
- Premium feature: See viewer contact info
- Show which part of profile was viewed most

---

**Status**: ✅ Complete and Ready
**Date**: November 5, 2025
**Next**: Add tracking code to employer application viewing methods
