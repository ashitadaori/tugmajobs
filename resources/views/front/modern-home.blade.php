@extends('front.layouts.app')

@section('title', 'TugmaJobs - Find Your Dream Job')

@push('scripts')
<script>
    // Force instant scroll to top on reload
    if (history.scrollRestoration) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
</script>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <!-- Role Toggle -->
                    <div class="hero-role-toggle mb-4">
                        <div class="toggle-wrapper">
                            <button type="button" class="toggle-btn active" data-role="jobseeker"
                                aria-label="I'm looking for a job">
                                <i class="fas fa-search me-2"></i>Find a Job
                            </button>
                            <button type="button" class="toggle-btn" data-role="employer" aria-label="I'm looking to hire">
                                <i class="fas fa-briefcase me-2"></i>Post a Job
                            </button>
                        </div>
                    </div>

                    <h1 class="hero-title">
                        <span class="hero-static-text">Find & Hire Experts for</span><br>
                        <span class="typing-container">
                            <span class="typing-text" id="typingText"></span>
                            <span class="typing-cursor">|</span>
                        </span>
                    </h1>
                    <p class="hero-subtitle">Find Jobs, Employment & Career Opportunities. Some of the companies we've
                        helped recruit excellent applicants over the years.</p>

                    <!-- Trust Indicators -->
                    <div class="trust-indicators mb-4">
                        <div class="trust-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Verified Employers</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-clock"></i>
                            <span>Fast Hiring</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-star"></i>
                            <span>Trusted by {{ number_format($stats['companies']) }}+ Companies</span>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="hero-search-form">
                        <form action="{{ route('jobs') }}" method="GET" class="search-form">
                            <div class="search-inputs">
                                <input type="text" name="keyword" class="form-control search-input"
                                    placeholder="Job Title or Keywords" value="{{ request('keyword') }}">
                                <div class="location-input-wrapper">
                                    <input type="text" name="location" id="locationSearch"
                                        class="form-control location-input"
                                        placeholder="Barangay or Area"
                                        value="{{ request('location') }}" autocomplete="off">
                                    <div id="locationSuggestions" class="location-suggestions"></div>
                                    <input type="hidden" name="location_lat" id="locationLat"
                                        value="{{ request('location_lat') }}">
                                    <input type="hidden" name="location_lng" id="locationLng"
                                        value="{{ request('location_lng') }}">
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
                        @foreach($popularKeywords as $keyword)
                            <a href="{{ route('jobs', ['keyword' => $keyword]) }}"
                                class="popular-tag">{{ ucfirst($keyword) }}</a>
                        @endforeach
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
                        <h3 class="ws-stat-number stat-number">{{ number_format($stats['total_jobs']) }}</h3>
                        <p class="ws-stat-label stat-label">Active Jobs</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ws-stat-box stat-box">
                        <div class="ws-stat-icon stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3 class="ws-stat-number stat-number">{{ number_format($stats['companies']) }}</h3>
                        <p class="ws-stat-label stat-label">Companies Hiring</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ws-stat-box stat-box">
                        <div class="ws-stat-icon stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="ws-stat-number stat-number">{{ number_format($stats['applications']) }}</h3>
                        <p class="ws-stat-label stat-label">Applications Submitted</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ws-stat-box stat-box">
                        <div class="ws-stat-icon stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="ws-stat-number stat-number">{{ number_format($stats['jobs_this_week']) }}</h3>
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
                    <p class="section-subtitle">Search all the open positions on the web. Get your own personalized
                        salary<br>estimate. Read reviews on over 30,000+ companies worldwide.</p>
                </div>
            </div>

            <div class="row g-4">
                @foreach($categories as $category)
                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('jobs', ['category' => $category->name]) }}" class="category-link" @guest
                            onclick="event.preventDefault(); switchToLogin(); new bootstrap.Modal(document.getElementById('authModal')).show();"
                        @endguest>
                            <div class="category-card ws-card">
                                <div class="category-icon">
                                    @if(strpos($category->icon, 'fa-') !== false)
                                        <i class="{{ $category->icon }}"></i>
                                    @elseif(strpos($category->icon, 'http') === 0)
                                        <img src="{{ $category->icon }}" alt="{{ $category->name }}" class="img-fluid">
                                    @else
                                        <span class="category-emoji">{{ $category->icon }}</span>
                                    @endif
                                </div>
                                <h4 class="category-title">{{ $category->name }}</h4>
                                <p class="category-count"><span class="ws-badge ws-badge-primary">{{ $category->jobs_count }}
                                        {{ Str::plural('Job', $category->jobs_count) }} Available</span></p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Featured Jobs Section -->
    <section class="popular-jobs-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title fw-bold">Featured Opportunities</h2>
                    <p class="section-subtitle text-muted">Discover amazing roles at innovative companies. Updated daily
                        with the best opportunities.</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="featured-jobs-carousel">
                        @forelse($featuredJobs as $job)
                            <div class="px-2 h-100">
                                <div class="featured-card">
                                    <!-- Header -->
                                    <div class="featured-header">
                                        <div class="company-logo-wrapper">
                                            @if($job->employer && $job->employer->employerProfile && $job->employer->employerProfile->company_logo)
                                                <img src="{{ asset('storage/' . $job->employer->employerProfile->company_logo) }}"
                                                    alt="{{ $job->employer->name }}"
                                                    onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-building text-muted\'></i>';">
                                            @else
                                                <i class="fas fa-building text-muted"></i>
                                            @endif
                                        </div>
                                        <div class="company-meta">
                                            <h6>{{ $job->employerCompany->company_name ?? $job->employer->employerProfile->company_name ?? 'Company' }}
                                            </h6>
                                            <span><i
                                                    class="far fa-clock me-1"></i>{{ $job->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="save-btn-corner">
                                            <x-save-job-button :job="$job" size="sm" :show-text="false"
                                                class="rounded border-0 bg-light text-secondary"
                                                style="width: 32px; height: 32px; padding: 0;" />
                                        </div>
                                    </div>

                                    <!-- Clickable Body Wrapper -->
                                    <a href="{{ route('jobDetail', $job->id) }}" class="job-title-link" @guest
                                        onclick="event.preventDefault(); switchToLogin(); new bootstrap.Modal(document.getElementById('authModal')).show();"
                                    @endguest>
                                        <h3 class="job-title-text">{{ $job->title }}</h3>
                                        <span class="job-location-text">
                                            <i
                                                class="fas fa-map-marker-alt me-2 text-muted"></i>{{ $job->location ?? 'Remote' }}
                                        </span>
                                    </a>

                                    <!-- Meta -->
                                    <div class="job-meta-row">
                                        <div class="job-type text-muted">
                                            <i class="fas fa-briefcase me-1"></i> {{ $job->jobType->name ?? 'Full Time' }}
                                        </div>
                                        <div class="salary-text">
                                            @if($job->salary_min && $job->salary_max)
                                                ₱{{ number_format($job->salary_min / 1000) }}k -
                                                ₱{{ number_format($job->salary_max / 1000) }}k
                                            @elseif($job->salary_min)
                                                From ₱{{ number_format($job->salary_min / 1000) }}k
                                            @else
                                                Negotiable
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Tags -->
                                    <div class="tags-row">
                                        @if($job->category)
                                            <span class="tag-pill purple">{{ $job->category->name }}</span>
                                        @endif
                                        <span class="tag-pill blue">{{ $job->jobType->name ?? 'Regular' }}</span>
                                        @if(stripos($job->location, 'Remote') !== false || stripos($job->title, 'Remote') !== false)
                                            <span class="tag-pill">Remote</span>
                                        @endif
                                        @if($job->vacancy > 1)
                                            <span class="tag-pill">{{ $job->vacancy }} Openings</span>
                                        @endif
                                    </div>

                                    <!-- Stats -->
                                    <div class="stats-row">
                                        <span class="me-3">{{ $job->applications_count ?? 0 }} applicants</span>
                                        <span>{{ $job->vacancy ?? 1 }} vacancy</span>
                                    </div>

                                    <!-- Footer Button -->
                                    <a href="{{ route('jobDetail', $job->id) }}" class="btn btn-view-details" @guest
                                        onclick="event.preventDefault(); switchToLogin(); new bootstrap.Modal(document.getElementById('authModal')).show();"
                                    @endguest>
                                        View Details <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 col-12">
                                <p class="text-muted">No featured opportunities available at the moment.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('jobs') }}" class="ws-btn ws-btn-primary ws-btn-lg btn btn-view-more" @guest
                            onclick="event.preventDefault(); switchToLogin(); new bootstrap.Modal(document.getElementById('authModal')).show();"
                        @endguest>
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
                @forelse($featuredCompanies as $company)
                    <div class="col-lg-4 col-md-6">
                        <div class="ws-company-card company-card ws-card">
                            <div class="company-header">
                                <div class="company-logo-wrapper ws-company-logo">
                                    @if($company->company_logo)
                                        <img src="{{ asset('storage/' . $company->company_logo) }}?v={{ time() }}"
                                            alt="{{ $company->company_name }}" class="company-logo-img"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default-company-logo.svg') }}';">
                                    @else
                                        <img src="{{ asset('images/default-company-logo.svg') }}" alt="{{ $company->company_name }}"
                                            class="company-logo-img default-logo">
                                    @endif
                                </div>
                                <div class="company-info">
                                    <h4 class="company-name ws-font-semibold">{{ $company->company_name }}</h4>
                                    <p class="company-location ws-text-secondary ws-text-sm">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        {{ $company->location ?? 'Location not specified' }}
                                    </p>
                                    @if($company->average_rating)
                                        <div class="company-rating mt-1">
                                            <div class="stars-display">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= floor($company->average_rating))
                                                        <i class="fas fa-star text-warning"></i>
                                                    @elseif($i - 0.5 <= $company->average_rating)
                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-warning"></i>
                                                    @endif
                                                @endfor
                                                <span class="rating-value ms-1">{{ $company->average_rating }}</span>
                                                <span class="reviews-count text-muted">({{ $company->reviews_count }}
                                                    {{ Str::plural('review', $company->reviews_count) }})</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="company-rating mt-1">
                                            <span class="no-reviews text-muted ws-text-sm">
                                                <i class="far fa-star me-1"></i>No reviews yet
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="company-description">
                                <p class="ws-text-secondary">
                                    {{ Str::limit($company->company_description ?? 'Company description not available', 120) }}
                                </p>
                            </div>

                            <div class="company-stats">
                                <div class="stat-item ws-flex ws-items-center ws-gap-2">
                                    <span class="stat-number ws-text-2xl ws-font-bold">{{ $company->jobs_count }}</span>
                                    <span class="stat-label ws-text-secondary ws-text-sm">Open
                                        {{ Str::plural('Job', $company->jobs_count) }}</span>
                                </div>
                            </div>

                            <div class="company-actions">
                                @if($company->type === 'standalone' && $company->slug)
                                    <a href="{{ route('companies.show', $company->slug) }}"
                                        class="ws-btn ws-btn-success btn-view-company">
                                        View Company
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                @elseif($company->type === 'standalone')
                                    <a href="{{ route('companies.show', $company->id) }}"
                                        class="ws-btn ws-btn-success btn-view-company">
                                        View Company
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                @else
                                    <a href="{{ route('companies.show', $company->id) }}"
                                        class="ws-btn ws-btn-success btn-view-company">
                                        View Company
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="ws-text-muted">No featured companies available at the moment.</p>
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('companies') }}" class="ws-btn ws-btn-primary ws-btn-lg btn btn-view-more">
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
                            <p class="step-description">Sign up in seconds using your email or Google account. Build your
                                professional profile.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <div class="step-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h4 class="step-title">Search & Apply</h4>
                            <p class="step-description">Browse thousands of jobs in Sta. Cruz, Davao del Sur and beyond.
                                Apply with one click.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <div class="step-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h4 class="step-title">Get Hired</h4>
                            <p class="step-description">Connect with employers, ace your interviews, and land your dream
                                job.</p>
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
                            <p class="step-description">Get qualified applications from talented professionals in your area.
                            </p>
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
                    <p class="cta-subtitle">Whether you're looking for your dream job or searching for top talent, TugmaJobs
                        connects you with the right opportunities in Sta. Cruz, Davao del Sur.</p>
                    <ul class="cta-benefits">
                        <li><i class="fas fa-check-circle"></i> Free job posting for employers</li>
                        <li><i class="fas fa-check-circle"></i> Access to qualified candidates</li>
                        <li><i class="fas fa-check-circle"></i> Easy job search for seekers</li>
                        <li><i class="fas fa-check-circle"></i> Fast and efficient process</li>
                    </ul>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="cta-buttons">
                        @auth
                            @if(Auth::user()->isEmployer())
                                <a href="{{ route('employer.jobs.create') }}" class="btn btn-cta btn-cta-employer">
                                    <i class="fas fa-plus-circle me-2"></i>Post a Job Now
                                </a>
                            @else
                                <a href="{{ route('jobs') }}" class="btn btn-cta btn-cta-jobseeker">
                                    <i class="fas fa-search me-2"></i>Apply for a Job
                                </a>
                            @endif
                        @else
                            <button type="button" class="btn btn-cta btn-cta-jobseeker" data-bs-toggle="modal"
                                data-bs-target="#authModal" onclick="switchToLogin()">
                                <i class="fas fa-search me-2"></i>Apply for a Job
                            </button>
                            <button type="button" class="btn btn-cta btn-cta-employer" data-bs-toggle="modal"
                                data-bs-target="#employerAuthModal" onclick="switchToEmployerLogin()">
                                <i class="fas fa-plus-circle me-2"></i>Post a Job Now
                            </button>
                        @endauth
                    </div>
                    <p class="cta-note mt-3">Join {{ number_format($stats['companies']) }}+ companies and thousands of job
                        seekers on TugmaJobs</p>
                </div>
            </div>
        </div>
    </section>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // TYPING EFFECT
            // ============================================
            const typingElement = document.getElementById('typingText');
            const jobTitles = [
                'Software Developer',
                'Marketing Manager',
                'Graphic Designer',
                'Sales Representative',
                'Customer Service',
                'Accountant',
                'HR Specialist',
                'Project Manager',
                'Data Analyst',
                'Teacher'
            ];

            let titleIndex =                 0;
            let charIndex = 0;
            let isDeleting = false;
            let typingSpeed = 100;

            function typeEffect() {
            if(!typingElement) return;

            const currentTitle = jobTitles[titleIndex];

            if(isDeleting) {
                typingElement.textContent = currentTitle.substring(0, charIndex - 1);
                charIndex--;
                typingSpeed = 50;
            } else {
                typingElement.textContent = currentTitle.substring(0, charIndex + 1);
                charIndex++;
            typingSpeed = 100;
        }

                                            if (!isDeleting && charIndex === currentTitle.length) {
            isDeleting = true;
            typingSpeed = 2000; // Pause at end
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            titleIndex = (titleIndex + 1) % jobTitles.length;
            typingSpeed = 500; // Pause before next word
        }

        setTimeout(typeEffect, typingSpeed);
                                        }

        // Start typing effect
        typeEffect();

        // ============================================
        // HERO ROLE TOGGLE
        // ============================================
        const toggleBtns = document.querySelectorAll('.hero-role-toggle .toggle-btn');
        const searchForm = document.querySelector('.hero-search-form');

        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                // Remove active from all
                toggleBtns.forEach(b => b.classList.remove('active'));
                // Add active to clicked
                this.classList.add('active');

                const role = this.dataset.role;

                if (role === 'employer') {
                    @auth
                        @if(Auth::user()->isEmployer())
                            window.location.href = '{{ route("employer.jobs.create") }}';
                        @else
                                                                                                        // Show modal for jobseekers
                                                                                                        const modal = new bootstrap.Modal(document.getElementById('jobseekerWarningModal'));
                            modal.show();
                            // Reset toggle
                            toggleBtns.forEach(b => b.classList.remove('active'));
                            document.querySelector('[data-role="jobseeker"]').classList.add('active');
                        @endif
                    @else
                                                                                                // Show employer auth modal
                                                                                                const modal = new bootstrap.Modal(document.getElementById('employerAuthModal'));
                            modal.show();
                            // Reset toggle
                            toggleBtns.forEach(b => b.classList.remove('active'));
                            document.querySelector('[data-role="jobseeker"]').classList.add('active');
                        @endauth
                                            }
            });
        });

        // ============================================
        // LOCATION SEARCH
        // ============================================
        const locationInput = document.getElementById('locationSearch');
        const suggestionsContainer = document.getElementById('locationSuggestions');
        const locationLatInput = document.getElementById('locationLat');
        const locationLngInput = document.getElementById('locationLng');

        let searchTimeout;
        let currentSuggestions = [];
        let selectedIndex = -1;

        // Handle location input
        locationInput.addEventListener('input', function () {
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
        locationInput.addEventListener('keydown', function (e) {
            if (!suggestionsContainer.style.display || suggestionsContainer.style.display === 'none') {
                return;
            }

            switch (e.key) {
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
        document.addEventListener('click', function (e) {
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
        locationInput.addEventListener('focus', function () {
            if (this.value.trim() === '') {
                currentSuggestions = popularLocations;
                displaySuggestions(popularLocations);
            }
        });

        // How It Works - Toggle between Jobseeker and Employer steps
        window.showJobseekerSteps = function () {
            document.getElementById('jobseekerSteps').style.display = 'block';
            document.getElementById('employerSteps').style.display = 'none';
        };

        window.showEmployerSteps = function () {
            document.getElementById('jobseekerSteps').style.display = 'none';
            document.getElementById('employerSteps').style.display = 'block';
        };
                                    });
    </script>

    <!-- Jobseeker Warning Modal -->
    @auth
        @if(!Auth::user()->isEmployer())
            <div class="modal fade" id="jobseekerWarningModal" tabindex="-1" aria-labelledby="jobseekerWarningModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content"
                        style="border-radius: 16px; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                        <div class="modal-header border-0 pb-0" style="padding: 1.5rem 1.5rem 0;">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center" style="padding: 1rem 2rem 2rem;">
                            <div
                                style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #d97706;"></i>
                            </div>
                            <h5 class="modal-title mb-3" id="jobseekerWarningModalLabel"
                                style="font-weight: 700; color: #1e293b; font-size: 1.5rem;">Employer Feature Only</h5>
                            <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 1.5rem;">
                                The "Post a Job" feature is exclusively available for employer accounts. As a jobseeker, you can
                                browse and apply for jobs instead.
                            </p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="{{ route('jobs') }}" class="btn"
                                    style="background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%); color: #0f172a; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 12px; border: none;">
                                    <i class="fas fa-search me-2"></i>Browse Jobs
                                </a>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                                    style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 500;">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- Job Location Map Modal -->
    <div class="modal fade" id="jobLocationModal" tabindex="-1" aria-labelledby="jobLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: 20px; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #78C841 0%, #5fb32e 100%); padding: 1.5rem;">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon me-3"
                            style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-map-marked-alt text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-white mb-1" id="jobLocationModalLabel">Job Location</h5>
                            <p class="text-white mb-0 opacity-75" id="jobLocationCompany" style="font-size: 0.9rem;"></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Job Info Bar -->
                    <div class="job-info-bar"
                        style="background: #f8fafc; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="mb-1" id="jobLocationTitle" style="color: #1e293b; font-weight: 600;"></h6>
                                <p class="mb-0" id="jobLocationAddress" style="color: #64748b; font-size: 0.9rem;">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <span></span>
                                </p>
                            </div>
                            <a href="#" id="jobLocationViewDetails" class="btn btn-sm"
                                style="background: linear-gradient(135deg, #78C841 0%, #5fb32e 100%); color: white; border-radius: 8px; font-weight: 500;">
                                View Job Details <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div id="jobLocationMapContainer" style="height: 400px; position: relative;">
                        <div id="jobLocationMap" style="width: 100%; height: 100%;"></div>
                        <!-- Loading Overlay -->
                        <div id="mapLoadingOverlay"
                            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; z-index: 10;">
                            <div class="text-center">
                                <div class="spinner-border text-success mb-3" role="status"
                                    style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p style="color: #64748b; font-size: 0.9rem;">Loading map...</p>
                            </div>
                        </div>
                        <!-- No Location Message -->
                        <div id="noLocationMessage"
                            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #f8fafc; display: none; align-items: center; justify-content: center; z-index: 10;">
                            <div class="text-center p-4">
                                <div
                                    style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fas fa-map-marker-alt" style="font-size: 2rem; color: #d97706;"></i>
                                </div>
                                <h5 style="color: #1e293b; font-weight: 600; margin-bottom: 0.5rem;">Location Not Available
                                </h5>
                                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">The exact location for
                                    this job hasn't been specified.</p>
                                <p style="color: #64748b; font-size: 0.85rem;"><i class="fas fa-info-circle me-1"></i>
                                    Contact the employer for more details.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding: 1rem 1.5rem; background: #f8fafc;">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-2" id="mapDirectionsLink"
                            style="display: none !important;">
                            <a href="#" id="getDirectionsBtn" target="_blank" class="btn btn-outline-primary btn-sm"
                                style="border-radius: 8px;">
                                <i class="fas fa-directions me-1"></i> Get Directions
                            </a>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                            style="border-radius: 8px;">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapbox CSS - Note: Already loaded in layout, this is for the modal map -->
    <!-- Mapbox JS - Note: Already loaded in layout -->

    <script>
        // Job Location Map Functionality
        let jobLocationMap = null;
        let jobLocationMarker = null;

        // Mapbox access token - get from config or use default
        const mapboxToken = '{{ config("mapbox.public_token") }}';

        function showJobLocationMap(jobId, jobTitle, location, latitude, longitude, companyName) {
            console.log('showJobLocationMap called:', { jobId, jobTitle, location, latitude, longitude, companyName });

            // Update modal content
            document.getElementById('jobLocationTitle').textContent = jobTitle;
            document.getElementById('jobLocationAddress').querySelector('span').textContent = location;
            document.getElementById('jobLocationCompany').textContent = companyName;
            document.getElementById('jobLocationViewDetails').href = '/jobs/detail/' + jobId;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('jobLocationModal'));
            modal.show();

            // Show loading, hide no location message
            document.getElementById('mapLoadingOverlay').style.display = 'flex';
            document.getElementById('noLocationMessage').style.display = 'none';

            // Check if we have valid coordinates
            const hasCoordinates = latitude !== null && longitude !== null && !isNaN(latitude) && !isNaN(longitude);

            if (!hasCoordinates) {
                // No coordinates available
                document.getElementById('mapLoadingOverlay').style.display = 'none';
                document.getElementById('noLocationMessage').style.display = 'flex';
                document.getElementById('mapDirectionsLink').style.display = 'none';
                return;
            }

            // Show directions link
            document.getElementById('mapDirectionsLink').style.display = 'flex';
            document.getElementById('getDirectionsBtn').href = `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}`;

            // Initialize or update the map
            setTimeout(() => {
                initializeJobLocationMap(latitude, longitude, jobTitle, companyName, location);
            }, 300);
        }

        function initializeJobLocationMap(lat, lng, jobTitle, companyName, address) {
            console.log('Initializing map at:', lat, lng);

            // Set Mapbox access token
            mapboxgl.accessToken = mapboxToken;

            const mapContainer = document.getElementById('jobLocationMap');

            // Remove existing map if any
            if (jobLocationMap) {
                jobLocationMap.remove();
                jobLocationMap = null;
            }

            try {
                // Create new map
                jobLocationMap = new mapboxgl.Map({
                    container: 'jobLocationMap',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [lng, lat],
                    zoom: 15,
                    attributionControl: false
                });

                // Add navigation controls
                jobLocationMap.addControl(new mapboxgl.NavigationControl(), 'top-right');

                // Add fullscreen control
                jobLocationMap.addControl(new mapboxgl.FullscreenControl(), 'top-right');

                // Create custom marker element
                const markerEl = document.createElement('div');
                markerEl.className = 'job-location-marker';
                markerEl.innerHTML = `
                                            <div class="marker-pin">
                                                <i class="fas fa-briefcase"></i>
                                            </div>
                                            <div class="marker-pulse"></div>
                                        `;

                // Add marker
                jobLocationMarker = new mapboxgl.Marker(markerEl)
                    .setLngLat([lng, lat])
                    .setPopup(
                        new mapboxgl.Popup({ offset: 25 })
                            .setHTML(`
                                                        <div class="map-popup">
                                                            <h6 style="margin: 0 0 5px 0; font-weight: 600; color: #1e293b;">${jobTitle}</h6>
                                                            <p style="margin: 0 0 5px 0; font-size: 0.85rem; color: #64748b;">${companyName}</p>
                                                            <p style="margin: 0; font-size: 0.8rem; color: #94a3b8;">
                                                                <i class="fas fa-map-marker-alt me-1"></i>${address}
                                                            </p>
                                                        </div>
                                                    `)
                    )
                    .addTo(jobLocationMap);

                // Show popup by default
                jobLocationMarker.togglePopup();

                // Hide loading overlay when map is loaded
                jobLocationMap.on('load', function () {
                    document.getElementById('mapLoadingOverlay').style.display = 'none';
                });

                // Handle map errors
                jobLocationMap.on('error', function (e) {
                    console.error('Map error:', e);
                    document.getElementById('mapLoadingOverlay').style.display = 'none';
                    document.getElementById('noLocationMessage').style.display = 'flex';
                    document.getElementById('noLocationMessage').querySelector('h5').textContent = 'Map Error';
                    document.getElementById('noLocationMessage').querySelector('p').textContent = 'Unable to load the map. Please try again.';
                });

            } catch (error) {
                console.error('Map initialization error:', error);
                document.getElementById('mapLoadingOverlay').style.display = 'none';
                document.getElementById('noLocationMessage').style.display = 'flex';
            }
        }

        // Clean up map when modal is hidden
        document.getElementById('jobLocationModal').addEventListener('hidden.bs.modal', function () {
            if (jobLocationMap) {
                jobLocationMap.remove();
                jobLocationMap = null;
            }
        });

        // Resize map when modal is shown (fixes rendering issues)
        document.getElementById('jobLocationModal').addEventListener('shown.bs.modal', function () {
            if (jobLocationMap) {
                jobLocationMap.resize();
            }
        });
    </script>

@endsection