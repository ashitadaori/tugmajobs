<?php if(auth()->guard()->check()): ?>
<div class="dropdown jobseeker-notif-wrapper">
    <button type="button" class="jobseeker-notif-bell-btn <?php echo e(Auth::user()->unreadNotificationsCount > 0 ? 'has-unread' : ''); ?>" id="jobseekerNotifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if(Auth::user()->unreadNotificationsCount > 0): ?>
            <span class="jobseeker-notif-badge"><?php echo e(Auth::user()->unreadNotificationsCount); ?></span>
        <?php endif; ?>
    </button>

    <div class="dropdown-menu dropdown-menu-end jobseeker-notif-dropdown" aria-labelledby="jobseekerNotifDropdown">
        <!-- Header -->
        <div class="jobseeker-notif-header">
            <h6 class="mb-0">Notifications</h6>
            <?php if(Auth::user()->unreadNotificationsCount > 0): ?>
                <button type="button" class="jobseeker-notif-mark-all" id="jobseekerMarkAllRead">
                    Mark all read
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Notification List -->
        <div class="jobseeker-notif-list" id="jobseekerNotifList">
            <?php $__empty_1 = true; $__currentLoopData = Auth::user()->notifications()->latest()->take(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $data = $notification->data ?? [];
                    $notifType = $notification->type ?? ($data['type'] ?? '');
                    $iconClass = 'fa-bell';
                    $iconColor = '#6366f1';

                    // Determine icon based on notification type
                    switch($notifType) {
                        case 'stage_approved':
                        case 'documents_approved':
                            $iconClass = 'fa-check-circle';
                            $iconColor = '#10b981';
                            break;
                        case 'submit_requirements':
                            $iconClass = 'fa-file-upload';
                            $iconColor = '#f59e0b';
                            break;
                        case 'interview_stage':
                        case 'interview_scheduled':
                            $iconClass = 'fa-calendar-check';
                            $iconColor = '#8b5cf6';
                            break;
                        case 'interview_rescheduled':
                            $iconClass = 'fa-calendar-alt';
                            $iconColor = '#f59e0b';
                            break;
                        case 'hired':
                            $iconClass = 'fa-trophy';
                            $iconColor = '#10b981';
                            break;
                        case 'application_rejected':
                            $iconClass = 'fa-times-circle';
                            $iconColor = '#ef4444';
                            break;
                        case 'application_status':
                            if(isset($data['status']) && $data['status'] === 'rejected') {
                                $iconClass = 'fa-times-circle';
                                $iconColor = '#ef4444';
                            } elseif(isset($data['status']) && $data['status'] === 'approved') {
                                $iconClass = 'fa-check-circle';
                                $iconColor = '#10b981';
                            }
                            break;
                        case 'new_company':
                            $iconClass = 'fa-building';
                            $iconColor = '#06b6d4';
                            break;
                        case 'review_response':
                            $iconClass = 'fa-reply';
                            $iconColor = '#8b5cf6';
                            break;
                    }

                    // Get redirect URL - prioritize application detail page if job_application_id exists
                    if (isset($data['job_application_id'])) {
                        $redirectUrl = route('account.showJobApplication', $data['job_application_id']);
                    } elseif ($notification->action_url) {
                        $redirectUrl = $notification->action_url;
                    } else {
                        $redirectUrl = route('account.myJobApplications');
                    }
                ?>

                <a href="<?php echo e($redirectUrl); ?>"
                   class="jobseeker-notif-item <?php echo e(is_null($notification->read_at) ? 'js-notif-unread' : ''); ?>"
                   data-notification-id="<?php echo e($notification->id); ?>"
                   data-redirect-url="<?php echo e($redirectUrl); ?>">
                    <div class="jobseeker-notif-icon">
                        <i class="fas <?php echo e($iconClass); ?>" style="color: <?php echo e($iconColor); ?>;"></i>
                    </div>
                    <div class="jobseeker-notif-content">
                        <div class="jobseeker-notif-title">
                            <?php echo e($notification->title ?? 'Notification'); ?>

                        </div>
                        <div class="jobseeker-notif-message">
                            <?php echo e(Str::limit($notification->message ?? ($data['message'] ?? 'You have a new notification'), 60)); ?>

                        </div>
                        <div class="jobseeker-notif-time">
                            <i class="far fa-clock"></i> <?php echo e($notification->created_at->diffForHumans()); ?>

                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="jobseeker-notif-empty">
                    <i class="far fa-bell-slash"></i>
                    <p>No notifications yet</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="jobseeker-notif-footer">
            <a href="<?php echo e(route('account.notifications.index')); ?>" class="jobseeker-notif-view-all">
                View all notifications
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Jobseeker Notification Dropdown - Clean Design */
.jobseeker-notif-wrapper {
    position: relative !important;
    display: inline-block !important;
}

/* Bell Button - Clean circular design with animation */
.jobseeker-notif-bell-btn {
    position: relative;
    width: 42px;
    height: 42px;
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

.jobseeker-notif-bell-btn:hover {
    background: #ffffff !important;
    border-color: #6366f1 !important;
    color: #6366f1 !important;
    transform: scale(1.05);
}

.jobseeker-notif-bell-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.jobseeker-notif-bell-btn i {
    font-size: 1.1rem !important;
    color: inherit !important;
    transition: transform 0.3s ease;
}

/* Bell ring animation on hover */
.jobseeker-notif-bell-btn:hover i {
    animation: bellRing 0.5s ease-in-out;
}

@keyframes bellRing {
    0% { transform: rotate(0); }
    15% { transform: rotate(14deg); }
    30% { transform: rotate(-14deg); }
    45% { transform: rotate(10deg); }
    60% { transform: rotate(-10deg); }
    75% { transform: rotate(4deg); }
    100% { transform: rotate(0); }
}

/* Continuous subtle animation when there are unread notifications */
.jobseeker-notif-bell-btn.has-unread i {
    animation: bellPulse 2s ease-in-out infinite;
}

@keyframes bellPulse {
    0%, 100% { transform: rotate(0) scale(1); }
    10% { transform: rotate(10deg) scale(1.1); }
    20% { transform: rotate(-8deg) scale(1.1); }
    30% { transform: rotate(6deg) scale(1.05); }
    40% { transform: rotate(-4deg) scale(1.05); }
    50% { transform: rotate(0) scale(1); }
}

/* Notification Badge */
.jobseeker-notif-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    min-width: 18px;
    height: 18px;
    font-size: 0.65rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    border: 2px solid white;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Dropdown Container */
.jobseeker-notif-dropdown {
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
.jobseeker-notif-header {
    padding: 12px 14px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.jobseeker-notif-header h6 {
    color: #ffffff !important;
    margin: 0 !important;
    font-size: 0.875rem !important;
    font-weight: 600 !important;
}

.jobseeker-notif-mark-all {
    font-size: 0.7rem;
    color: #ffffff !important;
    background: rgba(255,255,255,0.2);
    padding: 4px 10px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.jobseeker-notif-mark-all:hover {
    background: rgba(255,255,255,0.3) !important;
}

/* Notification List */
.jobseeker-notif-list {
    max-height: 320px;
    overflow-y: auto;
    overflow-x: hidden;
    background: #ffffff;
}

/* Notification Item */
.jobseeker-notif-item {
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

.jobseeker-notif-item:last-child {
    border-bottom: none !important;
}

.jobseeker-notif-item:hover {
    background-color: #f8fafc !important;
}

.jobseeker-notif-item.js-notif-unread {
    background-color: #eff6ff !important;
    border-left: 3px solid #3b82f6 !important;
}

.jobseeker-notif-item.js-notif-unread:hover {
    background-color: #dbeafe !important;
}

/* Icon */
.jobseeker-notif-icon {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.jobseeker-notif-icon i {
    font-size: 1rem;
}

/* Content */
.jobseeker-notif-content {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.jobseeker-notif-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.jobseeker-notif-message {
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.4;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.jobseeker-notif-time {
    font-size: 0.7rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 3px;
}

.jobseeker-notif-time i {
    font-size: 0.65rem;
}

/* Empty State */
.jobseeker-notif-empty {
    padding: 30px 20px;
    text-align: center;
    color: #94a3b8;
}

.jobseeker-notif-empty i {
    font-size: 2rem;
    opacity: 0.4;
    margin-bottom: 8px;
    display: block;
}

.jobseeker-notif-empty p {
    font-size: 0.85rem;
    font-weight: 500;
    color: #64748b;
    margin: 4px 0;
}

/* Footer */
.jobseeker-notif-footer {
    padding: 10px 14px;
    border-top: 1px solid #f1f5f9;
    background: #f8fafc;
    text-align: center;
}

.jobseeker-notif-view-all {
    color: #6366f1;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.75rem;
    transition: color 0.2s ease;
}

.jobseeker-notif-view-all:hover {
    color: #4f46e5;
    text-decoration: underline;
}

/* Scrollbar - Light themed */
.jobseeker-notif-list::-webkit-scrollbar {
    width: 6px;
    background: transparent;
}

.jobseeker-notif-list::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 3px;
}

.jobseeker-notif-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.jobseeker-notif-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Firefox scrollbar */
.jobseeker-notif-list {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}

/* Ensure dropdown has no dark scrollbar */
.jobseeker-notif-dropdown,
.jobseeker-notif-dropdown * {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.jobseeker-notif-dropdown::-webkit-scrollbar,
.jobseeker-notif-dropdown *::-webkit-scrollbar {
    width: 6px;
    background: transparent;
}

.jobseeker-notif-dropdown::-webkit-scrollbar-track,
.jobseeker-notif-dropdown *::-webkit-scrollbar-track {
    background: transparent;
}

.jobseeker-notif-dropdown::-webkit-scrollbar-thumb,
.jobseeker-notif-dropdown *::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

/* Responsive */
@media (max-width: 480px) {
    .jobseeker-notif-dropdown {
        width: 280px !important;
        right: -10px !important;
    }

    .jobseeker-notif-item {
        padding: 8px 12px !important;
    }

    .jobseeker-notif-icon {
        width: 32px;
        height: 32px;
    }

    .jobseeker-notif-title {
        font-size: 0.75rem;
    }

    .jobseeker-notif-message {
        font-size: 0.7rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Jobseeker notification dropdown loaded');

    // Function to update badge count and remove animation when no unread
    function updateBadgeCount(decrement = 1) {
        const badge = document.querySelector('.jobseeker-notif-badge');
        const bellBtn = document.querySelector('.jobseeker-notif-bell-btn');

        if (badge) {
            let count = parseInt(badge.textContent) || 0;
            count = Math.max(0, count - decrement);

            if (count > 0) {
                badge.textContent = count;
            } else {
                // Fade out and remove badge
                badge.style.transition = 'all 0.3s ease';
                badge.style.transform = 'scale(0)';
                badge.style.opacity = '0';
                setTimeout(() => badge.remove(), 300);

                // Remove has-unread class to stop bell animation
                if (bellBtn) {
                    bellBtn.classList.remove('has-unread');
                }

                // Also hide "Mark all read" button if no unread
                const markAllBtn = document.getElementById('jobseekerMarkAllRead');
                if (markAllBtn) {
                    markAllBtn.style.transition = 'opacity 0.2s';
                    markAllBtn.style.opacity = '0';
                    setTimeout(() => markAllBtn.style.display = 'none', 200);
                }
            }
        }
    }

    // Mark notification as read when clicked and redirect
    document.addEventListener('click', function(e) {
        const notificationItem = e.target.closest('.jobseeker-notif-item');
        if (!notificationItem) return;

        e.preventDefault();
        e.stopPropagation();

        const notificationId = notificationItem.dataset.notificationId;
        const redirectUrl = notificationItem.dataset.redirectUrl || notificationItem.getAttribute('href');
        const isUnread = notificationItem.classList.contains('js-notif-unread');

        // If already read, just redirect immediately
        if (!isUnread) {
            window.location.href = redirectUrl;
            return false;
        }

        // Immediately update UI for better UX
        notificationItem.classList.remove('js-notif-unread');
        notificationItem.style.transition = 'background-color 0.3s ease';
        updateBadgeCount(1);

        // Mark as read first, then redirect
        fetch(`/account/notifications/mark-as-read/${notificationId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Notification marked as read:', data);
            window.location.href = redirectUrl;
        })
        .catch(error => {
            console.error('Error marking as read:', error);
            // Still redirect even if marking as read fails
            window.location.href = redirectUrl;
        });

        return false;
    });

    // Mark all as read
    const markAllBtn = document.getElementById('jobseekerMarkAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Marking...';
            btn.disabled = true;

            fetch('/account/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Remove unread styling from all items
                    document.querySelectorAll('.jobseeker-notif-item.js-notif-unread').forEach(item => {
                        item.classList.remove('js-notif-unread');
                        item.style.transition = 'background-color 0.3s ease';
                    });

                    // Remove badge with animation
                    const badge = document.querySelector('.jobseeker-notif-badge');
                    if (badge) {
                        badge.style.transition = 'all 0.3s ease';
                        badge.style.transform = 'scale(0)';
                        badge.style.opacity = '0';
                        setTimeout(() => badge.remove(), 300);
                    }

                    // Remove has-unread class to stop bell animation
                    const bellBtn = document.querySelector('.jobseeker-notif-bell-btn');
                    if (bellBtn) {
                        bellBtn.classList.remove('has-unread');
                    }

                    // Hide mark all button
                    btn.style.transition = 'opacity 0.2s';
                    btn.style.opacity = '0';
                    setTimeout(() => btn.style.display = 'none', 200);
                } else {
                    throw new Error(data.message || 'Failed to mark as read');
                }
            })
            .catch(error => {
                console.error('Error marking all as read:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }
});
</script>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/jobseeker-notification-dropdown.blade.php ENDPATH**/ ?>