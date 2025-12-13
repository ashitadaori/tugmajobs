# Notification System Enhancement Guide

## Overview

The enhanced notification system provides automatic notification marking, batch operations, and user preferences management for a better user experience.

---

## Features

### 1. Auto-Mark Notifications as Read ✅

**Middleware Implementation**: `MarkNotificationAsRead.php`

Automatically marks notifications as read when:
- User visits a specific notification URL with notification ID
- User visits notifications index page with `?mark_all_read=1` parameter

**Usage**:
```php
// Notification detail page - auto-marks this notification as read
Route::get('/notifications/{notification}', [NotificationController::class, 'view'])
    ->middleware(['mark.notification.read']);

// Notifications index - optional mark all as read
Route::get('/notifications?mark_all_read=1', ...)
```

### 2. Batch Operations ✅

**Mark Multiple as Read**:
```javascript
// JavaScript
fetch('/notifications/mark-as-read-batch', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        notification_ids: ['uuid-1', 'uuid-2', 'uuid-3']
    })
});
```

**Delete Multiple**:
```javascript
fetch('/notifications/batch/delete', {
    method: 'DELETE',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        notification_ids: ['uuid-1', 'uuid-2']
    })
});
```

### 3. Real-Time Auto-Marking ✅

**JavaScript Integration**: `notification-auto-mark.js`

Features:
- **Hover to Mark**: Marks notification as read after 2 seconds of hovering
- **Click to Mark**: Instantly marks notification as read on click
- **Automatic Count Update**: Updates unread count in real-time
- **Batch Mark All**: Mark all unread notifications with one click

**Configuration**:
```javascript
window.NotificationAutoMark.config = {
    hoverDelay: 2000,        // 2 seconds
    enableHoverMark: true,   // Enable hover marking
    enableClickMark: true,   // Enable click marking
};
```

### 4. Mark Old Notifications ✅

Automatically mark notifications older than X days as read:

```javascript
// Mark notifications older than 30 days
fetch('/notifications/mark-old-as-read', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({ days: 30 })
});
```

### 5. User Preferences ✅

Manage notification preferences:

```php
// Get preferences
GET /notifications/preferences/view

// Update preferences
POST /notifications/preferences/update
{
    "email_notifications": true,
    "push_notifications": true,
    "application_updates": true,
    "job_matches": true,
    "system_announcements": true,
    "auto_mark_read": false,
    "mark_read_on_view": true
}
```

---

## Installation

### 1. Middleware Registration

Already registered in `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    'mark.notification.read' => \App\Http\Middleware\MarkNotificationAsRead::class,
];
```

### 2. Routes Configuration

Routes already set up in `routes/web.php`:
```php
Route::middleware(['auth', 'mark.notification.read'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'view']);
});
```

### 3. JavaScript Integration

Add to your layout file (e.g., `resources/views/layouts/app.blade.php`):

```html
<!-- Add before closing </body> tag -->
<script src="{{ asset('js/notification-auto-mark.js') }}"></script>

<!-- Add CSRF token meta tag in <head> -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 4. HTML Markup

Notifications should have the following structure:

```html
<!-- Notification item -->
<div class="notification-item unread" data-notification-id="{{ $notification->id }}">
    <div class="unread-indicator"></div>
    <div class="notification-content">
        <h4>{{ $notification->data['title'] }}</h4>
        <p>{{ $notification->data['message'] }}</p>
        <span class="time">{{ $notification->created_at->diffForHumans() }}</span>
    </div>
</div>
```

**Required attributes**:
- `data-notification-id`: Notification UUID
- `class="unread"`: For unread notifications
- `class="unread-indicator"`: Visual indicator (dot/badge)

---

## API Endpoints

### Get Unread Count
```
GET /notifications/unread-count
Response: { "count": 5 }
```

### Check for New Notifications
```
GET /notifications/check-new
Response: { "has_new": true, "count": 5 }
```

### Get Recent Notifications
```
GET /notifications/recent
Response: {
    "notifications": [...],
    "unreadCount": 5
}
```

### Mark Single as Read
```
POST /notifications/mark-as-read/{id}
Response: { "success": true }
```

### Mark All as Read
```
POST /notifications/mark-all-as-read
Response: { "success": true }
```

### Mark Batch as Read
```
POST /notifications/mark-as-read-batch
Body: { "notification_ids": ["uuid1", "uuid2"] }
Response: { "success": true, "marked_count": 2 }
```

### Auto-Mark as Read (AJAX)
```
POST /notifications/auto-mark-as-read/{id}
Response: {
    "success": true,
    "notification_id": "uuid",
    "read_at": "2025-11-14 10:30:00"
}
```

### Mark Old as Read
```
POST /notifications/mark-old-as-read
Body: { "days": 30 }
Response: {
    "success": true,
    "marked_count": 15,
    "cutoff_days": 30
}
```

### Delete Notification
```
DELETE /notifications/{id}
Response: { "success": true }
```

### Delete Batch
```
DELETE /notifications/batch/delete
Body: { "notification_ids": ["uuid1", "uuid2"] }
Response: { "success": true, "deleted_count": 2 }
```

### Get Preferences
```
GET /notifications/preferences/view
Response: {
    "email_notifications": true,
    "push_notifications": true,
    "application_updates": true,
    ...
}
```

### Update Preferences
```
POST /notifications/preferences/update
Body: { "email_notifications": true, ... }
Response: { "success": true, "message": "..." }
```

---

## Usage Examples

### Example 1: Notification List with Auto-Mark

```html
<div class="notifications-list">
    @foreach($notifications as $notification)
        <a href="{{ route('notifications.view', $notification->id) }}"
           class="notification-item {{ is_null($notification->read_at) ? 'unread' : 'read' }}"
           data-notification-id="{{ $notification->id }}">
            @if(is_null($notification->read_at))
                <span class="unread-indicator"></span>
            @endif
            <div class="notification-content">
                <h4>{{ $notification->data['title'] ?? 'Notification' }}</h4>
                <p>{{ $notification->data['message'] ?? '' }}</p>
                <span class="time">{{ $notification->created_at->diffForHumans() }}</span>
            </div>
        </a>
    @endforeach
</div>

<!-- Mark All Button -->
<button class="btn btn-sm btn-secondary" data-action="mark-all-read">
    Mark All as Read
</button>

<!-- Mark Old Button -->
<button class="btn btn-sm btn-secondary" data-action="mark-old-read" data-days="30">
    Mark Old as Read (30+ days)
</button>
```

### Example 2: Notification Bell Badge

```html
<!-- Notification Bell -->
<div class="notification-bell">
    <i class="fas fa-bell"></i>
    <span class="notification-count badge">{{ auth()->user()->unreadNotifications->count() }}</span>
</div>

<!-- Dropdown -->
<div class="notification-dropdown">
    <div class="notification-header">
        <h5>Notifications</h5>
        <button data-action="mark-all-read">Mark all as read</button>
    </div>
    <div class="notification-list">
        @foreach(auth()->user()->unreadNotifications->take(5) as $notification)
            <div class="notification-item unread"
                 data-notification-id="{{ $notification->id }}">
                <!-- Notification content -->
            </div>
        @endforeach
    </div>
</div>

<script>
// Auto-update count every 30 seconds
setInterval(() => {
    window.NotificationAutoMark.updateCount();
}, 30000);
</script>
```

### Example 3: Custom Notification Action

```javascript
// Listen for notification marked event
document.addEventListener('notification:marked-read', function(event) {
    console.log('Notification marked:', event.detail.notificationId);

    // Custom action: Update related UI
    updateDashboardStats();
});

// Manually mark a notification
window.NotificationAutoMark.markAsRead('notification-uuid-here');

// Mark multiple notifications
window.NotificationAutoMark.markMultiple([
    'uuid-1',
    'uuid-2',
    'uuid-3'
]);
```

---

## Middleware Behavior

The `MarkNotificationAsRead` middleware:

1. **Checks if user is authenticated**
2. **If notification ID in route parameter**:
   - Finds the notification
   - Marks it as read if unread
3. **If route is `notifications.index` with `mark_all_read` query param**:
   - Marks all unread notifications as read

**Example Routes**:
```
/notifications/{notification-id}        → Auto-marks this notification
/notifications?mark_all_read=1          → Marks all as read
/notifications                          → No auto-marking
```

---

## Controller Methods

### NotificationController Methods

| Method | Description | Route |
|--------|-------------|-------|
| `index()` | List all notifications | GET /notifications |
| `getUnreadCount()` | Get unread count | GET /notifications/unread-count |
| `checkNew()` | Check for new notifications | GET /notifications/check-new |
| `getRecentNotifications()` | Get 5 recent notifications | GET /notifications/recent |
| `markAsRead($id)` | Mark single as read | POST /notifications/mark-as-read/{id} |
| `markAllAsRead()` | Mark all as read | POST /notifications/mark-all-as-read |
| `destroy($id)` | Delete notification | DELETE /notifications/{id} |
| `markAsReadBatch()` | Mark multiple as read | POST /notifications/mark-as-read-batch |
| `destroyBatch()` | Delete multiple | DELETE /notifications/batch/delete |
| `autoMarkAsRead($id)` | AJAX auto-mark | POST /notifications/auto-mark-as-read/{id} |
| `markOldAsRead()` | Mark old notifications | POST /notifications/mark-old-as-read |
| `getPreferences()` | Get user preferences | GET /notifications/preferences/view |
| `updatePreferences()` | Update preferences | POST /notifications/preferences/update |

---

## CSS Styling (Recommended)

```css
/* Notification Item */
.notification-item {
    position: relative;
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.3s;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e8f4f8;
    font-weight: 600;
}

.notification-item.read {
    opacity: 0.7;
}

/* Unread Indicator */
.unread-indicator {
    position: absolute;
    top: 20px;
    left: 10px;
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
}

/* Notification Count Badge */
.notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: 11px;
    line-height: 18px;
    text-align: center;
    background-color: #dc3545;
    color: white;
    border-radius: 9px;
}

.notification-count[data-count="0"],
.notification-count:empty {
    display: none;
}
```

---

## Performance Optimization

### Database Indexes

Already added in migration `2025_11_14_062838_add_database_indexes_for_performance.php`:

```php
Schema::table('notifications', function (Blueprint $table) {
    $table->index('read_at', 'idx_notifications_read');
    $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'idx_notifications_unread');
});
```

### Eager Loading

```php
// Load notifications with relationships
$notifications = auth()->user()
    ->notifications()
    ->with(['notifiable'])
    ->latest()
    ->paginate(20);
```

### Caching

```php
// Cache unread count
$unreadCount = Cache::remember("user.{$userId}.unread_notifications", 300, function() use ($userId) {
    return User::find($userId)->unreadNotifications->count();
});
```

---

## Testing

### Test Auto-Mark Middleware

```php
public function test_notification_auto_marked_when_viewing()
{
    $user = User::factory()->create();
    $notification = $user->notifications()->create([...]);

    $this->actingAs($user)
        ->get(route('notifications.view', $notification->id))
        ->assertSuccessful();

    $this->assertNotNull($notification->fresh()->read_at);
}
```

### Test Batch Operations

```php
public function test_mark_multiple_notifications_as_read()
{
    $user = User::factory()->create();
    $notifications = collect([
        $user->notifications()->create([...]),
        $user->notifications()->create([...]),
    ]);

    $this->actingAs($user)
        ->postJson(route('notifications.markAsReadBatch'), [
            'notification_ids' => $notifications->pluck('id')->toArray()
        ])
        ->assertJson(['success' => true, 'marked_count' => 2]);

    $notifications->each(function($notification) {
        $this->assertNotNull($notification->fresh()->read_at);
    });
}
```

---

## Troubleshooting

### Notifications Not Auto-Marking

1. **Check middleware is registered**:
   ```bash
   php artisan route:list | grep notifications
   ```

2. **Verify CSRF token**:
   ```html
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

3. **Check browser console for errors**:
   - Open DevTools → Console
   - Look for AJAX errors

### Count Not Updating

1. **Clear cache**:
   ```bash
   php artisan cache:clear
   ```

2. **Check JavaScript is loaded**:
   ```javascript
   console.log(window.NotificationAutoMark);
   ```

3. **Verify route exists**:
   ```
   /notifications/unread-count
   ```

### Hover Not Working

1. **Check configuration**:
   ```javascript
   console.log(window.NotificationAutoMark.config.enableHoverMark);
   ```

2. **Verify data attribute**:
   ```html
   <div data-notification-id="uuid">...</div>
   ```

3. **Check CSS class**:
   ```html
   <div class="unread" ...>...</div>
   ```

---

## Best Practices

1. **Use Middleware for Auto-Marking**: Apply `mark.notification.read` middleware to notification view routes

2. **Implement Debouncing**: Prevent rapid-fire AJAX requests with debouncing

3. **Show Loading States**: Indicate when notifications are being marked

4. **Provide Visual Feedback**: Use animations when marking notifications

5. **Optimize Queries**: Use eager loading and indexes for better performance

6. **Cache Counts**: Cache unread counts for frequently accessed data

7. **Handle Errors Gracefully**: Show user-friendly error messages

8. **Test Thoroughly**: Write tests for all notification operations

---

## Summary

✅ **Auto-Mark Middleware** - Automatically marks notifications on view
✅ **Batch Operations** - Mark/delete multiple notifications at once
✅ **Real-Time Updates** - AJAX-based marking without page reload
✅ **User Preferences** - Customizable notification settings
✅ **JavaScript Integration** - Hover and click to mark
✅ **Performance Optimized** - Database indexes and caching

---

## Files Created/Modified

### Created:
1. `app/Http/Middleware/MarkNotificationAsRead.php`
2. `public/js/notification-auto-mark.js`
3. `docs/NOTIFICATION_SYSTEM_GUIDE.md`

### Modified:
1. `app/Http/Controllers/NotificationController.php` - Added 6 new methods
2. `app/Http/Kernel.php` - Registered middleware
3. `routes/web.php` - Added new notification routes

---

## Next Steps

1. **Integrate into UI**: Add the JavaScript to your layout
2. **Style Notifications**: Apply CSS for better UX
3. **Test Functionality**: Test all features thoroughly
4. **User Preferences Page**: Create UI for managing preferences
5. **Email Notifications**: Implement email notification system (if not already done)

---

*Notification system enhancement complete!*
*Ready for error tracking implementation.*
