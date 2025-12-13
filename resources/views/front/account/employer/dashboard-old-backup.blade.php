@extends('layouts.employer')

@section('page_title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Hero Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-card">
                <div class="hero-content">
                    <div class="hero-text">
                        <h2 class="hero-title">Welcome back, {{ auth()->user()->name }}! 👋</h2>
                        <p class="hero-subtitle">Here's your recruitment overview for today</p>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ route('employer.jobs.create') }}" class="btn-hero-primary">
                            <i class="bi bi-plus-circle me-2"></i>Post New Job
                        </a>
                        <a href="{{ route('employer.applications.index') }}" class="btn-hero-secondary">
                            <i class="bi bi-people me-2"></i>View Applications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    @php
        $unreadNotifications = \DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $hasNotifications = $unreadNotifications->count() > 0;
    @endphp

    @if($hasNotifications)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-dismissible fade show" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-bell-fill" style="font-size: 24px; color: white;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-2" style="color: white; font-weight: 600;">
                            <i class="bi bi-exclamation-circle me-2"></i>You have {{ $unreadNotifications->count() }} new notification{{ $unreadNotifications->count() > 1 ? 's' : '' }}
                        </h5>
                        <div class="notifications-list">
                            @foreach($unreadNotifications as $notification)
                                @php
                                    $data = json_decode($notification->data, true);
                                    $isApproved = $notification->type === 'job_approved';
                                    $isRejected = $notification->type === 'job_rejected';
                                @endphp
                                <div class="notification-item" style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px; margin-bottom: 10px; backdrop-filter: blur(10px);">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            @if($isApproved)
                                                <i class="bi bi-check-circle-fill" style="font-size: 20px; color: #10b981;"></i>
                                            @elseif($isRejected)
                                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; color: #fbbf24;"></i>
                                            @else
                                                <i class="bi bi-info-circle-fill" style="font-size: 20px; color: white;"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-1" style="color: white; font-weight: 500;">{{ $notification->message }}</p>
                                            @if($isRejected && isset($data['rejection_reason']))
                                                <p class="mb-2" style="color: rgba(255,255,255,0.9); font-size: 14px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 6px; border-left: 3px solid #fbbf24;">
                                                    <strong>Reason:</strong> {{ $data['rejection_reason'] }}
                                                </p>
                                            @endif
                                            <small style="color: rgba(255,255,255,0.7);">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                                        </div>
                                        <div class="flex-shrink-0 ms-3">
                                            <a href="{{ $notification->action_url ?? route('employer.jobs.index') }}" 
                                               class="btn btn-sm" 
                                               style="background: white; color: #667eea; font-weight: 600; border-radius: 6px;"
                                               onclick="markAsRead({{ $notification->id }})">
                                                @if($isRejected)
                                                    <i class="bi bi-pencil me-1"></i>Edit Job
                                                @else
                                                    <i class="bi bi-eye me-1"></i>View
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @php
                            $totalUnread = \DB::table('notifications')
                                ->where('user_id', auth()->id())
                                ->whereNull('read_at')
                                ->count();
                        @endphp
                        @if($totalUnread > 5)
                            <div class="mt-3">
                                <a href="#" style="color: white; text-decoration: underline;">
                                    View all {{ $totalUnread }} notifications
                                </a>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @php
                                $recentNotifications = \DB::table('notifications')
                                    ->where('user_id', auth()->id())
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            @if($recentNotifications->count() > 0)
                                @foreach($recentNotifications as $notification)
                                    @php
                                        $data = json_decode($notification->data, true);
                                        $isUnread = is_null($notification->read_at);
                                    @endphp
                                    <li>
                                        <a class="dropdown-item {{ $isUnread ? 'bg-light' : '' }}" href="{{ $notification->action_url ?? '#' }}" onclick="markAsRead({{ $notification->id }})">
                                            <div class="d-flex align-items-start">
                                                <div class="me-2">
                                                    @if($notification->type === 'job_approved')
                                                        <i class="bi bi-check-circle-fill text-success" style="font-size: 1.2rem;"></i>
                                                    @elseif($notification->type === 'job_rejected')
                                                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 1.2rem;"></i>
                                                    @else
                                                        <i class="bi bi-info-circle-fill text-primary" style="font-size: 1.2rem;"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small" style="color: #000000 !important;">{{ $notification->title }}</div>
                                                    <div class="small" style="color: #000000 !important;">{{ Str::limit($notification->message, 60) }}</div>
                                                    <div style="font-size: 0.75rem; color: #666666 !important;">
                                                        {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                                    </div>
                                                </div>
                                                @if($isUnread)
                                                    <div class="ms-2">
                                                        <span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px; padding: 0;"></span>
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </li>
                                    @if(!$loop->last)
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                @endforeach
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center small fw-semibold" href="#" style="color: #4f46e5;">
                                        View All Notifications
                                    </a>
                                </li>
                            @else
                                <li>
                                    <div class="dropdown-item text-center py-4">
                                        <i class="bi bi-bell-slash" style="font-size: 2rem; color: #9ca3af;"></i>
                                        <div class="mt-2" style="color: #6b7280;">No notifications</div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Profile Picture Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; width: 45px; height: 45px; overflow: hidden; border: 2px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            @if(auth()->user()->image)
                                <img src="{{ asset('storage/' . auth()->user()->image) }}" alt="{{ auth()->user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; margin-right: 10px;">
                                        @if(auth()->user()->image)
                                            <img src="{{ asset('storage/' . auth()->user()->image) }}" alt="{{ auth()->user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: #000000 !important;">{{ auth()->user()->name }}</div>
                                        <div class="small" style="color: #666666 !important;">{{ auth()->user()->email }}</div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('employer.profile.edit') }}" style="color: #000000 !important;"><i class="bi bi-person me-2"></i>Company Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('employer.dashboard') }}" style="color: #000000 !important;"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('employer.jobs.index') }}" style="color: #000000 !important;"><i class="bi bi-briefcase me-2"></i>My Jobs</a></li>
                            <li><a class="dropdown-item" href="{{ route('employer.applications.index') }}" style="color: #000000 !important;"><i class="bi bi-people me-2"></i>Applications</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" style="color: #000000 !important;"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1" style="color: #1f2937;">Here's what's happening with your jobs today.</h1>
                    </div>
                    <div>
                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary d-flex align-items-center px-4 py-2">
                            <i class="bi bi-plus-circle me-2"></i>Post New Job
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    @php
        $unreadNotifications = \DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $hasNotifications = $unreadNotifications->count() > 0;
    @endphp

    @if($hasNotifications)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-dismissible fade show" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-bell-fill" style="font-size: 24px; color: white;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-2" style="color: white; font-weight: 600;">
                            <i class="bi bi-exclamation-circle me-2"></i>You have {{ $unreadNotifications->count() }} new notification{{ $unreadNotifications->count() > 1 ? 's' : '' }}
                        </h5>
                        <div class="notifications-list">
                            @foreach($unreadNotifications as $notification)
                                @php
                                    $data = json_decode($notification->data, true);
                                    $isApproved = $notification->type === 'job_approved';
                                    $isRejected = $notification->type === 'job_rejected';
                                @endphp
                                <div class="notification-item" style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px; margin-bottom: 10px; backdrop-filter: blur(10px);">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            @if($isApproved)
                                                <i class="bi bi-check-circle-fill" style="font-size: 20px; color: #10b981;"></i>
                                            @elseif($isRejected)
                                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; color: #fbbf24;"></i>
                                            @else
                                                <i class="bi bi-info-circle-fill" style="font-size: 20px; color: white;"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-1" style="color: white; font-weight: 500;">{{ $notification->message }}</p>
                                            @if($isRejected && isset($data['rejection_reason']))
                                                <p class="mb-2" style="color: rgba(255,255,255,0.9); font-size: 14px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 6px; border-left: 3px solid #fbbf24;">
                                                    <strong>Reason:</strong> {{ $data['rejection_reason'] }}
                                                </p>
                                            @endif
                                            <small style="color: rgba(255,255,255,0.7);">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                                        </div>
                                        <div class="flex-shrink-0 ms-3">
                                            <a href="{{ $notification->action_url ?? route('employer.jobs.index') }}" 
                                               class="btn btn-sm" 
                                               style="background: white; color: #667eea; font-weight: 600; border-radius: 6px;"
                                               onclick="markAsRead({{ $notification->id }})"
                                                @if($isRejected)
                                                    <i class="bi bi-pencil me-1"></i>Edit Job
                                                @else
                                                    <i class="bi bi-eye me-1"></i>View
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @php
                            $totalUnread = \DB::table('notifications')
                                ->where('user_id', auth()->id())
                                ->whereNull('read_at')
                                ->count();
                        @endphp
                        @if($totalUnread > 5)
                            <div class="mt-3">
                                <a href="#" style="color: white; text-decoration: underline;">
                                    View all {{ $totalUnread }} notifications
                                </a>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    @endif

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
</div>

<style>
/* Employer Dashboard Background Styling */
.employer-dashboard-container {
    background-color: var(--gray-50, #0d0f11ff);
    min-height: 100vh;
    padding: 2rem 0;
    color: #000; /* Text color black */
}

/* Ensure consistent background for the main content area */
.main-content {
    background-color: var(--black-50, #080c0fff);
    color: #000; /* Text color black */
}

.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
    color: #000; /* Black text */
}

.quick-action-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bs-white);
    border-radius: 0.5rem;
    text-decoration: none;
    color: #000; /* Black text */
    transition: all 0.2s ease;
    box-shadow: var(--bs-box-shadow-sm);
}

.quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--bs-box-shadow);
    color: #000; /* Keep text black on hover */
}

.quick-action-card .icon-wrapper {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    color: #000; /* Black text */
}

.stats-card {
    transition: all 0.2s ease;
    color: #000; /* Black text */
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--bs-box-shadow);
    color: #000; /* Keep text black on hover */
}

.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000; /* Black text */
}

.empty-state {
    background: linear-gradient(to bottom, var(--bs-light), var(--bs-white));
    color: #000; /* Black text */
}

/* Force Bootstrap utility text classes to black */
.text-muted, 
.text-success, 
.text-warning, 
.text-info, 
.text-primary, 
.text-danger {
    color: #000 !important;
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

</style>

@push('scripts')
<script>
// Mark notification as read
function markAsRead(notificationId) {
    fetch(`/notifications/mark-as-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notification_id: notificationId })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              console.log('Notification marked as read');
          }
      })
      .catch(error => console.error('Error:', error));
    
    return true; // Allow navigation to continue
}

// Mark all notifications as read
function markAllAsRead() {
    fetch('/notifications/mark-all-as-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              // Reload the page to update the notification count
              window.location.reload();
          }
      })
      .catch(error => console.error('Error:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    // Dashboard Search Functionality
    const searchInput = document.getElementById('dashboardSearch');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterJobs(searchTerm, statusFilter.value);
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function(e) {
            const searchTerm = searchInput.value.toLowerCase();
            filterJobs(searchTerm, e.target.value);
        });
    }
    
    function filterJobs(searchTerm, status) {
        const jobRows = document.querySelectorAll('.table tbody tr');
        
        jobRows.forEach(row => {
            const title = row.querySelector('h6 a')?.textContent.toLowerCase() || '';
            const location = row.querySelector('.small.text-muted')?.textContent.toLowerCase() || '';
            const jobStatus = row.querySelector('.badge')?.textContent.toLowerCase().trim() || '';
            
            const matchesSearch = title.includes(searchTerm) || location.includes(searchTerm);
            const matchesStatus = !status || jobStatus === status.toLowerCase();
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    // Applications Chart
    const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
    const applicationsChart = new Chart(applicationsCtx, {
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

@push('styles')
<style>
/* Clean Dashboard Design */
.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s ease;
    min-width: 140px;
}

.quick-action-card:hover {
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
}

.quick-action-card .icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.quick-action-card span {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.stats-card {
    border: 1px solid #e5e7eb !important;
}

.stats-icon {
    width: 56px !important;
    height: 56px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 1.5rem !important;
}
</style>
@endpush

@endsection 