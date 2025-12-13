@extends('layouts.jobseeker')

@section('page-title', 'Dashboard')

@section('jobseeker-content')
<style>
/* Dashboard Professional Styles */
.dashboard-pro {
    padding: 0;
}

/* Welcome Header */
.welcome-header-pro {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 1rem;
    padding: 2rem;
    color: white;
    margin-bottom: 1.75rem;
    position: relative;
    overflow: hidden;
}

.welcome-header-pro::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.welcome-header-pro .content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.welcome-header-pro .welcome-text h1 {
    font-size: 1.625rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: white;
}

.welcome-header-pro .welcome-text p {
    font-size: 0.9375rem;
    opacity: 0.9;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    color: white !important;
}

.welcome-header-pro .welcome-text p span,
.welcome-header-pro .welcome-text p i {
    color: white !important;
}

/* Completion Ring */
.completion-ring-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255,255,255,0.1);
    padding: 0.75rem 1.25rem;
    border-radius: 0.75rem;
    backdrop-filter: blur(10px);
}

.completion-ring-wrapper .ring-container {
    position: relative;
    width: 56px;
    height: 56px;
}

.completion-ring-wrapper svg {
    transform: rotate(-90deg);
    width: 56px;
    height: 56px;
}

.completion-ring-wrapper .ring-bg {
    fill: transparent;
    stroke: rgba(255,255,255,0.2);
    stroke-width: 5;
}

.completion-ring-wrapper .ring-progress {
    fill: transparent;
    stroke: white;
    stroke-width: 5;
    stroke-linecap: round;
}

.completion-ring-wrapper .ring-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.875rem;
    font-weight: 700;
    color: white;
}

.completion-ring-wrapper .ring-info {
    text-align: left;
}

.completion-ring-wrapper .ring-label {
    font-size: 0.75rem;
    opacity: 0.8;
    display: block;
}

.completion-ring-wrapper .ring-status {
    font-size: 0.8125rem;
    font-weight: 600;
}

/* Stats Grid */
.stats-grid-dashboard {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card-dashboard {
    background: white;
    border-radius: 0.875rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.stat-card-dashboard::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--stat-color, #4f46e5);
}

.stat-card-dashboard:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    border-color: transparent;
}

.stat-card-dashboard .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    background: var(--stat-bg, #eef2ff);
    color: var(--stat-color, #4f46e5);
}

.stat-card-dashboard .stat-number {
    font-size: 1.75rem;
    font-weight: 800;
    color: #111827;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-card-dashboard .stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.875rem;
}

.stat-card-dashboard .stat-link {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--stat-color, #4f46e5);
    text-decoration: none;
    transition: gap 0.2s ease;
}

.stat-card-dashboard .stat-link:hover {
    gap: 0.5rem;
}

/* Stat color variants */
.stat-card-dashboard.primary { --stat-color: #4f46e5; --stat-bg: #eef2ff; }
.stat-card-dashboard.pink { --stat-color: #ec4899; --stat-bg: #fdf2f8; }
.stat-card-dashboard.cyan { --stat-color: #06b6d4; --stat-bg: #ecfeff; }
.stat-card-dashboard.green { --stat-color: #10b981; --stat-bg: #ecfdf5; }

/* Section Header */
.section-header-dashboard {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.section-header-dashboard h2 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.section-header-dashboard .view-all {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #4f46e5;
    text-decoration: none;
    transition: gap 0.2s ease;
}

.section-header-dashboard .view-all:hover {
    gap: 0.5rem;
}

/* Recent Applications */
.applications-list-dashboard {
    background: white;
    border-radius: 0.875rem;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    margin-bottom: 2rem;
}

.application-item-dashboard {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.125rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s ease;
}

.application-item-dashboard:last-child {
    border-bottom: none;
}

.application-item-dashboard:hover {
    background: #f9fafb;
}

.application-item-dashboard .company-logo {
    width: 44px;
    height: 44px;
    border-radius: 0.5rem;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.application-item-dashboard .company-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.application-item-dashboard .company-logo i {
    color: #9ca3af;
    font-size: 1.125rem;
}

.application-item-dashboard .job-info {
    flex: 1;
    min-width: 0;
}

.application-item-dashboard .job-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
    text-decoration: none;
    display: block;
}

.application-item-dashboard .job-title:hover {
    color: #4f46e5;
}

.application-item-dashboard .company-name {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0 0 0.25rem 0;
}

.application-item-dashboard .applied-date {
    font-size: 0.75rem;
    color: #9ca3af;
}

.application-item-dashboard .status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}

.application-item-dashboard .status-badge.pending {
    background: #fef3c7;
    color: #b45309;
}

.application-item-dashboard .status-badge.approved {
    background: #d1fae5;
    color: #047857;
}

.application-item-dashboard .status-badge.rejected {
    background: #fee2e2;
    color: #b91c1c;
}

/* Quick Actions Grid */
.quick-actions-dashboard {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

.quick-action-card {
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.875rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.quick-action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    border-color: var(--action-color, #4f46e5);
}

.quick-action-card .action-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    background: var(--action-bg, #eef2ff);
    color: var(--action-color, #4f46e5);
}

.quick-action-card .action-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.375rem;
}

.quick-action-card .action-desc {
    font-size: 0.8125rem;
    color: #6b7280;
    line-height: 1.5;
}

/* Action card color variants */
.quick-action-card.purple { --action-color: #7c3aed; --action-bg: #f3e8ff; }
.quick-action-card.pink { --action-color: #ec4899; --action-bg: #fdf2f8; }
.quick-action-card.cyan { --action-color: #06b6d4; --action-bg: #ecfeff; }
.quick-action-card.orange { --action-color: #f97316; --action-bg: #fff7ed; }

/* Empty State */
.empty-state-dashboard {
    text-align: center;
    padding: 3rem 2rem;
    background: white;
    border-radius: 0.875rem;
    border: 1px solid #e5e7eb;
    margin-bottom: 2rem;
}

.empty-state-dashboard .empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.25rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state-dashboard .empty-icon i {
    font-size: 2rem;
    color: #9ca3af;
}

.empty-state-dashboard h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.empty-state-dashboard p {
    font-size: 0.9375rem;
    color: #6b7280;
    margin: 0 0 1.25rem 0;
}

.empty-state-dashboard .btn-primary {
    background: #4f46e5;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    transition: background 0.2s ease;
}

.empty-state-dashboard .btn-primary:hover {
    background: #4338ca;
    color: white;
}

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid-dashboard,
    .quick-actions-dashboard {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .welcome-header-pro {
        padding: 1.5rem;
    }

    .welcome-header-pro .content {
        flex-direction: column;
        align-items: flex-start;
    }

    .welcome-header-pro .welcome-text h1 {
        font-size: 1.375rem;
    }

    .completion-ring-wrapper {
        align-self: stretch;
        justify-content: center;
    }

    .stats-grid-dashboard {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.875rem;
    }

    .stat-card-dashboard {
        padding: 1.25rem;
    }

    .stat-card-dashboard .stat-number {
        font-size: 1.5rem;
    }

    .quick-actions-dashboard {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.875rem;
    }

    .quick-action-card {
        padding: 1.25rem;
    }
}

@media (max-width: 480px) {
    .stats-grid-dashboard,
    .quick-actions-dashboard {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-pro">
    <!-- Welcome Header -->
    <div class="welcome-header-pro">
        <div class="content">
            <div class="welcome-text">
                <h1>Welcome back, {{ Auth::check() ? explode(' ', Auth::user()->name)[0] : 'Guest' }}!</h1>
                <p>
                    <i class="far fa-calendar-alt"></i>
                    <span>{{ date('l, F j, Y') }}</span>
                    <span style="opacity: 0.6;">|</span>
                    <span>Here's your job search overview</span>
                </p>
            </div>
            <div class="completion-ring-wrapper">
                @php
                    $percentage = $completionPercentage ?? 0;
                    $radius = 22;
                    $circumference = 2 * pi() * $radius;
                    $offset = $circumference - ($percentage / 100) * $circumference;
                @endphp
                <div class="ring-container">
                    <svg viewBox="0 0 56 56">
                        <circle class="ring-bg" cx="28" cy="28" r="{{ $radius }}"/>
                        <circle class="ring-progress" cx="28" cy="28" r="{{ $radius }}"
                                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"/>
                    </svg>
                    <span class="ring-value">{{ $percentage }}%</span>
                </div>
                <div class="ring-info">
                    <span class="ring-label">Profile</span>
                    <span class="ring-status">{{ $percentage >= 100 ? 'Complete' : 'In Progress' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Status -->
    <x-kyc-status-card :user="Auth::user()" />

    <!-- Stats Grid -->
    <div class="stats-grid-dashboard">
        <div class="stat-card-dashboard primary">
            <div class="stat-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-number">{{ $stats['applications'] ?? 0 }}</div>
            <div class="stat-label">Total Applications</div>
            <a href="{{ route('account.myJobApplications') }}" class="stat-link">
                View all <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card-dashboard pink">
            <div class="stat-icon">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="stat-number">{{ $stats['saved_jobs'] ?? 0 }}</div>
            <div class="stat-label">Saved Jobs</div>
            <a href="{{ route('account.saved-jobs.index') }}" class="stat-link">
                View all <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card-dashboard cyan">
            <div class="stat-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-number">{{ $stats['profile_views'] ?? 0 }}</div>
            <div class="stat-label">Profile Views</div>
            <a href="{{ route('account.analytics') }}" class="stat-link">
                View analytics <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card-dashboard green">
            <div class="stat-icon">
                @if(($completionPercentage ?? 0) >= 100)
                    <i class="fas fa-check-circle"></i>
                @else
                    <i class="fas fa-user-edit"></i>
                @endif
            </div>
            <div class="stat-number">{{ $completionPercentage ?? 0 }}%</div>
            <div class="stat-label">Profile Complete</div>
            <a href="{{ route('account.myProfile') }}" class="stat-link">
                Complete profile <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Recent Applications -->
    @if(isset($recentApplications) && $recentApplications->count() > 0)
    <div class="section-header-dashboard">
        <h2>Recent Applications</h2>
        <a href="{{ route('account.myJobApplications') }}" class="view-all">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="applications-list-dashboard">
        @foreach($recentApplications as $application)
            @if($application->job)
            <div class="application-item-dashboard">
                <div class="company-logo">
                    @if($application->job->company && $application->job->company->logo)
                        <img src="{{ asset('storage/' . $application->job->company->logo) }}" alt="Logo">
                    @else
                        <i class="fas fa-building"></i>
                    @endif
                </div>
                <div class="job-info">
                    <a href="{{ route('jobDetail', $application->job->id) }}" class="job-title">
                        {{ $application->job->title ?? 'Job Position' }}
                    </a>
                    <p class="company-name">
                        {{ $application->job->company->name ?? $application->job->employer->name ?? 'Company' }}
                    </p>
                    <span class="applied-date">
                        <i class="far fa-calendar me-1"></i>Applied {{ $application->created_at->diffForHumans() }}
                    </span>
                </div>
                <div>
                    @if($application->status == 'pending')
                        <span class="status-badge pending">Pending</span>
                    @elseif($application->status == 'approved' || $application->status == 'accepted')
                        <span class="status-badge approved">Approved</span>
                    @elseif($application->status == 'rejected')
                        <span class="status-badge rejected">Rejected</span>
                    @endif
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @else
    <div class="empty-state-dashboard">
        <div class="empty-icon">
            <i class="fas fa-briefcase"></i>
        </div>
        <h3>No Applications Yet</h3>
        <p>Start your job search journey by applying to positions that match your skills.</p>
        <a href="{{ route('jobs') }}" class="btn-primary">
            <i class="fas fa-search"></i> Browse Jobs
        </a>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="section-header-dashboard">
        <h2>Quick Actions</h2>
    </div>
    <div class="quick-actions-dashboard">
        <a href="{{ route('account.resume-builder.index') }}" class="quick-action-card purple">
            <div class="action-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="action-title">Create Resume</div>
            <p class="action-desc">Build a professional resume with our easy-to-use builder</p>
        </a>

        <a href="{{ route('account.saved-jobs.index') }}" class="quick-action-card pink">
            <div class="action-icon">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="action-title">Saved Jobs</div>
            <p class="action-desc">View and manage your saved job opportunities</p>
        </a>

        <a href="{{ route('account.analytics') }}" class="quick-action-card cyan">
            <div class="action-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="action-title">Analytics</div>
            <p class="action-desc">Track your job search progress and insights</p>
        </a>

        <a href="{{ route('account.myProfile') }}" class="quick-action-card orange">
            <div class="action-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="action-title">Edit Profile</div>
            <p class="action-desc">Update your information and preferences</p>
        </a>
    </div>
</div>
@endsection
