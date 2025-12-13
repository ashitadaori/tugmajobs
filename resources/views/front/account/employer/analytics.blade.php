@extends('layouts.employer')

@section('page_title', 'Analytics')

@section('content')
<!-- Header with Date Range -->
<div class="ep-flex ep-items-center ep-justify-between ep-mb-6">
    <div>
        <p style="color: var(--ep-gray-500); font-size: var(--ep-font-size-sm); margin: 0;">Track your recruitment performance and insights</p>
    </div>
    <div class="btn-group" role="group" id="dateRangeButtons">
        <button type="button" class="ep-btn ep-btn-outline" data-range="week">Week</button>
        <button type="button" class="ep-btn ep-btn-primary" data-range="month">Month</button>
        <button type="button" class="ep-btn ep-btn-outline" data-range="year">Year</button>
    </div>
</div>

<!-- Stats Overview -->
<div class="ep-stats-grid ep-mb-8">
    <div class="ep-stat-card primary">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-briefcase-fill"></i>
            </div>
        </div>
        <div class="ep-stat-label">Total Jobs Posted</div>
        <div class="ep-stat-value" id="total-jobs">{{ $jobMetrics['total_jobs'] }}</div>
        <a href="{{ route('employer.jobs.index') }}" class="ep-stat-link">
            View jobs <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="ep-stat-card success">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
        <div class="ep-stat-label">Active Jobs</div>
        <div class="ep-stat-value" id="active-jobs">{{ $jobMetrics['active_jobs'] }}</div>
        <a href="{{ route('employer.jobs.index', ['status' => 'active']) }}" class="ep-stat-link">
            Manage active <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="ep-stat-card info">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
        <div class="ep-stat-label">Total Applications</div>
        <div class="ep-stat-value" id="total-applications">{{ $jobMetrics['total_applications'] }}</div>
        <a href="{{ route('employer.applications.index') }}" class="ep-stat-link">
            View all <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="ep-stat-card warning">
        <div class="ep-stat-header">
            <div class="ep-stat-icon">
                <i class="bi bi-eye-fill"></i>
            </div>
        </div>
        <div class="ep-stat-label">Profile Views</div>
        <div class="ep-stat-value" id="profile-views">{{ $jobMetrics['profile_views'] }}</div>
        <a href="{{ route('employer.profile.edit') }}" class="ep-stat-link">
            Edit profile <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Charts Section -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--ep-space-6);" class="charts-grid ep-mb-8">
    <!-- Application Trends Chart -->
    <div class="ep-card">
        <div class="ep-card-header">
            <h3 class="ep-card-title">
                <i class="bi bi-graph-up"></i>
                Application Trends
            </h3>
        </div>
        <div class="ep-card-body">
            <div class="ep-chart-container" style="height: 320px;">
                <canvas id="applicationTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Job Performance Chart -->
    <div class="ep-card">
        <div class="ep-card-header">
            <h3 class="ep-card-title">
                <i class="bi bi-pie-chart"></i>
                Job Status Distribution
            </h3>
        </div>
        <div class="ep-card-body">
            <div class="ep-chart-container" style="height: 320px;">
                <canvas id="jobPerformanceChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Performing Jobs -->
<div class="ep-card">
    <div class="ep-card-header">
        <h3 class="ep-card-title">
            <i class="bi bi-trophy"></i>
            Top Performing Jobs
        </h3>
        <a href="{{ route('employer.jobs.index') }}" class="ep-btn ep-btn-outline ep-btn-sm">
            View All Jobs
        </a>
    </div>
    <div class="ep-card-body" style="padding: 0;">
        <div class="ep-table-wrapper">
            <table class="ep-table">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Category</th>
                        <th style="text-align: center;">Applications</th>
                        <th style="text-align: center;">Views</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Posted Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topJobs as $job)
                    <tr>
                        <td>
                            <div>
                                <div style="font-weight: 600; color: var(--ep-gray-800); margin-bottom: 4px;">{{ $job->title }}</div>
                                <div style="font-size: 12px; color: var(--ep-gray-500);">
                                    <i class="bi bi-geo-alt" style="margin-right: 4px;"></i>{{ $job->location }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="ep-badge ep-badge-gray">{{ $job->category?->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: 600; color: var(--ep-primary);">{{ $job->applications_count }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="color: var(--ep-gray-600);">{{ $job->views_count }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="ep-badge {{ $job->status ? 'ep-badge-success' : 'ep-badge-danger' }}">
                                {{ $job->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="text-align: center; color: var(--ep-gray-600);">
                            {{ $job->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="ep-empty-state">
                                <div class="ep-empty-icon">
                                    <i class="bi bi-bar-chart"></i>
                                </div>
                                <h4 class="ep-empty-title">No Jobs Yet</h4>
                                <p class="ep-empty-description">Post your first job to start seeing analytics data.</p>
                                <a href="{{ route('employer.jobs.create') }}" class="ep-btn ep-btn-primary">
                                    <i class="bi bi-plus-circle"></i>
                                    Post a Job
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--ep-space-6);
}

@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

#dateRangeButtons .ep-btn {
    border-radius: 0;
    margin: 0;
}

#dateRangeButtons .ep-btn:first-child {
    border-radius: var(--ep-radius-md) 0 0 var(--ep-radius-md);
}

#dateRangeButtons .ep-btn:last-child {
    border-radius: 0 var(--ep-radius-md) var(--ep-radius-md) 0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let trendsChart, performanceChart;

    function initCharts(trendsData) {
        // Application Trends Chart
        const trendsCtx = document.getElementById('applicationTrendsChart').getContext('2d');
        trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.map(item => item.date),
                datasets: [{
                    label: 'Applications',
                    data: trendsData.map(item => item.count),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.08)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#4f46e5',
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
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        padding: 14,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: '600', family: 'Inter' },
                        bodyFont: { size: 12, family: 'Inter' },
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { size: 12, family: 'Inter' },
                            color: '#6b7280'
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                    },
                    x: {
                        ticks: {
                            font: { size: 12, family: 'Inter' },
                            color: '#6b7280'
                        },
                        grid: { display: false }
                    }
                }
            }
        });

        // Job Performance Chart
        const performanceCtx = document.getElementById('jobPerformanceChart').getContext('2d');
        performanceChart = new Chart(performanceCtx, {
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
                        '#059669',
                        '#dc2626',
                        '#6b7280'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 12, family: 'Inter' },
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: '600', family: 'Inter' },
                        bodyFont: { size: 12, family: 'Inter' }
                    }
                },
                cutout: '65%'
            }
        });
    }

    function updateMetricsDisplay(metrics) {
        document.getElementById('total-jobs').textContent = metrics.total_jobs;
        document.getElementById('active-jobs').textContent = metrics.active_jobs;
        document.getElementById('total-applications').textContent = metrics.total_applications;
        document.getElementById('profile-views').textContent = metrics.profile_views;
    }

    function updateCharts(trendsData, metrics) {
        trendsChart.data.labels = trendsData.map(item => item.date);
        trendsChart.data.datasets[0].data = trendsData.map(item => item.count);
        trendsChart.update();

        performanceChart.data.datasets[0].data = [
            metrics.active_jobs,
            metrics.total_jobs - metrics.active_jobs,
            0
        ];
        performanceChart.update();
    }

    // Date range button click handler
    document.querySelectorAll('#dateRangeButtons .ep-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#dateRangeButtons .ep-btn').forEach(b => {
                b.classList.remove('ep-btn-primary');
                b.classList.add('ep-btn-outline');
            });
            this.classList.remove('ep-btn-outline');
            this.classList.add('ep-btn-primary');

            const range = this.dataset.range;

            fetch(`/account/employer/analytics/update-range?range=${range}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                updateMetricsDisplay(data.jobMetrics);
                updateCharts(data.applicationTrends, data.jobMetrics);
            })
            .catch(error => {
                console.error('Error updating analytics:', error);
            });
        });
    });

    // Initialize charts with current data
    initCharts({!! json_encode($applicationTrends) !!});
});
</script>
@endpush
@endsection
