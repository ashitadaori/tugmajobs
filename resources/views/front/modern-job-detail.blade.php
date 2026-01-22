@extends('layouts.jobseeker')

@section('page-title', isset($job) ? $job->title . ' - Job Details' : 'Job Details')

@section('jobseeker-content')
    <!-- Modern Hero Header -->
    <div class="job-detail-hero">
        <div class="container-fluid px-0">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="breadcrumb-nav ws-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item ws-breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home"></i>
                            Home</a></li>
                    <li class="breadcrumb-item ws-breadcrumb-item"><a href="{{ route('jobs') }}"><i
                                class="fas fa-briefcase"></i> Jobs</a></li>
                    <li class="breadcrumb-item ws-breadcrumb-item active" aria-current="page">
                        {{ Str::limit($job->title, 30) }}</li>
                </ol>
            </nav>

            <!-- Job Header Card -->
            <div class="job-header-card ws-card">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3 ws-flex ws-items-start ws-gap-3">
                            @php
                                $companyName = null;
                                $companyInitial = 'C';

                                // First check if linked to a company via company_id
                                if ($job->company_id && $job->company && !empty($job->company->name)) {
                                    $companyName = $job->company->name;
                                } elseif (!empty($job->company_name) && $job->company_name !== 'Confidential') {
                                    $companyName = $job->company_name;
                                } elseif ($job->employer && $job->employer->employerProfileDirect && !empty($job->employer->employerProfileDirect->company_name)) {
                                    $companyName = $job->employer->employerProfileDirect->company_name;
                                } elseif ($job->employer && !empty($job->employer->company_name)) {
                                    $companyName = $job->employer->company_name;
                                } elseif ($job->employer && !empty($job->employer->name)) {
                                    $companyName = $job->employer->name;
                                } else {
                                    $companyName = 'Confidential';
                                }

                                $companyInitial = substr($companyName, 0, 1);
                            @endphp

                            <div class="company-logo-wrapper ws-avatar ws-avatar-xl">
                                <div class="company-logo-badge">
                                    {{ $companyInitial }}
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="job-meta-tags mb-2 ws-flex ws-flex-wrap ws-gap-2">
                                    <span
                                        class="job-type-badge ws-badge ws-badge-primary {{ strtolower($job->jobType->name) }}">
                                        <i class="fas fa-briefcase"></i> {{ $job->jobType->name }}
                                    </span>
                                    @if($job->category)
                                        <span class="category-badge ws-badge ws-badge-info">
                                            <i class="fas fa-tag"></i> {{ $job->category->name }}
                                        </span>
                                    @endif
                                    <span class="posted-badge ws-badge ws-badge-secondary">
                                        <i class="fas fa-clock"></i> Posted
                                        {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
                                    </span>
                                </div>

                                <h1 class="job-title-modern ws-text-3xl ws-font-bold">{{ $job->title }}</h1>

                                <div class="company-info-inline ws-flex ws-flex-wrap ws-gap-3 ws-items-center">
                                    <span class="company-name-badge ws-text-lg ws-font-medium">
                                        <i class="fas fa-building"></i> {{ $companyName }}
                                    </span>
                                    <x-verified-badge :user="$job->employer" size="sm" />
                                    <span class="location-badge ws-badge ws-badge-secondary">
                                        <i class="fas fa-map-marker-alt"></i> {{ $job->getFullAddress() ?: $job->location }}
                                    </span>
                                    @if (!is_null($job->salary_range))
                                        <span class="salary-badge ws-badge ws-badge-success">
                                            <i class="fas fa-peso-sign"></i> {{ $job->salary_range }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="job-actions-modern ws-flex ws-gap-2">
                            @auth
                                @if(Auth::user()->role === 'jobseeker')
                                    <button type="button" id="bookmarkJobBtn-{{ $job->id }}"
                                        onclick="toggleBookmarkJob({{ $job->id }})"
                                        class="btn-modern btn-bookmark ws-btn ws-btn-outline {{ $count > 0 ? 'bookmarked ws-btn-success' : '' }}"
                                        aria-label="{{ $count > 0 ? 'Remove from bookmarks' : 'Bookmark for later' }}">
                                        <i class="fa-bookmark {{ $count > 0 ? 'fas' : 'far' }}"
                                            id="bookmarkJobIcon-{{ $job->id }}"></i>
                                        <span>{{ $count > 0 ? 'Bookmarked' : 'Bookmark' }}</span>
                                    </button>

                                    @if(Auth::user()->isKycVerified())
                                        <a href="{{ route('job.application.start', $job->id) }}"
                                            class="btn-modern btn-apply ws-btn ws-btn-primary ws-btn-lg">
                                            <i class="fas fa-paper-plane"></i>
                                            <span>Apply Now</span>
                                        </a>
                                    @else
                                        <a href="{{ route('kyc.index') }}" class="btn-modern btn-apply ws-btn ws-btn-primary ws-btn-lg">
                                            <i class="fas fa-lock"></i>
                                            <span>Complete KYC to Apply</span>
                                        </a>
                                    @endif
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn-modern btn-save ws-btn ws-btn-outline">
                                    <i class="far fa-heart"></i>
                                    <span>Save</span>
                                </a>
                                <a href="{{ route('login') }}" class="btn-modern btn-apply ws-btn ws-btn-primary ws-btn-lg">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Apply Now</span>
                                </a>
                            @endauth
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
                        <div class="overview-grid ws-grid"
                            style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                            <div class="overview-item ws-flex ws-items-center ws-gap-3">
                                <div class="overview-icon ws-stat-icon"
                                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fas fa-peso-sign"></i>
                                </div>
                                <div class="overview-content">
                                    <span class="overview-label ws-text-sm ws-text-secondary">Salary Range</span>
                                    <span
                                        class="overview-value ws-text-lg ws-font-semibold">{{ $job->salary_range ?? 'Negotiable' }}</span>
                                </div>
                            </div>

                            <div class="overview-item ws-flex ws-items-center ws-gap-3">
                                <div class="overview-icon ws-stat-icon"
                                    style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="overview-content">
                                    <span class="overview-label ws-text-sm ws-text-secondary">Job Type</span>
                                    <span
                                        class="overview-value ws-text-lg ws-font-semibold">{{ $job->jobType->name }}</span>
                                </div>
                            </div>

                            <div class="overview-item ws-flex ws-items-center ws-gap-3">
                                <div class="overview-icon ws-stat-icon"
                                    style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="overview-content">
                                    <span class="overview-label ws-text-sm ws-text-secondary">Vacancies</span>
                                    <span class="overview-value ws-text-lg ws-font-semibold">{{ $job->vacancy }}
                                        Position(s)</span>
                                </div>
                            </div>

                            <div class="overview-item ws-flex ws-items-center ws-gap-3">
                                <div class="overview-icon ws-stat-icon"
                                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="overview-content">
                                    <span class="overview-label ws-text-sm ws-text-secondary">Date Posted</span>
                                    <span
                                        class="overview-value ws-text-lg ws-font-semibold">{{ \Carbon\Carbon::parse($job->created_at)->format('M d, Y') }}</span>
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
                            {!! nl2br(e($job->description)) !!}
                        </div>
                    </div>
                </div>

                @if (!empty($job->responsibility))
                    <!-- Responsibilities -->
                    <div class="modern-card ws-card">
                        <div class="card-header-modern ws-card-header">
                            <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                                <i class="fas fa-tasks"></i> Key Responsibilities
                            </h2>
                        </div>
                        <div class="card-body-modern ws-card-body">
                            <div class="rich-text-content responsibility-list ws-text-base">
                                {!! nl2br(e($job->responsibility)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                @if (!empty($job->qualifications))
                    <!-- Qualifications -->
                    <div class="modern-card ws-card">
                        <div class="card-header-modern ws-card-header">
                            <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                                <i class="fas fa-graduation-cap"></i> Required Qualifications
                            </h2>
                        </div>
                        <div class="card-body-modern ws-card-body">
                            <div class="rich-text-content qualification-list ws-text-base">
                                {!! nl2br(e($job->qualifications)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                @if (!empty($job->benefits))
                    <!-- Benefits -->
                    <div class="modern-card ws-card">
                        <div class="card-header-modern ws-card-header">
                            <h2 class="section-title-modern ws-text-xl ws-font-semibold">
                                <i class="fas fa-gift"></i> Benefits & Perks
                            </h2>
                        </div>
                        <div class="card-body-modern ws-card-body">
                            <div class="rich-text-content benefits-list ws-text-base">
                                {!! nl2br(e($job->benefits)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Applicants Section -->
                @if (Auth::user() && Auth::user()->id == $job->user_id)
                    <div class="content-card">
                        <h3 class="mb-4">Applicants</h3>
                        @if ($applications->isNotEmpty())
                            <div class="applicants-list">
                                @foreach ($applications as $application)
                                    <div class="applicant-item">
                                        <div class="applicant-info">
                                            <h4>{{ $application->user->name }}</h4>
                                            <p class="mb-0">{{ $application->user->email }}</p>
                                            <p class="mb-0">{{ $application->user->mobile }}</p>
                                        </div>
                                        <div class="applicant-date">
                                            {{ \Carbon\Carbon::parse($application->applied_date)->format('d M, Y') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="no-applicants">
                                <i class="fas fa-users mb-3"></i>
                                <p>No applicants yet</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Reviews & Ratings Section -->
                @php
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
                @endphp

                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">Reviews & Ratings</h3>
                        @auth
                            @if(Auth::user()->role === 'jobseeker')
                                @if(!$hasApplied)
                                    <button type="button" class="btn btn-outline-secondary" disabled data-bs-toggle="tooltip"
                                        title="You need to apply to this job before you can write a review">
                                        <i class="fas fa-lock me-2"></i>Apply First to Review
                                    </button>
                                @elseif($canReviewJob || $canReviewCompany)
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                        <i class="fas fa-star me-2"></i>Write a Review
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary" disabled data-bs-toggle="tooltip"
                                        title="You have already reviewed this job and company">
                                        <i class="fas fa-check-circle me-2"></i>Already Reviewed
                                    </button>
                                @endif
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="fas fa-star me-2"></i>Login to Review
                            </a>
                        @endauth
                    </div>

                    <!-- Review Tabs -->
                    <ul class="nav nav-tabs mb-4" id="reviewTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="job-reviews-tab" data-bs-toggle="tab"
                                data-bs-target="#job-reviews" type="button" role="tab">
                                Job Reviews ({{ $jobReviews->count() }})
                                @if($jobAvgRating)
                                    <span class="badge bg-warning text-dark ms-2">
                                        <i class="fas fa-star"></i> {{ number_format($jobAvgRating, 1) }}
                                    </span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="company-reviews-tab" data-bs-toggle="tab"
                                data-bs-target="#company-reviews" type="button" role="tab">
                                Company Reviews ({{ $companyReviews->count() }})
                                @if($companyAvgRating)
                                    <span class="badge bg-warning text-dark ms-2">
                                        <i class="fas fa-star"></i> {{ number_format($companyAvgRating, 1) }}
                                    </span>
                                @endif
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="reviewTabsContent">
                        <!-- Job Reviews Tab -->
                        <div class="tab-pane fade show active" id="job-reviews" role="tabpanel">
                            @if($jobReviews->isEmpty())
                                <div class="no-reviews text-center py-5">
                                    <i class="fas fa-star-half-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No reviews yet. Be the first to review this job!</p>
                                </div>
                            @else
                                @foreach($jobReviews as $review)
                                    @include('components.review-card', ['review' => $review])
                                @endforeach
                            @endif
                        </div>

                        <!-- Company Reviews Tab -->
                        <div class="tab-pane fade" id="company-reviews" role="tabpanel">
                            @if($companyReviews->isEmpty())
                                <div class="no-reviews text-center py-5">
                                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No company reviews yet. Be the first to review this company!</p>
                                </div>
                            @else
                                @foreach($companyReviews as $review)
                                    @include('components.review-card', ['review' => $review])
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="col-lg-4">
                <!-- Sidebar wrapper for proper spacing -->
                <div class="sidebar-cards-wrapper">

                    <!-- Quick Apply Card -->
                    <div class="modern-card quick-apply-card">
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
                                        <span class="info-value">{{ $job->getFullAddress() ?: $job->location }}</span>
                                    </div>
                                </div>
                                <div class="apply-info-item">
                                    <i class="fas fa-calendar"></i>
                                    <div>
                                        <span class="info-label">Posted Date</span>
                                        <span
                                            class="info-value">{{ \Carbon\Carbon::parse($job->created_at)->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                <div class="apply-info-item">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <span class="info-label">Employment Type</span>
                                        <span class="info-value">{{ $job->jobType->name }}</span>
                                    </div>
                                </div>

                                <div class="divider-line"></div>

                                @auth
                                    @if(Auth::user()->role === 'jobseeker')
                                        @if(Auth::user()->isKycVerified())
                                            <a href="{{ route('job.application.start', $job->id) }}" class="btn-apply-sidebar">
                                                <i class="fas fa-paper-plane"></i>
                                                Apply for this Job
                                            </a>
                                        @else
                                            <a href="{{ route('kyc.index') }}" class="btn-apply-sidebar">
                                                <i class="fas fa-lock"></i>
                                                Complete KYC to Apply
                                            </a>
                                        @endif
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn-apply-sidebar">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Login to Apply
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Job Location Map -->
                    <div class="modern-card">
                        <div class="card-header-modern">
                            <h2 class="section-title-modern">
                                <i class="fas fa-map-marked-alt"></i> Job Location
                            </h2>
                        </div>
                        <div class="card-body-modern p-0">
                            @if($job->hasCoordinates())
                                <div id="job-location-map" class="job-location-map" data-lat="{{ $job->latitude }}"
                                    data-lng="{{ $job->longitude }}"
                                    data-address="{{ $job->getFullAddress() ?: $job->location }}"
                                    data-job-title="{{ $job->title }}">
                                </div>
                                <!-- Travel Time Info Panel -->
                                <div id="travel-time-panel" class="travel-time-panel">
                                    <div class="travel-time-header">
                                        <i class="fas fa-route"></i>
                                        <span>Travel Time to This Location</span>
                                    </div>
                                    <div id="travel-time-content" class="travel-time-content">
                                        <button id="get-directions-btn" class="get-directions-btn">
                                            <i class="fas fa-location-arrow"></i>
                                            <span>Get Directions from My Location</span>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="location-not-available">
                                    <div class="location-not-available-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <h4>Location Not Available</h4>
                                    <p>The exact location for this job hasn't been specified.</p>
                                    <div class="location-contact-hint">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Contact the employer for more details.</span>
                                    </div>
                                </div>
                            @endif
                            <div class="map-address-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $job->getFullAddress() ?: $job->location ?: 'Address not specified' }}</span>
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
                            @php
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
                                } else {
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

                                // Get company rating and review count
                                $companyRating = null;
                                $companyReviewCount = 0;
                                if ($job->employer) {
                                    $companyRating = \App\Models\Review::getCompanyAverageRating($job->employer->id);
                                    $companyReviewCount = \App\Models\Review::where('employer_id', $job->employer->id)
                                        ->where('review_type', 'company')
                                        ->count();
                                }
                            @endphp

                            <div class="company-details-list">
                                <div class="company-detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Company</span>
                                        <span class="detail-value">{{ $sidebarCompanyName }}</span>
                                    </div>
                                </div>

                                <!-- Company Star Rating -->
                                @if($companyRating !== null && $companyReviewCount > 0)
                                    <div class="company-detail-item company-rating-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Company Rating</span>
                                            <div class="company-rating-display">
                                                <div class="star-rating-wrapper">
                                                    @php
                                                        $fullStars = floor($companyRating);
                                                        $hasHalfStar = ($companyRating - $fullStars) >= 0.5;
                                                        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                                    @endphp
                                                    <div class="stars-container">
                                                        @for($i = 0; $i < $fullStars; $i++)
                                                            <i class="fas fa-star star-filled"></i>
                                                        @endfor
                                                        @if($hasHalfStar)
                                                            <i class="fas fa-star-half-alt star-filled"></i>
                                                        @endif
                                                        @for($i = 0; $i < $emptyStars; $i++)
                                                            <i class="far fa-star star-empty"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="rating-value">{{ number_format($companyRating, 1) }}</span>
                                                </div>
                                                <span class="review-count">({{ $companyReviewCount }}
                                                    {{ Str::plural('review', $companyReviewCount) }})</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="company-detail-item company-rating-item no-rating">
                                        <div class="detail-icon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Company Rating</span>
                                            <div class="company-rating-display">
                                                <div class="stars-container no-reviews">
                                                    @for($i = 0; $i < 5; $i++)
                                                        <i class="far fa-star star-empty"></i>
                                                    @endfor
                                                </div>
                                                <span class="no-reviews-text">No reviews yet</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($industry))
                                    <div class="company-detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-industry"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Industry</span>
                                            <span class="detail-value">{{ $industry }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($companySize))
                                    <div class="company-detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Company Size</span>
                                            <span class="detail-value">{{ $companySize }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($companyLocation))
                                    <div class="company-detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-map-pin"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Location</span>
                                            <span class="detail-value">{{ $companyLocation }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($job->employer->employerProfileDirect->founded_year))
                                    <div class="company-detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Founded</span>
                                            <span
                                                class="detail-value">{{ $job->employer->employerProfileDirect->founded_year }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($job->employer->employerProfileDirect->website))
                                    <div class="company-detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-globe"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Website</span>
                                            <a href="{{ $job->employer->employerProfileDirect->website }}" target="_blank"
                                                class="website-link-modern">
                                                Visit Website <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if (!empty($job->employer->employerProfileDirect->company_description))
                            <div class="company-description mt-4">
                                <h4>About the Company</h4>
                                <div class="content-text">
                                    {!! nl2br($job->employer->employerProfileDirect->company_description) !!}
                                </div>
                            </div>
                        @endif

                        @if (!empty($job->employer->employerProfileDirect->benefits_offered))
                            <div class="company-benefits mt-4">
                                <h4>Benefits Offered</h4>
                                <div class="benefits-list">
                                    @foreach($job->employer->employerProfileDirect->benefits_offered as $benefit)
                                        <span class="benefit-tag">
                                            <i class="fas fa-check-circle me-2"></i>{{ $benefit }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!empty($job->employer->employerProfileDirect->company_culture))
                            <div class="company-culture mt-4">
                                <h4>Company Culture</h4>
                                <div class="culture-list">
                                    @foreach($job->employer->employerProfileDirect->company_culture as $culture)
                                        <span class="culture-tag">
                                            <i class="fas fa-star me-2"></i>{{ $culture }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!empty($job->employer->employerProfileDirect->specialties))
                            <div class="company-specialties mt-4">
                                <h4>Specialties</h4>
                                <div class="specialties-list">
                                    @foreach($job->employer->employerProfileDirect->specialties as $specialty)
                                        <span class="specialty-tag">{{ $specialty }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div><!-- End sidebar-cards-wrapper -->
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applicationModalLabel">Apply for {{ $job->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="applicationForm" action="{{ route('jobs.apply', $job->id) }}" method="POST"
                        enctype="multipart/form-data" onsubmit="submitApplication(event)">
                        @csrf
                        <input type="hidden" name="job_id" value="{{ $job->id }}">
                        <div class="mb-3">
                            <label for="coverLetter" class="form-label">Cover Letter (Optional)</label>
                            <textarea class="form-control" id="coverLetter" name="cover_letter" rows="5"
                                placeholder="Tell us why you're a great fit for this position..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="resume" class="form-label">Resume (PDF, DOC, DOCX)</label>
                            <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx"
                                required>
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
                    <div class="alert alert-warning border-0 shadow-sm mb-4"
                        style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border-left: 4px solid #ffc107 !important;">
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
                                    Once submitted, your review cannot be edited or deleted. Please make sure your review is
                                    accurate and constructive.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form id="reviewForm">
                        @csrf
                        <input type="hidden" name="job_id" value="{{ $job->id }}">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Review Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="review_type" id="reviewTypeJob" value="job" {{ !$canReviewJob ? 'disabled' : 'checked' }}>
                                <label class="btn btn-outline-primary {{ !$canReviewJob ? 'disabled' : '' }}"
                                    for="reviewTypeJob">
                                    <i class="fas fa-briefcase me-2"></i>Job Review
                                    @if(!$canReviewJob)
                                        <span class="badge bg-secondary ms-2">Already Reviewed</span>
                                    @endif
                                </label>

                                <input type="radio" class="btn-check" name="review_type" id="reviewTypeCompany"
                                    value="company" {{ !$canReviewCompany ? 'disabled' : ($canReviewJob ? '' : 'checked') }}>
                                <label class="btn btn-outline-primary {{ !$canReviewCompany ? 'disabled' : '' }}"
                                    for="reviewTypeCompany">
                                    <i class="fas fa-building me-2"></i>Company Review
                                    @if(!$canReviewCompany)
                                        <span class="badge bg-secondary ms-2">Already Reviewed</span>
                                    @endif
                                </label>
                            </div>
                            <small class="form-text text-muted">Choose whether to review this specific job or the company
                                overall</small>
                            @if(!$canReviewJob && !$canReviewCompany)
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    You have already submitted reviews for both this job and company. Thank you for your
                                    feedback!
                                </div>
                            @endif
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
                            <label for="reviewTitle" class="form-label fw-bold">Review Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reviewTitle" name="title"
                                placeholder="Summarize your experience" required maxlength="200">
                            <div id="titleError" class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="reviewComment" class="form-label fw-bold">Your Review <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="reviewComment" name="comment" rows="5"
                                placeholder="Share your experience with this job/company..." required minlength="10"
                                maxlength="1000"></textarea>
                            <small class="form-text text-muted">Minimum 10 characters, maximum 1000 characters</small>
                            <div id="commentError" class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" id="isAnonymous"
                                    value="1">
                                <label class="form-check-label" for="isAnonymous">
                                    Post anonymously (your name will be hidden from public, but visible to employer)
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Reviews are published immediately and visible to everyone. Please be
                            honest and constructive.
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
            fetch('{{ route("account.reviews.store") }}', {
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
        .star-rating-input label:hover~label,
        .star-rating-input input[type="radio"]:checked~label {
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

@endsection

@push('styles')
    <link href="{{ asset('assets/css/job-detail-modern.css') }}?v={{ time() }}" rel="stylesheet">
    <!-- Mapbox GL JS CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css" rel="stylesheet">
    <style>
        /* Sidebar Cards Wrapper - Fix overlap issue */
        .sidebar-cards-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sidebar-cards-wrapper>.modern-card {
            margin-bottom: 0 !important;
            flex-shrink: 0;
        }

        /* Quick Apply Card */
        .quick-apply-card {
            position: relative;
            z-index: 5;
        }

        /* Job Location Map Card - Fix spacing and overflow */
        .modern-card:has(.job-location-map),
        .modern-card:has(#job-location-map) {
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .modern-card:has(.job-location-map) .card-body-modern,
        .modern-card:has(#job-location-map) .card-body-modern {
            padding: 0 !important;
        }

        /* Job Location Map Styles */
        .job-location-map {
            width: 100%;
            height: 450px;
            border-radius: 0;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .map-address-info {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-top: 2px solid #3b82f6;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        .map-address-info i {
            color: #ef4444;
            font-size: 1.25rem;
            margin-top: 2px;
            flex-shrink: 0;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }

        .map-address-info span {
            color: #1f2937;
            font-size: 0.95rem;
            line-height: 1.6;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        /* Location Not Available fallback */
        .location-not-available {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            text-align: center;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 250px;
        }

        .location-not-available-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .location-not-available-icon i {
            font-size: 2rem;
            color: #f59e0b;
        }

        .location-not-available h4 {
            color: #1f2937;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .location-not-available p {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            max-width: 280px;
        }

        .location-contact-hint {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.85rem;
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
        }

        .location-contact-hint i {
            color: #3b82f6;
        }

        /* Map marker pulse animation */
        .mapboxgl-marker {
            cursor: pointer;
        }

        .map-marker-container {
            position: relative;
        }

        .map-marker {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .map-marker::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .map-marker-pulse {
            position: absolute;
            width: 50px;
            height: 50px;
            background: rgba(239, 68, 68, 0.3);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: pulse-ring 2s infinite;
            z-index: -1;
        }

        @keyframes pulse-ring {
            0% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        /* Map popup styles */
        .mapboxgl-popup-content {
            padding: 12px 16px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            font-family: inherit;
        }

        .mapboxgl-popup-close-button {
            font-size: 18px;
            padding: 4px 8px;
            color: #6b7280;
        }

        .mapboxgl-popup-close-button:hover {
            color: #1f2937;
            background: transparent;
        }

        .map-popup-content {
            text-align: center;
        }

        .map-popup-content h4 {
            margin: 0 0 4px 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1f2937;
        }

        .map-popup-content p {
            margin: 0;
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* Map loading state */
        .map-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: #f8fafc;
        }

        .map-loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Map error state */
        .map-error {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 250px;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #991b1b;
            text-align: center;
            padding: 1rem;
        }

        .map-error i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #ef4444;
        }

        .map-error p {
            margin: 0;
            font-size: 0.9rem;
        }

        /* Navigation controls styling */
        .mapboxgl-ctrl-group {
            border-radius: 8px !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
        }

        .mapboxgl-ctrl-group button {
            width: 32px !important;
            height: 32px !important;
        }

        /* Responsive map height - Adjusted for better street visibility */
        @media (max-width: 768px) {
            .job-location-map {
                height: 350px;
                /* Increased from 250px for better mobile viewing */
            }

            .sidebar-cards-wrapper {
                gap: 1rem;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .job-location-map {
                height: 400px;
                /* Tablet size optimization */
            }
        }

        /* Travel Time Panel Styles */
        .travel-time-panel {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-top: 1px solid #bae6fd;
            padding: 1rem 1.25rem;
        }

        .travel-time-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            color: #0369a1;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .travel-time-header i {
            font-size: 1rem;
        }

        .travel-time-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .get-directions-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
        }

        .get-directions-btn:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .get-directions-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .get-directions-btn i {
            font-size: 0.85rem;
        }

        /* Travel Time Results */
        .travel-time-results {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .travel-mode-card {
            flex: 1;
            min-width: 140px;
            background: white;
            border-radius: 10px;
            padding: 0.875rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .travel-mode-card:hover {
            border-color: #0ea5e9;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
        }

        .travel-mode-card.active {
            border-color: #0ea5e9;
            background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
        }

        .travel-mode-icon {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .travel-mode-icon i {
            font-size: 1.25rem;
            color: #0ea5e9;
        }

        .travel-mode-icon span {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
        }

        .travel-time-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .travel-distance {
            font-size: 0.8rem;
            color: #64748b;
        }

        /* User Location Marker */
        .user-location-marker {
            width: 20px;
            height: 20px;
            background: #3b82f6;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.5);
            position: relative;
        }

        .user-location-marker::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            background: rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: user-pulse 2s infinite;
            z-index: -1;
        }

        @keyframes user-pulse {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(2);
                opacity: 0;
            }
        }

        /* Travel Loading State */
        .travel-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem;
            color: #64748b;
        }

        .travel-loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #e2e8f0;
            border-top-color: #0ea5e9;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* Travel Error State */
        .travel-error {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #dc2626;
            font-size: 0.85rem;
        }

        .travel-error i {
            flex-shrink: 0;
        }

        /* Open in Maps Button */
        .open-maps-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 0.625rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-top: 0.5rem;
        }

        .open-maps-btn:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            text-decoration: none;
        }

        /* Enhanced Job Pin Marker - Classic Red Pin Style with Precision Point */
        .job-pin-marker {
            position: relative;
            cursor: pointer;
            width: 50px;
            height: 65px;
            transition: transform 0.2s ease;
        }

        .job-pin-marker:hover {
            transform: scale(1.1);
        }

        .job-pin-icon {
            width: 50px;
            height: 65px;
            position: relative;
            filter: drop-shadow(0 6px 12px rgba(220, 38, 38, 0.5));
        }

        .job-pin-head {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.6),
                inset 0 2px 4px rgba(255, 255, 255, 0.3),
                0 0 0 1px rgba(220, 38, 38, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .job-pin-head::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            background: white;
            border-radius: 50%;
            transform: rotate(45deg);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .job-pin-head::before {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            background: #dc2626;
            border-radius: 50%;
            transform: rotate(45deg);
            z-index: 1;
        }

        .job-pin-head i {
            display: none;
        }

        /* Pin shadow on ground - More realistic */
        .job-pin-shadow {
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 8px;
            background: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(3px);
        }

        .job-pin-pulse {
            position: absolute;
            width: 70px;
            height: 70px;
            background: rgba(220, 38, 38, 0.25);
            border-radius: 50%;
            top: 5px;
            left: 50%;
            transform: translateX(-50%);
            animation: job-pin-pulse 2s ease-out infinite;
            z-index: -1;
        }

        @keyframes job-pin-pulse {
            0% {
                transform: translateX(-50%) scale(0.5);
                opacity: 1;
            }

            100% {
                transform: translateX(-50%) scale(1.5);
                opacity: 0;
            }
        }

        /* Mapbox Navigation Controls Enhancement */
        .mapboxgl-ctrl-group {
            background: white !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
            border: none !important;
        }

        .mapboxgl-ctrl-group button {
            width: 36px !important;
            height: 36px !important;
            border: none !important;
        }

        .mapboxgl-ctrl-group button:hover {
            background-color: #f3f4f6 !important;
        }

        .mapboxgl-ctrl-zoom-in .mapboxgl-ctrl-icon,
        .mapboxgl-ctrl-zoom-out .mapboxgl-ctrl-icon {
            background-size: 20px !important;
        }

        .mapboxgl-ctrl-fullscreen .mapboxgl-ctrl-icon {
            background-size: 18px !important;
        }

        /* Geolocate control styling */
        .mapboxgl-ctrl-geolocate .mapboxgl-ctrl-icon {
            background-size: 22px !important;
        }

        .mapboxgl-ctrl-geolocate:hover {
            background-color: #dbeafe !important;
        }

        /* Scale control styling */
        .mapboxgl-ctrl-scale {
            background-color: rgba(255, 255, 255, 0.9) !important;
            border-radius: 4px !important;
            border: 1px solid #e5e7eb !important;
            font-size: 11px !important;
            padding: 2px 6px !important;
            font-weight: 500;
        }

        /* Map Container Enhancement */
        .job-location-map {
            position: relative;
        }

        .job-location-map .mapboxgl-canvas {
            outline: none;
        }

        /* Map popup styling enhancement */
        .mapboxgl-popup-content {
            border-radius: 10px !important;
            padding: 12px 16px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
        }

        .mapboxgl-popup-close-button {
            font-size: 18px !important;
            padding: 4px 8px !important;
            color: #6b7280 !important;
        }

        .mapboxgl-popup-close-button:hover {
            background-color: #f3f4f6 !important;
            border-radius: 4px;
        }

        .map-popup-content h4 {
            font-size: 1.05rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .map-popup-content p {
            font-size: 0.9rem;
            color: #374151;
            margin: 0;
            line-height: 1.5;
        }

        .map-popup-content .popup-address {
            margin-bottom: 0.75rem;
            font-weight: 500;
        }

        .map-popup-content .popup-coordinates {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f0f9ff;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            border: 1px solid #bae6fd;
        }

        .map-popup-content .popup-coordinates i {
            color: #0284c7;
            font-size: 0.85rem;
        }

        .map-popup-content .popup-coordinates small {
            color: #0c4a6e;
            font-size: 0.8rem;
            line-height: 1.4;
        }

        .map-popup-content .popup-accuracy {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.5rem;
            background: #f0fdf4;
            border-radius: 6px;
            border: 1px solid #bbf7d0;
        }

        .map-popup-content .popup-accuracy i {
            color: #16a34a;
            font-size: 0.85rem;
        }

        .map-popup-content .popup-accuracy small {
            color: #15803d;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Responsive Travel Panel */
        @media (max-width: 576px) {
            .travel-time-results {
                flex-direction: column;
            }

            .travel-mode-card {
                min-width: 100%;
            }

            .get-directions-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <!-- jQuery and Bootstrap are already loaded in the layout -->
    <script type="text/javascript">
        // ============================================
        // Bookmark Job Functionality - MUST be outside $(document).ready() for inline onclick to work
        // ============================================
        function toggleBookmarkJob(jobId) {
            console.log('toggleBookmarkJob called with jobId:', jobId);

            @if(!Auth::check())
                window.location.href = '{{ route("login") }}';
                return;
            @endif

        const btn = document.getElementById(`bookmarkJobBtn-${jobId}`);
            const icon = document.getElementById(`bookmarkJobIcon-${jobId}`);

            if (!btn || !icon) {
                console.error('Bookmark button or icon not found for job:', jobId);
                return;
            }

            if (!btn.disabled) {
                btn.disabled = true;

                // Check if job is already bookmarked (check for 'fas' class or 'bookmarked' class on button)
                const isBookmarked = icon.classList.contains('fas') || btn.classList.contains('bookmarked');
                const route = isBookmarked ? '{{ route("jobs.unbookmark", ":id") }}' : '{{ route("jobs.bookmark", ":id") }}';
                const url = route.replace(':id', jobId);

                console.log('Sending AJAX request to:', url);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        console.log('AJAX success response:', response);
                        if (response.status) {
                            if (isBookmarked) {
                                // Job was bookmarked, now unbookmarked
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                                btn.querySelector('span').textContent = 'Bookmark';
                                btn.classList.remove('bookmarked', 'ws-btn-success');
                            } else {
                                // Job was not bookmarked, now bookmarked
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                                btn.querySelector('span').textContent = 'Bookmarked';
                                btn.classList.add('bookmarked', 'ws-btn-success');
                            }
                            // Show toast notification
                            if (typeof window.showToast === 'function') {
                                window.showToast(response.message || (isBookmarked ? 'Job removed from bookmarks!' : 'Job bookmarked!'), 'success');
                            }
                        } else {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                if (typeof window.showToast === 'function') {
                                    window.showToast(response.message || 'Error bookmarking job', 'error');
                                } else {
                                    alert(response.message || 'Error bookmarking job');
                                }
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', status, error, xhr.responseJSON);
                        const response = xhr.responseJSON;
                        if (response && response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            if (typeof window.showToast === 'function') {
                                window.showToast(response?.message || 'Error bookmarking job. Please try again.', 'error');
                            } else {
                                alert(response?.message || 'Error bookmarking job. Please try again.');
                            }
                        }
                    },
                    complete: function () {
                        btn.disabled = false;
                    }
                });
            }
        }

        $(document).ready(function () {
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
            $('#resume').on('change', function () {
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
            window.submitApplication = function (e) {
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
                    success: function (response) {
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
                    error: function (xhr, status, error) {
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
                    complete: function () {
                        // Reset button state
                        submitBtn.prop('disabled', false).html('Submit Application');
                    }
                });

                return false;
            };

            // Review System JavaScript
            $('#submitReview').on('click', function (e) {
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

                console.log('Sending AJAX request to:', '{{ route("account.reviews.store") }}');

                $.ajax({
                    url: '{{ route("account.reviews.store") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
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
                    error: function (xhr, status, error) {
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
                    complete: function () {
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Submit Review');
                    }
                });
            });

            // Delete review
            $(document).on('click', '.delete-review-btn', function () {
                if (!confirm('Are you sure you want to delete this review?')) {
                    return;
                }

                const reviewId = $(this).data('review-id');
                const btn = $(this);

                btn.prop('disabled', true);

                $.ajax({
                    url: `/account/reviews/${reviewId}`,
                    type: 'DELETE',
                    success: function (response) {
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
                    error: function () {
                        showAlert('Error deleting review. Please try again.', 'danger');
                        btn.prop('disabled', false);
                    }
                });
            });

            // Helpful button
            $(document).on('click', '.helpful-btn', function () {
                @guest
                    window.location.href = '{{ route("login") }}';
                    return;
                @endguest

            const reviewId = $(this).data('review-id');
                showAlert('Helpful feature coming soon!', 'info');
            });

            // Show toast for session flash messages (e.g., after job application)
            @if(session('success'))
                if (typeof window.showToast === 'function') {
                    window.showToast({!! json_encode(session('success')) !!}, 'success');
                }
            @endif

            @if(session('error'))
                if (typeof window.showToast === 'function') {
                    window.showToast({!! json_encode(session('error')) !!}, 'error');
                }
            @endif

            @if(session('info'))
                if (typeof window.showToast === 'function') {
                    window.showToast({!! json_encode(session('info')) !!}, 'info');
                }
            @endif
    });
    </script>

    <!-- Mapbox GL JS -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js"></script>
    <script>
        // Job Location Map Initialization with Travel Time
        document.addEventListener('DOMContentLoaded', function () {
            const mapContainer = document.getElementById('job-location-map');

            if (!mapContainer) {
                console.log('Map container not found - job may not have coordinates');
                return;
            }

            const jobLat = parseFloat(mapContainer.dataset.lat);
            const jobLng = parseFloat(mapContainer.dataset.lng);
            const address = mapContainer.dataset.address;
            const jobTitle = mapContainer.dataset.jobTitle || 'Job Location';

            // Validate coordinates
            if (isNaN(jobLat) || isNaN(jobLng)) {
                console.error('Invalid coordinates for map');
                mapContainer.innerHTML = `
                <div class="map-error">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>Unable to load map - Invalid coordinates</p>
                </div>
            `;
                return;
            }

            // Show loading state
            mapContainer.innerHTML = `
            <div class="map-loading">
                <div class="map-loading-spinner"></div>
            </div>
        `;

            // Mapbox access token - use the configured token
            const mapboxToken = '{{ config("mapbox.public_token") }}';

            if (!mapboxToken) {
                console.error('Mapbox token not configured');
                mapContainer.innerHTML = `
                <div class="map-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Map service unavailable</p>
                </div>
            `;
                return;
            }

            mapboxgl.accessToken = mapboxToken;

            let map;
            let userMarker = null;
            let routeLayerAdded = false;

            try {
                // Initialize the map with enhanced settings for street-level accuracy
                map = new mapboxgl.Map({
                    container: 'job-location-map',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [jobLng, jobLat],
                    zoom: 17, // Increased zoom for better street-level detail
                    pitch: 0, // Flat view for clearer street reading
                    bearing: 0,
                    attributionControl: false,
                    maxZoom: 20, // Allow zooming in very close
                    minZoom: 10 // Prevent zooming out too far
                });

                // Add navigation controls
                map.addControl(new mapboxgl.NavigationControl({
                    showCompass: false
                }), 'top-right');

                // Add fullscreen control
                map.addControl(new mapboxgl.FullscreenControl(), 'top-right');

                // Add geolocate control - allows users to see their location on the map
                map.addControl(new mapboxgl.GeolocateControl({
                    positionOptions: {
                        enableHighAccuracy: true
                    },
                    trackUserLocation: false,
                    showUserHeading: false,
                    showAccuracyCircle: false
                }), 'top-right');

                // Add scale control for distance reference
                map.addControl(new mapboxgl.ScaleControl({
                    maxWidth: 100,
                    unit: 'metric'
                }), 'bottom-left');

                // Add attribution control
                map.addControl(new mapboxgl.AttributionControl({
                    compact: true
                }));

                // Create enhanced job pin marker element - Classic Red Pin
                const markerEl = document.createElement('div');
                markerEl.className = 'job-pin-marker';
                markerEl.innerHTML = `
                <div class="job-pin-pulse"></div>
                <div class="job-pin-icon">
                    <div class="job-pin-head"></div>
                </div>
                <div class="job-pin-shadow"></div>
            `;

                // Add job location marker
                const jobMarker = new mapboxgl.Marker({
                    element: markerEl,
                    anchor: 'bottom'
                })
                    .setLngLat([jobLng, jobLat])
                    .addTo(map);

                // Create popup for job location with detailed information
                const popup = new mapboxgl.Popup({
                    offset: 35,
                    closeButton: true,
                    closeOnClick: false,
                    maxWidth: '320px'
                })
                    .setHTML(`
                <div class="map-popup-content">
                    <h4><i class="fas fa-briefcase me-1"></i> ${jobTitle}</h4>
                    <p class="popup-address"><i class="fas fa-map-marker-alt me-1 text-danger"></i> ${address}</p>
                    <div class="popup-coordinates">
                        <i class="fas fa-crosshairs me-1"></i>
                        <small>
                            <strong>Coordinates:</strong> ${jobLat.toFixed(6)}, ${jobLng.toFixed(6)}
                        </small>
                    </div>
                    <div class="popup-accuracy">
                        <i class="fas fa-check-circle me-1"></i>
                        <small>Precise street-level location</small>
                    </div>
                </div>
            `);

                // Show popup on marker click
                markerEl.addEventListener('click', function () {
                    jobMarker.setPopup(popup);
                    popup.addTo(map);
                });

                // Show popup by default on load
                map.on('load', function () {
                    popup.setLngLat([jobLng, jobLat]).addTo(map);

                    // Slight animation to center the marker with street-level zoom
                    map.flyTo({
                        center: [jobLng, jobLat],
                        zoom: 17,
                        duration: 1000,
                        essential: true // Animation will not be interrupted
                    });
                });

                // Handle map load errors
                map.on('error', function (e) {
                    console.error('Map error:', e);
                });

                // Travel Time Functionality
                const directionsBtn = document.getElementById('get-directions-btn');
                const travelTimeContent = document.getElementById('travel-time-content');

                if (directionsBtn) {
                    directionsBtn.addEventListener('click', function () {
                        getUserLocationAndCalculateRoute();
                    });
                }

                // Get user location and calculate route
                function getUserLocationAndCalculateRoute() {
                    if (!navigator.geolocation) {
                        showTravelError('Geolocation is not supported by your browser');
                        return;
                    }

                    // Show loading state
                    travelTimeContent.innerHTML = `
                    <div class="travel-loading">
                        <div class="travel-loading-spinner"></div>
                        <span>Getting your location...</span>
                    </div>
                `;

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const userLat = position.coords.latitude;
                            const userLng = position.coords.longitude;

                            // Add user location marker
                            addUserMarker(userLng, userLat);

                            // Calculate travel times for different modes
                            calculateAllTravelTimes(userLng, userLat, jobLng, jobLat);
                        },
                        function (error) {
                            let errorMessage = 'Unable to get your location';
                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage = 'Location access denied. Please enable location permissions.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage = 'Location information unavailable.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage = 'Location request timed out.';
                                    break;
                            }
                            showTravelError(errorMessage);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                }

                // Add user location marker to map
                function addUserMarker(lng, lat) {
                    // Remove existing user marker if any
                    if (userMarker) {
                        userMarker.remove();
                    }

                    const userMarkerEl = document.createElement('div');
                    userMarkerEl.className = 'user-location-marker';

                    userMarker = new mapboxgl.Marker({
                        element: userMarkerEl,
                        anchor: 'center'
                    })
                        .setLngLat([lng, lat])
                        .addTo(map);

                    // Fit bounds to show both markers
                    const bounds = new mapboxgl.LngLatBounds();
                    bounds.extend([lng, lat]);
                    bounds.extend([jobLng, jobLat]);

                    map.fitBounds(bounds, {
                        padding: { top: 50, bottom: 50, left: 50, right: 50 },
                        maxZoom: 14,
                        duration: 1000
                    });
                }

                // Calculate travel times for all modes
                async function calculateAllTravelTimes(userLng, userLat, destLng, destLat) {
                    travelTimeContent.innerHTML = `
                    <div class="travel-loading">
                        <div class="travel-loading-spinner"></div>
                        <span>Calculating travel times...</span>
                    </div>
                `;

                    const modes = ['driving', 'walking', 'cycling'];
                    const results = {};

                    try {
                        // Fetch all routes in parallel
                        const promises = modes.map(mode =>
                            fetchRoute(userLng, userLat, destLng, destLat, mode)
                        );

                        const routeResults = await Promise.all(promises);

                        modes.forEach((mode, index) => {
                            if (routeResults[index]) {
                                results[mode] = routeResults[index];
                            }
                        });

                        // Display results
                        displayTravelResults(results, userLng, userLat, destLng, destLat);

                        // Draw the driving route by default
                        if (results.driving && results.driving.geometry) {
                            drawRoute(results.driving.geometry);
                        }

                    } catch (error) {
                        console.error('Error calculating routes:', error);
                        showTravelError('Unable to calculate travel times. Please try again.');
                    }
                }

                // Fetch route from Mapbox Directions API
                async function fetchRoute(userLng, userLat, destLng, destLat, mode) {
                    const profile = mode === 'driving' ? 'driving' :
                        mode === 'walking' ? 'walking' : 'cycling';

                    const url = `https://api.mapbox.com/directions/v5/mapbox/${profile}/${userLng},${userLat};${destLng},${destLat}?geometries=geojson&access_token=${mapboxToken}`;

                    try {
                        const response = await fetch(url);
                        const data = await response.json();

                        if (data.routes && data.routes.length > 0) {
                            const route = data.routes[0];
                            return {
                                duration: route.duration, // in seconds
                                distance: route.distance, // in meters
                                geometry: route.geometry
                            };
                        }
                    } catch (error) {
                        console.error(`Error fetching ${mode} route:`, error);
                    }
                    return null;
                }

                // Display travel results
                function displayTravelResults(results, userLng, userLat, destLng, destLat) {
                    const modeIcons = {
                        driving: 'fa-car',
                        walking: 'fa-walking',
                        cycling: 'fa-bicycle'
                    };

                    const modeLabels = {
                        driving: 'Driving',
                        walking: 'Walking',
                        cycling: 'Cycling'
                    };

                    let html = '<div class="travel-time-results">';

                    for (const [mode, data] of Object.entries(results)) {
                        if (data) {
                            const duration = formatDuration(data.duration);
                            const distance = formatDistance(data.distance);

                            html += `
                            <div class="travel-mode-card ${mode === 'driving' ? 'active' : ''}" data-mode="${mode}">
                                <div class="travel-mode-icon">
                                    <i class="fas ${modeIcons[mode]}"></i>
                                    <span>${modeLabels[mode]}</span>
                                </div>
                                <div class="travel-time-value">${duration}</div>
                                <div class="travel-distance">${distance}</div>
                            </div>
                        `;
                        }
                    }

                    html += '</div>';

                    // Add "Open in Google Maps" button
                    const googleMapsUrl = `https://www.google.com/maps/dir/${userLat},${userLng}/${destLat},${destLng}`;
                    html += `
                    <a href="${googleMapsUrl}" target="_blank" class="open-maps-btn">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Open in Google Maps</span>
                    </a>
                `;

                    travelTimeContent.innerHTML = html;

                    // Add click handlers to switch routes
                    document.querySelectorAll('.travel-mode-card').forEach(card => {
                        card.addEventListener('click', function () {
                            const mode = this.dataset.mode;

                            // Update active state
                            document.querySelectorAll('.travel-mode-card').forEach(c => c.classList.remove('active'));
                            this.classList.add('active');

                            // Draw route for selected mode
                            if (results[mode] && results[mode].geometry) {
                                drawRoute(results[mode].geometry);
                            }
                        });
                    });
                }

                // Draw route on map
                function drawRoute(geometry) {
                    // Remove existing route layer if any
                    if (routeLayerAdded) {
                        if (map.getLayer('route')) map.removeLayer('route');
                        if (map.getSource('route')) map.removeSource('route');
                    }

                    map.addSource('route', {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            properties: {},
                            geometry: geometry
                        }
                    });

                    map.addLayer({
                        id: 'route',
                        type: 'line',
                        source: 'route',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#0ea5e9',
                            'line-width': 5,
                            'line-opacity': 0.8
                        }
                    });

                    routeLayerAdded = true;
                }

                // Format duration (seconds to human readable)
                function formatDuration(seconds) {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.round((seconds % 3600) / 60);

                    if (hours > 0) {
                        return `${hours}h ${minutes}m`;
                    }
                    return `${minutes} min`;
                }

                // Format distance (meters to human readable)
                function formatDistance(meters) {
                    if (meters >= 1000) {
                        return `${(meters / 1000).toFixed(1)} km`;
                    }
                    return `${Math.round(meters)} m`;
                }

                // Show travel error
                function showTravelError(message) {
                    travelTimeContent.innerHTML = `
                    <div class="travel-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>${message}</span>
                    </div>
                    <button id="get-directions-btn" class="get-directions-btn" style="margin-top: 0.5rem;">
                        <i class="fas fa-redo"></i>
                        <span>Try Again</span>
                    </button>
                `;

                    // Re-attach click handler
                    document.getElementById('get-directions-btn').addEventListener('click', function () {
                        getUserLocationAndCalculateRoute();
                    });
                }

            } catch (error) {
                console.error('Error initializing map:', error);
                mapContainer.innerHTML = `
                <div class="map-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load map</p>
                </div>
            `;
            }
        });
    </script>
@endpush

@push('scripts')
@endpush