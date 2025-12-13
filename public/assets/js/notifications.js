/**
 * Notifications handling script
 */
$(document).ready(function() {
    // Mark notification as read when clicked
    $(document).on('click', '#notification-list a', function(e) {
        e.preventDefault();
        const notificationId = $(this).data('notification-id');
        const actionUrl = $(this).data('action-url');
        
        $.ajax({
            url: `/notifications/mark-as-read/${notificationId}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Redirect to action URL if available, otherwise stay on page
                if (actionUrl) {
                    window.location.href = actionUrl;
                }
                
                // Update notification count
                refreshNotifications();
            }
        });
    });
    
    // Mark all notifications as read
    $(document).on('click', '#mark-all-read', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $.ajax({
            url: '/notifications/mark-all-as-read',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Update notification count and list
                refreshNotifications();
            }
        });
    });
    
    // Function to refresh notifications
    function refreshNotifications() {
        $.ajax({
            url: '/notifications/recent',
            type: 'GET',
            success: function(response) {
                // Update notification count
                const unreadCount = response.unreadCount;
                $('#notification-count').text(unreadCount + ' new');
                
                // Update notification badge
                if (unreadCount > 0) {
                    $('#notification-badge').text(unreadCount);
                    $('#mark-all-read').show();
                } else {
                    $('#notification-badge').text('');
                    $('#mark-all-read').hide();
                }
                
                // Update notification list
                let notificationHtml = '';
                if (response.notifications.length > 0) {
                    response.notifications.forEach(function(notification) {
                        const isUnread = notification.read_at === null;
                        const bgClass = isUnread ? 'bg-light' : '';
                        
                        let iconClass = 'fas fa-bell text-secondary';
                        if (notification.type === 'job_application') iconClass = 'fas fa-file-alt text-primary';
                        else if (notification.type === 'job_saved') iconClass = 'fas fa-heart text-danger';
                        else if (notification.type === 'application_status') iconClass = 'fas fa-user text-success';
                        else if (notification.type === 'job_match') iconClass = 'fas fa-briefcase text-primary';
                        else if (notification.type === 'profile_view') iconClass = 'fas fa-eye text-info';
                        else if (notification.type === 'message') iconClass = 'fas fa-envelope text-warning';
                        
                        const createdAt = new Date(notification.created_at);
                        const now = new Date();
                        const diffTime = Math.abs(now - createdAt);
                        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
                        const diffMinutes = Math.floor(diffTime / (1000 * 60));
                        
                        let timeAgo = '';
                        if (diffDays > 0) {
                            timeAgo = diffDays + (diffDays === 1 ? ' day ago' : ' days ago');
                        } else if (diffHours > 0) {
                            timeAgo = diffHours + (diffHours === 1 ? ' hour ago' : ' hours ago');
                        } else if (diffMinutes > 0) {
                            timeAgo = diffMinutes + (diffMinutes === 1 ? ' minute ago' : ' minutes ago');
                        } else {
                            timeAgo = 'just now';
                        }
                        
                        notificationHtml += `
                            <li>
                                <a class="dropdown-item py-2 ${bgClass}" 
                                   href="#" 
                                   data-notification-id="${notification.id}"
                                   data-action-url="${notification.action_url || ''}">
                                    <div class="d-flex">
                                        <i class="${iconClass} me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-bold">${notification.title}</div>
                                            <small class="text-muted">${notification.message}</small>
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                ${timeAgo}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        `;
                    });
                } else {
                    notificationHtml = '<li><div class="dropdown-item text-center py-3">No notifications</div></li>';
                }
                
                $('#notification-list').html(notificationHtml);
            }
        });
    }
    
    // Refresh notifications every 60 seconds
    setInterval(refreshNotifications, 60000);
});