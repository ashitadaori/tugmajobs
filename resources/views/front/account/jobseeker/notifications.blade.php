@extends('layouts.jobseeker')

@section('page-title', 'Notifications')

@section('jobseeker-content')
<div class="notifications-page">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">All Notifications</h4>
                @if($notifications->where('read_at', null)->count() > 0)
                    <button type="button" class="btn btn-sm btn-primary" id="markAllReadBtn">
                        <i class="fas fa-check-double me-1"></i> Mark All as Read
                    </button>
                @endif
            </div>
        </div>
        
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    
                    // Check notification types
                    $isReviewResponse = isset($data['type']) && $data['type'] === 'review_response';
                    $isNewCompany = isset($data['type']) && $data['type'] === 'new_company';
                    
                    // Determine icon and color based on notification type
                    $iconClass = 'fa-bell';
                    $iconColor = '#6366f1';
                    $bgClass = '';
                    
                    if($isNewCompany) {
                        $iconClass = 'fa-building';
                        $iconColor = '#06b6d4';
                        $bgClass = $isUnread ? 'bg-info-subtle' : '';
                    } elseif($isReviewResponse) {
                        $iconClass = 'fa-reply';
                        $iconColor = '#8b5cf6';
                        $bgClass = $isUnread ? 'bg-purple-subtle' : '';
                    } elseif(isset($data['status'])) {
                        if($data['status'] === 'rejected') {
                            $iconClass = 'fa-times-circle';
                            $iconColor = '#ef4444';
                            $bgClass = $isUnread ? 'bg-danger-subtle' : '';
                        } elseif($data['status'] === 'approved') {
                            $iconClass = 'fa-check-circle';
                            $iconColor = '#10b981';
                            $bgClass = $isUnread ? 'bg-success-subtle' : '';
                        }
                    } else {
                        $bgClass = $isUnread ? 'bg-info-subtle' : '';
                    }
                @endphp
                
                <div class="notification-item {{ $bgClass }} {{ $isUnread ? 'unread' : '' }}" 
                     data-notification-id="{{ $notification->id }}">
                    <div class="d-flex align-items-start p-4 border-bottom">
                        <div class="notification-icon me-3">
                            <div class="icon-circle" style="background-color: {{ $iconColor }}20;">
                                <i class="fas {{ $iconClass }}" style="color: {{ $iconColor }};"></i>
                            </div>
                        </div>
                        
                        <div class="notification-content flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 fw-bold">
                                    @if($isNewCompany)
                                        🎉 New Company Joined!
                                    @elseif($isReviewResponse)
                                        @if(isset($data['action']) && $data['action'] === 'updated')
                                            Response Updated
                                        @elseif(isset($data['action']) && $data['action'] === 'deleted')
                                            Response Removed
                                        @else
                                            Employer Responded to Your Review
                                        @endif
                                    @elseif(isset($data['job_title']))
                                        Application {{ ucfirst($data['status'] ?? 'Update') }}
                                    @else
                                        Notification
                                    @endif
                                    @if($isUnread)
                                        <span class="badge bg-primary ms-2">New</span>
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>
                            
                            <p class="mb-2 text-muted">
                                @if($isNewCompany)
                                    <strong>{{ $data['company_name'] ?? 'A new company' }}</strong> is now hiring! 
                                    Check out their profile and explore new job opportunities.
                                @elseif($isReviewResponse)
                                    <strong>{{ $data['company_name'] ?? 'An employer' }}</strong> 
                                    @if(isset($data['action']) && $data['action'] === 'updated')
                                        updated their response to your review
                                    @elseif(isset($data['action']) && $data['action'] === 'deleted')
                                        removed their response to your review
                                    @else
                                        responded to your review
                                    @endif
                                    @if(isset($data['job_title']))
                                        for <strong>{{ $data['job_title'] }}</strong>
                                    @endif
                                @elseif(isset($data['job_title']) && isset($data['company_name']))
                                    Your application for <strong>{{ $data['job_title'] }}</strong> 
                                    at <strong>{{ $data['company_name'] }}</strong> 
                                    was {{ $data['status'] ?? 'updated' }}.
                                @else
                                    {{ $data['message'] ?? 'You have a new notification' }}
                                @endif
                            </p>
                            
                            @if($isReviewResponse && isset($data['response']) && $data['response'])
                                <div class="alert alert-light mb-2">
                                    <small>
                                        <i class="fas fa-reply me-1"></i>
                                        <strong>Their Response:</strong> {{ $data['response'] }}
                                    </small>
                                </div>
                            @elseif(isset($data['notes']) && $data['notes'])
                                <div class="alert alert-light mb-2">
                                    <small>
                                        <i class="fas fa-comment-dots me-1"></i>
                                        <strong>Feedback:</strong> {{ $data['notes'] }}
                                    </small>
                                </div>
                            @endif
                            
                            <div class="notification-actions">
                                @if($isNewCompany && isset($data['url']))
                                    <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-building me-1"></i> View Company
                                    </a>
                                @elseif(isset($data['job_application_id']))
                                    <a href="{{ route('account.showJobApplication', $data['job_application_id']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                @else
                                    <a href="{{ route('account.myJobApplications') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View Applications
                                    </a>
                                @endif
                                @if($isUnread)
                                    <button type="button" class="btn btn-sm btn-outline-secondary mark-read-btn" 
                                            data-notification-id="{{ $notification->id }}">
                                        <i class="fas fa-check me-1"></i> Mark as Read
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="far fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications yet</h5>
                    <p class="text-muted">You'll see notifications here when there are updates on your applications.</p>
                    <a href="{{ route('jobs') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-search me-2"></i> Browse Jobs
                    </a>
                </div>
            @endforelse
        </div>
        
        @if($notifications->hasPages())
            <div class="card-footer bg-white">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.notifications-page {
    padding: 0;
}

.notification-item {
    transition: all 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-item.unread {
    border-left: 4px solid #3b82f6;
}

.notification-icon .icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-icon i {
    font-size: 1.5rem;
}

.notification-content h6 {
    color: #1f2937;
}

.notification-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.bg-danger-subtle {
    background-color: #fee2e2 !important;
}

.bg-success-subtle {
    background-color: #d1fae5 !important;
}

.bg-info-subtle {
    background-color: #dbeafe !important;
}
</style>

<script>
$(document).ready(function() {
    // Mark single notification as read
    $('.mark-read-btn').on('click', function() {
        const btn = $(this);
        const notificationId = btn.data('notification-id');
        const notificationItem = btn.closest('.notification-item');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/account/notifications/mark-as-read/${notificationId}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status) {
                    notificationItem.removeClass('unread bg-danger-subtle bg-success-subtle bg-info-subtle');
                    notificationItem.find('.badge').fadeOut();
                    btn.fadeOut();
                    
                    if (typeof showToast === 'function') {
                        showToast('Notification marked as read', 'success', 2000);
                    }
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Mark as Read');
                if (typeof showToast === 'function') {
                    showToast('Failed to mark as read', 'error');
                }
            }
        });
    });
    
    // Mark all as read
    $('#markAllReadBtn').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '/account/notifications/mark-all-as-read',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status) {
                    if (typeof showToast === 'function') {
                        showToast('All notifications marked as read!', 'success', 2000);
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                if (typeof showToast === 'function') {
                    showToast('Failed to mark all as read', 'error');
                }
            }
        });
    });
});
</script>
@endsection
