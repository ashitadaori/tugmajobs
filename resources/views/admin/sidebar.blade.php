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

            <!-- Jobs Posted - Added to match layout -->
            <a href="{{ route('admin.jobs.index') }}"
                class="menu-item {{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                <i class="fas fa-list"></i>
                <span>Jobs Posted</span>
            </a>

            <!-- Employer Companies -->
            <a href="{{ route('admin.companies.index') }}"
                class="menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Employer Accounts</span>
            </a>

            <!-- Employer Documents - Added to match layout -->
            <a href="{{ route('admin.employers.documents.index') }}"
                class="menu-item {{ request()->routeIs('admin.employers.documents.*') ? 'active' : '' }}">
                <i class="fas fa-file-contract"></i>
                <span>Employer Documents</span>
                @php
                    $pendingDocsCount = \App\Models\EmployerDocument::where('status', 'pending')->count();
                @endphp
                @if($pendingDocsCount > 0)
                    <span class="badge bg-warning">{{ $pendingDocsCount }}</span>
                @endif
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
                class="menu-item {{ request()->routeIs('admin.analytics.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>

            <!-- K-Means Clustering -->
            <a href="{{ route('admin.analytics.kmeans') }}"
                class="menu-item {{ request()->routeIs('admin.analytics.kmeans*') ? 'active' : '' }}">
                <i class="fas fa-project-diagram"></i>
                <span>K-Means Clustering</span>
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