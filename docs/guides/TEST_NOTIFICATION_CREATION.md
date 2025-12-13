# Test Notification Creation

## Manual Test

Run this in tinker to manually create a test notification:

```bash
php artisan tinker
```

Then run:

```php
// Find an employer user
$employer = \App\Models\User::where('role', 'employer')->first();

// Create a test notification
$notification = \App\Models\Notification::create([
    'user_id' => $employer->id,
    'title' => 'TEST: New Application Received',
    'message' => 'Test User has applied for "Test Job"',
    'type' => 'new_application',
    'data' => json_encode([
        'job_application_id' => 999,
        'job_id' => 999,
        'job_title' => 'Test Job',
        'applicant_name' => 'Test User',
        'applicant_id' => 1,
    ]),
    'action_url' => '/employer/applications',
    'read_at' => null
]);

echo "Notification created with ID: " . $notification->id;
echo "\nFor employer: " . $employer->name . " (ID: " . $employer->id . ")";
echo "\nUnread count: " . $employer->unreadNotificationsCount;
```

## Then Check:

1. Login as that employer
2. Look at the notification bell
3. Should see badge with count
4. Click bell to see notification

## If Badge Doesn't Show:

Check browser console (F12) for errors in the JavaScript.
