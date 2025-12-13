@extends('front.layouts.app')

@section('content')
<div class="notifications-page-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                {{-- Page Header --}}
                <div class="notif-page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="header-text">
                            <h1>Notifications</h1>
                            <p>
                                @if($notifications->where('read_at', null)->count() > 0)
                                    You have <span class="unread-count">{{ $notifications->where('read_at', null)->count() }}</span> unread notification{{ $notifications->where('read_at', null)->count() !== 1 ? 's' : '' }}
                                @else
                                    All caught up!
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($notifications->where('read_at', null)->count() > 0)
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="mark-all-btn">
                                <i class="fas fa-check-double"></i>
                                <span>Mark all as read</span>
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Filter Tabs --}}
                <div class="notif-filter-tabs">
                    <button class="filter-tab active" data-filter="all">
                        <i class="fas fa-list"></i> All
                        <span class="tab-count">{{ $notifications->count() }}</span>
                    </button>
                    <button class="filter-tab" data-filter="unread">
                        <i class="fas fa-envelope"></i> Unread
                        <span class="tab-count">{{ $notifications->where('read_at', null)->count() }}</span>
                    </button>
                    <button class="filter-tab" data-filter="read">
                        <i class="fas fa-envelope-open"></i> Read
                        <span class="tab-count">{{ $notifications->where('read_at', '!=', null)->count() }}</span>
                    </button>
                </div>

                @if($notifications->isEmpty())
                    {{-- Empty State --}}
                    <div class="notif-empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h3>No notifications yet</h3>
                        <p>When you receive notifications, they'll appear here</p>
                        <a href="{{ route('jobs') }}" class="btn-browse-jobs">
                            <i class="fas fa-search me-2"></i> Browse Jobs
                        </a>
                    </div>
                @else
                    {{-- Notifications List --}}
                    <div class="notif-list">
                        @foreach($notifications as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $notifType = $notification->type ?? ($data['type'] ?? '');
                                $iconClass = 'fa-bell';
                                $iconBg = 'default';

                                // Determine icon and color based on type
                                if(str_contains($notifType, 'approved') || str_contains($notifType, 'hired')) {
                                    $iconClass = 'fa-check-circle';
                                    $iconBg = 'success';
                                } elseif(str_contains($notifType, 'rejected')) {
                                    $iconClass = 'fa-times-circle';
                                    $iconBg = 'danger';
                                } elseif(str_contains($notifType, 'interview')) {
                                    $iconClass = 'fa-calendar-check';
                                    $iconBg = 'purple';
                                } elseif(str_contains($notifType, 'application') || str_contains($notifType, 'new_application')) {
                                    $iconClass = 'fa-user-plus';
                                    $iconBg = 'info';
                                } elseif(str_contains($notifType, 'document') || str_contains($notifType, 'requirement')) {
                                    $iconClass = 'fa-file-alt';
                                    $iconBg = 'warning';
                                } elseif(str_contains($notifType, 'job')) {
                                    $iconClass = 'fa-briefcase';
                                    $iconBg = 'primary';
                                }
                            @endphp

                            <div class="notif-item {{ is_null($notification->read_at) ? 'unread' : 'read' }}"
                                 data-status="{{ is_null($notification->read_at) ? 'unread' : 'read' }}">
                                <div class="notif-icon {{ $iconBg }}">
                                    <i class="fas {{ $iconClass }}"></i>
                                </div>
                                <div class="notif-content">
                                    <div class="notif-header">
                                        <h4 class="notif-title">{{ $notification->title }}</h4>
                                        <div class="notif-meta">
                                            @if(is_null($notification->read_at))
                                                <span class="new-badge">New</span>
                                            @endif
                                            <span class="notif-time">
                                                <i class="far fa-clock"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="notif-message">{{ $notification->message }}</p>
                                    <div class="notif-actions">
                                        @if($notification->action_url)
                                            <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="action-btn primary">
                                                <i class="fas fa-external-link-alt"></i>
                                                <span>View Details</span>
                                            </a>
                                        @else
                                            @if(is_null($notification->read_at))
                                                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="action-btn secondary">
                                                        <i class="fas fa-check"></i>
                                                        <span>Mark as read</span>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete" title="Delete notification">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($notifications->hasPages())
                        <div class="notif-pagination">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Notifications Page Container */
.notifications-page-container {
    min-height: calc(100vh - 80px);
    background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
    padding: 30px 0 50px;
}

/* Page Header */
.notif-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.header-icon i {
    font-size: 1.5rem;
    color: #ffffff;
}

.header-text h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.header-text p {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0;
}

.header-text .unread-count {
    font-weight: 700;
    color: #6366f1;
}

.mark-all-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.mark-all-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.mark-all-btn i {
    font-size: 1rem;
}

/* Filter Tabs */
.notif-filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    background: #ffffff;
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
}

.filter-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    background: transparent;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-tab:hover {
    background: #f1f5f9;
    color: #475569;
}

.filter-tab.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.filter-tab .tab-count {
    padding: 2px 8px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}

.filter-tab:not(.active) .tab-count {
    background: #e2e8f0;
    color: #475569;
}

/* Notifications List */
.notif-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Notification Item */
.notif-item {
    display: flex;
    gap: 16px;
    background: #ffffff;
    border-radius: 14px;
    padding: 20px;
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.notif-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: #d1d5db;
}

.notif-item.unread {
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
    border-left: 4px solid #3b82f6;
}

.notif-item.read {
    opacity: 0.85;
}

/* Notification Icon */
.notif-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notif-icon i {
    font-size: 1.25rem;
}

.notif-icon.default {
    background: #f1f5f9;
    color: #64748b;
}

.notif-icon.success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
}

.notif-icon.danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
}

.notif-icon.warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
}

.notif-icon.info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #2563eb;
}

.notif-icon.primary {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4f46e5;
}

.notif-icon.purple {
    background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
    color: #7c3aed;
}

/* Notification Content */
.notif-content {
    flex: 1;
    min-width: 0;
}

.notif-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 8px;
}

.notif-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    line-height: 1.4;
}

.notif-item.unread .notif-title {
    color: #0f172a;
}

.notif-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.new-badge {
    padding: 4px 10px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.notif-time {
    font-size: 0.8rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.notif-time i {
    font-size: 0.75rem;
}

.notif-message {
    font-size: 0.9rem;
    color: #64748b;
    line-height: 1.5;
    margin: 0 0 14px 0;
}

/* Notification Actions */
.notif-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.action-btn i {
    font-size: 0.75rem;
}

.action-btn.primary {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.action-btn.primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    color: #ffffff;
}

.action-btn.secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.action-btn.secondary:hover {
    background: #e2e8f0;
    color: #334155;
}

.action-btn.delete {
    background: transparent;
    color: #94a3b8;
    padding: 8px 12px;
}

.action-btn.delete:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Empty State */
.notif-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
}

.empty-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.empty-icon i {
    font-size: 2.5rem;
    color: #94a3b8;
}

.notif-empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 8px 0;
}

.notif-empty-state p {
    font-size: 0.95rem;
    color: #64748b;
    margin: 0 0 24px 0;
}

.btn-browse-jobs {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-browse-jobs:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: #ffffff;
}

/* Pagination */
.notif-pagination {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}

.notif-pagination .pagination {
    gap: 6px;
}

.notif-pagination .page-link {
    border-radius: 8px;
    padding: 10px 16px;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 500;
    transition: all 0.2s ease;
}

.notif-pagination .page-link:hover {
    background: #f1f5f9;
    border-color: #d1d5db;
    color: #1e293b;
}

.notif-pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .notifications-page-container {
        padding: 20px 0 40px;
    }

    .notif-page-header {
        flex-direction: column;
        gap: 16px;
        padding: 20px;
        text-align: center;
    }

    .header-content {
        flex-direction: column;
    }

    .mark-all-btn {
        width: 100%;
        justify-content: center;
    }

    .notif-filter-tabs {
        overflow-x: auto;
        padding: 6px;
    }

    .filter-tab {
        padding: 10px 14px;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .notif-item {
        padding: 16px;
        gap: 12px;
    }

    .notif-icon {
        width: 42px;
        height: 42px;
    }

    .notif-icon i {
        font-size: 1rem;
    }

    .notif-header {
        flex-direction: column;
        gap: 8px;
    }

    .notif-meta {
        align-self: flex-start;
    }

    .notif-title {
        font-size: 0.95rem;
    }

    .notif-message {
        font-size: 0.85rem;
    }

    .notif-actions {
        flex-wrap: wrap;
    }

    .action-btn span {
        display: none;
    }

    .action-btn {
        padding: 10px 14px;
    }

    .action-btn.primary span,
    .action-btn.secondary span {
        display: inline;
    }
}

@media (max-width: 480px) {
    .header-icon {
        width: 48px;
        height: 48px;
    }

    .header-icon i {
        font-size: 1.25rem;
    }

    .header-text h1 {
        font-size: 1.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter tabs functionality
    const filterTabs = document.querySelectorAll('.filter-tab');
    const notifItems = document.querySelectorAll('.notif-item');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;

            // Filter items
            notifItems.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'flex';
                } else {
                    item.style.display = item.dataset.status === filter ? 'flex' : 'none';
                }
            });
        });
    });

    // Confirm delete
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this notification?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection
