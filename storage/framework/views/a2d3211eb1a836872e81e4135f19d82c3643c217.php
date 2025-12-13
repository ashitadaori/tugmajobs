<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="cache-version" content="<?php echo e(config('app.asset_version', 'v1')); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Admin Dashboard - <?php echo e(config('app.name')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-link:hover {
            background: #e9ecef;
        }
        .nav-link.active {
            background: #0d6efd;
            color: white;
        }
        .nav-link-success {
            background: #10b981 !important;
            color: white !important;
        }
        .nav-link-success:hover {
            background: #059669 !important;
            color: white !important;
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
        
        /* Force small pagination arrows */
        .pagination {
            font-size: 14px !important;
        }
        
        /* Target SVG arrows specifically */
        .pagination svg,
        .pagination .page-link svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            font-size: 14px !important;
        }
        
        /* Override any inline styles on page links */
        .pagination .page-link {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 36px !important;
            height: 36px !important;
        }
        
        .pagination .page-item {
            margin: 0 2px !important;
        }
        
        /* Force arrow text size if using text arrows */
        .pagination .page-link:first-child,
        .pagination .page-link:last-child {
            font-size: 1rem !important;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <!-- Profile Section -->
                <div class="sidebar-profile">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo e(Auth::user()->profile_image ?? asset('images/default-profile.svg')); ?>"
                             alt="Profile" class="admin-avatar me-2">
                        <div class="sidebar-profile-info">
                            <div class="sidebar-profile-name"><?php echo e(Auth::user()->name); ?></div>
                            <div class="sidebar-profile-role"><?php echo e(ucfirst(Auth::user()->role)); ?></div>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2">Navigation</p>
                <nav class="nav flex-column">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                        <i class="bi bi-grid me-2"></i> Dashboard
                    </a>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                        <i class="bi bi-people me-2"></i> User Management
                    </a>
                    <a href="<?php echo e(route('admin.jobs.create')); ?>" class="nav-link nav-link-success <?php echo e(request()->routeIs('admin.jobs.create') ? 'active' : ''); ?>">
                        <i class="bi bi-plus-circle me-2"></i> Post New Job
                    </a>
                    <a href="<?php echo e(route('admin.jobs.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.jobs.index') ? 'active' : ''); ?>">
                        <i class="bi bi-list-check me-2"></i> My Posted Jobs
                    </a>
                    <a href="<?php echo e(route('admin.company-management.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.company-management.*') ? 'active' : ''); ?>">
                        <i class="bi bi-building me-2"></i> Company Management
                    </a>
                    <a href="<?php echo e(route('admin.companies.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.companies.*') && !request()->routeIs('admin.company-management.*') ? 'active' : ''); ?>">
                        <i class="bi bi-briefcase me-2"></i> Employer Accounts
                    </a>
                    <a href="<?php echo e(route('admin.jobs.pending')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.jobs.pending') ? 'active' : ''); ?>">
                        <i class="bi bi-clock me-2"></i> Pending Jobs
                    </a>
                    <a href="<?php echo e(route('admin.analytics.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.analytics.*') ? 'active' : ''); ?>">
                        <i class="bi bi-graph-up me-2"></i> Analytics
                    </a>
                    <a href="<?php echo e(route('admin.kyc.didit-verifications')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.kyc.*') ? 'active' : ''); ?>">
                        <i class="bi bi-card-checklist me-2"></i> KYC Verifications
                        <?php
                            $pendingKycCount = \App\Models\User::whereHas('kycVerifications', function($q) {
                                $q->whereIn('status', ['pending', 'in_progress']);
                            })->count();
                        ?>
                        <?php if($pendingKycCount > 0): ?>
                            <span class="badge bg-warning text-dark ms-auto"><?php echo e($pendingKycCount); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo e(route('admin.employers.documents.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.employers.documents.*') ? 'active' : ''); ?>">
                        <i class="bi bi-file-earmark-text me-2"></i> Employer Documents
                        <?php
                            $pendingDocsCount = \App\Models\EmployerDocument::where('status', 'pending')->count();
                        ?>
                        <?php if($pendingDocsCount > 0): ?>
                            <span class="badge bg-warning text-dark ms-auto"><?php echo e($pendingDocsCount); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo e(route('admin.posters.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.posters.*') ? 'active' : ''); ?>">
                        <i class="bi bi-image me-2"></i> Poster Builder
                    </a>
                </nav>

                <p class="text-muted small mb-2 mt-4">Account</p>
                <nav class="nav flex-column">
                    <a href="<?php echo e(route('admin.profile.edit')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.profile.*') ? 'active' : ''); ?>">
                        <i class="bi bi-person me-2"></i> My Profile
                    </a>
                    <a href="<?php echo e(route('admin.profile.password')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.profile.password') ? 'active' : ''); ?>">
                        <i class="bi bi-lock me-2"></i> Change Password
                    </a>
                </nav>

                <p class="text-muted small mb-2 mt-4">System</p>
                <nav class="nav flex-column">
                    <a href="<?php echo e(route('admin.maintenance.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.maintenance.*') ? 'active' : ''); ?>">
                        <i class="bi bi-tools me-2"></i> Maintenance Mode
                    </a>
                    <a href="<?php echo e(route('admin.admins.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.admins.*') ? 'active' : ''); ?>">
                        <i class="bi bi-person-badge me-2"></i> Admin Management
                    </a>
                    <a href="<?php echo e(route('admin.settings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
                        <i class="bi bi-gear me-2"></i> Site Settings
                    </a>
                </nav>

                <!-- Logout -->
                <div class="mt-4">
                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
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
                        <h4 class="mb-0"><?php echo $__env->yieldContent('page_title', 'Dashboard'); ?></h4>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Global Search -->
                        <?php echo $__env->make('admin.partials.global-search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- Notification Center -->
                        <?php echo $__env->make('admin.partials.notification-center', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- Profile Menu -->
                        <div class="profile-menu dropdown">
                            <button class="btn btn-link dropdown-toggle text-dark" type="button" id="profileDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo e(Auth::user()->profile_image ?? asset('images/default-profile.svg')); ?>"
                                     alt="Profile" class="admin-avatar">
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                                <li><h6 class="dropdown-header"><?php echo e(Auth::user()->name); ?></h6></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('admin.profile.edit')); ?>">
                                    <i class="bi bi-person me-2"></i> My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('admin.profile.password')); ?>">
                                    <i class="bi bi-lock me-2"></i> Change Password
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <main class="content-area">
                    <?php echo $__env->yieldContent('content'); ?>
                </main>
            </div>
        </div>
    </div>

    <!-- Quick Actions Widget (Floating) -->
    <?php echo $__env->make('admin.partials.quick-actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php echo $__env->yieldContent('scripts'); ?>
    
    <!-- Toast Notification Container -->
    <div id="adminToastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
    
    <script>
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
    <?php if(session('success')): ?>
        showAdminToast('<?php echo e(session('success')); ?>', 'success');
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        showAdminToast('<?php echo e(session('error')); ?>', 'error');
    <?php endif; ?>
    
    <?php if(session('warning')): ?>
        showAdminToast('<?php echo e(session('warning')); ?>', 'warning');
    <?php endif; ?>
    
    <?php if(session('info')): ?>
        showAdminToast('<?php echo e(session('info')); ?>', 'info');
    <?php endif; ?>
    </script>
</body>
</html> <?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/layouts/admin.blade.php ENDPATH**/ ?>