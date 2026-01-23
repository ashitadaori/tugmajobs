{{-- Main Navigation Bar Component --}}
<style>
    /* Ensure ALL navbars in this component are fixed and visible */
    .main-navbar-wrapper>.navbar,
    .main-navbar-wrapper>.modern-navbar {
        position: fixed !important;
        top: 0 !important;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 1000 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: flex !important;
    }

    /* Ensure navbar is visible on homepage */
    body.homepage .main-navbar-wrapper>.navbar,
    body.homepage .main-navbar-wrapper>.modern-navbar,
    body.force-home .main-navbar-wrapper>.navbar,
    body.force-home .main-navbar-wrapper>.modern-navbar {
        position: fixed !important;
        top: 0 !important;
        z-index: 1000 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: flex !important;
        background: rgba(255, 255, 255, 0.95) !important;
    }

    /* Modern Navbar Styles */
    .modern-navbar {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(229, 231, 235, 0.8);
        padding: 0.875rem 0;
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .modern-navbar .nav-link {
        font-weight: 500;
        color: #4b5563;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modern-navbar .nav-link:hover {
        color: #6366f1;
        background: rgba(99, 102, 241, 0.08);
    }

    .modern-navbar .nav-link.active {
        color: #6366f1;
        background: rgba(99, 102, 241, 0.1);
        font-weight: 600;
    }

    .modern-navbar .nav-link i {
        font-size: 0.9rem;
    }

    .modern-navbar .navbar-brand {
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }

    .modern-navbar .brand-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .modern-navbar .brand-text {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 1.25rem;
        letter-spacing: -0.025em;
    }

    .modern-navbar .profile-dropdown-toggle {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.375rem 0.75rem;
        border-radius: 50px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .modern-navbar .profile-dropdown-toggle:hover {
        background: #f1f5f9;
        border-color: #d1d5db;
    }

    .modern-navbar .profile-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .modern-navbar .profile-avatar-placeholder {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .modern-navbar .dropdown-menu {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        padding: 0.5rem;
        margin-top: 0.5rem;
    }

    .modern-navbar .dropdown-item {
        border-radius: 8px;
        padding: 0.625rem 1rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        transition: all 0.15s ease;
    }

    .modern-navbar .dropdown-item:hover {
        background: #f8fafc;
    }

    .modern-navbar .dropdown-item i {
        width: 18px;
        text-align: center;
        color: #6b7280;
    }

    .modern-navbar .dropdown-item.text-danger i {
        color: #ef4444;
    }

    .modern-navbar .mobile-menu-btn {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        color: #4b5563;
        transition: all 0.2s ease;
    }

    .modern-navbar .mobile-menu-btn:hover,
    .modern-navbar .mobile-menu-btn:focus {
        background: #f1f5f9;
        border-color: #d1d5db;
        color: #6366f1;
        box-shadow: none;
    }

    /* Sidebar Toggle Button - for dashboard pages */
    .modern-navbar .sidebar-toggle-btn {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        color: #4b5563;
        transition: all 0.2s ease;
        display: none;
        margin-right: 0.75rem;
    }

    .modern-navbar .sidebar-toggle-btn:hover {
        background: #f1f5f9;
        border-color: #d1d5db;
        color: #6366f1;
    }

    /* Show sidebar toggle on dashboard pages at tablet/mobile */
    @media (max-width: 991.98px) {
        .modern-jobseeker-layout .modern-navbar .sidebar-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }

    /* Ensure navbar items are visible on desktop */
    @media (min-width: 992px) {
        .modern-navbar .navbar-collapse {
            display: flex !important;
            flex-basis: auto;
        }

        .modern-navbar .navbar-nav {
            flex-direction: row;
            gap: 0.25rem;
        }

        .modern-navbar .navbar-nav.me-auto {
            margin-left: 1.5rem !important;
        }
    }

    /* Ensure first nav item (Home) is visible */
    .modern-navbar .navbar-nav .nav-item:first-child {
        display: flex !important;
    }

    .modern-navbar .navbar-nav .nav-item:first-child .nav-link {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Mobile navbar collapse styling */
    @media (max-width: 991.98px) {
        .modern-navbar .navbar-collapse {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.8);
            padding: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .modern-navbar .navbar-nav {
            gap: 0.25rem;
        }

        .modern-navbar .navbar-nav .nav-link {
            padding: 0.75rem 1rem;
        }

        .modern-navbar .navbar-nav.align-items-center {
            align-items: stretch !important;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .modern-navbar .profile-dropdown-toggle {
            justify-content: flex-start;
            width: 100%;
            border-radius: 8px;
        }
    }

    /* Fix for Jobseeker Layout Overlap */
    @media (min-width: 992px) {

        .modern-jobseeker-layout .main-navbar-wrapper>.navbar,
        .modern-jobseeker-layout .main-navbar-wrapper>.modern-navbar {
            left: 280px !important;
            /* var(--m-sidebar-width) */
            width: calc(100% - 280px) !important;
        }
    }
</style>

<div class="main-navbar-wrapper">
    @auth
        @if(Auth::user()->role === 'jobseeker')
            {{-- Modern Jobseeker Navbar --}}
            <nav class="navbar navbar-expand-lg modern-navbar">
                <div class="container-fluid px-4">
                    <!-- Sidebar Toggle Button (visible on mobile for dashboard pages) -->
                    <button class="sidebar-toggle-btn" type="button" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                        <i class="fas fa-bars"></i>
                    </button>

                    <a class="navbar-brand" href="{{ route('home') }}">
                        <div class="brand-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <span class="brand-text">TugmaJobs</span>
                    </a>

                    <button class="navbar-toggler mobile-menu-btn" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                                    href="{{ route('home', ['force_home' => 1]) }}">
                                    <i class="fas fa-home"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jobs*') ? 'active' : '' }}"
                                    href="{{ route('jobs') }}">
                                    <i class="fas fa-search"></i> Find Jobs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('companies*') ? 'active' : '' }}"
                                    href="{{ route('companies') }}">
                                    <i class="fas fa-building"></i> Companies
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}"
                                    href="{{ route('account.dashboard') }}">
                                    <i class="fas fa-chart-line"></i> My Career
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#jobseekerHelpModal">
                                    <i class="fas fa-question-circle"></i> Help
                                </a>
                                @include('components.jobseeker-help-modal')
                            </li>
                        </ul>

                        <ul class="navbar-nav align-items-center">
                            <li class="nav-item">
                                @include('components.jobseeker-notification-dropdown')
                            </li>
                            <li class="nav-item dropdown ms-3">
                                <a class="profile-dropdown-toggle dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                    data-bs-toggle="dropdown">
                                    @if(Auth::user()->image)
                                        <img src="{{ asset('profile_img/thumb/' . Auth::user()->image) }}" alt="Profile"
                                            class="profile-avatar"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default-profile.svg') }}';">
                                    @else
                                        <img src="{{ asset('images/default-profile.svg') }}" alt="Profile"
                                            class="profile-avatar default-avatar">
                                    @endif
                                    <span class="d-none d-md-inline"
                                        style="font-weight: 500; color: #374151;">{{ Str::limit(Auth::user()->name, 12) }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('account.myProfile') }}">
                                            <i class="fas fa-user"></i> My Profile
                                        </a></li>
                                    <li><a class="dropdown-item" href="{{ route('account.myJobApplications') }}">
                                            <i class="fas fa-file-alt"></i> Applications
                                        </a></li>
                                    <li><a class="dropdown-item" href="{{ route('account.settings') }}">
                                            <i class="fas fa-cog"></i> Settings
                                        </a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        @elseif(Auth::user()->role === 'employer')
            {{-- Employer Navbar --}}
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <i class="fas fa-briefcase text-primary"></i>
                        <strong class="text-primary">TugmaJobs</strong>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                    <i class="fas fa-home"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jobs*') ? 'active' : '' }}"
                                    href="{{ route('jobs') }}">
                                    <i class="fas fa-search"></i> Find Jobs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('companies*') ? 'active' : '' }}"
                                    href="{{ route('companies') }}">
                                    <i class="fas fa-building"></i> Companies
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('employer.*') ? 'active' : '' }}"
                                    href="{{ route('employer.dashboard') }}">
                                    <i class="fas fa-briefcase"></i> Employer Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('help.employer') ? 'active' : '' }}"
                                    href="{{ route('help.employer') }}">
                                    <i class="fas fa-question-circle"></i> Help
                                </a>
                            </li>
                        </ul>

                        <ul class="navbar-nav">
                            <li class="nav-item">
                                @include('components.notification-dropdown')
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                    data-bs-toggle="dropdown">
                                    {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('employer.profile.edit') }}">
                                            <i class="fas fa-user"></i> Profile
                                        </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.settings.index') }}">
                                            <i class="fas fa-cog"></i> Settings
                                        </a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        @else
            {{-- Fallback Navbar for authenticated users with unknown role --}}
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <i class="fas fa-briefcase text-primary"></i>
                        <strong class="text-primary">TugmaJobs</strong>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                    <i class="fas fa-home"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jobs*') ? 'active' : '' }}"
                                    href="{{ route('jobs') }}">
                                    <i class="fas fa-search"></i> Find Jobs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('companies*') ? 'active' : '' }}"
                                    href="{{ route('companies') }}">
                                    <i class="fas fa-building"></i> Companies
                                </a>
                            </li>
                        </ul>

                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                    data-bs-toggle="dropdown">
                                    {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        @endif
    @else
        {{-- Guest Navbar --}}
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <i class="fas fa-briefcase text-primary"></i>
                    <strong class="text-primary">TugmaJobs</strong>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs*') ? 'active' : '' }}"
                                href="{{ route('jobs') }}">
                                <i class="fas fa-search"></i> Find Jobs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('companies*') ? 'active' : '' }}"
                                href="{{ route('companies') }}">
                                <i class="fas fa-building"></i> Companies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('help.*') ? 'active' : '' }}"
                                href="{{ route('help.index') }}">
                                <i class="fas fa-question-circle"></i> Help
                            </a>
                        </li>
                    </ul>

                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <button type="button" class="btn btn-link nav-link" data-bs-toggle="modal"
                                data-bs-target="#authModal" onclick="switchToLogin()">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#employerAuthModal" onclick="switchToEmployerLogin()">
                                <i class="fas fa-building"></i> Employer
                            </button>
                        </li>
                        <li class="nav-item ms-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authModal"
                                onclick="switchToRegister()">
                                <i class="fas fa-user-plus"></i> Get Started
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    @endauth
</div>{{-- End main-navbar-wrapper --}}