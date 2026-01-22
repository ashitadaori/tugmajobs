@extends('layouts.jobseeker')

@section('page-title', 'Bookmarked Jobs')

@section('jobseeker-content')
<style>
/* Bookmarked Jobs Professional Styles */
.bookmarked-jobs-pro {
    padding: 0;
}

/* Hero Banner Header */
.bookmarked-jobs-hero {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 1rem;
    padding: 1.75rem 2rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}

.bookmarked-jobs-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.bookmarked-jobs-hero .hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.bookmarked-jobs-hero .hero-text h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: white !important;
    margin: 0 0 0.375rem 0;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.bookmarked-jobs-hero .hero-text h1 i {
    font-size: 1.25rem;
    opacity: 0.9;
}

.bookmarked-jobs-hero .hero-text p {
    font-size: 0.9375rem;
    color: rgba(255, 255, 255, 0.9) !important;
    margin: 0;
}

.bookmarked-jobs-hero .hero-text p span,
.bookmarked-jobs-hero .hero-text p i {
    color: rgba(255, 255, 255, 0.9) !important;
}

.bookmarked-jobs-hero .bookmarked-count-badge {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 0.75rem 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.bookmarked-jobs-hero .bookmarked-count-badge .count-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
    line-height: 1;
}

.bookmarked-jobs-hero .bookmarked-count-badge .count-label {
    font-size: 0.8125rem;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.2;
}

/* Page Header - Alternative simple header */
.page-header-bookmarked {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #e5e7eb;
}

.page-header-bookmarked .header-content h1 {
    font-size: 1.375rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.375rem 0;
}

.page-header-bookmarked .header-content p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.page-header-bookmarked .bookmarked-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #eef2ff;
    color: #4f46e5;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.875rem;
}

.page-header-bookmarked .bookmarked-count i {
    font-size: 1rem;
}

/* Alerts */
.alert-pro {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    border: none;
}

.alert-pro.success {
    background: #d1fae5;
    color: #047857;
}

.alert-pro.danger {
    background: #fee2e2;
    color: #b91c1c;
}

.alert-pro .btn-close {
    margin-left: auto;
    opacity: 0.7;
}

/* Bookmarked Jobs List */
.bookmarked-jobs-list {
    background: white;
    border-radius: 0.875rem;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.bookmarked-job-card {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s ease;
}

.bookmarked-job-card:last-child {
    border-bottom: none;
}

.bookmarked-job-card:hover {
    background: #f9fafb;
}

.bookmarked-job-card .company-logo {
    width: 56px;
    height: 56px;
    border-radius: 0.625rem;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.bookmarked-job-card .company-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bookmarked-job-card .company-logo i {
    color: #9ca3af;
    font-size: 1.5rem;
}

.bookmarked-job-card .job-content {
    flex: 1;
    min-width: 0;
}

.bookmarked-job-card .job-title {
    font-size: 1.0625rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.375rem 0;
    text-decoration: none;
    display: block;
    transition: color 0.2s ease;
}

.bookmarked-job-card .job-title:hover {
    color: #4f46e5;
}

.bookmarked-job-card .company-name {
    font-size: 0.9375rem;
    color: #4b5563;
    margin: 0 0 0.625rem 0;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.bookmarked-job-card .company-name i {
    color: #9ca3af;
    font-size: 0.875rem;
}

.bookmarked-job-card .job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.bookmarked-job-card .meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.bookmarked-job-card .meta-item i {
    color: #9ca3af;
    font-size: 0.75rem;
}

.bookmarked-job-card .bookmarked-date {
    font-size: 0.75rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.bookmarked-job-card .job-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex-shrink: 0;
}

.bookmarked-job-card .btn-view {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.bookmarked-job-card .btn-view:hover {
    background: #4338ca;
    color: white;
    transform: translateY(-1px);
}

.bookmarked-job-card .btn-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: transparent;
    color: #dc2626;
    border: 1px solid #fecaca;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.bookmarked-job-card .btn-remove:hover {
    background: #fef2f2;
    border-color: #dc2626;
}

.bookmarked-job-card .applicant-count {
    font-size: 0.75rem;
    color: #9ca3af;
    text-align: center;
    margin-top: 0.25rem;
}

/* Empty State */
.empty-state-bookmarked {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 0.875rem;
    border: 1px solid #e5e7eb;
}

.empty-state-bookmarked .empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state-bookmarked .empty-icon i {
    font-size: 2.5rem;
    color: #6366f1;
}

.empty-state-bookmarked h3 {
    font-size: 1.375rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.empty-state-bookmarked p {
    font-size: 1rem;
    color: #6b7280;
    margin: 0 0 1.5rem 0;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state-bookmarked .btn-browse {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    background: #4f46e5;
    color: white;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.empty-state-bookmarked .btn-browse:hover {
    background: #4338ca;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.25);
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

.pagination-wrapper .pagination {
    display: flex;
    gap: 0.375rem;
}

.pagination-wrapper .page-item .page-link {
    padding: 0.5rem 0.875rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #4b5563;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.pagination-wrapper .page-item .page-link:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.pagination-wrapper .page-item.active .page-link {
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .bookmarked-jobs-hero {
        padding: 1.5rem;
    }

    .bookmarked-jobs-hero .hero-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .bookmarked-jobs-hero .hero-text h1 {
        font-size: 1.25rem;
    }

    .bookmarked-jobs-hero .bookmarked-count-badge {
        align-self: stretch;
        justify-content: center;
    }

    .page-header-bookmarked {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .bookmarked-job-card {
        flex-direction: column;
        gap: 1rem;
    }

    .bookmarked-job-card .company-logo {
        width: 48px;
        height: 48px;
    }

    .bookmarked-job-card .job-actions {
        flex-direction: row;
        width: 100%;
    }

    .bookmarked-job-card .btn-view,
    .bookmarked-job-card .btn-remove {
        flex: 1;
    }

    .bookmarked-job-card .applicant-count {
        display: none;
    }
}
</style>

<div class="bookmarked-jobs-pro">
    <!-- Hero Banner Header -->
    <div class="bookmarked-jobs-hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1><i class="fas fa-bookmark"></i> My Bookmarked Jobs</h1>
                <p>Manage your bookmarked job opportunities</p>
            </div>
            <div class="bookmarked-count-badge">
                <div class="count-number">{{ $bookmarkedJobs->total() }}</div>
                <div class="count-label">Jobs<br>Bookmarked</div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert-pro success" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-pro danger" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($bookmarkedJobs->count() > 0)
        <!-- Bookmarked Jobs List -->
        <div class="bookmarked-jobs-list">
            @foreach($bookmarkedJobs as $bookmarkedJob)
                @if($bookmarkedJob->job)
                <div class="bookmarked-job-card" data-job-id="{{ $bookmarkedJob->job->id }}">
                    <div class="company-logo">
                        @if($bookmarkedJob->job->company && $bookmarkedJob->job->company->logo)
                            <img src="{{ asset('storage/' . $bookmarkedJob->job->company->logo) }}" alt="{{ $bookmarkedJob->job->company->name }}">
                        @elseif($bookmarkedJob->job->employer && $bookmarkedJob->job->employer->image)
                            <img src="{{ asset('profile_img/' . $bookmarkedJob->job->employer->image) }}" alt="{{ $bookmarkedJob->job->employer->name }}">
                        @else
                            <i class="fas fa-building"></i>
                        @endif
                    </div>
                    <div class="job-content">
                        <a href="{{ route('jobDetail', $bookmarkedJob->job->id) }}" class="job-title">
                            {{ $bookmarkedJob->job->title }}
                        </a>
                        <p class="company-name">
                            <i class="fas fa-building"></i>
                            {{ $bookmarkedJob->job->company->name ?? $bookmarkedJob->job->employer->name ?? 'Company' }}
                        </p>
                        <div class="job-meta">
                            <span class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $bookmarkedJob->job->location ?? 'Location not specified' }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-briefcase"></i>
                                {{ $bookmarkedJob->job->jobType->name ?? 'N/A' }}
                            </span>
                            @if($bookmarkedJob->job->salary_min && $bookmarkedJob->job->salary_max)
                            <span class="meta-item">
                                <i class="fas fa-peso-sign"></i>
                                {{ number_format($bookmarkedJob->job->salary_min) }} - {{ number_format($bookmarkedJob->job->salary_max) }}
                            </span>
                            @endif
                        </div>
                        <div class="bookmarked-date">
                            <i class="far fa-clock"></i>
                            Bookmarked {{ $bookmarkedJob->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="job-actions">
                        <a href="{{ route('jobDetail', $bookmarkedJob->job->id) }}" class="btn-view">
                            <i class="fas fa-eye"></i> View Job
                        </a>
                        <button type="button" class="btn-remove remove-bookmarked-job" data-job-id="{{ $bookmarkedJob->job->id }}">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                        @if($bookmarkedJob->job->applications_count > 0)
                        <span class="applicant-count">{{ $bookmarkedJob->job->applications_count }} applicants</span>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Pagination -->
        @if($bookmarkedJobs->hasPages())
        <div class="pagination-wrapper">
            {{ $bookmarkedJobs->links() }}
        </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="empty-state-bookmarked">
            <div class="empty-icon">
                <i class="fas fa-bookmark"></i>
            </div>
            <h3>No Bookmarked Jobs Yet</h3>
            <p>Start bookmarking jobs you're interested in to view them here later.</p>
            <a href="{{ route('jobs') }}" class="btn-browse">
                <i class="fas fa-search"></i> Browse Jobs
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.remove-bookmarked-job').forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job-id');
            const jobCard = this.closest('.bookmarked-job-card');

            if (confirm('Are you sure you want to remove this job from your bookmarked list?')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
                this.disabled = true;

                fetch('{{ route("account.bookmarked-jobs.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ job_id: jobId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        jobCard.style.transition = 'all 0.3s ease';
                        jobCard.style.opacity = '0';
                        jobCard.style.transform = 'translateX(-20px)';

                        setTimeout(() => {
                            jobCard.remove();
                            const remainingJobs = document.querySelectorAll('.bookmarked-job-card');
                            if (remainingJobs.length === 0) {
                                location.reload();
                            }
                            // Update count
                            const countElement = document.querySelector('.bookmarked-count span');
                            if (countElement) {
                                countElement.textContent = remainingJobs.length + ' jobs bookmarked';
                            }
                        }, 300);
                    } else {
                        alert(data.message || 'Failed to remove job');
                        this.innerHTML = '<i class="fas fa-trash-alt"></i> Remove';
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    this.innerHTML = '<i class="fas fa-trash-alt"></i> Remove';
                    this.disabled = false;
                });
            }
        });
    });
});
</script>
@endpush
@endsection
