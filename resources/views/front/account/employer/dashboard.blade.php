@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Employer Dashboard Navigation Bar -->
    <div class="employer-dashboard-navbar mb-4">
        <div class="d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <h4 class="mb-0 me-4">Employer Dashboard</h4>
                <div class="dashboard-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('employer.dashboard') }}">Employer</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
            
            <div class="employer-actions d-flex align-items-center gap-3">
                <!-- Quick Actions -->
                <div class="btn-group">
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Post Job
                    </a>
                    <a href="{{ route('employer.applications.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-people me-1"></i> Applications
                        @if(isset($pendingApplications) && $pendingApplications > 0)
                            <span class="badge bg-warning ms-1">{{ $pendingApplications }}</span>
                        @endif
                    </a>
                </div>
                
                <!-- Company Status -->
                <div class="company-status">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-success me-2"></div>
                        <small class="text-muted">Active Company</small>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">3</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">Recent Notifications</li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person-plus me-2 text-primary"></i>New application received</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2 text-success"></i>Job posting viewed</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-clock me-2 text-warning"></i>Job expires in 3 days</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Quick Navigation Tabs -->
        <div class="employer-tabs">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('employer.dashboard') }}">
                        <i class="bi bi-house-door me-1"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employer.jobs.index') }}">
                        <i class="bi bi-briefcase me-1"></i> My Jobs
                        @if(isset($activeJobs) && $activeJobs > 0)
                            <span class="badge bg-primary ms-1">{{ $activeJobs }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employer.applications.index') }}">
                        <i class="bi bi-people me-1"></i> Applications
                        @if(isset($totalApplications) && $totalApplications > 0)
                            <span class="badge bg-success ms-1">{{ $totalApplications }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employer.applications.shortlisted') }}">
                        <i class="bi bi-star me-1"></i> Shortlisted
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employer.analytics.index') }}">
                        <i class="bi bi-graph-up me-1"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employer.profile.edit') }}">
                        <i class="bi bi-building me-1"></i> Company
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                        <i class="bi bi-gear me-1"></i> Settings
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('employer.settings.index') }}">
                            <i class="bi bi-sliders me-2"></i> General
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('employer.settings.notifications') }}">
                            <i class="bi bi-bell me-2"></i> Notifications
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('employer.settings.security') }}">
                            <i class="bi bi-shield-lock me-2"></i> Security
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">
                            Welcome back, {{ auth()->user()->name }}!
                            <x-verified-badge :user="auth()->user()" />
                        </h1>
                        <p class="text-muted mb-0">Here's what's happening with your jobs today.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary d-flex align-items-center">
                            <i class="bi bi-plus-circle me-2"></i>Post New Job
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-download me-2"></i>Export Data</a></li>
                                <li><a class="dropdown-item" href="{{ route('employer.analytics.index') }}"><i class="bi bi-graph-up me-2"></i>View Analytics</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Verification Section for Employers -->
    <div class="row mb-4">
        <div class="col-12">
            <x-kyc-status-card :user="auth()->user()" />
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('employer.jobs.create') }}" class="quick-action-card">
                    <div class="icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <span>Post Job</span>
                </a>
                <a href="{{ route('employer.applications.index') }}" class="quick-action-card">
                    <div class="icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-people"></i>
                    </div>
                    <span>View Applications</span>
                </a>
                <a href="{{ route('employer.profile.edit') }}" class="quick-action-card">
                    <div class="icon-wrapper bg-info-subtle text-info">
                        <i class="bi bi-building"></i>
                    </div>
                    <span>Company Profile</span>
                </a>
                <a href="{{ route('employer.analytics.index') }}" class="quick-action-card">
                    <div class="icon-wrapper bg-warning-subtle text-warning">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <span>Analytics</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Jobs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary-subtle text-primary rounded-circle p-3 me-3">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Jobs</h6>
                        <h2 class="mb-1">{{ number_format($postedJobs) }}</h2>
                        <div class="small {{ ($postedJobsGrowth ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-arrow-{{ ($postedJobsGrowth ?? 0) > 0 ? 'up' : 'down' }}"></i> 
                            {{ abs($postedJobsGrowth ?? 0) }}% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Jobs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success-subtle text-success rounded-circle p-3 me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Active Jobs</h6>
                        <h2 class="mb-1">{{ number_format($activeJobs) }}</h2>
                        <div class="small {{ ($activeJobsGrowth ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-arrow-{{ ($activeJobsGrowth ?? 0) > 0 ? 'up' : 'down' }}"></i>
                            {{ abs($activeJobsGrowth ?? 0) }}% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning-subtle text-warning rounded-circle p-3 me-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Pending Applications</h6>
                        <h2 class="mb-1">{{ number_format($pendingApplications ?? 0) }}</h2>
                        <div class="small text-warning">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $pendingApplicationsCount ?? '0' }} need review
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Applications -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-info-subtle text-info rounded-circle p-3 me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Applications</h6>
                        <h2 class="mb-1">{{ number_format($totalApplications) }}</h2>
                        <div class="small {{ ($applicationsGrowth ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-arrow-{{ ($applicationsGrowth ?? 0) > 0 ? 'up' : 'down' }}"></i>
                            {{ abs($applicationsGrowth ?? 0) }}% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Applications Chart -->
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Applications Overview</h5>
                            <div class="small text-muted">Track your application trends</div>
                        </div>
                        <div class="chart-period">
                            <select class="form-select form-select-sm" id="applicationsPeriod">
                                <option value="7">Last 7 days</option>
                                <option value="30">Last 30 days</option>
                                <option value="90">Last 3 months</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="applicationsChart" height="300"></canvas>
                </div>
            </div>

            <!-- Recent Jobs Table -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Recent Jobs</h5>
                            <div class="small text-muted">Your latest job postings</div>
                        </div>
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-link text-decoration-none">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentJobs->isEmpty())
                        <div class="empty-state text-center py-5">
                            <img src="{{ asset('images/empty-jobs.svg') }}" alt="No Jobs" class="mb-4" style="max-width: 200px;">
                            <h3 class="h5 mb-3">No Jobs Posted Yet</h3>
                            <p class="text-muted mb-4">Start by posting your first job listing</p>
                            <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i> Post Your First Job
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Job Title</th>
                                        <th class="border-0 text-center">Applications</th>
                                        <th class="border-0 text-center">Status</th>
                                        <th class="border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentJobs as $job)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center py-2">
                                                    <div class="job-icon bg-light rounded p-2 me-3">
                                                        <i class="bi bi-briefcase text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <a href="{{ route('jobDetail', $job->id) }}" class="text-dark text-decoration-none">
                                                                {{ $job->title }}
                                                            </a>
                                                        </h6>
                                                        <div class="small text-muted">
                                                            <i class="bi bi-geo-alt me-1"></i>
                                                            {{ $job->location }}
                                                            <span class="mx-2">&bull;</span>
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $job->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('employer.applications.index', ['job_id' => $job->id]) }}" 
                                                   class="text-decoration-none">
                                                    <span class="badge bg-light text-dark p-2">
                                                        {{ $job->applications_count }}
                                                        <span class="text-muted ms-1">Total</span>
                                                    </span>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $statusClass = match($job->status) {
                                                        'active' => 'success',
                                                        'draft' => 'warning',
                                                        default => 'danger'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} p-2">
                                                    {{ ucfirst($job->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('jobDetail', $job->id) }}" 
                                                       class="btn btn-light btn-sm" 
                                                       data-bs-toggle="tooltip" 
                                                       title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('employer.jobs.edit', $job->id) }}" 
                                                       class="btn btn-light btn-sm" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Edit Job">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-light btn-sm dropdown-toggle dropdown-toggle-split"
                                                            data-bs-toggle="dropdown">
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('employer.applications.index', ['job_id' => $job->id]) }}">
                                                                <i class="bi bi-people me-2"></i> View Applications
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi bi-share me-2"></i> Share Job
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('employer.jobs.delete', $job->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="bi bi-trash me-2"></i> Delete Job
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Job Performance Chart -->
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <h5 class="card-title mb-1">Job Performance</h5>
                    <div class="small text-muted">Distribution of your job postings</div>
                </div>
                <div class="card-body">
                    <canvas id="jobPerformanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
}

.quick-action-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bs-white);
    border-radius: 0.5rem;
    text-decoration: none;
    color: var(--bs-body-color);
    transition: all 0.2s ease;
    box-shadow: var(--bs-box-shadow-sm);
}

.quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--bs-box-shadow);
    color: var(--bs-primary);
}

.quick-action-card .icon-wrapper {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
}

.stats-card {
    transition: all 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--bs-box-shadow);
}

.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    background: linear-gradient(to bottom, var(--bs-light), var(--bs-white));
}

@media (max-width: 768px) {
    .quick-action-card {
        width: calc(50% - 0.5rem);
    }
}

@media (max-width: 576px) {
    .quick-action-card {
        width: 100%;
    }
}

/* Employer Dashboard Navbar Styles */
.employer-dashboard-navbar {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    padding: 0 1.5rem;
}

.dashboard-breadcrumb .breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
}

.dashboard-breadcrumb .breadcrumb-item a {
    color: #6b7280;
    text-decoration: none;
}

.dashboard-breadcrumb .breadcrumb-item.active {
    color: #1f2937;
}

.company-status .status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.employer-tabs {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.employer-tabs .nav-pills .nav-link {
    color: #1f2937;
    background: none;
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    margin-right: 0.5rem;
    transition: all 0.2s ease;
    font-weight: 500;
}

.employer-tabs .nav-pills .nav-link:hover {
    background-color: #f3f4f6;
    color: #2563eb;
}

.employer-tabs .nav-pills .nav-link.active {
    background-color: #2563eb;
    color: white;
}

.employer-tabs .nav-pills .nav-link .badge {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .employer-dashboard-navbar .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .employer-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .employer-tabs {
        overflow-x: auto;
    }
    
    .employer-tabs .nav {
        flex-nowrap;
        min-width: max-content;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Applications Chart
    const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
    new Chart(applicationsCtx, {
        type: 'line',
        data: {
            labels: @json($applicationTrendsLabels),
            datasets: [{
                label: 'Applications',
                data: @json($applicationTrendsData),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Job Performance Chart
    const performanceCtx = document.getElementById('jobPerformanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'bar',
        data: {
            labels: @json($jobPerformanceLabels),
            datasets: [
                {
                    label: 'Views',
                    data: @json($jobPerformanceViews),
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgb(13, 110, 253)',
                    borderWidth: 1
                },
                {
                    label: 'Applications',
                    data: @json($jobPerformanceApplications),
                    backgroundColor: 'rgba(25, 135, 84, 0.2)',
                    borderColor: 'rgb(25, 135, 84)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Handle period change for applications chart
    document.getElementById('applicationsPeriod').addEventListener('change', function(e) {
        const days = e.target.value;
        fetch(`/employer/analytics/update-range?days=${days}`)
            .then(response => response.json())
            .then(data => {
                applicationsChart.data.labels = data.labels;
                applicationsChart.data.datasets[0].data = data.data;
                applicationsChart.update();
            });
    });
});
</script>
@endpush 