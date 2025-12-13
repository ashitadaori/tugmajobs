# Admin Job Posting - Company Name Now Optional

## Change Summary
Made the company name field optional for admin job postings to support partners who only provide job vacancies without company information.

## What Changed

### 1. Form Updated (`resources/views/admin/jobs/create.blade.php`)
- Removed red asterisk (*) from Company Name field
- Changed section header to "Company Information (Optional)"
- Added helper text: "Leave blank if partner didn't provide company information"
- Removed `required` attribute from company_name input

### 2. Validation Updated (`app/Http/Controllers/Admin/JobController.php`)
- Changed validation rule from `'company_name' => 'required|...'` to `'company_name' => 'nullable|...'`
- Added default value: If company name is blank, it defaults to "Confidential"

### 3. Job Display Updated (`resources/views/front/modern-job-detail.blade.php`)
- Updated company name logic to check `$job->company_name` first (for admin-posted jobs)
- Falls back to "Confidential" if no company name is available

## How It Works Now

### Scenario 1: Partner Provides Company Name
```
Admin fills in:
- Company Name: "ABC Corporation"
- Company Website: "https://abc.com"

Result: Job displays "ABC Corporation"
```

### Scenario 2: Partner Doesn't Provide Company Name
```
Admin leaves blank:
- Company Name: (empty)
- Company Website: (empty)

Result: Job displays "Confidential"
```

## Use Cases

This is perfect for:
- ✅ Partners who send job vacancies without company details
- ✅ Confidential job postings
- ✅ Recruitment agencies posting on behalf of unnamed clients
- ✅ Companies that want to remain anonymous during initial screening

## Testing

1. Login as admin
2. Go to "Post New Job"
3. Fill out all required fields
4. **Leave Company Name blank**
5. Click "Post Job"
6. Job should be created successfully
7. View the job - it should show "Confidential" as the company name

## Notes

- Company Website is also optional (always has been)
- The "Confidential" label is professional and commonly used in job boards
- If you want to change "Confidential" to something else (like "Private Company" or "Undisclosed"), edit line in `JobController.php`:
  ```php
  $job->company_name = $request->company_name ?: 'Confidential';
  ```
