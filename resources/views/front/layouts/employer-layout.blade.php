<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Employer Dashboard</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/modern-style.css') }}" rel="stylesheet">
    
    <style>
        /* Employer Layout Background Styling - Purple Theme */
        :root {
            --purple-gradient-start: #6366f1;
            --purple-gradient-end: #8b5cf6;
            --purple-dark: #4f46e5;
            --purple-light: #a78bfa;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        
        body.employer-dashboard {
            background-color: var(--gray-50);
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .employer-layout-wrapper {
            background-color: var(--gray-50);
            min-height: 100vh;
        }
        
        .main-content {
            background-color: var(--gray-50);
            flex: 1;
            padding: 0;
        }
        
        /* Update sidebar to match the purple gradient theme */
        .employer-sidebar {
            background: linear-gradient(180deg, var(--purple-gradient-start) 0%, var(--purple-gradient-end) 100%);
            color: white;
            width: 280px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(99, 102, 241, 0.15);
        }
        
        /* Adjust main content to account for fixed sidebar */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .employer-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .employer-sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="employer-dashboard">
    <div class="d-flex employer-layout-wrapper">
        <!-- Sidebar -->
        @include('front.layouts.employer-sidebar')

        <!-- Main Content -->
        <main class="main-content">
            <!-- Maintenance Banner -->
            @if(auth()->check() && auth()->user()->role === 'employer' && \App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
                <div class="alert alert-warning alert-dismissible fade show m-3 mb-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                        <div class="flex-grow-1">
                            <strong>Maintenance Mode Active</strong><br>
                            <small>{{ \App\Models\MaintenanceSetting::getMaintenanceMessage('employer') }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Content -->
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
</body>
</html>
