<!-- Notification Center Component -->
<div id="notificationCenter" class="notification-center">
    <!-- Notification Bell -->
    <div class="notification-bell-wrapper">
        <button class="notification-bell" id="notificationBell" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            <span class="notification-pulse" id="notificationPulse" style="display: none;"></span>
        </button>
    </div>

    <!-- Notification Dropdown -->
    <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
        <div class="notification-header">
            <h6 class="mb-0">
                <i class="bi bi-bell me-2"></i>Notifications
            </h6>
            <div class="notification-actions">
                <button class="btn-icon" onclick="markAllAsRead()" title="Mark all as read">
                    <i class="bi bi-check2-all"></i>
                </button>
                <button class="btn-icon" onclick="refreshNotifications()" title="Refresh">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="notification-tabs">
            <button class="notif-tab active" data-filter="all" onclick="filterNotifications('all')">
                All <span class="tab-count" id="countAll">0</span>
            </button>
            <button class="notif-tab" data-filter="unread" onclick="filterNotifications('unread')">
                Unread <span class="tab-count" id="countUnread">0</span>
            </button>
            <button class="notif-tab" data-filter="job_approved" onclick="filterNotifications('job_approved')">
                <i class="bi bi-briefcase"></i> Jobs
            </button>
            <button class="notif-tab" data-filter="new_application" onclick="filterNotifications('new_application')">
                <i class="bi bi-file-earmark"></i> Applications
            </button>
        </div>

        <!-- Notifications List -->
        <div class="notification-list" id="notificationList">
            <div class="notification-loading">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <span class="ms-2">Loading notifications...</span>
            </div>
        </div>

        <!-- View All Footer -->
        <div class="notification-footer">
            <a href="#" class="view-all-link">View All Notifications</a>
        </div>
    </div>
</div>

<style>
.notification-center {
    position: relative;
}

.notification-bell-wrapper {
    display: inline-block;
}

.notification-bell {
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
    transition: all 0.3s;
    color: #6b7280;
}

.notification-bell:hover {
    background: #f9fafb;
    border-color: #667eea;
    color: #667eea;
}

.notification-bell i {
    font-size: 1.25rem;
}

.notification-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    border: 2px solid white;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-5px);
    }
    60% {
        transform: translateY(-3px);
    }
}

.notification-pulse {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ef4444;
    animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 420px;
    max-height: 600px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    animation: dropdownSlide 0.3s ease-out;
}

@keyframes dropdownSlide {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: #f3f4f6;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #6b7280;
}

.btn-icon:hover {
    background: #e5e7eb;
    color: #374151;
}

.notification-tabs {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
    padding: 0 1rem;
    gap: 0.5rem;
}

.notif-tab {
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 0.875rem;
    color: #6b7280;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
}

.notif-tab:hover {
    color: #374151;
}

.notif-tab.active {
    color: #667eea;
    border-bottom-color: #667eea;
    font-weight: 600;
}

.tab-count {
    background: #f3f4f6;
    color: #6b7280;
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
}

.notif-tab.active .tab-count {
    background: #ede9fe;
    color: #667eea;
}

.notification-list {
    flex: 1;
    overflow-y: auto;
    max-height: 400px;
}

.notification-loading {
    padding: 2rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
}

.notification-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.notification-item:hover {
    background: #f9fafb;
}

.notification-item.unread {
    background: #eff6ff;
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #667eea;
}

.notif-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-right: 1rem;
}

.notif-icon.success { background: #d1fae5; color: #10b981; }
.notif-icon.info { background: #dbeafe; color: #3b82f6; }
.notif-icon.warning { background: #fef3c7; color: #f59e0b; }
.notif-icon.danger { background: #fee2e2; color: #ef4444; }

.notif-content {
    flex: 1;
}

.notif-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.notif-message {
    color: #6b7280;
    font-size: 0.875rem;
    line-height: 1.4;
}

.notif-time {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.5rem;
}

.notif-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.notif-action-btn {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
}

.notif-action-primary {
    background: #667eea;
    color: white;
}

.notif-action-primary:hover {
    background: #5568d3;
}

.notif-action-secondary {
    background: #f3f4f6;
    color: #6b7280;
}

.notif-action-secondary:hover {
    background: #e5e7eb;
}

.notification-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.view-all-link {
    color: #667eea;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
}

.view-all-link:hover {
    text-decoration: underline;
}

.empty-notifications {
    padding: 3rem 2rem;
    text-align: center;
    color: #9ca3af;
}

.empty-notifications i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

@media (max-width: 768px) {
    .notification-dropdown {
        width: calc(100vw - 40px);
        right: -10px;
    }
}
</style>

<script>
let notifications = [];
let currentFilter = 'all';
let notificationCheckInterval;

// Initialize Notification Center
document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationCenter();
    loadNotifications();

    // Check for new notifications every 30 seconds
    notificationCheckInterval = setInterval(loadNotifications, 30000);
});

function initializeNotificationCenter() {
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        const isVisible = dropdown.style.display !== 'none';

        if (isVisible) {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
            loadNotifications();
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== bell) {
            dropdown.style.display = 'none';
        }
    });
}

function loadNotifications() {
    fetch('<?php echo e(route('admin.notifications.index')); ?>?limit=50')
        .then(res => res.json())
        .then(data => {
            notifications = data.notifications || [];
            updateNotificationBadge();
            renderNotifications();
        })
        .catch(err => {
            console.error('Failed to load notifications:', err);
        });
}

function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    const pulse = document.getElementById('notificationPulse');
    const unreadCount = notifications.filter(n => !n.read_at).length;

    document.getElementById('countAll').textContent = notifications.length;
    document.getElementById('countUnread').textContent = unreadCount;

    if (unreadCount > 0) {
        badge.style.display = 'flex';
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
        pulse.style.display = 'block';
    } else {
        badge.style.display = 'none';
        pulse.style.display = 'none';
    }
}

function renderNotifications() {
    const container = document.getElementById('notificationList');
    let filteredNotifications = notifications;

    if (currentFilter === 'unread') {
        filteredNotifications = notifications.filter(n => !n.read_at);
    } else if (currentFilter !== 'all') {
        filteredNotifications = notifications.filter(n => n.type === currentFilter);
    }

    if (filteredNotifications.length === 0) {
        container.innerHTML = `
            <div class="empty-notifications">
                <i class="bi bi-bell-slash"></i>
                <p class="mb-0">No notifications</p>
            </div>
        `;
        return;
    }

    container.innerHTML = filteredNotifications.map(notif => {
        const data = JSON.parse(notif.data || '{}');
        const iconClass = getNotificationIconClass(notif.type);
        const icon = getNotificationIcon(notif.type);

        return `
            <div class="notification-item ${!notif.read_at ? 'unread' : ''}"
                 onclick="handleNotificationClick('${notif.id}', '${notif.action_url || '#'}')">
                <div class="d-flex align-items-start">
                    <div class="notif-icon ${iconClass}">
                        <i class="${icon}"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">${notif.title}</div>
                        <div class="notif-message">${notif.message}</div>
                        <div class="notif-time">
                            <i class="bi bi-clock me-1"></i>${formatNotificationTime(notif.created_at)}
                        </div>
                        ${!notif.read_at ? `
                            <div class="notif-actions">
                                <button class="notif-action-btn notif-action-primary"
                                        onclick="event.stopPropagation(); markAsRead('${notif.id}')">
                                    Mark as read
                                </button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function getNotificationIconClass(type) {
    const iconMap = {
        'job_approved': 'success',
        'job_rejected': 'warning',
        'new_application': 'info',
        'new_job': 'info',
        'kyc_approved': 'success',
        'kyc_rejected': 'danger',
        'admin_announcement': 'info'
    };
    return iconMap[type] || 'info';
}

function getNotificationIcon(type) {
    const iconMap = {
        'job_approved': 'bi-check-circle',
        'job_rejected': 'bi-x-circle',
        'new_application': 'bi-file-earmark-text',
        'new_job': 'bi-briefcase',
        'kyc_approved': 'bi-shield-check',
        'kyc_rejected': 'bi-shield-x',
        'admin_announcement': 'bi-megaphone'
    };
    return iconMap[type] || 'bi-bell';
}

function formatNotificationTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;

    return date.toLocaleDateString();
}

function filterNotifications(filter) {
    currentFilter = filter;

    document.querySelectorAll('.notif-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`.notif-tab[data-filter="${filter}"]`).classList.add('active');

    renderNotifications();
}

function handleNotificationClick(notificationId, actionUrl) {
    markAsRead(notificationId);

    if (actionUrl && actionUrl !== '#') {
        setTimeout(() => {
            window.location.href = actionUrl;
        }, 200);
    }
}

function markAsRead(notificationId) {
    fetch(`<?php echo e(route('admin.notifications.mark-read')); ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(() => {
        const notif = notifications.find(n => n.id == notificationId);
        if (notif) {
            notif.read_at = new Date().toISOString();
        }
        updateNotificationBadge();
        renderNotifications();
    });
}

function markAllAsRead() {
    const unreadIds = notifications.filter(n => !n.read_at).map(n => n.id);

    if (unreadIds.length === 0) {
        showAdminToast('No unread notifications', 'info');
        return;
    }

    fetch(`<?php echo e(route('admin.notifications.mark-all-read')); ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notification_ids: unreadIds })
    })
    .then(() => {
        notifications.forEach(n => {
            if (unreadIds.includes(n.id)) {
                n.read_at = new Date().toISOString();
            }
        });
        updateNotificationBadge();
        renderNotifications();
        showAdminToast('All notifications marked as read', 'success');
    });
}

function refreshNotifications() {
    document.getElementById('notificationList').innerHTML = `
        <div class="notification-loading">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span class="ms-2">Refreshing...</span>
        </div>
    `;

    loadNotifications();
}
</script>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/partials/notification-center.blade.php ENDPATH**/ ?>