<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ Auth::id() }}">
        <meta name="user-role" content="{{ Auth::user()->role }}">
        @if(Auth::user()->kyc_session_id)
            <meta name="kyc-session-id" content="{{ Auth::user()->kyc_session_id }}">
        @endif
    @endauth
    <title>{{ config('app.name', 'TugmaJobs') }}</title>

    <!-- Preconnect to external resources for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>

    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style">

    <!-- Fonts - with display swap for better performance -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Critical CSS - Loaded synchronously -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/no-blink.css') }}" rel="stylesheet">

    <!-- Modern Design System - Primary Styles -->
    <link href="{{ asset('assets/css/modern-design-system.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/enhanced-notifications.css') }}" rel="stylesheet">

    <!-- Page-specific head scripts (run before other scripts) -->
    @yield('head-scripts')

    <!-- No-Blink Prevention Script - Must run early -->
    <script src="{{ asset('assets/js/no-blink.js') }}"></script>

    <!-- Deferred Scripts - Load after DOM -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="{{ asset('assets/js/ui-enhancements.js') }}" defer></script>
    <script src="{{ asset('assets/js/csrf-token-handler.js') }}" defer></script>
    <script src="{{ asset('assets/js/notifications.js') }}" defer></script>

    @stack('styles')

    <!-- Critical Sidebar Styles - Must load before body -->
    <!-- Custom Jobseeker Styles -->

</head>

<body
    class="modern-jobseeker-body @if(request()->routeIs('home') || request()->has('force_home')) homepage @endif @if(request()->routeIs('jobs') || request()->routeIs('jobs.*')) jobs-page @endif @if(request()->routeIs('companies') || request()->routeIs('companies.*')) companies-page @endif @if(request()->has('force_home')) force-home @endif">

    <!-- Page Loading Animation - Disabled to prevent white/black screen on reload -->
    <!-- <div class="page-loader" id="pageLoader">
        <div class="loader-content">
            <div class="neon-loader">
                <div class="circle-middle">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="worm"></div>
            </div>
            <div class="loader-text">
                <span class="loader-title">Loading</span>
                <span class="loader-dots">
                    <span>.</span><span>.</span><span>.</span>
                </span>
            </div>
        </div>
    </div> -->

    @include('components.maintenance-banner')

    @auth
        {{-- KYC banner removed as requested --}}
        {{-- @unless(request()->routeIs('home') || request()->has('force_home') || request()->routeIs('jobs') ||
        request()->routeIs('jobs.*') || request()->routeIs('companies') || request()->routeIs('companies.*'))
        <x-kyc-reminder-banner />
        @endunless --}}
    @endauth
    <!-- Modern Jobseeker Design -->
    <div class="modern-jobseeker-layout">
        <!-- Use Main Navbar Component -->
        @include('components.main-navbar')

        <!-- Sidebar Overlay for Mobile -->
        <div class="js-sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Jobseeker Sidebar -->
        @php
            $user = Auth::user();
            $savedCount = $user ? $user->bookmarkedJobs()->count() : 0;
            $applicationsCount = $user ? \App\Models\JobApplication::where('user_id', $user->id)->count() : 0;
        @endphp

        <aside class="sidebar-modern" id="jobseekerSidebar">
            <!-- Brand Section -->
            <div class="sidebar-brand-modern">
                <a href="{{ route('home') }}" class="sidebar-brand-link">
                    <div class="sidebar-brand-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <span class="sidebar-brand-text">TugmaJobs</span>
                </a>
            </div>

            <!-- Profile Section -->
            <div class="sidebar-profile-modern">
                <a href="{{ route('account.myProfile') }}" class="sidebar-profile-link">
                    @if($user && $user->image)
                        <img src="{{ asset('profile_img/thumb/' . $user->image) }}" alt="Profile"
                            class="sidebar-profile-avatar"
                            onerror="this.onerror=null; this.src='{{ asset('images/default-profile.svg') }}';">
                    @else
                        <img src="{{ asset('images/default-profile.svg') }}" alt="Profile"
                            class="sidebar-profile-avatar default-avatar">
                    @endif
                    <div class="sidebar-profile-info">
                        <h6 class="sidebar-profile-name">{{ $user ? $user->name : 'Guest' }}</h6>
                        <p class="sidebar-profile-role">Job Seeker</p>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav-modern">
                <!-- Overview Section -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Overview</div>
                    <ul class="sidebar-nav-list">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.dashboard') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.analytics') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.analytics') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i>
                                <span>Analytics</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Job Search Section -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Job Search</div>
                    <ul class="sidebar-nav-list">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('jobs') }}"
                                class="sidebar-nav-link {{ request()->routeIs('jobs') || request()->routeIs('jobs.*') ? 'active' : '' }}">
                                <i class="fas fa-search"></i>
                                <span>Find Jobs</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.myJobApplications') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.myJobApplications') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i>
                                <span>Applications</span>
                                @if($applicationsCount > 0)
                                    <span class="sidebar-nav-badge">{{ $applicationsCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.bookmarked-jobs.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.bookmarked-jobs.*') ? 'active' : '' }}">
                                <i class="fas fa-bookmark"></i>
                                <span>Bookmarked Jobs</span>
                                @if($savedCount > 0)
                                    <span class="sidebar-nav-badge">{{ $savedCount }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Profile Section -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Profile</div>
                    <ul class="sidebar-nav-list">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.myProfile') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.myProfile') ? 'active' : '' }}">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.resume-builder.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.resume-builder.*') ? 'active' : '' }}">
                                <i class="fas fa-file-pdf"></i>
                                <span>Resume Builder</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Settings Section -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Settings</div>
                    <ul class="sidebar-nav-list">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.settings') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.settings') ? 'active' : '' }}">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('account.changePassword') }}"
                                class="sidebar-nav-link {{ request()->routeIs('account.changePassword') ? 'active' : '' }}">
                                <i class="fas fa-key"></i>
                                <span>Password</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Logout Footer -->
            <div class="sidebar-footer-modern">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-modern">
            <div class="main-content-modern">
                @yield('jobseeker-content')
            </div>
        </main>

        <!-- Mobile Sidebar Overlay -->
        <div class="mobile-sidebar-overlay" onclick="closeSidebar()"></div>
    </div>

    <script>
        // Mobile sidebar functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('jobseekerSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('jobseekerSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }

        // Close sidebar when clicking on navigation links on mobile
        document.addEventListener('DOMContentLoaded', function () {
            // Clean up old sidebar collapsed state
            localStorage.removeItem('sidebarCollapsed');
            document.body.classList.remove('sidebar-collapsed');

            // Mobile sidebar toggle
            const sidebarToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.getElementById('jobseekerSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                    if (overlay) overlay.classList.toggle('show');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    if (sidebar) sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }

            // Close sidebar on window resize
            window.addEventListener('resize', function () {
                if (window.innerWidth > 992) {
                    if (sidebar) sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                }
            });

            // Fix: Prevent scrollbar when navbar dropdown opens
            const navbarDropdowns = document.querySelectorAll('.navbar .dropdown');
            navbarDropdowns.forEach(function (dropdown) {
                dropdown.addEventListener('show.bs.dropdown', function () {
                    // Store current scroll position and prevent scroll jump
                    document.body.style.paddingRight = '0px';
                    document.documentElement.style.overflow = 'hidden';
                    document.documentElement.style.overflowY = 'scroll';
                });

                dropdown.addEventListener('hide.bs.dropdown', function () {
                    // Restore normal overflow
                    document.body.style.paddingRight = '';
                    document.documentElement.style.overflow = '';
                    document.documentElement.style.overflowY = '';
                });
            });

            // Force hide KYC banner on homepage, jobs, and companies pages
            if (window.location.pathname === '/' ||
                window.location.search.includes('force_home=1') ||
                window.location.pathname === '/jobs' ||
                window.location.pathname.startsWith('/jobs/') ||
                window.location.pathname === '/companies' ||
                window.location.pathname.startsWith('/companies/')) {
                const kycBanners = document.querySelectorAll('.kyc-reminder-banner');
                kycBanners.forEach(function (banner) {
                    if (banner) {
                        banner.style.display = 'none !important';
                        banner.remove();
                    }
                });
                document.body.classList.add('homepage');
                document.body.classList.remove('has-kyc-banner');
            }

            const navLinks = document.querySelectorAll('.js-sidebar .js-nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 992) {
                        closeSidebar();
                    }
                });
            });

            // ===== Page Loading Animation =====
            initPageLoader();
        });

        // Page Loader Controller
        function initPageLoader() {
            const pageLoader = document.getElementById('pageLoader');
            if (!pageLoader) return;

            // Hide loader when page is fully loaded
            window.addEventListener('load', function () {
                hidePageLoader();
            });

            // Also hide on DOMContentLoaded as backup
            hidePageLoader();

            // Get all navigation links that should trigger the loader
            const navigationLinks = document.querySelectorAll(`
                .js-nav-link,
                .js-brand-link,
                .js-profile-link,
                .navbar .nav-link:not(.dropdown-toggle),
                .quick-action-card,
                .job-card a,
                a.btn-modern,
                .pagination a,
                .breadcrumb a
            `);

            navigationLinks.forEach(link => {
                // Skip links that are just hash anchors or javascript void
                if (!link.href ||
                    link.href.startsWith('javascript:') ||
                    link.href === '#' ||
                    link.href.endsWith('#') ||
                    link.target === '_blank' ||
                    link.classList.contains('no-loader') ||
                    link.classList.contains('dropdown-toggle') ||
                    link.getAttribute('data-bs-toggle')) {
                    return;
                }

                link.addEventListener('click', function (e) {
                    // Don't show loader for same-page navigation
                    const currentUrl = window.location.href.split('#')[0];
                    const targetUrl = this.href.split('#')[0];

                    if (currentUrl === targetUrl) {
                        return;
                    }

                    // Don't show loader if it's a form submit button or has data attributes for modals
                    if (this.type === 'submit' || this.getAttribute('data-bs-toggle')) {
                        return;
                    }

                    // Show the page loader
                    showPageLoader();

                    // Add loading state to the clicked link
                    this.classList.add('loading');
                });
            });

            // Handle form submissions
            const forms = document.querySelectorAll('form:not(.no-loader-form)');
            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    // Don't show loader for AJAX forms
                    if (this.classList.contains('ajax-form') ||
                        this.hasAttribute('data-ajax') ||
                        this.id === 'changePasswordForm' ||
                        this.id === 'logoutForm') {
                        return;
                    }

                    // Show loader for regular form submissions
                    showPageLoader();
                });
            });

            // Handle browser back/forward navigation
            window.addEventListener('pageshow', function (event) {
                // Hide loader when navigating back/forward
                if (event.persisted) {
                    hidePageLoader();
                }
            });

            // Handle beforeunload to show loader when leaving page
            window.addEventListener('beforeunload', function () {
                showPageLoader();
            });
        }

        function showPageLoader() {
            const pageLoader = document.getElementById('pageLoader');
            if (pageLoader) {
                pageLoader.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function hidePageLoader() {
            const pageLoader = document.getElementById('pageLoader');
            if (pageLoader) {
                pageLoader.classList.remove('active');
                document.body.style.overflow = '';

                // Remove loading state from all links
                document.querySelectorAll('.loading').forEach(el => {
                    el.classList.remove('loading');
                });
            }
        }
    </script>

    <!-- Toast Notifications -->
    @include('components.toast-notifications')

    <!-- KYC Completion Handler -->
    @auth
        @if(Auth::user()->kyc_status === 'in_progress' || request()->routeIs('kyc.*'))
            <script src="{{ asset('assets/js/kyc-completion-handler.js') }}"></script>
            <script src="{{ asset('assets/js/kyc-cross-device-handler.js') }}"></script>
        @endif
    @endauth

    <!-- Modal Fix: Move modals to body to prevent z-index/overflow issues -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Move all modals to body element to prevent stacking context issues
            const modals = document.querySelectorAll('.modal');
            modals.forEach(function (modal) {
                // Only move if not already direct child of body
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
            });
        });
    </script>

    @stack('scripts')

</body>

</html>