@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Company Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="company-logo me-4">
                            @if($company->logo_url)
                                <img src="{{ asset($company->logo_url) }}" alt="{{ $company->company_name }}" 
                                    class="rounded-3" style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" 
                                    style="width: 100px; height: 100px;">
                                    <i class="fas fa-building text-muted fa-3x"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h1 class="h3 mb-2">{{ $company->company_name }}</h1>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                {{ $company->location ?? 'Location not specified' }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-briefcase me-2"></i>
                                {{ $activeJobs->total() }} {{ Str::plural('job', $activeJobs->total()) }} open
                            </p>
                        </div>
                    </div>

                    @if($company->company_description)
                        <div class="mb-4">
                            <h5 class="mb-3">About {{ $company->company_name }}</h5>
                            <p class="text-muted">{{ $company->company_description }}</p>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        @if($company->website)
                            <a href="{{ $company->website }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Visit Website
                            </a>
                        @endif
                        <button type="button" class="btn btn-primary" onclick="scrollToReviews()">
                            <i class="fas fa-star me-2"></i>
                            View Reviews
                            @php
                                $companyReviewCount = \App\Models\Review::where('employer_id', $company->user_id)
                                    ->where('review_type', 'company')
                                    ->count();
                                $companyAvgRating = \App\Models\Review::getCompanyAverageRating($company->user_id);
                            @endphp
                            @if($companyReviewCount > 0)
                                <span class="badge bg-warning text-dark ms-1">
                                    {{ number_format($companyAvgRating, 1) }} ⭐
                                </span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <!-- Company Reviews Section -->
            <div class="card border-0 shadow-sm mb-4" id="reviews-section">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-star text-warning me-2"></i>
                        Company Reviews
                        @if($companyReviewCount > 0)
                            <span class="badge bg-light text-dark ms-2">
                                {{ number_format($companyAvgRating, 1) }} ⭐ • {{ $companyReviewCount }} {{ Str::plural('review', $companyReviewCount) }}
                            </span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $companyReviews = \App\Models\Review::where('employer_id', $company->user_id)
                            ->where('review_type', 'company')
                            ->with('user', 'job')
                            ->orderBy('created_at', 'desc')
                            ->get();
                    @endphp

                    @if($companyReviews->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-star-half-alt text-muted fa-3x mb-3"></i>
                            <h5>No Reviews Yet</h5>
                            <p class="text-muted">Be the first to review this company!</p>
                            <p class="text-muted small">Apply to one of their jobs to leave a review.</p>
                        </div>
                    @else
                        @foreach($companyReviews as $review)
                            @include('components.review-card', ['review' => $review])
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Open Positions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Open Positions</h5>
                </div>
                <div class="card-body p-0">
                    @if($activeJobs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($activeJobs as $job)
                                <div class="list-group-item p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('jobDetail', $job->id) }}" class="text-dark text-decoration-none">
                                                    {{ $job->title }}
                                                </a>
                                            </h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt me-2"></i>{{ $job->location }}
                                                <span class="mx-2">&bull;</span>
                                                <i class="fas fa-clock me-2"></i>{{ $job->job_type }}
                                            </p>
                                            <p class="mb-0">{{ Str::limit($job->description, 150) }}</p>
                                        </div>
                                        <span class="badge bg-light text-dark">
                                            {{ $job->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-briefcase text-muted fa-3x mb-3"></i>
                            <h5>No Open Positions</h5>
                            <p class="text-muted">This company currently has no open positions.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pagination -->
            @if($activeJobs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            {{-- Previous Page Link --}}
                            @if ($activeJobs->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo; Previous</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $activeJobs->previousPageUrl() }}" rel="prev">&laquo; Previous</a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($activeJobs->getUrlRange(1, $activeJobs->lastPage()) as $page => $url)
                                @if ($page == $activeJobs->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($activeJobs->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $activeJobs->nextPageUrl() }}" rel="next">Next &raquo;</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">Next &raquo;</span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
                <div class="text-center text-muted mt-2">
                    <small>Showing {{ $activeJobs->firstItem() }} to {{ $activeJobs->lastItem() }} of {{ $activeJobs->total() }} results</small>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Company Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @if($company->industry)
                            <li class="mb-3">
                                <i class="fas fa-industry me-2 text-muted"></i>
                                <span class="text-muted">Industry:</span>
                                <br>
                                <strong>{{ $company->industry }}</strong>
                            </li>
                        @endif
                        
                        @if($company->company_size)
                            <li class="mb-3">
                                <i class="fas fa-users me-2 text-muted"></i>
                                <span class="text-muted">Company Size:</span>
                                <br>
                                <strong>{{ $company->company_size }}</strong>
                            </li>
                        @endif

                        @if($company->founded_year)
                            <li class="mb-3">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                <span class="text-muted">Founded:</span>
                                <br>
                                <strong>{{ $company->founded_year }}</strong>
                            </li>
                        @endif

                        @if($company->contact_email)
                            <li>
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <span class="text-muted">Email:</span>
                                <br>
                                <strong>{{ $company->contact_email }}</strong>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Social Links -->
            @if(isset($company->social_links['linkedin']) || isset($company->social_links['twitter']) || isset($company->social_links['facebook']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Connect With Us</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            @if(isset($company->social_links['linkedin']) && $company->social_links['linkedin'])
                                <a href="{{ $company->social_links['linkedin'] }}" target="_blank" 
                                    class="btn btn-outline-primary">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            @endif
                            
                            @if(isset($company->social_links['twitter']) && $company->social_links['twitter'])
                                <a href="{{ $company->social_links['twitter'] }}" target="_blank" 
                                    class="btn btn-outline-primary">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            @endif
                            
                            @if(isset($company->social_links['facebook']) && $company->social_links['facebook'])
                                <a href="{{ $company->social_links['facebook'] }}" target="_blank" 
                                    class="btn btn-outline-primary">
                                    <i class="fab fa-facebook"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function scrollToReviews() {
    const reviewsSection = document.getElementById('reviews-section');
    if (reviewsSection) {
        reviewsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Add a highlight effect
        reviewsSection.classList.add('highlight-section');
        setTimeout(() => {
            reviewsSection.classList.remove('highlight-section');
        }, 2000);
    }
}
</script>
@endpush

@push('styles')
<style>
.highlight-section {
    animation: highlight 2s ease-in-out;
}

@keyframes highlight {
    0%, 100% { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); }
    50% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.5); }
}

.btn-primary {
    background-color: #6366f1;
    border-color: #6366f1;
}

.btn-primary:hover {
    background-color: #4f46e5;
    border-color: #4f46e5;
}
</style>
@endpush 