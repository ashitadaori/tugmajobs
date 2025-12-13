<?php $__env->startSection('page-title', 'Find Jobs'); ?>

<?php $__env->startSection('jobseeker-content'); ?>
<div class="jobs-page-container">
    <!-- Enhanced Page Header -->
    <header class="jobs-header">
        <!-- Decorative Elements -->
        <div class="header-decoration">
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            <div class="decoration-circle circle-3"></div>
            <div class="decoration-dots"></div>
        </div>

        <div class="jobs-header-inner">
            <div class="jobs-header-left">
                <div class="header-icon-wrapper">
                    <i class="fas fa-search"></i>
                </div>
                <div class="header-text">
                    <h1 class="jobs-title">Find Your Dream Job</h1>
                    <p class="jobs-subtitle">
                        <i class="fas fa-sparkles"></i>
                        Discover <strong><?php echo e($jobs->total()); ?></strong> opportunities matching your career goals
                    </p>
                </div>
            </div>
            <div class="jobs-header-right">
                <div class="jobs-stat-badge">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo e($jobs->total()); ?></span>
                        <span class="stat-label">Jobs Available</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="jobs-layout-grid">
        <!-- Sidebar Filters -->
        <aside class="jobs-sidebar">
            <div class="filter-panel">
                <div class="filter-panel-header">
                    <div class="filter-title">
                        <i class="fas fa-sliders-h"></i>
                        <span>Filters</span>
                    </div>
                    <a href="<?php echo e(route('jobs')); ?>" class="filter-clear-btn">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                </div>

                <form action="" method="GET" id="searchForm" class="filter-form">
                    <!-- Keyword Search -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-search"></i>
                            Keywords
                        </label>
                        <div class="filter-input-wrapper">
                            <input type="text"
                                   value="<?php echo e(Request::get('keyword')); ?>"
                                   name="keyword"
                                   id="keyword"
                                   placeholder="Job title, skills, company..."
                                   class="filter-input">
                        </div>
                    </div>

                    <!-- Location Search -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Location
                        </label>
                        <div class="filter-input-wrapper">
                            <input type="text"
                                   value="<?php echo e(Request::get('location')); ?>"
                                   name="location"
                                   id="location"
                                   placeholder="City or province..."
                                   class="filter-input">
                        </div>

                        <div class="radius-control">
                            <span class="radius-text">Within</span>
                            <input type="number"
                                   value="<?php echo e(Request::get('radius', 10)); ?>"
                                   name="radius"
                                   id="radius"
                                   class="radius-input"
                                   min="1"
                                   max="100">
                            <span class="radius-text">km</span>
                        </div>

                        <!-- Hidden fields for geolocation -->
                        <input type="hidden" name="location_filter_latitude" id="location_filter_latitude" value="<?php echo e(Request::get('location_filter_latitude')); ?>">
                        <input type="hidden" name="location_filter_longitude" id="location_filter_longitude" value="<?php echo e(Request::get('location_filter_longitude')); ?>">

                        <button type="button" class="location-detect-btn" onclick="useCurrentLocation()">
                            <i class="fas fa-crosshairs"></i>
                            Use My Location
                        </button>
                    </div>

                    <!-- Job Type -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-briefcase"></i>
                            Job Type
                        </label>
                        <div class="filter-select-wrapper">
                            <select name="jobType" id="jobType" class="filter-select">
                                <option value="">All Types</option>
                                <?php $__currentLoopData = $jobTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($jobType->name); ?>" <?php echo e(Request::get('jobType') == $jobType->name ? 'selected' : ''); ?>>
                                        <?php echo e($jobType->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <button type="submit" class="filter-submit-btn">
                        <i class="fas fa-search"></i>
                        Search Jobs
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="jobs-main">
            <!-- Mobile Filter Toggle -->
            <button type="button" class="mobile-filters-btn" id="mobileFilterToggle">
                <i class="fas fa-filter"></i>
                <span>Filters</span>
                <?php if(Request::get('keyword') || Request::get('location') || Request::get('jobType')): ?>
                    <span class="filter-count"><?php echo e((Request::get('keyword') ? 1 : 0) + (Request::get('location') ? 1 : 0) + (Request::get('jobType') ? 1 : 0)); ?></span>
                <?php endif; ?>
            </button>

            <!-- Smart Matching Status -->
            <?php if($userHasPreferences): ?>
                <div class="smart-match-banner active">
                    <div class="smart-match-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="smart-match-content">
                        <strong>Smart Matching Active</strong>
                        <span>Personalized results based on <?php echo e(count($userCategories)); ?> <?php echo e(Str::plural('preference', count($userCategories))); ?></span>
                    </div>
                    <a href="<?php echo e(route('account.myProfile')); ?>" class="smart-match-settings" aria-label="Settings">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            <?php elseif($categoryPrompt): ?>
                <div class="smart-match-banner inactive">
                    <div class="smart-match-icon">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div class="smart-match-content">
                        <strong>Enable Smart Matching</strong>
                        <span>Set preferences for personalized job recommendations</span>
                    </div>
                    <a href="<?php echo e(route('account.myProfile')); ?>" class="smart-match-activate">
                        Activate
                    </a>
                </div>
            <?php endif; ?>

            <!-- Recommended Jobs Section -->
            <?php if($recommendedJobs->isNotEmpty()): ?>
                <section class="recommended-jobs-section">
                    <div class="section-header">
                        <div class="section-title-group">
                            <i class="fas fa-star"></i>
                            <h2>Top Matches for You</h2>
                        </div>
                        <span class="powered-badge">AI Powered</span>
                    </div>
                    <div class="recommended-jobs-grid">
                        <?php $__currentLoopData = $recommendedJobs->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recJob): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="recommended-job-card">
                                <div class="match-score">
                                    <?php echo e(95 - ($loop->index * 5)); ?>% Match
                                </div>
                                <div class="recommended-job-content">
                                    <div class="company-initial">
                                        <?php echo e(substr($recJob->employer->employerProfileDirect->company_name ?? $recJob->employer->company_name ?? $recJob->employer->name ?? 'C', 0, 1)); ?>

                                    </div>
                                    <h3 class="recommended-job-title"><?php echo e($recJob->title); ?></h3>
                                    <p class="recommended-company">
                                        <?php echo e($recJob->employer->employerProfileDirect->company_name ?? $recJob->employer->company_name ?? $recJob->employer->name ?? 'Company'); ?>

                                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.verified-badge','data' => ['user' => $recJob->employer,'size' => 'xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('verified-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recJob->employer),'size' => 'xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                                    </p>
                                    <div class="recommended-tags">
                                        <span class="tag-type"><?php echo e($recJob->jobType->name); ?></span>
                                        <span class="tag-category"><?php echo e($recJob->category->name); ?></span>
                                    </div>
                                </div>
                                <a href="<?php echo e(route('jobDetail', $recJob->id)); ?>" class="recommended-job-link">
                                    View Details
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-info">
                    <span class="results-count">
                        <strong><?php echo e($jobs->total()); ?></strong> jobs found
                    </span>
                    <?php if(Request::get('keyword') || Request::get('location') || Request::get('jobType')): ?>
                        <div class="active-filters">
                            <?php if(Request::get('keyword')): ?>
                                <span class="active-filter-tag">
                                    <i class="fas fa-search"></i>
                                    <?php echo e(Request::get('keyword')); ?>

                                </span>
                            <?php endif; ?>
                            <?php if(Request::get('location')): ?>
                                <span class="active-filter-tag">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo e(Request::get('location')); ?>

                                </span>
                            <?php endif; ?>
                            <?php if(Request::get('jobType')): ?>
                                <span class="active-filter-tag">
                                    <i class="fas fa-briefcase"></i>
                                    <?php echo e(Request::get('jobType')); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="results-sort">
                    <label for="sort">Sort:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()" form="searchForm">
                        <option value="1" <?php echo e((Request::get('sort') != '0') ? 'selected' : ''); ?>>Newest</option>
                        <option value="0" <?php echo e((Request::get('sort') == '0') ? 'selected' : ''); ?>>Oldest</option>
                    </select>
                </div>
            </div>

            <!-- Job Listings - Grid Style -->
            <?php if($jobs->isNotEmpty()): ?>
                <div class="job-grid">
                    <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="job-card-grid">
                            <!-- Card Header -->
                            <div class="job-card-header">
                                <div class="job-card-top">
                                    <div class="job-company-logo">
                                        <?php echo e(substr($job->employer->employerProfileDirect->company_name ?? $job->employer->company_name ?? $job->employer->name ?? 'C', 0, 1)); ?>

                                    </div>
                                    <div class="job-header-info">
                                        <h3 class="job-title">
                                            <a href="<?php echo e(route('jobDetail', $job->id)); ?>"><?php echo e($job->title); ?></a>
                                        </h3>
                                        <p class="job-company">
                                            <?php echo e($job->employer->employerProfileDirect->company_name ?? $job->employer->company_name ?? $job->employer->name ?? 'Company'); ?>

                                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.verified-badge','data' => ['user' => $job->employer,'size' => 'xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('verified-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($job->employer),'size' => 'xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                                        </p>
                                    </div>
                                    <button class="job-menu-btn" aria-label="Options">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </div>
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo e($job->location); ?>

                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="job-card-body">
                                <div class="job-info-row">
                                    <span class="job-info-item">
                                        <span class="info-label"><?php echo e($job->experience_level ?? 'Entry Level'); ?></span>
                                    </span>
                                    <span class="job-info-item">
                                        <span class="info-label"><?php echo e($job->jobType->name); ?></span>
                                    </span>
                                    <?php if($job->salary_range): ?>
                                        <span class="job-info-item salary">
                                            <span class="info-label"><?php echo e($job->salary_range); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="job-description"><?php echo e(Str::words(strip_tags($job->description), 20, '...')); ?></p>
                            </div>

                            <!-- Card Tags -->
                            <div class="job-card-tags">
                                <span class="job-tag primary"><?php echo e($job->jobType->name); ?></span>
                                <span class="job-tag"><?php echo e(Str::limit($job->category->name, 18)); ?></span>
                                <?php if($job->skills): ?>
                                    <?php $__currentLoopData = array_slice(explode(',', $job->skills), 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="job-tag"><?php echo e(trim($skill)); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>

                            <!-- Card Footer -->
                            <div class="job-card-footer">
                                <span class="job-posted">
                                    <i class="far fa-clock"></i>
                                    <?php echo e($job->created_at->diffForHumans()); ?>

                                </span>
                                <div class="job-actions">
                                    <a href="<?php echo e(route('jobDetail', $job->id)); ?>" class="job-view-btn">
                                        View Job
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.save-job-button','data' => ['job' => $job,'size' => 'sm','class' => 'job-action-btn save']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('save-job-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['job' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($job),'size' => 'sm','class' => 'job-action-btn save']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Pagination -->
                <?php if($jobs->hasPages()): ?>
                    <div class="pagination-container">
                        <?php echo e($jobs->withQueryString()->links('pagination::bootstrap-5')); ?>

                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state-card">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Jobs Found</h3>
                    <p>We couldn't find jobs matching your criteria. Try adjusting your filters.</p>
                    <a href="<?php echo e(route('jobs')); ?>" class="empty-reset-btn">
                        <i class="fas fa-redo"></i>
                        Reset Filters
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
/* ============================================
   PROFESSIONAL JOBS PAGE - CLEAN & MODERN
   ============================================ */

/* === Container === */
.jobs-page-container {
    min-height: 100vh;
    background: #f8fafc;
    padding-bottom: 2rem;
}

/* === Enhanced Header === */
.jobs-header {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%);
    padding: 2rem 0;
    margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    width: calc(100% + 3rem);
    position: relative;
    overflow: hidden;
    border-radius: 0 0 24px 24px;
}

/* Header Decorative Elements */
.header-decoration {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
}

.decoration-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}

.decoration-circle.circle-1 {
    width: 300px;
    height: 300px;
    top: -150px;
    right: -50px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
}

.decoration-circle.circle-2 {
    width: 200px;
    height: 200px;
    bottom: -100px;
    left: 10%;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
}

.decoration-circle.circle-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    right: 20%;
    transform: translateY(-50%);
    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
}

.decoration-dots {
    position: absolute;
    top: 20px;
    right: 15%;
    width: 80px;
    height: 80px;
    background-image: radial-gradient(rgba(255,255,255,0.2) 1px, transparent 1px);
    background-size: 10px 10px;
    opacity: 0.5;
}

.jobs-header-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    position: relative;
    z-index: 1;
}

.jobs-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon-wrapper {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.header-icon-wrapper i {
    font-size: 1.5rem;
    color: #fff;
}

.header-text {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.jobs-title {
    color: #fff;
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.025em;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.jobs-subtitle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    margin: 0;
    font-weight: 400;
}

.jobs-subtitle i {
    font-size: 0.8rem;
    color: #fbbf24;
}

.jobs-subtitle strong {
    font-weight: 700;
    color: #fff;
}

.jobs-stat-badge {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 16px;
    padding: 0.875rem 1.25rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.jobs-stat-badge .stat-icon {
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.jobs-stat-badge .stat-icon i {
    font-size: 1.1rem;
    color: #fff;
}

.jobs-stat-badge .stat-content {
    display: flex;
    flex-direction: column;
}

.jobs-stat-badge .stat-value {
    color: #fff;
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.025em;
}

.jobs-stat-badge .stat-label {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.75rem;
    font-weight: 500;
    margin-top: 0.2rem;
}

/* Header Responsive */
@media (max-width: 768px) {
    .jobs-header {
        padding: 1.5rem 0;
        border-radius: 0 0 16px 16px;
    }

    .jobs-header-inner {
        flex-direction: column;
        gap: 1.25rem;
        text-align: center;
    }

    .jobs-header-left {
        flex-direction: column;
        gap: 0.75rem;
    }

    .header-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
    }

    .header-icon-wrapper i {
        font-size: 1.25rem;
    }

    .jobs-title {
        font-size: 1.35rem;
    }

    .jobs-subtitle {
        font-size: 0.85rem;
        justify-content: center;
    }

    .jobs-stat-badge {
        padding: 0.75rem 1rem;
    }

    .jobs-stat-badge .stat-icon {
        width: 38px;
        height: 38px;
    }

    .jobs-stat-badge .stat-value {
        font-size: 1.5rem;
    }

    .decoration-dots {
        display: none;
    }
}

/* === Layout Grid === */
.jobs-layout-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
    align-items: start;
}

/* === Sidebar Filters - Elegant Style === */
.jobs-sidebar {
    position: sticky;
    top: 1rem;
}

.filter-panel {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.filter-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(to right, #fafbfc, #f8fafc);
    border-bottom: 1px solid #e5e7eb;
}

.filter-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
}

.filter-title i {
    color: #4f46e5;
    font-size: 0.85rem;
}

.filter-clear-btn {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #64748b;
    text-decoration: none;
    padding: 0.35rem 0.625rem;
    border-radius: 6px;
    transition: all 0.2s;
}

.filter-clear-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

.filter-form {
    padding: 1.25rem;
}

.filter-section {
    margin-bottom: 1.25rem;
}

.filter-section:last-of-type {
    margin-bottom: 0;
}

.filter-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.625rem;
}

.filter-label i {
    color: #94a3b8;
    font-size: 0.7rem;
}

.filter-input-wrapper {
    position: relative;
}

.filter-input {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.2s;
}

.filter-input:focus {
    outline: none;
    background: #fff;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79,70,229,0.1);
}

.filter-input::placeholder {
    color: #94a3b8;
}

.filter-select-wrapper {
    position: relative;
}

.filter-select {
    width: 100%;
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    font-size: 0.85rem;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    appearance: none;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-select:focus {
    outline: none;
    background: #fff;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.select-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.65rem;
    pointer-events: none;
}

.radius-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.radius-text {
    font-size: 0.8rem;
    color: #64748b;
}

.radius-input {
    width: 55px;
    padding: 0.4rem 0.5rem;
    font-size: 0.8rem;
    text-align: center;
    color: #334155;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.radius-input:focus {
    outline: none;
    border-color: #4f46e5;
}

.location-detect-btn {
    width: 100%;
    margin-top: 0.75rem;
    padding: 0.625rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #4f46e5;
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: all 0.2s;
}

.location-detect-btn:hover {
    background: #e0e7ff;
    border-color: #a5b4fc;
}

.filter-submit-btn {
    width: 100%;
    margin-top: 1.25rem;
    padding: 0.875rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}

.filter-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
}

/* === Main Content === */
.jobs-main {
    min-width: 0;
}

/* Mobile Filter Toggle */
.mobile-filters-btn {
    display: none;
    width: 100%;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #4f46e5;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.mobile-filters-btn .filter-count {
    background: #4f46e5;
    color: #fff;
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
}

/* === Smart Match Banner === */
.smart-match-banner {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.smart-match-banner.active {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 1px solid #a7f3d0;
}

.smart-match-banner.inactive {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #fcd34d;
}

.smart-match-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.8rem;
}

.smart-match-banner.active .smart-match-icon {
    background: #10b981;
    color: #fff;
}

.smart-match-banner.inactive .smart-match-icon {
    background: #f59e0b;
    color: #fff;
}

.smart-match-content {
    flex: 1;
    min-width: 0;
}

.smart-match-content strong {
    display: block;
    font-size: 0.8rem;
    color: #1e293b;
    margin-bottom: 0;
}

.smart-match-content span {
    font-size: 0.7rem;
    color: #64748b;
}

.smart-match-settings {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: rgba(0,0,0,0.05);
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 0.75rem;
}

.smart-match-settings:hover {
    background: rgba(0,0,0,0.1);
    color: #1e293b;
}

.smart-match-activate {
    padding: 0.35rem 0.75rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #fff;
    background: #f59e0b;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.2s;
}

.smart-match-activate:hover {
    background: #d97706;
    color: #fff;
}

/* === Recommended Jobs === */
.recommended-jobs-section {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.recommended-jobs-section .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-title-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title-group i {
    color: #fbbf24;
    font-size: 0.85rem;
}

.section-title-group h2 {
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0;
}

.powered-badge {
    font-size: 0.6rem;
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    background: rgba(255,255,255,0.15);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.recommended-jobs-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.recommended-job-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    transition: all 0.2s;
}

.recommended-job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

.match-score {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
}

.recommended-job-content {
    padding: 0.75rem 0.625rem 0.5rem;
    text-align: center;
}

.company-initial {
    width: 36px;
    height: 36px;
    margin: 0 auto 0.5rem;
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
}

.recommended-job-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.2rem;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.recommended-company {
    font-size: 0.7rem;
    color: #64748b;
    margin: 0 0 0.4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.recommended-tags {
    display: flex;
    justify-content: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.recommended-tags .tag-type,
.recommended-tags .tag-category {
    font-size: 0.6rem;
    font-weight: 600;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

.tag-type {
    background: #eef2ff;
    color: #4f46e5;
}

.tag-category {
    background: #f0fdf4;
    color: #16a34a;
}

.recommended-job-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.5rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #4f46e5;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    text-decoration: none;
    transition: all 0.2s;
}

.recommended-job-link:hover {
    background: #eef2ff;
    color: #4338ca;
}

.recommended-job-link i {
    font-size: 0.6rem;
    transition: transform 0.2s;
}

.recommended-job-link:hover i {
    transform: translateX(3px);
}

/* === Results Header - Elegant Style === */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.results-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.results-count {
    font-size: 0.9rem;
    color: #64748b;
}

.results-count strong {
    color: #4f46e5;
    font-weight: 700;
    font-size: 1rem;
}

.active-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.active-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #4f46e5;
    background: #eef2ff;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    border: 1px solid #e0e7ff;
}

.active-filter-tag i {
    font-size: 0.65rem;
    opacity: 0.8;
}

.results-sort {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.results-sort label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

.results-sort select {
    padding: 0.5rem 0.875rem;
    font-size: 0.8rem;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.results-sort select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* === Job Cards - Grid Style (Compact Design) === */
.job-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.job-card-grid {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
}

.job-card-grid:hover {
    border-color: #c7d2fe;
    box-shadow: 0 4px 16px rgba(79, 70, 229, 0.1);
}

/* Card Header */
.job-card-header {
    margin-bottom: 0.75rem;
}

.job-card-top {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.job-company-logo {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    flex-shrink: 0;
}

.job-header-info {
    flex: 1;
    min-width: 0;
}

.job-title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    line-height: 1.3;
}

.job-title a {
    color: #1e293b;
    text-decoration: none;
    transition: color 0.2s;
}

.job-title a:hover {
    color: #4f46e5;
}

.job-company {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.job-menu-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #94a3b8;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.job-menu-btn:hover {
    background: #f1f5f9;
    color: #64748b;
}

.job-location {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    color: #64748b;
}

.job-location i {
    color: #10b981;
    font-size: 0.7rem;
}

/* Card Body */
.job-card-grid .job-card-body {
    flex: 1;
    padding: 0;
    display: block;
}

.job-info-row {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.job-info-item {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.job-info-item .info-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1e293b;
}

.job-info-item.salary .info-label {
    color: #059669;
}

.job-description {
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Card Tags */
.job-card-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    margin-top: 0.625rem;
    padding-top: 0.625rem;
    border-top: 1px solid #f1f5f9;
}

.job-tag {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    font-size: 0.7rem;
    font-weight: 500;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    transition: all 0.2s;
}

.job-tag:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

/* Card Footer */
.job-card-grid .job-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.625rem;
    padding-top: 0.625rem;
    border-top: 1px solid #f1f5f9;
    background: transparent;
}

.job-posted {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
}

.job-posted i {
    font-size: 0.7rem;
}

.job-actions {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.job-action-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    color: #94a3b8;
}

.job-action-btn:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.job-action-btn.save:hover {
    border-color: #fecaca;
    background: #fef2f2;
    color: #ef4444;
}

/* Fix for save job button - disable tooltips and overlays */
.job-card-grid .save-job-btn,
.job-card-grid .job-action-btn,
.job-actions .btn {
    position: relative;
}

.job-card-grid .save-job-btn::before,
.job-card-grid .save-job-btn::after,
.job-card-grid .job-action-btn::before,
.job-card-grid .job-action-btn::after,
.job-actions .btn::before,
.job-actions .btn::after {
    display: none !important;
    content: none !important;
}

/* Completely disable all tooltips and popovers on job cards */
.job-card-grid [title],
.job-card-grid [data-bs-toggle],
.job-actions [title],
.job-actions [data-bs-toggle],
.job-menu-btn[title],
.job-grid [title] {
    pointer-events: auto;
}

.job-card-grid .tooltip,
.job-actions .tooltip,
.job-grid .tooltip,
.tooltip.show {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* Hide Bootstrap popover on job cards */
.job-card-grid .popover,
.job-actions .popover,
.job-grid .popover,
.popover.show {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* Prevent any hover popup/overlay on job elements */
.job-card-grid *,
.job-grid * {
    --bs-tooltip-opacity: 0;
}

/* Disable menu button tooltip behavior */
.job-menu-btn {
    pointer-events: auto !important;
}

/* Style the save button properly */
.job-actions .save-job-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    color: #94a3b8;
}

.job-actions .save-job-btn:hover {
    border-color: #fecaca;
    background: #fef2f2;
    color: #ef4444;
}

.job-actions .save-job-btn.btn-success {
    background: #dcfce7;
    border-color: #86efac;
    color: #16a34a;
}

.job-actions .save-job-btn.btn-success:hover {
    background: #bbf7d0;
    border-color: #4ade80;
}

.job-actions .save-job-btn i {
    margin: 0 !important;
    font-size: 0.85rem;
}

/* View Job Button */
.job-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #4f46e5;
    background: transparent;
    border: 1px solid #e0e7ff;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.job-view-btn:hover {
    background: #4f46e5;
    color: #fff;
    border-color: #4f46e5;
}

.job-view-btn i {
    font-size: 0.65rem;
    transition: transform 0.2s;
}

.job-view-btn:hover i {
    transform: translateX(2px);
}

/* Tag Variations */
.job-tag.primary {
    background: #eef2ff;
    color: #4f46e5;
    border-color: #c7d2fe;
}

/* Responsive Grid */
@media (max-width: 1100px) {
    .job-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .job-card-grid {
        padding: 1rem;
    }

    .job-info-row {
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
    }
}

/* === Pagination === */
.pagination-container {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

.pagination-container .pagination {
    background: #fff;
    border-radius: 12px;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    display: flex;
    gap: 0.25rem;
}

.pagination-container .page-item .page-link {
    padding: 0.625rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    border: none;
    border-radius: 8px;
    transition: all 0.2s;
}

.pagination-container .page-item.active .page-link {
    background: #4f46e5;
    color: #fff;
}

.pagination-container .page-item .page-link:hover {
    background: #f1f5f9;
    color: #4f46e5;
}

.pagination-container .page-item.disabled .page-link {
    color: #cbd5e1;
}

/* === Empty State === */
.empty-state-card {
    text-align: center;
    padding: 3rem 2rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
}

.empty-state-card .empty-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1.25rem;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state-card .empty-icon i {
    font-size: 1.5rem;
    color: #94a3b8;
}

.empty-state-card h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.5rem;
}

.empty-state-card p {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0 0 1.5rem;
    max-width: 320px;
    margin-left: auto;
    margin-right: auto;
}

.empty-reset-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
    background: #4f46e5;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}

.empty-reset-btn:hover {
    background: #4338ca;
    color: #fff;
    transform: translateY(-1px);
}

/* === Responsive Design === */
@media (max-width: 1200px) {
    .jobs-layout-grid {
        grid-template-columns: 240px 1fr;
        gap: 0.875rem;
    }

    .recommended-jobs-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .jobs-layout-grid {
        grid-template-columns: 1fr;
    }

    .jobs-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        max-width: 280px;
        height: 100vh;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        overflow-y: auto;
    }

    .jobs-sidebar.show {
        transform: translateX(0);
    }

    .filter-panel {
        border-radius: 0;
        height: 100%;
        border: none;
    }

    .mobile-filters-btn {
        display: flex;
    }

    .recommended-jobs-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
}

@media (max-width: 768px) {
    .jobs-header {
        padding: 0.75rem 0;
        margin: -1rem -1rem 0.75rem -1rem;
        width: calc(100% + 2rem);
    }

    .jobs-header-inner {
        flex-direction: column;
        text-align: center;
        gap: 0.625rem;
    }

    .results-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }

    .results-info {
        flex-direction: column;
        gap: 0.375rem;
    }

    .job-card-body {
        flex-direction: column;
        padding: 0.75rem;
    }

    .job-company-logo {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .job-title-row {
        flex-direction: column;
        gap: 0.375rem;
    }

    .job-card-footer {
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
    }

    .job-apply-btn {
        width: 100%;
        justify-content: center;
    }

    .recommended-jobs-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .jobs-title {
        font-size: 1.1rem;
    }

    .jobs-stat-badge .stat-value {
        font-size: 1.25rem;
    }

    .job-meta-row {
        gap: 0.25rem 0.5rem;
    }
}

/* === Overlay for Mobile Sidebar === */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.sidebar-overlay.show {
    opacity: 1;
    visibility: visible;
}

/* === Animations === */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.job-card-grid {
    animation: fadeInUp 0.4s ease-out backwards;
}

.job-card-grid:nth-child(1) { animation-delay: 0.05s; }
.job-card-grid:nth-child(2) { animation-delay: 0.1s; }
.job-card-grid:nth-child(3) { animation-delay: 0.15s; }
.job-card-grid:nth-child(4) { animation-delay: 0.2s; }
.job-card-grid:nth-child(5) { animation-delay: 0.25s; }
.job-card-grid:nth-child(6) { animation-delay: 0.3s; }

.recommended-job-card {
    animation: fadeInUp 0.4s ease-out;
}

/* Large screens */
@media (min-width: 1600px) {
    .jobs-layout-grid {
        max-width: 1500px;
    }
}

@media (min-width: 1920px) {
    .jobs-layout-grid {
        max-width: 1700px;
    }
}

</style>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token Setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Disable all Bootstrap tooltips and popovers on job cards
    // Remove title attributes that might trigger tooltips
    document.querySelectorAll('.job-grid [title], .job-card-grid [title]').forEach(function(el) {
        el.removeAttribute('title');
    });

    // Dispose any existing Bootstrap tooltips/popovers on job elements
    document.querySelectorAll('.job-grid *, .job-card-grid *').forEach(function(el) {
        // Dispose tooltip if exists
        if (bootstrap && bootstrap.Tooltip) {
            var tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) tooltip.dispose();
        }
        // Dispose popover if exists
        if (bootstrap && bootstrap.Popover) {
            var popover = bootstrap.Popover.getInstance(el);
            if (popover) popover.dispose();
        }
    });

    // Clear coordinates when location is manually typed
    const locationInput = document.getElementById('location');
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            document.getElementById('location_filter_latitude').value = '';
            document.getElementById('location_filter_longitude').value = '';
        });
    }

    // Form submission with loading state
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            const searchBtn = this.querySelector('.filter-submit-btn');
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                searchBtn.disabled = true;
            }
        });
    }

    // Mobile filter sidebar toggle
    const mobileToggle = document.getElementById('mobileFilterToggle');
    const sidebar = document.querySelector('.jobs-sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.add('show');
            if (overlay) overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            if (sidebar) sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        });
    }

    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            if (sidebar) sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
});

// Geolocation function
function useCurrentLocation() {
    const btn = document.querySelector('.location-detect-btn');
    const originalText = btn.innerHTML;
    const mapboxToken = '<?php echo e(config("mapbox.public_token")); ?>';

    if (!window.isSecureContext) {
        showNotification('Location requires HTTPS connection.', 'error');
        return;
    }

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Detecting...';
    btn.disabled = true;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById('location_filter_latitude').value = lat;
                document.getElementById('location_filter_longitude').value = lng;

                if (mapboxToken && mapboxToken.length > 10) {
                    fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${mapboxToken}&types=locality,place,neighborhood&limit=1`)
                        .then(r => r.json())
                        .then(data => {
                            let locationName = 'Near Me';
                            if (data.features && data.features.length > 0) {
                                locationName = data.features[0].place_name.split(',')[0].trim();
                            }
                            document.getElementById('location').value = locationName;
                            btn.innerHTML = '<i class="fas fa-check"></i> Location Found';
                            btn.style.background = '#10b981';
                            btn.style.color = '#fff';
                            btn.style.borderColor = '#10b981';
                            showNotification(`Location: ${locationName}`, 'success');
                            resetBtn(3000);
                        })
                        .catch(() => {
                            document.getElementById('location').value = 'Near Me';
                            btn.innerHTML = '<i class="fas fa-check"></i> Located';
                            showNotification('Location detected!', 'success');
                            resetBtn(2000);
                        });
                } else {
                    document.getElementById('location').value = 'Near Me';
                    btn.innerHTML = '<i class="fas fa-check"></i> Located';
                    showNotification('Location detected!', 'success');
                    resetBtn(2000);
                }
            },
            function(error) {
                btn.innerHTML = '<i class="fas fa-times"></i> Failed';
                btn.style.background = '#ef4444';
                btn.style.color = '#fff';
                btn.style.borderColor = '#ef4444';
                let msg = 'Unable to detect location.';
                if (error.code === error.PERMISSION_DENIED) msg = 'Location access denied.';
                showNotification(msg, 'error');
                resetBtn(3000);
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 }
        );
    } else {
        showNotification('Geolocation not supported.', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }

    function resetBtn(delay) {
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
            btn.disabled = false;
        }, delay);
    }
}

// Toast notification
function showNotification(message, type = 'info') {
    const existing = document.querySelectorAll('.toast-notification');
    existing.forEach(n => n.remove());

    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;

    const colors = { success: '#10b981', error: '#ef4444', info: '#4f46e5' };
    toast.style.cssText = `
        position: fixed;
        top: 1rem;
        right: 1rem;
        background: ${colors[type] || colors.info};
        color: #fff;
        padding: 0.875rem 1.25rem;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        z-index: 9999;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        transform: translateX(120%);
        transition: transform 0.3s ease;
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.style.transform = 'translateX(0)', 10);
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.jobseeker', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/modern-jobs.blade.php ENDPATH**/ ?>