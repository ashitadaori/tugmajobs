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

            <!-- Post New Job -->
            <a href="{{ route('admin.jobs.create') }}"
                class="menu-item {{ request()->routeIs('admin.jobs.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>Post New Job</span>
            </a>

            <!-- Company Management -->
            <a href="{{ route('admin.company-management.index') }}"
                class="menu-item {{ request()->routeIs('admin.company-management.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Company Management</span>
            </a>

            <!-- Employer Companies -->
            <a href="{{ route('admin.companies.index') }}"
                class="menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Employer Accounts</span>
            </a>

            <!-- Pending Jobs -->
            <a href="{{ route('admin.jobs.pending') }}"
                class="menu-item {{ request()->routeIs('admin.jobs.pending') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span>Pending Jobs</span>
                @if($pendingJobsCount ?? 0 > 0)
                    <span class="badge bg-warning">{{ $pendingJobsCount }}</span>
                @endif
            </a>

            <!-- KYC Verifications Dropdown -->
            <div class="kyc-menu-group">
                <a href="#kycSubmenu"
                    class="menu-item d-flex justify-content-between align-items-center {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.kyc.*') ? 'true' : 'false' }}">
                    <span>
                        <i class="fas fa-id-card"></i>
                        <span>KYC Verifications</span>
                    </span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.kyc.*') ? 'show' : '' }}" id="kycSubmenu">
                    <a href="{{ route('admin.kyc.didit-verifications') }}"
                        class="menu-item submenu-item {{ request()->routeIs('admin.kyc.didit-verifications') || request()->routeIs('admin.kyc.show-didit-verification') ? 'active' : '' }}">
                        <i class="fas fa-robot"></i>
                        <span>DiDit Verifications</span>
                    </a>
                    <a href="{{ route('admin.kyc.manual-documents') }}"
                        class="menu-item submenu-item {{ request()->routeIs('admin.kyc.manual-documents') || request()->routeIs('admin.kyc.show-manual-document') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span>Manual Documents</span>
                        @php
                            $manualKycPendingCount = \App\Models\KycDocument::where('status', 'pending')->count();
                        @endphp
                        @if($manualKycPendingCount > 0)
                            <span class="badge bg-warning">{{ $manualKycPendingCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
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

            <!-- Graphic Poster Builder -->
            <a href="{{ route('admin.posters.index') }}"
                class="menu-item {{ request()->routeIs('admin.posters.*') ? 'active' : '' }}">
                <i class="fas fa-image"></i>
                <span>Poster Builder</span>
            </a>

            <!-- Analytics -->
            <a href="{{ route('admin.analytics.dashboard') }}"
                class="menu-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>

            <!-- Audit Reports -->
            <a href="{{ route('admin.audit-reports.index') }}"
                class="menu-item {{ request()->routeIs('admin.audit-reports.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Audit Reports</span>
            </a>

            <!-- Maintenance Mode -->
            <a href="{{ route('admin.maintenance.index') }}"
                class="menu-item {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                <i class="fas fa-tools"></i>
                <span>Maintenance Mode</span>
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
            <form method="POST" action="{{ route('logout') }}">
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
        padding: 1rem;
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
        padding: 0.5rem 1rem;
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

    /* Submenu styles */
    .kyc-menu-group .chevron-icon {
        transition: transform 0.3s ease;
        font-size: 0.75rem;
    }

    .kyc-menu-group .menu-item[aria-expanded="true"] .chevron-icon {
        transform: rotate(180deg);
    }

    .submenu-item {
        padding-left: 2.5rem !important;
        font-size: 0.9rem;
    }

    .submenu-item i {
        font-size: 0.85rem;
    }

    #kycSubmenu {
        background: rgba(0, 0, 0, 0.02);
        border-radius: 0.5rem;
        margin-top: 0.25rem;
    }

    /* Special styling for Post New Job button */
    .menu-item-success {
        background: #10b981;
        color: #fff !important;
    }

    .menu-item-success:hover {
        background: #059669;
        color: #fff !important;
    }

    .menu-item-success.active {
        background: #047857;
        color: #fff !important;
    }

    .menu-item-success i {
        color: #fff;
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