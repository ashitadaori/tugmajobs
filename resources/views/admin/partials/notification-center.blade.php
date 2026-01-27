@php
    $adminNotifications = \App\Models\Notification::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
        ->whereNull('read_at')
        ->count();
@endphp

<div class="dropdown admin-notif-wrapper">
    <button type="button"
        class="admin-notif-bell-btn {{ $unreadCount > 0 ? 'has-unread' : '' }}"
        id="adminNotificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        @if($unreadCount > 0)
            <span class="admin-notif-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end admin-notif-dropdown" aria-labelledby="adminNotificationDropdown">
        <!-- Header -->
        <div class="admin-notif-header">
            <h6 class="mb-0"><i class="bi bi-bell me-2"></i>Notifications</h6>
            @if($unreadCount > 0)
                <button type="button" class="admin-notif-mark-all" id="adminMarkAllRead">
                    Mark all read
                </button>
            @endif
        </div>

        <!-- Notification List -->
        <div class="admin-notif-list" id="adminNotifList">
            @forelse($adminNotifications as $notification)
                @php
                    $iconClass = 'bi-bell';
                    $iconBg = '#e0e7ff';
                    $iconColor = '#6366f1';

                    // Icon mapping based on notification type
                    switch($notification->type) {
                        case 'admin_employer_document':
                            $iconClass = 'bi-file-earmark-arrow-up';
                            $iconBg = '#fef3c7';
                            $iconColor = '#f59e0b';
                            break;
                        case 'admin_kyc_submission':
                            $iconClass = 'bi-person-badge';
                            $iconBg = '#fef3c7';
                            $iconColor = '#f59e0b';
                            break;
                        case 'new_job_pending':
                            $iconClass = 'bi-briefcase';
                            $iconBg = '#fef3c7';
                            $iconColor = '#f59e0b';
                            break;
                        case 'admin_new_application':
                            $iconClass = 'bi-file-earmark-text';
                            $iconBg = '#dbeafe';
                            $iconColor = '#3b82f6';
                            break;
                        case 'kyc_verified':
                        case 'document_approved':
                            $iconClass = 'bi-check-circle';
                            $iconBg = '#d1fae5';
                            $iconColor = '#10b981';
                            break;
                        case 'kyc_rejected':
                        case 'document_rejected':
                            $iconClass = 'bi-x-circle';
                            $iconBg = '#fee2e2';
                            $iconColor = '#ef4444';
                            break;
                    }
                @endphp

                <a href="{{ $notification->action_url ?? '#' }}"
                    class="admin-notif-item {{ is_null($notification->read_at) ? 'unread' : '' }}"
                    data-notification-id="{{ $notification->id }}">
                    <div class="admin-notif-icon" style="background: {{ $iconBg }};">
                        <i class="bi {{ $iconClass }}" style="color: {{ $iconColor }};"></i>
                    </div>
                    <div class="admin-notif-content">
                        <div class="admin-notif-title">{{ $notification->title ?? 'Notification' }}</div>
                        <div class="admin-notif-message">{{ Str::limit($notification->message, 80) }}</div>
                        <div class="admin-notif-time">
                            <i class="bi bi-clock"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="admin-notif-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications</p>
                    <small>You'll see alerts for KYC submissions, documents, and jobs here</small>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if($adminNotifications->count() >= 10)
            <div class="admin-notif-footer">
                <a href="{{ route('admin.dashboard') }}" class="admin-notif-view-all">
                    View all notifications
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    .admin-notif-wrapper {
        position: relative !important;
    }

    .admin-notif-bell-btn {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #6b7280;
    }

    .admin-notif-bell-btn:hover {
        background: #f9fafb;
        border-color: #667eea;
        color: #667eea;
    }

    .admin-notif-bell-btn i {
        font-size: 1.25rem;
    }

    .admin-notif-bell-btn.has-unread i {
        animation: bellPulse 2s ease-in-out infinite;
    }

    @keyframes bellPulse {
        0%, 100% { transform: rotate(0); }
        10% { transform: rotate(10deg); }
        20% { transform: rotate(-8deg); }
        30% { transform: rotate(6deg); }
        40% { transform: rotate(-4deg); }
        50% { transform: rotate(0); }
    }

    .admin-notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        min-width: 18px;
        height: 18px;
        font-size: 0.65rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
    }

    .admin-notif-dropdown {
        width: 360px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        padding: 0 !important;
        margin-top: 8px !important;
        overflow: hidden !important;
    }

    .admin-notif-header {
        padding: 14px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .admin-notif-header h6 {
        color: white !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
    }

    .admin-notif-mark-all {
        font-size: 0.7rem;
        color: white;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 10px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .admin-notif-mark-all:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .admin-notif-list {
        max-height: 400px;
        overflow-y: auto;
        background: white;
    }

    .admin-notif-item {
        display: flex !important;
        align-items: flex-start !important;
        padding: 12px 16px !important;
        border-bottom: 1px solid #f3f4f6 !important;
        text-decoration: none !important;
        color: #374151 !important;
        transition: background 0.15s !important;
        gap: 12px;
    }

    .admin-notif-item:hover {
        background: #f9fafb !important;
    }

    .admin-notif-item.unread {
        background: #eff6ff !important;
        border-left: 3px solid #667eea !important;
    }

    .admin-notif-item.unread:hover {
        background: #dbeafe !important;
    }

    .admin-notif-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .admin-notif-icon i {
        font-size: 1.1rem;
    }

    .admin-notif-content {
        flex: 1;
        min-width: 0;
    }

    .admin-notif-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .admin-notif-message {
        font-size: 0.8rem;
        color: #6b7280;
        line-height: 1.4;
        margin-bottom: 4px;
    }

    .admin-notif-time {
        font-size: 0.7rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .admin-notif-empty {
        padding: 40px 20px;
        text-align: center;
        color: #9ca3af;
    }

    .admin-notif-empty i {
        font-size: 2.5rem;
        opacity: 0.3;
        margin-bottom: 10px;
        display: block;
    }

    .admin-notif-empty p {
        font-size: 0.9rem;
        font-weight: 500;
        color: #6b7280;
        margin: 4px 0;
    }

    .admin-notif-empty small {
        font-size: 0.75rem;
    }

    .admin-notif-footer {
        padding: 12px 16px;
        border-top: 1px solid #f3f4f6;
        background: #f9fafb;
        text-align: center;
    }

    .admin-notif-view-all {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .admin-notif-view-all:hover {
        text-decoration: underline;
    }

    .admin-notif-list::-webkit-scrollbar {
        width: 4px;
    }

    .admin-notif-list::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 2px;
    }

    @media (max-width: 576px) {
        .admin-notif-dropdown {
            width: 300px !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark all as read
    const markAllBtn = document.getElementById('adminMarkAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            fetch('{{ route("admin.notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                document.querySelectorAll('.admin-notif-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
                const badge = document.querySelector('.admin-notif-badge');
                if (badge) badge.remove();
                document.querySelector('.admin-notif-bell-btn').classList.remove('has-unread');
                markAllBtn.style.display = 'none';
            });
        });
    }

    // Mark individual notification as read on click
    document.querySelectorAll('.admin-notif-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const notifId = this.dataset.notificationId;
            const url = this.getAttribute('href');

            if (this.classList.contains('unread') && notifId) {
                e.preventDefault();
                fetch('{{ route("admin.notifications.mark-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ notification_id: notifId })
                }).then(() => {
                    if (url && url !== '#') {
                        window.location.href = url;
                    }
                });
            }
        });
    });
});
</script>
