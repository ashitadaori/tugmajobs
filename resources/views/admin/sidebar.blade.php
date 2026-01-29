<!-- Admin Sidebar -->
<div class="admin-sidebar">
    <div class="sidebar-header">
        <h4 class="sidebar-title">Admin Panel</h4>
    </div>

    <div class="sidebar-menu">
        <!-- Main Section -->
        <div class="menu-section">
            <div class="menu-title">Main</div>

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
                class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>

            <!-- Analytics -->
            <a href="{{ route('admin.analytics.dashboard') }}"
                class="menu-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i>
                <span>Analytics</span>
            </a>
        </div>

        <!-- Job Management Section -->
        <div class="menu-section">
            <div class="menu-title">Job Management</div>

            <!-- Post New Job -->
            <a href="{{ route('admin.jobs.create') }}"
                class="menu-item menu-item-highlight {{ request()->routeIs('admin.jobs.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>Post New Job</span>
            </a>

            <!-- All Jobs -->
            <a href="{{ route('admin.jobs.index') }}"
                class="menu-item {{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>All Jobs</span>
            </a>

            <!-- Pending Jobs -->
            <a href="{{ route('admin.jobs.pending') }}"
                class="menu-item {{ request()->routeIs('admin.jobs.pending') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span>Pending Approval</span>
                @php
                    $pendingCount = \App\Models\Job::where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-warning text-dark">{{ $pendingCount }}</span>
                @endif
            </a>

            <!-- Categories -->
            <a href="{{ route('admin.categories.index') }}"
                class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-folder-open"></i>
                <span>Categories</span>
            </a>
        </div>

        <!-- User Management Section -->
        <div class="menu-section">
            <div class="menu-title">User Management</div>

            <!-- All Users -->
            <a href="{{ route('admin.users.index') }}"
                class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>All Users</span>
            </a>

            <!-- Employer Accounts -->
            <a href="{{ route('admin.companies.index') }}"
                class="menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Employer Accounts</span>
            </a>

            <!-- Company Management -->
            <a href="{{ route('admin.company-management.index') }}"
                class="menu-item {{ request()->routeIs('admin.company-management.*') ? 'active' : '' }}">
                <i class="fas fa-city"></i>
                <span>Company Profiles</span>
            </a>
        </div>

        <!-- Verification Section -->
        <div class="menu-section">
            <div class="menu-title">Verification</div>

            <!-- KYC Verifications Dropdown -->
            <div class="kyc-menu-group">
                <a href="#kycSubmenu"
                    class="menu-item d-flex justify-content-between align-items-center {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.kyc.*') ? 'true' : 'false' }}">
                    <span>
                        <i class="fas fa-user-check"></i>
                        <span>KYC Verification</span>
                    </span>
                    @php
                        $totalKycPending = \App\Models\KycDocument::where('status', 'pending')->count();
                    @endphp
                    @if($totalKycPending > 0)
                        <span class="badge bg-danger">{{ $totalKycPending }}</span>
                    @else
                        <i class="fas fa-chevron-down chevron-icon"></i>
                    @endif
                </a>
                <div class="collapse {{ request()->routeIs('admin.kyc.*') ? 'show' : '' }}" id="kycSubmenu">
                    <a href="{{ route('admin.kyc.didit-verifications') }}"
                        class="menu-item submenu-item {{ request()->routeIs('admin.kyc.didit-verifications') || request()->routeIs('admin.kyc.show-didit-verification') ? 'active' : '' }}">
                        <i class="fas fa-robot"></i>
                        <span>DiDit Auto</span>
                    </a>
                    <a href="{{ route('admin.kyc.manual-documents') }}"
                        class="menu-item submenu-item {{ request()->routeIs('admin.kyc.manual-documents') || request()->routeIs('admin.kyc.show-manual-document') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span>Manual Review</span>
                        @php
                            $manualKycPendingCount = \App\Models\KycDocument::where('status', 'pending')->count();
                        @endphp
                        @if($manualKycPendingCount > 0)
                            <span class="badge bg-warning text-dark">{{ $manualKycPendingCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Employer Documents -->
            <a href="{{ route('admin.employers.documents.index') }}"
                class="menu-item {{ request()->routeIs('admin.employers.documents.*') ? 'active' : '' }}">
                <i class="fas fa-file-contract"></i>
                <span>Employer Docs</span>
                @php
                    $pendingDocsCount = \App\Models\EmployerDocument::where('status', 'pending')->count();
                @endphp
                @if($pendingDocsCount > 0)
                    <span class="badge bg-warning text-dark">{{ $pendingDocsCount }}</span>
                @endif
            </a>
        </div>

        <!-- Content & Tools Section -->
        <div class="menu-section">
            <div class="menu-title">Content & Tools</div>

            <!-- Poster Builder -->
            <a href="{{ route('admin.posters.index') }}"
                class="menu-item {{ request()->routeIs('admin.posters.*') ? 'active' : '' }}">
                <i class="fas fa-palette"></i>
                <span>Poster Builder</span>
            </a>

            <!-- Audit Reports -->
            <a href="{{ route('admin.audit-reports.index') }}"
                class="menu-item {{ request()->routeIs('admin.audit-reports.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Audit Reports</span>
            </a>
        </div>

        @if(auth()->user()?->role === 'superadmin')
            <!-- System Section (Superadmin Only) -->
            <div class="menu-section">
                <div class="menu-title">System</div>

                <!-- Admin Management -->
                <a href="{{ route('admin.admins.index') }}"
                    class="menu-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin Accounts</span>
                </a>

                <!-- Site Settings -->
                <a href="{{ route('admin.settings.index') }}"
                    class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>

                <!-- Maintenance Mode -->
                <a href="{{ route('admin.maintenance.index') }}"
                    class="menu-item {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance</span>
                </a>
            </div>
        @endif

        <!-- Account Section -->
        <div class="menu-section menu-section-bottom">
            <div class="menu-divider"></div>

            <div class="user-info-section">
                <span class="user-name">{{ Auth::user()?->name ?? 'Admin' }}</span>
                <span class="user-role">{{ ucfirst(Auth::user()?->role ?? 'admin') }}</span>
            </div>

            <!-- Profile -->
            <a href="{{ route('admin.profile.edit') }}"
                class="menu-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
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
