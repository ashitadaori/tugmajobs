<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="cache-version" content="{{ config('app.asset_version', 'v1') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - {{ config('app.name') }}</title>
    <title>Admin Dashboard - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Page Transition Prevention - Must be loaded early -->
    <link href="{{ asset('assets/css/no-blink.css') }}" rel="stylesheet">

    <style>
        :root {
            /* Custom Admin Variables */
            --admin-sidebar-bg: #f8f9fa;
            --admin-sidebar-text: #333;
            --admin-card-bg: #ffffff;
            --admin-border-color: #dee2e6;
        }

        body {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .sidebar {
            min-height: 100vh;
            background: var(--admin-sidebar-bg);
            padding: 20px;
            border-right: 1px solid var(--admin-border-color);
            min-width: 220px;
        }

        .nav-link {
            color: var(--admin-sidebar-text);
            padding: 8px 16px;
            border-radius: 4px;
            margin: 4px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            white-space: nowrap;
            font-size: 0.875rem;
        }

        .nav-link i {
            flex-shrink: 0;
        }

        .nav-link:hover {
            color: var(--bs-primary);
            background: rgba(13, 110, 253, 0.1);
        }

        .nav-link.active {
            background: #0d6efd;
            color: white;
        }

        .content-area {
            padding: 20px;
        }

        .stats-card {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .badge-custom {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .badge-active {
            background: #198754;
            color: white;
        }

        .badge-pending {
            background: #ffc107;
            color: black;
        }

        .badge-suspended {
            background: #dc3545;
            color: white;
        }

        .top-bar {
            background: var(--admin-card-bg);
            border-bottom: 1px solid var(--admin-border-color);
            padding: 1rem;
        }

        .profile-menu .dropdown-toggle::after {
            display: none;
        }

        .profile-menu .dropdown-menu {
            right: 0;
            left: auto;
        }

        .admin-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .sidebar-profile {
            padding: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--admin-border-color);
        }

        .sidebar-profile-info {
            margin-top: 0.5rem;
        }

        .sidebar-profile-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .sidebar-profile-role {
            font-size: 0.875rem;
            color: var(--bs-secondary-color);
        }

        /* Force small pagination arrows */
        .pagination {
            font-size: 14px !important;
        }

        /* Target SVG arrows specifically */
        .pagination svg,
        .pagination .page-link svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            font-size: 14px !important;
        }

        /* Override any inline styles on page links */
        .pagination .page-link {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 36px !important;
            height: 36px !important;
        }

        .pagination .page-item {
            margin: 0 2px !important;
        }

        /* Force arrow text size if using text arrows */
        .pagination .page-link:first-child,
        .pagination .page-link:last-child {
            font-size: 1rem !important;
        }

        /* KYC Submenu Styles */
        .kyc-nav-section {
            margin: 0;
        }

        .kyc-nav-section .kyc-chevron {
            transition: transform 0.3s ease;
            font-size: 0.75rem;
        }

        .kyc-nav-section .nav-link[aria-expanded="true"] .kyc-chevron {
            transform: rotate(180deg);
        }

        #kycSubmenu {
            background: rgba(0, 0, 0, 0.03);
            border-radius: 4px;
            margin: 4px 0;
            padding: 4px 0;
        }

        #kycSubmenu .nav-link {
            font-size: 0.8125rem;
            padding: 6px 12px 6px 24px;
        }

        #kycSubmenu .nav-link.active {
            background: #0d6efd;
            color: white;
        }
    </style>
    @stack('styles')
    @yield('styles')

    <!-- No-Blink Prevention Script - Must run early -->
    <script src="{{ asset('assets/js/no-blink.js') }}"></script>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <!-- Profile Section -->
                <div class="sidebar-profile">
                    <div class="d-flex align-items-center">
                        <img src="{{ Auth::user()->profile_image ?? asset('images/default-profile.svg') }}"
                            alt="Profile" class="admin-avatar me-2">
                        <div class="sidebar-profile-info">
                            <div class="sidebar-profile-name">{{ Auth::user()->name }}</div>
                            <div class="sidebar-profile-role">{{ ucfirst(Auth::user()->role) }}</div>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2">Navigation</p>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i> User Management
                    </a>
                    <a href="{{ route('admin.jobs.create') }}"
                        class="nav-link {{ request()->routeIs('admin.jobs.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle me-2"></i> Post New Job
                    </a>
                    <a href="{{ route('admin.jobs.index') }}"
                        class="nav-link {{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                        <i class="bi bi-list-check me-2"></i> Jobs Posted
                    </a>
                    <a href="{{ route('admin.company-management.index') }}"
                        class="nav-link {{ request()->routeIs('admin.company-management.*') ? 'active' : '' }}">
                        <i class="bi bi-building me-2"></i> Company Management
                    </a>
                    <a href="{{ route('admin.companies.index') }}"
                        class="nav-link {{ request()->routeIs('admin.companies.*') && !request()->routeIs('admin.company-management.*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase me-2"></i> Employer Accounts
                    </a>
                    <a href="{{ route('admin.jobs.pending') }}"
                        class="nav-link {{ request()->routeIs('admin.jobs.pending') ? 'active' : '' }}">
                        <i class="bi bi-clock me-2"></i> Pending Jobs
                    </a>
                    <a href="{{ route('admin.analytics.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up me-2"></i> Analytics
                    </a>
                    <!-- KYC Verifications Section -->
                    <div class="kyc-nav-section">
                        <a href="#kycSubmenu"
                            class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}"
                            data-bs-toggle="collapse"
                            aria-expanded="{{ request()->routeIs('admin.kyc.*') ? 'true' : 'false' }}">
                            <span><i class="bi bi-card-checklist me-2"></i> KYC Verifications</span>
                            <i class="bi bi-chevron-down kyc-chevron"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.kyc.*') ? 'show' : '' }}" id="kycSubmenu">
                            <a href="{{ route('admin.kyc.didit-verifications') }}"
                                class="nav-link ps-4 {{ request()->routeIs('admin.kyc.didit-verifications') || request()->routeIs('admin.kyc.show-didit-verification') ? 'active' : '' }}">
                                <i class="bi bi-robot me-2"></i> DiDit Verifications
                                @php
                                    $pendingKycCount = \App\Models\User::whereHas('kycVerifications', function ($q) {
                                        $q->whereIn('status', ['pending', 'in_progress']);
                                    })->count();
                                @endphp
                                @if($pendingKycCount > 0)
                                    <span class="badge bg-warning text-dark ms-auto">{{ $pendingKycCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.kyc.manual-documents') }}"
                                class="nav-link ps-4 {{ request()->routeIs('admin.kyc.manual-documents') || request()->routeIs('admin.kyc.show-manual-document') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-person me-2"></i> Manual KYC Review
                                @php
                                    $pendingManualKycCount = \App\Models\KycDocument::where('status', 'pending')->count();
                                @endphp
                                @if($pendingManualKycCount > 0)
                                    <span class="badge bg-warning text-dark ms-auto">{{ $pendingManualKycCount }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.employers.documents.index') }}"
                        class="nav-link {{ request()->routeIs('admin.employers.documents.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text me-2"></i> Employer Documents
                        @php
                            $pendingDocsCount = \App\Models\EmployerDocument::where('status', 'pending')->count();
                        @endphp
                        @if($pendingDocsCount > 0)
                            <span class="badge bg-warning text-dark ms-auto">{{ $pendingDocsCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.posters.index') }}"
                        class="nav-link {{ request()->routeIs('admin.posters.*') ? 'active' : '' }}">
                        <i class="bi bi-image me-2"></i> Poster Builder
                    </a>
                </nav>

                <p class="text-muted small mb-2 mt-4">Account</p>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.profile.edit') }}"
                        class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                        <i class="bi bi-person me-2"></i> My Profile
                    </a>
                    <a href="{{ route('admin.profile.password') }}"
                        class="nav-link {{ request()->routeIs('admin.profile.password') ? 'active' : '' }}">
                        <i class="bi bi-lock me-2"></i> Change Password
                    </a>
                </nav>

                <p class="text-muted small mb-2 mt-4">System</p>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.maintenance.index') }}"
                        class="nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                        <i class="bi bi-tools me-2"></i> Maintenance Mode
                    </a>
                    <a href="{{ route('admin.admins.index') }}"
                        class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge me-2"></i> Admin Management
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                        class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear me-2"></i> Site Settings
                    </a>
                </nav>

                <!-- Logout -->
                <div class="mt-4">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Top Bar -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="top-bar d-flex justify-content-end align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Global Search -->
                        @include('admin.partials.global-search')

                        <!-- Notification Center -->
                        @include('admin.partials.notification-center')

                        <!-- Profile Menu -->
                        <div class="profile-menu dropdown">
                            <button class="btn btn-link dropdown-toggle text-dark" type="button" id="profileDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ Auth::user()->profile_image ?? asset('images/default-profile.svg') }}"
                                    alt="Profile" class="admin-avatar">
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                                <li>
                                    <h6 class="dropdown-header">{{ Auth::user()->name }}</h6>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                                        <i class="bi bi-person me-2"></i> My Profile
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.profile.password') }}">
                                        <i class="bi bi-lock me-2"></i> Change Password
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <main class="content-area">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
    @yield('scripts')

    <!-- Toast Notification Container -->
    <div id="adminToastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <script>
        // Admin Toast Notification System
        function showAdminToast(message, type = 'success', duration = 3000) {
            const container = document.getElementById('adminToastContainer');
            const toast = document.createElement('div');

            const bgColors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };

            toast.style.cssText = `
            background: ${bgColors[type] || bgColors.success};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
            font-size: 15px;
            font-weight: 500;
        `;

            toast.innerHTML = `
            <span style="font-size: 20px; font-weight: bold;">${icons[type] || icons.success}</span>
            <span>${message}</span>
        `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // Add animation styles
        if (!document.getElementById('adminToastStyles')) {
            const style = document.createElement('style');
            style.id = 'adminToastStyles';
            style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
            document.head.appendChild(style);
        }

        // Check for session messages
        @if(session('success'))
            showAdminToast('{{ session('success') }}', 'success');
        @endif

        @if(session('error'))
            showAdminToast('{{ session('error') }}', 'error');
        @endif

        @if(session('warning'))
            showAdminToast('{{ session('warning') }}', 'warning');
        @endif

        @if(session('info'))
            showAdminToast('{{ session('info') }}', 'info');
        @endif
    </script>
</body>

</html>