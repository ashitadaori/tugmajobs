@php
    use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Dashboard') - Employer Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Modern Design System - Bento Grid, Glass, Animations -->
    <link href="{{ asset('assets/css/modern-design-system.css') }}" rel="stylesheet">

    <!-- Professional Employer Styles -->
    <link href="{{ asset('assets/css/employer-professional.css') }}?v={{ time() }}" rel="stylesheet">

    <!-- Page Transition Prevention - Must be loaded early -->
    <link href="{{ asset('assets/css/no-blink.css') }}" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ Auth::id() }}">
    @endauth

    @stack('styles')

    <!-- No-Blink Prevention Script - Must run early -->
    <script src="{{ asset('assets/js/no-blink.js') }}"></script>
</head>

<body class="employer-wrapper">
    @include('components.maintenance-banner')

    <!-- Sidebar Overlay for Mobile -->
    <div class="ep-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    @php
        $user = auth()->user();
        $employerProfile = \App\Models\Employer::where('user_id', $user->id)->first();
        $isVerified = $user->kyc_status === 'verified';
        $canPostJobs = $user->canPostJobs();

        // Get counts for badges
        $totalJobs = \App\Models\Job::where('employer_id', auth()->id())->count();
        $pendingApplications = \App\Models\JobApplication::whereHas('job', function ($q) {
            $q->where('employer_id', auth()->id());
        })->where('status', 'pending')->count();
        $reviewCount = \App\Models\Review::where('employer_id', auth()->id())->count();
        $avgRating = \App\Models\Review::getCompanyAverageRating(auth()->id());
    @endphp

    <aside class="sidebar-modern" id="employerSidebar">
        <!-- Brand Section -->
        <div class="sidebar-brand-modern">
            <a href="{{ route('employer.dashboard') }}" class="sidebar-brand-link">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-building"></i>
                </div>
                <span class="sidebar-brand-text">TugmaJobs</span>
            </a>
        </div>

        <!-- Profile Section -->
        <div class="sidebar-profile-modern">
            <a href="{{ route('employer.profile.edit') }}" class="sidebar-profile-link">
                @if($user->profile_photo)
                    <img src="{{ asset('profile_img/' . $user->profile_photo) }}" alt="Profile"
                        class="sidebar-profile-avatar"
                        onerror="this.onerror=null; this.src='{{ asset('images/default-company-logo.svg') }}';">
                @elseif($employerProfile && $employerProfile->company_logo)
                    <img src="{{ $employerProfile->logo_url }}" alt="Company Logo" class="sidebar-profile-avatar"
                        onerror="this.onerror=null; this.src='{{ asset('images/default-company-logo.svg') }}';">
                @else
                    <img src="{{ asset('images/default-company-logo.svg') }}" alt="Company Logo"
                        class="sidebar-profile-avatar default-avatar">
                @endif
                <div class="sidebar-profile-info">
                    <h6 class="sidebar-profile-name">{{ $employerProfile->company_name ?? $user->name }}</h6>
                    <p class="sidebar-profile-role">Employer Account</p>
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
                        <a href="{{ route('employer.dashboard') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.analytics.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.analytics*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Recruitment Section -->
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Recruitment</div>
                <ul class="sidebar-nav-list">
                    @if($canPostJobs)
                        <li class="sidebar-nav-item">
                            <a href="{{ route('employer.jobs.create') }}"
                                class="sidebar-nav-link {{ request()->routeIs('employer.jobs.create') ? 'active' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Post New Job</span>
                            </a>
                        </li>
                    @endif
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.jobs.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.jobs.index') && !request()->has('status') ? 'active' : '' }}">
                            <i class="fas fa-briefcase"></i>
                            <span>All Jobs</span>
                            @if($totalJobs > 0)
                                <span class="sidebar-nav-badge">{{ $totalJobs }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.applications.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.applications*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>Applications</span>
                            @if($pendingApplications > 0)
                                <span class="sidebar-nav-badge warning">{{ $pendingApplications }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Company Section -->
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Company</div>
                <ul class="sidebar-nav-list">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.profile.edit') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.profile*') ? 'active' : '' }}">
                            <i class="fas fa-building"></i>
                            <span>Company Profile</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.reviews.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.reviews*') ? 'active' : '' }}">
                            <i class="fas fa-star"></i>
                            <span>Reviews</span>
                            @if($reviewCount > 0)
                                <span class="sidebar-nav-badge"
                                    style="background: #fbbf24; color: #1f2937;">{{ number_format($avgRating, 1) }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.documents.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.documents*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>Documents & KYC</span>
                            @if($isVerified)
                                <span class="sidebar-nav-badge success"><i class="fas fa-check"></i></span>
                            @else
                                <span class="sidebar-nav-badge danger"><i class="fas fa-exclamation"></i></span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Settings Section -->
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Settings</div>
                <ul class="sidebar-nav-list">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.settings.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.settings.index') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span>General</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.settings.notifications') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.settings.notifications') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('employer.settings.security') }}"
                            class="sidebar-nav-link {{ request()->routeIs('employer.settings.security') ? 'active' : '' }}">
                            <i class="fas fa-shield-alt"></i>
                            <span>Security</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Logout -->
        <!-- Help & Guide -->
        <div class="sidebar-footer-modern">
            <a href="#" class="sidebar-logout-btn" data-bs-toggle="modal" data-bs-target="#employerHelpModal"
                style="text-decoration: none;">
                <i class="fas fa-question-circle"></i>
                <span>Help & Guide</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-modern ep-main">
        <!-- Top Bar -->
        <header class="ep-topbar">
            <div class="ep-topbar-left">
                <button class="ep-mobile-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="ep-page-title">@yield('page_title', 'Dashboard')</h1>
            </div>
            <div class="ep-topbar-right">
                <!-- Notifications -->
                @include('components.notification-dropdown')

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="ep-btn ep-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false" style="gap: 8px;">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline">{{ $user->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">{{ $user->name }}</h6>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('employer.profile.edit') }}"><i
                                    class="bi bi-person me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('employer.settings.security') }}"><i
                                    class="bi bi-lock me-2"></i>Change Password</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i
                                        class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="ep-content">
            {{-- Toast Notifications --}}
            @include('components.toast-notifications')

            @yield('content')
        </div>
    </main>

    <!-- Help Offcanvas -->
    <!-- Help Modal -->
    @include('components.employer-help-modal')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- KYC & Verification Modals -->
    @include('components.kyc-modal')
    @include('components.verification-alert-modal')

    <!-- Toastify -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script src="{{ asset('assets/js/notifications.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('employerSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (sidebarToggle && sidebar && overlay) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });

                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('show');
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

            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Check if we should show the KYC modal
            // Disabled auto-show to prevent popup on page load
            // @if(session('show_kyc_modal'))
            //     if (typeof showKycModal === 'function') {
            //         showKycModal();
            //     }
            // @endif
    });

        // Function to show verification alert
        function showVerificationAlert(message, status) {
            const verificationModal = document.getElementById('verificationAlertModal');
            const messageElement = document.getElementById('verificationMessage');
            const actionButtonsContainer = document.getElementById('verificationActionButtons');

            if (verificationModal && messageElement && actionButtonsContainer) {
                messageElement.innerHTML = message;
                actionButtonsContainer.innerHTML = '';

                if (status === 'kyc_required') {
                    actionButtonsContainer.innerHTML = `
                    <button type="button" class="btn btn-primary" onclick="showKycModal(); bootstrap.Modal.getInstance(verificationModal).hide();">
                        Complete KYC
                    </button>
                    <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Later</button>
                `;
                } else {
                    actionButtonsContainer.innerHTML = `
                    <a href="{{ route('employer.profile.edit') }}" class="btn btn-primary">Complete Verification</a>
                    <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Later</button>
                `;
                }

                const modal = new bootstrap.Modal(verificationModal);
                modal.show();
            }
        }

        // Start inline verification
        function startInlineVerification() {
            if (typeof showKycModal === 'function') {
                showKycModal();
            }
        }
    </script>

    @stack('scripts')
</body>

</html>