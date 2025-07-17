<!-- Admin Sidebar -->
<div class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <h4>Admin Panel</h4>
        </div>
    </div>

    <div class="sidebar-menu">
        <!-- Global Controls Section -->
        <div class="menu-section">
            <div class="menu-title">Global Controls</div>

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>

            <!-- User Management -->
            <a href="{{ route('admin.users.index') }}" 
               class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>User Management</span>
            </a>

            <!-- Job Management -->
            <a href="{{ route('admin.jobs.pending') }}" 
               class="menu-item {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>Job Management</span>
                @if($pendingJobsCount ?? 0 > 0)
                    <span class="badge bg-warning">{{ $pendingJobsCount }}</span>
                @endif
            </a>

            <!-- KYC Queue -->
            <a href="{{ route('admin.kyc.index') }}" 
               class="menu-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i>
                <span>KYC Queue</span>
                @if($kycPendingCount ?? 0 > 0)
                    <span class="badge bg-danger">{{ $kycPendingCount }}</span>
                @endif
            </a>
        </div>

        <!-- Content Management Section -->
        <div class="menu-section">
            <div class="menu-title">Content Management</div>

            <!-- Categories -->
            <a href="{{ route('admin.categories.index') }}" 
               class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>

            <!-- Job Types -->
            <a href="{{ route('admin.job-types.index') }}" 
               class="menu-item {{ request()->routeIs('admin.job-types.*') ? 'active' : '' }}">
                <i class="fas fa-list"></i>
                <span>Job Types</span>
            </a>
        </div>

        @if(auth()->user()->role === 'superadmin')
        <!-- System Settings Section (Superadmin Only) -->
        <div class="menu-section">
            <div class="menu-title">System Settings</div>

            <!-- Admin Management -->
            <a href="{{ route('admin.admins.index') }}" 
               class="menu-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Admin Management</span>
            </a>

            <!-- Site Settings -->
            <a href="{{ route('admin.settings.index') }}" 
               class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Site Settings</span>
            </a>
        </div>
        @endif

        <!-- Account Section -->
        <div class="menu-section">
            <div class="menu-title">Account</div>

            <!-- Profile -->
            <a href="{{ route('admin.profile.edit') }}" 
               class="menu-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>

            <!-- Change Password -->
            <a href="{{ route('admin.profile.password') }}" 
               class="menu-item {{ request()->routeIs('admin.profile.password') ? 'active' : '' }}">
                <i class="fas fa-lock"></i>
                <span>Change Password</span>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="menu-item logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.admin-sidebar {
    background: #fff;
    border-right: 1px solid #e5e7eb;
    height: 100vh;
    width: 280px;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    z-index: 1000;
    padding: 1rem 0;
}

.sidebar-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1rem;
}

.sidebar-header .logo h4 {
    margin: 0;
    color: #1a1a1a;
    font-weight: 600;
}

.menu-section {
    margin-bottom: 1.5rem;
    padding: 0 1rem;
}

.menu-title {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.5rem;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #4b5563;
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    margin-bottom: 0.25rem;
    position: relative;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.menu-item:hover {
    background: #f3f4f6;
    color: #1a1a1a;
}

.menu-item.active {
    background: #2563eb;
    color: #fff;
}

.menu-item i {
    width: 20px;
    margin-right: 10px;
    font-size: 1rem;
}

.menu-item .badge {
    position: absolute;
    right: 1rem;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.logout-btn {
    color: #ef4444;
}

.logout-btn:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .admin-sidebar {
        background: #1a1a1a;
        border-color: #374151;
    }

    .sidebar-header {
        border-color: #374151;
    }

    .sidebar-header .logo h4 {
        color: #fff;
    }

    .menu-title {
        color: #9ca3af;
    }

    .menu-item {
        color: #9ca3af;
    }

    .menu-item:hover {
        background: #374151;
        color: #fff;
    }

    .menu-item.active {
        background: #2563eb;
        color: #fff;
    }

    .logout-btn {
        color: #ef4444;
    }

    .logout-btn:hover {
        background: #374151;
        color: #f87171;
    }
}
</style>


