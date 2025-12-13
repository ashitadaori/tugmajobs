# Testing Notification System

## To test if notifications work, run this in tinker:

```php
php artisan tinker
```

Then run:

```php
$user = App\Models\User::where('role', 'jobseeker')->first();
$application = App\Models\JobApplication::where('user_id', $user->id)->first();

if ($application) {
    $user->notify(new App\Notifications\ApplicationStatusUpdated($application, 'Test feedback message'));
    echo "Notification sent to user: " . $user->name . "\n";
    echo "Check notifications count: " . $user->notifications()->count() . "\n";
} else {
    echo "No application found for this user\n";
}
```

## Or check if notifications exist:

```php
php artisan tinker
```

```php
$user = App\Models\User::find(3); // Replace 3 with jobseeker user ID
echo "Notifications: " . $user->notifications()->count() . "\n";
$user->notifications()->latest()->take(3)->get(['id', 'type', 'created_at', 'read_at', 'data']);
```

## Check the notifications table directly:

```sql
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5;
```

## The Real Issue:

The notification system should work, but we need to:
1. Verify notifications are being created when employer rejects
2. Check if the bell icon is loading notifications correctly
3. Ensure the User model has Notifiable trait

Let me create a test route to manually trigger a notification...
