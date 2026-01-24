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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/workscout-framework.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/modern-style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/improved-readability.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/enhanced-notifications.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/homepage-modern.css') }}" rel="stylesheet">

    <!-- Slick Carousel for dynamic content -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

    <!-- Mapbox GL JS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />

    @stack('styles')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/ui-enhancements.js') }}"></script>
    <script src="{{ asset('assets/js/csrf-token-handler.js') }}"></script>
    <script src="{{ asset('assets/js/notifications.js') }}"></script>

    <!-- Slick Carousel for dynamic content -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="{{ asset('assets/js/dynamic-interactions.js') }}"></script>

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #0891b2;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --bg-dark: #1f2937;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #374151;
            /* Clear readable dark gray */
            font-size: 16px;
            /* Standard readable size */
            line-height: 1.6;
            /* Good line height for readability */
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-sm);
            padding: 1.25rem 0;
            /* Increased padding */
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        /* Dynamic navbar positioning when KYC banner is present */
        body.has-kyc-banner .navbar {
            top: 0;
        }

        body.has-kyc-banner .main-content {
            margin-top: 5rem;
        }

        /* Hide KYC banner on force_home pages, homepage, jobs, and companies pages */
        body.force-home .kyc-reminder-banner,
        body.homepage .kyc-reminder-banner,
        body.jobs-page .kyc-reminder-banner,
        body.companies-page .kyc-reminder-banner {
            display: none !important;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.75rem;
            /* Larger brand text */
            color: var(--primary-color) !important;
        }

        .nav-link {
            font-weight: 500;
            font-size: 1.125rem;
            /* Larger nav links */
            color: var(--text-dark) !important;
            padding: 0.75rem 1.25rem;
            /* Increased padding */
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

        /* Fix for homepage with force_home parameter */
        body.homepage .main-content,
        body.force-home .main-content {
            margin-top: 0;
            padding: 0;
        }

        /* Mobile navbar and content fixes */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 0;
            }

            /* Ensure hero section clears navbar on mobile */
            body.homepage .hero-section,
            body.force-home .hero-section {
                padding-top: 90px !important;
            }

            .nav-link {
                font-size: 1rem;
                padding: 0.625rem 1rem;
            }

            .navbar-brand {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            body.homepage .hero-section,
            body.force-home .hero-section {
                padding-top: 80px !important;
            }
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
            padding: 0.875rem 1.75rem;
            /* Larger padding for better touch targets */
            font-weight: 500;
            font-size: 1.125rem;
            /* Larger button text */
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            min-height: 48px;
            /* Minimum touch target size */
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn {
            font-size: 1.125rem;
            /* Larger button text for all buttons */
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            min-height: 44px;
            /* Minimum touch target size */
        }

        .btn-sm {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            min-height: 36px;
        }

        .btn-lg {
            font-size: 1.25rem;
            padding: 1rem 2rem;
            min-height: 52px;
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
            padding: 0.75rem;
            min-width: 220px;
        }

        .dropdown-item {
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            font-size: 1.125rem;
            /* Larger dropdown text */
            color: var(--text-dark);
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            line-height: 1.5;
        }

        .dropdown-item:hover {
            background-color: var(--bg-light);
            color: var(--primary-color);
        }

        .dropdown-header {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            padding: 0.75rem 1.25rem 0.5rem;
        }

        .dropdown-divider {
            margin: 0.75rem 0;
        }

        .alert {
            border: none;
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            /* Larger padding for better readability */
            margin-bottom: 1.5rem;
            font-size: 1.125rem;
            /* Larger alert text */
            font-weight: 500;
            line-height: 1.5;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #047857;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #d97706;
            border-left: 4px solid #f59e0b;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1d4ed8;
            border-left: 4px solid #3b82f6;
        }

        .badge {
            padding: 0.5rem 0.875rem;
            /* Larger badge padding */
            font-weight: 500;
            font-size: 0.875rem;
            /* Larger badge text */
            border-radius: var(--radius-sm);
            line-height: 1;
        }

        /* Enhanced form controls */
        .form-control {
            font-size: 1.125rem;
            /* Larger form text */
            padding: 0.875rem 1rem;
            /* Larger form padding */
            border: 2px solid #e5e7eb;
            border-radius: var(--radius);
            min-height: 48px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-label {
            font-size: 1.125rem;
            /* Larger label text */
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        /* Enhanced table styles */
        .table {
            font-size: 1.125rem;
            /* Larger table text */
        }

        .table th {
            font-weight: 600;
            color: var(--text-dark);
            padding: 1rem 1.25rem;
            /* Larger table padding */
        }

        .table td {
            padding: 1rem 1.25rem;
            /* Larger table padding */
            color: var(--text-dark);
        }

        /* Enhanced breadcrumb */
        .breadcrumb-item {
            font-size: 1.125rem;
            /* Larger breadcrumb text */
        }

        /* Enhanced pagination */
        .page-link {
            font-size: 1.125rem;
            /* Larger pagination text */
            padding: 0.75rem 1rem;
            /* Larger pagination padding */
            min-height: 44px;
        }

        /* Responsive Navbar */
        @media (max-width: 992px) {
            .navbar-collapse {
                background: #ffffff;
                padding: 1rem;
                border-radius: var(--radius);
                box-shadow: var(--shadow-lg);
            }
        }

        /* Featured Jobs Carousel */
        .featured-jobs-carousel .job-card {
            margin: 15px;
        }

        /* GLOBAL FIX: Completely disable ALL Bootstrap tooltips */
        .tooltip,
        .bs-tooltip-top,
        .bs-tooltip-bottom,
        .bs-tooltip-start,
        .bs-tooltip-end,
        .bs-tooltip-auto,
        [role="tooltip"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Remove white boxes on hover */
        * {
            -webkit-tap-highlight-color: transparent !important;
        }

        /* Disable all title attribute tooltips */
        [title]:hover::after,
        [title]:hover::before,
        [data-bs-toggle="tooltip"]::after,
        [data-bs-toggle="tooltip"]::before {
            content: none !important;
            display: none !important;
        }

        /* ===== Global Scrollbar Styling - Light Theme ===== */
        /* Prevent dark scrollbar appearance */
        html,
        body {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            width: 8px;
            height: 8px;
            background: #f1f5f9;
        }

        html::-webkit-scrollbar-track,
        body::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        html::-webkit-scrollbar-thumb,
        body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        html::-webkit-scrollbar-thumb:hover,
        body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* All dropdowns and popups - light scrollbar */
        .dropdown-menu,
        .dropdown-menu *,
        [class*="dropdown"],
        [class*="dropdown"] * {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .dropdown-menu::-webkit-scrollbar,
        .dropdown-menu *::-webkit-scrollbar,
        [class*="dropdown"]::-webkit-scrollbar,
        [class*="dropdown"] *::-webkit-scrollbar {
            width: 6px;
            background: transparent;
        }

        .dropdown-menu::-webkit-scrollbar-track,
        .dropdown-menu *::-webkit-scrollbar-track,
        [class*="dropdown"]::-webkit-scrollbar-track,
        [class*="dropdown"] *::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 3px;
        }

        .dropdown-menu::-webkit-scrollbar-thumb,
        .dropdown-menu *::-webkit-scrollbar-thumb,
        [class*="dropdown"]::-webkit-scrollbar-thumb,
        [class*="dropdown"] *::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dropdown-menu::-webkit-scrollbar-thumb:hover,
        .dropdown-menu *::-webkit-scrollbar-thumb:hover,
        [class*="dropdown"]::-webkit-scrollbar-thumb:hover,
        [class*="dropdown"] *::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <script>
        // NUCLEAR OPTION: Disable Bootstrap tooltips completely
        document.addEventListener('DOMContentLoaded', function () {
            // Prevent Bootstrap from initializing tooltips
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const originalTooltip = bootstrap.Tooltip;
                bootstrap.Tooltip = function () {
                    return { dispose: function () { }, hide: function () { }, show: function () { } };
                };
                bootstrap.Tooltip.getInstance = function () { return null; };
            }

            // Remove all existing tooltips
            setInterval(function () {
                document.querySelectorAll('.tooltip, [role="tooltip"]').forEach(el => el.remove());
            }, 100);
        });
    </script>
</head>

<body
    class="@if(request()->routeIs('home') || request()->has('force_home')) homepage @endif @if(request()->routeIs('jobs') || request()->routeIs('jobs.*')) jobs-page @endif @if(request()->routeIs('companies') || request()->routeIs('companies.*')) companies-page @endif @if(request()->has('force_home')) force-home @endif">
    @auth
        {{-- KYC banner removed as requested --}}
        {{-- @unless(request()->routeIs('home') || request()->has('force_home') || request()->routeIs('jobs') ||
        request()->routeIs('jobs.*') || request()->routeIs('companies') || request()->routeIs('companies.*'))
        <x-kyc-reminder-banner />
        @endunless --}}
    @endauth

    {{-- Use Main Navbar Component --}}
    @include('components.main-navbar')

    <div class="main-content">
        @if(request()->routeIs('home') || request()->has('force_home') || request()->routeIs('companies') || request()->routeIs('companies.*'))
            <!-- Homepage and Companies pages get fullscreen treatment -->
            @yield('content')
        @else
            <!-- Other pages get normal container -->
            <div class="container">
                @yield('content')
            </div>
        @endif
    </div>

    <!-- Mapbox GL JS -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>

    <!-- KYC Completion Handler -->
    @auth
        @if(Auth::user()->kyc_status === 'in_progress' || request()->routeIs('kyc.*'))
            <script src="{{ asset('assets/js/kyc-completion-handler.js') }}"></script>
            <script src="{{ asset('assets/js/kyc-cross-device-handler.js') }}"></script>
        @endif
    @endauth

    @stack('scripts')

    <!-- KYC Status Refresher Script -->
    <script src="{{ asset('assets/js/kyc-status-refresher.js') }}"></script>

    <!-- Homepage KYC Banner Remover Script -->
    <script src="{{ asset('assets/js/homepage-kyc-banner-remover.js') }}"></script>

    <!-- Auth Modals -->
    @guest
        @include('components.auth-modal')
        @include('components.employer-auth-modal')
    @endguest

    <!-- Modern Navbar Styles -->
    <style>
        /* Modern Navbar Design */
        .modern-navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .modern-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #7c3aed;
            font-weight: 700;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .modern-brand:hover {
            color: #5b21b6;
            text-decoration: none;
        }

        .brand-icon {
            font-size: 1.75rem;
            color: #7c3aed;
        }

        .brand-text {
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .modern-nav {
            gap: 0.5rem;
        }

        .modern-nav-link {
            color: #374151;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
        }

        .modern-nav-link:hover {
            color: #7c3aed;
            background-color: #f3f4f6;
            text-decoration: none;
        }

        .modern-nav-link.active {
            color: #7c3aed;
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .modern-nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -0.75rem;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: #7c3aed;
            border-radius: 50%;
        }

        .modern-user-nav {
            gap: 1rem;
            margin-left: 2rem;
            padding-left: 2rem;
            border-left: 1px solid #e5e7eb;
        }

        .modern-notification-link {
            color: #6b7280;
            font-size: 1.1rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .modern-notification-link:hover {
            color: #7c3aed;
            background-color: #f3f4f6;
            text-decoration: none;
        }

        .modern-user-link {
            color: #374151;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            gap: 0.5rem;
        }

        .modern-user-link:hover {
            color: #7c3aed;
            background-color: #f3f4f6;
            text-decoration: none;
        }

        .user-name {
            font-weight: 500;
        }

        .modern-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .modern-toggler:focus {
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .modern-toggler .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2855, 65, 81, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Dropdown styling */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem;
            margin-top: 0.5rem;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .dropdown-item:hover {
            background-color: #f3f4f6;
            color: #7c3aed;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .modern-user-nav {
                margin-left: 0;
                padding-left: 0;
                border-left: none;
                border-top: 1px solid #e5e7eb;
                padding-top: 1rem;
                margin-top: 1rem;
            }
        }

        @media (max-width: 576px) {
            .modern-brand {
                font-size: 1.25rem;
            }

            .brand-icon {
                font-size: 1.5rem;
            }
        }
    </style>

    <!-- Dynamic navbar positioning script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isHomePage = window.location.pathname === '/' || window.location.search.includes('force_home=1');
            const isTargetPage = isHomePage ||
                window.location.pathname === '/jobs' ||
                window.location.pathname.startsWith('/jobs/') ||
                window.location.pathname === '/companies' ||
                window.location.pathname.startsWith('/companies/');

            // Force hide KYC banner on homepage, jobs, and companies pages
            if (isTargetPage) {
                const kycBanners = document.querySelectorAll('.kyc-reminder-banner');
                kycBanners.forEach(function (banner) {
                    if (banner) {
                        banner.style.display = 'none !important';
                        banner.remove(); // Completely remove it from DOM
                    }
                });

                // Only add homepage class if it is actually the homepage
                // This prevents margin-top: 0 from being applied to companies/jobs pages
                if (isHomePage) {
                    document.body.classList.add('homepage');
                }
                document.body.classList.remove('has-kyc-banner');
            }


            // Handle KYC banner visibility and navbar positioning
            const kycBanner = document.querySelector('.kyc-reminder-banner');
            const navbar = document.querySelector('.navbar');
            const body = document.body;

            function adjustNavbarPosition() {
                if (kycBanner && !kycBanner.style.display.includes('none') && !body.classList.contains('homepage')) {
                    body.classList.add('has-kyc-banner');
                    navbar.style.top = kycBanner.offsetHeight + 'px';
                } else {
                    body.classList.remove('has-kyc-banner');
                    navbar.style.top = '0px';
                }
            }

            // Initial adjustment
            adjustNavbarPosition();

            // Watch for banner dismissal
            if (kycBanner && !body.classList.contains('homepage')) {
                const observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            adjustNavbarPosition();
                        }
                    });
                });

                observer.observe(kycBanner, {
                    attributes: true,
                    attributeFilter: ['style']
                });
            }
        });
    </script>

    <!-- Toast Notification Container -->
    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Toast Notification System -->
    <script>
        function showToast(message, type = 'info', duration = 4000) {
            const container = document.getElementById('toastContainer');
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
            background: ${bgColors[type] || bgColors.info};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 500px;
            animation: slideIn 0.3s ease-out;
            font-size: 15px;
            font-weight: 500;
        `;

            toast.innerHTML = `
            <span style="font-size: 20px; font-weight: bold;">${icons[type] || icons.info}</span>
            <span>${message}</span>
        `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // Add animation styles
        if (!document.getElementById('toastStyles')) {
            const style = document.createElement('style');
            style.id = 'toastStyles';
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

        // Check for session messages and show as toast
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif

        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif

        @if(session('warning'))
            showToast('{{ session('warning') }}', 'warning');
        @endif

        @if(session('info'))
            showToast('{{ session('info') }}', 'success');
        @endif
    </script>
</body>

</html>