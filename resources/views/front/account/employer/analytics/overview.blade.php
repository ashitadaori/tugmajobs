@extends('front.layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <!-- Sidebar -->
    @include('front.account.employer.sidebar')
    
    <!-- Main Content -->
    <div class="dashboard-content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Analytics Overview</h4>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateDateRange('week')">Week</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm active" onclick="updateDateRange('month')">Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateDateRange('year')">Year</button>
                        </div>
                    </div>
                    
                    <!-- Overall Metrics -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Total Jobs</h6>
                                            <h2 class="card-title mb-0">{{ $jobMetrics['total_jobs'] ?? 0 }}</h2>
                                        </div>
                                        <div class="icon-box">
                                            <i class="fas fa-briefcase fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Active Jobs</h6>
                                            <h2 class="card-title mb-0">{{ $jobMetrics['active_jobs'] ?? 0 }}</h2>
                                        </div>
                                        <div class="icon-box">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Applications</h6>
                                            <h2 class="card-title mb-0">{{ $jobMetrics['total_applications'] ?? 0 }}</h2>
                                        </div>
                                        <div class="icon-box">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Profile Views</h6>
                                            <h2 class="card-title mb-0">{{ $jobMetrics['profile_views'] ?? 0 }}</h2>
                                        </div>
                                        <div class="icon-box">
                                            <i class="fas fa-eye fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-3 mb-4">
                        <!-- Application Trends Chart -->
                        <div class="col-md-8">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Application Trends</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="applicationTrendsChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Job Performance Chart -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Job Performance</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="jobPerformanceChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity and Top Jobs Row -->
                    <div class="row g-3">
                        <!-- Recent Activity -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Recent Activity</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        @forelse($recentActivity as $activity)
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <p class="mb-1">{{ $activity->description }}</p>
                                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="list-group-item text-center text-muted py-4">
                                                <i class="fas fa-info-circle mb-2"></i>
                                                <p class="mb-0">No recent activity</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Performing Jobs -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Top Performing Jobs</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Job Title</th>
                                                    <th>Category</th>
                                                    <th class="text-center">Applications</th>
                                                    <th class="text-center">Views</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($topJobs ?? [] as $job)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <h6 class="mb-1">{{ $job->title }}</h6>
                                                                <div class="small text-muted">
                                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $job->location }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $job->category?->name ?? 'Uncategorized' }}</td>
                                                    <td class="text-center">{{ $job->applications_count }}</td>
                                                    <td class="text-center">{{ $job->views_count }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $job->status ? 'success' : 'danger' }}">
                                                            {{ $job->status ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">{{ $job->created_at->format('M d, Y') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">
                                                        <i class="fas fa-folder-open mb-2"></i>
                                                        <p class="mb-0">No jobs found</p>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.dashboard-wrapper {
    display: flex;
    min-height: calc(100vh - var(--header-height));
    position: relative;
    overflow: hidden;
}

.dashboard-content {
    flex: 1;
    overflow-y: auto;
    height: calc(100vh - var(--header-height));
    padding: 1.5rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1.5rem;
    height: auto;
}

.card-header {
    background: #fff;
    border-bottom: 1px solid var(--border-color);
    padding: 1rem 1.25rem;
}

.card-body {
    position: relative;
}

canvas {
    max-height: 200px !important;
}

.icon-box {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.table > :not(caption) > * > * {
    padding: 1rem 1.25rem;
    vertical-align: middle;
}

.table thead th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody td {
    font-size: 0.875rem;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.btn-group .btn {
    padding: 0.375rem 1rem;
    font-size: 0.875rem;
}

.btn-group .btn.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
}

.list-group-item {
    padding: 1rem 1.25rem;
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.table-responsive {
    margin: 0 -1.25rem;
    padding: 0 1.25rem;
    border-top: 1px solid var(--border-color);
}

@media (max-width: 767.98px) {
    .card-body {
        padding: 1rem;
    }
    
    .row {
        margin-right: -0.5rem;
        margin-left: -0.5rem;
    }
    
    .col-md-3, .col-md-4, .col-md-6, .col-md-8 {
        padding-right: 0.5rem;
        padding-left: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Application Trends Chart
    const trendsCtx = document.getElementById('applicationTrendsChart').getContext('2d');
    const trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($applicationTrends->pluck('date')) !!},
            datasets: [{
                label: 'Applications',
                data: {!! json_encode($applicationTrends->pluck('count')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2,
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
    const performanceChart = new Chart(performanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active Jobs', 'Inactive Jobs', 'Draft Jobs'],
            datasets: [{
                data: [
                    {{ $jobMetrics['active_jobs'] ?? 0 }},
                    {{ ($jobMetrics['total_jobs'] ?? 0) - ($jobMetrics['active_jobs'] ?? 0) }},
                    0
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        boxWidth: 8,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    window.updateDateRange = function(range) {
        // Add functionality to update chart range
        const buttons = document.querySelectorAll('.btn-group .btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        // Here you would typically make an AJAX call to get new data
        // and update the charts
    };
});
</script>
@endpush
@endsection 