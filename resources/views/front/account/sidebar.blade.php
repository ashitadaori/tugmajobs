<!-- Modern Sidebar Navigation -->
<div class="modern-sidebar">
    <!-- User Profile Section -->
    <div class="user-profile mb-4">
        <div class="d-flex align-items-center">
            <div class="profile-initial rounded-circle bg-indigo-600 text-white">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="ms-3">
                <h6 class="mb-1">{{ Auth::user()->name }}</h6>
                <p class="text-muted mb-0 small">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

    <!-- Platform Section -->
    <div class="menu-section mb-4">
        <h6 class="menu-title">PLATFORM</h6>
        <div class="nav flex-column">
            <a href="{{ route('account.dashboard') }}" class="nav-link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('jobs') }}" class="nav-link {{ request()->routeIs('jobs') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>Jobs</span>
            </a>
            <a href="{{ route('account.applications') }}" class="nav-link {{ request()->routeIs('account.applications') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>Applications</span>
            </a>
            <a href="{{ route('account.analytics') }}" class="nav-link {{ request()->routeIs('account.analytics') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
            <a href="{{ route('account.company') }}" class="nav-link {{ request()->routeIs('account.company') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Company Profile</span>
            </a>
        </div>
    </div>

    <!-- Settings Section -->
    <div class="menu-section">
        <h6 class="menu-title">SETTINGS</h6>
        <div class="nav flex-column">
            <a href="{{ route('account.settings') }}" class="nav-link {{ request()->routeIs('account.settings') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>General</span>
            </a>
            <a href="{{ route('account.notifications') }}" class="nav-link {{ request()->routeIs('account.notifications') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a href="{{ route('account.security') }}" class="nav-link {{ request()->routeIs('account.security') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Security</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="nav-link-form">
                @csrf
                <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.modern-sidebar {
    width: 280px;
    min-height: 100vh;
    background: #ffffff;
    padding: 1.5rem;
    border-right: 1px solid #e5e7eb;
}

.profile-initial {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
}

.menu-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 1rem;
    padding-left: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    border-radius: 0.75rem;
    margin-bottom: 0.25rem;
    transition: background-color 0.2s ease;
}

.nav-link:hover {
    background-color: #4f46e5;
    color: #ffffff;
}

.nav-link.active {
    background-color: #4f46e5;
    color: #ffffff;
}

.nav-link i {
    width: 20px;
    margin-right: 0.75rem;
    font-size: 1rem;
}

.nav-link span {
    font-size: 0.875rem;
    font-weight: 500;
}

.nav-link-form {
    margin-bottom: 0.25rem;
}

.nav-link-form .nav-link {
    margin-bottom: 0;
}

/* Prevent hover scaling or movement */
.nav-link, .nav-link:hover, .nav-link:active {
    transform: none;
    box-shadow: none;
}

/* Ensure consistent height for all nav items */
.nav-link, .nav-link-form .nav-link {
    height: 45px;
}

/* Ensure text and icons stay white on hover */
.nav-link:hover i,
.nav-link.active i,
.nav-link:hover span,
.nav-link.active span {
    color: #ffffff;
}

/* Special styling for logout button */
.nav-link.text-danger:hover {
    background-color: #4f46e5;
    color: #ffffff !important;
}

@media (max-width: 768px) {
    .modern-sidebar {
        width: 100%;
        min-height: auto;
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
    }
}
</style>


