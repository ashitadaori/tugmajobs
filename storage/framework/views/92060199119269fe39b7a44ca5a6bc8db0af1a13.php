

<?php $__env->startSection('page-title', isset($job) ? $job->title . ' - Job Details' : 'Job Details'); ?>

<?php $__env->startSection('jobseeker-content'); ?>
<!-- Modern Hero Header -->
<div class="job-detail-hero">
    <div class="container-fluid px-0">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav ws-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item ws-breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item ws-breadcrumb-item"><a href="<?php echo e(route('jobs')); ?>"><i class="fas fa-briefcase"></i> Jobs</a></li>
                <li class="breadcrumb-item ws-breadcrumb-item active" aria-current="page"><?php echo e(Str::limit($job->title, 30)); ?></li>
            </ol>
        </nav>

        <!-- Job Header Card -->
        <div class="job-header-card ws-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3 ws-flex ws-items-start ws-gap-3">
                        <?php
                            $companyName = null;
                            $companyInitial = 'C';

                            // First check if linked to a company via company_id
                            if ($job->company_id && $job->company && !empty($job->company->name)) {
                                $companyName = $job->company->name;
                            }
                            elseif (!empty($job->company_name) && $job->company_name !== 'Confidential') {
                                $companyName = $job->company_name;
                            }
                            elseif ($job->employer && $job->employer->employerProfileDirect && !empty($job->employer->employerProfileDirect->company_name)) {
                                $companyName = $job->employer->employerProfileDirect->company_name;
                            }
                            elseif ($job->employer && !empty($job->employer->company_name)) {
                                $companyName = $job->employer->company_name;
                            }
                            elseif ($job->employer && !empty($job->employer->name)) {
                                $companyName = $job->employer->name;
                            }
                            else {
                                $companyName = 'Confidential';
                            }

                            $companyInitial = substr($companyName, 0, 1);
                        ?>

                        <div class="company-logo-wrapper ws-avatar ws-avatar-xl">
                            <div class="company-logo-badge">
                                <?php echo e($companyInitial); ?>

                            </div>
                        </div>

                        <div class="flex-grow-1">
                            <div class="job-meta-tags mb-2 ws-flex ws-flex-wrap ws-gap-2">
                                <span class="job-type-badge ws-badge ws-badge-primary <?php echo e(strtolower($job->jobType->name)); ?>">
                                    <i class="fas fa-briefcase"></i> <?php echo e($job->jobType->name); ?>

                                </span>
                                <?php if($job->category): ?>
                                    <span class="category-badge ws-badge ws-badge-info">
                                        <i class="fas fa-tag"></i> <?php echo e($job->category->name); ?>

                                    </span>
                                <?php endif; ?>
                                <span class="posted-badge ws-badge ws-badge-secondary">
                                    <i class="fas fa-clock"></i> Posted <?php echo e(\Carbon\Carbon::parse($job->created_at)->diffForHumans()); ?>

                                </span>
                            </div>

                            <h1 class="job-title-modern ws-text-3xl ws-font-bold"><?php echo e($job->title); ?></h1>

                            <div class="company-info-inline ws-flex ws-flex-wrap ws-gap-3 ws-items-center">
                                <span class="company-name-badge ws-text-lg ws-font-medium">
                                    <i class="fas fa-building"></i> <?php echo e($companyName); ?>

                                </span>
                                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.verified-badge','data' => ['user' => $job->employer,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('verified-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($job->employer),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                                <span class="location-badge ws-badge ws-badge-secondary">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo e($job->getFullAddress() ?: $job->location); ?>

                                </span>
                                <?php if(!is_null($job->salary)): ?>
                                    <span class="salary-badge ws-badge ws-badge-success">
                                        <i class="fas fa-dollar-sign"></i> <?php echo e($job->salary); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="job-actions-modern ws-flex ws-gap-2">
                        <?php if(auth()->guard()->check()): ?>
                            <?php if(Auth::user()->role === 'jobseeker'): ?>
                                <button type="button"
                                        id="saveJobBtn-<?php echo e($job->id); ?>"
                                        onclick="toggleSaveJob(<?php echo e($job->id); ?>)"
                                        class="btn-modern btn-save ws-btn ws-btn-outline <?php echo e($count > 0 ? 'saved ws-btn-success' : ''); ?>"
                                        aria-label="<?php echo e($count > 0 ? 'Remove from saved' : 'Save for later'); ?>">
                                    <i class="fa-heart <?php echo e($count > 0 ? 'fas' : 'far'); ?>" id="saveJobIcon-<?php echo e($job->id); ?>"></i>
                                    <span><?php echo e($count > 0 ? 'Saved' : 'Save'); ?></span>
                                </button>

                                <?php if(Auth::user()->isKycVerified()): ?>
                                    <a href="<?php echo e(route('job.application.start', $job->id)); ?>"
                                       class="btn-modern btn-apply ws-btn ws-btn-primary ws-btn-lg">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>Apply Now</span>
                                    </a>
                                <?php else: ?>
                                    <button type="button"
                                            class="btn-modern btn-apply ws-btn ws-btn-primary ws-btn-lg"
                                            data-bs-toggle="modal"
                                            data-bs-target="#kycRequiredModal">
                                        <i class="fas fa-lock"></i>
                                        <span>Apply Now</span>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="btn-modern btn-save ws-btn ws-btn-outline">
                                <i class="far fa-heart"></i>
                                <span>Save</span>
                            </a>
                            <a href="<?php echo e(route('login')); ?>" class="btn-modern btn-apply ws-btn ws-btn-primary ws-btn-lg">
                                <i class="fas fa-paper-plane"></i>
                                <span>Apply Now</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="container-fluid content-area-modern px-0">
    <div class="row g-4">
        <!-- Left Column - Job Details -->
        <div class="col-lg-8">

            <!-- Job Overview Card -->
            <div class="modern-card ws-card">
                <div class="card-header-modern ws-card-header">
                    <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                        <i class="fas fa-file-alt"></i> Job Overview
                    </h2>
                </div>
                <div class="card-body-modern ws-card-body">
                    <div class="overview-grid ws-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div class="overview-item ws-flex ws-items-center ws-gap-3">
                            <div class="overview-icon ws-stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="overview-content">
                                <span class="overview-label ws-text-sm ws-text-secondary">Salary Range</span>
                                <span class="overview-value ws-text-lg ws-font-semibold"><?php echo e($job->salary ?? 'Negotiable'); ?></span>
                            </div>
                        </div>

                        <div class="overview-item ws-flex ws-items-center ws-gap-3">
                            <div class="overview-icon ws-stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="overview-content">
                                <span class="overview-label ws-text-sm ws-text-secondary">Job Type</span>
                                <span class="overview-value ws-text-lg ws-font-semibold"><?php echo e($job->jobType->name); ?></span>
                            </div>
                        </div>

                        <div class="overview-item ws-flex ws-items-center ws-gap-3">
                            <div class="overview-icon ws-stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="overview-content">
                                <span class="overview-label ws-text-sm ws-text-secondary">Vacancies</span>
                                <span class="overview-value ws-text-lg ws-font-semibold"><?php echo e($job->vacancy); ?> Position(s)</span>
                            </div>
                        </div>

                        <div class="overview-item ws-flex ws-items-center ws-gap-3">
                            <div class="overview-icon ws-stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="overview-content">
                                <span class="overview-label ws-text-sm ws-text-secondary">Date Posted</span>
                                <span class="overview-value ws-text-lg ws-font-semibold"><?php echo e(\Carbon\Carbon::parse($job->created_at)->format('M d, Y')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Description -->
            <div class="modern-card ws-card">
                <div class="card-header-modern ws-card-header">
                    <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                        <i class="fas fa-align-left"></i> Job Description
                    </h2>
                </div>
                <div class="card-body-modern ws-card-body">
                    <div class="rich-text-content ws-text-base">
                        <?php echo nl2br(e($job->description)); ?>

                    </div>
                </div>
            </div>

            <?php if(!empty($job->responsibility)): ?>
                <!-- Responsibilities -->
                <div class="modern-card ws-card">
                    <div class="card-header-modern ws-card-header">
                        <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                            <i class="fas fa-tasks"></i> Key Responsibilities
                        </h2>
                    </div>
                    <div class="card-body-modern ws-card-body">
                        <div class="rich-text-content responsibility-list ws-text-base">
                            <?php echo nl2br(e($job->responsibility)); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(!empty($job->qualifications)): ?>
                <!-- Qualifications -->
                <div class="modern-card ws-card">
                    <div class="card-header-modern ws-card-header">
                        <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                            <i class="fas fa-graduation-cap"></i> Required Qualifications
                        </h2>
                    </div>
                    <div class="card-body-modern ws-card-body">
                        <div class="rich-text-content qualification-list ws-text-base">
                            <?php echo nl2br(e($job->qualifications)); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(!empty($job->benefits)): ?>
                <!-- Benefits -->
                <div class="modern-card ws-card">
                    <div class="card-header-modern ws-card-header">
                        <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                            <i class="fas fa-gift"></i> Benefits & Perks
                        </h2>
                    </div>
                    <div class="card-body-modern ws-card-body">
                        <div class="rich-text-content benefits-list ws-text-base">
                            <?php echo nl2br(e($job->benefits)); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Applicants Section -->
            <?php if(Auth::user() && Auth::user()->id == $job->user_id): ?>
                <div class="content-card">
                    <h3 class="mb-4">Applicants</h3>
                    <?php if($applications->isNotEmpty()): ?>
                        <div class="applicants-list">
                            <?php $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="applicant-item">
                                    <div class="applicant-info">
                                        <h4><?php echo e($application->user->name); ?></h4>
                                        <p class="mb-0"><?php echo e($application->user->email); ?></p>
                                        <p class="mb-0"><?php echo e($application->user->mobile); ?></p>
                                    </div>
                                    <div class="applicant-date">
                                        <?php echo e(\Carbon\Carbon::parse($application->applied_date)->format('d M, Y')); ?>

                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="no-applicants">
                            <i class="fas fa-users mb-3"></i>
                            <p>No applicants yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Reviews & Ratings Section -->
            <?php
                $jobReviews = \App\Models\Review::where('job_id', $job->id)
                    ->where('review_type', 'job')
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $companyReviews = \App\Models\Review::where('employer_id', $job->employer_id)
                    ->where('review_type', 'company')
                    ->with('user', 'job')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $jobAvgRating = \App\Models\Review::getJobAverageRating($job->id);
                $companyAvgRating = \App\Models\Review::getCompanyAverageRating($job->employer_id);
                
                $jobRatingDist = \App\Models\Review::getJobRatingDistribution($job->id);
                $companyRatingDist = \App\Models\Review::getCompanyRatingDistribution($job->employer_id);
                
                // Check if user can review
                $canReviewJob = false;
                $canReviewCompany = false;
                $hasApplied = false;
                $hasReviewedJob = false;
                $hasReviewedCompany = false;
                
                if (Auth::check() && Auth::user()->role === 'jobseeker') {
                    // Check if user has applied
                    $hasApplied = \App\Models\JobApplication::where('user_id', Auth::id())
                        ->where('job_id', $job->id)
                        ->exists();
                    
                    if ($hasApplied) {
                        // Check if already reviewed
                        $hasReviewedJob = \App\Models\Review::where('user_id', Auth::id())
                            ->where('job_id', $job->id)
                            ->where('review_type', 'job')
                            ->exists();
                        
                        $hasReviewedCompany = \App\Models\Review::where('user_id', Auth::id())
                            ->where('job_id', $job->id)
                            ->where('review_type', 'company')
                            ->exists();
                        
                        $canReviewJob = !$hasReviewedJob;
                        $canReviewCompany = !$hasReviewedCompany;
                    }
                }
            ?>

            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Reviews & Ratings</h3>
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(Auth::user()->role === 'jobseeker'): ?>
                            <?php if(!$hasApplied): ?>
                                <button type="button" class="btn btn-outline-secondary" disabled data-bs-toggle="tooltip" 
                                        title="You need to apply to this job before you can write a review">
                                    <i class="fas fa-lock me-2"></i>Apply First to Review
                                </button>
                            <?php elseif($canReviewJob || $canReviewCompany): ?>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                    <i class="fas fa-star me-2"></i>Write a Review
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary" disabled data-bs-toggle="tooltip" 
                                        title="You have already reviewed this job and company">
                                    <i class="fas fa-check-circle me-2"></i>Already Reviewed
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">
                            <i class="fas fa-star me-2"></i>Login to Review
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Review Tabs -->
                <ul class="nav nav-tabs mb-4" id="reviewTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="job-reviews-tab" data-bs-toggle="tab" data-bs-target="#job-reviews" type="button" role="tab">
                            Job Reviews (<?php echo e($jobReviews->count()); ?>)
                            <?php if($jobAvgRating): ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="fas fa-star"></i> <?php echo e(number_format($jobAvgRating, 1)); ?>

                                </span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="company-reviews-tab" data-bs-toggle="tab" data-bs-target="#company-reviews" type="button" role="tab">
                            Company Reviews (<?php echo e($companyReviews->count()); ?>)
                            <?php if($companyAvgRating): ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="fas fa-star"></i> <?php echo e(number_format($companyAvgRating, 1)); ?>

                                </span>
                            <?php endif; ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="reviewTabsContent">
                    <!-- Job Reviews Tab -->
                    <div class="tab-pane fade show active" id="job-reviews" role="tabpanel">
                        <?php if($jobReviews->isEmpty()): ?>
                            <div class="no-reviews text-center py-5">
                                <i class="fas fa-star-half-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No reviews yet. Be the first to review this job!</p>
                            </div>
                        <?php else: ?>
                            <?php $__currentLoopData = $jobReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('components.review-card', ['review' => $review], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Company Reviews Tab -->
                    <div class="tab-pane fade" id="company-reviews" role="tabpanel">
                        <?php if($companyReviews->isEmpty()): ?>
                            <div class="no-reviews text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No company reviews yet. Be the first to review this company!</p>
                            </div>
                        <?php else: ?>
                            <?php $__currentLoopData = $companyReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('components.review-card', ['review' => $review], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="col-lg-4">

            <!-- Quick Apply Card -->
            <div class="modern-card sticky-sidebar">
                <div class="card-header-modern">
                    <h2 class="section-title-modern">
                        <i class="fas fa-bolt"></i> Quick Apply
                    </h2>
                </div>
                <div class="card-body-modern">
                    <div class="quick-apply-content">
                        <div class="apply-info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="info-label">Location</span>
                                <span class="info-value"><?php echo e($job->getFullAddress() ?: $job->location); ?></span>
                            </div>
                        </div>
                        <div class="apply-info-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <span class="info-label">Posted Date</span>
                                <span class="info-value"><?php echo e(\Carbon\Carbon::parse($job->created_at)->format('M d, Y')); ?></span>
                            </div>
                        </div>
                        <div class="apply-info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="info-label">Employment Type</span>
                                <span class="info-value"><?php echo e($job->jobType->name); ?></span>
                            </div>
                        </div>

                        <div class="divider-line"></div>

                        <?php if(auth()->guard()->check()): ?>
                            <?php if(Auth::user()->role === 'jobseeker'): ?>
                                <?php if(Auth::user()->isKycVerified()): ?>
                                    <a href="<?php echo e(route('job.application.start', $job->id)); ?>" class="btn-apply-sidebar">
                                        <i class="fas fa-paper-plane"></i>
                                        Apply for this Job
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn-apply-sidebar" data-bs-toggle="modal" data-bs-target="#kycRequiredModal">
                                        <i class="fas fa-lock"></i>
                                        Complete KYC to Apply
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="btn-apply-sidebar">
                                <i class="fas fa-sign-in-alt"></i>
                                Login to Apply
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Company Details -->
            <div class="modern-card">
                <div class="card-header-modern">
                    <h2 class="section-title-modern">
                        <i class="fas fa-building"></i> Company Info
                    </h2>
                </div>
                <div class="card-body-modern">
                    <?php
                        // Get actual company name (not employer's personal name)
                        $sidebarCompanyName = null;

                        // First check if linked to a company via company_id
                        if ($job->company_id && $job->company && !empty($job->company->name)) {
                            $sidebarCompanyName = $job->company->name;
                        }
                        // For admin-posted jobs, use company_name from job table (if not 'Confidential')
                        elseif (!empty($job->company_name) && $job->company_name !== 'Confidential') {
                            $sidebarCompanyName = $job->company_name;
                        }
                        // Try to get company name from employerProfileDirect
                        elseif ($job->employer && $job->employer->employerProfileDirect && !empty($job->employer->employerProfileDirect->company_name)) {
                            $sidebarCompanyName = $job->employer->employerProfileDirect->company_name;
                        }
                        // Fallback to employer table company_name if available
                        elseif ($job->employer && !empty($job->employer->company_name)) {
                            $sidebarCompanyName = $job->employer->company_name;
                        }
                        else {
                            $sidebarCompanyName = 'Company Name Not Available';
                        }

                        $industry = null;
                        $companySize = null;
                        $companyLocation = null;

                        if ($job->employer && $job->employer->employerProfileDirect) {
                            $industry = $job->employer->employerProfileDirect->industry;
                            $companySize = $job->employer->employerProfileDirect->company_size;
                            $companyLocation = $job->employer->employerProfileDirect->location;
                        } elseif ($job->employer) {
                            $industry = $job->employer->industry ?? null;
                            $companySize = $job->employer->company_size ?? null;
                            $companyLocation = $job->employer->city ?? null;
                        }
                    ?>

                    <div class="company-details-list">
                        <div class="company-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Company</span>
                                <span class="detail-value"><?php echo e($sidebarCompanyName); ?></span>
                            </div>
                        </div>

                        <?php if(!empty($industry)): ?>
                            <div class="company-detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-industry"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Industry</span>
                                    <span class="detail-value"><?php echo e($industry); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($companySize)): ?>
                            <div class="company-detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Company Size</span>
                                    <span class="detail-value"><?php echo e($companySize); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($companyLocation)): ?>
                            <div class="company-detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-map-pin"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Location</span>
                                    <span class="detail-value"><?php echo e($companyLocation); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($job->employer->employerProfileDirect->founded_year)): ?>
                            <div class="company-detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Founded</span>
                                    <span class="detail-value"><?php echo e($job->employer->employerProfileDirect->founded_year); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($job->employer->employerProfileDirect->website)): ?>
                            <div class="company-detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Website</span>
                                    <a href="<?php echo e($job->employer->employerProfileDirect->website); ?>" target="_blank" class="website-link-modern">
                                        Visit Website <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if(!empty($job->employer->employerProfileDirect->company_description)): ?>
                    <div class="company-description mt-4">
                        <h4>About the Company</h4>
                        <div class="content-text">
                            <?php echo nl2br($job->employer->employerProfileDirect->company_description); ?>

                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($job->employer->employerProfileDirect->benefits_offered)): ?>
                    <div class="company-benefits mt-4">
                        <h4>Benefits Offered</h4>
                        <div class="benefits-list">
                            <?php $__currentLoopData = $job->employer->employerProfileDirect->benefits_offered; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $benefit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="benefit-tag">
                                    <i class="fas fa-check-circle me-2"></i><?php echo e($benefit); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($job->employer->employerProfileDirect->company_culture)): ?>
                    <div class="company-culture mt-4">
                        <h4>Company Culture</h4>
                        <div class="culture-list">
                            <?php $__currentLoopData = $job->employer->employerProfileDirect->company_culture; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $culture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="culture-tag">
                                    <i class="fas fa-star me-2"></i><?php echo e($culture); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($job->employer->employerProfileDirect->specialties)): ?>
                    <div class="company-specialties mt-4">
                        <h4>Specialties</h4>
                        <div class="specialties-list">
                            <?php $__currentLoopData = $job->employer->employerProfileDirect->specialties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="specialty-tag"><?php echo e($specialty); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Application Modal -->
<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationModalLabel">Apply for <?php echo e($job->title); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="applicationForm" action="<?php echo e(route('jobs.apply', $job->id)); ?>" method="POST" enctype="multipart/form-data" onsubmit="submitApplication(event)">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="job_id" value="<?php echo e($job->id); ?>">
                    <div class="mb-3">
                        <label for="coverLetter" class="form-label">Cover Letter (Optional)</label>
                        <textarea class="form-control" id="coverLetter" name="cover_letter" rows="5" placeholder="Tell us why you're a great fit for this position..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="resume" class="form-label">Resume (PDF, DOC, DOCX)</label>
                        <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                        <div class="form-text">Maximum file size: 5MB</div>
                        <div id="resumeError" class="invalid-feedback" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitApplication">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Write a Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Important Notice -->
                <div class="alert alert-warning border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border-left: 4px solid #ffc107 !important;">
                    <div class="d-flex align-items-start">
                        <div class="me-3" style="font-size: 1.5rem;">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-2" style="color: #856404; font-weight: 700;">
                                <i class="fas fa-info-circle me-1"></i>Important Notice
                            </h6>
                            <p class="mb-0" style="color: #856404; font-size: 0.95rem; line-height: 1.6;">
                                <strong>You can only submit ONE review per job and ONE review per company.</strong><br>
                                Once submitted, your review cannot be edited or deleted. Please make sure your review is accurate and constructive.
                            </p>
                        </div>
                    </div>
                </div>

                <form id="reviewForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="job_id" value="<?php echo e($job->id); ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Review Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="review_type" id="reviewTypeJob" value="job" 
                                   <?php echo e(!$canReviewJob ? 'disabled' : 'checked'); ?>>
                            <label class="btn btn-outline-primary <?php echo e(!$canReviewJob ? 'disabled' : ''); ?>" for="reviewTypeJob">
                                <i class="fas fa-briefcase me-2"></i>Job Review
                                <?php if(!$canReviewJob): ?>
                                    <span class="badge bg-secondary ms-2">Already Reviewed</span>
                                <?php endif; ?>
                            </label>
                            
                            <input type="radio" class="btn-check" name="review_type" id="reviewTypeCompany" value="company"
                                   <?php echo e(!$canReviewCompany ? 'disabled' : ($canReviewJob ? '' : 'checked')); ?>>
                            <label class="btn btn-outline-primary <?php echo e(!$canReviewCompany ? 'disabled' : ''); ?>" for="reviewTypeCompany">
                                <i class="fas fa-building me-2"></i>Company Review
                                <?php if(!$canReviewCompany): ?>
                                    <span class="badge bg-secondary ms-2">Already Reviewed</span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <small class="form-text text-muted">Choose whether to review this specific job or the company overall</small>
                        <?php if(!$canReviewJob && !$canReviewCompany): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                You have already submitted reviews for both this job and company. Thank you for your feedback!
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Rating <span class="text-danger">*</span></label>
                        <div class="star-rating-input">
                            <input type="radio" name="rating" value="5" id="star5" required>
                            <label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                        <div id="ratingError" class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="reviewTitle" class="form-label fw-bold">Review Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reviewTitle" name="title" placeholder="Summarize your experience" required maxlength="200">
                        <div id="titleError" class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="reviewComment" class="form-label fw-bold">Your Review <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reviewComment" name="comment" rows="5" placeholder="Share your experience with this job/company..." required minlength="10" maxlength="1000"></textarea>
                        <small class="form-text text-muted">Minimum 10 characters, maximum 1000 characters</small>
                        <div id="commentError" class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_anonymous" id="isAnonymous" value="1">
                            <label class="form-check-label" for="isAnonymous">
                                Post anonymously (your name will be hidden from public, but visible to employer)
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Reviews are published immediately and visible to everyone. Please be honest and constructive.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitReview" onclick="submitReviewForm()">
                    <i class="fas fa-paper-plane me-2"></i>Submit Review
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global function for review submission
function submitReviewForm() {
    console.log('submitReviewForm called');
    
    const btn = document.getElementById('submitReview');
    const form = document.getElementById('reviewForm');
    
    if (!form) {
        console.error('Form not found');
        alert('Error: Form not found');
        return;
    }
    
    const formData = new FormData(form);
    
    // Log form data
    console.log('Form data:', {
        job_id: formData.get('job_id'),
        review_type: formData.get('review_type'),
        rating: formData.get('rating'),
        title: formData.get('title'),
        comment: formData.get('comment')
    });
    
    // Validate rating
    if (!formData.get('rating')) {
        alert('Please select a rating');
        return;
    }
    
    // Show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    
    // Send AJAX request
    fetch('<?php echo e(route("account.reviews.store")); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        // Check if response is OK (status 200-299)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        console.log('Data status type:', typeof data.status);
        console.log('Data status value:', data.status);
        
        // Check for success - handle both boolean true and string 'true'
        if (data.status === true || data.status === 1 || data.status === '1' || data.status === 'true') {
            // Success - show message and reload
            console.log('Success! Reloading page...');
            
            // Close modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
            if (modal) {
                modal.hide();
            }
            
            // Show success message
            if (typeof showToast === 'function') {
                showToast('Review submitted successfully!', 'success');
            }
            
            // Reload after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Error from server
            console.log('Server returned error:', data.message);
            const errorMsg = data.message || 'Error submitting review';
            
            if (typeof showToast === 'function') {
                showToast(errorMsg, 'error');
            } else {
                alert(errorMsg);
            }
            
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Review';
        }
    })
    .catch(error => {
        console.error('Catch error:', error);
        console.error('Error message:', error.message);
        
        // Don't show error if it's just a navigation error (page is reloading)
        if (error.message && error.message.includes('Failed to fetch')) {
            console.log('Fetch failed - likely because page is reloading');
            return;
        }
        
        if (typeof showToast === 'function') {
            showToast('Error submitting review. Please try again.', 'error');
        } else {
            alert('Error submitting review. Please try again.');
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Review';
    });
}

// Delete review function
function deleteReview(reviewId) {
    if (!confirm('Are you sure you want to delete this review?')) {
        return;
    }
    
    console.log('Deleting review:', reviewId);
    
    fetch(`/account/reviews/${reviewId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Delete response:', data);
        if (data.status) {
            if (typeof showToast === 'function') {
                showToast('Review deleted successfully', 'success');
            } else {
                alert('Review deleted successfully');
            }
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Error deleting review', 'error');
            } else {
                alert(data.message || 'Error deleting review');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('Error deleting review. Please try again.', 'error');
        } else {
            alert('Error deleting review. Please try again.');
        }
    });
}

// Edit review function (placeholder - will open modal with existing data)
function editReview(reviewId) {
    alert('Edit functionality coming soon! For now, you can delete and create a new review.');
}
</script>

<style>
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --bg-light: #f9fafb;
    --bg-dark: #111827;
}

/* Save Button - Red Style */
.job-actions-modern .btn-save {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    border: none !important;
    color: #fff !important;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.job-actions-modern .btn-save:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    color: #fff !important;
}

.job-actions-modern .btn-save.saved {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.job-actions-modern .btn-save.saved:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.job-actions-modern .btn-save i {
    font-size: 1rem;
}

/* Quick Job Summary Section */
.job-quick-summary {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.summary-item:hover {
    background: #f3f4f6;
    transform: translateY(-2px);
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.summary-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.summary-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    font-size: 0.9375rem;
    color: #111827;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.job-detail-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    padding: 3rem 0;
    margin-top: -2rem;
    color: #fff;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.2);
}

.back-link {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 0.95rem;
}

.back-link:hover {
    color: #fff;
}

.company-badge {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
}

.job-title {
    font-size: 2rem;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.company-name {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
}

/* Job Action Buttons Container */
.job-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .job-action-buttons {
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Enhanced Button Styles */
.btn-job-save, .btn-job-apply {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-width: 130px;
    justify-content: center;
    text-transform: none;
    letter-spacing: 0.025em;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Save Job Button */
.btn-job-save {
    background: rgba(255, 255, 255, 0.95);
    color: #374151;
    border-color: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.btn-job-save:hover {
    background: rgba(255, 255, 255, 1);
    color: #dc2626;
    border-color: rgba(220, 38, 38, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2);
}

.btn-job-save.saved {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
    border-color: rgba(220, 38, 38, 0.3);
}

.btn-job-save.saved:hover {
    background: rgba(220, 38, 38, 0.15);
    color: #b91c1c;
}

.btn-job-save i {
    font-size: 1.1em;
    transition: all 0.3s ease;
}

.btn-job-save:hover i {
    transform: scale(1.1);
    color: #dc2626;
}

.btn-job-save.saved i {
    color: #dc2626;
}

/* Apply Now Button */
.btn-job-apply {
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    color: var(--primary-color);
    border-color: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    position: relative;
}

.btn-job-apply::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s ease;
}

.btn-job-apply:hover::before {
    left: 100%;
}

.btn-job-apply:hover {
    background: #fff;
    color: var(--secondary-color);
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
}

.btn-job-apply i {
    transition: transform 0.3s ease;
}

.btn-job-apply:hover i {
    transform: translateX(2px);
}

/* Button Text */
.btn-text {
    font-weight: 600;
    position: relative;
    z-index: 1;
}

/* Loading State */
.btn-job-save:disabled,
.btn-job-apply:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Responsive Design */
@media (max-width: 576px) {
    .btn-job-save, .btn-job-apply {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
        min-width: 120px;
    }
    
    .job-action-buttons {
        width: 100%;
    }
    
    .btn-job-save, .btn-job-apply {
        flex: 1;
        max-width: 180px;
    }
}

/* Focus States for Accessibility */
.btn-job-save:focus,
.btn-job-apply:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Animation for save state change */
.btn-job-save .btn-text {
    transition: all 0.3s ease;
}

.btn-job-save.saved .btn-text {
    color: #dc2626;
}

.content-card {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.job-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: var(--bg-light);
    border-radius: 6px;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.tag i {
    color: var(--primary-color);
}

.content-section {
    margin-bottom: 2rem;
}

.content-section:last-child {
    margin-bottom: 0;
}

.content-section h3 {
    color: var(--text-dark);
    font-size: 1.25rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.content-text {
    color: var(--text-light);
    line-height: 1.6;
    white-space: pre-line;
}

.summary-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--bg-light);
}

.summary-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.summary-item .label {
    color: var(--text-light);
    font-size: 0.95rem;
}

.summary-item .value {
    color: var(--text-dark);
    font-weight: 500;
}

.website-link {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.website-link:hover {
    color: var(--secondary-color);
}

.applicants-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.applicant-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: 8px;
}

.applicant-info h4 {
    margin-bottom: 0.5rem;
    color: var(--text-dark);
    font-size: 1.1rem;
}

.applicant-info p {
    color: var(--text-light);
    font-size: 0.9rem;
}

.applicant-date {
    color: var(--primary-color);
    font-weight: 500;
    font-size: 0.9rem;
}

.no-applicants {
    text-align: center;
    padding: 2rem;
}

.no-applicants i {
    font-size: 3rem;
    color: var(--primary-color);
    display: block;
    margin-bottom: 1rem;
}

.no-applicants p {
    color: var(--text-light);
    margin: 0;
}

/* Modal Styles */
.modal-content {
    border-radius: 12px;
    border: none;
}

.modal-header {
    border-bottom: 1px solid var(--bg-light);
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid var(--bg-light);
    padding: 1.5rem;
}

.form-label {
    color: var(--text-dark);
    font-weight: 500;
}

.form-control {
    border: 1px solid var(--bg-light);
    border-radius: 8px;
    padding: 0.75rem;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

.form-text {
    color: var(--text-light);
    font-size: 0.85rem;
}

.btn-secondary {
    background-color: var(--bg-light);
    border: none;
    color: var(--text-dark);
}

.btn-secondary:hover {
    background-color: #e5e7eb;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
}

.btn-primary:hover {
    background-color: var(--secondary-color);
}

.company-description h4,
.company-benefits h4,
.company-culture h4,
.company-specialties h4 {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.benefits-list,
.culture-list,
.specialties-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.benefit-tag,
.culture-tag,
.specialty-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background-color: var(--bg-light);
    border-radius: 6px;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.benefit-tag i {
    color: #10b981;
}

.culture-tag i {
    color: #f59e0b;
}

.specialty-tag {
    background-color: rgba(99, 102, 241, 0.1);
    color: var(--primary-color);
}

.company-description {
    padding-top: 1.5rem;
    border-top: 1px solid var(--bg-light);
}

.company-benefits,
.company-culture,
.company-specialties {
    padding-top: 1.5rem;
    border-top: 1px solid var(--bg-light);
}

.website-link {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.website-link:hover {
    color: var(--secondary-color);
}

.website-link i {
    font-size: 0.85rem;
}

/* Review System Styles */
.nav-tabs {
    border-bottom: 2px solid #e5e7eb;
}

.nav-tabs .nav-link {
    border: none;
    color: #6b7280;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: var(--primary-color);
    border-bottom-color: rgba(99, 102, 241, 0.3);
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
    background: none;
}

.no-reviews {
    padding: 3rem 1rem;
}

/* Star Rating Input */
.star-rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.5rem;
    font-size: 2rem;
}

.star-rating-input input[type="radio"] {
    display: none;
}

.star-rating-input label {
    cursor: pointer;
    color: #d1d5db;
    transition: color 0.2s ease;
}

.star-rating-input label:hover,
.star-rating-input label:hover ~ label,
.star-rating-input input[type="radio"]:checked ~ label {
    color: #fbbf24;
}

.star-rating-input label:hover {
    transform: scale(1.1);
}

/* Fix text overflow - wrap long text properly */
.content-text {
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    white-space: pre-wrap;
    max-width: 100%;
}

.content-section {
    overflow: hidden;
}

.content-section h3 {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Custom Review Button Color - Pink/Red */
button[data-bs-target="#reviewModal"],
#submitReview {
    background: linear-gradient(135deg, #CD2C58 0%, #b02449 100%) !important;
    border-color: #CD2C58 !important;
    color: white !important;
}

button[data-bs-target="#reviewModal"]:hover,
#submitReview:hover {
    background: linear-gradient(135deg, #b02449 0%, #9a1f3d 100%) !important;
    border-color: #b02449 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(205, 44, 88, 0.4) !important;
}

button[data-bs-target="#reviewModal"]:active,
#submitReview:active {
    transform: translateY(0);
}
</style>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link href="<?php echo e(asset('assets/css/job-detail-modern.css')); ?>?v=<?php echo e(time()); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- jQuery and Bootstrap are already loaded in the layout -->
<script type="text/javascript">
// ============================================
// Save Job Functionality - MUST be outside $(document).ready() for inline onclick to work
// ============================================
function toggleSaveJob(jobId) {
    console.log('toggleSaveJob called with jobId:', jobId);

    <?php if(!Auth::check()): ?>
        window.location.href = '<?php echo e(route("login")); ?>';
        return;
    <?php endif; ?>

    const btn = document.getElementById(`saveJobBtn-${jobId}`);
    const icon = document.getElementById(`saveJobIcon-${jobId}`);

    if (!btn || !icon) {
        console.error('Save button or icon not found for job:', jobId);
        return;
    }

    if (!btn.disabled) {
        btn.disabled = true;

        // Check if job is already saved (check for 'fas' class or 'saved' class on button)
        const isSaved = icon.classList.contains('fas') || btn.classList.contains('saved');
        const route = isSaved ? '<?php echo e(route("jobs.unsave", ":id")); ?>' : '<?php echo e(route("jobs.save", ":id")); ?>';
        const url = route.replace(':id', jobId);

        console.log('Sending AJAX request to:', url);

        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('AJAX success response:', response);
                if (response.status) {
                    if (isSaved) {
                        // Job was saved, now unsaved
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        btn.querySelector('span').textContent = 'Save';
                        btn.classList.remove('saved', 'ws-btn-success');
                    } else {
                        // Job was not saved, now saved
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        btn.querySelector('span').textContent = 'Saved';
                        btn.classList.add('saved', 'ws-btn-success');
                    }
                    // Show toast notification
                    if (typeof window.showToast === 'function') {
                        window.showToast(response.message || (isSaved ? 'Job removed from saved!' : 'Job saved!'), 'success');
                    }
                } else {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        if (typeof window.showToast === 'function') {
                            window.showToast(response.message || 'Error saving job', 'error');
                        } else {
                            alert(response.message || 'Error saving job');
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr.responseJSON);
                const response = xhr.responseJSON;
                if (response && response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(response?.message || 'Error saving job. Please try again.', 'error');
                    } else {
                        alert(response?.message || 'Error saving job. Please try again.');
                    }
                }
            },
            complete: function() {
                btn.disabled = false;
            }
        });
    }
}

$(document).ready(function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Set up AJAX CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Use unified toast notification system
    function showAlert(message, type = 'success') {
        // Map 'danger' to 'error' for toast system
        const toastType = type === 'danger' ? 'error' : type;
        
        // Use global toast function if available
        if (typeof showToast === 'function') {
            showToast(message, toastType);
        } else {
            // Fallback to alert if toast system not loaded
            alert(message);
        }
    }

    // File size validation function (5MB in bytes = 5 * 1024 * 1024)
    function validateFileSize(file, maxSize = 5 * 1024 * 1024) {
        if (file && file.size > maxSize) {
            return false;
        }
        return true;
    }

    // Handle resume file selection
    $('#resume').on('change', function() {
        const file = this.files[0];
        const resumeError = $('#resumeError');
        
        if (!file) {
            resumeError.text('Please select a file').show();
            return;
        }
        
        if (!validateFileSize(file)) {
            resumeError.text('File size must be less than 5MB').show();
            this.value = ''; // Clear the file input
        } else {
            resumeError.hide();
        }
    });

    // Handle form submission
    window.submitApplication = function(e) {
        e.preventDefault();
        
        const form = $('#applicationForm');
        const submitBtn = $('#submitApplication');
        const resumeFile = $('#resume')[0].files[0];
        const resumeError = $('#resumeError');

        // Validate required fields
        if (!resumeFile) {
            resumeError.text('Please select a resume file').show();
            return false;
        }

        // Check file size before submission
        if (!validateFileSize(resumeFile)) {
            resumeError.text('File size must be less than 5MB').show();
            return false;
        }

        const formData = new FormData(form[0]);
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...');
        
        // Make the AJAX request
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Response:', response); // Debug log
                
                if (response.status === true) {
                    // Show success message first
                    showAlert('Application submitted successfully! We will notify you of any updates.', 'success');
                    
                    // Reset form and errors
                    form[0].reset();
                    resumeError.hide();
                    
                    // Hide modal
                    $('#applicationModal').modal('hide');
                    
                    // Reload the page after a short delay to update the application status
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    if (response.errors) {
                        let errorMessage = '';
                        for (let key in response.errors) {
                            errorMessage += response.errors[key] + '\n';
                        }
                        showAlert(errorMessage, 'danger');
                    } else {
                        showAlert(response.message || 'Error submitting application. Please try again.', 'danger');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', xhr.responseText); // Debug log
                const response = xhr.responseJSON;
                
                let errorMessage = 'Error submitting application. ';
                if (response && response.errors) {
                    for (let key in response.errors) {
                        errorMessage += response.errors[key] + ' ';
                    }
                } else if (response && response.message) {
                    errorMessage += response.message;
                } else {
                    errorMessage += 'Please try again.';
                }
                
                showAlert(errorMessage, 'danger');
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).html('Submit Application');
            }
        });

        return false;
    };

    // Review System JavaScript
    $('#submitReview').on('click', function(e) {
        e.preventDefault();
        console.log('Submit Review button clicked');
        
        const btn = $(this);
        const form = $('#reviewForm');
        
        // Check if form exists
        if (form.length === 0) {
            console.error('Review form not found');
            alert('Error: Review form not found');
            return;
        }
        
        const formData = new FormData(form[0]);
        
        // Log form data for debugging
        console.log('Form data:', {
            job_id: formData.get('job_id'),
            review_type: formData.get('review_type'),
            rating: formData.get('rating'),
            title: formData.get('title'),
            comment: formData.get('comment')
        });
        
        // Clear previous errors
        $('.invalid-feedback').hide().text('');
        $('.form-control, .form-check-input').removeClass('is-invalid');
        
        // Validate rating
        if (!$('input[name="rating"]:checked').val()) {
            $('#ratingError').text('Please select a rating').show();
            console.log('Validation failed: No rating selected');
            return;
        }
        
        // Show loading state
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');
        
        console.log('Sending AJAX request to:', '<?php echo e(route("account.reviews.store")); ?>');
        
        $.ajax({
            url: '<?php echo e(route("account.reviews.store")); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Success response:', response);
                if (response.status) {
                    showAlert('Review submitted successfully!', 'success');
                    $('#reviewModal').modal('hide');
                    form[0].reset();
                    
                    // Reload page to show new review
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    console.log('Response status false:', response.message);
                    showAlert(response.message || 'Error submitting review', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                const response = xhr.responseJSON;
                if (response && response.errors) {
                    // Display validation errors
                    console.log('Validation errors:', response.errors);
                    for (let field in response.errors) {
                        const errorMsg = response.errors[field][0];
                        $(`#${field}Error`).text(errorMsg).show();
                        $(`[name="${field}"]`).addClass('is-invalid');
                    }
                } else if (response && response.message) {
                    showAlert(response.message, 'danger');
                } else {
                    showAlert('Error submitting review. Please try again.', 'danger');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Submit Review');
            }
        });
    });

    // Delete review
    $(document).on('click', '.delete-review-btn', function() {
        if (!confirm('Are you sure you want to delete this review?')) {
            return;
        }
        
        const reviewId = $(this).data('review-id');
        const btn = $(this);
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/account/reviews/${reviewId}`,
            type: 'DELETE',
            success: function(response) {
                if (response.status) {
                    showAlert('Review deleted successfully', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert(response.message || 'Error deleting review', 'danger');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                showAlert('Error deleting review. Please try again.', 'danger');
                btn.prop('disabled', false);
            }
        });
    });

    // Helpful button
    $(document).on('click', '.helpful-btn', function() {
        <?php if(auth()->guard()->guest()): ?>
            window.location.href = '<?php echo e(route("login")); ?>';
            return;
        <?php endif; ?>

        const reviewId = $(this).data('review-id');
        showAlert('Helpful feature coming soon!', 'info');
    });

    // Show toast for session flash messages (e.g., after job application)
    <?php if(session('success')): ?>
        if (typeof window.showToast === 'function') {
            window.showToast(<?php echo json_encode(session('success')); ?>, 'success');
        }
    <?php endif; ?>

    <?php if(session('error')): ?>
        if (typeof window.showToast === 'function') {
            window.showToast(<?php echo json_encode(session('error')); ?>, 'error');
        }
    <?php endif; ?>

    <?php if(session('info')): ?>
        if (typeof window.showToast === 'function') {
            window.showToast(<?php echo json_encode(session('info')); ?>, 'info');
        }
    <?php endif; ?>
});
</script>
<?php $__env->stopPush(); ?>

<!-- KYC Required Modal -->
<div class="modal fade" id="kycRequiredModal" tabindex="-1" aria-labelledby="kycRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px 20px 0 0; padding: 2rem;">
                <h5 class="modal-title text-white" id="kycRequiredModalLabel">
                    <i class="fas fa-shield-alt me-2"></i>Identity Verification Required
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div class="text-center mb-4">
                    <div class="kyc-icon mb-3" style="font-size: 4rem; color: #fbbf24;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h4 class="mb-3" style="color: #1f2937; font-weight: 700;">Complete KYC to Apply</h4>
                    <p class="text-muted mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                        To ensure the safety and trust of our job platform, all jobseekers must complete identity verification before applying for jobs.
                    </p>
                </div>

                <div class="kyc-benefits mb-4">
                    <h6 class="mb-3" style="color: #374151; font-weight: 600;">
                        <i class="fas fa-check-circle text-success me-2"></i>Benefits of Verification:
                    </h6>
                    <ul class="list-unstyled ms-3">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <span style="color: #6b7280;">Apply for unlimited job positions</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <span style="color: #6b7280;">Build trust with employers</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <span style="color: #6b7280;">Get priority in application reviews</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <span style="color: #6b7280;">Quick verification (2-5 minutes)</span>
                        </li>
                    </ul>
                </div>

                <div class="alert alert-info" style="border-radius: 12px; border-left: 4px solid #3b82f6; background-color: #eff6ff;">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>Your information is secure and encrypted. We use industry-standard verification processes.</small>
                </div>
            </div>
            <div class="modal-footer border-0" style="padding: 0 2rem 2rem 2rem;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 10px; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <a href="<?php echo e(route('kyc.index')); ?>" class="btn text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; padding: 0.75rem 1.5rem; font-weight: 600;">
                    <i class="fas fa-shield-check me-2"></i>Start Verification
                </a>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('layouts.jobseeker', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/modern-job-detail.blade.php ENDPATH**/ ?>