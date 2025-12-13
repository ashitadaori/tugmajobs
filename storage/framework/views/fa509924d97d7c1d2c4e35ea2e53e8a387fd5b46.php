
<?php if(auth()->guard()->check()): ?>
    <?php if(Auth::user()->role === 'jobseeker'): ?>
        
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom" style="padding: 0.75rem 0; position: sticky; top: 0; z-index: 1000;">
            <div class="container-fluid px-4">
                <a class="navbar-brand d-flex align-items-center" href="<?php echo e(route('home')); ?>" style="margin: 0;">
                    <i class="fas fa-briefcase text-primary" style="font-size: 1.25rem;"></i>
                    <strong class="text-primary ms-2" style="font-size: 1.125rem;">TugmaJobs</strong>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home', ['force_home' => 1])); ?>">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('jobs*') ? 'active' : ''); ?>" href="<?php echo e(route('jobs')); ?>">
                                <i class="fas fa-search"></i> Find Jobs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('companies*') ? 'active' : ''); ?>" href="<?php echo e(route('companies')); ?>">
                                <i class="fas fa-building"></i> Companies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('account.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('account.dashboard')); ?>">
                                <i class="fas fa-user"></i> My Career
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item">
                            <?php echo $__env->make('components.jobseeker-notification-dropdown', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </li>
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" style="gap: 0.5rem;">
                                <?php if(Auth::user()->image): ?>
                                    <img src="<?php echo e(asset('profile_img/thumb/' . Auth::user()->image)); ?>"
                                         alt="Profile"
                                         class="rounded-circle"
                                         style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #e5e7eb;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                         style="width: 32px; height: 32px; border: 2px solid #e5e7eb;">
                                        <span style="font-size: 0.875rem; color: #6b7280; font-weight: 600;">
                                            <?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?>

                                        </span>
                                    </div>
                                <?php endif; ?>
                                <span class="d-none d-md-inline"><?php echo e(Auth::user()->name); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo e(route('account.myProfile')); ?>">
                                    <i class="fas fa-user"></i> My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('account.settings')); ?>">
                                    <i class="fas fa-cog"></i> Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
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
    <?php elseif(Auth::user()->role === 'employer'): ?>
        
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
                    <i class="fas fa-briefcase text-primary"></i>
                    <strong class="text-primary">TugmaJobs</strong>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home')); ?>">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('jobs*') ? 'active' : ''); ?>" href="<?php echo e(route('jobs')); ?>">
                                <i class="fas fa-search"></i> Find Jobs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('companies*') ? 'active' : ''); ?>" href="<?php echo e(route('companies')); ?>">
                                <i class="fas fa-building"></i> Companies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('employer.*') ? 'active' : ''); ?>" href="<?php echo e(route('employer.dashboard')); ?>">
                                <i class="fas fa-briefcase"></i> Employer Dashboard
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <?php echo $__env->make('components.notification-dropdown', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php echo e(Auth::user()->name); ?>

                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo e(route('employer.profile.edit')); ?>">
                                    <i class="fas fa-user"></i> Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('employer.settings.index')); ?>">
                                    <i class="fas fa-cog"></i> Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
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
    <?php endif; ?>
<?php else: ?>
    
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
                <i class="fas fa-briefcase text-primary"></i>
                <strong class="text-primary">TugmaJobs</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home')); ?>">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('jobs*') ? 'active' : ''); ?>" href="<?php echo e(route('jobs')); ?>">
                            <i class="fas fa-search"></i> Find Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('companies*') ? 'active' : ''); ?>" href="<?php echo e(route('companies')); ?>">
                            <i class="fas fa-building"></i> Companies
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <button type="button" class="btn btn-link nav-link" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToLogin()">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#employerAuthModal" onclick="switchToEmployerLogin()">
                            <i class="fas fa-building"></i> Employer
                        </button>
                    </li>
                    <li class="nav-item ms-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToRegister()">
                            <i class="fas fa-user-plus"></i> Get Started
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    
    <?php echo $__env->make('components.auth-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('components.employer-auth-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/main-navbar.blade.php ENDPATH**/ ?>