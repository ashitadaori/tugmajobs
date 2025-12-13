# Guest Browse Jobs Error - FIXED

## Problem
When browsing jobs by category as a guest (not logged in), the page crashed with error:
```
Attempt to read property "unreadNotifications" on null
```

## Root Cause
The jobseeker notification dropdown component (`jobseeker-notification-dropdown.blade.php`) was trying to access `Auth::user()->unreadNotifications` without checking if the user was authenticated first.

The jobs browsing page (`modern-jobs.blade.php`) extends the `layouts.jobseeker` layout, which includes the notification dropdown. This layout is used by both:
- Authenticated jobseekers (who should see notifications)
- Guest users (who are just browsing jobs)

## Solution
Wrapped the entire notification dropdown component with `@auth` directive:

```blade
@auth
<div class="jobseeker-notif-wrapper">
    <!-- notification dropdown content -->
</div>
@endauth
```

This ensures the notification dropdown only renders when a user is logged in.

## Files Changed
- `resources/views/components/jobseeker-notification-dropdown.blade.php`

## Testing
1. **As Guest:**
   - Go to homepage
   - Click "Browse Jobs" or any category
   - Should work without errors
   - No notification bell should appear

2. **As Logged-in Jobseeker:**
   - Login as jobseeker
   - Browse jobs
   - Should see notification bell
   - Notifications should work normally

## Impact
- ✅ Guests can now browse jobs without errors
- ✅ Logged-in jobseekers still see their notifications
- ✅ No functionality lost
- ✅ Better user experience for both guests and authenticated users
