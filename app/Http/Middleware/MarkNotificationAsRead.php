<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MarkNotificationAsRead
{
    /**
     * Handle an incoming request.
     *
     * Automatically marks notifications as read when the user visits
     * the notification page or accesses notification-related routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if ($request->user()) {
            // Auto-mark notification as read if notification ID is in the route
            if ($request->route('notification')) {
                $notification = $request->user()
                    ->notifications()
                    ->where('id', $request->route('notification'))
                    ->first();

                if ($notification && is_null($notification->read_at)) {
                    $notification->markAsRead();
                }
            }

            // Auto-mark all notifications as read when visiting notifications page
            // (Only if specifically enabled via query parameter to avoid aggressive marking)
            if ($request->route()->getName() === 'notifications.index' && $request->has('mark_all_read')) {
                $request->user()->unreadNotifications->markAsRead();
            }
        }

        return $next($request);
    }
}
