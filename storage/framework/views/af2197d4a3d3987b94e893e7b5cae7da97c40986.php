

<?php $__env->startSection('title', 'TugmaJobs - Find Your Dream Job'); ?>

<?php $__env->startSection('content'); ?>
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="hero-title">Find & Hire Experts for<br>any Job</h1>
                <p class="hero-subtitle">Find Jobs, Employment & Career Opportunities. Some of the companies we've helped recruit excellent applicants over the years.</p>
                
                <!-- Search Form -->
                <div class="hero-search-form">
                    <form action="<?php echo e(route('jobs')); ?>" method="GET" class="search-form">
                        <div class="search-inputs">
                            <input type="text" name="keyword" class="form-control search-input" placeholder="Job Title or Keywords" value="<?php echo e(request('keyword')); ?>">
                            <div class="location-input-wrapper">
                                <input type="text" 
                                       name="location" 
                                       id="locationSearch" 
                                       class="form-control location-input" 
                                       placeholder="Location (e.g., Poblacion, Sta. Cruz)" 
                                       value="<?php echo e(request('location')); ?>"
                                       autocomplete="off">
                                <div id="locationSuggestions" class="location-suggestions"></div>
                                <input type="hidden" name="location_lat" id="locationLat" value="<?php echo e(request('location_lat')); ?>">
                                <input type="hidden" name="location_lng" id="locationLng" value="<?php echo e(request('location_lng')); ?>">
                            </div>
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Popular Searches -->
                <div class="popular-searches">
                    <span class="popular-label">Popular Searches:</span>
                    <?php $__currentLoopData = $popularKeywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyword): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('jobs', ['keyword' => $keyword])); ?>" class="popular-tag"><?php echo e(ucfirst($keyword)); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="ws-stat-box stat-box">
                    <div class="ws-stat-icon stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="ws-stat-number stat-number"><?php echo e(number_format($stats['total_jobs'])); ?></h3>
                    <p class="ws-stat-label stat-label">Active Jobs</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="ws-stat-box stat-box">
                    <div class="ws-stat-icon stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="ws-stat-number stat-number"><?php echo e(number_format($stats['companies'])); ?></h3>
                    <p class="ws-stat-label stat-label">Companies Hiring</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="ws-stat-box stat-box">
                    <div class="ws-stat-icon stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="ws-stat-number stat-number"><?php echo e(number_format($stats['applications'])); ?></h3>
                    <p class="ws-stat-label stat-label">Applications Submitted</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="ws-stat-box stat-box">
                    <div class="ws-stat-icon stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="ws-stat-number stat-number"><?php echo e(number_format($stats['jobs_this_week'])); ?></h3>
                    <p class="ws-stat-label stat-label">Jobs This Week</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Browse Jobs By Category -->
<section class="categories-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Browse Jobs By Category</h2>
                <p class="section-subtitle">Search all the open positions on the web. Get your own personalized salary<br>estimate. Read reviews on over 30,000+ companies worldwide.</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-3 col-md-6">
                <a href="<?php echo e(route('jobs', ['category' => $category->name])); ?>" class="category-link">
                    <div class="category-card ws-card">
                        <div class="category-icon">
                            <?php if(strpos($category->icon, 'fa-') !== false): ?>
                                <i class="<?php echo e($category->icon); ?>"></i>
                            <?php elseif(strpos($category->icon, 'http') === 0): ?>
                                <img src="<?php echo e($category->icon); ?>" alt="<?php echo e($category->name); ?>" class="img-fluid">
                            <?php else: ?>
                                <span class="category-emoji"><?php echo e($category->icon); ?></span>
                            <?php endif; ?>
                        </div>
                        <h4 class="category-title"><?php echo e($category->name); ?></h4>
                        <p class="category-count"><span class="ws-badge ws-badge-primary"><?php echo e($category->jobs_count); ?> <?php echo e(Str::plural('Job', $category->jobs_count)); ?> Available</span></p>
                    </div>
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<!-- Featured Jobs Section -->
<section class="popular-jobs-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Featured Jobs</h2>
                <p class="section-subtitle">Here are some of the most popular job openings.</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="featured-jobs-carousel">
                    <?php $__empty_1 = true; $__currentLoopData = $featuredJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <!-- Job Card -->
                    <a href="<?php echo e(route('jobDetail', $job->id)); ?>" class="text-decoration-none">
                        <div class="ws-job-card job-card ws-card">
                            <div class="ws-job-card-header job-company">
                                <div class="ws-job-card-logo company-logo">
                                    <?php if($job->employer && $job->employer->employerProfile && $job->employer->employerProfile->company_logo): ?>
                                        <img src="<?php echo e(asset('storage/' . $job->employer->employerProfile->company_logo)); ?>?v=<?php echo e(time()); ?>"
                                             alt="<?php echo e($job->employer->name); ?>"
                                             class="img-fluid"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-building\'></i>';">
                                    <?php else: ?>
                                        <i class="fas fa-building"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="ws-job-card-info company-info">
                                    <h5 class="ws-job-card-company company-name"><?php echo e($job->employer->employerProfileDirect->company_name ?? $job->employer->company_name ?? $job->employer->name ?? 'Company'); ?></h5>
                                    <span class="job-posted ws-text-secondary ws-text-sm"><?php echo e($job->created_at->diffForHumans()); ?></span>
                                </div>
                            </div>

                            <div class="job-details">
                                <h4 class="ws-job-card-title job-title"><?php echo e($job->title); ?></h4>
                                <div class="job-location ws-text-secondary">
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo e($job->location ?? 'Remote'); ?>

                                </div>
                                <div class="ws-job-card-meta job-meta">
                                    <span class="job-applications ws-text-sm ws-text-secondary"><?php echo e($job->applications_count ?? 0); ?> applied of <?php echo e($job->vacancy ?? 'multiple'); ?> <?php echo e(Str::plural('vacancy', $job->vacancy)); ?></span>
                                    <div class="ws-job-card-badges job-tags">
                                        <span class="ws-badge ws-badge-primary job-type"><?php echo e($job->jobType->name ?? 'Full Time'); ?></span>
                                        <?php if($job->salary_min && $job->salary_max): ?>
                                            <span class="ws-badge ws-badge-success job-salary">₱<?php echo e(number_format($job->salary_min)); ?> - ₱<?php echo e(number_format($job->salary_max)); ?></span>
                                        <?php elseif($job->salary_min): ?>
                                            <span class="ws-badge ws-badge-success job-salary">From ₱<?php echo e(number_format($job->salary_min)); ?></span>
                                        <?php elseif($job->salary_max): ?>
                                            <span class="ws-badge ws-badge-success job-salary">Up to ₱<?php echo e(number_format($job->salary_max)); ?></span>
                                        <?php else: ?>
                                            <span class="ws-badge ws-badge-secondary job-salary">Salary Negotiable</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-5">
                        <p class="ws-text-muted">No featured jobs available at the moment.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo e(route('jobs')); ?>" class="ws-btn ws-btn-primary ws-btn-lg btn btn-view-more">
                        View More Jobs <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Companies Section -->
<section class="featured-companies-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Featured Companies</h2>
                <p class="section-subtitle">Discover amazing companies that are hiring right now.</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php $__empty_1 = true; $__currentLoopData = $featuredCompanies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-lg-4 col-md-6">
                <div class="ws-company-card company-card ws-card">
                    <div class="company-header">
                        <div class="company-logo-wrapper ws-company-logo">
                            <?php if($company->company_logo): ?>
                                <img src="<?php echo e(asset('storage/' . $company->company_logo)); ?>?v=<?php echo e(time()); ?>"
                                     alt="<?php echo e($company->company_name); ?>"
                                     class="company-logo-img"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'company-logo-placeholder\'><i class=\'fas fa-building\'></i></div>';">
                            <?php else: ?>
                                <div class="company-logo-placeholder">
                                    <i class="fas fa-building"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="company-info">
                            <h4 class="company-name ws-font-semibold"><?php echo e($company->company_name); ?></h4>
                            <p class="company-location ws-text-secondary ws-text-sm">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo e($company->location ?? 'Location not specified'); ?>

                            </p>
                        </div>
                    </div>

                    <div class="company-description">
                        <p class="ws-text-secondary"><?php echo e(Str::limit($company->company_description ?? 'Company description not available', 120)); ?></p>
                    </div>

                    <div class="company-stats">
                        <div class="stat-item ws-flex ws-items-center ws-gap-2">
                            <span class="stat-number ws-text-2xl ws-font-bold"><?php echo e($company->jobs_count); ?></span>
                            <span class="stat-label ws-text-secondary ws-text-sm">Open <?php echo e(Str::plural('Job', $company->jobs_count)); ?></span>
                        </div>
                    </div>

                    <div class="company-actions">
                        <?php if($company->type === 'standalone' && $company->slug): ?>
                            <a href="<?php echo e(route('companies.show', $company->slug)); ?>" class="ws-btn ws-btn-success btn-view-company">
                                View Company
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        <?php elseif($company->type === 'standalone'): ?>
                            <a href="<?php echo e(route('companies.show', $company->id)); ?>" class="ws-btn ws-btn-success btn-view-company">
                                View Company
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('companies.show', $company->id)); ?>" class="ws-btn ws-btn-success btn-view-company">
                                View Company
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12 text-center py-5">
                <p class="ws-text-muted">No featured companies available at the moment.</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo e(route('companies')); ?>" class="ws-btn ws-btn-primary ws-btn-lg btn btn-view-more">
                View All Companies <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">How TugmaJobs Works</h2>
                <p class="section-subtitle">Get started in just a few simple steps</p>
            </div>
        </div>

        <!-- Toggle Buttons -->
        <div class="text-center mb-5">
            <div class="btn-group role-toggle" role="group">
                <input type="radio" class="btn-check" name="roleToggle" id="forJobseekers" checked>
                <label class="btn btn-toggle" for="forJobseekers" onclick="showJobseekerSteps()">For Jobseekers</label>

                <input type="radio" class="btn-check" name="roleToggle" id="forEmployers">
                <label class="btn btn-toggle" for="forEmployers" onclick="showEmployerSteps()">For Employers</label>
            </div>
        </div>

        <!-- Jobseeker Steps -->
        <div id="jobseekerSteps" class="steps-container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h4 class="step-title">Create Your Account</h4>
                        <p class="step-description">Sign up in seconds using your email or Google account. Build your professional profile.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="step-title">Search & Apply</h4>
                        <p class="step-description">Browse thousands of jobs in Sta. Cruz, Davao del Sur and beyond. Apply with one click.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4 class="step-title">Get Hired</h4>
                        <p class="step-description">Connect with employers, ace your interviews, and land your dream job.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employer Steps -->
        <div id="employerSteps" class="steps-container" style="display: none;">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4 class="step-title">Post Your Job</h4>
                        <p class="step-description">Create a free account and post your job openings in minutes.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="step-title">Review Candidates</h4>
                        <p class="step-description">Get qualified applications from talented professionals in your area.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h4 class="step-title">Hire Top Talent</h4>
                        <p class="step-description">Interview and hire the best candidates for your company.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="employer-cta-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h2 class="cta-title">Ready to Take the Next Step?</h2>
                <p class="cta-subtitle">Whether you're looking for your dream job or searching for top talent, TugmaJobs connects you with the right opportunities in Sta. Cruz, Davao del Sur.</p>
                <ul class="cta-benefits">
                    <li><i class="fas fa-check-circle"></i> Free job posting for employers</li>
                    <li><i class="fas fa-check-circle"></i> Access to qualified candidates</li>
                    <li><i class="fas fa-check-circle"></i> Easy job search for seekers</li>
                    <li><i class="fas fa-check-circle"></i> Fast and efficient process</li>
                </ul>
            </div>
            <div class="col-lg-5 text-center">
                <div class="cta-buttons">
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(Auth::user()->isEmployer()): ?>
                            <a href="<?php echo e(route('employer.jobs.create')); ?>" class="btn btn-cta btn-cta-employer">
                                <i class="fas fa-plus-circle me-2"></i>Post a Job Now
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('jobs')); ?>" class="btn btn-cta btn-cta-jobseeker">
                                <i class="fas fa-search me-2"></i>Apply for a Job
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="btn btn-cta btn-cta-jobseeker" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToLogin()">
                            <i class="fas fa-search me-2"></i>Apply for a Job
                        </button>
                        <button type="button" class="btn btn-cta btn-cta-employer" data-bs-toggle="modal" data-bs-target="#employerAuthModal" onclick="switchToEmployerLogin()">
                            <i class="fas fa-plus-circle me-2"></i>Post a Job Now
                        </button>
                    <?php endif; ?>
                </div>
                <p class="cta-note mt-3">Join <?php echo e(number_format($stats['companies'])); ?>+ companies and thousands of job seekers on TugmaJobs</p>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
    padding: 140px 0 100px;
    color: white;
    position: relative;
    overflow: hidden;
    min-height: 600px;
    display: flex;
    align-items: center;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, 
        rgba(67, 86, 99, 0.95) 0%,
        rgba(67, 86, 99, 0.95) 100%);
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    color: #78C841;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    position: relative;
    letter-spacing: -0.5px;
}

.hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 3rem;
    line-height: 1.8;
    color: #78C841;
    font-weight: 500;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    background: rgba(67, 86, 99, 0.85);
    padding: 1.5rem 2rem;
    border-radius: 16px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 2px solid rgba(120, 200, 65, 0.1);
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.hero-search-form {
    position: relative;
    z-index: 2;
    margin-bottom: 2.5rem;
    width: 100%;
}

.search-form {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 10px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    max-width: 750px;
    margin: 0 auto;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.search-inputs {
    display: flex;
    align-items: stretch;
    gap: 10px;
}

.search-input {
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 16px 24px;
    font-size: 1rem;
    flex: 1;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    color: #1e293b;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.3);
    border-color: #4fd1c5;
}

.search-input::placeholder {
    color: #64748b;
}

.location-input-wrapper {
    position: relative;
    flex: 1;
    min-width: 220px;
}

.location-input {
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 16px 24px;
    font-size: 1rem;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    color: #1e293b;
    transition: all 0.3s ease;
}

.location-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.3);
    border-color: #4fd1c5;
}

.location-input::placeholder {
    color: #64748b;
}

.location-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    display: none;
}

.location-suggestion {
    padding: 12px 20px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.2s ease;
}

.location-suggestion:hover {
    background-color: #f8fafc;
}

.location-suggestion:last-child {
    border-bottom: none;
}

.location-suggestion.active {
    background-color: #10b981;
    color: white;
}

.suggestion-name {
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 2px;
}

.suggestion-address {
    font-size: 12px;
    color: #64748b;
}

.location-suggestion.active .suggestion-name,
.location-suggestion.active .suggestion-address {
    color: white;
}

.btn-search {
    background: linear-gradient(135deg, #78C841 0%, #5fb32e 100%);
    color: white;
    border: none;
    padding: 16px 32px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.125rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(120, 200, 65, 0.2);
    white-space: nowrap;
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(120, 200, 65, 0.3);
    background: linear-gradient(135deg, #5fb32e 0%, #4a9e1f 100%);
    color: white;
}

.btn-search:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.popular-searches {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.popular-label {
    font-weight: 600;
    color: #78C841;
    background: rgba(67, 86, 99, 0.9);
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 2px solid rgba(120, 200, 65, 0.2);
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    letter-spacing: 0.5px;
    font-size: 1rem;
}

.popular-tag {
    background: rgba(255, 255, 255, 0.95);
    color: #78C841;
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 2px solid rgba(120, 200, 65, 0.3);
    font-weight: 600;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    letter-spacing: 0.5px;
}

.popular-tag:hover {
    background: #78C841;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(120, 200, 65, 0.3);
    border-color: #78C841;
}

/* Categories Section */
.categories-section {
    padding: 100px 0;
    background: #f8fafc;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.125rem;
    color: #64748b;
    line-height: 1.6;
}

.category-card {
    background: white;
    padding: 40px 30px;
    border-radius: 16px;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
    height: 100%;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    border-color: #10b981;
}

.category-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
}

.category-icon i {
    font-size: 2rem;
    color: white;
}

.category-icon img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.category-emoji {
    font-size: 2rem;
    line-height: 1;
}

.category-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}

.category-count {
    color: #64748b;
    font-size: 14px;
    margin: 0;
}

.category-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.category-link:hover {
    text-decoration: none;
    color: inherit;
}

/* Popular Jobs Section */
.popular-jobs-section {
    padding: 100px 0;
    background: white;
}

.job-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-color: #10b981;
}

.job-company {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.company-logo {
    width: 60px;
    height: 60px;
    background: #1877f2;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.company-logo i {
    font-size: 1.5rem;
    color: white;
}

.company-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.company-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.job-posted {
    font-size: 14px;
    color: #64748b;
}

.job-title {
    font-size: 1.375rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 10px;
}

.job-location {
    color: #64748b;
    margin-bottom: 15px;
}

.job-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.job-applications {
    font-size: 14px;
    color: #64748b;
}

.job-tags {
    display: flex;
    gap: 10px;
}

.job-type {
    background: #e0e7ff;
    color: #4338ca;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.job-salary {
    background: #f3f4f6;
    color: #1f2937;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.btn-view-more {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 16px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.125rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);
}

.btn-view-more:hover {
    background: #4338ca;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
}

/* Featured Companies Section */
.featured-companies-section {
    padding: 100px 0;
    background: #f8fafc;
}

.company-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 30px;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.company-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.12);
    border-color: #10b981;
}

.company-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.company-logo-wrapper {
    width: 70px;
    height: 70px;
    margin-right: 20px;
    flex-shrink: 0;
}

.company-logo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
}

.company-logo-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.company-logo-placeholder i {
    font-size: 1.8rem;
    color: white;
}

.company-info {
    flex: 1;
}

.company-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.company-location {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.company-location i {
    color: #94a3b8;
    font-size: 0.8rem;
}

.company-description {
    flex: 1;
    margin-bottom: 20px;
}

.company-description p {
    color: #64748b;
    line-height: 1.6;
    margin: 0;
    font-size: 0.9rem;
}

.company-stats {
    margin-bottom: 25px;
    padding: 15px 0;
    border-top: 1px solid #f1f5f9;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #059669;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
}

.company-actions {
    margin-top: auto;
}

.btn-view-company {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.btn-view-company:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

.btn-view-company i {
    transition: transform 0.3s ease;
}

.btn-view-company:hover i {
    transform: translateX(4px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .search-inputs {
        flex-direction: column;
        gap: 10px;
    }
    
    .search-input,
    .location-input-wrapper {
        width: 100%;
    }
    
    .popular-searches {
        flex-direction: column;
        gap: 10px;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .job-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}

/* Fix for white tooltip boxes on hover */
* {
    -webkit-tap-highlight-color: transparent;
}

/* Remove default browser tooltips */
[title]:hover::after,
[title]:hover::before {
    display: none !important;
}

/* Prevent white boxes from appearing */
.nav-link::before,
.nav-link::after,
.category-card::before,
.category-card::after,
.job-card::before,
.job-card::after {
    display: none !important;
}

/* Clean hover states */
a:hover, button:hover {
    outline: none;
}

/* Stats Section */
.stats-section {
    padding: 80px 0;
    background: white;
    border-top: 1px solid #f1f5f9;
}

.stat-box {
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 20px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-box:hover {
    transform: translateY(-10px);
    border-color: #78C841;
    box-shadow: 0 20px 40px rgba(120, 200, 65, 0.15);
}

.stat-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #78C841 0%, #5fb32e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 10px 30px rgba(120, 200, 65, 0.25);
}

.stat-icon i {
    font-size: 2rem;
    color: white;
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 10px;
    line-height: 1;
}

.stat-label {
    font-size: 1rem;
    color: #64748b;
    font-weight: 500;
    margin: 0;
}

/* How It Works Section */
.how-it-works-section {
    padding: 100px 0;
    background: #f8fafc;
}

.role-toggle {
    display: inline-flex;
    background: white;
    border-radius: 50px;
    padding: 6px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.btn-toggle {
    padding: 12px 40px;
    border-radius: 50px;
    border: none;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    transition: all 0.3s ease;
}

.btn-check:checked + .btn-toggle {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.steps-container {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.step-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    height: 100%;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
    position: relative;
}

.step-card:hover {
    transform: translateY(-10px);
    border-color: #2563eb;
    box-shadow: 0 20px 40px rgba(37, 99, 235, 0.15);
}

.step-number {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.step-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px auto 25px;
}

.step-icon i {
    font-size: 2.5rem;
    color: #2563eb;
}

.step-title {
    font-size: 1.375rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 15px;
}

.step-description {
    color: #64748b;
    line-height: 1.7;
    font-size: 0.95rem;
    margin: 0;
}

/* Employer CTA Section */
.employer-cta-section {
    padding: 100px 0;
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    position: relative;
    overflow: hidden;
}

.employer-cta-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.employer-cta-section::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 20px;
    color: white;
}

.cta-subtitle {
    font-size: 1.25rem;
    margin-bottom: 30px;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
}

.cta-benefits {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.cta-benefits li {
    display: flex;
    align-items: center;
    font-size: 1.125rem;
    color: rgba(255, 255, 255, 0.95);
}

.cta-benefits i {
    color: #78C841;
    margin-right: 12px;
    font-size: 1.25rem;
}

.cta-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

.btn-cta {
    padding: 16px 40px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: none;
    min-width: 250px;
    cursor: pointer;
}

.btn-cta-jobseeker {
    background: white;
    color: #2563eb;
}

.btn-cta-jobseeker:hover {
    background: #78C841;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

.btn-cta-employer {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 2px solid white;
}

.btn-cta-employer:hover {
    background: white;
    color: #2563eb;
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

.cta-note {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
    font-style: italic;
}

/* Responsive adjustments for new sections */
@media (max-width: 992px) {
    .carousel-control-prev {
        left: -30px;
    }

    .carousel-control-next {
        right: -30px;
    }

    .cta-benefits {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stat-number {
        font-size: 2rem;
    }

    .stat-box {
        padding: 30px 15px;
    }

    .role-toggle {
        flex-direction: column;
        width: 100%;
    }

    .btn-toggle {
        width: 100%;
        padding: 12px 20px;
    }

    .step-card {
        margin-bottom: 40px;
    }

    .cta-title {
        font-size: 1.75rem;
    }

    .cta-subtitle {
        font-size: 1rem;
    }

    .employer-cta-section .col-lg-5 {
        margin-top: 30px;
    }

    .btn-cta {
        padding: 14px 30px;
        font-size: 1rem;
        min-width: 220px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationInput = document.getElementById('locationSearch');
    const suggestionsContainer = document.getElementById('locationSuggestions');
    const locationLatInput = document.getElementById('locationLat');
    const locationLngInput = document.getElementById('locationLng');
    
    let searchTimeout;
    let currentSuggestions = [];
    let selectedIndex = -1;

    // Handle location input
    locationInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideSuggestions();
            return;
        }
        
        // Debounce the search
        searchTimeout = setTimeout(() => {
            searchLocations(query);
        }, 300);
    });

    // Handle keyboard navigation
    locationInput.addEventListener('keydown', function(e) {
        if (!suggestionsContainer.style.display || suggestionsContainer.style.display === 'none') {
            return;
        }

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, currentSuggestions.length - 1);
                updateSelection();
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection();
                break;
            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && currentSuggestions[selectedIndex]) {
                    selectSuggestion(currentSuggestions[selectedIndex]);
                }
                break;
            case 'Escape':
                hideSuggestions();
                break;
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!locationInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });

    // Search for locations using Mapbox API
    async function searchLocations(query) {
        try {
            const response = await fetch(`/api/location/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.suggestions && data.suggestions.length > 0) {
                currentSuggestions = data.suggestions;
                displaySuggestions(data.suggestions);
            } else {
                hideSuggestions();
            }
        } catch (error) {
            console.error('Location search error:', error);
            hideSuggestions();
        }
    }

    // Display location suggestions
    function displaySuggestions(suggestions) {
        suggestionsContainer.innerHTML = '';
        selectedIndex = -1;
        
        suggestions.forEach((suggestion, index) => {
            const suggestionElement = document.createElement('div');
            suggestionElement.className = 'location-suggestion';
            suggestionElement.innerHTML = `
                <div class="suggestion-name">${suggestion.name}</div>
                <div class="suggestion-address">${suggestion.full_address || suggestion.place_name}</div>
            `;
            
            suggestionElement.addEventListener('click', () => {
                selectSuggestion(suggestion);
            });
            
            suggestionsContainer.appendChild(suggestionElement);
        });
        
        suggestionsContainer.style.display = 'block';
    }

    // Update visual selection
    function updateSelection() {
        const suggestions = suggestionsContainer.querySelectorAll('.location-suggestion');
        suggestions.forEach((suggestion, index) => {
            suggestion.classList.toggle('active', index === selectedIndex);
        });
    }

    // Select a suggestion
    function selectSuggestion(suggestion) {
        locationInput.value = suggestion.name;
        locationLatInput.value = suggestion.coordinates ? suggestion.coordinates.latitude : '';
        locationLngInput.value = suggestion.coordinates ? suggestion.coordinates.longitude : '';
        hideSuggestions();
    }

    // Hide suggestions
    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
        selectedIndex = -1;
        currentSuggestions = [];
    }

    // Add some popular Sta. Cruz, Davao del Sur locations as quick options
    const popularLocations = [
        { name: 'Poblacion', full_address: 'Poblacion, Sta. Cruz, Davao del Sur' },
        { name: 'Darong', full_address: 'Darong, Sta. Cruz, Davao del Sur' },
        { name: 'Coronon', full_address: 'Coronon, Sta. Cruz, Davao del Sur' },
        { name: 'Inawayan', full_address: 'Inawayan, Sta. Cruz, Davao del Sur' },
        { name: 'Remote Work', full_address: 'Work from anywhere' }
    ];

    // Show popular locations when input is focused and empty
    locationInput.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            currentSuggestions = popularLocations;
            displaySuggestions(popularLocations);
        }
    });

    // How It Works - Toggle between Jobseeker and Employer steps
    window.showJobseekerSteps = function() {
        document.getElementById('jobseekerSteps').style.display = 'block';
        document.getElementById('employerSteps').style.display = 'none';
    };

    window.showEmployerSteps = function() {
        document.getElementById('jobseekerSteps').style.display = 'none';
        document.getElementById('employerSteps').style.display = 'block';
    };
});
</script>

<!-- Jobseeker Warning Modal -->
<?php if(auth()->guard()->check()): ?>
<?php if(!Auth::user()->isEmployer()): ?>
<div class="modal fade" id="jobseekerWarningModal" tabindex="-1" aria-labelledby="jobseekerWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="modal-header border-0 pb-0" style="padding: 1.5rem 1.5rem 0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" style="padding: 1rem 2rem 2rem;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #d97706;"></i>
                </div>
                <h5 class="modal-title mb-3" id="jobseekerWarningModalLabel" style="font-weight: 700; color: #1e293b; font-size: 1.5rem;">Employer Feature Only</h5>
                <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    The "Post a Job" feature is exclusively available for employer accounts. As a jobseeker, you can browse and apply for jobs instead.
                </p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?php echo e(route('jobs')); ?>" class="btn" style="background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%); color: #0f172a; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 12px; border: none;">
                        <i class="fas fa-search me-2"></i>Browse Jobs
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 500;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('front.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/modern-home.blade.php ENDPATH**/ ?>