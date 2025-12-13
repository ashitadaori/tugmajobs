@extends('layouts.employer')

@section('page_title', 'Dashboard')

@section('content')
<!-- Welcome Banner -->
@if(!auth()->user()->canPostJobs())
<div class="ep-alert ep-alert-warning ep-mb-6">
    <i class="bi bi-shield-exclamation ep-alert-icon"></i>
    <div class="ep-alert-content">
        <div class="ep-alert-title">Account Verification Required</div>
        <div class="ep-alert-message">Complete your verification to unlock all employer features and start posting jobs.</div>
    </div>
    <button type="button" class="ep-btn ep-btn-primary ep-btn-sm" onclick="startInlineVerification()">
        <i class="bi bi-shield-check"></i>
        Verify Now
    </button>
</div>
@endif

<div class="ep-welcome-banner">
    <div class="ep-welcome-title">Welcome back! 👋</div>
    <p class="ep-welcome-subtitle">{{ now()->format('l, F j, Y') }} - Here's what's happening with your recruitment.</p>
    @if(auth()->user()->canPostJobs())
    <div class="ep-welcome-actions">
        <a href="{{ route('employer.jobs.create') }}" class="ep-welcome-btn ep-welcome-btn-primary">
            <i class="bi bi-plus-circle"></i>
            Post New Job
        </a>
        <a href="{{ route('employer.applications.index') }}" class="ep-welcome-btn ep-welcome-btn-outline">
            <i class="bi bi-eye"></i>
            View Applications
        </a>
    </div>
    @endif
</div>

<!-- Stats Cards -->
<div class="ep-stats-grid">
    <!-- Total Jobs -->
    <div class="ep-stat-card primary">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-briefcase-fill"></i>
            </div>
        </div>
        <div class="ep-stat-label">Total Jobs</div>
        <div class="ep-stat-value" data-count="{{ $postedJobs }}">{{ $postedJobs }}</div>
        <div class="ep-stat-change {{ ($postedJobsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
            <i class="bi bi-arrow-{{ ($postedJobsGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($postedJobsGrowth ?? 0) }}% from last month
        </div>
        <a href="{{ route('employer.jobs.index') }}" class="ep-stat-link">
            View all jobs <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- Active Jobs -->
    <div class="ep-stat-card success">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
        <div class="ep-stat-label">Active Jobs</div>
        <div class="ep-stat-value" data-count="{{ $activeJobs }}">{{ $activeJobs }}</div>
        <div class="ep-stat-change {{ ($activeJobsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
            <i class="bi bi-arrow-{{ ($activeJobsGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($activeJobsGrowth ?? 0) }}% from last month
        </div>
        <a href="{{ route('employer.jobs.index', ['status' => 'active']) }}" class="ep-stat-link">
            Manage active <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- Total Applications -->
    <div class="ep-stat-card info">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
        <div class="ep-stat-label">Total Applications</div>
        <div class="ep-stat-value" data-count="{{ $totalApplications }}">{{ $totalApplications }}</div>
        <div class="ep-stat-change {{ ($applicationsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
            <i class="bi bi-arrow-{{ ($applicationsGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($applicationsGrowth ?? 0) }}% from last month
        </div>
        <a href="{{ route('employer.applications.index') }}" class="ep-stat-link">
            View applications <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- Pending Review -->
    <div class="ep-stat-card warning">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
        <div class="ep-stat-label">Pending Review</div>
        <div class="ep-stat-value" data-count="{{ $pendingApplications ?? 0 }}">{{ $pendingApplications ?? 0 }}</div>
        <div class="ep-stat-change" style="color: var(--ep-warning);">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Needs attention
        </div>
        <a href="{{ route('employer.applications.index', ['status' => 'pending']) }}" class="ep-stat-link">
            Review now <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="ep-dashboard-grid">
    <!-- Left Column -->
    <div class="ep-dashboard-main">
        <!-- Application Trends Chart -->
        <div class="ep-card ep-mb-6">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-graph-up"></i>
                    Application Trends
                </h3>
                <select class="ep-form-select" id="chartPeriod" style="width: auto; padding: 8px 32px 8px 12px;">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 3 months</option>
                </select>
            </div>
            <div class="ep-card-body">
                <div class="ep-chart-container">
                    <canvas id="applicationsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Job Postings -->
        <div class="ep-card">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-briefcase"></i>
                    Recent Job Postings
                </h3>
                <a href="{{ route('employer.jobs.index') }}" class="ep-btn ep-btn-outline ep-btn-sm">
                    View All
                </a>
            </div>
            <div class="ep-card-body" style="padding: 0;">
                @forelse($recentJobs as $job)
                <div class="ep-job-item">
                    <div class="ep-job-icon">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div class="ep-job-info">
                        <div class="ep-job-title">{{ Str::limit($job->title, 40) }}</div>
                        <div class="ep-job-meta">
                            <span class="ep-badge ep-badge-{{ $job->status == 'active' ? 'success' : ($job->status == 'pending' ? 'warning' : 'gray') }}">
                                {{ ucfirst($job->status) }}
                            </span>
                            <span><i class="bi bi-geo-alt"></i> {{ $job->location ?? 'Remote' }}</span>
                            <span><i class="bi bi-clock"></i> {{ $job->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="ep-job-stats">
                        <span><i class="bi bi-eye"></i> {{ $job->views ?? 0 }}</span>
                        <span><i class="bi bi-people"></i> {{ $job->applications_count ?? 0 }}</span>
                    </div>
                    <div class="ep-job-actions">
                        <a href="{{ route('employer.jobs.show', $job->id) }}" class="ep-btn ep-btn-icon ep-btn-outline" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('employer.jobs.edit', $job->id) }}" class="ep-btn ep-btn-icon ep-btn-outline" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="ep-empty-state">
                    <div class="ep-empty-icon">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <h4 class="ep-empty-title">No jobs posted yet</h4>
                    <p class="ep-empty-description">Start attracting top talent by posting your first job listing today.</p>
                    @if(auth()->user()->canPostJobs())
                    <a href="{{ route('employer.jobs.create') }}" class="ep-btn ep-btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Post Your First Job
                    </a>
                    @else
                    <button type="button" class="ep-btn ep-btn-primary" onclick="startInlineVerification()">
                        <i class="bi bi-shield-lock"></i>
                        Complete Verification First
                    </button>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="ep-dashboard-sidebar">
        <!-- Quick Stats Widget -->
        <div class="ep-quick-stats ep-mb-6">
            <h4 class="ep-quick-stats-title">Quick Stats</h4>

            <div class="ep-quick-stat-item">
                <div class="ep-quick-stat-icon blue">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <div class="ep-quick-stat-info">
                    <div class="ep-quick-stat-label">Profile Views</div>
                    <div class="ep-quick-stat-value">{{ $profileViews ?? 0 }}</div>
                </div>
            </div>

            <div class="ep-quick-stat-item">
                <div class="ep-quick-stat-icon green">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div class="ep-quick-stat-info">
                    <div class="ep-quick-stat-label">Average Rating</div>
                    <div class="ep-quick-stat-value">
                        @php
                            // Get employer profile (needed for later sections)
                            $profile = \App\Models\Employer::where('user_id', auth()->id())->first();
                            // Get average rating from ALL reviews (job + company reviews)
                            $rating = \App\Models\Review::where('employer_id', auth()->id())->avg('rating') ?? 0;
                        @endphp
                        {{ number_format($rating, 1) }} <span style="color: #fbbf24; font-size: 0.9em;">★</span>
                    </div>
                </div>
            </div>

            <div class="ep-quick-stat-item">
                <div class="ep-quick-stat-icon purple">
                    <i class="bi bi-bookmark-fill"></i>
                </div>
                <div class="ep-quick-stat-info">
                    <div class="ep-quick-stat-label">Shortlisted</div>
                    <div class="ep-quick-stat-value">{{ $shortlistedCandidates ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Profile Completion -->
        @php
            // Use the same calculation as profile edit page for consistency
            $profileCompletion = $profile ? $profile->getProfileCompletionPercentage() : 0;

            // Define completion items for display (matching profile edit page)
            $hasCompanyLogo = false;
            if (!empty($profile->company_logo)) {
                $logoPath = str_replace('storage/', '', $profile->company_logo);
                $hasCompanyLogo = \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath);
            }

            $completionItems = [
                'company_info' => !empty($profile->company_name) && !empty($profile->company_description) && !empty($profile->industry),
                'contact_details' => !empty($profile->business_email) && !empty($profile->business_address),
                'company_logo' => $hasCompanyLogo,
                'culture_benefits' => !empty($profile->company_culture) && !empty($profile->benefits_offered),
            ];
        @endphp
        <div class="ep-card">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-check-circle"></i>
                    Profile Completion
                </h3>
            </div>
            <div class="ep-card-body">
                <div style="margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 600; color: var(--ep-gray-800);">{{ $profileCompletion }}% Complete</span>
                    </div>
                    <div style="height: 8px; background: var(--ep-gray-200); border-radius: 9999px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $profileCompletion }}%; background: linear-gradient(90deg, var(--ep-primary), var(--ep-primary-light)); border-radius: 9999px; transition: width 0.5s ease;"></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-{{ $completionItems['company_info'] ? 'check-circle-fill' : 'circle' }}" style="color: {{ $completionItems['company_info'] ? 'var(--ep-success)' : 'var(--ep-gray-400)' }};"></i>
                        <span style="font-size: 14px; color: var(--ep-gray-700);">Company Information</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-{{ $completionItems['contact_details'] ? 'check-circle-fill' : 'circle' }}" style="color: {{ $completionItems['contact_details'] ? 'var(--ep-success)' : 'var(--ep-gray-400)' }};"></i>
                        <span style="font-size: 14px; color: var(--ep-gray-700);">Contact Details</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-{{ $completionItems['company_logo'] ? 'check-circle-fill' : 'circle' }}" style="color: {{ $completionItems['company_logo'] ? 'var(--ep-success)' : 'var(--ep-gray-400)' }};"></i>
                        <span style="font-size: 14px; color: var(--ep-gray-700);">Company Logo</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-{{ $completionItems['culture_benefits'] ? 'check-circle-fill' : 'circle' }}" style="color: {{ $completionItems['culture_benefits'] ? 'var(--ep-success)' : 'var(--ep-gray-400)' }};"></i>
                        <span style="font-size: 14px; color: var(--ep-gray-700);">Culture & Benefits</span>
                    </div>
                </div>

                @if($profileCompletion < 100)
                <a href="{{ route('employer.profile.edit') }}" class="ep-btn ep-btn-outline-primary" style="width: 100%; margin-top: 16px;">
                    <i class="bi bi-pencil"></i>
                    Complete Profile
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.ep-dashboard-main {
    display: flex;
    flex-direction: column;
    gap: var(--ep-space-6);
}

.ep-dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--ep-space-6);
}

@media (max-width: 1200px) {
    .ep-dashboard-sidebar {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .ep-dashboard-sidebar {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Count Up Animation for Stats
    const statValues = document.querySelectorAll('.ep-stat-value[data-count]');
    statValues.forEach(stat => {
        const target = parseInt(stat.dataset.count);
        let current = 0;
        const increment = target / 40;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            stat.textContent = Math.floor(current).toLocaleString();
        }, 25);
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
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.08)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#4338ca',
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
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            padding: 14,
                            cornerRadius: 8,
                            titleFont: {
                                size: 13,
                                weight: '600',
                                family: 'Inter'
                            },
                            bodyFont: {
                                size: 12,
                                family: 'Inter'
                            },
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    return context.parsed.y + ' applications';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 12,
                                    family: 'Inter'
                                },
                                color: '#6b7280'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.04)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12,
                                    family: 'Inter'
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
