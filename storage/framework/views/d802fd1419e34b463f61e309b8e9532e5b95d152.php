<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php if(auth()->guard()->check()): ?>
    <meta name="user-id" content="<?php echo e(Auth::id()); ?>">
    <meta name="user-role" content="<?php echo e(Auth::user()->role); ?>">
    <?php if(Auth::user()->kyc_session_id): ?>
    <meta name="kyc-session-id" content="<?php echo e(Auth::user()->kyc_session_id); ?>">
    <?php endif; ?>
    <?php endif; ?>
    <title><?php echo e(config('app.name', 'TugmaJobs')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/workscout-framework.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/modern-style.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/improved-readability.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/enhanced-notifications.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/jobseeker-professional.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/jobseeker-zoom-fix.css')); ?>" rel="stylesheet">

    <!-- Page-specific head scripts (run before other scripts) -->
    <?php echo $__env->yieldContent('head-scripts'); ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('assets/js/ui-enhancements.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/csrf-token-handler.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/notifications.js')); ?>"></script>

    <!-- No-Blink Prevention -->
    <link href="<?php echo e(asset('assets/css/no-blink.css')); ?>" rel="stylesheet">
    <script src="<?php echo e(asset('assets/js/no-blink.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('styles'); ?>

    <!-- Critical Sidebar Styles - Must load before body -->
    <style>
    /* ===== Jobseeker Professional Layout (Matching Employer Style) ===== */
    :root {
        /* Primary Colors - Professional Blue-Indigo (Matching Employer) */
        --js-primary: #4f46e5;
        --js-primary-light: #6366f1;
        --js-primary-dark: #4338ca;
        --js-primary-50: #eef2ff;
        --js-primary-100: #e0e7ff;

        /* Neutral Colors */
        --js-gray-50: #f9fafb;
        --js-gray-100: #f3f4f6;
        --js-gray-200: #e5e7eb;
        --js-gray-300: #d1d5db;
        --js-gray-400: #9ca3af;
        --js-gray-500: #6b7280;
        --js-gray-600: #4b5563;
        --js-gray-700: #374151;
        --js-gray-800: #1f2937;
        --js-gray-900: #111827;

        /* Status Colors */
        --js-success: #059669;
        --js-warning: #d97706;
        --js-danger: #dc2626;

        /* Layout */
        --js-sidebar-width: 270px;
        --js-topbar-height: 60px;

        /* Typography */
        --js-font-size-xs: 0.75rem;
        --js-font-size-sm: 0.875rem;
        --js-font-size-base: 1rem;
        --js-font-size-lg: 1.125rem;

        /* Spacing */
        --js-space-1: 0.25rem;
        --js-space-2: 0.5rem;
        --js-space-3: 0.75rem;
        --js-space-4: 1rem;
        --js-space-5: 1.25rem;
        --js-space-6: 1.5rem;

        /* Border Radius */
        --js-radius-sm: 0.375rem;
        --js-radius-md: 0.5rem;
        --js-radius-lg: 0.75rem;
        --js-radius-full: 9999px;

        /* Transitions */
        --js-transition-fast: 150ms ease;
        --js-transition-base: 200ms ease;
        --js-transition-slow: 300ms ease;
    }

    /* Modern Jobseeker Layout */
    .modern-jobseeker-layout {
        min-height: 100vh;
        background-color: var(--js-gray-50);
        overflow-x: hidden;
    }

    html, body.modern-jobseeker-body {
        overflow-x: hidden;
        margin: 0;
        padding: 0;
    }

    /* Navbar - Fixed at top */
    .modern-jobseeker-layout .navbar {
        background: white !important;
        border-bottom: 1px solid var(--js-gray-200) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        z-index: 1001 !important;
        height: var(--js-topbar-height);
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
    }

    /* ===== Professional Sidebar - Light Theme with Border ===== */
    .js-sidebar {
        position: fixed !important;
        top: var(--js-topbar-height);
        left: 0;
        width: var(--js-sidebar-width);
        height: calc(100vh - var(--js-topbar-height));
        /* Clean light background */
        background: #ffffff;
        /* Blue border on the right */
        border-right: 1px solid #e5e7eb;
        z-index: 1000;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.04);
    }

    /* Sidebar Overlay for Mobile */
    .js-sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all var(--js-transition-slow);
    }

    .js-sidebar-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    /* Hide scrollbar but keep scroll functionality */
    .js-sidebar,
    .js-sidebar-nav {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }

    .js-sidebar::-webkit-scrollbar,
    .js-sidebar-nav::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
        width: 0 !important;
        height: 0 !important;
    }

    .js-sidebar *,
    .js-sidebar-nav * {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .js-sidebar *::-webkit-scrollbar,
    .js-sidebar-nav *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* Brand Section - Light Theme */
    .js-sidebar-brand {
        padding: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .js-brand-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        transition: all var(--js-transition-base);
    }

    .js-brand-link:hover {
        opacity: 0.9;
    }

    .js-brand-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--js-primary) 0%, var(--js-primary-light) 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    .js-brand-text {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--js-gray-800);
        letter-spacing: -0.025em;
    }

    /* Profile Section - Light Theme Design */
    .js-sidebar-profile {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        background: transparent;
    }

    .js-profile-link {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        text-decoration: none;
        transition: all var(--js-transition-base);
        padding: 0.625rem;
        border-radius: 12px;
    }

    .js-profile-link:hover {
        background: #f1f5f9;
    }

    .js-profile-avatar {
        width: 42px;
        height: 42px;
        border-radius: var(--js-radius-full);
        object-fit: cover;
        border: 2px solid #e5e7eb;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .js-profile-avatar-placeholder {
        width: 42px;
        height: 42px;
        border-radius: var(--js-radius-full);
        background: linear-gradient(135deg, var(--js-primary) 0%, var(--js-primary-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
    }

    .js-profile-info {
        flex: 1;
        min-width: 0;
    }

    .js-profile-name {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--js-gray-800);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    .js-profile-role {
        margin: 0.125rem 0 0 0;
        font-size: 0.75rem;
        color: var(--js-gray-500);
        font-weight: 500;
    }

    /* Sidebar Navigation - Light Theme with Colored Active State */
    .js-sidebar-nav {
        flex: 1;
        padding: 1rem 0;
        overflow-y: auto;
    }

    .js-nav-section {
        margin-bottom: 1.75rem;
    }

    .js-nav-section-title {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--js-gray-400);
        padding: 0 1.25rem;
        margin-bottom: 0.625rem;
    }

    .js-nav-list {
        list-style: none !important;
        margin: 0;
        padding: 0;
    }

    .js-nav-item {
        margin: 0.25rem 0;
        list-style: none !important;
    }

    .js-nav-link {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.75rem 1rem;
        margin: 0 0.75rem;
        color: var(--js-gray-600) !important;
        text-decoration: none !important;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all var(--js-transition-base);
        border-radius: 10px;
        position: relative;
        border: 1px solid transparent;
    }

    .js-nav-link:hover {
        background: #f8fafc;
        color: var(--js-gray-800) !important;
    }

    .js-nav-link.active {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: var(--js-primary) !important;
        font-weight: 600;
        border: 1px solid #c7d2fe;
    }

    .js-nav-link.active::before {
        content: '';
        position: absolute;
        left: -0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 70%;
        background: linear-gradient(180deg, var(--js-primary) 0%, var(--js-primary-light) 100%);
        border-radius: 0 4px 4px 0;
    }

    .js-nav-link i {
        font-size: 1.1rem;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
        color: var(--js-gray-400);
        transition: color var(--js-transition-base);
    }

    .js-nav-link:hover i {
        color: var(--js-gray-600);
    }

    .js-nav-link.active i {
        color: var(--js-primary);
    }

    .js-nav-link span:not(.js-nav-badge) {
        flex: 1;
        min-width: 0;
    }

    /* Navigation Badge - Updated for Light Theme */
    .js-nav-badge {
        background: #ef4444;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: var(--js-radius-full);
        min-width: 20px;
        text-align: center;
        flex-shrink: 0;
        margin-left: auto;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }

    .js-nav-badge.success {
        background: var(--js-success);
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
    }

    .js-nav-badge.warning {
        background: var(--js-warning);
        box-shadow: 0 2px 4px rgba(217, 119, 6, 0.3);
    }

    .js-nav-badge.danger {
        background: var(--js-danger);
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
    }

    /* Sidebar Footer (Logout) - Light Theme Design */
    .js-sidebar-footer {
        padding: 1.25rem;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
        background: #fafbfc;
    }

    .js-logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        width: 100%;
        padding: 0.75rem 1rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        color: var(--js-gray-600);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all var(--js-transition-base);
    }

    .js-logout-btn:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    .js-logout-btn i {
        font-size: 1rem;
    }

    /* ===== Main Content Area ===== */
    .js-main {
        margin-left: var(--js-sidebar-width);
        margin-top: var(--js-topbar-height);
        min-height: calc(100vh - var(--js-topbar-height));
        background: var(--js-gray-50);
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
    }

    .js-content {
        padding: var(--js-space-6);
    }

    /* Mobile Menu Toggle Button */
    .mobile-menu-toggle {
        background: none;
        border: none;
        color: var(--js-gray-500);
        font-size: var(--js-font-size-lg);
        padding: var(--js-space-2);
        border-radius: var(--js-radius-sm);
        display: none;
        transition: all var(--js-transition-base);
    }

    .mobile-menu-toggle:hover {
        background-color: var(--js-gray-100);
        color: var(--js-primary);
    }

    /* Mobile Sidebar Overlay (legacy) */
    .mobile-sidebar-overlay {
        display: none;
    }

    /* Body scroll lock when sidebar is open */
    body.sidebar-open {
        overflow: hidden;
    }

    /* ===== Responsive Design ===== */
    @media (max-width: 991.98px) {
        .mobile-menu-toggle {
            display: block;
        }

        .js-sidebar {
            transform: translateX(-100%);
            top: 0;
            height: 100vh;
            width: var(--js-sidebar-width);
        }

        .js-sidebar.show {
            transform: translateX(0);
        }

        .js-main {
            margin-left: 0;
        }

        .js-content {
            padding: var(--js-space-5);
        }
    }

    @media (max-width: 768px) {
        .js-sidebar {
            width: 280px;
        }

        .js-content {
            padding: var(--js-space-4);
        }
    }

    @media (max-width: 576px) {
        .js-sidebar {
            width: 85%;
            max-width: 300px;
        }

        .js-content {
            padding: var(--js-space-3);
        }
    }

    /* Additional base styles */
    body.modern-jobseeker-body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: var(--js-gray-50);
        color: var(--js-gray-800);
        font-size: var(--js-font-size-base);
        line-height: 1.6;
        margin: 0;
        padding: 0;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Notification Dropdown Styling */
    .nav-item.dropdown .notification-bell {
        background: transparent;
        border: none;
        color: var(--js-gray-500);
        font-size: var(--js-font-size-lg);
        padding: var(--js-space-2);
        border-radius: var(--js-radius-md);
        cursor: pointer;
        position: relative;
        transition: all var(--js-transition-base);
    }

    .nav-item.dropdown .notification-bell:hover {
        background: var(--js-gray-100);
        color: var(--js-primary);
    }

    .nav-item.dropdown .notification-badge {
        position: absolute;
        top: var(--js-space-1);
        right: var(--js-space-1);
        background: var(--js-danger);
        color: white;
        font-size: 0.65rem;
        font-weight: 600;
        padding: 0.125rem 0.375rem;
        border-radius: var(--js-radius-full);
        min-width: 18px;
        text-align: center;
    }

    /* Flash message styles */
    .alert {
        border: none;
        border-radius: var(--js-radius-md);
        padding: var(--js-space-4) var(--js-space-6);
        margin-bottom: var(--js-space-6);
        font-size: var(--js-font-size-base);
        font-weight: 500;
        line-height: 1.5;
    }

    .alert-success {
        background-color: #ecfdf5;
        color: #047857;
        border-left: 4px solid var(--js-success);
    }

    .alert-danger {
        background-color: #fef2f2;
        color: #dc2626;
        border-left: 4px solid var(--js-danger);
    }

    .alert-warning {
        background-color: #fffbeb;
        color: #d97706;
        border-left: 4px solid var(--js-warning);
    }

    .alert-info {
        background-color: #f0f9ff;
        color: #0284c7;
        border-left: 4px solid #0ea5e9;
    }

    /* Dropdown menu improvements */
    .dropdown-menu {
        border: 1px solid var(--js-gray-200);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        border-radius: var(--js-radius-md);
        padding: var(--js-space-3);
        min-width: 220px;
    }

    .dropdown-item {
        padding: var(--js-space-3);
        font-weight: 500;
        color: var(--js-gray-800);
        border-radius: var(--js-radius-sm);
        transition: all var(--js-transition-base);
    }

    .dropdown-item:hover {
        background-color: var(--js-gray-50);
        color: var(--js-primary);
    }

    /* Hide KYC banner on specific pages */
    body.force-home .kyc-reminder-banner,
    body.homepage .kyc-reminder-banner,
    body.jobs-page .kyc-reminder-banner,
    body.companies-page .kyc-reminder-banner {
        display: none !important;
    }

    /* ===== FIX: Navbar Dropdown Scrollbar Issue ===== */
    /* Prevent dropdown from causing page scroll */
    .navbar .dropdown-menu {
        position: absolute !important;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Ensure navbar doesn't cause horizontal scroll */
    .modern-jobseeker-layout .navbar {
        overflow: visible !important;
    }

    .modern-jobseeker-layout .navbar .container-fluid {
        overflow: visible !important;
    }

    /* Fix dropdown positioning to stay within viewport */
    .navbar .dropdown-menu.dropdown-menu-end {
        right: 0;
        left: auto;
        transform: none !important;
    }

    /* Prevent body scroll when dropdown is open */
    .navbar .nav-item.dropdown {
        position: relative;
    }

    /* Ensure dropdown doesn't extend beyond viewport */
    .navbar .dropdown-menu.show {
        z-index: 1050;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid var(--js-gray-200);
        border-radius: var(--js-radius-md);
        min-width: 200px;
        padding: 0.5rem;
    }

    /* Style dropdown items */
    .navbar .dropdown-menu .dropdown-item {
        padding: 0.625rem 1rem;
        border-radius: var(--js-radius-sm);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        transition: all 0.15s ease;
    }

    .navbar .dropdown-menu .dropdown-item:hover {
        background: var(--js-gray-100);
    }

    .navbar .dropdown-menu .dropdown-item i {
        width: 16px;
        text-align: center;
        color: var(--js-gray-500);
    }

    .navbar .dropdown-menu .dropdown-item:hover i {
        color: var(--js-primary);
    }

    .navbar .dropdown-menu .dropdown-divider {
        margin: 0.375rem 0;
        border-color: var(--js-gray-200);
    }

    /* Fix for the main content not shifting when dropdown opens */
    html.dropdown-open,
    body.dropdown-open {
        overflow-x: hidden !important;
        padding-right: 0 !important;
    }

    /* Ensure the layout doesn't shift */
    .modern-jobseeker-layout {
        overflow-x: hidden;
    }

    /* ===== Global Scrollbar Styling - Light Theme ===== */
    /* Prevent dark scrollbar when dropdown opens */
    html, body {
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
    [class*="dropdown"] *,
    .popover,
    .popover *,
    .modal,
    .modal * {
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
</head>
<body class="modern-jobseeker-body <?php if(request()->routeIs('home') || request()->has('force_home')): ?> homepage <?php endif; ?> <?php if(request()->routeIs('jobs') || request()->routeIs('jobs.*')): ?> jobs-page <?php endif; ?> <?php if(request()->routeIs('companies') || request()->routeIs('companies.*')): ?> companies-page <?php endif; ?> <?php if(request()->has('force_home')): ?> force-home <?php endif; ?>">
    <?php echo $__env->make('components.maintenance-banner', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <?php if(auth()->guard()->check()): ?>
        
        
    <?php endif; ?>
<!-- Modern Jobseeker Design -->
<div class="modern-jobseeker-layout">
    <!-- Use Main Navbar Component -->
    <?php echo $__env->make('components.main-navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Sidebar Overlay for Mobile -->
    <div class="js-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Jobseeker Sidebar -->
    <?php
        $user = Auth::user();
        $savedCount = $user ? $user->savedJobs()->count() : 0;
        $applicationsCount = $user ? \App\Models\JobApplication::where('user_id', $user->id)->count() : 0;
    ?>

    <aside class="js-sidebar" id="jobseekerSidebar">
        <!-- Brand Section -->
        <div class="js-sidebar-brand">
            <a href="<?php echo e(route('home')); ?>" class="js-brand-link">
                <div class="js-brand-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <span class="js-brand-text">TugmaJobs</span>
            </a>
        </div>

        <!-- Profile Section -->
        <div class="js-sidebar-profile">
            <a href="<?php echo e(route('account.myProfile')); ?>" class="js-profile-link">
                <?php if($user && $user->image): ?>
                    <img src="<?php echo e(asset('profile_img/thumb/' . $user->image)); ?>" alt="Profile" class="js-profile-avatar">
                <?php else: ?>
                    <div class="js-profile-avatar-placeholder">
                        <?php echo e($user ? strtoupper(substr($user->name, 0, 1)) : 'G'); ?>

                    </div>
                <?php endif; ?>
                <div class="js-profile-info">
                    <h6 class="js-profile-name"><?php echo e($user ? $user->name : 'Guest'); ?></h6>
                    <p class="js-profile-role">Job Seeker</p>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="js-sidebar-nav">
            <!-- Overview Section -->
            <div class="js-nav-section">
                <div class="js-nav-section-title">Overview</div>
                <ul class="js-nav-list">
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.dashboard')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.dashboard') ? 'active' : ''); ?>" data-tooltip="Dashboard">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.analytics')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.analytics') ? 'active' : ''); ?>" data-tooltip="Analytics">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Job Search Section -->
            <div class="js-nav-section">
                <div class="js-nav-section-title">Job Search</div>
                <ul class="js-nav-list">
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('jobs')); ?>" class="js-nav-link <?php echo e(request()->routeIs('jobs') || request()->routeIs('jobs.*') ? 'active' : ''); ?>" data-tooltip="Find Jobs">
                            <i class="fas fa-search"></i>
                            <span>Find Jobs</span>
                        </a>
                    </li>
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.myJobApplications')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.myJobApplications') ? 'active' : ''); ?>" data-tooltip="Applications">
                            <i class="fas fa-file-alt"></i>
                            <span>Applications</span>
                            <?php if($applicationsCount > 0): ?>
                                <span class="js-nav-badge"><?php echo e($applicationsCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.saved-jobs.index')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.saved-jobs.*') ? 'active' : ''); ?>" data-tooltip="Saved Jobs">
                            <i class="fas fa-bookmark"></i>
                            <span>Saved Jobs</span>
                            <?php if($savedCount > 0): ?>
                                <span class="js-nav-badge"><?php echo e($savedCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Profile Section -->
            <div class="js-nav-section">
                <div class="js-nav-section-title">Profile</div>
                <ul class="js-nav-list">
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.myProfile')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.myProfile') ? 'active' : ''); ?>" data-tooltip="My Profile">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.resume-builder.index')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.resume-builder.*') ? 'active' : ''); ?>" data-tooltip="Resume Builder">
                            <i class="fas fa-file-pdf"></i>
                            <span>Resume Builder</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Settings Section -->
            <div class="js-nav-section">
                <div class="js-nav-section-title">Settings</div>
                <ul class="js-nav-list">
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.settings')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.settings') ? 'active' : ''); ?>" data-tooltip="Account Settings">
                            <i class="fas fa-cog"></i>
                            <span>Account Settings</span>
                        </a>
                    </li>
                    <li class="js-nav-item">
                        <a href="<?php echo e(route('account.changePassword')); ?>" class="js-nav-link <?php echo e(request()->routeIs('account.changePassword') ? 'active' : ''); ?>" data-tooltip="Change Password">
                            <i class="fas fa-key"></i>
                            <span>Change Password</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Logout Footer -->
        <div class="js-sidebar-footer">
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="js-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="js-main">
        <div class="js-content">
            <?php echo $__env->yieldContent('jobseeker-content'); ?>
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
document.addEventListener('DOMContentLoaded', function() {
    // Clean up old sidebar collapsed state
    localStorage.removeItem('sidebarCollapsed');
    document.body.classList.remove('sidebar-collapsed');

    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.getElementById('jobseekerSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            if (sidebar) sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            if (sidebar) sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        }
    });

    // Fix: Prevent scrollbar when navbar dropdown opens
    const navbarDropdowns = document.querySelectorAll('.navbar .dropdown');
    navbarDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('show.bs.dropdown', function() {
            // Store current scroll position and prevent scroll jump
            document.body.style.paddingRight = '0px';
            document.documentElement.style.overflow = 'hidden';
            document.documentElement.style.overflowY = 'scroll';
        });

        dropdown.addEventListener('hide.bs.dropdown', function() {
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
        kycBanners.forEach(function(banner) {
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
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                closeSidebar();
            }
        });
    });
});
</script>

<!-- Toast Notifications -->
<?php echo $__env->make('components.toast-notifications', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- KYC Completion Handler -->
<?php if(auth()->guard()->check()): ?>
<?php if(Auth::user()->kyc_status === 'in_progress' || request()->routeIs('kyc.*')): ?>
<script src="<?php echo e(asset('assets/js/kyc-completion-handler.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/kyc-cross-device-handler.js')); ?>"></script>
<?php endif; ?>
<?php endif; ?>

<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/layouts/jobseeker.blade.php ENDPATH**/ ?>