# 🧹 Clean Up Duplicate Notifications

## Problem
You're seeing duplicate notifications because there might be old test notifications in the database.

---

## Solution 1: Delete Old Review Response Notifications

Run this in your database or via tinker:

### Option A: Delete ALL review response notifications (fresh start)
```sql
DELETE FROM notifications 
WHERE type = 'review_response';
```

### Option B: Delete only duplicate notifications (keep one of each)
```sql
DELETE n1 FROM notifications n1
INNER JOIN notifications n2 
WHERE n1.id > n2.id 
AND n1.user_id = n2.user_id
AND n1.type = 'review_response'
AND n1.type = n2.type
AND JSON_EXTRACT(n1.data, '$.review_id') = JSON_EXTRACT(n2.data, '$.review_id')
AND JSON_EXTRACT(n1.data, '$.action') = JSON_EXTRACT(n2.data, '$.action')
AND TIMESTAMPDIFF(SECOND, n2.created_at, n1.created_at) < 10;
```

### Option C: Via Laravel Tinker
```php
php artisan tinker

// Delete all review response notifications
DB::table('notifications')->where('type', 'review_response')->delete();

// Or delete duplicates only
$notifications = DB::table('notifications')
    ->where('type', 'review_response')
    ->orderBy('created_at', 'desc')
    ->get();

$seen = [];
foreach ($notifications as $notif) {
    $data = json_decode($notif->data, true);
    $key = $notif->user_id . '_' . ($data['review_id'] ?? '') . '_' . ($data['action'] ?? '');
    
    if (isset($seen[$key])) {
        DB::table('notifications')->where('id', $notif->id)->delete();
        echo "Deleted duplicate notification ID: {$notif->id}\n";
    } else {
        $seen[$key] = true;
    }
}
```

---

## Solution 2: Prevention (Already Implemented)

I've updated the controller to check for duplicate notifications before inserting. It now:

1. **Checks for recent duplicates** (within last 5 seconds)
2. **Compares review_id and action** to identify duplicates
3. **Only inserts if no duplicate exists**

This prevents future duplicates from being created.

---

## Quick Fix Command

Run this command to clean up all duplicate review response notifications:

```bash
php artisan tinker --execute="DB::table('notifications')->where('type', 'review_response')->delete(); echo 'Cleaned up review response notifications';"
```

Then test again by having the employer respond to a review. You should now see only ONE notification.

---

## Verify Cleanup

Check how many notifications exist:

```bash
php artisan tinker --execute="echo 'Total notifications: ' . DB::table('notifications')->count(); echo '\nReview response notifications: ' . DB::table('notifications')->where('type', 'review_response')->count();"
```

---

## Test After Cleanup

1. **Clean up old notifications** (use one of the methods above)
2. **As employer:** Respond to a review
3. **As jobseeker:** Check notifications
4. **Expected:** See only ONE notification

---

**Status:** Duplicate prevention implemented + cleanup instructions provided
