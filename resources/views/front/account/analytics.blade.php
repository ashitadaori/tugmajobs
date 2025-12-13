@extends('layouts.jobseeker')

@section('page-title', 'Analytics')

@section('jobseeker-content')
<div class="analytics-dashboard">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">Analytics Dashboard</h1>
            <p class="page-description">Track your job search progress and career insights</p>
        </div>
        <div class="header-meta">
            <span class="last-updated">
                <i class="fas fa-sync-alt"></i>
                Updated just now
            </span>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-details">
                <span class="stat-value">{{ $totalApplications }}</span>
                <span class="stat-label">Total Applications</span>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-details">
                <span class="stat-value">{{ $pendingApplications }}</span>
                <span class="stat-label">Pending Review</span>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-value">{{ $acceptedApplications }}</span>
                <span class="stat-label">Accepted</span>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-value">{{ $rejectedApplications }}</span>
                <span class="stat-label">Not Selected</span>
            </div>
        </div>
    </div>

    <!-- Profile Views Stats -->
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-eye"></i>
            Profile Visibility
        </h2>
    </div>

    <div class="profile-stats-grid">
        <div class="profile-stat-card">
            <div class="profile-stat-icon total">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="profile-stat-content">
                <span class="profile-stat-value">{{ $totalProfileViews }}</span>
                <span class="profile-stat-label">Total Views</span>
            </div>
        </div>

        <div class="profile-stat-card">
            <div class="profile-stat-icon weekly">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="profile-stat-content">
                <span class="profile-stat-value">{{ $profileViewsThisWeek }}</span>
                <span class="profile-stat-label">This Week</span>
            </div>
        </div>

        <div class="profile-stat-card">
            <div class="profile-stat-icon monthly">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="profile-stat-content">
                <span class="profile-stat-value">{{ $profileViewsThisMonth }}</span>
                <span class="profile-stat-label">This Month</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-container">
        <!-- Application Trends -->
        <div class="chart-card chart-wide">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3 class="chart-title">Application Activity</h3>
                    <p class="chart-subtitle">Your applications over the last 30 days</p>
                </div>
            </div>
            <div class="chart-content">
                <canvas id="applicationTrendsChart"></canvas>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="chart-card chart-narrow">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3 class="chart-title">Status Overview</h3>
                    <p class="chart-subtitle">Application distribution</p>
                </div>
            </div>
            <div class="chart-content chart-centered">
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Secondary Charts -->
    <div class="charts-container secondary">
        <!-- Category Distribution -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3 class="chart-title">Top Categories</h3>
                    <p class="chart-subtitle">Industries you've applied to</p>
                </div>
            </div>
            <div class="chart-content">
                <canvas id="categoryDistributionChart"></canvas>
            </div>
        </div>

        <!-- Success Metrics -->
        <div class="chart-card metrics-card">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3 class="chart-title">Performance Metrics</h3>
                    <p class="chart-subtitle">Your success rates</p>
                </div>
            </div>
            <div class="metrics-content">
                @php
                    $acceptanceRate = $totalApplications > 0 ? round(($acceptedApplications / $totalApplications) * 100, 1) : 0;
                    $pendingRate = $totalApplications > 0 ? round(($pendingApplications / $totalApplications) * 100, 1) : 0;
                    $rejectionRate = $totalApplications > 0 ? round(($rejectedApplications / $totalApplications) * 100, 1) : 0;
                @endphp

                <div class="metric-item">
                    <div class="metric-header">
                        <span class="metric-name">
                            <i class="fas fa-trophy text-success"></i>
                            Acceptance Rate
                        </span>
                        <span class="metric-percentage {{ $acceptanceRate >= 20 ? 'text-success' : ($acceptanceRate >= 10 ? 'text-warning' : 'text-muted') }}">
                            {{ $acceptanceRate }}%
                        </span>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-fill success" style="width: {{ min($acceptanceRate, 100) }}%"></div>
                    </div>
                </div>

                <div class="metric-item">
                    <div class="metric-header">
                        <span class="metric-name">
                            <i class="fas fa-clock text-warning"></i>
                            Pending Rate
                        </span>
                        <span class="metric-percentage text-warning">{{ $pendingRate }}%</span>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-fill warning" style="width: {{ min($pendingRate, 100) }}%"></div>
                    </div>
                </div>

                <div class="metric-item">
                    <div class="metric-header">
                        <span class="metric-name">
                            <i class="fas fa-times text-danger"></i>
                            Rejection Rate
                        </span>
                        <span class="metric-percentage text-danger">{{ $rejectionRate }}%</span>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-fill danger" style="width: {{ min($rejectionRate, 100) }}%"></div>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="insight-content">
                        @if($totalApplications == 0)
                            <p>Start applying to jobs to see your performance metrics!</p>
                        @elseif($acceptanceRate >= 20)
                            <p>Excellent work! Your acceptance rate is above average. Keep up the momentum!</p>
                        @elseif($acceptanceRate >= 10)
                            <p>You're on track! Consider tailoring your applications more to boost your success rate.</p>
                        @else
                            <p>Tip: Customize your resume for each application to improve your chances.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-history"></i>
            Recent Applications
        </h2>
        <a href="{{ route('account.myJobApplications') }}" class="view-all-link">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="applications-card">
        <div class="applications-list">
            @forelse($recentApplications as $application)
            <div class="application-row">
                <div class="application-main">
                    <h4 class="job-title">{{ $application->job->title ?? 'Job Title Not Available' }}</h4>
                    <div class="job-meta">
                        <span class="job-category">
                            <i class="fas fa-folder"></i>
                            {{ $application->job->category->name ?? 'Uncategorized' }}
                        </span>
                        <span class="job-date">
                            <i class="fas fa-clock"></i>
                            {{ $application->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                <div class="application-status">
                    <span class="status-pill status-{{ strtolower($application->status ?? 'pending') }}">
                        {{ ucfirst($application->status ?? 'pending') }}
                    </span>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h4>No Applications Yet</h4>
                <p>Start your job search and track your progress here</p>
                <a href="{{ route('jobs') }}" class="btn-primary-action">
                    <i class="fas fa-search"></i>
                    Find Jobs
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Profile Viewers -->
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-users"></i>
            Profile Viewers
        </h2>
    </div>

    <div class="viewers-card">
        <div class="viewers-list">
            @forelse($recentProfileViewers as $view)
            <div class="viewer-row">
                <div class="viewer-avatar">
                    @if($view->viewer && $view->viewer->employerProfile && $view->viewer->employerProfile->logo)
                        <img src="{{ asset('storage/' . $view->viewer->employerProfile->logo) }}" alt="{{ $view->viewer->name }}">
                    @else
                        <div class="avatar-placeholder">
                            <i class="fas fa-building"></i>
                        </div>
                    @endif
                </div>
                <div class="viewer-details">
                    <h4 class="viewer-name">
                        {{ $view->viewer->name ?? 'Anonymous Employer' }}
                        @if($view->viewer_type === 'employer')
                            <span class="employer-badge">Employer</span>
                        @endif
                    </h4>
                    <p class="viewer-company">
                        @if($view->viewer && $view->viewer->employerProfile)
                            {{ $view->viewer->employerProfile->company_name ?? 'Company' }}
                        @else
                            Viewing Company
                        @endif
                    </p>
                </div>
                <div class="viewer-meta">
                    <span class="view-time">
                        <i class="fas fa-clock"></i>
                        {{ $view->viewed_at->diffForHumans() }}
                    </span>
                    @if($view->source)
                        <span class="view-source">
                            via {{ ucfirst(str_replace('_', ' ', $view->source)) }}
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h4>No Profile Views Yet</h4>
                <p>Apply to more jobs to increase your visibility to employers</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
/* Analytics Dashboard Styles */
.analytics-dashboard {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.page-description {
    color: #6b7280;
    font-size: 0.9375rem;
    margin: 0;
}

.header-meta {
    display: flex;
    align-items: center;
}

.last-updated {
    font-size: 0.8125rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.last-updated i {
    font-size: 0.75rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: #ffffff;
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid #f3f4f6;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.stat-primary .stat-icon {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
}

.stat-warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
    color: white;
}

.stat-success .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: white;
}

.stat-danger .stat-icon {
    background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
    color: white;
}

.stat-details {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8125rem;
    color: #6b7280;
    font-weight: 500;
}

/* Section Headers */
.analytics-dashboard .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
    margin-top: 0.5rem;
}

.analytics-dashboard .section-header .section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827 !important;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    text-transform: none;
    letter-spacing: normal;
}

.analytics-dashboard .section-header .section-title i {
    color: #6366f1;
    font-size: 1rem;
}

.view-all-link {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6366f1;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    transition: color 0.2s;
}

.view-all-link:hover {
    color: #4f46e5;
}

.view-all-link i {
    font-size: 0.75rem;
}

/* Profile Stats Grid */
.profile-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}

.profile-stat-card {
    background: #ffffff;
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid #f3f4f6;
}

.profile-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.profile-stat-icon.total {
    background: #ede9fe;
    color: #7c3aed;
}

.profile-stat-icon.weekly {
    background: #d1fae5;
    color: #059669;
}

.profile-stat-icon.monthly {
    background: #fef3c7;
    color: #d97706;
}

.profile-stat-content {
    display: flex;
    flex-direction: column;
}

.profile-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
}

.profile-stat-label {
    font-size: 0.8125rem;
    color: #6b7280;
    font-weight: 500;
}

/* Charts Container */
.charts-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.charts-container.secondary {
    grid-template-columns: 1fr 1fr;
}

.chart-card {
    background: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid #f3f4f6;
    overflow: hidden;
}

.chart-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
}

.chart-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.chart-subtitle {
    font-size: 0.8125rem;
    color: #9ca3af;
    margin: 0;
}

.chart-content {
    padding: 1.5rem;
    height: 280px;
}

.chart-content.chart-centered {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Metrics Card */
.metrics-content {
    padding: 1.5rem;
}

.metric-item {
    margin-bottom: 1.25rem;
}

.metric-item:last-of-type {
    margin-bottom: 0;
}

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.metric-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.metric-name i {
    font-size: 0.875rem;
}

.metric-percentage {
    font-size: 1rem;
    font-weight: 700;
}

.metric-bar {
    height: 8px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.metric-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.metric-fill.success {
    background: linear-gradient(90deg, #10b981, #34d399);
}

.metric-fill.warning {
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.metric-fill.danger {
    background: linear-gradient(90deg, #ef4444, #f87171);
}

.insight-card {
    margin-top: 1.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 0.75rem;
    border-left: 4px solid #6366f1;
    display: flex;
    gap: 0.75rem;
}

.insight-icon {
    width: 32px;
    height: 32px;
    background: #6366f1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.insight-content p {
    margin: 0;
    font-size: 0.875rem;
    color: #4b5563;
    line-height: 1.5;
}

/* Applications Card */
.applications-card,
.viewers-card {
    background: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid #f3f4f6;
    margin-bottom: 2rem;
    overflow: hidden;
}

.applications-list,
.viewers-list {
    max-height: 400px;
    overflow-y: auto;
}

.application-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.application-row:last-child {
    border-bottom: none;
}

.application-row:hover {
    background: #f9fafb;
}

.application-main {
    flex: 1;
    min-width: 0;
}

.job-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.375rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.job-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.job-category,
.job-date {
    font-size: 0.8125rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.job-category i,
.job-date i {
    font-size: 0.75rem;
    color: #9ca3af;
}

.status-pill {
    padding: 0.375rem 0.875rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-accepted {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.status-interview {
    background: #dbeafe;
    color: #1e40af;
}

/* Viewers */
.viewer-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.viewer-row:last-child {
    border-bottom: none;
}

.viewer-row:hover {
    background: #f9fafb;
}

.viewer-avatar {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
}

.viewer-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.viewer-avatar .avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
}

.viewer-details {
    flex: 1;
    min-width: 0;
}

.viewer-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.employer-badge {
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    background: #6366f1;
    color: white;
    padding: 0.125rem 0.5rem;
    border-radius: 1rem;
}

.viewer-company {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0;
}

.viewer-meta {
    text-align: right;
}

.view-time {
    font-size: 0.8125rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    justify-content: flex-end;
}

.view-time i {
    font-size: 0.75rem;
}

.view-source {
    font-size: 0.75rem;
    color: #9ca3af;
    display: block;
    margin-top: 0.25rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-icon {
    width: 64px;
    height: 64px;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: #9ca3af;
    font-size: 1.5rem;
}

.empty-state h4 {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.5rem 0;
}

.empty-state p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 1.25rem 0;
}

.btn-primary-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    color: white;
}

/* Scrollbar */
.applications-list::-webkit-scrollbar,
.viewers-list::-webkit-scrollbar {
    width: 6px;
}

.applications-list::-webkit-scrollbar-track,
.viewers-list::-webkit-scrollbar-track {
    background: #f3f4f6;
}

.applications-list::-webkit-scrollbar-thumb,
.viewers-list::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.applications-list::-webkit-scrollbar-thumb:hover,
.viewers-list::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Text Colors */
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-muted { color: #9ca3af !important; }

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-container {
        grid-template-columns: 1fr;
    }

    .charts-container.secondary {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .analytics-dashboard {
        padding: 1.25rem;
    }

    .page-header {
        flex-direction: column;
        gap: 0.75rem;
    }

    .page-title {
        font-size: 1.5rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .profile-stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .stat-card {
        padding: 1.25rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .application-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .viewer-row {
        flex-wrap: wrap;
    }

    .viewer-meta {
        width: 100%;
        text-align: left;
        margin-top: 0.5rem;
        padding-left: calc(48px + 1rem);
    }

    .chart-content {
        height: 250px;
    }
}

@media (max-width: 480px) {
    .analytics-dashboard {
        padding: 1rem;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .job-meta {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    font: {
                        family: "'Inter', sans-serif",
                        size: 12
                    },
                    usePointStyle: true,
                    padding: 16
                }
            },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                titleFont: {
                    family: "'Inter', sans-serif",
                    size: 13,
                    weight: '600'
                },
                bodyFont: {
                    family: "'Inter', sans-serif",
                    size: 12
                },
                padding: 12,
                cornerRadius: 8,
                displayColors: false
            }
        }
    };

    // Application Trends Chart
    const trendsCtx = document.getElementById('applicationTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($applicationTrends->pluck('date')) !!},
            datasets: [{
                label: 'Applications',
                data: {!! json_encode($applicationTrends->pluck('count')) !!},
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#9ca3af',
                        callback: value => Number.isInteger(value) ? value : ''
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.04)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#9ca3af',
                        maxRotation: 45
                    },
                    grid: { display: false }
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Accepted', 'Rejected'],
            datasets: [{
                data: [{{ $pendingApplications }}, {{ $acceptedApplications }}, {{ $rejectedApplications }}],
                backgroundColor: [
                    'rgba(251, 191, 36, 0.85)',
                    'rgba(16, 185, 129, 0.85)',
                    'rgba(239, 68, 68, 0.85)'
                ],
                borderColor: ['#fbbf24', '#10b981', '#ef4444'],
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            ...commonOptions,
            cutout: '65%',
            plugins: {
                ...commonOptions.plugins,
                legend: {
                    position: 'bottom',
                    labels: {
                        ...commonOptions.plugins.legend.labels,
                        padding: 20
                    }
                }
            }
        }
    });

    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($categoryStats->pluck('name')) !!},
            datasets: [{
                label: 'Applications',
                data: {!! json_encode($categoryStats->pluck('count')) !!},
                backgroundColor: [
                    'rgba(99, 102, 241, 0.85)',
                    'rgba(139, 92, 246, 0.85)',
                    'rgba(236, 72, 153, 0.85)',
                    'rgba(251, 146, 60, 0.85)',
                    'rgba(34, 197, 94, 0.85)'
                ],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#9ca3af',
                        callback: value => Number.isInteger(value) ? value : ''
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.04)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#9ca3af'
                    },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
