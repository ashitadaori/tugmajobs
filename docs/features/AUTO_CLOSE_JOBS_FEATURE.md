# ✅ Auto-Close Jobs When Vacancies Filled

## Feature Overview
Automatically close job postings when all vacancies are filled. When an employer accepts enough applicants to fill all available positions, the job is automatically hidden from jobseekers.

## How It Works

### Example Scenario:
1. **Employer posts job** with 2 vacancies
2. **5 jobseekers apply** for the position
3. **Employer accepts 2 applicants** 
4. **Job automatically closes** ✅
5. **Job no longer visible** to other jobseekers
6. **Remaining 3 applicants** can still see their application status

## Implementation Details

### 1. Job Model Updates
**File:** `app/Models/Job.php`

#### New Methods Added:

**`getAcceptedApplicationsCountAttribute()`**
- Counts approved/accepted applications for the job
- Returns integer count

**`isFilled()`**
- Checks if accepted applications >= vacancy count
- Returns boolean

**`checkAndAutoClose()`**
- Automatically closes job if filled
- Only closes approved jobs
- Returns true if closed, false otherwise

**`scopeOpen()`**
- Query scope to get only open jobs
- Excludes closed/filled jobs
- Can be used: `Job::open()->get()`

### 2. Auto-Close Trigger
**File:** `app/Http/Controllers/EmployerController.php`

**Method:** `updateApplicationStatus()`

When employer approves an application:
```php
if ($request->status === 'approved') {
    $job = $application->job;
    if ($job->checkAndAutoClose()) {
        \Log::info('Job auto-closed: All vacancies filled');
    }
}
```

### 3. Job Listing Updates

#### JobsController (Browse Jobs Page)
**File:** `app/Http/Controllers/JobsController.php`

```php
// Exclude closed jobs from listings
$query = Job::where('status', Job::STATUS_APPROVED)
            ->where('status', '!=', Job::STATUS_CLOSED);
```

#### HomeController (Homepage)
**File:** `app/Http/Controllers/HomeController.php`

```php
// Featured and latest jobs exclude closed jobs
$featuredJobs = Job::where('status', Job::STATUS_APPROVED)
                   ->where('status', '!=', Job::STATUS_CLOSED)
                   ->get();
```

## Job Status Flow

```
PENDING (0) → Admin Approval → APPROVED (1) → Accepting Applicants
                                    ↓
                            Vacancies Filled?
                                    ↓
                                  YES
                                    ↓
                              CLOSED (4) → Hidden from Jobseekers
```

## Database Schema

### Jobs Table
- `vacancy` (integer) - Number of positions available
- `status` (integer) - Job status (0-4)

### Job Status Constants
```php
const STATUS_PENDING = 0;   // Waiting admin approval
const STATUS_APPROVED = 1;  // Active and visible
const STATUS_REJECTED = 2;  // Rejected by admin
const STATUS_EXPIRED = 3;   // Past deadline
const STATUS_CLOSED = 4;    // Vacancies filled
```

### Job Applications Table
- `status` (string) - Application status

### Application Status Constants
```php
const STATUS_PENDING = 'pending';
const STATUS_APPROVED = 'approved';  // Counts toward vacancy
const STATUS_REJECTED = 'rejected';
```

## User Experience

### For Employers:
1. Post job with vacancy count (e.g., 2 positions)
2. Review applications
3. Accept qualified candidates
4. **System automatically closes job** when 2 accepted
5. Job status changes to "Closed" in Job Management
6. Can still view all applications
7. Can manually reopen if needed (future feature)

### For Jobseekers:
1. Browse available jobs
2. Apply to open positions
3. **Closed jobs don't appear** in search results
4. **Already applied jobs** still visible in "My Applications"
5. Can see if job was closed after applying

## Benefits

✅ **Automatic Management** - No manual closing needed
✅ **Better UX** - Jobseekers only see available positions
✅ **Accurate Listings** - No applying to filled positions
✅ **Time Saving** - Employers don't need to manually close
✅ **Fair Process** - First accepted applicants fill the spots

## Edge Cases Handled

### What if vacancy is 0 or null?
- Job never auto-closes
- Employer can accept unlimited applicants

### What if employer rejects an accepted applicant?
- Future enhancement: Auto-reopen job
- Currently: Stays closed (employer can manually reopen)

### What if employer wants to hire more than vacancy?
- Can continue accepting (no hard limit)
- Job closes when count reaches vacancy number

### What about pending applications?
- Remain visible to employer
- Jobseeker can still track status
- Just can't apply to closed jobs

## Testing Steps

### Test 1: Basic Auto-Close
1. Create job with vacancy = 2
2. Have 3 jobseekers apply
3. Accept 1st applicant → Job stays open
4. Accept 2nd applicant → **Job auto-closes** ✅
5. Verify job not visible in browse page
6. Verify 3rd applicant can still see their pending application

### Test 2: Single Vacancy
1. Create job with vacancy = 1
2. Have 2 jobseekers apply
3. Accept 1 applicant → **Job auto-closes immediately** ✅

### Test 3: No Vacancy Limit
1. Create job with vacancy = 0 or null
2. Accept multiple applicants
3. Job stays open (never auto-closes) ✅

### Test 4: Job Visibility
1. Browse jobs page → Closed jobs not shown ✅
2. Homepage → Closed jobs not in featured/latest ✅
3. Direct link → Can still view job details (future: show "Position Filled")
4. Employer dashboard → Can see closed jobs ✅

## Future Enhancements

### Possible Additions:
1. **Manual Reopen** - Let employers reopen closed jobs
2. **Notification** - Notify employer when job auto-closes
3. **Badge** - Show "Position Filled" badge on closed jobs
4. **Analytics** - Track time-to-fill metrics
5. **Waitlist** - Allow applications to closed jobs as backup
6. **Auto-Reopen** - Reopen if accepted applicant withdraws

## Logging

Auto-close events are logged:
```php
\Log::info('Job #' . $job->id . ' auto-closed: All ' . $job->vacancy . ' vacancies filled');
```

Check logs at: `storage/logs/laravel.log`

---

**Status:** ✅ Implemented
**Date:** November 7, 2025
**Files Modified:** 3
- `app/Models/Job.php`
- `app/Http/Controllers/EmployerController.php`
- `app/Http/Controllers/JobsController.php`
- `app/Http/Controllers/HomeController.php`
