@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <!-- Admin Dashboard Navigation Bar -->
        <div class="admin-dashboard-navbar mb-4">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center">
                    <h4 class="mb-0 me-4">Admin Dashboard</h4>
                    <div class="dashboard-breadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="admin-actions d-flex align-items-center gap-3">
                    <!-- System Status -->
                    <div class="system-status">
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-success me-2"></div>
                            <small class="text-muted">System Online</small>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="btn-group">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-people me-1"></i> Manage Users
                        </a>
                        <a href="{{ route('admin.users.index', ['kyc_status' => 'all']) }}"
                            class="btn btn-outline-info btn-sm">
                            <i class="bi bi-shield-check me-1"></i> KYC Overview
                            @if(isset($pendingKyc) && $pendingKyc > 0)
                                <span class="badge bg-warning ms-1">{{ $pendingKyc }} pending</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Tabs -->
            <div class="admin-tabs">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people me-1"></i> Users
                            @if(isset($totalUsers) && $totalUsers > 0)
                                <span class="badge bg-primary ms-1">{{ number_format($totalUsers) }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.jobs.index') }}">
                            <i class="bi bi-briefcase me-1"></i> Jobs
                            @if(isset($activeJobs) && $activeJobs > 0)
                                <span class="badge bg-success ms-1">{{ number_format($activeJobs) }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.categories.index') }}">
                            <i class="bi bi-tags me-1"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.analytics.dashboard') }}">
                            <i class="bi bi-graph-up me-1"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                            <i class="bi bi-gear me-1"></i> System
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                    <i class="bi bi-sliders me-2"></i> Settings
                                </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.security-log') }}">
                                    <i class="bi bi-shield-lock me-2"></i> Security Log
                                </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.audit-log') }}">
                                    <i class="bi bi-clipboard-data me-2"></i> Audit Log
                                </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.audit-reports.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Audit Reports
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Dashboard</h1>
            <div class="btn-group">
                <a href="{{ route('admin.dashboard.export') }}" class="btn btn-success"
                    title="Export dashboard statistics to CSV">
                    <i class="bi bi-download me-2"></i>Export Statistics
                </a>
                <button type="button" id="refresh-stats" class="btn btn-outline-primary ms-2" onclick="refreshDashboard()"
                    title="Manually refresh dashboard data">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                    <div id="total-users" class="stats-card stats-card-clickable">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Total Users</h6>
                                <h2 class="mb-0 text-dark">{{ number_format($totalUsers) }}</h2>
                                <div class="small text-success">
                                    <i class="bi bi-arrow-up"></i> {{ $userGrowth }}% from last month
                                </div>
                            </div>
                            <div class="flex-shrink-0 ms-3">
                                <div class="bg-light rounded-circle p-3">
                                    <i class="bi bi-people text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-hover-indicator">
                            <i class="bi bi-arrow-right"></i> View All Users
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.companies.index') }}" class="text-decoration-none">
                    <div id="active-jobs" class="stats-card stats-card-clickable">
                        <div class="live-indicator"></div>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Active Jobs</h6>
                                <h2 class="mb-0 text-dark">{{ number_format($activeJobs) }}</h2>
                                <div class="small text-success">
                                    <i class="bi bi-arrow-up"></i> {{ $jobGrowth }}% from last month
                                </div>
                            </div>
                            <div class="flex-shrink-0 ms-3">
                                <div class="bg-light rounded-circle p-3">
                                    <i class="bi bi-briefcase text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-hover-indicator">
                            <i class="bi bi-arrow-right"></i> View All Jobs
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.jobs.pending') }}" class="text-decoration-none">
                    <div id="pending-jobs"
                        class="stats-card stats-card-clickable {{ $pendingJobs > 0 ? 'border-warning' : '' }}">
                        @if($pendingJobs > 0)
                            <div class="position-absolute top-0 end-0 p-2">
                                <span class="badge bg-warning text-dark pulse-animation">{{ $pendingJobs }}</span>
                            </div>
                        @endif
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Pending Jobs</h6>
                                <h2 class="mb-0 {{ $pendingJobs > 0 ? 'text-warning' : 'text-dark' }}">
                                    {{ number_format($pendingJobs) }}
                                </h2>
                                <div class="small">
                                    @if($pendingJobs > 0)
                                        <span class="text-warning">
                                            <i class="bi bi-arrow-right"></i> Review Now
                                        </span>
                                    @else
                                        <span class="text-success">
                                            <i class="bi bi-check-circle"></i> All jobs reviewed
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0 ms-3">
                                <div class="bg-light rounded-circle p-3">
                                    <i
                                        class="bi bi-clock-history {{ $pendingJobs > 0 ? 'text-warning' : 'text-muted' }}"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-hover-indicator">
                            <i class="bi bi-arrow-right"></i> Review Pending Jobs
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.kyc.didit-verifications') }}" class="text-decoration-none">
                    <div id="verified-kyc" class="stats-card stats-card-clickable">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">KYC Verified</h6>
                                <h2 class="mb-0 text-dark">{{ $verifiedKyc }}</h2>
                                <div class="small text-info">
                                    <i class="bi bi-info-circle"></i>
                                    <span id="pending-kyc-info">{{ $pendingKyc }} pending verification</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ms-3">
                                <div class="bg-light rounded-circle p-3">
                                    <i class="bi bi-shield-check text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-hover-indicator">
                            <i class="bi bi-arrow-right"></i> View KYC Verifications
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.companies.index') }}" class="text-decoration-none">
                    <div id="total-applications" class="stats-card stats-card-clickable">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Total Applications</h6>
                                <h2 class="mb-0 text-dark">{{ number_format($totalApplications) }}</h2>
                                <div class="small text-success">
                                    <i class="bi bi-arrow-up"></i> {{ $applicationGrowth }}% from last month
                                </div>
                            </div>
                            <div class="flex-shrink-0 ms-3">
                                <div class="bg-light rounded-circle p-3">
                                    <i class="bi bi-file-text text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-hover-indicator">
                            <i class="bi bi-arrow-right"></i> View All Applications
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Registration Chart -->
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">New User Registrations</h5>
                        <div class="small text-muted">User registration trends over time</div>
                    </div>
                    <div class="card-body">
                        <canvas id="registrationChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- User Distribution by Role -->
            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">User Distribution</h5>
                            <div class="small text-muted">By role type</div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <!-- Job Seekers -->
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                                            <i class="bi bi-person-badge text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Job Seekers</h6>
                                            <small class="text-muted">Active candidates</small>
                                        </div>
                                    </div>
                                    <h4 class="mb-0 text-primary">
                                        {{ \App\Models\User::where('role', 'jobseeker')->count() }}
                                    </h4>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary"
                                        style="width: {{ $totalUsers > 0 ? (\App\Models\User::where('role', 'jobseeker')->count() / $totalUsers * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Employers -->
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                                            <i class="bi bi-building text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Employers</h6>
                                            <small class="text-muted">Companies hiring</small>
                                        </div>
                                    </div>
                                    <h4 class="mb-0 text-success">{{ \App\Models\User::where('role', 'employer')->count() }}
                                    </h4>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $totalUsers > 0 ? (\App\Models\User::where('role', 'employer')->count() / $totalUsers * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Admins with User List -->
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                                            <i class="bi bi-shield-check text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Administrators</h6>
                                            <small class="text-muted">System admins</small>
                                        </div>
                                    </div>
                                    <h4 class="mb-0 text-warning">{{ \App\Models\User::where('role', 'admin')->count() }}
                                    </h4>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-warning"
                                        style="width: {{ $totalUsers > 0 ? (\App\Models\User::where('role', 'admin')->count() / $totalUsers * 100) : 0 }}%">
                                    </div>
                                </div>

                                <!-- Admin Users List -->
                                @php
                                    $adminUsers = \App\Models\User::where('role', 'admin')->latest()->take(5)->get();
                                @endphp
                                @if($adminUsers->count() > 0)
                                    <div class="admin-users-list mt-2">
                                        <small class="text-muted fw-semibold d-block mb-2">Admin Users:</small>
                                        @foreach($adminUsers as $admin)
                                            <div class="d-flex align-items-center py-2 border-top">
                                                <div class="flex-shrink-0">
                                                    @if($admin->profile_photo)
                                                        <img src="{{ asset('profile_img/' . $admin->profile_photo) }}"
                                                            class="rounded-circle" width="32" height="32" alt="{{ $admin->name }}">
                                                    @else
                                                        <div class="bg-warning bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <span class="text-warning fw-bold">{{ substr($admin->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <div class="fw-semibold" style="font-size: 0.875rem;">{{ $admin->name }}</div>
                                                    <small class="text-muted">{{ $admin->email }}</small>
                                                </div>
                                                <a href="{{ route('admin.users.show', $admin->id) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                        @if(\App\Models\User::where('role', 'admin')->count() > 5)
                                            <div class="text-center mt-2">
                                                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}"
                                                    class="btn btn-sm btn-link">
                                                    View all {{ \App\Models\User::where('role', 'admin')->count() }} admins
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Feed Row -->
        <div class="row g-4 mb-4">
            <!-- Activity Feed -->
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Recent Activity</h5>
                            <small class="text-muted">Latest platform events</small>
                        </div>
                        <a href="{{ route('admin.settings.audit-log') }}" class="btn btn-sm btn-outline-primary">View
                            All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="activity-feed">
                            @php
                                // Get recent activities
                                $recentJobs = \App\Models\Job::with('employer')->latest()->take(3)->get();
                                $recentUsers = \App\Models\User::latest()->take(3)->get();
                                $recentApplications = \App\Models\JobApplication::with(['user', 'job'])->latest()->take(3)->get();

                                // Merge and sort by created_at
                                $activities = collect();

                                foreach ($recentJobs as $job) {
                                    $activities->push([
                                        'type' => 'job',
                                        'data' => $job,
                                        'created_at' => $job->created_at
                                    ]);
                                }

                                foreach ($recentUsers as $user) {
                                    $activities->push([
                                        'type' => 'user',
                                        'data' => $user,
                                        'created_at' => $user->created_at
                                    ]);
                                }

                                foreach ($recentApplications as $app) {
                                    $activities->push([
                                        'type' => 'application',
                                        'data' => $app,
                                        'created_at' => $app->created_at
                                    ]);
                                }

                                $activities = $activities->sortByDesc('created_at')->take(8);
                            @endphp

                            @forelse($activities as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon
                                                    @if($activity['type'] === 'job') activity-icon-primary
                                                    @elseif($activity['type'] === 'user') activity-icon-success
                                                    @else activity-icon-info
                                                    @endif">
                                        @if($activity['type'] === 'job')
                                            <i class="bi bi-briefcase"></i>
                                        @elseif($activity['type'] === 'user')
                                            <i class="bi bi-person-plus"></i>
                                        @else
                                            <i class="bi bi-file-earmark-text"></i>
                                        @endif
                                    </div>
                                    <div class="activity-content">
                                        @if($activity['type'] === 'job')
                                            <p class="activity-text">
                                                <strong>{{ $activity['data']->employer->name ?? 'Unknown' }}</strong> posted a new
                                                job:
                                                <a
                                                    href="{{ route('admin.jobs.show', $activity['data']->id) }}">{{ Str::limit($activity['data']->title, 40) }}</a>
                                            </p>
                                        @elseif($activity['type'] === 'user')
                                            <p class="activity-text">
                                                <strong>{{ $activity['data']->name }}</strong> registered as
                                                <span
                                                    class="badge bg-{{ $activity['data']->role === 'employer' ? 'success' : 'primary' }} badge-sm">{{ ucfirst($activity['data']->role) }}</span>
                                            </p>
                                        @else
                                            <p class="activity-text">
                                                <strong>{{ $activity['data']->user->name ?? 'User' }}</strong> applied to
                                                <a href="#">{{ Str::limit($activity['data']->job->title ?? 'Unknown Job', 30) }}</a>
                                            </p>
                                        @endif
                                        <span class="activity-time">
                                            <i class="bi bi-clock me-1"></i>{{ $activity['created_at']->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">No recent activity</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Activity Feed */
            .activity-feed {
                max-height: 400px;
                overflow-y: auto;
            }

            .activity-item {
                display: flex;
                gap: 1rem;
                padding: 1rem 1.5rem;
                border-bottom: 1px solid #f3f4f6;
                transition: background-color 0.2s ease;
            }

            .activity-item:hover {
                background-color: #f9fafb;
            }

            .activity-item:last-child {
                border-bottom: none;
            }

            .activity-icon {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .activity-icon-primary {
                background: #e0e7ff;
                color: #4f46e5;
            }

            .activity-icon-success {
                background: #dcfce7;
                color: #059669;
            }

            .activity-icon-info {
                background: #e0f2fe;
                color: #0284c7;
            }

            .activity-icon-warning {
                background: #fef3c7;
                color: #d97706;
            }

            .activity-content {
                flex: 1;
            }

            .activity-text {
                margin: 0 0 0.25rem 0;
                font-size: 0.875rem;
                color: #374151;
                line-height: 1.5;
            }

            .activity-text a {
                color: #4f46e5;
                text-decoration: none;
            }

            .activity-text a:hover {
                text-decoration: underline;
            }

            .activity-time {
                font-size: 0.75rem;
                color: #9ca3af;
            }

            .badge-sm {
                font-size: 0.65rem;
                padding: 0.15rem 0.4rem;
            }
        </style>
        <style>
            /* Admin Dashboard Navbar Styles */
            .admin-dashboard-navbar {
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

            .system-status .status-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
            }

            .admin-tabs {
                border-top: 1px solid #e5e7eb;
                padding-top: 1rem;
            }

            .admin-tabs .nav-pills .nav-link {
                color: #1f2937;
                background: none;
                border-radius: 0.375rem;
                padding: 0.5rem 1rem;
                margin-right: 0.5rem;
                transition: all 0.2s ease;
                font-weight: 500;
            }

            .admin-tabs .nav-pills .nav-link:hover {
                background-color: #f3f4f6;
                color: #2563eb;
            }

            .admin-tabs .nav-pills .nav-link.active {
                background-color: #2563eb;
                color: white;
            }

            /* Pulse animation for pending jobs notification */
            .pulse-animation {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    transform: scale(1);
                    opacity: 1;
                }

                50% {
                    transform: scale(1.1);
                    opacity: 0.7;
                }

                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            /* Pending jobs card styling */
            .stats-card.border-warning {
                border-left: 4px solid #f59e0b !important;
                box-shadow: 0 2px 4px rgba(245, 158, 11, 0.1);
            }

            .admin-tabs .nav-pills .nav-link .badge {
                font-size: 0.75rem;
            }

            .stats-card {
                background: #fff;
                border-radius: 0.5rem;
                padding: 1.5rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                border: 1px solid #e5e7eb;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .stats-card-clickable {
                cursor: pointer;
            }

            .stats-card-clickable:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
                border-color: #2563eb;
            }

            .card-hover-indicator {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(to top, rgba(37, 99, 235, 0.1), transparent);
                padding: 0.75rem 1.5rem;
                transform: translateY(100%);
                transition: transform 0.3s ease;
                font-size: 0.875rem;
                color: #2563eb;
                font-weight: 500;
            }

            .stats-card-clickable:hover .card-hover-indicator {
                transform: translateY(0);
            }

            /* Dynamic update indicators */
            .stats-card.stat-updated {
                animation: statUpdated 1s ease;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
            }

            .dashboard-updating .stats-card {
                opacity: 0.8;
            }

            .dashboard-updating .stats-card:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
                animation: shimmer 1.5s infinite;
            }

            /* Loading spinner animation */
            .spin {
                animation: spin 1s linear infinite;
            }

            /* Keyframe animations */
            @keyframes statUpdated {
                0% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
                }

                50% {
                    transform: scale(1.02);
                    box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.2);
                }

                100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
                }
            }

            @keyframes shimmer {
                0% {
                    transform: translateX(-100%);
                }

                100% {
                    transform: translateX(100%);
                }
            }

            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }

            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }

                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            /* Toast notifications */
            .toast-notification {
                border-radius: 6px;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            /* Real-time indicator for active jobs */
            .stats-card .live-indicator {
                position: absolute;
                top: 10px;
                right: 10px;
                width: 8px;
                height: 8px;
                background: #22c55e;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
                }

                70% {
                    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
                }
            }

            @media (max-width: 768px) {
                .admin-dashboard-navbar .d-flex {
                    flex-direction: column;
                    align-items: flex-start !important;
                    gap: 1rem;
                }

                .admin-actions {
                    width: 100%;
                    justify-content: space-between;
                }

                .admin-tabs {
                    overflow-x: auto;
                }

                .admin-tabs .nav {
                    flex-nowrap;
                    min-width: max-content;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Dashboard Statistics Update System
            class AdminDashboardUpdater {
                constructor() {
                    this.updateInterval = 30000; // 30 seconds
                    this.isUpdating = false;
                    this.lastUpdateTime = null;
                    this.init();
                }

                init() {
                    // Disable automatic updates to prevent errors
                    // this.startAutoUpdate();

                    // Manual refresh button (if exists)
                    const refreshBtn = document.getElementById('refresh-stats');
                    if (refreshBtn) {
                        refreshBtn.addEventListener('click', () => this.updateStats());
                    }

                    // Disable auto-update on focus to prevent errors
                    // document.addEventListener('visibilitychange', () => {
                    //     if (!document.hidden && this.shouldUpdate()) {
                    //         this.updateStats();
                    //     }
                    // });
                }

                startAutoUpdate() {
                    // Disabled to prevent continuous error messages
                    // setInterval(() => {
                    //     if (!document.hidden && this.shouldUpdate()) {
                    //         this.updateStats();
                    //     }
                    // }, this.updateInterval);
                }

                shouldUpdate() {
                    if (!this.lastUpdateTime) return true;
                    const timeDiff = Date.now() - this.lastUpdateTime;
                    return timeDiff > this.updateInterval;
                }

                async updateStats() {
                    if (this.isUpdating) return;

                    this.isUpdating = true;
                    this.showLoadingIndicator();

                    try {
                        const response = await fetch('{{ route('admin.dashboard.stats') }}', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success) {
                            this.updateDashboardCards(result.data);
                            this.lastUpdateTime = Date.now();
                            this.showSuccessIndicator();
                        } else {
                            throw new Error('Failed to fetch dashboard statistics');
                        }

                    } catch (error) {
                        console.error('Error updating dashboard stats:', error);
                        this.showErrorIndicator();
                    } finally {
                        this.isUpdating = false;
                        this.hideLoadingIndicator();
                    }
                }

                updateDashboardCards(data) {
                    // Update Total Users
                    this.updateCard('total-users', data.totalUsers, data.userGrowth, '%');

                    // Update Active Jobs (main focus)
                    this.updateCard('active-jobs', data.activeJobs, data.jobGrowth, '%');

                    // Update Pending Jobs (high priority)
                    this.updatePendingJobsCard(data.pendingJobs);

                    // Update KYC Verified
                    this.updateCard('verified-kyc', data.verifiedKyc);

                    // Update Pending KYC
                    this.updateCard('pending-kyc', data.pendingKyc);

                    // Update Total Applications
                    this.updateCard('total-applications', data.totalApplications, data.applicationGrowth, '%');

                    // Update badges in navigation
                    this.updateNavigationBadges(data);
                }

                updateCard(cardId, mainValue, growthValue = null, growthSuffix = '') {
                    const cardElement = document.getElementById(cardId);
                    if (!cardElement) return;

                    // Update main value with animation
                    const mainValueElement = cardElement.querySelector('.main-value, h2');
                    if (mainValueElement) {
                        const currentValue = parseInt(mainValueElement.textContent.replace(/,/g, '')) || 0;
                        const newValue = parseInt(mainValue) || 0;

                        if (currentValue !== newValue) {
                            // Add highlight animation
                            cardElement.classList.add('stat-updated');
                            setTimeout(() => cardElement.classList.remove('stat-updated'), 1000);

                            // Animate number change
                            this.animateNumber(mainValueElement, currentValue, newValue);
                        }
                    }

                    // Update growth value if provided
                    if (growthValue !== null) {
                        const growthElement = cardElement.querySelector('.growth-value, .small');
                        if (growthElement) {
                            const growthText = `${growthValue >= 0 ? '↗' : '↘'} ${Math.abs(growthValue)}${growthSuffix} from last month`;
                            growthElement.innerHTML = growthText;
                            growthElement.className = `small text-${growthValue >= 0 ? 'success' : 'danger'}`;
                        }
                    }
                }

                updatePendingJobsCard(pendingJobs) {
                    const cardElement = document.getElementById('pending-jobs');
                    if (!cardElement) return;

                    // Update main value
                    const mainValueElement = cardElement.querySelector('h2');
                    if (mainValueElement) {
                        const currentValue = parseInt(mainValueElement.textContent.replace(/,/g, '')) || 0;
                        const newValue = parseInt(pendingJobs) || 0;

                        if (currentValue !== newValue) {
                            cardElement.classList.add('stat-updated');
                            setTimeout(() => cardElement.classList.remove('stat-updated'), 1000);
                            this.animateNumber(mainValueElement, currentValue, newValue);

                            // Update warning styling
                            if (newValue > 0) {
                                mainValueElement.classList.add('text-warning');
                                cardElement.classList.add('border-warning');
                            } else {
                                mainValueElement.classList.remove('text-warning');
                                cardElement.classList.remove('border-warning');
                            }
                        }
                    }

                    // Update badge and link
                    const badge = cardElement.querySelector('.badge');
                    const link = cardElement.querySelector('a, .small');

                    if (pendingJobs > 0) {
                        if (badge) badge.textContent = pendingJobs;
                        if (link) {
                            link.innerHTML = '<i class="bi bi-arrow-right"></i> Review Now';
                            link.className = 'text-warning text-decoration-none';
                        }
                    } else {
                        if (badge) badge.style.display = 'none';
                        if (link) {
                            link.innerHTML = '<i class="bi bi-check-circle"></i> All jobs reviewed';
                            link.className = 'small text-success';
                        }
                    }
                }

                updateNavigationBadges(data) {
                    // Update jobs badge in navigation
                    const jobsBadge = document.querySelector('a[href*="admin.jobs"] .badge');
                    if (jobsBadge) {
                        jobsBadge.textContent = this.formatNumber(data.activeJobs);
                    }

                    // Update KYC pending count in notifications
                    const kycNotifications = document.querySelectorAll('[data-kyc-count]');
                    kycNotifications.forEach(el => {
                        el.textContent = `${data.pendingKyc} KYC pending review`;
                    });
                }

                animateNumber(element, from, to, duration = 1000) {
                    const startTime = performance.now();
                    const difference = to - from;

                    const step = (currentTime) => {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);

                        const current = Math.floor(from + (difference * this.easeOutCubic(progress)));
                        element.textContent = this.formatNumber(current);

                        if (progress < 1) {
                            requestAnimationFrame(step);
                        }
                    };

                    requestAnimationFrame(step);
                }

                easeOutCubic(t) {
                    return 1 - Math.pow(1 - t, 3);
                }

                formatNumber(num) {
                    return new Intl.NumberFormat().format(num);
                }

                showLoadingIndicator() {
                    // Add loading class to dashboard
                    document.body.classList.add('dashboard-updating');

                    // Show loading in refresh button if exists
                    const refreshBtn = document.getElementById('refresh-stats');
                    if (refreshBtn) {
                        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Updating...';
                        refreshBtn.disabled = true;
                    }
                }

                hideLoadingIndicator() {
                    document.body.classList.remove('dashboard-updating');

                    const refreshBtn = document.getElementById('refresh-stats');
                    if (refreshBtn) {
                        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';
                        refreshBtn.disabled = false;
                    }
                }

                showSuccessIndicator() {
                    this.showToast('Dashboard updated successfully', 'success');
                }

                showErrorIndicator() {
                    // Suppress error toast to prevent annoying users
                    // this.showToast('Failed to update dashboard', 'error');
                    console.log('Dashboard update failed - suppressed error toast');
                }

                showToast(message, type = 'info') {
                    // Simple toast notification
                    const toast = document.createElement('div');
                    toast.className = `toast-notification toast-${type}`;
                    toast.textContent = message;
                    toast.style.cssText = `
                                position: fixed;
                                top: 20px;
                                right: 20px;
                                padding: 12px 20px;
                                background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                                color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                                border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
                                border-radius: 4px;
                                z-index: 9999;
                                animation: slideIn 0.3s ease;
                            `;

                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.style.animation = 'slideOut 0.3s ease';
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                }
            }

            // Registration Chart
            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            new Chart(registrationCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($registrationData->pluck('day')) !!},
                    datasets: [{
                        label: 'New Registrations',
                        data: {!! json_encode($registrationData->pluck('count')) !!},
                        backgroundColor: [
                            '#0dcaf0', '#0effff', '#0aa2c0', '#087990', '#055160',
                            '#3ff', '#33ccff', '#0099cc', '#006699', '#003366'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // User Types Chart
            const userTypesCtx = document.getElementById('userTypesChart').getContext('2d');
            new Chart(userTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Job Seekers', 'Employers', 'Admins'],
                    datasets: [{
                        data: {!! json_encode($userTypeData) !!},
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Initialize dashboard updater
            document.addEventListener('DOMContentLoaded', () => {
                new AdminDashboardUpdater();
            });

            // Function to show simple toast
            function showSimpleToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.textContent = message;
                toast.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            padding: 12px 20px;
                            background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                            color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                            border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
                            border-radius: 6px;
                            z-index: 9999;
                            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                            font-weight: 500;
                        `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }

            // Manual refresh dashboard function
            function refreshDashboard() {
                const refreshBtn = document.getElementById('refresh-stats');
                const originalHTML = refreshBtn.innerHTML;

                // Show loading state
                refreshBtn.disabled = true;
                refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Refreshing...';

                // Clear cache and reload
                fetch('{{ route('admin.dashboard.clear-cache') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSimpleToast('Dashboard refreshed successfully!', 'success');
                            // Reload page after short delay to show the toast
                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing dashboard:', error);
                        showSimpleToast('Failed to refresh dashboard', 'error');
                        refreshBtn.disabled = false;
                        refreshBtn.innerHTML = originalHTML;
                    });
            }
        </script>
    @endpush
@endsection