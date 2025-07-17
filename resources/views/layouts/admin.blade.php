<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #dee2e6;
        }
        .nav-link {
            color: #333;
            padding: 8px 16px;
            border-radius: 4px;
            margin: 4px 0;
        }
        .nav-link:hover {
            background: #e9ecef;
        }
        .nav-link.active {
            background: #0d6efd;
            color: white;
        }
        .content-area {
            padding: 20px;
        }
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            background: white;
            border-bottom: 1px solid #dee2e6;
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
            border-bottom: 1px solid #dee2e6;
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
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <!-- Profile Section -->
                <div class="sidebar-profile">
                    <div class="d-flex align-items-center">
                        <img src="{{ Auth::user()->profile_image ?? asset('images/default-avatar.png') }}" 
                             alt="Profile" class="admin-avatar me-2">
                        <div class="sidebar-profile-info">
                            <div class="sidebar-profile-name">{{ Auth::user()->name }}</div>
                            <div class="sidebar-profile-role">{{ ucfirst(Auth::user()->role) }}</div>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2">Navigation</p>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i> User Management
                    </a>
                    <a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase me-2"></i> Job Management
                    </a>
                    <a href="{{ route('admin.kyc.index') }}" class="nav-link {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check me-2"></i> KYC Queue
                    </a>
                </nav>

                <p class="text-muted small mb-2 mt-4">Account</p>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                        <i class="bi bi-person me-2"></i> My Profile
                    </a>
                    <a href="{{ route('admin.profile.password') }}" class="nav-link {{ request()->routeIs('admin.profile.password') ? 'active' : '' }}">
                        <i class="bi bi-lock me-2"></i> Change Password
                    </a>
                </nav>

                <p class="text-muted small mb-2 mt-4">Superadmin</p>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.admins.index') }}" class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge me-2"></i> Admin Management
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
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
                <div class="top-bar d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">@yield('page_title', 'Dashboard')</h4>
                    </div>
                    <div class="profile-menu dropdown">
                        <button class="btn btn-link dropdown-toggle text-dark" type="button" id="profileDropdown" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->profile_image ?? asset('images/default-avatar.png') }}" 
                                 alt="Profile" class="admin-avatar">
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                            <li><h6 class="dropdown-header">{{ Auth::user()->name }}</h6></li>
                            <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                                <i class="bi bi-person me-2"></i> My Profile
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.profile.password') }}">
                                <i class="bi bi-lock me-2"></i> Change Password
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
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

                <!-- Main Content -->
                <main class="content-area">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
</body>
</html> 