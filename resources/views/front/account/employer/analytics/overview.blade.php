@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Analytics & Performance</h1>
                        <p class="text-muted mb-0">Track your job postings and recruitment performance</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="updateDateRange('week')">Week</button>
                            <button type="button" class="btn btn-outline-secondary active" onclick="updateDateRange('month')">Month</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="updateDateRange('year')">Year</button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('employer.analytics.export') }}"><i class="bi bi-download me-2"></i>Export Data</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-2">Total Jobs</p>
                            <h3 class="mb-0">{{ $jobMetrics['total_jobs'] }}</h3>
                        </div>
                        <div class="icon-wrapper bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-briefcase"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-2">Active Jobs</p>
                            <h3 class="mb-0">{{ $jobMetrics['active_jobs'] }}</h3>
                        </div>
                        <div class="icon-wrapper bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-2">Total Applications</p>
                            <h3 class="mb-0">{{ $jobMetrics['total_applications'] }}</h3>
                        </div>
                        <div class="icon-wrapper bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-2">Profile Views</p>
                            <h3 class="mb-0">{{ $jobMetrics['profile_views'] }}</h3>
                        </div>
                        <div class="icon-wrapper bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-eye"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Application Trends Chart -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Application Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="applicationTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Job Performance Chart -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Job Performance</h5>
                </div>
                <div class="card-body">
                    <canvas id="jobPerformanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Activity</th>
                                    <th class="text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $activity)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-wrapper bg-light rounded-circle me-3" style="width: 32px; height: 32px">
                                                <i class="bi bi-{{ $activity->type === 'Application' ? 'person-check' : 'eye' }} text-muted"></i>
                                            </div>
                                            <div>{{ $activity->description }}</div>
                                        </div>
                                    </td>
                                    <td class="text-end text-muted">{{ $activity->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-wrapper i {
    font-size: 24px;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.table td {
    font-size: 0.875rem;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Application Trends Chart
    const applicationTrendsCtx = document.getElementById('applicationTrendsChart').getContext('2d');
    new Chart(applicationTrendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($applicationTrends->pluck('date')) !!},
            datasets: [{
                label: 'Applications',
                data: {!! json_encode($applicationTrends->pluck('count')) !!},
                borderColor: '#4A6CF7',
                backgroundColor: 'rgba(74, 108, 247, 0.1)',
                tension: 0.4,
                fill: true
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
    const jobPerformanceCtx = document.getElementById('jobPerformanceChart').getContext('2d');
    new Chart(jobPerformanceCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($topJobs->pluck('title')) !!},
            datasets: [{
                data: {!! json_encode($topJobs->pluck('applications_count')) !!},
                backgroundColor: [
                    '#4A6CF7',
                    '#6AD2FF',
                    '#4ADE80',
                    '#FFB547',
                    '#FF92AE'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 20
                    }
                }
            }
        }
    });
});

function updateDateRange(range) {
    // Remove active class from all buttons
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Make AJAX call to update data
    fetch(`/employer/analytics/update-range?range=${range}`)
        .then(response => response.json())
        .then(data => {
            // Update metrics
            document.querySelector('#total-jobs').textContent = data.jobMetrics.total_jobs;
            document.querySelector('#active-jobs').textContent = data.jobMetrics.active_jobs;
            document.querySelector('#total-applications').textContent = data.jobMetrics.total_applications;
            document.querySelector('#profile-views').textContent = data.jobMetrics.profile_views;
            
            // Update charts
            applicationTrendsChart.data.labels = data.applicationTrends.map(item => item.date);
            applicationTrendsChart.data.datasets[0].data = data.applicationTrends.map(item => item.count);
            applicationTrendsChart.update();
            
            jobPerformanceChart.data.labels = data.topJobs.map(job => job.title);
            jobPerformanceChart.data.datasets[0].data = data.topJobs.map(job => job.applications_count);
            jobPerformanceChart.update();
        });
}
</script>
@endpush
@endsection 