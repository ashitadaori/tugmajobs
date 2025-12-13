<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->orderBy('created_at', 'desc')->paginate(10);
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get the user's unread notifications count.
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->notifications()->unread()->count();
        
        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Check for new notifications (for auto-refresh).
     */
    public function checkNew()
    {
        $count = Auth::user()->notifications()->unread()->count();
        
        return response()->json([
            'has_new' => $count > 0,
            'count' => $count
        ]);
    }

    /**
     * Get the user's recent notifications for the dropdown.
     */
    public function getRecentNotifications()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Add human-readable timestamps
        $notifications->each(function($notification) {
            $notification->created_at_human = $notification->created_at->diffForHumans();
        });
        
        $unreadCount = Auth::user()->notifications()->unread()->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect($notification->action_url ?? route('notifications.index'));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()->unread()->update(['read_at' => now()]);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    /**
     * Mark multiple notifications as read.
     */
    public function markAsReadBatch(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'required|string'
        ]);

        $count = Auth::user()->notifications()
            ->whereIn('id', $request->notification_ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'marked_count' => $count
            ]);
        }

        return redirect()->back()->with('success', "{$count} notification(s) marked as read.");
    }

    /**
     * Delete multiple notifications.
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'required|string'
        ]);

        $count = Auth::user()->notifications()
            ->whereIn('id', $request->notification_ids)
            ->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $count
            ]);
        }

        return redirect()->back()->with('success', "{$count} notification(s) deleted successfully.");
    }

    /**
     * Auto-mark notification as read (AJAX endpoint).
     * Used for real-time marking when hovering/clicking notifications.
     */
    public function autoMarkAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'notification_id' => $id,
            'read_at' => $notification->read_at
        ]);
    }

    /**
     * Mark old notifications as read.
     * Useful for cleaning up notifications older than X days.
     */
    public function markOldAsRead(Request $request)
    {
        $request->validate([
            'days' => 'integer|min:1|max:365'
        ]);

        $days = $request->input('days', 30);
        $cutoffDate = now()->subDays($days);

        $count = Auth::user()->notifications()
            ->whereNull('read_at')
            ->where('created_at', '<', $cutoffDate)
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'marked_count' => $count,
                'cutoff_days' => $days
            ]);
        }

        return redirect()->back()->with('success', "Marked {$count} notifications older than {$days} days as read.");
    }

    /**
     * Get user's notification preferences.
     */
    public function getPreferences()
    {
        $user = Auth::user();

        // Get notification preferences from user settings or metadata
        $preferences = [
            'email_notifications' => $user->email_notifications ?? true,
            'push_notifications' => $user->push_notifications ?? true,
            'application_updates' => $user->notification_settings['application_updates'] ?? true,
            'job_matches' => $user->notification_settings['job_matches'] ?? true,
            'system_announcements' => $user->notification_settings['system_announcements'] ?? true,
            'auto_mark_read' => $user->notification_settings['auto_mark_read'] ?? false,
            'mark_read_on_view' => $user->notification_settings['mark_read_on_view'] ?? true,
        ];

        if (request()->wantsJson()) {
            return response()->json($preferences);
        }

        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * Update user's notification preferences.
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'application_updates' => 'boolean',
            'job_matches' => 'boolean',
            'system_announcements' => 'boolean',
            'auto_mark_read' => 'boolean',
            'mark_read_on_view' => 'boolean',
        ]);

        $user = Auth::user();

        // Update user notification settings
        $notificationSettings = $user->notification_settings ?? [];

        $notificationSettings = array_merge($notificationSettings, [
            'application_updates' => $request->input('application_updates', true),
            'job_matches' => $request->input('job_matches', true),
            'system_announcements' => $request->input('system_announcements', true),
            'auto_mark_read' => $request->input('auto_mark_read', false),
            'mark_read_on_view' => $request->input('mark_read_on_view', true),
        ]);

        $user->update([
            'email_notifications' => $request->input('email_notifications', true),
            'push_notifications' => $request->input('push_notifications', true),
            'notification_settings' => $notificationSettings,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }
}