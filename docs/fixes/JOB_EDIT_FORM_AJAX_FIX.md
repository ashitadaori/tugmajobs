# Job Edit Form AJAX Save Fix

## Problem
The job edit form was not saving changes. When clicking "Save Changes", nothing happened because the controller was returning redirect responses instead of JSON responses expected by the AJAX call.

## Root Cause
The `updateJob` method in `EmployerController` was only returning redirect responses, but the edit form uses AJAX submission and expects JSON responses.

## Solution
Modified the `updateJob` method to detect AJAX requests and return appropriate JSON responses:

### Changes Made

1. **Added AJAX Detection for Authorization**
   - Returns JSON error for AJAX requests when unauthorized
   - Maintains redirect for non-AJAX requests

2. **Added AJAX Response for Validation Errors**
   - Returns JSON with validation errors (422 status)
   - Maintains redirect for non-AJAX requests

3. **Added AJAX Response for Success**
   - Returns JSON with success status and message
   - Maintains redirect for non-AJAX requests

4. **Added AJAX Response for Exceptions**
   - Returns JSON with error message (500 status)
   - Maintains redirect for non-AJAX requests

## Code Changes

### File: `app/Http/Controllers/EmployerController.php`

```php
// Authorization check
if ($job->employer_id !== Auth::id()) {
    if ($request->ajax()) {
        return response()->json(['status' => false, 'message' => 'Unauthorized action.'], 403);
    }
    abort(403, 'Unauthorized action.');
}

// Validation error response
if ($validator->fails()) {
    if ($request->ajax()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }
    return redirect()->back()->withErrors($validator)->withInput();
}

// Success response
if ($request->ajax()) {
    return response()->json([
        'status' => true,
        'message' => $message
    ]);
}

// Exception handling
if ($request->ajax()) {
    return response()->json([
        'status' => false,
        'message' => 'Error updating job: ' . $e->getMessage()
    ], 500);
}
```

## Features Maintained

1. **Job Resubmission**: Rejected jobs can be resubmitted for approval
2. **Auto-Reopen**: Closed jobs automatically reopen when vacancy is increased
3. **Validation**: All form fields are properly validated
4. **Error Handling**: Comprehensive error handling with user-friendly messages
5. **Toast Notifications**: Success and error messages displayed via toast

## Testing

Test the following scenarios:

1. **Normal Edit**: Edit an approved job and save changes
2. **Rejected Job**: Edit and resubmit a rejected job
3. **Closed Job**: Edit a closed job and increase vacancy to reopen
4. **Validation**: Submit with missing required fields
5. **Unauthorized**: Try to edit another employer's job

## User Experience

- Loading state shows "Updating..." while saving
- Success toast appears with appropriate message
- Automatic redirect to jobs list after successful save
- Error messages highlight problematic fields
- Form remains filled if validation fails

## Status
✅ Fixed and tested
