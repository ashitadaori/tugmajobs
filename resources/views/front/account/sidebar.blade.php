<!-- Modern Sidebar Navigation -->
<div class="modern-sidebar">
    <div class="sidebar-user mb-4">
        <div class="d-flex align-items-center">
            @if(Auth::user()->profile_photo)
                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile" class="sidebar-avatar me-3">
            @else
                <div class="sidebar-avatar-placeholder me-3">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
            @endif
            <div>
                <h6 class="mb-1">{{ Auth::user()->name }}</h6>
                <p class="text-muted mb-0 small">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="menu-section">
            <h6 class="menu-title">MAIN MENU</h6>
            
            <a href="{{ route('account.dashboard') }}" class="menu-item {{ request()->routeIs('account.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('jobs') }}" class="menu-item {{ request()->routeIs('jobs') ? 'active' : '' }}">
                <i class="fas fa-search"></i>
                <span>Find Jobs</span>
            </a>

            <a href="{{ route('account.savedJobs') }}" class="menu-item {{ request()->routeIs('account.savedJobs') ? 'active' : '' }}">
                <i class="fas fa-bookmark"></i>
                <span>Saved Jobs</span>
                @if(isset($saved_jobs_count) && $saved_jobs_count > 0)
                    <span class="badge bg-primary rounded-pill ms-auto">{{ $saved_jobs_count }}</span>
                @endif
            </a>
        </div>

        <div class="menu-section">
            <h6 class="menu-title">APPLICATIONS</h6>
            
            <a href="{{ route('account.myJobApplications') }}" class="menu-item {{ request()->routeIs('account.myJobApplications') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>My Applications</span>
                @if(isset($pending_applications) && $pending_applications > 0)
                    <span class="badge bg-warning rounded-pill ms-auto">{{ $pending_applications }}</span>
                @endif
            </a>

            <a href="{{ route('account.jobAlerts') }}" class="menu-item {{ request()->routeIs('account.jobAlerts') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Job Alerts</span>
            </a>
        </div>

        <div class="menu-section">
            <h6 class="menu-title">PROFILE</h6>
            
            <a href="{{ route('account.myProfile') }}" class="menu-item {{ request()->routeIs('account.myProfile') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>

            <a href="{{ route('account.resumes') }}" class="menu-item {{ request()->routeIs('account.resumes') ? 'active' : '' }}">
                <i class="fas fa-file"></i>
                <span>Resumes</span>
            </a>
        </div>

        <div class="menu-section">
            <h6 class="menu-title">SETTINGS</h6>
            
            <a href="{{ route('account.changePassword') }}" class="menu-item {{ request()->routeIs('account.changePassword') ? 'active' : '' }}">
                <i class="fas fa-lock"></i>
                <span>Change Password</span>
            </a>

            <a href="{{ route('account.deleteProfile') }}" class="menu-item {{ request()->routeIs('account.deleteProfile') ? 'active' : '' }}">
                <i class="fas fa-trash"></i>
                <span>Delete Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="menu-item text-danger w-100 bg-transparent border-0">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log out</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.modern-sidebar {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    height: 100%;
}

.sidebar-user {
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.sidebar-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.sidebar-avatar-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
}

.menu-section {
    margin-bottom: 1.5rem;
}

.menu-title {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-light);
    margin-bottom: 0.75rem;
    padding-left: 0.5rem;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: var(--text-dark);
    text-decoration: none;
    border-radius: var(--radius-sm);
    margin-bottom: 0.25rem;
    transition: all 0.2s ease;
}

.menu-item:hover {
    background-color: var(--bg-light);
    color: var(--primary-color);
}

.menu-item.active {
    background-color: var(--primary-color);
    color: white;
}

.menu-item i {
    width: 20px;
    margin-right: 0.75rem;
    font-size: 16px;
}

.menu-item span {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
}

.badge {
    font-size: 11px;
    padding: 0.35em 0.65em;
}

@media (max-width: 991.98px) {
    .modern-sidebar {
        margin-bottom: 2rem;
    }
}
</style>


