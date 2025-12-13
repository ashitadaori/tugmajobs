@extends('layouts.employer')

@section('page_title', 'Employer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        Welcome back, {{ Auth::user()->name }}! 
                        <span class="wave">👋</span>
                    </h1>
                    <p class="page-subtitle">Here's what's happening with your job postings today</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    @include('components.verified-badge', ['user' => Auth::user(), 'size' => 'lg'])
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-plus me-2"></i>Post New Job
                    </a>
                </div>
            </div>
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- KYC banner removed as requested --}}
    {{-- @if(!Auth::user()->isKycVerified())
        @include('components.kyc-reminder-banner')
    @endif --}}

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card card-floating">
                <div class="stats-header">
                    <div class="stats-icon primary">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stats-trend up">
                        <i class="fas fa-arrow-up"></i>
                        +12%
                    </div>
                </div>
                <div class="stats-label">Total Jobs Posted</div>
                <div class="stats-value">{{ $postedJobs ?? 0 }}</div>
                <div class="stats-description">
                    {{ ($postedJobs ?? 0) > 0 ? 'Great job building your presence!' : 'Start by posting your first job' }}
                </div>
                <div class="stats-progress">
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ min(($postedJobs ?? 0) * 10, 100) }}%"></div>
                    </div>
                </div>
                <div class="stats-action">
                    <a href="{{ route('employer.jobs.index') }}" class="btn btn-sm btn-outline-primary">
                        View All Jobs
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card card-floating">
                <div class="stats-header">
                    <div class="stats-icon success">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stats-trend up">
                        <i class="fas fa-arrow-up"></i>
                        +8%
                    </div>
                </div>
                <div class="stats-label">Active Jobs</div>
                <div class="stats-value">{{ $activeJobs ?? 0 }}</div>
                <div class="stats-description">
                    Jobs currently accepting applications
                </div>
                <div class="stats-progress">
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ ($postedJobs ?? 0) > 0 ? (($activeJobs ?? 0) / ($postedJobs ?? 1)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="stats-action">
                    <a href="{{ route('employer.jobs.index') }}?status=active" class="btn btn-sm btn-outline-success">
                        Manage Active
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card card-floating">
                <div class="stats-header">
                    <div class="stats-icon warning">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-trend up">
                        <i class="fas fa-arrow-up"></i>
                        +25%
                    </div>
                </div>
                <div class="stats-label">Total Applications</div>
                <div class="stats-value">{{ $totalApplications ?? 0 }}</div>
                <div class="stats-description">
                    Applications received across all jobs
                </div>
                <div class="stats-progress">
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: {{ min(($totalApplications ?? 0) * 2, 100) }}%"></div>
                    </div>
                </div>
                <div class="stats-action">
                    <a href="{{ route('employer.applications.index') }}" class="btn btn-sm btn-outline-warning">
                        Review Applications
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card card-floating">
                <div class="stats-header">
                    <div class="stats-icon info">
                        <i class="fas fa-clock"></i>
                    </div>
                    @if(($pendingApplications ?? 0) > 0)
                        <div class="stats-alert">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    @endif
                </div>
                <div class="stats-label">Pending Reviews</div>
                <div class="stats-value">{{ $pendingApplications ?? 0 }}</div>
                <div class="stats-description">
                    {{ ($pendingApplications ?? 0) > 0 ? 'Applications waiting for your review' : 'All caught up!' }}
                </div>
                <div class="stats-progress">
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: {{ ($totalApplications ?? 0) > 0 ? (($pendingApplications ?? 0) / ($totalApplications ?? 1)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="stats-action">
                    @if(($pendingApplications ?? 0) > 0)
                        <a href="{{ route('employer.applications.index') }}?status=pending" class="btn btn-sm btn-info">
                            Review Now
                        </a>
                    @else
                        <span class="text-muted small">No pending reviews</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- KYC Status Card -->
            @if(!Auth::user()->isKycVerified())
                <div class="mb-4">
                    @include('components.kyc-status-card', ['user' => Auth::user()])
                </div>
            @endif

            <!-- Recent Jobs -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-briefcase me-2"></i>Recent Job Postings
                        </h5>
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recentJobs) && $recentJobs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Status</th>
                                        <th>Applications</th>
                                        <th>Posted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentJobs as $job)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="job-icon me-3">
                                                        <i class="fas fa-briefcase"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $job->title }}</h6>
                                                        <small class="text-muted">{{ $job->location }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($job->status === 'active')
                                                    <span class="badge badge-active">Active</span>
                                                @elseif($job->status === 'pending')
                                                    <span class="badge badge-pending">Pending</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($job->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ $job->applications_count ?? 0 }}</span>
                                                <small class="text-muted">applications</small>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $job->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('employer.jobs.show', $job) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('employer.jobs.edit', $job) }}" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon mb-3">
                                <i class="fas fa-briefcase fa-3x text-muted"></i>
                            </div>
                            <h6>No Jobs Posted Yet</h6>
                            <p class="text-muted">Start attracting top talent by posting your first job.</p>
                            <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Post Your First Job
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Recent Applications
                        </h5>
                        <a href="{{ route('employer.applications.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recentApplications) && $recentApplications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Job</th>
                                        <th>Status</th>
                                        <th>Applied</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentApplications as $application)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-placeholder me-3">
                                                        {{ substr($application->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $application->user->name }}</h6>
                                                        <small class="text-muted">{{ $application->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <h6 class="mb-0">{{ $application->job->title }}</h6>
                                                <small class="text-muted">{{ $application->job->location }}</small>
                                            </td>
                                            <td>
                                                @if($application->status === 'pending')
                                                    <span class="badge badge-pending">Pending</span>
                                                @elseif($application->status === 'reviewed')
                                                    <span class="badge bg-info">Reviewed</span>
                                                @elseif($application->status === 'shortlisted')
                                                    <span class="badge bg-success">Shortlisted</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $application->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('employer.applications.show', $application) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Review
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon mb-3">
                                <i class="fas fa-users fa-3x text-muted"></i>
                            </div>
                            <h6>No Applications Yet</h6>
                            <p class="text-muted">Applications will appear here once candidates start applying to your jobs.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="quick-action-card">
                                <div class="icon-wrapper">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <h6 class="action-title">Post Job</h6>
                                <p class="action-subtitle">Create a new job posting</p>
                                <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary btn-sm">
                                    Get Started
                                </a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-action-card">
                                <div class="icon-wrapper">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h6 class="action-title">Analytics</h6>
                                <p class="action-subtitle">View performance metrics</p>
                                <a href="{{ route('employer.analytics.index') }}" class="btn btn-primary btn-sm">
                                    View Stats
                                </a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-action-card">
                                <div class="icon-wrapper">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h6 class="action-title">Company</h6>
                                <p class="action-subtitle">Update company profile</p>
                                <a href="{{ route('employer.profile.edit') }}" class="btn btn-primary btn-sm">
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-action-card">
                                <div class="icon-wrapper">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h6 class="action-title">Settings</h6>
                                <p class="action-subtitle">Manage preferences</p>
                                <a href="{{ route('employer.settings.index') }}" class="btn btn-primary btn-sm">
                                    Configure
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bell me-2"></i>Recent Notifications
                        </h5>
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(Auth::user()->notifications()->latest()->take(5)->count() > 0)
                        <div class="notification-list">
                            @foreach(Auth::user()->notifications()->latest()->take(5)->get() as $notification)
                                <div class="notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
                                    <div class="d-flex">
                                        <div class="notification-icon me-3">
                                            <i class="{{ $notification->getIconClass() }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="notification-title">{{ $notification->title }}</h6>
                                            <p class="notification-message">{{ $notification->message }}</p>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mb-3">
                                <i class="fas fa-bell fa-2x text-muted"></i>
                            </div>
                            <h6>No Notifications</h6>
                            <p class="text-muted small">You're all caught up!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/unified-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/ui-consistency-complete.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/dynamic-dashboard.js') }}"></script>
@endpush