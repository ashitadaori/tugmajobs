<div class="dropdown employer-notif-wrapper">
    <button type="button"
        class="employer-notif-bell-btn {{ Auth::user()->unreadNotificationsCount > 0 ? 'has-unread' : '' }}"
        id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        @if(Auth::user()->unreadNotificationsCount > 0)
            <span class="employer-notif-badge">{{ Auth::user()->unreadNotificationsCount }}</span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end employer-notif-dropdown" aria-labelledby="notificationDropdown"
        style="position: absolute !important;">
        <!-- Header -->
        <div class="employer-notif-header">
            <h6 class="mb-0">Notifications</h6>
            @if(Auth::user()->unreadNotificationsCount > 0)
                <button type="button" class="employer-notif-mark-all" id="employerMarkAllRead">
                    Mark all read
                </button>
            @endif
        </div>

        <!-- Notification List -->
        <div class="employer-notif-list" id="employerNotifList">
            @forelse(Auth::user()->notifications()->latest()->take(5)->get() as $notification)
                @php
                    $data = $notification->data;
                    $iconClass = 'bi-bell';
                    $iconColor = '#6366f1';

                    // Determine icon based on notification type (using Bootstrap Icons)
                    if (isset($data['type'])) {
                        if ($data['type'] === 'new_application') {
                            $iconClass = 'bi-person-plus';
                            $iconColor = '#3b82f6';
                        } elseif ($data['type'] === 'test') {
                            $iconClass = 'bi-flask';
                            $iconColor = '#8b5cf6';
                        }
                    }

                    $redirectUrl = $notification->action_url ?? route('employer.applications.index');
                @endphp

                <a href="{{ $redirectUrl }}"
                    class="employer-notif-item {{ is_null($notification->read_at) ? 'emp-notif-unread' : '' }}"
                    data-notification-id="{{ $notification->id }}" data-redirect-url="{{ $redirectUrl }}">
                    <div class="employer-notif-icon">
                        <i class="bi {{ $iconClass }}" style="color: {{ $iconColor }};"></i>
                    </div>
                    <div class="employer-notif-content">
                        <div class="employer-notif-title">
                            {{ $notification->title ?? 'Notification' }}
                        </div>
                        <div class="employer-notif-message">
                            {{ $notification->message ?? ($data['message'] ?? 'New notification') }}
                        </div>
                        <div class="employer-notif-time">
                            <i class="bi bi-clock"></i>
                            {{ $notification->created_at->timezone('Asia/Manila')->diffForHumans() }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="employer-notif-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications yet</p>
                    <small>You'll see new application alerts here</small>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if(Auth::user()->notifications()->count() > 5)
            <div class="employer-notif-footer">
                <a href="{{ route('notifications.index') }}" class="employer-notif-view-all">
                    View all notifications
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    /* Employer Notification Dropdown - Compact Design */
    .employer-notif-wrapper {
        position: relative !important;
        display: inline-block !important;
    }

    /* Bell Button */
    .employer-notif-bell-btn {
        position: relative;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #64748b !important;
        padding: 0;
    }

    .employer-notif-bell-btn:hover {
        background: #ffffff !important;
        border-color: #6366f1 !important;
        color: #6366f1 !important;
    }

    .employer-notif-bell-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }

    .employer-notif-bell-btn i {
        font-size: 1.1rem !important;
        transition: transform 0.3s ease;
    }

    /* Bell ring animation on hover */
    .employer-notif-bell-btn:hover i {
        animation: employerBellRing 0.5s ease-in-out;
    }

    @keyframes employerBellRing {
        0% {
            transform: rotate(0);
        }

        15% {
            transform: rotate(14deg);
        }

        30% {
            transform: rotate(-14deg);
        }

        45% {
            transform: rotate(10deg);
        }

        60% {
            transform: rotate(-10deg);
        }

        75% {
            transform: rotate(4deg);
        }

        100% {
            transform: rotate(0);
        }
    }

    /* Continuous subtle animation when there are unread notifications */
    .employer-notif-bell-btn.has-unread i {
        animation: employerBellPulse 2s ease-in-out infinite;
    }

    @keyframes employerBellPulse {

        0%,
        100% {
            transform: rotate(0) scale(1);
        }

        10% {
            transform: rotate(10deg) scale(1.1);
        }

        20% {
            transform: rotate(-8deg) scale(1.1);
        }

        30% {
            transform: rotate(6deg) scale(1.05);
        }

        40% {
            transform: rotate(-4deg) scale(1.05);
        }

        50% {
            transform: rotate(0) scale(1);
        }
    }

    /* Notification Badge */
    .employer-notif-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        min-width: 16px;
        height: 16px;
        font-size: 0.625rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
        border: 2px solid white;
    }

    /* Dropdown Container */
    .employer-notif-dropdown {
        width: 320px !important;
        max-width: calc(100vw - 20px) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        padding: 0 !important;
        margin-top: 8px !important;
        background: #ffffff !important;
        overflow: hidden !important;
        right: 0 !important;
    }

    /* Header */
    .employer-notif-header {
        padding: 12px 14px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .employer-notif-header h6 {
        color: #ffffff !important;
        margin: 0 !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
    }

    .employer-notif-mark-all {
        font-size: 0.7rem;
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 10px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .employer-notif-mark-all:hover {
        background: rgba(255, 255, 255, 0.3) !important;
    }

    /* Notification List */
    .employer-notif-list {
        max-height: 320px;
        overflow-y: auto;
        overflow-x: hidden;
        background: #ffffff;
    }

    /* Notification Item */
    .employer-notif-item {
        display: flex !important;
        align-items: flex-start !important;
        padding: 10px 14px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        text-decoration: none !important;
        color: #374151 !important;
        transition: background-color 0.15s ease !important;
        background: white !important;
        cursor: pointer !important;
        gap: 10px;
    }

    .employer-notif-item:last-child {
        border-bottom: none !important;
    }

    .employer-notif-item:hover {
        background-color: #f8fafc !important;
    }

    .employer-notif-item.emp-notif-unread {
        background-color: #eff6ff !important;
        border-left: 3px solid #3b82f6 !important;
    }

    .employer-notif-item.emp-notif-unread:hover {
        background-color: #dbeafe !important;
    }

    /* Icon */
    .employer-notif-icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .employer-notif-icon i {
        font-size: 1rem;
    }

    /* Content */
    .employer-notif-content {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .employer-notif-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .employer-notif-message {
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.4;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .employer-notif-time {
        font-size: 0.7rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .employer-notif-time i {
        font-size: 0.65rem;
    }

    /* Empty State */
    .employer-notif-empty {
        padding: 30px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .employer-notif-empty i {
        font-size: 2rem;
        opacity: 0.4;
        margin-bottom: 8px;
        display: block;
    }

    .employer-notif-empty p {
        font-size: 0.85rem;
        font-weight: 500;
        color: #64748b;
        margin: 4px 0;
    }

    .employer-notif-empty small {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    /* Footer */
    .employer-notif-footer {
        padding: 10px 14px;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
        text-align: center;
    }

    .employer-notif-view-all {
        color: #6366f1;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.75rem;
        transition: color 0.2s ease;
    }

    .employer-notif-view-all:hover {
        color: #4f46e5;
        text-decoration: underline;
    }

    /* Scrollbar */
    .employer-notif-list::-webkit-scrollbar {
        width: 4px;
    }

    .employer-notif-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .employer-notif-list::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 2px;
    }

    .employer-notif-list::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }

    /* Responsive */
    @media (max-width: 480px) {
        .employer-notif-dropdown {
            width: 280px !important;
            right: -10px !important;
        }

        .employer-notif-item {
            padding: 8px 12px !important;
        }

        .employer-notif-icon {
            width: 32px;
            height: 32px;
        }

        .employer-notif-title {
            font-size: 0.75rem;
        }

        .employer-notif-message {
            font-size: 0.7rem;
        }
    }
</style>

<script>
    // Wait for jQuery to be available
    (function () {
        function initNotifications() {
            if (typeof jQuery === 'undefined') {
                console.log('jQuery not loaded yet, waiting...');
                setTimeout(initNotifications, 100);
                return;
            }

            jQuery(document).ready(function ($) {
                console.log('Employer notification dropdown initialized');

                // Get current user role from Laravel
                const userRole = '{{ Auth::user()->role }}';

                // Function to map employer routes to admin routes for admin users
                function mapNotificationUrl(url) {
                    // Only map URLs if user is admin
                    if (userRole !== 'admin' && userRole !== 'superadmin') {
                        return url;
                    }

                    // Map employer routes to admin equivalents
                    const routeMappings = {
                        '/employer/applications/': '/admin/jobs/applications/',
                        '/employer/jobs/': '/admin/jobs/',
                    };

                    // Apply mappings
                    let mappedUrl = url;
                    for (const [employerPath, adminPath] of Object.entries(routeMappings)) {
                        if (mappedUrl.includes(employerPath)) {
                            mappedUrl = mappedUrl.replace(employerPath, adminPath);
                            console.log('Mapped notification URL for admin:', url, '->', mappedUrl);
                            break;
                        }
                    }

                    return mappedUrl;
                }

                // Function to update badge count and remove animation when no unread
                function updateBadgeCount(decrement = 1) {
                    const badge = $('.employer-notif-badge');
                    const bellBtn = $('.employer-notif-bell-btn');

                    if (badge.length) {
                        let count = parseInt(badge.text()) || 0;
                        count = Math.max(0, count - decrement);

                        if (count > 0) {
                            badge.text(count);
                        } else {
                            // Animate badge removal
                            badge.css({
                                'transition': 'all 0.3s ease',
                                'transform': 'scale(0)',
                                'opacity': '0'
                            });
                            setTimeout(function () {
                                badge.remove();
                            }, 300);

                            // Remove has-unread class to stop bell animation
                            bellBtn.removeClass('has-unread');

                            // Also hide "Mark all read" button if no unread
                            $('#employerMarkAllRead').fadeOut(200);
                        }
                    }
                }

                // Mark all notifications as read
                $(document).on('click', '#employerMarkAllRead', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const btn = $(this);
                    btn.prop('disabled', true).text('Marking...');

                    $.ajax({
                        url: '/employer/notifications/mark-all-as-read',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            // Remove unread styling from all items
                            $('.employer-notif-item.emp-notif-unread').removeClass('emp-notif-unread');

                            // Animate badge removal
                            const badge = $('.employer-notif-badge');
                            badge.css({
                                'transition': 'all 0.3s ease',
                                'transform': 'scale(0)',
                                'opacity': '0'
                            });
                            setTimeout(function () {
                                badge.remove();
                            }, 300);

                            // Remove has-unread class to stop bell animation
                            $('.employer-notif-bell-btn').removeClass('has-unread');

                            // Hide mark all button
                            btn.fadeOut(200);
                        },
                        error: function (xhr) {
                            console.error('Failed to mark all as read:', xhr.status);
                            btn.prop('disabled', false).text('Mark all read');
                        }
                    });
                });

                // Click notification to mark as read and redirect
                $(document).on('click', '.employer-notif-item', function (e) {
                    e.preventDefault();

                    const item = $(this);
                    const notificationId = item.data('notification-id');
                    const redirectUrl = item.data('redirect-url');
                    const isUnread = item.hasClass('emp-notif-unread');

                    // Map the URL based on user role
                    const mappedUrl = mapNotificationUrl(redirectUrl);

                    // If notification is unread, mark it as read first
                    if (isUnread && notificationId) {
                        // Immediately update UI
                        item.removeClass('emp-notif-unread');
                        item.css('transition', 'background-color 0.3s ease');
                        updateBadgeCount(1);

                        // Send request to mark as read
                        $.ajax({
                            url: '/employer/notifications/mark-as-read/' + notificationId,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                console.log('Notification marked as read:', response);
                            },
                            error: function (xhr) {
                                console.error('Failed to mark notification as read');
                            },
                            complete: function () {
                                // Redirect after marking (or attempting to mark) - use mapped URL
                                if (mappedUrl) {
                                    window.location.href = mappedUrl;
                                }
                            }
                        });
                    } else {
                        // Already read, just redirect - use mapped URL
                        if (mappedUrl) {
                            window.location.href = mappedUrl;
                        }
                    }
                });
            });
        }

        initNotifications();
    })();
</script>