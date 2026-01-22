@extends('layouts.jobseeker')

@section('page-title', 'Dashboard')

@section('jobseeker-content')
    <!-- SVG Gradient Definition for Ring -->
    <svg width="0" height="0" style="position: absolute;">
        <defs>
            <linearGradient id="ring-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#6366f1" />
                <stop offset="100%" style="stop-color:#8b5cf6" />
            </linearGradient>
        </defs>
    </svg>

    <!-- Modern Dashboard Content -->
    <div class="dashboard-modern">
        <!-- Welcome Banner with Glassmorphism -->
        <div class="welcome-banner-modern m-animate-fade-in-up">
            <div class="welcome-banner-content">
                <div class="welcome-banner-text">
                    <h1>
                        Welcome back, {{ Auth::check() ? explode(' ', Auth::user()->name)[0] : 'Guest' }}!
                    </h1>
                    <p>
                        <i class="far fa-calendar-alt"></i>
                        <span>{{ date('l, F j, Y') }}</span>
                        <span style="opacity: 0.5; margin: 0 8px;">•</span>
                        <span>Here's your job search overview</span>
                    </p>
                </div>
                <div class="welcome-banner-ring">
                    @php
                        $percentage = $completionPercentage ?? 0;
                        $radius = 26;
                        $circumference = 2 * pi() * $radius;
                        $offset = $circumference - ($percentage / 100) * $circumference;
                    @endphp
                    <div class="completion-ring-modern">
                        <svg viewBox="0 0 64 64">
                            <circle class="ring-bg" cx="32" cy="32" r="{{ $radius }}" fill="none"
                                stroke="var(--m-bg-tertiary)" stroke-width="5" />
                            <circle class="ring-progress" cx="32" cy="32" r="{{ $radius }}" fill="none"
                                stroke="url(#ring-gradient)" stroke-width="5" stroke-linecap="round"
                                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                                style="transition: stroke-dashoffset 1s ease-out;" />
                        </svg>
                        <span class="completion-ring-value">{{ $percentage }}%</span>
                    </div>
                    <div class="completion-ring-info">
                        <span class="completion-ring-label">Profile</span>
                        <span class="completion-ring-status">
                            @if($percentage >= 100)
                                <i class="fas fa-check-circle" style="margin-right: 4px; color: var(--m-success);"></i> Complete
                            @else
                                In Progress
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KYC Status - Only show if user is NOT verified -->
        @if(!Auth::user()->isKycVerified())
            <x-kyc-status-card :user="Auth::user()" />
        @endif

        <!-- Stats Bento Grid -->
        <div class="bento-grid m-animate-fade-in-up m-delay-1" style="margin-bottom: var(--m-space-8);">
            <!-- Applications Card -->
            <div class="bento-card bento-sm stat-card-modern stat-primary">
                <div class="stat-icon-modern">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value-modern">{{ $stats['applications'] ?? 0 }}</div>
                    <div class="stat-label-modern">Total Applications</div>
                </div>
                <a href="{{ route('account.myJobApplications') }}" class="stat-link-modern">
                    View all <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Saved Jobs Card -->
            <div class="bento-card bento-sm stat-card-modern stat-purple">
                <div class="stat-icon-modern">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value-modern">{{ $stats['saved_jobs'] ?? 0 }}</div>
                    <div class="stat-label-modern">Saved Jobs</div>
                </div>
                <a href="{{ route('account.bookmarked-jobs.index') }}" class="stat-link-modern">
                    View all <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Profile Views Card -->
            <div class="bento-card bento-sm stat-card-modern stat-info">
                <div class="stat-icon-modern">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value-modern">{{ $stats['profile_views'] ?? 0 }}</div>
                    <div class="stat-label-modern">Profile Views</div>
                </div>
                <a href="{{ route('account.analytics') }}" class="stat-link-modern">
                    Analytics <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Profile Completion Card -->
            <div class="bento-card bento-sm stat-card-modern stat-success">
                <div class="stat-icon-modern">
                    @if(($completionPercentage ?? 0) >= 100)
                        <i class="fas fa-check-circle"></i>
                    @else
                        <i class="fas fa-user-edit"></i>
                    @endif
                </div>
                <div class="stat-content">
                    <div class="stat-value-modern">{{ $completionPercentage ?? 0 }}%</div>
                    <div class="stat-label-modern">Profile Complete</div>
                    @if(($completionPercentage ?? 0) < 100)
                        <div class="stat-change-modern"
                            style="background: var(--m-warning-light); color: var(--m-warning-dark);">
                            <i class="fas fa-exclamation-circle"></i> Needs attention
                        </div>
                    @else
                        <div class="stat-change-modern positive">
                            <i class="fas fa-check"></i> All set
                        </div>
                    @endif
                </div>
                <a href="{{ route('account.myProfile') }}" class="stat-link-modern">
                    @if(($completionPercentage ?? 0) >= 100)
                        View profile
                    @else
                        Complete profile
                    @endif
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Recent Applications Section -->
        <div class="card-modern m-animate-fade-in-up m-delay-2" style="margin-bottom: var(--m-space-8);">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-clock"></i>
                    Recent Applications
                </h3>
                @if(isset($recentApplications) && $recentApplications->count() > 0)
                    <a href="{{ route('account.myJobApplications') }}" class="btn-modern btn-modern-ghost">
                        View All <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                    </a>
                @endif
            </div>
            <div class="card-modern-body" style="padding: 0;">
                @if(isset($recentApplications) && $recentApplications->count() > 0)
                    @foreach($recentApplications as $application)
                        @if($application->job)
                            <div class="application-item-modern">
                                <div class="application-logo-modern">
                                    @if($application->job->company && $application->job->company->logo)
                                        <img src="{{ asset('storage/' . $application->job->company->logo) }}" alt="Logo">
                                    @else
                                        <i class="fas fa-building"></i>
                                    @endif
                                </div>
                                <div class="application-info-modern">
                                    <a href="{{ route('jobDetail', $application->job->id) }}" class="application-title-modern">
                                        {{ $application->job->title ?? 'Job Position' }}
                                    </a>
                                    <p class="application-company-modern">
                                        {{ $application->job->company->name ?? $application->job->employer->name ?? 'Company' }}
                                    </p>
                                    <span class="application-date-modern">
                                        <i class="far fa-calendar"></i>
                                        Applied {{ $application->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <div>
                                    @if($application->status == 'pending')
                                        <span class="status-badge-modern pending">Pending</span>
                                    @elseif($application->status == 'approved' || $application->status == 'accepted')
                                        <span class="status-badge-modern approved">Approved</span>
                                    @elseif($application->status == 'rejected')
                                        <span class="status-badge-modern rejected">Rejected</span>
                                    @else
                                        <span class="status-badge-modern pending">{{ ucfirst($application->status) }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="empty-state-modern">
                        <div class="empty-state-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="empty-state-title">No Applications Yet</h3>
                        <p class="empty-state-desc">Start your job search journey by applying to positions that match your
                            skills.</p>
                        <a href="{{ route('jobs') }}" class="btn-modern btn-modern-primary">
                            <i class="fas fa-search"></i> Browse Jobs
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="section-header-modern m-animate-fade-in-up m-delay-3">
            <h2 class="section-title-modern">Quick Actions</h2>
        </div>
        <div class="quick-action-grid m-animate-fade-in-up m-delay-4">
            <a href="{{ route('account.resume-builder.index') }}" class="quick-action-modern action-purple">
                <div class="quick-action-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h4 class="quick-action-title">Create Resume</h4>
                <p class="quick-action-desc">Build a professional resume with our easy-to-use builder</p>
            </a>

            <a href="{{ route('account.bookmarked-jobs.index') }}" class="quick-action-modern action-pink">
                <div class="quick-action-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <h4 class="quick-action-title">Bookmarks</h4>
                <p class="quick-action-desc">View and manage your bookmarked job opportunities</p>
            </a>

            <a href="{{ route('account.analytics') }}" class="quick-action-modern action-cyan">
                <div class="quick-action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4 class="quick-action-title">Analytics</h4>
                <p class="quick-action-desc">Track your job search progress and insights</p>
            </a>

            <a href="{{ route('account.myProfile') }}" class="quick-action-modern action-orange">
                <div class="quick-action-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h4 class="quick-action-title">Edit Profile</h4>
                <p class="quick-action-desc">Update your information and preferences</p>
            </a>
        </div>
    </div>

    <style>
        /* Dashboard-specific overrides */
        .dashboard-modern {
            animation: m-fade-in 0.5s ease-out;
        }

        /* Override bento grid for dashboard stats */
        .dashboard-modern .bento-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .dashboard-modern .bento-card.bento-sm {
            grid-column: span 1;
        }

        @media (max-width: 1200px) {
            .dashboard-modern .bento-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-modern .bento-card.bento-sm {
                grid-column: span 1;
            }
        }

        @media (max-width: 640px) {
            .dashboard-modern .bento-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-modern .bento-card.bento-sm {
                grid-column: span 1;
            }
        }
    </style>
@endsection