@extends('layouts.employer')

@section('page_title', 'Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    
    <!-- Hero Welcome Section -->
    <div class="hero-welcome mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1" style="color: #111827; font-weight: 700; font-size: 1.75rem;">
                    Welcome back, {{ auth()->user()->name }}! 👋
                </h2>
                <p class="text-muted mb-0" style="font-size: 1rem;">Here's your recruitment overview for today</p>
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->canPostJobs())
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 500;">
                        <i class="bi bi-plus-circle me-2"></i>Post New Job
                    </a>
                @else
                    <button type="button" class="btn btn-warning px-4 py-2" onclick="startInlineVerification()" style="border-radius: 10px; font-weight: 500;">
                        <i class="bi bi-shield-check me-2"></i>Complete Verification to Post Jobs
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- KYC Verification Status Banner -->
    @php
        $verificationStatus = auth()->user()->getEmployerVerificationStatus();
        $kycOnly = config('app.employer_kyc_only', false);
    @endphp

    @if(!auth()->user()->canPostJobs())
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="kyc-alert-icon">
                                <i class="bi bi-shield-exclamation"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            @if($verificationStatus['status'] === 'kyc_pending')
                                <h5 class="mb-2" style="color: #92400e; font-weight: 700;">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Identity Verification Required
                                </h5>
                                <p class="mb-3" style="color: #78350f; font-size: 0.95rem;">
                                    To post jobs and access all employer features, you must complete KYC (Know Your Customer) identity verification. This is a one-time process that takes only a few minutes.
                                </p>
                                <button type="button" class="btn btn-warning btn-lg px-4" onclick="startInlineVerification()" style="font-weight: 600; border-radius: 10px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                                    <i class="bi bi-shield-check me-2"></i>Complete Verification Now
                                </button>
                            @elseif($verificationStatus['status'] === 'documents_pending')
                                <h5 class="mb-2" style="color: #92400e; font-weight: 700;">
                                    <i class="bi bi-file-earmark-text-fill me-2"></i>Document Approval Pending
                                </h5>
                                <p class="mb-3" style="color: #78350f; font-size: 0.95rem;">
                                    Your KYC verification is complete! However, you still need to submit required business documents for admin approval before posting jobs.
                                </p>
                                <a href="{{ route('employer.documents.index') }}" class="btn btn-warning btn-lg px-4" style="font-weight: 600; border-radius: 10px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                                    <i class="bi bi-upload me-2"></i>Submit Documents
                                </a>
                            @else
                                <h5 class="mb-2" style="color: #92400e; font-weight: 700;">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Verification Required
                                </h5>
                                <p class="mb-3" style="color: #78350f; font-size: 0.95rem;">
                                    {{ $verificationStatus['message'] }}
                                </p>
                                <button type="button" class="btn btn-warning btn-lg px-4" onclick="startInlineVerification()" style="font-weight: 600; border-radius: 10px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                                    <i class="bi bi-shield-check me-2"></i>Complete Verification
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->isKycVerified() && session('kyc_just_completed'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="kyc-success-icon">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2" style="color: #065f46; font-weight: 700;">
                                <i class="bi bi-check-circle-fill me-2"></i>Identity Verified Successfully!
                            </h5>
                            <p class="mb-0" style="color: #064e3b; font-size: 0.95rem;">
                                @if($kycOnly)
                                    Congratulations! Your identity has been verified. You can now post jobs and access all employer features.
                                @else
                                    Your identity has been verified! You can now submit your business documents for approval to start posting jobs.
                                @endif
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Stats Grid -->
    <div class="row g-3 mb-4">
        <!-- Total Jobs -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Total Jobs</p>
                    <h3 class="stat-number">{{ number_format($postedJobs) }}</h3>
                    <span class="stat-badge {{ ($postedJobsGrowth ?? 0) >= 0 ? 'badge-success' : 'badge-danger' }}">
                        <i class="bi bi-{{ ($postedJobsGrowth ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($postedJobsGrowth ?? 0) }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Active Jobs -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-green">
                <div class="stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Active Jobs</p>
                    <h3 class="stat-number">{{ number_format($activeJobs) }}</h3>
                    <span class="stat-badge {{ ($activeJobsGrowth ?? 0) >= 0 ? 'badge-success' : 'badge-danger' }}">
                        <i class="bi bi-{{ ($activeJobsGrowth ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($activeJobsGrowth ?? 0) }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-orange">
                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Pending</p>
                    <h3 class="stat-number">{{ number_format($pendingApplications ?? 0) }}</h3>
                    <span class="stat-badge badge-warning">
                        <i class="bi bi-clock"></i>
                        Need review
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Applications -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-purple">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Applications</p>
                    <h3 class="stat-number">{{ number_format($totalApplications) }}</h3>
                    <span class="stat-badge {{ ($applicationsGrowth ?? 0) >= 0 ? 'badge-success' : 'badge-danger' }}">
                        <i class="bi bi-{{ ($applicationsGrowth ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($applicationsGrowth ?? 0) }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Applications Chart -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1" style="font-weight: 600; color: #111827;">Applications Overview</h5>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Track your application trends</p>
                        </div>
                        <select class="form-select form-select-sm" style="width: auto; border-radius: 8px;">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>Last 3 months</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="applicationsChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0" style="font-weight: 600; color: #111827;">Recent Activity</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentActivities->take(5) as $activity)
                    <div class="activity-item">
                        <div class="activity-icon activity-{{ $activity['type'] }}">
                            <i class="bi bi-{{ $activity['icon'] }}"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">{{ $activity['title'] }}</p>
                            <p class="activity-desc">{{ $activity['description'] }}</p>
                            <span class="activity-time">{{ $activity['created_at']->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <p class="text-muted mt-2">No recent activity</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Jobs -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1" style="font-weight: 600; color: #111827;">Recent Jobs</h5>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Your latest job postings</p>
                        </div>
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0" style="background: white;">
                    @if($recentJobs->isEmpty())
                        <div class="text-center py-5" style="background: white;">
                            <i class="bi bi-briefcase" style="font-size: 4rem; color: #cbd5e1;"></i>
                            <h5 class="mt-3 mb-2" style="color: #111827;">No Jobs Posted Yet</h5>
                            <p class="text-muted mb-4">Start by posting your first job listing</p>
                            <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Post Your First Job
                            </a>
                        </div>
                    @else
                        <div class="table-responsive" style="background: white;">
                            <table class="table table-hover mb-0" style="background: white;">
                                <thead style="background: #f9fafb;">
                                    <tr>
                                        <th class="border-0 py-3 px-4" style="color: #374151; font-weight: 600;">Job Title</th>
                                        <th class="border-0 py-3 text-center" style="color: #374151; font-weight: 600;">Applications</th>
                                        <th class="border-0 py-3 text-center" style="color: #374151; font-weight: 600;">Status</th>
                                        <th class="border-0 py-3 text-center" style="color: #374151; font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody style="background: white;">
                                    @foreach($recentJobs as $job)
                                    <tr style="background: white;">
                                        <td class="px-4 py-3" style="background: white;">
                                            <div class="d-flex align-items-center">
                                                <div class="job-icon me-3">
                                                    <i class="bi bi-briefcase"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1" style="font-weight: 600; color: #111827;">{{ $job->title }}</h6>
                                                    <p class="mb-0" style="font-size: 0.875rem; color: #6b7280;">
                                                        <i class="bi bi-geo-alt me-1"></i>{{ $job->location }}
                                                        <span class="mx-2">•</span>
                                                        <i class="bi bi-clock me-1"></i>{{ $job->created_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center py-3" style="background: white;">
                                            <a href="{{ route('employer.applications.index', ['job_id' => $job->id]) }}" class="text-decoration-none">
                                                <span class="badge bg-light px-3 py-2" style="font-size: 0.875rem; color: #111827; border: 1px solid #e5e7eb;">
                                                    <strong>{{ $job->applications_count }}</strong> <span style="color: #6b7280;">Total</span>
                                                </span>
                                            </a>
                                        </td>
                                        <td class="text-center py-3" style="background: white;">
                                            <span class="badge {{ $job->status == 'active' ? 'bg-success' : 'bg-warning' }}" style="padding: 0.5rem 1rem; font-weight: 600;">
                                                {{ ucfirst($job->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center py-3" style="background: white;">
                                            <a href="{{ route('employer.jobs.edit', $job->id) }}" class="btn btn-sm btn-outline-primary me-1" style="border-radius: 8px;">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('employer.applications.index', ['job_id' => $job->id]) }}" class="btn btn-sm btn-outline-success" style="border-radius: 8px;">
                                                <i class="bi bi-people"></i>
                                            </a>
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
    </div>

</div>
@endsection

@push('styles')
<style>
/* KYC Alert Styles */
.kyc-alert-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.75rem;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.kyc-success-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.75rem;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Eye-Catching Dashboard Styles */
body {
    background: #f8fafc !important;
}

.hero-welcome {
    padding: 2rem 0;
}

/* Stat Cards - More Vibrant */
.stat-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    border: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.stat-card-blue::before {
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.stat-card-green::before {
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

.stat-card-orange::before {
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
}

.stat-card-purple::before {
    background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%);
}

.stat-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
}

.stat-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-card-blue .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card-green .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.stat-card-orange .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.stat-card-purple .stat-icon {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

.stat-details {
    flex: 1;
}

.stat-label {
    font-size: 0.8125rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

/* Activity Items */
.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-application {
    background: #dbeafe;
    color: #1e40af;
}

.activity-job {
    background: #d1fae5;
    color: #065f46;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.activity-desc {
    color: #6b7280;
    margin-bottom: 0.25rem;
    font-size: 0.8125rem;
}

.activity-time {
    color: #9ca3af;
    font-size: 0.75rem;
}

/* Job Icon */
.job-icon {
    width: 40px;
    height: 40px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
}

/* Cards - Bright and Clean */
.card {
    background: white !important;
    border: none !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
    background: white !important;
    border-bottom: 1px solid #f3f4f6 !important;
}

/* Table Styling */
.table {
    color: #111827 !important;
}

.table thead {
    background: #f9fafb !important;
}

.table thead th {
    color: #374151 !important;
    font-weight: 600 !important;
    font-size: 0.8125rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.table tbody td {
    color: #111827 !important;
}

.table-hover tbody tr {
    background: white !important;
}

.table-hover tbody tr:hover {
    background-color: #f9fafb !important;
}

/* Activity Panel - Light Background */
.activity-item {
    background: white !important;
}

/* Job Icon - More Colorful */
.job-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

/* Buttons - More Vibrant */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4) !important;
}

/* Make text more readable */
h2, h3, h4, h5, h6 {
    color: #111827 !important;
}

p {
    color: #374151 !important;
}

.text-muted {
    color: #6b7280 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Applications Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('applicationsChart');
    if (ctx) {
        // Get data with fallbacks
        const labels = @json($applicationTrendsLabels ?? []);
        const data = @json($applicationTrendsData ?? []);

        // Only create chart if we have valid data
        if (labels.length > 0) {
            try {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Applications',
                            data: data,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
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
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                borderRadius: 8,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.parsed.y + (context.parsed.y === 1 ? ' application' : ' applications');
                                        return label;
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
                                        size: 12
                                    }
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
                                    }
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
            } catch (error) {
                console.error('Error initializing chart:', error);
                // Display error message in chart container
                ctx.parentElement.innerHTML = '<div class="text-center py-5"><i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i><p class="text-muted mt-2">Unable to load chart data</p></div>';
            }
        } else {
            // Display "no data" message
            ctx.parentElement.innerHTML = '<div class="text-center py-5"><i class="bi bi-bar-chart" style="font-size: 2rem; color: #cbd5e1;"></i><p class="text-muted mt-2">No application data available yet</p></div>';
        }
    }
});
</script>
@endpush
