/**
 * Notification Auto-Mark Read JavaScript
 *
 * Automatically marks notifications as read when user hovers over them or clicks them.
 * Uses AJAX to update notification status without page reload.
 */

(function() {
    'use strict';

    // Configuration
    const config = {
        hoverDelay: 2000, // 2 seconds hover before marking as read
        enableHoverMark: true, // Set to false to disable hover marking
        enableClickMark: true, // Mark on click
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
    };

    // Track hover timers
    const hoverTimers = new Map();

    /**
     * Mark a notification as read via AJAX
     */
    function markNotificationAsRead(notificationId) {
        // Prevent duplicate requests
        if (document.querySelector(`[data-notification-id="${notificationId}"]`)?.dataset.markedAsRead === 'true') {
            return;
        }

        fetch(`/notifications/auto-mark-as-read/${notificationId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mark as read in UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    notificationElement.classList.add('read');
                    notificationElement.dataset.markedAsRead = 'true';

                    // Remove unread indicator
                    const unreadDot = notificationElement.querySelector('.unread-indicator');
                    if (unreadDot) {
                        unreadDot.remove();
                    }
                }

                // Update unread count
                updateUnreadCount();

                // Trigger custom event
                document.dispatchEvent(new CustomEvent('notification:marked-read', {
                    detail: { notificationId: notificationId }
                }));
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    /**
     * Update the unread notification count in the UI
     */
    function updateUnreadCount() {
        fetch('/notifications/unread-count', {
            headers: {
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            const countElements = document.querySelectorAll('.notification-count, .unread-count');
            countElements.forEach(element => {
                element.textContent = data.count;

                // Hide badge if count is 0
                if (data.count === 0) {
                    element.style.display = 'none';
                } else {
                    element.style.display = '';
                }
            });
        })
        .catch(error => {
            console.error('Error updating unread count:', error);
        });
    }

    /**
     * Handle hover events on notifications
     */
    function handleNotificationHover(event) {
        if (!config.enableHoverMark) return;

        const notificationElement = event.currentTarget;
        const notificationId = notificationElement.dataset.notificationId;
        const isUnread = notificationElement.classList.contains('unread');

        if (!notificationId || !isUnread) return;

        // Clear any existing timer for this notification
        if (hoverTimers.has(notificationId)) {
            clearTimeout(hoverTimers.get(notificationId));
        }

        // Set new timer
        const timer = setTimeout(() => {
            markNotificationAsRead(notificationId);
            hoverTimers.delete(notificationId);
        }, config.hoverDelay);

        hoverTimers.set(notificationId, timer);
    }

    /**
     * Handle mouse leave events (cancel hover timer)
     */
    function handleNotificationLeave(event) {
        const notificationElement = event.currentTarget;
        const notificationId = notificationElement.dataset.notificationId;

        if (hoverTimers.has(notificationId)) {
            clearTimeout(hoverTimers.get(notificationId));
            hoverTimers.delete(notificationId);
        }
    }

    /**
     * Handle click events on notifications
     */
    function handleNotificationClick(event) {
        if (!config.enableClickMark) return;

        const notificationElement = event.currentTarget;
        const notificationId = notificationElement.dataset.notificationId;
        const isUnread = notificationElement.classList.contains('unread');

        if (!notificationId || !isUnread) return;

        // Mark immediately on click
        markNotificationAsRead(notificationId);

        // Cancel any pending hover timer
        if (hoverTimers.has(notificationId)) {
            clearTimeout(hoverTimers.get(notificationId));
            hoverTimers.delete(notificationId);
        }
    }

    /**
     * Batch mark multiple notifications as read
     */
    function markMultipleAsRead(notificationIds) {
        fetch('/notifications/mark-as-read-batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                notification_ids: notificationIds
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI for each notification
                notificationIds.forEach(id => {
                    const notificationElement = document.querySelector(`[data-notification-id="${id}"]`);
                    if (notificationElement) {
                        notificationElement.classList.remove('unread');
                        notificationElement.classList.add('read');
                    }
                });

                // Update count
                updateUnreadCount();

                // Show success message
                showNotification(`${data.marked_count} notifications marked as read`);
            }
        })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
        });
    }

    /**
     * Show a toast notification
     */
    function showNotification(message) {
        // Use existing notification system if available, otherwise console
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            console.log(message);
        }
    }

    /**
     * Initialize notification auto-marking
     */
    function init() {
        // Find all notification elements
        const notifications = document.querySelectorAll('[data-notification-id]');

        notifications.forEach(notification => {
            // Add hover listeners
            if (config.enableHoverMark) {
                notification.addEventListener('mouseenter', handleNotificationHover);
                notification.addEventListener('mouseleave', handleNotificationLeave);
            }

            // Add click listener
            if (config.enableClickMark) {
                notification.addEventListener('click', handleNotificationClick);
            }
        });

        // Add "Mark All as Read" button handler
        const markAllButton = document.querySelector('[data-action="mark-all-read"]');
        if (markAllButton) {
            markAllButton.addEventListener('click', function(event) {
                event.preventDefault();

                const unreadIds = Array.from(document.querySelectorAll('[data-notification-id].unread'))
                    .map(el => el.dataset.notificationId);

                if (unreadIds.length === 0) {
                    showNotification('No unread notifications');
                    return;
                }

                markMultipleAsRead(unreadIds);
            });
        }

        // Add "Mark Old as Read" button handler
        const markOldButton = document.querySelector('[data-action="mark-old-read"]');
        if (markOldButton) {
            markOldButton.addEventListener('click', function(event) {
                event.preventDefault();

                const days = parseInt(this.dataset.days || '30');

                fetch('/notifications/mark-old-as-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ days: days }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`${data.marked_count} notifications older than ${days} days marked as read`);
                        // Reload to update UI
                        setTimeout(() => window.location.reload(), 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }

        console.log('Notification auto-mark initialized');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose public API
    window.NotificationAutoMark = {
        markAsRead: markNotificationAsRead,
        updateCount: updateUnreadCount,
        markMultiple: markMultipleAsRead,
        config: config,
    };
})();
