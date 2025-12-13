<?php
use Illuminate\Support\Facades\Storage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('page_title', 'Dashboard'); ?> - Employer Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Professional Employer Styles -->
    <link href="<?php echo e(asset('assets/css/employer-professional.css')); ?>?v=<?php echo e(time()); ?>" rel="stylesheet">

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php if(auth()->guard()->check()): ?>
    <meta name="user-id" content="<?php echo e(Auth::id()); ?>">
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="employer-wrapper">
    <?php echo $__env->make('components.maintenance-banner', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Sidebar Overlay for Mobile -->
    <div class="ep-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <?php
        $user = auth()->user();
        $employerProfile = \App\Models\Employer::where('user_id', $user->id)->first();
        $isVerified = $user->kyc_status === 'verified';
        $canPostJobs = $user->canPostJobs();

        // Get counts for badges
        $totalJobs = \App\Models\Job::where('employer_id', auth()->id())->count();
        $pendingApplications = \App\Models\JobApplication::whereHas('job', function($q) {
            $q->where('employer_id', auth()->id());
        })->where('status', 'pending')->count();
        $reviewCount = \App\Models\Review::where('employer_id', auth()->id())->count();
        $avgRating = \App\Models\Review::getCompanyAverageRating(auth()->id());
    ?>

    <aside class="ep-sidebar" id="employerSidebar">
        <!-- Profile Section -->
        <div class="ep-sidebar-profile">
            <?php if($user->profile_photo): ?>
                <img src="<?php echo e(asset('profile_img/' . $user->profile_photo)); ?>" alt="Profile" class="ep-profile-avatar">
            <?php elseif($employerProfile && $employerProfile->company_logo): ?>
                <img src="<?php echo e($employerProfile->logo_url); ?>" alt="Company Logo" class="ep-profile-avatar">
            <?php else: ?>
                <div class="ep-profile-avatar-placeholder">
                    <?php echo e(substr($employerProfile->company_name ?? $user->name, 0, 1)); ?>

                </div>
            <?php endif; ?>
            <div class="ep-profile-info">
                <h6 class="ep-profile-name"><?php echo e($employerProfile->company_name ?? $user->name); ?></h6>
                <p class="ep-profile-role">Employer Account</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="ep-sidebar-nav">
            <!-- Overview Section -->
            <div class="ep-nav-section">
                <div class="ep-nav-section-title">Overview</div>
                <ul class="ep-nav-list">
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.dashboard')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.dashboard') ? 'active' : ''); ?>">
                            <i class="bi bi-grid-1x2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.analytics.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.analytics*') ? 'active' : ''); ?>">
                            <i class="bi bi-bar-chart-line"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Recruitment Section -->
            <div class="ep-nav-section">
                <div class="ep-nav-section-title">Recruitment</div>
                <ul class="ep-nav-list">
                    <?php if($canPostJobs): ?>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.jobs.create')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.jobs.create') ? 'active' : ''); ?>">
                            <i class="bi bi-plus-circle"></i>
                            <span>Post New Job</span>
                            <?php if($isVerified): ?>
                                <span class="ep-nav-badge success">Instant</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.jobs.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.jobs.index') && !request()->has('status') ? 'active' : ''); ?>">
                            <i class="bi bi-briefcase"></i>
                            <span>All Jobs</span>
                            <?php if($totalJobs > 0): ?>
                                <span class="ep-nav-badge"><?php echo e($totalJobs); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.applications.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.applications*') ? 'active' : ''); ?>">
                            <i class="bi bi-people"></i>
                            <span>Applications</span>
                            <?php if($pendingApplications > 0): ?>
                                <span class="ep-nav-badge warning"><?php echo e($pendingApplications); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Company Section -->
            <div class="ep-nav-section">
                <div class="ep-nav-section-title">Company</div>
                <ul class="ep-nav-list">
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.profile.edit')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.profile*') ? 'active' : ''); ?>">
                            <i class="bi bi-building"></i>
                            <span>Company Profile</span>
                        </a>
                    </li>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.reviews.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.reviews*') ? 'active' : ''); ?>">
                            <i class="bi bi-star"></i>
                            <span>Reviews</span>
                            <?php if($reviewCount > 0): ?>
                                <span class="ep-nav-badge" style="background: #fbbf24; color: #1f2937;"><?php echo e(number_format($avgRating, 1)); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.documents.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.documents*') ? 'active' : ''); ?>">
                            <i class="bi bi-file-earmark-check"></i>
                            <span>Documents & KYC</span>
                            <?php if($isVerified): ?>
                                <span class="ep-nav-badge success"><i class="bi bi-check"></i></span>
                            <?php else: ?>
                                <span class="ep-nav-badge danger"><i class="bi bi-exclamation"></i></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Settings Section -->
            <div class="ep-nav-section">
                <div class="ep-nav-section-title">Settings</div>
                <ul class="ep-nav-list">
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.settings.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.settings.index') ? 'active' : ''); ?>">
                            <i class="bi bi-gear"></i>
                            <span>General</span>
                        </a>
                    </li>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.settings.notifications')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.settings.notifications') ? 'active' : ''); ?>">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li class="ep-nav-item">
                        <a href="<?php echo e(route('employer.settings.security')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('employer.settings.security') ? 'active' : ''); ?>">
                            <i class="bi bi-shield-lock"></i>
                            <span>Security</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Logout -->
        <div class="ep-sidebar-footer">
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="ep-logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ep-main">
        <!-- Top Bar -->
        <header class="ep-topbar">
            <div class="ep-topbar-left">
                <button class="ep-mobile-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="ep-page-title"><?php echo $__env->yieldContent('page_title', 'Dashboard'); ?></h1>
            </div>
            <div class="ep-topbar-right">
                <!-- Notifications -->
                <?php echo $__env->make('components.notification-dropdown', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="ep-btn ep-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="gap: 8px;">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline"><?php echo e($user->name); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?php echo e($user->name); ?></h6></li>
                        <li><a class="dropdown-item" href="<?php echo e(route('employer.profile.edit')); ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(route('employer.settings.security')); ?>"><i class="bi bi-lock me-2"></i>Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="<?php echo e(route('logout')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="ep-content">
            
            <?php echo $__env->make('components.toast-notifications', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- KYC & Verification Modals -->
    <?php echo $__env->make('components.kyc-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('components.verification-alert-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Toastify -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script src="<?php echo e(asset('assets/js/notifications.js')); ?>"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('employerSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
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

        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Check if we should show the KYC modal
        <?php if(session('show_kyc_modal')): ?>
            if (typeof showKycModal === 'function') {
                showKycModal();
            }
        <?php endif; ?>
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
                    <a href="<?php echo e(route('employer.profile.edit')); ?>" class="btn btn-primary">Complete Verification</a>
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

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/layouts/employer.blade.php ENDPATH**/ ?>