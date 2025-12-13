@extends('layouts.employer')

@section('page_title', 'Dashboard')

@section('content')
<div class="modern-dashboard">
    <!-- Top Welcome Bar with Quick Actions -->
    <div class="dashboard-header animate-fade-in">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="welcome-section">
                    <h1 class="welcome-title">
                        <span class="wave-emoji">👋</span>
                        Welcome back, <span class="highlight-name">{{ auth()->user()->name }}</span>!
                    </h1>
                    <p class="welcome-subtitle">
                        <i class="bi bi-calendar-check me-2"></i>
                        {{ now()->format('l, F j, Y') }}
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="quick-actions">
                    @if(auth()->user()->canPostJobs())
                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-gradient-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Post New Job
                        </a>
                        <a href="{{ route('employer.applications.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-inbox me-2"></i>
                            View Applications
                        </a>
                    @else
                        <button type="button" class="btn btn-gradient-warning" onclick="startInlineVerification()">
                            <i class="bi bi-shield-lock me-2"></i>
                            Complete Verification
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Status Alert (if needed) -->
    @if(!auth()->user()->canPostJobs())
        <div class="verification-alert animate-slide-down">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div class="alert-body">
                    <h4 class="alert-title">Account Verification Required</h4>
                    <p class="alert-message">
                        Complete your verification to unlock all employer features and start posting jobs.
                    </p>
                    <button type="button" class="btn btn-warning" onclick="startInlineVerification()">
                        <i class="bi bi-shield-check me-2"></i>
                        Verify Now
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Overview Cards -->
    <div class="stats-grid stagger-animation">
        <!-- Total Jobs Card -->
        <div class="stat-card-modern stat-primary">
            <div class="stat-icon-wrapper">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Jobs</p>
                <h2 class="stat-value" data-count="{{ $postedJobs }}">0</h2>
                <div class="stat-trend {{ ($postedJobsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-{{ ($postedJobsGrowth ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    <span>{{ abs($postedJobsGrowth ?? 0) }}%</span>
                    <small>from last month</small>
                </div>
            </div>
            <a href="{{ route('employer.jobs.index') }}" class="stat-link">View all jobs <i class="bi bi-arrow-right"></i></a>
        </div>

        <!-- Active Jobs Card -->
        <div class="stat-card-modern stat-success">
            <div class="stat-icon-wrapper">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Active Jobs</p>
                <h2 class="stat-value" data-count="{{ $activeJobs }}">0</h2>
                <div class="stat-trend {{ ($activeJobsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-{{ ($activeJobsGrowth ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    <span>{{ abs($activeJobsGrowth ?? 0) }}%</span>
                    <small>from last month</small>
                </div>
            </div>
            <a href="{{ route('employer.jobs.index', ['status' => 'active']) }}" class="stat-link">Manage active <i class="bi bi-arrow-right"></i></a>
        </div>

        <!-- Total Applications Card -->
        <div class="stat-card-modern stat-info">
            <div class="stat-icon-wrapper">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Applications</p>
                <h2 class="stat-value" data-count="{{ $totalApplications }}">0</h2>
                <div class="stat-trend {{ ($applicationsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-{{ ($applicationsGrowth ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    <span>{{ abs($applicationsGrowth ?? 0) }}%</span>
                    <small>from last month</small>
                </div>
            </div>
            <a href="{{ route('employer.applications.index') }}" class="stat-link">View applications <i class="bi bi-arrow-right"></i></a>
        </div>

        <!-- Pending Applications Card -->
        <div class="stat-card-modern stat-warning">
            <div class="stat-icon-wrapper">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Pending Review</p>
                <h2 class="stat-value" data-count="{{ $pendingApplications ?? 0 }}">0</h2>
                <div class="stat-trend urgent">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>Needs attention</span>
                </div>
            </div>
            <a href="{{ route('employer.applications.index', ['status' => 'pending']) }}" class="stat-link">Review now <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Left Column: Charts & Analytics -->
        <div class="left-column">
            <!-- Applications Trends Chart -->
            <div class="modern-card chart-card animate-scale-in">
                <div class="card-header-modern">
                    <div class="header-left">
                        <h3 class="card-title">
                            <i class="bi bi-graph-up me-2"></i>
                            Application Trends
                        </h3>
                        <p class="card-subtitle">Track your recruitment performance</p>
                    </div>
                    <div class="header-right">
                        <select class="form-select-modern" id="chartPeriod">
                            <option value="7">Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="90">Last 3 months</option>
                        </select>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="chart-container">
                        <canvas id="applicationsChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Jobs Posted -->
            <div class="modern-card recent-jobs-card">
                <div class="card-header-modern">
                    <div class="header-left">
                        <h3 class="card-title">
                            <i class="bi bi-briefcase me-2"></i>
                            Recent Job Postings
                        </h3>
                    </div>
                    <div class="header-right">
                        <a href="{{ route('employer.jobs.index') }}" class="link-primary">View all <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-body-modern">
                    @forelse($recentJobs as $job)
                        <div class="job-item">
                            <div class="job-icon">
                                <i class="bi bi-briefcase-fill"></i>
                            </div>
                            <div class="job-details">
                                <h4 class="job-title">{{ $job->title }}</h4>
                                <div class="job-meta">
                                    <span class="badge badge-modern badge-{{ $job->status }}">{{ ucfirst($job->status) }}</span>
                                    <span class="meta-item">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $job->location }}
                                    </span>
                                    <span class="meta-item">
                                        <i class="bi bi-clock"></i>
                                        {{ $job->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="job-stats">
                                    <span class="stat-item">
                                        <i class="bi bi-eye"></i>
                                        {{ $job->views ?? 0 }} views
                                    </span>
                                    <span class="stat-item">
                                        <i class="bi bi-people"></i>
                                        {{ $job->applications_count ?? 0 }} applications
                                    </span>
                                </div>
                            </div>
                            <div class="job-actions">
                                <a href="{{ route('employer.jobs.show', $job->id) }}" class="btn btn-sm btn-icon" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('employer.jobs.edit', $job->id) }}" class="btn btn-sm btn-icon" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-briefcase empty-icon"></i>
                            <h4>No jobs posted yet</h4>
                            <p>Start by posting your first job listing</p>
                            <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Post Your First Job
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Activity & Quick Info -->
        <div class="right-column">
            <!-- Profile Completion Widget -->
            @php
                $profile = \App\Models\Employer::where('user_id', auth()->id())->first();
                $completion = $profile ? $profile->getProfileCompletionPercentage() : 0;
            @endphp

            @if($completion < 100)
                <div class="modern-card completion-card">
                    <div class="card-body-modern">
                        <div class="completion-header">
                            <h4 class="card-title">Complete Your Profile</h4>
                            <span class="completion-percent">{{ $completion }}%</span>
                        </div>
                        <div class="progress-bar-modern">
                            <div class="progress-fill" style="width: {{ $completion }}%"></div>
                        </div>
                        <p class="completion-text">
                            Complete your profile to increase visibility and attract quality candidates
                        </p>
                        <a href="{{ route('employer.profile.edit') }}" class="btn btn-block btn-outline-primary">
                            <i class="bi bi-pencil me-2"></i>
                            Complete Profile
                        </a>
                    </div>
                </div>
            @endif

            <!-- Recent Activity Feed -->
            <div class="modern-card activity-card">
                <div class="card-header-modern">
                    <h3 class="card-title">
                        <i class="bi bi-bell me-2"></i>
                        Recent Activity
                    </h3>
                </div>
                <div class="card-body-modern">
                    <div class="activity-feed">
                        @forelse($recentActivities->take(5) as $activity)
                            <div class="activity-item">
                                <div class="activity-icon activity-{{ $activity['type'] }}">
                                    <i class="bi bi-{{ $activity['icon'] }}"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-title">{{ $activity['title'] }}</p>
                                    <p class="activity-description">{{ $activity['description'] }}</p>
                                    <span class="activity-time">
                                        <i class="bi bi-clock"></i>
                                        {{ $activity['created_at']->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state-small">
                                <i class="bi bi-inbox"></i>
                                <p>No recent activity</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Stats Widget -->
            <div class="modern-card quick-stats-card">
                <div class="card-header-modern">
                    <h3 class="card-title">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Quick Stats
                    </h3>
                </div>
                <div class="card-body-modern">
                    <div class="quick-stat-item">
                        <div class="stat-icon-small stat-primary">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="stat-info">
                            <p class="stat-label-small">Profile Views</p>
                            <h4 class="stat-value-small">{{ $profileViews ?? 0 }}</h4>
                        </div>
                    </div>
                    <div class="quick-stat-item">
                        <div class="stat-icon-small stat-success">
                            <i class="bi bi-star"></i>
                        </div>
                        <div class="stat-info">
                            <p class="stat-label-small">Average Rating</p>
                            <h4 class="stat-value-small">{{ number_format($profile->average_rating ?? 0, 1) }} ⭐</h4>
                        </div>
                    </div>
                    <div class="quick-stat-item">
                        <div class="stat-icon-small stat-info">
                            <i class="bi bi-bookmark-check"></i>
                        </div>
                        <div class="stat-info">
                            <p class="stat-label-small">Shortlisted</p>
                            <h4 class="stat-value-small">{{ $shortlistedCandidates ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help & Resources -->
            <div class="modern-card help-card">
                <div class="card-body-modern">
                    <div class="help-icon">
                        <i class="bi bi-question-circle"></i>
                    </div>
                    <h4 class="help-title">Need Help?</h4>
                    <p class="help-text">
                        Learn how to post effective jobs and manage applicants
                    </p>
                    <div class="help-links">
                        <a href="#" class="help-link">
                            <i class="bi bi-book"></i>
                            Documentation
                        </a>
                        <a href="#" class="help-link">
                            <i class="bi bi-chat-dots"></i>
                            Get Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Dashboard Styles */
.modern-dashboard {
    padding: 2rem;
    background: #f8f9fa;
    min-height: 100vh;
}

/* Dashboard Header */
.dashboard-header {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.welcome-section {
    flex: 1;
}

.welcome-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.wave-emoji {
    animation: wave 2s ease-in-out infinite;
    display: inline-block;
    font-size: 2.5rem;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    75% { transform: rotate(-20deg); }
}

.highlight-name {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.welcome-subtitle {
    color: #6b7280;
    margin: 0.5rem 0 0;
    font-size: 1rem;
}

.quick-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 0.875rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-gradient-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-gradient-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border: none;
    color: white;
    padding: 0.875rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

/* Verification Alert */
.verification-alert {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 2px solid #fbbf24;
}

.alert-content {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.alert-icon {
    width: 60px;
    height: 60px;
    background: rgba(245, 158, 11, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #f59e0b;
    flex-shrink: 0;
}

.alert-body {
    flex: 1;
}

.alert-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #92400e;
    margin: 0 0 0.5rem;
}

.alert-message {
    color: #78350f;
    margin-bottom: 1rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card-modern {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--stat-color-1), var(--stat-color-2));
}

.stat-card-modern.stat-primary {
    --stat-color-1: #667eea;
    --stat-color-2: #764ba2;
}

.stat-card-modern.stat-success {
    --stat-color-1: #10b981;
    --stat-color-2: #059669;
}

.stat-card-modern.stat-info {
    --stat-color-1: #3b82f6;
    --stat-color-2: #2563eb;
}

.stat-card-modern.stat-warning {
    --stat-color-1: #f59e0b;
    --stat-color-2: #d97706;
}

.stat-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
}

.stat-icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--stat-color-1), var(--stat-color-2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    margin-bottom: 1.5rem;
}

.stat-content {
    margin-bottom: 1.5rem;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    line-height: 1;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
    font-size: 0.875rem;
}

.stat-trend.positive {
    color: #10b981;
}

.stat-trend.negative {
    color: #ef4444;
}

.stat-trend.urgent {
    color: #f59e0b;
}

.stat-trend small {
    color: #9ca3af;
}

.stat-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--stat-color-1);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: gap 0.3s ease;
}

.stat-link:hover {
    gap: 1rem;
    color: var(--stat-color-2);
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
}

.left-column,
.right-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.modern-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.card-header-modern {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
}

.card-subtitle {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0.25rem 0 0;
}

.card-body-modern {
    padding: 2rem;
}

/* Chart Card */
.chart-container {
    position: relative;
    height: 300px;
}

/* Job Items */
.job-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 12px;
    background: #f9fafb;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.job-item:last-child {
    margin-bottom: 0;
}

.job-item:hover {
    background: #f3f4f6;
    transform: translateX(4px);
}

.job-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.job-details {
    flex: 1;
}

.job-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem;
}

.job-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 0.75rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.job-stats {
    display: flex;
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #9ca3af;
    font-size: 0.875rem;
}

.job-actions {
    display: flex;
    gap: 0.5rem;
    align-items: flex-start;
}

.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    background: white;
    color: #6b7280;
    transition: all 0.3s ease;
}

.btn-icon:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* Activity Feed */
.activity-feed {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    gap: 1rem;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: white;
}

.activity-icon.activity-application {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.activity-icon.activity-job {
    background: linear-gradient(135deg, #10b981, #059669);
}

.activity-icon.activity-message {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem;
}

.activity-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.5rem;
}

.activity-time {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #9ca3af;
}

/* Profile Completion Card */
.completion-card .card-body-modern {
    padding: 2rem;
}

.completion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.completion-percent {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.progress-bar-modern {
    height: 8px;
    background: #f3f4f6;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 10px;
    transition: width 1s ease;
}

.completion-text {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

/* Quick Stats Widget */
.quick-stat-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-radius: 12px;
    background: #f9fafb;
    margin-bottom: 1rem;
}

.quick-stat-item:last-child {
    margin-bottom: 0;
}

.stat-icon-small {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.stat-icon-small.stat-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.stat-icon-small.stat-success {
    background: linear-gradient(135deg, #10b981, #059669);
}

.stat-icon-small.stat-info {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.stat-label-small {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value-small {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0.25rem 0 0;
}

/* Help Card */
.help-card .card-body-modern {
    text-align: center;
    padding: 2rem;
}

.help-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.75rem;
    color: white;
}

.help-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.5rem;
}

.help-text {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

.help-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.help-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    color: #6b7280;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.help-link:hover {
    background: #f3f4f6;
    border-color: #667eea;
    color: #667eea;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-state h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.empty-state-small {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}

/* Badge Modern */
.badge-modern {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-active {
    background: #d1fae5;
    color: #065f46;
}

.badge-pending {
    background: #fef3c7;
    color: #92400e;
}

.badge-draft {
    background: #e5e7eb;
    color: #4b5563;
}

/* Form Select Modern */
.form-select-modern {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-select-modern:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Responsive */
@media (max-width: 1200px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .right-column {
        order: 2;
    }
}

@media (max-width: 768px) {
    .modern-dashboard {
        padding: 1rem;
    }

    .dashboard-header {
        padding: 1.5rem;
    }

    .quick-actions {
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .welcome-title {
        font-size: 1.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Count Up Animation for Stats
    const statValues = document.querySelectorAll('.stat-value[data-count]');
    statValues.forEach(stat => {
        const target = parseInt(stat.dataset.count);
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            stat.textContent = Math.floor(current).toLocaleString();
        }, 20);
    });

    // Applications Chart
    const ctx = document.getElementById('applicationsChart');
    if (ctx) {
        const labels = @json($applicationTrendsLabels ?? []);
        const data = @json($applicationTrendsData ?? []);

        if (labels.length > 0) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Applications',
                        data: data,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.05)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#764ba2',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            padding: 16,
                            borderRadius: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 12
                                },
                                color: '#6b7280'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6b7280'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    }
});
</script>
@endpush
@endsection
