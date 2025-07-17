@extends('front.layouts.app')

@push('styles')
<style>
/* HOMEPAGE FULLSCREEN STYLES - Override external CSS conflicts */
.main-content {
    margin-top: 0 !important;
    margin-left: 0 !important;
    padding: 0 !important;
    background: none !important;
    min-height: auto !important;
}

/* Override navbar positioning for homepage */
.navbar {
    position: fixed !important;
    top: 0 !important;
    width: 100% !important;
    z-index: 1030 !important;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(15px) !important;
    box-shadow: 0 2px 20px rgba(0,0,0,0.1) !important;
}

/* Ensure body doesn't have conflicting margins */
body {
    margin: 0 !important;
    padding: 0 !important;
    overflow-x: hidden !important;
}

/* Fix navbar height calculation */
.hero-carousel {
    height: 100vh;
    width: 100%;
    margin-top: 0;
}

/* Ensure proper navbar height on different screen sizes */
@media (min-width: 992px) {
    .hero-carousel {
        height: calc(100vh - 76px);
        margin-top: 76px;
    }
}

@media (max-width: 991.98px) {
    .hero-carousel {
        height: calc(100vh - 70px);
        margin-top: 70px;
    }
}

@media (max-width: 767.98px) {
    .hero-carousel {
        height: calc(100vh - 65px);
        margin-top: 65px;
    }
}

.hero-section {
    height: 100vh;
    display: flex;
    align-items: center;
    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
}

.search-section {
    margin-top: -120px;
    position: relative;
    z-index: 10;
    padding: 2rem 0 4rem 0;
}

/* Section backgrounds and spacing */
.stats-section { 
    background: #ffffff; 
    padding: 5rem 0; 
}

.categories-section { 
    background: #f8fafc; 
    padding: 5rem 0; 
}

.how-it-works { 
    background: #ffffff; 
    padding: 5rem 0; 
}

.featured-jobs { 
    background: #f8fafc; 
    padding: 5rem 0; 
}

.latest-jobs { 
    background: #ffffff; 
    padding: 5rem 0; 
}

.testimonials { 
    background: #f8fafc; 
    padding: 5rem 0; 
}

.cta-section { 
    background: linear-gradient(135deg, #3b82f6, #1d4ed8); 
    padding: 5rem 0; 
}

/* Hero content styling */
.hero-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-block;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    line-height: 1.1;
    color: white;
}

.hero-subtitle {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
}

.hero-btn {
    padding: 0.875rem 2rem;
    font-weight: 600;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

/* Floating stats cards */
.hero-stats-floating {
    position: relative;
}

.floating-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    animation: float 6s ease-in-out infinite;
}

.floating-card:nth-child(2) {
    animation-delay: -2s;
}

.floating-card:nth-child(3) {
    animation-delay: -4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

/* Search box styling */
.search-box {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.search-input-group {
    margin-bottom: 1rem;
}

.search-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.search-input {
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.5rem;
}

/* Trending tags */
.trending-tag {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    margin: 0.25rem;
    transition: all 0.2s ease;
}

.trending-tag:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-1px);
}

/* Stats cards */
.stats-card {
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

/* Category cards */
.category-card {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.category-icon {
    width: 4rem;
    height: 4rem;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.category-arrow {
    position: absolute;
    top: 1rem;
    right: 1rem;
    opacity: 0;
    transition: all 0.3s ease;
}

.category-card:hover .category-arrow {
    opacity: 1;
    transform: translateX(5px);
}

/* How it works */
.how-it-works-card {
    position: relative;
    padding: 2rem 1rem;
}

.step-number {
    position: absolute;
    top: -1rem;
    left: 50%;
    transform: translateX(-50%);
    width: 2.5rem;
    height: 2.5rem;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.125rem;
}

.step-icon {
    margin-top: 1rem;
}

/* Additional UI Enhancements */
.bg-gradient {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.section-title h2 {
    font-size: 2.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

.section-title p {
    font-size: 1.125rem;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
}

/* Job Card Enhancements */
.job-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.job-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.company-logo {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.125rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.new-job-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

/* Save Job Button */
.save-job-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.save-job-btn:hover {
    background-color: #fee2e2;
    border-color: #fca5a5;
}

.save-job-btn .fa-heart.text-danger {
    color: #dc2626 !important;
}

/* Testimonial Cards */
.testimonial-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stars i {
    font-size: 0.875rem;
    margin-right: 0.125rem;
}

.author-avatar {
    font-size: 2.5rem;
}

/* CTA Section Enhancement */
.cta-section {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.cta-section .container {
    position: relative;
    z-index: 2;
}

/* Carousel Enhancements */
.carousel-indicators {
    bottom: 2rem;
}

.carousel-indicators button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.5);
    background-color: transparent;
    transition: all 0.3s ease;
}

.carousel-indicators button.active {
    background-color: white;
    border-color: white;
}

.carousel-control-prev,
.carousel-control-next {
    width: 5%;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    opacity: 1;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 2rem;
    height: 2rem;
    background-size: 100%;
}

/* Mobile responsiveness */
@media (max-width: 991.98px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.125rem;
    }
    
    .search-section {
        margin-top: -80px;
        padding: 1.5rem 0 3rem 0;
    }
    
    .stats-section, .categories-section, .how-it-works,
    .featured-jobs, .latest-jobs, .testimonials,
    .cta-section {
        padding: 3rem 0;
    }
    
    .section-title h2 {
        font-size: 1.875rem;
    }
    
    .section-title p {
        font-size: 1rem;
    }
}

@media (max-width: 767.98px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .hero-actions .btn {
        width: 100%;
        margin-bottom: 0.75rem;
    }
    
    .search-section {
        margin-top: -60px;
        padding: 1rem 0 2rem 0;
    }
    
    .stats-section, .categories-section, .how-it-works,
    .featured-jobs, .latest-jobs, .testimonials,
    .cta-section {
        padding: 2.5rem 0;
    }
    
    .search-box {
        padding: 1.5rem;
    }
    
    .section-title h2 {
        font-size: 1.5rem;
    }
    
    .floating-card {
        margin-bottom: 1rem;
        padding: 1rem;
    }
    
    .company-logo {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>
@endpush

@section('content')

<!-- Enhanced Hero Section -->
<div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
    <div class="carousel-indicators">
        @foreach($bannerImages as $key => $banner)
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $key }}" class="{{ $key === 0 ? 'active' : '' }}" aria-current="{{ $key === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $key + 1 }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner h-100">
        @foreach($bannerImages as $key => $banner)
        <div class="carousel-item {{ $key === 0 ? 'active' : '' }} h-100">
            <div class="hero-section h-100" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.8), rgba(29, 78, 216, 0.9)), url('{{ asset('assets/images/' . $banner['image']) }}') no-repeat center center; background-size: cover;">
                <div class="container h-100 d-flex align-items-center">
                    <div class="row w-100 align-items-center">
                        <div class="col-12 col-lg-6 text-white">
                            <div class="hero-badge mb-3">
                                <i class="fas fa-star me-2"></i>
                                #1 TugmaJobs in Digos City
                            </div>
                            <h1 class="hero-title mb-4">{{ $banner['title'] }}</h1>
                            <p class="hero-subtitle mb-5">{{ $banner['subtitle'] }}</p>
                            <div class="hero-actions">
                                @guest
                                    <a href="{{ route('register') }}" class="btn btn-light btn-lg me-3 hero-btn">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Get Started Free
                                    </a>
                                    <a href="{{ route('jobs') }}" class="btn btn-outline-light btn-lg hero-btn">
                                        <i class="fas fa-search me-2"></i>
                                        Browse Jobs
                                    </a>
                                @else
                                    <a href="{{ route('jobs') }}" class="btn btn-light btn-lg me-3 hero-btn">
                                        <i class="fas fa-search me-2"></i>
                                        Find Jobs Now
                                    </a>
                                    @if(Auth::user()->isEmployer())
                                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-outline-light btn-lg hero-btn">
                                            <i class="fas fa-plus me-2"></i>
                                            Post a Job
                                        </a>
                                    @else
                                        <a href="{{ route('account.dashboard') }}" class="btn btn-outline-light btn-lg hero-btn">
                                            <i class="fas fa-tachometer-alt me-2"></i>
                                            My Dashboard
                                        </a>
                                    @endif
                                @endguest
                            </div>
                        </div>
                        <div class="col-12 col-lg-6 d-none d-lg-block">
                            <div class="hero-stats-floating">
                                <div class="floating-card">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="mb-0">{{ number_format($stats['total_jobs']) }}+</h4>
                                            <small>Active Jobs</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="floating-card">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="mb-0">{{ number_format($stats['companies']) }}+</h4>
                                            <small>Companies</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="floating-card">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="mb-0">{{ number_format($stats['applications']) }}+</h4>
                                            <small>Happy Candidates</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- Enhanced Search Box Section -->
<div class="search-section py-5 bg-gradient">
    <div class="container">
        <div class="search-box bg-white p-4 rounded-4 shadow-lg">
            <div class="text-center mb-4">
                <h3 class="mb-2">Find Your Perfect Job</h3>
                <p class="text-muted mb-0">Search from {{ number_format($stats['total_jobs']) }}+ active job opportunities</p>
            </div>
            
            <form action="{{ route('jobs') }}" method="GET" id="searchForm">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="search-input-group">
                            <label class="search-label">
                                <i class="fas fa-search text-primary"></i>
                                What job are you looking for?
                            </label>
                            <input type="text" name="keyword" id="keyword" class="form-control search-input" 
                                   placeholder="e.g. Software Engineer, Marketing Manager" 
                                   value="{{ Request::get('keyword') }}">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-input-group">
                            <label class="search-label">
                                <i class="fas fa-map-marker-alt text-success"></i>
                                Location
                            </label>
                            <x-location-input 
                                name="location" 
                                :value="Request::get('location', '')" 
                                placeholder="Search location in Digos City"
                                id="homepage-location"
                                class="form-select search-input"
                            />
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-input-group">
                            <label class="search-label">
                                <i class="fas fa-briefcase text-info"></i>
                                Job Type
                            </label>
                            <select name="job_type" id="job_type" class="form-select search-input">
                                <option value="">All Types</option>
                                @foreach($jobTypes as $jobType)
                                    <option value="{{ $jobType->id }}" {{ Request::get('job_type') == $jobType->id ? 'selected' : '' }}>
                                        {{ $jobType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="search-input-group">
                            <label class="search-label invisible">Search</label>
                            <button type="submit" class="btn btn-primary w-100 search-btn">
                                <i class="fas fa-search me-2"></i>
                                Search Jobs
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Search Toggle -->
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-link text-muted" data-bs-toggle="collapse" data-bs-target="#advancedSearch">
                        <i class="fas fa-sliders-h me-1"></i>
                        Advanced Search
                    </button>
                </div>
                
                <!-- Advanced Search Options -->
                <div class="collapse mt-3" id="advancedSearch">
                    <div class="row g-3 p-3 bg-light rounded-3">
                        <div class="col-md-3">
                            <label class="form-label small">Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}" {{ Request::get('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Salary Range</label>
                            <select name="salary_range" class="form-select form-select-sm">
                                <option value="">Any Salary</option>
                                <option value="0-25000" {{ Request::get('salary_range') == '0-25000' ? 'selected' : '' }}>₱0 - ₱25,000</option>
                                <option value="25000-50000" {{ Request::get('salary_range') == '25000-50000' ? 'selected' : '' }}>₱25,000 - ₱50,000</option>
                                <option value="50000-100000" {{ Request::get('salary_range') == '50000-100000' ? 'selected' : '' }}>₱50,000 - ₱100,000</option>
                                <option value="100000+" {{ Request::get('salary_range') == '100000+' ? 'selected' : '' }}>₱100,000+</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Experience Level</label>
                            <select name="experience" class="form-select form-select-sm">
                                <option value="">Any Experience</option>
                                <option value="entry" {{ Request::get('experience') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                                <option value="mid" {{ Request::get('experience') == 'mid' ? 'selected' : '' }}>Mid Level</option>
                                <option value="senior" {{ Request::get('experience') == 'senior' ? 'selected' : '' }}>Senior Level</option>
                                <option value="executive" {{ Request::get('experience') == 'executive' ? 'selected' : '' }}>Executive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Posted Date</label>
                            <select name="date_posted" class="form-select form-select-sm">
                                <option value="">Any Time</option>
                                <option value="today" {{ Request::get('date_posted') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ Request::get('date_posted') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ Request::get('date_posted') == 'month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Popular Searches & Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="text-center">
                    <p class="mb-2 text-muted">
                        <i class="fas fa-fire text-warning me-1"></i>
                        Trending Searches:
                    </p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        @if(isset($popularKeywords) && $popularKeywords->isNotEmpty())
                            @foreach($popularKeywords as $keyword)
                            <a href="{{ route('jobs', ['keyword' => $keyword]) }}" class="trending-tag">
                                <i class="fas fa-hashtag me-1"></i>{{ ucfirst($keyword) }}
                            </a>
                            @endforeach
                        @endif
                        @if(isset($trendingJobTypes) && $trendingJobTypes->isNotEmpty())
                            @foreach($trendingJobTypes->take(3) as $jobType)
                            <a href="{{ route('jobs', ['job_type' => $jobType->id]) }}" class="trending-tag">
                                <i class="fas fa-briefcase me-1"></i>{{ $jobType->name }}
                            </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <p class="mb-2 text-muted">
                        <i class="fas fa-bolt text-primary me-1"></i>
                        Quick Actions:
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        @auth
                            @if(Auth::user()->isJobSeeker())
                                <a href="{{ route('account.savedJobs') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-heart me-1"></i>Saved Jobs
                                </a>
                                <a href="{{ route('account.ai.job-match') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-robot me-1"></i>AI Match
                                </a>
                            @endif
                        @else
                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user-plus me-1"></i>Sign Up
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-sign-in-alt me-1"></i>Sign In
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Job Statistics Section -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <i class="fas fa-briefcase fa-2x text-primary mb-3"></i>
                    <h3 class="mb-2">{{ number_format($stats['total_jobs']) }}</h3>
                    <p class="text-muted mb-0">Active Jobs</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <i class="fas fa-building fa-2x text-success mb-3"></i>
                    <h3 class="mb-2">{{ number_format($stats['companies']) }}</h3>
                    <p class="text-muted mb-0">Companies</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <i class="fas fa-users fa-2x text-info mb-3"></i>
                    <h3 class="mb-2">{{ number_format($stats['applications']) }}</h3>
                    <p class="text-muted mb-0">Applications</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <i class="fas fa-chart-line fa-2x text-warning mb-3"></i>
                    <h3 class="mb-2">{{ number_format($stats['jobs_this_week']) }}</h3>
                    <p class="text-muted mb-0">New This Week</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced Categories Section -->
<section class="categories-section py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="mb-3">
                <i class="fas fa-th-large text-primary me-2"></i>
                Browse by Category
            </h2>
            <p class="text-muted">Discover opportunities across different industries and specializations</p>
        </div>
        <div class="row g-4">
            @foreach($categories as $category)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <a href="{{ route('jobs', ['category' => $category->id]) }}" class="text-decoration-none">
                    <div class="category-card bg-white p-4 rounded-4 shadow-sm text-center h-100 position-relative overflow-hidden">
                        <div class="category-icon-wrapper mb-3">
                            @php
                                $categoryIcons = [
                                    'Technology' => 'fa-laptop-code',
                                    'Healthcare' => 'fa-heartbeat',
                                    'Education' => 'fa-graduation-cap',
                                    'Finance' => 'fa-chart-line',
                                    'Marketing' => 'fa-bullhorn',
                                    'Sales' => 'fa-handshake',
                                    'Engineering' => 'fa-cogs',
                                    'Design' => 'fa-palette',
                                    'Human Resources' => 'fa-users',
                                    'Customer Service' => 'fa-headset',
                                    'Manufacturing' => 'fa-industry',
                                    'Retail' => 'fa-shopping-cart',
                                    'Construction' => 'fa-hard-hat',
                                    'Transportation' => 'fa-truck',
                                    'Food Service' => 'fa-utensils',
                                    'Legal' => 'fa-balance-scale',
                                    'Media' => 'fa-video',
                                    'Non-Profit' => 'fa-hands-helping',
                                    'Government' => 'fa-landmark',
                                    'Agriculture' => 'fa-seedling'
                                ];
                                $iconClass = $categoryIcons[$category->name] ?? $category->icon ?? 'fa-briefcase';
                            @endphp
                            <div class="category-icon">
                                <i class="fas {{ $iconClass }} fa-2x text-primary"></i>
                            </div>
                        </div>
                        <h5 class="mb-2 fw-bold">{{ $category->name }}</h5>
                        <p class="text-muted mb-3">{{ $category->jobs_count }} {{ Str::plural('opening', $category->jobs_count) }}</p>
                        <div class="category-arrow">
                            <i class="fas fa-arrow-right text-primary"></i>
                        </div>
                        <div class="category-bg-pattern"></div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        
        <!-- View All Categories Button -->
        <div class="text-center mt-5">
            <a href="{{ route('jobs') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-th me-2"></i>
                View All Categories
            </a>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works py-5">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="mb-3">
                <i class="fas fa-lightbulb text-warning me-2"></i>
                How It Works
            </h2>
            <p class="text-muted">Get started in just a few simple steps</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="how-it-works-card text-center">
                    <div class="step-number">1</div>
                    <div class="step-icon mb-3">
                        <i class="fas fa-user-plus fa-2x text-primary"></i>
                    </div>
                    <h5 class="mb-3">Create Account</h5>
                    <p class="text-muted">Sign up for free and build your professional profile in minutes</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="how-it-works-card text-center">
                    <div class="step-number">2</div>
                    <div class="step-icon mb-3">
                        <i class="fas fa-search fa-2x text-success"></i>
                    </div>
                    <h5 class="mb-3">Search Jobs</h5>
                    <p class="text-muted">Browse thousands of job opportunities that match your skills</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="how-it-works-card text-center">
                    <div class="step-number">3</div>
                    <div class="step-icon mb-3">
                        <i class="fas fa-paper-plane fa-2x text-info"></i>
                    </div>
                    <h5 class="mb-3">Apply Instantly</h5>
                    <p class="text-muted">Submit your application with just one click using our smart system</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="how-it-works-card text-center">
                    <div class="step-number">4</div>
                    <div class="step-icon mb-3">
                        <i class="fas fa-trophy fa-2x text-warning"></i>
                    </div>
                    <h5 class="mb-3">Get Hired</h5>
                    <p class="text-muted">Connect with employers and land your dream job</p>
                </div>
            </div>
        </div>
    </div>
</section>

@if($featuredJobs->isNotEmpty())
<section class="featured-jobs py-5">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="mb-3">Featured Jobs</h2>
            <p class="text-muted">Handpicked opportunities for you</p>
        </div>
        <div class="row g-4">
            @foreach($featuredJobs as $job)
            <div class="col-lg-4 col-md-6">
                <div class="card job-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="company-logo me-3 rounded-3 p-2" style="background-color: {{ '#' . substr(md5($job->employer->employerProfile->company_name ?? 'Company'), 0, 6) }}">
                                <span class="text-white">{{ strtoupper(substr($job->employer->employerProfile->company_name ?? 'C', 0, 1)) }}</span>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">{{ $job->title }}</h5>
                                <p class="text-muted mb-0 small">{{ $job->employer->employerProfile->company_name }}</p>
                            </div>
                        </div>
                        
                        <div class="job-meta mb-3">
                            <div class="d-flex align-items-center text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <span>{{ $job->getFullAddress() ?: $job->location }}</span>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-clock me-2"></i>
                                <span>{{ $job->jobType->name }}</span>
                            </div>
                            @if($job->salary_range || $job->salary)
                            <div class="d-flex align-items-center text-muted mt-2">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                <span>
                                    @if($job->salary_min && $job->salary_max)
                                        ₱{{ number_format($job->salary_min) }} - ₱{{ number_format($job->salary_max) }}
                                    @elseif($job->salary_range)
                                        {{ $job->salary_range }}
                                    @else
                                        {{ $job->salary }}
                                    @endif
                                </span>
                            </div>
                            @endif
                        </div>
                        
                        @if($job->deadline || $job->application_deadline)
                        <div class="job-deadline text-muted mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Deadline: {{ ($job->application_deadline ?? $job->deadline)?->format('M d, Y') ?? 'No deadline' }}
                        </div>
                        @endif
                        
                        <div class="d-flex gap-2">
                            <a href="{{ route('jobDetail', $job->id) }}" class="btn btn-outline-primary flex-grow-1">View Details</a>
                            <button onclick="toggleSaveJob({{ $job->id }})" class="btn btn-outline-secondary save-job-btn" id="saveJobBtn-{{ $job->id }}">
                                <i class="fas fa-heart {{ Auth::check() && $job->savedByUser(Auth::id()) ? 'text-danger' : '' }}" id="saveJobIcon-{{ $job->id }}"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Enhanced Latest Jobs Section -->
<section class="latest-jobs py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="mb-3">
                <i class="fas fa-clock text-success me-2"></i>
                Latest Job Opportunities
            </h2>
            <p class="text-muted">Fresh opportunities posted this week - don't miss out!</p>
        </div>
        
        <div class="row g-4">
            @foreach($latestJobs as $job)
            <div class="col-md-6">
                <div class="card job-card border-0 shadow-sm h-100 position-relative">
                    @if($job->created_at->diffInDays() <= 1)
                        <div class="new-job-badge">
                            <i class="fas fa-star me-1"></i>NEW
                        </div>
                    @endif
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="company-logo rounded-3 p-2" style="background-color: {{ '#' . substr(md5($job->employer->employerProfile->company_name ?? 'Company'), 0, 6) }}">
                                <span class="text-white fw-bold">{{ strtoupper(substr($job->employer->employerProfile->company_name ?? 'C', 0, 1)) }}</span>
                            </div>
                            <div class="job-info flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-1">{{ $job->title }}</h5>
                                    @if($job->featured)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    @endif
                                </div>
                                <p class="text-muted mb-2 small">
                                    <i class="fas fa-building me-1"></i>
                                    {{ $job->employer->employerProfile->company_name }}
                                </p>
                                
                                <div class="job-meta mb-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                                <span>{{ $job->getFullAddress() ?: $job->location }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="fas fa-clock me-2 text-info"></i>
                                                <span>{{ $job->jobType->name }}</span>
                                            </div>
                                        </div>
                                        @if($job->salary_min && $job->salary_max)
                                        <div class="col-6">
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="fas fa-money-bill-wave me-2 text-warning"></i>
                                                <span>₱{{ number_format($job->salary_min) }} - ₱{{ number_format($job->salary_max) }}</span>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-6">
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="fas fa-calendar me-2 text-primary"></i>
                                                <span>{{ $job->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 align-items-center">
                                    <a href="{{ route('jobDetail', $job->id) }}" class="btn btn-sm btn-primary flex-grow-1">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                    <button onclick="toggleSaveJob({{ $job->id }})" class="btn btn-sm btn-outline-secondary save-job-btn" id="saveJobBtn-{{ $job->id }}" title="Save Job">
                                        <i class="fas fa-heart {{ Auth::check() && $job->savedByUser(Auth::id()) ? 'text-danger' : '' }}" id="saveJobIcon-{{ $job->id }}"></i>
                                    </button>
                                    @auth
                                        @if(Auth::user()->isJobSeeker())
                                            <a href="{{ route('jobDetail', $job->id) }}#apply" class="btn btn-sm btn-success">
                                                <i class="fas fa-paper-plane me-1"></i>Apply
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- View All Jobs Button -->
        <div class="text-center mt-5">
            <a href="{{ route('jobs') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>
                View All Jobs ({{ number_format($stats['total_jobs']) }}+)
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials py-5">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="mb-3">
                <i class="fas fa-quote-left text-primary me-2"></i>
                Success Stories
            </h2>
            <p class="text-muted">Hear from job seekers and employers who found success with us</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="testimonial-content mb-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-0">"I found my dream job within a week of signing up! The AI job matching feature is incredible and saved me so much time."</p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar me-3">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Maria Santos</h6>
                            <small class="text-muted">Software Developer</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="testimonial-content mb-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-0">"As an employer, this platform helped us find qualified candidates quickly. The application management system is top-notch!"</p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar me-3">
                            <i class="fas fa-user-tie fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">John Dela Cruz</h6>
                            <small class="text-muted">HR Manager, TechCorp</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="testimonial-content mb-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-0">"The local focus on Digos City jobs is exactly what I needed. Finally, TugmaJobs understands our local market!"</p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar me-3">
                            <i class="fas fa-user-graduate fa-2x text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Ana Rodriguez</h6>
                            <small class="text-muted">Marketing Specialist</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="mb-3">Ready to Take the Next Step in Your Career?</h2>
                <p class="mb-4 lead">Join thousands of professionals who have found their perfect job match through our platform.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i>
                        Sign Up Free
                    </a>
                    <a href="{{ route('jobs') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>
                        Browse Jobs
                    </a>
                @else
                    @if(Auth::user()->isEmployer())
                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-light btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            Post Your First Job
                        </a>
                    @else
                        <a href="{{ route('account.ai.job-match') }}" class="btn btn-light btn-lg">
                            <i class="fas fa-robot me-2"></i>
                            Try AI Job Match
                        </a>
                    @endif
                @endguest
            </div>
        </div>
    </div>
</section>

<style>
/* CLEAN HOMEPAGE LAYOUT - FIXED MARGINS AND UI */

/* Remove horizontal scroll and fix fullscreen layout */
.homepage-fullscreen {
    width: 100%;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

/* Fix navbar positioning and styling */
.navbar {
    position: fixed !important;
    top: 0;
    width: 100%;
    z-index: 1030;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(15px);
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

/* Adjust body for fixed navbar */
body {
    padding-top: 76px;
    margin: 0;
    overflow-x: hidden;
}

/* Hero Section - Clean and Consistent */
.hero-carousel {
    height: calc(100vh - 76px);
    min-height: 600px;
    margin: 0;
    padding: 0;
    width: 100%;
}

.hero-section {
    height: 100%;
    min-height: inherit;
    display: flex;
    align-items: center;
    position: relative;
    padding: 0;
}

.hero-section .container {
    padding: 2rem 15px;
}

/* Search Section - Proper Positioning */
.search-section {
    margin-top: -120px;
    position: relative;
    z-index: 10;
    padding: 2rem 0 4rem 0;
}

/* Fix section margins and padding consistently */
.stats-section {
    padding: 5rem 0;
    margin: 0;
}

.categories-section {
    padding: 5rem 0;
    margin: 0;
    background-color: #f8fafc;
}

.how-it-works {
    padding: 5rem 0;
    margin: 0;
    background-color: #ffffff;
}

.featured-jobs {
    padding: 5rem 0;
    margin: 0;
    background-color: #f8fafc;
}

.latest-jobs {
    padding: 5rem 0;
    margin: 0;
    background-color: #ffffff;
}

.testimonials {
    padding: 5rem 0;
    margin: 0;
    background-color: #f8fafc;
}

.cta-section {
    padding: 5rem 0;
    margin: 0;
}

/* Fix container margins */
.container {
    margin-left: auto;
    margin-right: auto;
    padding-left: 15px;
    padding-right: 15px;
}

/* Fix floating cards positioning */
.hero-stats-floating {
    margin-top: 1rem;
}

.floating-card {
    margin-bottom: 1.5rem;
}

/* Fix search box positioning */
.search-box {
    margin: 0 auto;
    max-width: 100%;
}

/* Ensure proper spacing for all cards */
.job-card,
.category-card,
.stats-card,
.testimonial-card {
    margin-bottom: 2rem;
}

/* Fix button spacing */
.hero-actions .btn {
    margin-bottom: 1rem;
}

/* Mobile Responsiveness - Clean and Consistent */
@media (max-width: 991.98px) {
    body {
        padding-top: 70px;
    }
    
    .hero-carousel {
        height: calc(100vh - 70px);
        min-height: 500px;
    }
    
    .search-section {
        margin-top: -80px;
        padding: 1.5rem 0 3rem 0;
    }
    
    .stats-section,
    .categories-section,
    .how-it-works,
    .featured-jobs,
    .latest-jobs,
    .testimonials {
        padding: 3rem 0;
    }
    
    .cta-section {
        padding: 3rem 0;
    }
}

@media (max-width: 767.98px) {
    body {
        padding-top: 65px;
    }
    
    .hero-carousel {
        height: calc(100vh - 65px);
        min-height: 450px;
    }
    
    .hero-section .container {
        padding: 1rem 15px;
    }
    
    .search-section {
        margin-top: -60px;
        padding: 1rem 0 2rem 0;
    }
    
    .hero-title {
        font-size: 2.2rem;
        line-height: 1.2;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .hero-actions .btn {
        width: 100%;
        margin-bottom: 0.75rem;
    }
    
    .stats-section,
    .categories-section,
    .how-it-works,
    .featured-jobs,
    .latest-jobs,
    .testimonials {
        padding: 2.5rem 0;
    }
    
    .cta-section {
        padding: 2.5rem 0;
    }
}

.hero-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.hero-subtitle {
    font-size: 1.25rem;
    font-weight: 400;
    opacity: 0.9;
    line-height: 1.6;
}

.hero-btn {
    padding: 0.875rem 2rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hero-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.hero-stats-floating {
    position: relative;
    animation: float 6s ease-in-out infinite;
}

.floating-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.floating-card:hover {
    transform: translateX(10px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Search Section Styles - Remove duplicate rule */

.bg-gradient {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.search-box {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.search-input-group {
    margin-bottom: 1rem;
}

.search-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.search-label i {
    margin-right: 0.5rem;
}

.search-input {
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.search-btn {
    padding: 0.875rem 1.5rem;
    font-weight: 600;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border: none;
    transition: all 0.3s ease;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.trending-tag {
    display: inline-block;
    background: white;
    color: #374151;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    margin: 0.25rem;
}

.trending-tag:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Stats Section */
.stats-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.stats-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

/* Categories Section */
.category-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
}

.category-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.category-card:hover .category-arrow {
    transform: translateX(5px);
}

.category-icon-wrapper {
    position: relative;
    z-index: 2;
}

.category-icon {
    width: 80px;
    height: 80px;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.3s ease;
}

.category-card:hover .category-icon {
    background: rgba(59, 130, 246, 0.2);
    transform: scale(1.1);
}

.category-arrow {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    transition: all 0.3s ease;
}

.category-bg-pattern {
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
    z-index: 1;
}

/* How It Works Section */
.how-it-works-card {
    position: relative;
    padding: 2rem;
}

.step-number {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.step-icon {
    background: rgba(59, 130, 246, 0.1);
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.3s ease;
}

.how-it-works-card:hover .step-icon {
    background: rgba(59, 130, 246, 0.2);
    transform: scale(1.1);
}

/* Job Cards */
.job-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.job-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.new-job-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

/* Company Logo Styles */
.company-logo {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.125rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Testimonial Card Styles */
.testimonial-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stars i {
    font-size: 0.875rem;
    margin-right: 0.125rem;
}

.author-avatar {
    font-size: 2.5rem;
}

/* Save Job Button Styles */
.save-job-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.save-job-btn:hover {
    background-color: #fee2e2;
    border-color: #fca5a5;
}

.save-job-btn .fa-heart.text-danger {
    color: #dc2626 !important;
}

/* Section Title Styles */
.section-title h2 {
    font-size: 2.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

.section-title p {
    font-size: 1.125rem;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
}

/* CTA Section Enhancements */
.cta-section {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="20" cy="80" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.cta-section .container {
    position: relative;
    z-index: 2;
}

/* Fix any remaining layout issues */
.main-content {
    margin-top: 0 !important;
    margin-left: 0 !important;
    padding: 0 !important;
    background: none !important;
    min-height: auto !important;
}

/* Ensure no horizontal scroll */
html, body {
    overflow-x: hidden;
    max-width: 100%;
}

* {
    box-sizing: border-box;
}

/* Fix carousel indicators */
.carousel-indicators {
    bottom: 2rem;
}

.carousel-indicators button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.5);
    background-color: transparent;
    transition: all 0.3s ease;
}

.carousel-indicators button.active {
    background-color: white;
    border-color: white;
}

/* Fix carousel controls */
.carousel-control-prev,
.carousel-control-next {
    width: 5%;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    opacity: 1;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 2rem;
    height: 2rem;
    background-size: 100%;
}
</style>

@push('scripts')
<script>
// Save Job Functionality
function toggleSaveJob(jobId) {
    @auth
        console.log('Toggling save for job ID:', jobId);
        fetch(`/jobs/${jobId}/toggle-save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            const icon = document.getElementById(`saveJobIcon-${jobId}`);
            if (data.saved) {
                icon.classList.add('text-danger');
                console.log('Job saved - icon updated to red');
            } else {
                icon.classList.remove('text-danger');
                console.log('Job unsaved - icon updated to normal');
            }
        })
        .catch(error => {
            console.error('Error saving job:', error);
            alert('Error saving job. Please try again.');
        });
    @else
        window.location.href = '{{ route("login") }}';
    @endauth
}

// Initialize carousel with proper settings
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('heroCarousel');
    if (carousel) {
        new bootstrap.Carousel(carousel, {
            interval: 5000,
            wrap: true,
            pause: 'hover'
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.company-logo {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
}

.job-meta {
    font-size: 0.875rem;
}

.save-job-btn {
    width: 40px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    border-radius: 8px;
}

.save-job-btn:hover {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
    transform: scale(1.1);
}

.save-job-btn:hover i:not(.text-danger) {
    color: white;
}

.save-job-btn i.text-danger {
    color: #dc2626 !important;
}

/* Testimonials */
.testimonial-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.stars i {
    font-size: 0.875rem;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="10" cy="90" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.1;
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .search-section {
        margin-top: -100px;
    }
    
    .hero-stats-floating {
        margin-top: 3rem;
    }
    
    .floating-card {
        margin-bottom: 1rem;
    }
}

@media (max-width: 767.98px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .search-section {
        margin-top: -80px;
    }
    
    .search-input {
        padding: 0.75rem;
    }
    
    .trending-tag {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.slide-up {
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #3b82f6;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel
    var carousel = new bootstrap.Carousel(document.getElementById('heroCarousel'), {
        interval: 5000,
        wrap: true
    });
});

function toggleSaveJob(jobId) {
    @auth
        console.log('Toggling save for job ID:', jobId);
        fetch(`/jobs/${jobId}/toggle-save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            const icon = document.getElementById(`saveJobIcon-${jobId}`);
            if (data.saved) {
                icon.classList.add('text-danger');
                console.log('Job saved - icon updated to red');
            } else {
                icon.classList.remove('text-danger');
                console.log('Job unsaved - icon updated to normal');
            }
        })
        .catch(error => {
            console.error('Error saving job:', error);
            alert('Error saving job. Please try again.');
        });
    @else
        window.location.href = '{{ route("login") }}';
    @endauth
}
</script>
@endpush

</div>
<!-- End homepage-wrapper -->

@endsection
