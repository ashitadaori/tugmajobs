@extends('front.layouts.app')

@section('content')
<div class="dashboard">
    <!-- Dashboard Navigation Bar -->
    <div class="dashboard-navbar mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center">
                    <h4 class="mb-0 me-4">Job Seeker Dashboard</h4>
                    <div class="dashboard-breadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="dashboard-actions d-flex align-items-center gap-3">
                    <!-- Quick Actions -->
                    <div class="btn-group">
                        <a href="{{ route('jobs') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-1"></i> Find Jobs
                        </a>
                        <a href="{{ route('account.ai.job-match') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-robot me-1"></i> AI Match
                        </a>
                    </div>
                    
                    <!-- Profile Completion -->
                    <div class="profile-completion">
                        <div class="d-flex align-items-center">
                            <div class="progress me-2" style="width: 80px; height: 6px;">
                                <div class="progress-bar bg-success" style="width: 75%"></div>
                            </div>
                            <small class="text-muted">75% Complete</small>
                        </div>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">2</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">Recent Notifications</li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-briefcase me-2 text-primary"></i>New job match found</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2 text-success"></i>Profile viewed by employer</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Quick Navigation Tabs -->
            <div class="dashboard-tabs">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('account.dashboard') }}">
                            <i class="fas fa-home me-1"></i> Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('account.myJobApplications') }}">
                            <i class="fas fa-file-alt me-1"></i> Applications
                            @if(isset($stats['applications']) && $stats['applications'] > 0)
                                <span class="badge bg-primary ms-1">{{ $stats['applications'] }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('account.savedJobs') }}">
                            <i class="fas fa-heart me-1"></i> Saved Jobs
                            @if(isset($stats['saved_jobs']) && $stats['saved_jobs'] > 0)
                                <span class="badge bg-success ms-1">{{ $stats['saved_jobs'] }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('account.myProfile') }}">
                            <i class="fas fa-user me-1"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('account.resumes') }}">
                            <i class="fas fa-file-pdf me-1"></i> Resumes
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                            <i class="fas fa-robot me-1"></i> AI Tools
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('account.ai.job-match') }}">
                                <i class="fas fa-search me-2"></i> Job Matching
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('account.ai.resume-builder') }}">
                                <i class="fas fa-magic me-2"></i> Resume Builder
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            @include('front.account.sidebar')
        </div>
        
        <div class="col-lg-9">
            <!-- Welcome Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile" class="dashboard-avatar me-3">
                        @else
                            <div class="dashboard-avatar-placeholder me-3">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div>
                            <h4 class="mb-1">
                                Welcome back, {{ Auth::user()->name }}!
                                <x-verified-badge :user="Auth::user()" />
                            </h4>
                            <p class="text-muted mb-0">Here's what's happening with your job search</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KYC Verification Section -->
            <x-kyc-status-card :user="Auth::user()" />

            <!-- Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-1">{{ $stats['applications'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">Applications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-bookmark"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-1">{{ $stats['saved_jobs'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">Saved Jobs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-info text-white">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-1">{{ $stats['profile_views'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">Profile Views</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Applications</h5>
                        <a href="{{ route('account.myJobApplications') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recent_applications) && count($recent_applications) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_applications as $application)
                                        <tr>
                                            <td>
                                                <a href="{{ route('jobDetail', $application->job->id) }}" class="text-decoration-none">
                                                    {{ $application->job->title }}
                                                </a>
                                            </td>
                                            <td>{{ $application->job->company->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $application->status_color }}">
                                                    {{ $application->status }}
                                                </span>
                                            </td>
                                            <td>{{ $application->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="{{ asset('images/empty-applications.svg') }}" alt="No Applications" class="mb-3" style="width: 200px">
                            <h5>No Applications Yet</h5>
                            <p class="text-muted">Start applying to jobs to see your applications here</p>
                            <a href="{{ route('jobs') }}" class="btn btn-primary">Find Jobs</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recommended Jobs -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recommended Jobs</h5>
                        <a href="{{ route('jobs') }}" class="btn btn-sm btn-primary">View All Jobs</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recommended_jobs) && count($recommended_jobs) > 0)
                        <div class="row">
                            @foreach($recommended_jobs as $job)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                @if($job->company->logo)
                                                    <img src="{{ asset('storage/' . $job->company->logo) }}" alt="{{ $job->company->name }}" class="company-logo me-3">
                                                @else
                                                    <div class="company-logo-placeholder me-3">
                                                        {{ substr($job->company->name, 0, 2) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('jobDetail', $job->id) }}" class="text-decoration-none">
                                                            {{ $job->title }}
                                                        </a>
                                                    </h6>
                                                    <p class="text-muted mb-0">{{ $job->company->name }}</p>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center text-muted mb-2">
                                                    <i class="fas fa-map-marker-alt me-2"></i>
                                                    {{ $job->location }}
                                                </div>
                                                <div class="d-flex align-items-center text-muted">
                                                    <i class="fas fa-clock me-2"></i>
                                                    {{ $job->type }}
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-light text-dark">
                                                    Posted {{ $job->created_at->diffForHumans() }}
                                                </span>
                                                <a href="{{ route('jobDetail', $job->id) }}" class="btn btn-sm btn-outline-primary">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="{{ asset('images/empty-jobs.svg') }}" alt="No Jobs" class="mb-3" style="width: 200px">
                            <h5>No Recommended Jobs</h5>
                            <p class="text-muted">Complete your profile to get personalized job recommendations</p>
                            <a href="{{ route('account.myProfile') }}" class="btn btn-primary">Update Profile</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    min-height: calc(100vh - 6rem);
}

.dashboard-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.dashboard-avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stats-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.company-logo {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    object-fit: cover;
}

.company-logo-placeholder {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background-color: var(--bg-light);
    color: var(--text-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.table > :not(caption) > * > * {
    padding: 1rem;
}

.table th {
    font-weight: 600;
    color: var(--text-dark);
}

.table td {
    vertical-align: middle;
}

/* Dashboard Navbar Styles */
.dashboard-navbar {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.dashboard-breadcrumb .breadcrumb {
    background: none;
    padding: 0;
}

.dashboard-breadcrumb .breadcrumb-item a {
    color: var(--text-light);
    text-decoration: none;
}

.dashboard-breadcrumb .breadcrumb-item.active {
    color: var(--text-dark);
}

.dashboard-tabs {
    border-top: 1px solid var(--border-color);
    padding-top: 1rem;
}

.dashboard-tabs .nav-pills .nav-link {
    color: var(--text-dark);
    background: none;
    border-radius: var(--radius-sm);
    padding: 0.5rem 1rem;
    margin-right: 0.5rem;
    transition: all 0.2s ease;
}

.dashboard-tabs .nav-pills .nav-link:hover {
    background-color: var(--bg-light);
    color: var(--primary-color);
}

.dashboard-tabs .nav-pills .nav-link.active {
    background-color: var(--primary-color);
    color: white;
}

.profile-completion .progress {
    background-color: var(--bg-light);
}

@media (max-width: 768px) {
    .dashboard-navbar .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .dashboard-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .dashboard-tabs {
        overflow-x: auto;
    }
    
    .dashboard-tabs .nav {
        flex-nowrap;
        min-width: max-content;
    }
}
</style>
@endsection 