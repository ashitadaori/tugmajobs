@extends('front.layouts.app')

@section('content')
<div class="jobs-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12">
                <h1 class="text-white mb-4">Browse Available Jobs</h1>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-4 col-lg-3 mb-4">
            <form action="" method="GET" id="searchForm">
                <div class="filter-card">
                    <div class="mb-4">
                        <h5 class="filter-title">Keywords</h5>
                        <input type="text" value="{{ Request::get('keyword') }}" name="keyword" id="keyword" placeholder="Keywords" class="form-control">
                    </div>

                    <div class="mb-4">
                        <h5 class="filter-title">Location</h5>
                        <x-job-location-filter 
                            :currentLocation="Request::get('location', '')"
                            :radius="Request::get('radius', 10)"
                        />
                    </div>

                    <div class="mb-4">
                        <h5 class="filter-title">Job Type</h5>
                        <select name="jobType" id="jobType" class="form-select">
                            <option value="">All Job Types</option>
                            @foreach($jobTypes as $jobType)
                                <option value="{{ $jobType->name }}" {{ Request::get('jobType') == $jobType->name ? 'selected' : '' }}>{{ $jobType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-buttons">
                        <button class="btn btn-primary w-100 mb-2" type="submit">Search</button>
                        <a href="{{ route('jobs') }}" class="btn btn-outline-primary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Job Listings -->
        <div class="col-md-8 col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="results-count">
                    {{ $jobs->total() }} jobs found
                </div>
                <div class="sort-dropdown">
                    <select name="sort" id="sort" class="form-select">
                        <option value="1" {{ (Request::get('sort') == '1') ? 'selected' : '' }}>Latest</option>
                        <option value="0" {{ (Request::get('sort') == '0') ? 'selected' : '' }}>Oldest</option>
                    </select>
                </div>
            </div>

            <div class="row g-4">
                @if ($jobs->isNotEmpty())
                    @foreach ($jobs as $job)
                        <div class="col-lg-6">
                            <div class="job-card">
                                <div class="job-company-logo">
                                    <div class="company-badge">
                                        {{ substr($job->employer->employerProfile->company_name ?? 'C', 0, 1) }}
                                    </div>
                                </div>
                                <h3 class="job-title">{{ $job->title }}</h3>
                                <p class="company-name">
                                    {{ $job->employer->employerProfile->company_name ?? 'Company Name' }}
                                    <x-verified-badge :user="$job->employer" size="xs" />
                                </p>
                                <p class="job-description">{{ Str::words(strip_tags($job->description), $words=15, '...') }}</p>
                                <div class="job-tags">
                                    <span class="tag">{{ $job->jobType->name }}</span>
                                    <span class="tag">{{ $job->location }}</span>
                                    @if ($job->salary_range)
                                        <span class="tag"><i class="fas fa-money-bill-wave"></i> {{ $job->salary_range }}</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <a href="{{ route('jobDetail', $job->id) }}" class="btn btn-primary flex-grow-1">View Details</a>
                                    @auth
                                        @if(Auth::user()->role === 'jobseeker')
                                            <button type="button" 
                                                    class="btn btn-outline-primary save-job-btn {{ $job->savedByUser(Auth::id()) ? 'saved' : '' }}"
                                                    data-job-id="{{ $job->id }}">
                                                <i class="fa-heart {{ $job->savedByUser(Auth::id()) ? 'fas text-danger' : 'far text-muted' }}"></i>
                                            </button>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                            <i class="far fa-heart text-muted"></i>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="col-12 mt-4">
                        <nav class="custom-pagination">
                            {{ $jobs->withQueryString()->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @else
                    <div class="col-12">
                        <div class="no-jobs-found">
                            <i class="fas fa-search mb-3"></i>
                            <h3>No jobs found</h3>
                            <p>Try adjusting your search filters or browse all available jobs.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.jobs-header {
    background: var(--primary-color);
    padding: 3rem 0;
    margin-top: -2rem;
}

.filter-card {
    background: #ffffff;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.filter-title {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #333;
    font-weight: 600;
}

.form-control, .form-select {
    border: 1px solid #e0e0e0;
    padding: 0.8rem 1rem;
    border-radius: 8px;
    background: #fff;
    color: #333;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(82, 73, 255, 0.1);
}

.results-count {
    font-size: 1.1rem;
    color: #333;
    font-weight: 500;
}

.job-card {
    background: #ffffff;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.job-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.company-badge {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
    background: var(--primary-color);
    border-radius: 10px;
    margin-bottom: 1rem;
}

.job-title {
    color: #333;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.company-name {
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-weight: 500;
}

.job-description {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.job-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.tag {
    background: rgba(82, 73, 255, 0.1);
    color: var(--primary-color);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.no-jobs-found {
    text-align: center;
    padding: 3rem;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.no-jobs-found i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.no-jobs-found h3 {
    color: #333;
    margin-bottom: 1rem;
}

.no-jobs-found p {
    color: #666;
}

.custom-pagination {
    margin-top: 2rem;
}

.custom-pagination .pagination {
    justify-content: center;
}

.custom-pagination .page-link {
    color: var(--primary-color);
    border-color: #e0e0e0;
    padding: 0.5rem 1rem;
}

.custom-pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

@media (max-width: 768px) {
    .filter-card {
        margin-bottom: 2rem;
    }
    
    .job-card {
        margin-bottom: 1rem;
    }
}

.save-job-btn {
    width: 40px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.save-job-btn:hover {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.save-job-btn:hover i:not(.text-danger) {
    color: white;
}

.save-job-btn i {
    font-size: 1rem;
}

.save-job-btn i.text-danger {
    color: #dc3545 !important;
}
</style>
@endsection

@section('customJs')
<script>
// Set up AJAX CSRF token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function toggleSaveJob(jobId) {
    @if(!Auth::check())
        window.location.href = '{{ route("login") }}';
        return;
    @endif

    const btn = document.getElementById(`saveJobBtn-${jobId}`);
    const icon = document.getElementById(`saveJobIcon-${jobId}`);
    
    if (!btn.disabled) {
        btn.disabled = true;
        
        // Check if job is already saved
        const isSaved = icon.classList.contains('text-danger');
        const route = isSaved ? `/jobs/${jobId}/unsave` : `/jobs/${jobId}/save`;
        
        $.ajax({
            url: route,
            type: 'POST',
            success: function(response) {
                if (response.status) {
                    if (isSaved) {
                        icon.classList.remove('text-danger');
                    } else {
                        icon.classList.add('text-danger');
                    }
                } else {
                    alert(response.message || 'Error saving job');
                }
            },
            error: function(xhr) {
                alert('Error saving job. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
            }
        });
    }
}

// Handle sort dropdown change
document.getElementById('sort').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});

// Form submission handling
$('#searchForm').on('submit', function(e) {
    e.preventDefault();
    const keyword = $('#keyword').val();
    const location = $('#location').val();
    const jobType = $('#jobType').val();
    const sort = $('#sort').val();
    
    let url = '{{ route("jobs") }}?';
    if (keyword) url += `keyword=${encodeURIComponent(keyword)}&`;
    if (location) url += `location=${encodeURIComponent(location)}&`;
    if (jobType) url += `jobType=${encodeURIComponent(jobType)}&`;
    if (sort) url += `sort=${sort}`;
    
    window.location.href = url;
});

// Sort change handling
$('#sort').on('change', function() {
    $('#searchForm').submit();
});
</script>
@endsection
