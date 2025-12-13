<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get admin notifications
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $userId = auth()->id();

        $notifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => DB::table('notifications')
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->count()
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string'
        ]);

        $userId = auth()->id();

        DB::table('notifications')
            ->where('id', $request->notification_id)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $userId = auth()->id();
        $notificationIds = $request->get('notification_ids', []);

        if (empty($notificationIds)) {
            DB::table('notifications')
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } else {
            DB::table('notifications')
                ->where('user_id', $userId)
                ->whereIn('id', $notificationIds)
                ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        $userId = auth()->id();

        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        $userId = auth()->id();

        DB::table('notifications')
            ->where('user_id', $userId)
            ->whereNotNull('read_at')
            ->delete();

        return response()->json(['success' => true]);
    }
}
