# Job Vacancy Not Saving - Debug Guide

## Problem
When editing a job and changing the vacancy number, it appears to save but reverts back to the original value (or 1) when viewing the job again.

## Verification Steps

### 1. Check Database Directly
Run this SQL query to see the actual vacancy value in the database:

```sql
SELECT id, title, vacancy, status FROM jobs WHERE id = YOUR_JOB_ID;
```

### 2. Check Laravel Logs
The controller now has detailed logging. Check `storage/logs/laravel.log` for entries like:

```
Job update request received
- job_id: X
- request_vacancy: Y (what you entered)
- current_vacancy: Z (what's in DB before save)
```

And:

```
Job updated/resubmitted
- job_id: X
- old_vacancy: Y
- new_vacancy: Z (what was saved)
- save_result: true/false
```

### 3. Test Direct Database Update
Run the test script:

```bash
php test_job_vacancy_update.php
```

This will verify that the database field itself can be updated.

## Possible Causes

### 1. Browser Cache
- Clear browser cache
- Try in incognito/private mode
- Hard refresh (Ctrl+F5)

### 2. Form Not Submitting Correct Value
- Open browser DevTools (F12)
- Go to Network tab
- Submit the form
- Check the POST request payload
- Verify `vacancy` field has the correct value

### 3. Validation Failing Silently
- Check if there are validation errors
- Look for error messages on the page
- Check Laravel logs for validation failures

### 4. Database Transaction Rollback
- Check if there's an exception after save
- Look for error logs
- Verify database connection is stable

### 5. Another Process Updating the Job
- Check if there are any observers or event listeners
- Look for scheduled tasks that might update jobs
- Check for database triggers

## Debug Steps

### Step 1: Add Console Log to Form
Add this to the edit form before the closing `</form>` tag:

```javascript
<script>
document.getElementById('editJobForm').addEventListener('submit', function(e) {
    const vacancy = document.getElementById('vacancy').value;
    console.log('Submitting form with vacancy:', vacancy);
});
</script>
```

### Step 2: Check Network Request
1. Open DevTools (F12)
2. Go to Network tab
3. Submit the form
4. Find the POST request to `/employer/jobs/{id}`
5. Check the "Payload" or "Form Data" section
6. Verify `vacancy` has the correct value

### Step 3: Check Response
1. In the same Network request
2. Check the Response tab
3. Look for the success message
4. Verify it says "Job updated successfully"

### Step 4: Check Database Immediately
Right after saving, run this SQL:

```sql
SELECT id, title, vacancy, status, updated_at 
FROM jobs 
WHERE id = YOUR_JOB_ID;
```

Compare the `updated_at` timestamp to confirm the save happened.

## Quick Fix Attempts

### Attempt 1: Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Attempt 2: Check Database Column Type
```sql
DESCRIBE jobs;
```

Verify `vacancy` column is INT or similar numeric type.

### Attempt 3: Try Direct Update via Tinker
```bash
php artisan tinker
```

Then:
```php
$job = App\Models\Job::find(YOUR_JOB_ID);
$job->vacancy = 5;
$job->save();
$job->refresh();
echo $job->vacancy; // Should show 5
```

## Expected Behavior

When you:
1. Edit a job with vacancy = 2
2. Change it to vacancy = 5
3. Click "Save Changes"

You should see:
- Success toast: "Job updated successfully"
- Redirect to jobs list
- Job shows vacancy = 5
- Database has vacancy = 5

## Files to Check

1. `app/Http/Controllers/EmployerController.php` - updateJob method
2. `app/Models/Job.php` - fillable array, observers
3. `resources/views/front/account/employer/jobs/edit.blade.php` - form
4. `storage/logs/laravel.log` - error logs
5. Database - actual values

## Contact Info

If the issue persists after these checks, provide:
1. Laravel log entries for the update
2. Network request payload screenshot
3. Database query result
4. Browser console errors (if any)
