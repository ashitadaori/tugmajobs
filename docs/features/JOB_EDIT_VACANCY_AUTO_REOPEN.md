# ✅ Job Edit: Vacancy Update & Auto-Reopen Feature

## Issues Fixed

### 1. ❌ Vacancy field not saving
**Problem:** Pag edit sa job, ang vacancy changes wala ma-save
**Solution:** Added `vacancy` to validation rules and update logic

### 2. ❌ Closed jobs don't reopen when vacancy increased
**Problem:** Pag naa nay vacant balik, ang job wala mo-appear sa browse
**Solution:** Added auto-reopen logic when vacancy is increased

## Implementation

### 1. Added Vacancy to Validation
**File:** `app/Http/Controllers/EmployerController.php`

```php
$rules = [
    // ... other rules
    'vacancy' => 'required|integer|min:1|max:100',
];
```

### 2. Added Vacancy to Update Logic
```php
$job->vacancy = $request->vacancy;
```

### 3. Auto-Reopen Logic
```php
// If job was closed, check if we should reopen it
elseif ($wasClosed) {
    // Get current accepted applications count
    $acceptedCount = $job->applications()->where('status', 'approved')->count();
    
    // If new vacancy is greater than accepted count, reopen the job
    if ($request->vacancy > $acceptedCount) {
        $job->status = Job::STATUS_APPROVED;
        $message = 'Job updated and reopened!';
    }
}
```

## How It Works

### Scenario 1: Job is Closed (2/2 filled)
1. Employer edits job
2. Changes vacancy from 2 to 5
3. System checks: 5 > 2 (accepted)
4. ✅ **Job reopens automatically**
5. ✅ **Appears in browse page**
6. Message: "Job updated and reopened! Now hiring 5 positions (2 already filled)."

### Scenario 2: Job is Closed (3/3 filled)
1. Employer edits job
2. Changes vacancy from 3 to 3 (no change)
3. System checks: 3 = 3 (accepted)
4. ❌ **Job stays closed**
5. Message: "Job updated but remains closed (all 3 positions are filled)."

### Scenario 3: Job is Closed (2/2 filled)
1. Employer edits job
2. Changes vacancy from 2 to 1
3. System checks: 1 < 2 (accepted)
4. ❌ **Job stays closed**
5. Message: "Job updated but remains closed (all 1 positions are filled)."

### Scenario 4: Job is Open
1. Employer edits job
2. Changes vacancy from 5 to 3
3. System checks: Not closed
4. ✅ **Job stays open**
5. Message: "Job updated successfully."

## Status Flow

```
CLOSED (4) → Edit Vacancy → Check Accepted Count
                                    ↓
                        New Vacancy > Accepted?
                                    ↓
                            YES              NO
                             ↓                ↓
                    APPROVED (1)      CLOSED (4)
                    (Reopened)        (Stays Closed)
                         ↓
                Visible in Browse
```

## User Messages

### Reopened:
```
"Job updated and reopened! Now hiring 5 positions (2 already filled)."
```

### Stays Closed:
```
"Job updated but remains closed (all 3 positions are filled)."
```

### Normal Update:
```
"Job updated successfully."
```

## Testing Steps

### Test 1: Reopen Closed Job
1. Have a job with 2 vacancies, 2 accepted (CLOSED)
2. Edit job, change vacancy to 5
3. Save changes
4. ✅ Job should reopen (status = APPROVED)
5. ✅ Job appears in browse page
6. ✅ Shows message about reopening

### Test 2: Keep Closed
1. Have a job with 2 vacancies, 2 accepted (CLOSED)
2. Edit job, keep vacancy at 2
3. Save changes
4. ✅ Job stays closed (status = CLOSED)
5. ✅ Job NOT in browse page
6. ✅ Shows message about staying closed

### Test 3: Normal Edit
1. Have an open job with 5 vacancies, 1 accepted
2. Edit job, change title or description
3. Save changes
4. ✅ Job stays open
5. ✅ Normal success message

## Benefits

✅ **Automatic Reopening** - No manual status change needed
✅ **Smart Logic** - Only reopens if there are actual vacancies
✅ **Clear Messages** - Employer knows what happened
✅ **Seamless UX** - Job appears in browse immediately
✅ **Logging** - All reopens are logged for tracking

## Database Impact

### Before Update:
```sql
SELECT id, title, vacancy, status, 
       (SELECT COUNT(*) FROM job_applications 
        WHERE job_id = jobs.id AND status = 'approved') as accepted
FROM jobs WHERE id = X;

-- Result: id=5, vacancy=2, status=4 (CLOSED), accepted=2
```

### After Update (Vacancy 2→5):
```sql
-- Same query
-- Result: id=5, vacancy=5, status=1 (APPROVED), accepted=2
```

### Job Now Visible:
```sql
SELECT * FROM jobs 
WHERE status = 1 
  AND status != 4;
-- Job #5 appears in results
```

## Edge Cases

### What if employer decreases vacancy below accepted count?
- Job stays closed
- Message: "Job updated but remains closed"
- Example: 3 accepted, change vacancy to 2 → stays closed

### What if employer increases vacancy but still not enough?
- Job stays closed
- Example: 5 accepted, change vacancy from 5 to 6 → reopens (6 > 5)

### What if job is pending or rejected?
- Normal update flow
- No auto-reopen logic
- Status unchanged

## Logging

All reopens are logged:
```php
\Log::info('Job #5 reopened: Vacancy=5, Accepted=2');
```

Check: `storage/logs/laravel.log`

---

**Status:** ✅ Implemented
**Date:** November 7, 2025
**Files Modified:** 1
- `app/Http/Controllers/EmployerController.php`

**Related Features:**
- Auto-close when vacancies filled
- Job browse filtering
- Employer job management
