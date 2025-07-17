<!-- User Profile Section -->
<div class="employer-sidebar">
    <div class="user-profile mb-4">
        <div class="d-flex align-items-center gap-3">
            @if(auth()->user()->profile_photo)
                <img src="{{ asset('profile_img/' . auth()->user()->profile_photo) }}" 
                     alt="Profile Photo" 
                     class="rounded-circle profile-photo">
            @else
                <div class="profile-initial rounded-circle">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            @endif
            <div>
                <h6 class="mb-0 fw-semibold">{{ auth()->user()->name }}</h6>
                <small class="text-muted">{{ auth()->user()->email }}</small>
            </div>
        </div>
    </div>

    <!-- Platform Section -->
    <div class="sidebar-section mb-4">
        <p class="sidebar-label mb-3">Platform</p>
        
        <div class="nav-item">
            <a href="{{ route('employer.dashboard') }}" 
               class="nav-link {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('employer.jobs.index') }}" 
               class="nav-link {{ request()->routeIs('employer.jobs.*') ? 'active' : '' }}">
                <i class="bi bi-briefcase"></i>
                <span>Jobs</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('employer.applications.index') }}" 
               class="nav-link {{ request()->routeIs('employer.applications.*') ? 'active' : '' }}">
                <i class="bi bi-file-text"></i>
                <span>Applications</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('employer.analytics.index') }}" 
               class="nav-link {{ request()->routeIs('employer.analytics.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span>Analytics</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('employer.profile.edit') }}" 
               class="nav-link {{ request()->routeIs('employer.profile.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span>Company Profile</span>
            </a>
        </div>
    </div>

    <!-- Settings Section -->
    <div class="sidebar-section">
        <p class="sidebar-label mb-3">Settings</p>

        <div class="nav-item">
            <a href="{{ route('employer.settings.index') }}" 
               class="nav-link {{ request()->routeIs('employer.settings.index') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>General</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('employer.settings.notifications') }}" 
               class="nav-link {{ request()->routeIs('employer.settings.notifications') ? 'active' : '' }}">
                <i class="bi bi-bell"></i>
                <span>Notifications</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('employer.settings.security') }}" 
               class="nav-link {{ request()->routeIs('employer.settings.security') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i>
                <span>Security</span>
            </a>
        </div>

        <div class="nav-item">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.employer-sidebar {
    background: var(--white);
    padding: 1.5rem;
    height: 100%;
}

.profile-photo {
    width: 42px;
    height: 42px;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.profile-initial {
    width: 42px;
    height: 42px;
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--primary-color);
    border: 2px solid var(--border-color);
}

.sidebar-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--text-light);
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-left: 0.75rem;
}

.nav-item {
    margin-bottom: 0.375rem;
}

.nav-link {
    color: var(--text-dark);
    padding: 0.625rem 0.75rem;
    border-radius: var(--border-radius);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.875rem;
    text-decoration: none;
    font-size: 0.9375rem;
}

.nav-link i {
    font-size: 1.25rem;
    flex-shrink: 0;
    width: 1.5rem;
    text-align: center;
}

.nav-link span {
    font-weight: 500;
}

.nav-link:hover {
    background-color: var(--hover-bg);
    color: var(--primary-color);
    transform: translateX(4px);
}

.nav-link.active {
    background-color: var(--primary-color);
    color: var(--white);
    box-shadow: 0 4px 8px rgba(79, 70, 229, 0.15);
}

.nav-link.active:hover {
    transform: translateX(4px);
    background-color: var(--primary-dark);
}

/* Logout button styling */
button.nav-link {
    cursor: pointer;
    color: var(--danger-color);
}

button.nav-link:hover {
    background-color: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}
</style>
