

<?php $__env->startSection('page-title', 'Companies'); ?>

<?php $__env->startSection('jobseeker-content'); ?>
<div class="companies-page">
    <!-- Header Section -->
    <div class="page-header mb-4 ws-card">
        <div class="d-flex justify-content-between align-items-center ws-flex ws-justify-between ws-items-center">
            <div>
                <h2 class="mb-2 ws-text-2xl ws-font-bold">
                    <i class="bi bi-building text-primary me-2"></i>
                    Explore Companies
                </h2>
                <p class="text-muted mb-0 ws-text-secondary">Discover great places to work and find your dream job</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary fs-6 px-3 py-2 ws-badge ws-badge-primary">
                    <?php echo e($companies->total()); ?> <?php echo e(Str::plural('Company', $companies->total())); ?>

                </span>
            </div>
        </div>
    </div>

    <!-- Companies Grid -->
    <?php if($companies->count() > 0): ?>
        <div class="row g-4">
            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isNew = $company->created_at >= now()->subDays(7);
                    $jobsCount = is_countable($company->jobs) ? count($company->jobs) : 0;
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="company-card h-100 ws-company-card ws-card">
                        <!-- New Badge -->
                        <?php if($isNew): ?>
                            <div class="new-badge ws-badge ws-badge-success">
                                <i class="bi bi-star-fill me-1"></i> NEW
                            </div>
                        <?php endif; ?>

                        <div class="company-card-body">
                            <!-- Company Logo & Info -->
                            <div class="company-header mb-3 ws-flex ws-items-start ws-gap-3">
                                <div class="company-logo-wrapper ws-company-logo">
                                    <?php if($company->logo_url): ?>
                                        <img src="<?php echo e(asset($company->logo_url)); ?>"
                                             alt="<?php echo e($company->company_name); ?>"
                                             class="company-logo">
                                    <?php else: ?>
                                        <div class="company-logo-placeholder">
                                            <i class="bi bi-building"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="company-info">
                                    <h5 class="company-name ws-text-lg ws-font-semibold">
                                        <a href="<?php echo e(route('companies.show', $company->id)); ?>">
                                            <?php echo e($company->company_name); ?>

                                        </a>
                                    </h5>
                                    <p class="company-location ws-text-sm ws-text-secondary">
                                        <i class="bi bi-geo-alt"></i>
                                        <?php echo e($company->location ?? 'Sta. Cruz, Davao del Sur'); ?>

                                    </p>
                                </div>
                            </div>

                            <!-- Company Description -->
                            <p class="company-description ws-text-secondary ws-text-sm">
                                <?php echo e(Str::limit($company->company_description ?? 'This company is now hiring! Check out their profile to learn more about their culture and available positions.', 120)); ?>

                            </p>

                            <!-- Company Stats -->
                            <div class="company-stats mb-3 ws-flex ws-gap-3 ws-flex-wrap">
                                <div class="stat-item ws-badge ws-badge-info">
                                    <i class="bi bi-briefcase text-primary"></i>
                                    <span><?php echo e($jobsCount); ?> <?php echo e(Str::plural('Job', $jobsCount)); ?></span>
                                </div>
                                <?php if($company->created_at): ?>
                                    <div class="stat-item ws-badge ws-badge-secondary">
                                        <i class="bi bi-calendar-check text-success"></i>
                                        <span>Joined <?php echo e($company->created_at->diffForHumans()); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Action Button -->
                            <a href="<?php echo e(route('companies.show', $company->id)); ?>"
                               class="btn btn-primary w-100 view-company-btn ws-btn ws-btn-primary">
                                <i class="bi bi-eye me-2"></i>
                                View Company Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Pagination -->
        <?php if($companies->hasPages()): ?>
            <div class="d-flex justify-content-center mt-5">
                <?php echo e($companies->links()); ?>

            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state ws-card">
            <div class="empty-icon">
                <i class="bi bi-building"></i>
            </div>
            <h4 class="ws-text-xl ws-font-semibold">No Companies Yet</h4>
            <p class="text-muted ws-text-secondary">Check back soon for new companies joining our platform!</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Companies Page Styles */
.companies-page {
    padding: 2rem;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 16px;
    color: white;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.page-header h2 {
    color: white;
    font-weight: 700;
}

.page-header .text-muted {
    color: rgba(255, 255, 255, 0.9) !important;
}

/* Company Card */
.company-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.company-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
}

.new-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.company-card-body {
    padding: 1.75rem;
}

/* Company Header */
.company-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.company-logo-wrapper {
    flex-shrink: 0;
}

.company-logo {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #f3f4f6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.company-logo-placeholder {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e5e7eb;
}

.company-logo-placeholder i {
    font-size: 2rem;
    color: #9ca3af;
}

.company-info {
    flex: 1;
    min-width: 0;
}

.company-name {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.375rem;
    line-height: 1.3;
}

.company-name a {
    color: #1f2937;
    text-decoration: none;
    transition: color 0.2s ease;
}

.company-name a:hover {
    color: #667eea;
}

.company-location {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.company-location i {
    color: #9ca3af;
}

/* Company Description */
.company-description {
    font-size: 0.9375rem;
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 1rem;
}

/* Company Stats */
.company-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 10px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #4b5563;
    font-weight: 500;
}

.stat-item i {
    font-size: 1rem;
}

/* View Button */
.view-company-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.view-company-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.empty-icon {
    font-size: 5rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-state h4 {
    color: #1f2937;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
    .companies-page {
        padding: 1rem;
    }

    .page-header {
        padding: 1.5rem;
    }

    .page-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .page-header .text-end {
        text-align: left !important;
    }

    .company-card-body {
        padding: 1.25rem;
    }

    .company-logo,
    .company-logo-placeholder {
        width: 60px;
        height: 60px;
    }
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.jobseeker', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/companies/index.blade.php ENDPATH**/ ?>