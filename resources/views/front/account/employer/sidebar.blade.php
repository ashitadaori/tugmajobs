@php
    $user = auth()->user();
@endphp

<div class="dashboard-sidebar">
    <div class="user-profile">
        <div class="profile-image">
            @if($user->employerProfile && $user->employerProfile->company_logo)
                <img src="{{ $user->employerProfile->logo_url }}" alt="Company Logo">
            @else
                <div class="profile-initial">{{ substr($user->employerProfile->company_name ?? $user->name, 0, 1) }}</div>
            @endif
        </div>
        <div class="profile-info">
            <h6 class="mb-1">{{ $user->employerProfile->company_name ?? $user->name }}</h6>
            <p class="text-muted mb-0 small">{{ $user->email }}</p>
        </div>
    </div>

    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('employer.dashboard') }}" class="nav-link {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('employer.jobs.index') }}" class="nav-link {{ request()->routeIs('employer.jobs.*') ? 'active' : '' }}">
                    <i class="fas fa-briefcase"></i>
                    <span>My Jobs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('employer.applications.index') }}" class="nav-link {{ request()->routeIs('employer.applications.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Applications</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('employer.analytics.index') }}" class="nav-link {{ request()->routeIs('employer.analytics.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('employer.profile.edit') }}" class="nav-link {{ request()->routeIs('employer.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Company Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('employer.settings.notifications') }}" class="nav-link {{ request()->routeIs('employer.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
:root {
    --header-height: 60px;
    --sidebar-width: 280px;
    --primary-color: #4A6CF7;
    --border-color: #e9ecef;
    --text-dark: #2D3748;
    --text-light: #718096;
    --bg-light: #f8f9fa;
}

.dashboard-sidebar {
    position: fixed;
    top: var(--header-height);
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    background: #fff;
    border-right: 1px solid #e9ecef;
    z-index: 1000;
    transition: all 0.3s ease;
    overflow-y: auto;
}

.user-profile {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
}

.profile-image {
    width: 48px;
    height: 48px;
    margin-right: 1rem;
    flex-shrink: 0;
}

.profile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-initial {
    width: 100%;
    height: 100%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 600;
    border-radius: 50%;
}

.profile-info {
    overflow: hidden;
}

.profile-info h6 {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-menu {
    padding: 1rem 0;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: #6c757d;
    transition: all 0.2s ease;
}

.nav-link:hover {
    color: var(--primary-color);
    background: rgba(74, 108, 247, 0.05);
}

.nav-link.active {
    color: var(--primary-color);
    background: rgba(74, 108, 247, 0.1);
    font-weight: 500;
}

.nav-link i {
    width: 20px;
    margin-right: 0.75rem;
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 991.98px) {
    .dashboard-sidebar {
        transform: translateX(-100%);
    }

    .dashboard-sidebar.show {
        transform: translateX(0);
    }
}
</style>

<div class="sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('.dashboard-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    function toggleSidebar() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 991.98 && 
            !sidebar.contains(e.target) && 
            !toggleBtn.contains(e.target) && 
            sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991.98 && sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    });
});
</script>