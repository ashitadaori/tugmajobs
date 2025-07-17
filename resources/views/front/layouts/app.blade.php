<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TugmaJobs') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/modern-style.css') }}" rel="stylesheet">
    
    <!-- Mapbox GL JS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
    
    @stack('styles')
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #0891b2;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --bg-dark: #1e293b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .navbar {
            background: #fff;
            box-shadow: var(--shadow-sm);
            padding: 1rem;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            background-color: var(--bg-light);
        }

        .main-content {
            margin-top: 5rem;
            min-height: calc(100vh - 5rem);
            padding: 2rem 0;
        }

        .card {
            background: #fff;
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
        }

        .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            border-radius: var(--radius);
            padding: 0.5rem;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--bg-light);
            color: var(--primary-color);
        }

        .alert {
            border: none;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
            border-radius: var(--radius-sm);
        }
    </style>
</head>
<body>
    @auth
        <x-kyc-reminder-banner />
    @endauth
    
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-briefcase me-2"></i>
                {{ config('app.name', 'JobPortal') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('jobs') }}" class="nav-link {{ request()->routeIs('jobs*') ? 'active' : '' }}">
                            <i class="fas fa-search me-1"></i> Find Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('companies') }}" class="nav-link {{ request()->routeIs('companies*') ? 'active' : '' }}">
                            <i class="fas fa-building me-1"></i> Companies
                        </a>
                    </li>
                    
                    @auth
                        @if(Auth::user()->isJobSeeker())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="jobSeekerDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-tie me-1"></i> My Career
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('account.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('account.myJobApplications') }}">
                                        <i class="fas fa-file-alt me-2"></i> My Applications
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('account.savedJobs') }}">
                                        <i class="fas fa-heart me-2"></i> Saved Jobs
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('account.resumes') }}">
                                        <i class="fas fa-file-pdf me-2"></i> My Resumes
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('account.jobAlerts') }}">
                                        <i class="fas fa-bell me-2"></i> Job Alerts
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('account.ai.job-match') }}">
                                        <i class="fas fa-robot me-2"></i> AI Job Match
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('account.ai.resume-builder') }}">
                                        <i class="fas fa-magic me-2"></i> AI Resume Builder
                                    </a></li>
                                </ul>
                            </li>
                        @endif
                        
                        @if(Auth::user()->isEmployer())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="employerDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-building me-1"></i> Employer Hub
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('employer.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.jobs.index') }}">
                                        <i class="fas fa-briefcase me-2"></i> My Jobs
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.jobs.create') }}">
                                        <i class="fas fa-plus me-2"></i> Post a Job
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.applications.index') }}">
                                        <i class="fas fa-users me-2"></i> Applications
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.applications.shortlisted') }}">
                                        <i class="fas fa-star me-2"></i> Shortlisted
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.analytics.index') }}">
                                        <i class="fas fa-chart-bar me-2"></i> Analytics
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.profile.edit') }}">
                                        <i class="fas fa-edit me-2"></i> Company Profile
                                    </a></li>
                                </ul>
                            </li>
                        @endif
                        
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i> Admin Panel
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                        <i class="fas fa-users me-2"></i> Users
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.jobs.index') }}">
                                        <i class="fas fa-briefcase me-2"></i> Jobs
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}">
                                        <i class="fas fa-tags me-2"></i> Categories
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.job-types.index') }}">
                                        <i class="fas fa-list me-2"></i> Job Types
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.analytics.dashboard') }}">
                                        <i class="fas fa-chart-line me-2"></i> Analytics
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                        <i class="fas fa-cog me-2"></i> Settings
                                    </a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>
                
                <!-- User Account Section -->
                <ul class="navbar-nav align-items-center">
                    @auth
                        <!-- Notifications -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    3
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <small class="text-muted">3 new</small>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2" href="#">
                                    <div class="d-flex">
                                        <i class="fas fa-briefcase text-primary me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-bold">New job match found</div>
                                            <small class="text-muted">Software Engineer at TechCorp</small>
                                        </div>
                                    </div>
                                </a></li>
                                <li><a class="dropdown-item py-2" href="#">
                                    <div class="d-flex">
                                        <i class="fas fa-user text-success me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-bold">Application viewed</div>
                                            <small class="text-muted">Your application was reviewed</small>
                                        </div>
                                    </div>
                                </a></li>
                                <li><a class="dropdown-item py-2" href="#">
                                    <div class="d-flex">
                                        <i class="fas fa-heart text-danger me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-bold">Job saved</div>
                                            <small class="text-muted">Frontend Developer position</small>
                                        </div>
                                    </div>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                            </ul>
                        </li>
                        
                        <!-- User Profile Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                @if(Auth::user()->profile_photo)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile" class="user-avatar me-2">
                                @else
                                    <div class="user-avatar me-2 bg-primary text-white d-flex align-items-center justify-content-center">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-header">
                                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Role-specific dashboard links -->
                                @if(Auth::user()->isEmployer())
                                    <li><a class="dropdown-item" href="{{ route('employer.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a></li>
                                @elseif(Auth::user()->isJobSeeker())
                                    <li><a class="dropdown-item" href="{{ route('account.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a></li>
                                @elseif(Auth::user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                                    </a></li>
                                @endif
                                
                                <li><a class="dropdown-item" href="{{ route('account.myProfile') }}">
                                    <i class="fas fa-user me-2"></i> My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('account.settings') }}">
                                    <i class="fas fa-cog me-2"></i> Settings
                                </a></li>
                                
                                @if(Auth::user()->isEmployer())
                                    <li><a class="dropdown-item" href="{{ route('employer.settings.index') }}">
                                        <i class="fas fa-building me-2"></i> Company Settings
                                    </a></li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="dropdown-item p-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <!-- Guest Navigation -->
                        <li class="nav-item me-2">
                            <a href="{{ route('login') }}" class="nav-link">
                                <i class="fas fa-sign-in-alt me-1"></i> Sign In
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="{{ route('register') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i> Get Started
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        @if(request()->routeIs('home'))
            <!-- Homepage gets fullscreen treatment -->
            @if(Session::has('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(Session::has('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ Session::get('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        @else
            <!-- Other pages get normal container -->
            <div class="container">
                @if(Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ Session::get('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        @endif
    </div>

    <!-- Mapbox GL JS -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
    
    @stack('scripts')
</body>
</html>
