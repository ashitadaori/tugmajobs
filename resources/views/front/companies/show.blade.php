@extends('front.layouts.app')

@section('page-title', $company->company_name . ' - Company Profile')

@section('content')
<div class="company-profile-wrapper">
    <!-- Company Hero Section -->
    <div class="company-hero">
        <div class="container">
            <div class="hero-content">
                <div class="company-hero-card">
                    <div class="company-logo-section">
                        @if($company->logo_url)
                            <img src="{{ asset($company->logo_url) }}"
                                 alt="{{ $company->company_name }}"
                                 class="company-hero-logo"
                                 onerror="this.onerror=null; this.src='{{ asset('images/default-company-logo.svg') }}';">
                        @else
                            <img src="{{ asset('images/default-company-logo.svg') }}"
                                 alt="{{ $company->company_name }}"
                                 class="company-hero-logo default-logo">
                        @endif
                    </div>
                    <div class="company-hero-info">
                        <h1 class="company-hero-name">{{ $company->company_name }}</h1>
                        <div class="company-meta">
                            <span class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $company->location ?? 'Location not specified' }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-briefcase"></i>
                                {{ $activeJobs->total() }} {{ Str::plural('job', $activeJobs->total()) }} open
                            </span>
                            @php
                                $companyReviewCount = \App\Models\Review::where('employer_id', $company->user_id)
                                    ->where('review_type', 'company')
                                    ->count();
                                $companyAvgRating = \App\Models\Review::getCompanyAverageRating($company->user_id);
                            @endphp
                            @if($companyReviewCount > 0)
                                <span class="meta-item rating-badge">
                                    <i class="fas fa-star"></i>
                                    {{ number_format($companyAvgRating, 1) }} ({{ $companyReviewCount }} {{ Str::plural('review', $companyReviewCount) }})
                                </span>
                            @endif
                        </div>
                        <div class="company-hero-actions">
                            @if($company->website)
                                <a href="{{ $company->website }}" target="_blank" class="btn-hero-action btn-website">
                                    <i class="fas fa-globe"></i>
                                    Visit Website
                                </a>
                            @endif
                            <button type="button" class="btn-hero-action btn-reviews" onclick="scrollToReviews()">
                                <i class="fas fa-star"></i>
                                View Reviews
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- About Section -->
                @if($company->company_description)
                    <div class="content-card mb-4">
                        <div class="card-header-custom">
                            <i class="fas fa-info-circle"></i>
                            <h3>About {{ $company->company_name }}</h3>
                        </div>
                        <div class="card-body-custom">
                            <p class="company-about-text">{{ $company->company_description }}</p>
                        </div>
                    </div>
                @endif

                <!-- Company Reviews Section -->
                <div class="content-card mb-4" id="reviews-section">
                    <div class="card-header-custom">
                        <i class="fas fa-star text-warning"></i>
                        <h3>Company Reviews</h3>
                        @if($companyReviewCount > 0)
                            <span class="header-badge">
                                {{ number_format($companyAvgRating, 1) }} <i class="fas fa-star text-warning"></i> &bull; {{ $companyReviewCount }} {{ Str::plural('review', $companyReviewCount) }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body-custom">
                        @php
                            $companyReviews = \App\Models\Review::where('employer_id', $company->user_id)
                                ->where('review_type', 'company')
                                ->with('user', 'job')
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp

                        @if($companyReviews->isEmpty())
                            <div class="empty-reviews">
                                <div class="empty-icon">
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <h5>No Reviews Yet</h5>
                                <p>Be the first to review this company!</p>
                                <span class="text-muted small">Apply to one of their jobs to leave a review.</span>
                            </div>
                        @else
                            <div class="reviews-list">
                                @foreach($companyReviews as $review)
                                    @include('components.review-card', ['review' => $review])
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Open Positions -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-briefcase"></i>
                        <h3>Open Positions</h3>
                        <span class="header-badge">{{ $activeJobs->total() }} {{ Str::plural('job', $activeJobs->total()) }}</span>
                    </div>
                    <div class="card-body-custom p-0">
                        @if($activeJobs->count() > 0)
                            <div class="jobs-list">
                                @foreach($activeJobs as $job)
                                    <a href="{{ route('jobDetail', $job->id) }}" class="job-item">
                                        <div class="job-item-content">
                                            <h5 class="job-title">{{ $job->title }}</h5>
                                            <div class="job-meta">
                                                <span class="job-meta-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    {{ $job->location }}
                                                </span>
                                                @if($job->jobType)
                                                    <span class="job-meta-item">
                                                        <i class="fas fa-clock"></i>
                                                        {{ $job->jobType->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="job-excerpt">{{ Str::limit($job->description, 120) }}</p>
                                        </div>
                                        <div class="job-item-action">
                                            <span class="job-posted">{{ $job->created_at->diffForHumans() }}</span>
                                            <span class="view-job-btn">
                                                View Job <i class="fas fa-arrow-right"></i>
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-jobs">
                                <div class="empty-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <h5>No Open Positions</h5>
                                <p>This company currently has no open positions.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pagination -->
                @if($activeJobs->hasPages())
                    <div class="pagination-wrapper">
                        {{ $activeJobs->links() }}
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Company Info Card -->
                <div class="sidebar-card mb-4">
                    <div class="sidebar-header">
                        <i class="fas fa-building"></i>
                        <h4>Company Information</h4>
                    </div>
                    <div class="sidebar-body">
                        <ul class="info-list">
                            @if($company->industry)
                                <li class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-industry"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Industry</span>
                                        <span class="info-value">{{ $company->industry }}</span>
                                    </div>
                                </li>
                            @endif

                            @if($company->company_size)
                                <li class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Company Size</span>
                                        <span class="info-value">{{ $company->company_size }}</span>
                                    </div>
                                </li>
                            @endif

                            @if($company->founded_year)
                                <li class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Founded</span>
                                        <span class="info-value">{{ $company->founded_year }}</span>
                                    </div>
                                </li>
                            @endif

                            @if($company->contact_email)
                                <li class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Email</span>
                                        <span class="info-value">{{ $company->contact_email }}</span>
                                    </div>
                                </li>
                            @endif

                            @if($company->company_phone)
                                <li class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Phone</span>
                                        <span class="info-value">{{ $company->company_phone }}</span>
                                    </div>
                                </li>
                            @endif

                            @if($company->location)
                                <li class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Location</span>
                                        <span class="info-value">{{ $company->location }}</span>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Social Links -->
                @if(isset($company->social_links) && (
                    (isset($company->social_links['linkedin']) && $company->social_links['linkedin']) ||
                    (isset($company->social_links['twitter']) && $company->social_links['twitter']) ||
                    (isset($company->social_links['facebook']) && $company->social_links['facebook']) ||
                    (isset($company->social_links['instagram']) && $company->social_links['instagram'])
                ))
                    <div class="sidebar-card">
                        <div class="sidebar-header">
                            <i class="fas fa-share-alt"></i>
                            <h4>Connect With Us</h4>
                        </div>
                        <div class="sidebar-body">
                            <div class="social-links">
                                @if(isset($company->social_links['linkedin']) && $company->social_links['linkedin'])
                                    <a href="{{ $company->social_links['linkedin'] }}" target="_blank" class="social-link linkedin">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                @endif

                                @if(isset($company->social_links['twitter']) && $company->social_links['twitter'])
                                    <a href="{{ $company->social_links['twitter'] }}" target="_blank" class="social-link twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                @endif

                                @if(isset($company->social_links['facebook']) && $company->social_links['facebook'])
                                    <a href="{{ $company->social_links['facebook'] }}" target="_blank" class="social-link facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                @endif

                                @if(isset($company->social_links['instagram']) && $company->social_links['instagram'])
                                    <a href="{{ $company->social_links['instagram'] }}" target="_blank" class="social-link instagram">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Company Location Map -->
                <div class="sidebar-card mb-4">
                    <div class="sidebar-header">
                        <i class="fas fa-map-marked-alt"></i>
                        <h4>Company Location</h4>
                    </div>
                    <div class="sidebar-body p-0">
                        @if($company->latitude && $company->longitude)
                            <div id="company-location-map" class="company-location-map"
                                 data-lat="{{ $company->latitude }}"
                                 data-lng="{{ $company->longitude }}"
                                 data-company-name="{{ $company->company_name }}"
                                 data-address="{{ $company->location }}">
                            </div>
                        @else
                            <div class="map-not-available">
                                <div class="map-not-available-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <p>Location coordinates not available</p>
                            </div>
                        @endif
                        <div class="map-address-bar">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $company->location ?? 'Address not specified' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <a href="{{ route('companies') }}" class="btn-back-companies">
                    <i class="fas fa-arrow-left"></i>
                    Back to Companies
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Company Profile Wrapper */
.company-profile-wrapper {
    background: #f8fafc;
    min-height: 100vh;
}

/* Hero Section */
.company-hero {
    background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
    padding: 3rem 0;
    position: relative;
    overflow: hidden;
}

.company-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}

.hero-content {
    position: relative;
    z-index: 1;
}

.company-hero-card {
    display: flex;
    align-items: center;
    gap: 2rem;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    padding: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.company-hero-logo {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    background: white;
}

.company-hero-logo.default-logo {
    padding: 12px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.company-hero-info {
    flex: 1;
}

.company-hero-name {
    color: white;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.company-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.95);
    font-size: 0.95rem;
    font-weight: 500;
}

.meta-item i {
    font-size: 0.85rem;
}

.meta-item.rating-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
}

.company-hero-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-hero-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-website {
    background: white;
    color: #059669;
}

.btn-website:hover {
    background: #f0fdf4;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.btn-reviews {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-reviews:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Content Cards */
.content-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header-custom {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border-bottom: 1px solid #d1fae5;
}

.card-header-custom i {
    font-size: 1.25rem;
    color: #059669;
}

.card-header-custom h3 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    flex: 1;
}

.header-badge {
    background: white;
    color: #059669;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.card-body-custom {
    padding: 1.5rem;
}

.card-body-custom.p-0 {
    padding: 0;
}

.company-about-text {
    color: #4b5563;
    line-height: 1.8;
    font-size: 1rem;
    margin: 0;
}

/* Empty States */
.empty-reviews,
.empty-jobs {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.empty-icon i {
    font-size: 2rem;
    color: #10b981;
}

.empty-reviews h5,
.empty-jobs h5 {
    color: #1f2937;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.empty-reviews p,
.empty-jobs p {
    color: #6b7280;
    margin-bottom: 0;
}

/* Jobs List */
.jobs-list {
    display: flex;
    flex-direction: column;
}

.job-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    text-decoration: none;
    transition: all 0.3s ease;
}

.job-item:last-child {
    border-bottom: none;
}

.job-item:hover {
    background: #f0fdf4;
}

.job-item-content {
    flex: 1;
    min-width: 0;
}

.job-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    transition: color 0.2s ease;
}

.job-item:hover .job-title {
    color: #059669;
}

.job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.job-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.job-meta-item i {
    color: #10b981;
    font-size: 0.8rem;
}

.job-excerpt {
    font-size: 0.9rem;
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

.job-item-action {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.75rem;
    margin-left: 1.5rem;
}

.job-posted {
    font-size: 0.8rem;
    color: #9ca3af;
    white-space: nowrap;
}

.view-job-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #059669;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.job-item:hover .view-job-btn {
    opacity: 1;
    transform: translateX(0);
}

/* Sidebar Cards */
.sidebar-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border-bottom: 1px solid #d1fae5;
}

.sidebar-header i {
    font-size: 1.125rem;
    color: #059669;
}

.sidebar-header h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.sidebar-body {
    padding: 1.5rem;
}

/* Info List */
.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.875rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-item:first-child {
    padding-top: 0;
}

.info-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-icon i {
    font-size: 1rem;
    color: #10b981;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.8rem;
    color: #9ca3af;
    font-weight: 500;
}

.info-value {
    font-size: 0.95rem;
    color: #1f2937;
    font-weight: 600;
}

/* Social Links */
.social-links {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.social-link {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link i {
    font-size: 1.25rem;
    color: white;
}

.social-link.linkedin {
    background: linear-gradient(135deg, #0077B5 0%, #0e76a8 100%);
}

.social-link.twitter {
    background: linear-gradient(135deg, #1DA1F2 0%, #0c85d0 100%);
}

.social-link.facebook {
    background: linear-gradient(135deg, #1877F2 0%, #0c5dc9 100%);
}

.social-link.instagram {
    background: linear-gradient(135deg, #E4405F 0%, #c13584 100%);
}

.social-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

/* Back Button */
.btn-back-companies {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 1rem 1.5rem;
    background: white;
    color: #059669;
    border: 2px solid #d1fae5;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.btn-back-companies:hover {
    background: #059669;
    color: white;
    border-color: #059669;
}

/* Company Location Map */
.company-location-map {
    width: 100%;
    height: 250px;
    border-radius: 0;
    overflow: hidden;
}

.map-not-available {
    padding: 2.5rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
}

.map-not-available-icon {
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.map-not-available-icon i {
    font-size: 1.5rem;
    color: #10b981;
}

.map-not-available p {
    color: #6b7280;
    margin: 0;
    font-size: 0.9rem;
}

.map-address-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border-top: 1px solid #d1fae5;
}

.map-address-bar i {
    color: #10b981;
    font-size: 1rem;
}

.map-address-bar span {
    color: #374151;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Map Marker Popup */
.company-map-popup {
    padding: 0.5rem;
}

.company-map-popup h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.company-map-popup p {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination-wrapper .pagination {
    gap: 0.5rem;
}

.pagination-wrapper .page-link {
    border-radius: 10px;
    border: none;
    padding: 0.625rem 1rem;
    color: #059669;
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.pagination-wrapper .page-link:hover {
    background: #059669;
    color: white;
}

.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
}

/* Highlight Animation */
.highlight-section {
    animation: highlight 2s ease-in-out;
}

@keyframes highlight {
    0%, 100% { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
    50% { box-shadow: 0 0 30px rgba(5, 150, 105, 0.4); }
}

/* Responsive */
@media (max-width: 768px) {
    .company-hero {
        padding: 2rem 0;
    }

    .company-hero-card {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem;
    }

    .company-hero-logo {
        width: 100px;
        height: 100px;
    }

    .company-hero-name {
        font-size: 1.5rem;
    }

    .company-meta {
        justify-content: center;
    }

    .company-hero-actions {
        justify-content: center;
    }

    .job-item {
        flex-direction: column;
        gap: 1rem;
    }

    .job-item-action {
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
        margin-left: 0;
    }

    .view-job-btn {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
@endsection

@push('scripts')
<script>
function scrollToReviews() {
    const reviewsSection = document.getElementById('reviews-section');
    if (reviewsSection) {
        reviewsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        reviewsSection.classList.add('highlight-section');
        setTimeout(() => {
            reviewsSection.classList.remove('highlight-section');
        }, 2000);
    }
}

// Company Location Map Initialization
document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('company-location-map');

    if (!mapContainer) {
        return;
    }

    const lat = parseFloat(mapContainer.dataset.lat);
    const lng = parseFloat(mapContainer.dataset.lng);
    const companyName = mapContainer.dataset.companyName || 'Company Location';
    const address = mapContainer.dataset.address || '';

    if (isNaN(lat) || isNaN(lng)) {
        console.log('Invalid coordinates for company map');
        return;
    }

    // Get Mapbox token from config
    const mapboxToken = '{{ config("services.mapbox.token", env("MAPBOX_TOKEN", "")) }}';

    if (!mapboxToken) {
        console.error('Mapbox token not configured');
        mapContainer.innerHTML = '<div class="map-not-available"><div class="map-not-available-icon"><i class="fas fa-map-marker-alt"></i></div><p>Map service unavailable</p></div>';
        return;
    }

    mapboxgl.accessToken = mapboxToken;

    try {
        // Initialize the map
        const map = new mapboxgl.Map({
            container: 'company-location-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [lng, lat],
            zoom: 15,
            attributionControl: false
        });

        // Add navigation controls
        map.addControl(new mapboxgl.NavigationControl({
            showCompass: false
        }), 'top-right');

        // Create custom marker element
        const markerEl = document.createElement('div');
        markerEl.className = 'company-custom-marker';
        markerEl.innerHTML = `
            <div style="
                width: 40px;
                height: 40px;
                background: linear-gradient(135deg, #059669 0%, #10b981 100%);
                border-radius: 50% 50% 50% 0;
                transform: rotate(-45deg);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
                border: 3px solid white;
            ">
                <i class="fas fa-building" style="
                    transform: rotate(45deg);
                    color: white;
                    font-size: 16px;
                "></i>
            </div>
        `;

        // Create popup content
        const popupContent = `
            <div class="company-map-popup">
                <h4>${companyName}</h4>
                <p><i class="fas fa-map-marker-alt" style="color: #10b981; margin-right: 6px;"></i>${address}</p>
            </div>
        `;

        // Add marker with popup
        const popup = new mapboxgl.Popup({
            offset: [0, -35],
            closeButton: false
        }).setHTML(popupContent);

        new mapboxgl.Marker(markerEl)
            .setLngLat([lng, lat])
            .setPopup(popup)
            .addTo(map);

        // Open popup by default
        map.on('load', function() {
            popup.addTo(map);
        });

    } catch (error) {
        console.error('Error initializing company map:', error);
        mapContainer.innerHTML = '<div class="map-not-available"><div class="map-not-available-icon"><i class="fas fa-map-marker-alt"></i></div><p>Unable to load map</p></div>';
    }
});
</script>
@endpush
