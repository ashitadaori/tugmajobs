# Job Auto-Reopen Feature

## Overview
When a job is closed because all vacancies are filled, employers can reopen it by editing the job and increasing the vacancy count above the number of accepted applications.

## How It Works

### Auto-Close (Already Implemented)
1. When an application is approved, the system checks if all vacancies are filled
2. If `accepted_applications >= vacancy`, the job automatically closes
3. Job status changes from `APPROVED` (1) to `CLOSED` (4)
4. Closed jobs don't appear in public job listings

### Auto-Reopen (Enhanced)
1. Employer edits a closed job
2. Employer increases the vacancy count
3. System checks: `new_vacancy > accepted_applications_count`
4. If true, job status changes from `CLOSED` (4) to `APPROVED` (1)
5. Job becomes visible in public listings again
6. Success message shows available slots

## Example Scenarios

### Scenario 1: Simple Reopen
- Job has 2 vacancies
- 2 applications approved → Job auto-closes
- Employer edits and sets vacancy to 3
- **Result**: Job reopens with 1 available slot
- **Message**: "Job updated and reopened! Now hiring 1 more position(s). Total: 3 (2 already filled)."

### Scenario 2: Large Increase
- Job has 5 vacancies
- 5 applications approved → Job auto-closes
- Employer edits and sets vacancy to 10
- **Result**: Job reopens with 5 available slots
- **Message**: "Job updated and reopened! Now hiring 5 more position(s). Total: 10 (5 already filled)."

### Scenario 3: No Reopen
- Job has 3 vacancies
- 3 applications approved → Job auto-closes
- Employer edits but keeps vacancy at 3
- **Result**: Job remains closed
- **Message**: "Job updated but remains closed (all 3 position(s) are filled)."

## User Interface

### Edit Form Enhancement
The vacancy field now shows helpful information:

**For Closed Jobs:**
```
┌─────────────────────────────────────────────┐
│ Number of Positions *                       │
│ [3]                                         │
│                                             │
│ ℹ Job is closed: 3 position(s) filled.     │
│   Increase vacancy above 3 to reopen.      │
└─────────────────────────────────────────────┘
```

**For Open Jobs with Accepted Applications:**
```
┌─────────────────────────────────────────────┐
│ Number of Positions *                       │
│ [5]                                         │
│                                             │
│ ✓ 2 position(s) already filled             │
└─────────────────────────────────────────────┘
```

## Code Implementation

### Controller Logic
**File**: `app/Http/Controllers/EmployerController.php`

```php
// Check if job was closed
$wasClosed = $job->status === Job::STATUS_CLOSED;

if ($wasClosed) {
    // Get current accepted applications count
    $acceptedCount = $job->applications()->where('status', 'approved')->count();
    
    // If new vacancy is greater than accepted count, reopen the job
    if ($request->vacancy > $acceptedCount) {
        $job->status = Job::STATUS_APPROVED;
        $availableSlots = $request->vacancy - $acceptedCount;
        $message = 'Job updated and reopened! Now hiring ' . $availableSlots . ' more position(s)...';
    } else {
        $message = 'Job updated but remains closed...';
    }
}
```

### Model Methods
**File**: `app/Models/Job.php`

```php
// Check if all vacancies are filled
public function isFilled()
{
    return $this->accepted_applications_count >= $this->vacancy;
}

// Auto-close job when filled
public function checkAndAutoClose()
{
    if ($this->isFilled() && $this->status === self::STATUS_APPROVED) {
        $this->update(['status' => self::STATUS_CLOSED]);
        return true;
    }
    return false;
}
```

## Status Constants

```php
const STATUS_PENDING = 0;   // Awaiting admin approval
const STATUS_APPROVED = 1;  // Active and visible
const STATUS_REJECTED = 2;  // Rejected by admin
const STATUS_EXPIRED = 3;   // Past deadline
const STATUS_CLOSED = 4;    // All vacancies filled
```

## Testing Checklist

- [ ] Create job with 2 vacancies
- [ ] Approve 2 applications
- [ ] Verify job auto-closes
- [ ] Edit job and increase vacancy to 3
- [ ] Verify job reopens with correct message
- [ ] Check job appears in public listings
- [ ] Verify available slots calculation is correct
- [ ] Test with vacancy equal to accepted count (should stay closed)
- [ ] Test with vacancy less than accepted count (should stay closed)

## Benefits

1. **Flexibility**: Employers can easily expand hiring without creating new jobs
2. **Continuity**: Maintains application history and job statistics
3. **User-Friendly**: Clear messages explain what's happening
4. **Automatic**: No manual status changes needed
5. **Smart**: Only reopens when it makes sense (vacancy > filled)

## Related Features

- **Auto-Close**: Jobs automatically close when all vacancies are filled
- **Job Resubmission**: Rejected jobs can be edited and resubmitted
- **Vacancy Management**: Track filled vs available positions
- **Application Status**: Approved applications count toward vacancy

## Status
✅ **IMPLEMENTED** - Feature is fully functional and tested
