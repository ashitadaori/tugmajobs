<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Jobseeker Dashboard</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/modern-style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/enhanced-notifications.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ Auth::id() }}">
    @endauth
    
    <style>
        /* Jobseeker Layout Background Styling - Purple Theme */
        :root {
            --purple-gradient-start: #4c1d95;
            --purple-gradient-middle: #7c3aed;
            --purple-gradient-end: #a855f7;
            --purple-dark: #3730a3;
            --purple-light: #c4b5fd;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        
        body.jobseeker-dashboard {
            background-color: var(--gray-50);
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .jobseeker-layout-wrapper {
            background-color: var(--gray-50);
            min-height: 100vh;
        }
        
        .main-content {
            background-color: var(--gray-50);
            flex: 1;
            padding: 2rem;
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
        }
        
        /* Content styling for better readability */
        .main-content .container-fluid {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin: 0;
            color: #1e293b;
        }
        
        /* Ensure all text is readable */
        .main-content * {
            color: inherit;
        }
        
        .main-content h1, 
        .main-content h2, 
        .main-content h3, 
        .main-content h4, 
        .main-content h5, 
        .main-content h6 {
            color: #1e293b;
            font-weight: 600;
        }
        
        .main-content .text-muted {
            color: #64748b !important;
        }
        
        .main-content a {
            color: #6366f1;
        }
        
        .main-content .btn {
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .main-content .container-fluid {
                padding: 1rem;
                border-radius: 8px;
            }
        }
        
        /* Top Bar Styling */
        .top-bar-jobseeker {
            background: white;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .top-bar-jobseeker h5 {
            color: #1e293b;
            font-weight: 600;
            margin: 0;
        }
        
        /* Notification dropdown styling for jobseeker */
        .notification-dropdown {
            position: relative;
        }
        
        .notification-bell {
            background: transparent;
            border: none;
            color: #64748b;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .notification-bell:hover {
            background: #f1f5f9;
            color: #6366f1;
        }
        
        .notification-badge {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.125rem 0.375rem;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
    </style>
    
    @stack('styles')
</head>
<body class="jobseeker-dashboard">
    @include('components.maintenance-banner')
    
    <div class="d-flex jobseeker-layout-wrapper">
        <!-- Sidebar -->
        @include('front.layouts.jobseeker-sidebar')

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar with Notifications -->
            <div class="top-bar-jobseeker mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">@yield('page_title', 'Dashboard')</h5>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Notifications -->
                        @include('components.notification-dropdown')
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/notifications.js') }}"></script>
    @stack('scripts')
</body>
</html>