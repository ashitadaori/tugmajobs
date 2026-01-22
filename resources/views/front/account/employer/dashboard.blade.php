@extends('layouts.employer')

@section('page_title', 'Dashboard')

@section('content')
<!-- Verification Alert Banner -->
@if(!auth()->user()->canPostJobs())
@if(auth()->user()->kyc_status === 'pending_review')
{{-- Manual Review in Progress Banner --}}
<div class="bento-card bento-full m-animate-fade-in-up" style="padding: var(--m-space-5); margin-bottom: var(--m-space-6); background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-color: #0ea5e9;">
    <div style="display: flex; align-items: center; gap: var(--m-space-4); flex-wrap: wrap;">
        <div style="width: 48px; height: 48px; border-radius: var(--m-radius-lg); background: #0ea5e9; display: flex; align-items: center; justify-content: center; color: white;">
            <i class="fas fa-hourglass-half" style="font-size: 1.25rem;"></i>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <div style="font-weight: 600; color: var(--m-text-primary); margin-bottom: 2px;">Manual Review in Progress</div>
            <div style="font-size: var(--m-text-sm); color: var(--m-text-secondary);">Your documents are being reviewed by our team. This typically takes 1-3 business days.</div>
        </div>
        <div style="display: flex; gap: var(--m-space-3);">
            <a href="{{ route('kyc.manual.status') }}" class="btn-modern btn-modern-primary" style="background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);">
                <i class="fas fa-eye"></i>
                View Status
            </a>
        </div>
    </div>
</div>
@else
{{-- Standard Verification Required Banner --}}
<div class="bento-card bento-full m-animate-fade-in-up" style="padding: var(--m-space-5); margin-bottom: var(--m-space-6); background: linear-gradient(135deg, var(--m-warning-light) 0%, #fef9c3 100%); border-color: var(--m-warning);">
    <div style="display: flex; align-items: center; gap: var(--m-space-4); flex-wrap: wrap;">
        <div style="width: 48px; height: 48px; border-radius: var(--m-radius-lg); background: var(--m-warning); display: flex; align-items: center; justify-content: center; color: white;">
            <i class="fas fa-shield-alt" style="font-size: 1.25rem;"></i>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <div style="font-weight: 600; color: var(--m-text-primary); margin-bottom: 2px;">Account Verification Required</div>
            <div style="font-size: var(--m-text-sm); color: var(--m-text-secondary);">Complete your verification to unlock all employer features and start posting jobs.</div>
        </div>
        <div style="display: flex; gap: var(--m-space-3);">
            <button type="button" class="btn-modern btn-modern-primary" onclick="startInlineVerification()">
                <i class="fas fa-shield-check"></i>
                Verify Now
            </button>
            <a href="{{ route('kyc.manual.form') }}" class="btn-modern btn-modern-secondary">
                <i class="fas fa-file-upload"></i>
                Manual Verification
            </a>
            @if(auth()->user()->kyc_status === 'in_progress' || auth()->user()->kyc_status === 'failed')
            <button type="button" class="btn-modern btn-modern-secondary" data-bs-toggle="modal" data-bs-target="#resetKycModal">
                <i class="fas fa-redo"></i>
                Reset KYC
            </button>
            @endif
        </div>
    </div>
</div>
@endif
@endif

<!-- Reset KYC Modal -->
<div class="modal fade" id="resetKycModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: var(--m-radius-xl); border: none; box-shadow: var(--m-shadow-xl);">
            <div class="modal-header" style="border-bottom: 1px solid var(--m-border);">
                <h5 class="modal-title" style="font-weight: 600;">Reset KYC Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employer.settings.kyc.reset') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div style="padding: var(--m-space-4); background: var(--m-warning-light); border-radius: var(--m-radius-lg); margin-bottom: var(--m-space-4);">
                        <div style="display: flex; gap: var(--m-space-3); align-items: flex-start;">
                            <i class="fas fa-exclamation-triangle" style="color: var(--m-warning-dark); margin-top: 2px;"></i>
                            <div>
                                <strong style="color: var(--m-warning-dark);">Warning:</strong>
                                <p style="margin: var(--m-space-2) 0 0 0; color: var(--m-text-secondary); font-size: var(--m-text-sm);">This action will delete all KYC records and reset your verification status.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmResetDashboard" required>
                        <label class="form-check-label" for="confirmResetDashboard">
                            I understand that this action cannot be undone
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--m-border);">
                    <button type="button" class="btn-modern btn-modern-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern" style="background: var(--m-danger); color: white;">
                        <i class="fas fa-redo"></i> Reset KYC
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Welcome Banner with Glassmorphism -->
<div class="welcome-banner-modern m-animate-fade-in-up" style="margin-bottom: var(--m-space-6);">
    <div class="welcome-banner-content">
        <div class="welcome-banner-text">
            <h1>Welcome back!</h1>
            <p>
                <i class="far fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
                <span style="opacity: 0.5; margin: 0 8px;">•</span>
                <span>Here's what's happening with your recruitment</span>
            </p>
        </div>
        @if(auth()->user()->canPostJobs())
        <div class="welcome-banner-actions">
            <a href="{{ route('employer.jobs.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus-circle"></i>
                Post New Job
            </a>
            <a href="{{ route('employer.applications.index') }}" class="btn-modern btn-modern-secondary">
                <i class="fas fa-eye"></i>
                View Applications
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Stats Bento Grid -->
<div class="bento-grid m-animate-fade-in-up m-delay-1" style="margin-bottom: var(--m-space-8);">
    <!-- Total Jobs -->
    <div class="bento-card bento-sm stat-card-modern stat-primary">
        <div class="stat-icon-modern">
            <i class="fas fa-briefcase"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value-modern" data-count="{{ $postedJobs }}">{{ $postedJobs }}</div>
            <div class="stat-label-modern">Total Jobs</div>
            @if(($postedJobsGrowth ?? 0) != 0)
            <div class="stat-change-modern {{ ($postedJobsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ ($postedJobsGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($postedJobsGrowth ?? 0) }}% from last month
            </div>
            @endif
        </div>
        <a href="{{ route('employer.jobs.index') }}" class="stat-link-modern">
            View all jobs <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <!-- Active Jobs -->
    <div class="bento-card bento-sm stat-card-modern stat-success">
        <div class="stat-icon-modern">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value-modern" data-count="{{ $activeJobs }}">{{ $activeJobs }}</div>
            <div class="stat-label-modern">Active Jobs</div>
            @if(($activeJobsGrowth ?? 0) != 0)
            <div class="stat-change-modern {{ ($activeJobsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ ($activeJobsGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($activeJobsGrowth ?? 0) }}% from last month
            </div>
            @endif
        </div>
        <a href="{{ route('employer.jobs.index', ['status' => 'active']) }}" class="stat-link-modern">
            Manage active <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <!-- Total Applications -->
    <div class="bento-card bento-sm stat-card-modern stat-info">
        <div class="stat-icon-modern">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value-modern" data-count="{{ $totalApplications }}">{{ $totalApplications }}</div>
            <div class="stat-label-modern">Total Applications</div>
            @if(($applicationsGrowth ?? 0) != 0)
            <div class="stat-change-modern {{ ($applicationsGrowth ?? 0) >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ ($applicationsGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($applicationsGrowth ?? 0) }}% from last month
            </div>
            @endif
        </div>
        <a href="{{ route('employer.applications.index') }}" class="stat-link-modern">
            View applications <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <!-- Pending Review -->
    <div class="bento-card bento-sm stat-card-modern stat-warning">
        <div class="stat-icon-modern">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value-modern" data-count="{{ $pendingApplications ?? 0 }}">{{ $pendingApplications ?? 0 }}</div>
            <div class="stat-label-modern">Pending Review</div>
            @if(($pendingApplications ?? 0) > 0)
            <div class="stat-change-modern" style="background: var(--m-warning-light); color: var(--m-warning-dark);">
                <i class="fas fa-exclamation-circle"></i> Needs attention
            </div>
            @endif
        </div>
        <a href="{{ route('employer.applications.index', ['status' => 'pending']) }}" class="stat-link-modern">
            Review now <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="bento-grid m-animate-fade-in-up m-delay-2" style="grid-template-columns: 2fr 1fr; gap: var(--m-space-6);">
    <!-- Left Column - Charts & Recent Jobs -->
    <div style="display: flex; flex-direction: column; gap: var(--m-space-6);">
        <!-- Application Trends Chart -->
        <div class="card-modern">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-chart-line"></i>
                    Application Trends
                </h3>
                <select class="btn-modern btn-modern-secondary" id="chartPeriod" style="padding: var(--m-space-2) var(--m-space-4); font-size: var(--m-text-sm);">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 3 months</option>
                </select>
            </div>
            <div class="card-modern-body">
                <div style="height: 280px;">
                    <canvas id="applicationsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Job Postings -->
        <div class="card-modern">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-briefcase"></i>
                    Recent Job Postings
                </h3>
                <a href="{{ route('employer.jobs.index') }}" class="btn-modern btn-modern-ghost">
                    View All <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>
            <div class="card-modern-body" style="padding: 0;">
                @forelse($recentJobs as $job)
                <div class="application-item-modern">
                    <div class="application-logo-modern" style="background: linear-gradient(135deg, var(--m-accent-subtle) 0%, var(--m-purple-subtle) 100%);">
                        <i class="fas fa-briefcase" style="color: var(--m-accent);"></i>
                    </div>
                    <div class="application-info-modern">
                        <a href="{{ route('employer.jobs.show', $job->id) }}" class="application-title-modern">
                            {{ Str::limit($job->title, 40) }}
                        </a>
                        <p class="application-company-modern" style="display: flex; align-items: center; gap: var(--m-space-3); flex-wrap: wrap; margin-top: var(--m-space-1);">
                            <span class="status-badge-modern {{ $job->status == 'active' ? 'approved' : ($job->status == 'pending' ? 'pending' : 'rejected') }}">
                                {{ ucfirst($job->status) }}
                            </span>
                            <span style="display: flex; align-items: center; gap: 4px;"><i class="fas fa-map-marker-alt"></i> {{ $job->location ?? 'Remote' }}</span>
                            <span style="display: flex; align-items: center; gap: 4px;"><i class="far fa-clock"></i> {{ $job->created_at->diffForHumans() }}</span>
                        </p>
                    </div>
                    <div style="display: flex; gap: var(--m-space-4); font-size: var(--m-text-sm); color: var(--m-text-secondary);">
                        <span style="display: flex; align-items: center; gap: 4px;"><i class="fas fa-eye"></i> {{ $job->views ?? 0 }}</span>
                        <span style="display: flex; align-items: center; gap: 4px;"><i class="fas fa-users"></i> {{ $job->applications_count ?? 0 }}</span>
                    </div>
                    <div style="display: flex; gap: var(--m-space-2);">
                        <a href="{{ route('employer.jobs.show', $job->id) }}" class="btn-modern btn-modern-ghost" style="padding: var(--m-space-2);">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('employer.jobs.edit', $job->id) }}" class="btn-modern btn-modern-ghost" style="padding: var(--m-space-2);">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="empty-state-modern">
                    <div class="empty-state-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="empty-state-title">No jobs posted yet</h3>
                    <p class="empty-state-desc">Start attracting top talent by posting your first job listing today.</p>
                    @if(auth()->user()->canPostJobs())
                    <a href="{{ route('employer.jobs.create') }}" class="btn-modern btn-modern-primary">
                        <i class="fas fa-plus-circle"></i>
                        Post Your First Job
                    </a>
                    @else
                    <button type="button" class="btn-modern btn-modern-primary" onclick="startInlineVerification()">
                        <i class="fas fa-shield-alt"></i>
                        Complete Verification First
                    </button>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div style="display: flex; flex-direction: column; gap: var(--m-space-6);">
        <!-- Quick Stats Widget -->
        <div class="card-modern">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-chart-pie"></i>
                    Quick Stats
                </h3>
            </div>
            <div class="card-modern-body" style="display: flex; flex-direction: column; gap: var(--m-space-4);">
                <div style="display: flex; align-items: center; gap: var(--m-space-3); padding: var(--m-space-3); background: var(--m-bg-tertiary); border-radius: var(--m-radius-lg);">
                    <div style="width: 40px; height: 40px; border-radius: var(--m-radius-md); background: var(--m-info); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--m-text-xs); color: var(--m-text-tertiary);">Profile Views</div>
                        <div style="font-size: var(--m-text-lg); font-weight: 600; color: var(--m-text-primary);">{{ $profileViews ?? 0 }}</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: var(--m-space-3); padding: var(--m-space-3); background: var(--m-bg-tertiary); border-radius: var(--m-radius-lg);">
                    <div style="width: 40px; height: 40px; border-radius: var(--m-radius-md); background: #fbbf24; display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--m-text-xs); color: var(--m-text-tertiary);">Average Rating</div>
                        <div style="font-size: var(--m-text-lg); font-weight: 600; color: var(--m-text-primary);">
                            @php
                                $profile = \App\Models\Employer::where('user_id', auth()->id())->first();
                                $rating = \App\Models\Review::where('employer_id', auth()->id())->avg('rating') ?? 0;
                            @endphp
                            {{ number_format($rating, 1) }} <span style="color: #fbbf24;">★</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: var(--m-space-3); padding: var(--m-space-3); background: var(--m-bg-tertiary); border-radius: var(--m-radius-lg);">
                    <div style="width: 40px; height: 40px; border-radius: var(--m-radius-md); background: var(--m-purple); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--m-text-xs); color: var(--m-text-tertiary);">Shortlisted</div>
                        <div style="font-size: var(--m-text-lg); font-weight: 600; color: var(--m-text-primary);">{{ $shortlistedCandidates ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Completion -->
        @php
            $profileCompletion = $profile ? $profile->getProfileCompletionPercentage() : 0;
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
        <div class="card-modern">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-check-circle"></i>
                    Profile Completion
                </h3>
            </div>
            <div class="card-modern-body">
                <div style="margin-bottom: var(--m-space-4);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--m-space-2);">
                        <span style="font-weight: 600; color: var(--m-text-primary);">{{ $profileCompletion }}% Complete</span>
                    </div>
                    <div style="height: 8px; background: var(--m-bg-tertiary); border-radius: var(--m-radius-full); overflow: hidden;">
                        <div style="height: 100%; width: {{ $profileCompletion }}%; background: linear-gradient(90deg, var(--m-accent), var(--m-purple)); border-radius: var(--m-radius-full); transition: width 0.5s var(--m-ease-spring);"></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: var(--m-space-3);">
                    @foreach([
                        'company_info' => 'Company Information',
                        'contact_details' => 'Contact Details',
                        'company_logo' => 'Company Logo',
                        'culture_benefits' => 'Culture & Benefits'
                    ] as $key => $label)
                    <div style="display: flex; align-items: center; gap: var(--m-space-2);">
                        <i class="fas fa-{{ $completionItems[$key] ? 'check-circle' : 'circle' }}" style="color: {{ $completionItems[$key] ? 'var(--m-success)' : 'var(--m-text-tertiary)' }};"></i>
                        <span style="font-size: var(--m-text-sm); color: var(--m-text-secondary);">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>

                @if($profileCompletion < 100)
                <a href="{{ route('employer.profile.edit') }}" class="btn-modern btn-modern-secondary" style="width: 100%; margin-top: var(--m-space-4); justify-content: center;">
                    <i class="fas fa-pencil-alt"></i>
                    Complete Profile
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Employer Dashboard Specific Styles */
.bento-grid {
    grid-template-columns: repeat(4, 1fr);
}

.bento-card.bento-sm {
    grid-column: span 1;
}

@media (max-width: 1400px) {
    .bento-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .bento-card.bento-sm {
        grid-column: span 1;
    }
}

@media (max-width: 992px) {
    .bento-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Count Up Animation for Stats
    const statValues = document.querySelectorAll('.stat-value-modern[data-count]');
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
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.08)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#4f46e5',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            padding: 14,
                            cornerRadius: 8,
                            titleFont: { size: 13, weight: '600', family: 'Inter' },
                            bodyFont: { size: 12, family: 'Inter' },
                            displayColors: false,
                            callbacks: {
                                title: (context) => context[0].label,
                                label: (context) => context.parsed.y + ' applications'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 12, family: 'Inter' }, color: '#a1a1aa' },
                            grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                        },
                        x: {
                            ticks: { font: { size: 12, family: 'Inter' }, color: '#a1a1aa' },
                            grid: { display: false }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' }
                }
            });
        }
    }
});
</script>
@endpush
@endsection
