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
    <link href="{{ asset('assets/css/admin-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/admin-theme.css') }}" rel="stylesheet">

    <style>
        :root {
            /* Custom Admin Variables - Light Mode */
            --admin-sidebar-bg: #ffffff;
            --admin-sidebar-text: #333;
            --admin-card-bg: #ffffff;
            --admin-border-color: #dee2e6;
            --admin-body-bg: #f8f9fa;
            --admin-text-main: #212529;
            --admin-heading-color: #343a40;
        }

        [data-theme="dark"] {
            /* Custom Admin Variables - Dark Mode */
            --admin-sidebar-bg: #1f2937;
            --admin-sidebar-text: #e5e7eb;
            --admin-card-bg: #1f2937;
            --admin-border-color: #374151;
            --admin-body-bg: #111827;
            --admin-text-main: #f3f4f6;
            --admin-heading-color: #f9fafb;
            --bs-body-bg: #111827;
            --bs-body-color: #f3f4f6;
        }

        body {
            background-color: var(--admin-body-bg);
            color: var(--admin-text-main);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .stats-card,
        .card {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border-color);
            color: var(--admin-text-main);
        }



        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: var(--admin-heading-color);
        }

        /* Dark Mode Toggle Button */
        .theme-toggle-btn {
            background: none;
            border: 1px solid var(--admin-border-color);
            color: var(--admin-text-main);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .theme-toggle-btn:hover {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }
    </style>
    @stack('styles')
    @yield('styles')

    <!-- No-Blink Prevention Script - Must run early -->
    <script src="{{ asset('assets/js/no-blink.js') }}"></script>
    <script>
        // Apply theme immediately to prevent flicker
        (function () {
            const savedTheme = localStorage.getItem('admin_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>

<body>
    <div class="admin-main-wrapper">
        <!-- Sidebar -->
        @include('admin.sidebar')

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content Wrapper -->
        <div class="admin-content-wrapper">
            <!-- Top Bar -->
            <header class="admin-top-bar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="sidebar-toggle me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <!-- Global Search could go here if left aligned, but currently right aligned in design -->
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button class="theme-toggle-btn" id="themeToggle" title="Toggle Dark Mode">
                        <i class="fas fa-moon"></i>
                    </button>

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
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
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
            </header>

            <!-- Main Content -->
            <main class="admin-content-area">
                @yield('content')
            </main>
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
        // Theme Toggle Logic
        document.addEventListener('DOMContentLoaded', function () {
            // Dark Mode Toggle
            const toggleBtn = document.getElementById('themeToggle');
            const icon = toggleBtn.querySelector('i');

            // Sidebar Toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            // Function to update icon
            function updateIcon(theme) {
                if (theme === 'dark') {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }

            // check current theme and update icon
            const currentTheme = document.documentElement.getAttribute('data-theme');
            updateIcon(currentTheme);

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('admin_theme', newTheme);
                    updateIcon(newTheme);
                });
            }

            // Sidebar Event Listeners
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                    if (overlay) overlay.classList.toggle('show');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
        });

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