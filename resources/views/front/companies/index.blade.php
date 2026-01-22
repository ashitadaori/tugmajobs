@extends('front.layouts.app')

@section('page-title', 'Companies - Find Great Places to Work')

@section('content')
<div class="companies-page-wrapper">
    <!-- Hero Header Section -->
    <div class="companies-hero">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">Explore Companies</h1>
                <p class="hero-subtitle">Discover great places to work and find your dream job</p>
                <div class="companies-count-badge">
                    <i class="fas fa-building me-2"></i>
                    {{ $companies->total() }} {{ Str::plural('Company', $companies->total()) }} Hiring
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Companies Grid -->
        @if($companies->count() > 0)
            <div class="row g-4">
                @foreach($companies as $company)
                    @php
                        $isNew = $company->created_at >= now()->subDays(7);
                        $jobsCount = is_countable($company->jobs) ? count($company->jobs) : 0;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="company-card h-100">
                            <!-- New Badge -->
                            @if($isNew)
                                <div class="new-badge">
                                    <i class="fas fa-star me-1"></i> NEW
                                </div>
                            @endif

                            <div class="company-card-body">
                                <!-- Company Logo & Info -->
                                <div class="company-header">
                                    <div class="company-logo-wrapper">
                                        @if($company->logo_url)
                                            <img src="{{ asset($company->logo_url) }}"
                                                 alt="{{ $company->company_name }}"
                                                 class="company-logo"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/default-company-logo.svg') }}';">
                                        @else
                                            <img src="{{ asset('images/default-company-logo.svg') }}"
                                                 alt="{{ $company->company_name }}"
                                                 class="company-logo default-logo">
                                        @endif
                                    </div>
                                    <div class="company-info">
                                        <h5 class="company-name">
                                            <a href="{{ route('companies.show', $company->id) }}">
                                                {{ $company->company_name }}
                                            </a>
                                        </h5>
                                        <p class="company-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $company->location ?? 'Location not specified' }}
                                        </p>
                                        @if($company->average_rating)
                                            <div class="company-rating">
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
                                                    <span class="rating-value">{{ $company->average_rating }}</span>
                                                    <span class="reviews-count">({{ $company->reviews_count }} {{ Str::plural('review', $company->reviews_count) }})</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="company-rating">
                                                <span class="no-reviews">
                                                    <i class="far fa-star me-1"></i>No reviews yet
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Company Description -->
                                <p class="company-description">
                                    {{ Str::limit($company->company_description ?? 'This company is now hiring! Check out their profile to learn more about their culture and available positions.', 120) }}
                                </p>

                                <!-- Company Stats -->
                                <div class="company-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-briefcase"></i>
                                        <span>{{ $jobsCount }} Open {{ Str::plural('Job', $jobsCount) }}</span>
                                    </div>
                                    @if($company->created_at)
                                        <div class="stat-item">
                                            <i class="fas fa-calendar-check"></i>
                                            <span>Joined {{ $company->created_at->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Button -->
                                <a href="{{ route('companies.show', $company->id) }}" class="btn-view-company">
                                    <span>View Company</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($companies->hasPages())
                <div class="pagination-wrapper">
                    {{ $companies->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h4>No Companies Yet</h4>
                <p>Check back soon for new companies joining our platform!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        @endif
    </div>
</div>

<style>
/* Companies Page Wrapper */
.companies-page-wrapper {
    background: #f8fafc;
    min-height: 100vh;
}

/* Hero Section */
.companies-hero {
    background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
    padding: 4rem 0;
    position: relative;
    overflow: hidden;
}

.companies-hero::before {
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

.hero-title {
    color: white;
    font-size: 2.75rem;
    font-weight: 800;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hero-subtitle {
    color: rgba(255, 255, 255, 0.95);
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    font-weight: 400;
}

.companies-count-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Company Card */
.company-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.company-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(5, 150, 105, 0.15);
}

.company-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #059669, #10b981, #34d399);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.company-card:hover::before {
    opacity: 1;
}

.new-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.company-card-body {
    padding: 1.75rem;
}

/* Company Header */
.company-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.company-logo-wrapper {
    flex-shrink: 0;
}

.company-logo {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    object-fit: cover;
    border: 3px solid #f3f4f6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.company-card:hover .company-logo {
    transform: scale(1.05);
}

.company-logo.default-logo {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    padding: 8px;
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
    color: #059669;
}

.company-location {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.company-location i {
    color: #10b981;
    font-size: 0.8rem;
}

/* Company Rating */
.company-rating {
    display: flex;
    align-items: center;
}

.company-rating .stars-display {
    display: flex;
    align-items: center;
    gap: 2px;
    font-size: 0.85rem;
}

.company-rating .stars-display i {
    font-size: 0.8rem;
}

.company-rating .rating-value {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.85rem;
    margin-left: 6px;
}

.company-rating .reviews-count {
    font-size: 0.75rem;
    color: #6b7280;
    margin-left: 4px;
}

.company-rating .no-reviews {
    font-size: 0.8rem;
    color: #9ca3af;
}

/* Company Description */
.company-description {
    font-size: 0.9375rem;
    color: #4b5563;
    line-height: 1.7;
    margin-bottom: 1.25rem;
}

/* Company Stats */
.company-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border-radius: 12px;
    margin-bottom: 1.25rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #065f46;
    font-weight: 500;
}

.stat-item i {
    font-size: 0.9rem;
    color: #10b981;
}

/* View Button */
.btn-view-company {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
    border: none;
    padding: 0.875rem 1.5rem;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-view-company:hover {
    background: linear-gradient(135deg, #047857 0%, #059669 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(5, 150, 105, 0.35);
    color: white;
}

.btn-view-company i {
    transition: transform 0.3s ease;
}

.btn-view-company:hover i {
    transform: translateX(4px);
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
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
    transition: all 0.2s ease;
}

.pagination-wrapper .page-link:hover {
    background: #059669;
    color: white;
    transform: translateY(-2px);
}

.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.empty-icon i {
    font-size: 3rem;
    color: #10b981;
}

.empty-state h4 {
    color: #1f2937;
    font-weight: 700;
    margin-bottom: 0.75rem;
    font-size: 1.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.empty-state .btn-primary {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .companies-hero {
        padding: 3rem 0;
    }

    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .company-card-body {
        padding: 1.25rem;
    }

    .company-logo {
        width: 64px;
        height: 64px;
    }

    .company-stats {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
@endsection
