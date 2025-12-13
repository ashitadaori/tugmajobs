@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Analytics Dashboard</h1>
            <p class="text-muted">Comprehensive insights and statistics</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary" id="refresh-analytics">
                <i class="bi bi-arrow-clockwise me-2"></i>Refresh
            </button>
            <button type="button" class="btn btn-outline-secondary">
                <i class="bi bi-download me-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Total Jobs</h6>
                        <h2 class="mb-0">{{ number_format($totalJobs) }}</h2>
                        <div class="small text-info">
                            <i class="bi bi-check-circle"></i> {{ number_format($activeJobs) }} active
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-briefcase text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Total Applications</h6>
                        <h2 class="mb-0">{{ number_format($totalApplications) }}</h2>
                        <div class="small text-warning">
                            <i class="bi bi-clock"></i> {{ number_format($pendingApplications) }} pending
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-file-text text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Total Users</h6>
                        <h2 class="mb-0">{{ number_format($totalUsers) }}</h2>
                        <div class="small text-success">
                            <i class="bi bi-people"></i> All roles
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-people text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Pending Jobs</h6>
                        <h2 class="mb-0 {{ $pendingJobs > 0 ? 'text-warning' : '' }}">{{ number_format($pendingJobs) }}</h2>
                        <div class="small">
                            @if($pendingJobs > 0)
                                <a href="{{ route('admin.jobs.index', ['status' => '0']) }}" class="text-warning text-decoration-none">
                                    <i class="bi bi-arrow-right"></i> Review Now
                                </a>
                            @else
                                <span class="text-success">
                                    <i class="bi bi-check-circle"></i> All reviewed
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-clock-history {{ $pendingJobs > 0 ? 'text-warning' : 'text-muted' }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Trends Chart -->
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Trends Over Time</h5>
                            <div class="small text-muted">Track growth and activity</div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active" data-chart-type="jobs">Jobs</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="applications">Applications</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="users">Users</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="companies">Companies</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" data-days="7">7 Days</button>
                            <button type="button" class="btn btn-outline-secondary active" data-days="30">30 Days</button>
                            <button type="button" class="btn btn-outline-secondary" data-days="90">90 Days</button>
                        </div>
                    </div>
                    <canvas id="trendsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Job Status Distribution -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Job Status Distribution</h5>
                    <div class="small text-muted">Current job statuses</div>
                </div>
                <div class="card-body">
                    <canvas id="jobStatusChart" height="250"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bi bi-circle-fill text-success me-2"></i>Approved</span>
                            <strong>{{ number_format($approvedJobs) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bi bi-circle-fill text-warning me-2"></i>Pending</span>
                            <strong>{{ number_format($pendingJobs) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-circle-fill text-danger me-2"></i>Rejected</span>
                            <strong>{{ number_format($rejectedJobs) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Application Status -->
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Application Status</h5>
                    <div class="small text-muted">Current application statuses</div>
                </div>
                <div class="card-body">
                    <canvas id="applicationStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Job Categories</h5>
                    <div class="small text-muted">Most popular categories</div>
                </div>
                <div class="card-body">
                    @if($topCategories->count() > 0)
                        @foreach($topCategories as $category)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">{{ $category->name }}</span>
                                    <span class="badge bg-primary">{{ number_format($category->count) }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ ($category->count / $topCategories->first()->count) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">No categories data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Company Analytics Section -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h4 class="mb-3"><i class="bi bi-building me-2"></i>Company Analytics</h4>
        </div>

        <!-- Company Stats Cards -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Total Companies</h6>
                        <h2 class="mb-0">{{ number_format($totalCompanies) }}</h2>
                        <div class="small text-info">
                            <i class="bi bi-building"></i> Employers
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-building text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Active Companies</h6>
                        <h2 class="mb-0 text-success">{{ number_format($activeCompanies) }}</h2>
                        <div class="small text-muted">
                            <i class="bi bi-check-circle"></i> Posted jobs
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Inactive Companies</h6>
                        <h2 class="mb-0 text-warning">{{ number_format($inactiveCompanies) }}</h2>
                        <div class="small text-muted">
                            <i class="bi bi-x-circle"></i> No jobs yet
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-x-circle text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Verified Companies</h6>
                        <h2 class="mb-0 text-info">{{ number_format($verifiedCompanies) }}</h2>
                        <div class="small text-muted">
                            <i class="bi bi-patch-check"></i> Email verified
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-patch-check text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Company Activity Status -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Company Activity Status</h5>
                    <div class="small text-muted">Active vs Inactive</div>
                </div>
                <div class="card-body">
                    <canvas id="companyActivityChart" height="250"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bi bi-circle-fill text-success me-2"></i>Active</span>
                            <strong>{{ number_format($activeCompanies) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bi bi-circle-fill text-warning me-2"></i>Inactive</span>
                            <strong>{{ number_format($inactiveCompanies) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-circle-fill text-info me-2"></i>Verified</span>
                            <strong>{{ number_format($verifiedCompanies) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Companies by Jobs -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Companies by Jobs</h5>
                    <div class="small text-muted">Most job postings</div>
                </div>
                <div class="card-body">
                    @if($topCompaniesByJobs->count() > 0)
                        <div style="max-height: 400px; overflow-y: auto;">
                            @foreach($topCompaniesByJobs as $index => $company)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                            <span class="fw-semibold text-truncate" style="max-width: 200px;" title="{{ $company->name }}">
                                                {{ $company->name }}
                                            </span>
                                        </div>
                                        <span class="badge bg-success">{{ number_format($company->jobs_count) }} jobs</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ ($company->jobs_count / $topCompaniesByJobs->first()->jobs_count) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">No company data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Companies by Applications -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Companies by Applications</h5>
                    <div class="small text-muted">Most applications received</div>
                </div>
                <div class="card-body">
                    @if($topCompaniesByApplications->count() > 0)
                        <div style="max-height: 400px; overflow-y: auto;">
                            @foreach($topCompaniesByApplications as $index => $company)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                            <span class="fw-semibold text-truncate" style="max-width: 180px;" title="{{ $company->name }}">
                                                {{ $company->name }}
                                            </span>
                                        </div>
                                        <span class="badge bg-info">{{ number_format($company->applications_count) }} apps</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ ($company->applications_count / $topCompaniesByApplications->first()->applications_count) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">No application data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.stats-card {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card {
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let trendsChart;
    let currentChartType = 'jobs';
    let currentDays = 30;

    // Initialize Job Status Chart
    const jobStatusCtx = document.getElementById('jobStatusChart').getContext('2d');
    new Chart(jobStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [{{ $approvedJobs }}, {{ $pendingJobs }}, {{ $rejectedJobs }}],
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Initialize Application Status Chart
    const appStatusCtx = document.getElementById('applicationStatusChart').getContext('2d');
    new Chart(appStatusCtx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Accepted', 'Rejected'],
            datasets: [{
                label: 'Applications',
                data: [{{ $pendingApplications }}, {{ $acceptedApplications }}, {{ $rejectedApplications }}],
                backgroundColor: ['#f59e0b', '#22c55e', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Initialize Company Activity Chart
    const companyActivityCtx = document.getElementById('companyActivityChart').getContext('2d');
    window.companyActivityChart = new Chart(companyActivityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Unverified'],
            datasets: [{
                data: [{{ $activeCompanies }}, {{ $inactiveCompanies }}, {{ $unverifiedCompanies }}],
                backgroundColor: ['#22c55e', '#f59e0b', '#6b7280']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Load trends chart
    function loadTrendsChart(type, days) {
        fetch(`{{ route('admin.analytics.dashboard') }}?type=${type}&days=${days}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (trendsChart) {
                trendsChart.destroy();
            }

            const ctx = document.getElementById('trendsChart').getContext('2d');
            trendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: type.charAt(0).toUpperCase() + type.slice(1),
                        data: data.values,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading chart:', error));
    }

    // Initial load
    loadTrendsChart(currentChartType, currentDays);

    // Chart type buttons
    document.querySelectorAll('[data-chart-type]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-chart-type]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentChartType = this.dataset.chartType;
            loadTrendsChart(currentChartType, currentDays);
        });
    });

    // Days buttons
    document.querySelectorAll('[data-days]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-days]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentDays = this.dataset.days;
            loadTrendsChart(currentChartType, currentDays);
        });
    });

    // Refresh button
    document.getElementById('refresh-analytics')?.addEventListener('click', function() {
        location.reload();
    });

    // Auto-refresh company data every 30 seconds
    let refreshInterval = setInterval(refreshCompanyData, 30000); // 30 seconds

    function refreshCompanyData() {
        fetch('{{ route('admin.analytics.dashboard') }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.companyStats) {
                updateCompanyStats(data.companyStats);
                console.log('✅ Company data refreshed automatically');
            }
        })
        .catch(error => console.error('Auto-refresh error:', error));
    }

    function updateCompanyStats(stats) {
        // Update company stat cards
        const companyCards = document.querySelectorAll('.stats-card h2');
        if (companyCards.length >= 8) { // First 4 are general stats, next 4 are company stats
            // Total Companies
            companyCards[4].textContent = stats.totalCompanies.toLocaleString();
            // Active Companies
            companyCards[5].textContent = stats.activeCompanies.toLocaleString();
            // Inactive Companies
            companyCards[6].textContent = stats.inactiveCompanies.toLocaleString();
            // Verified Companies
            companyCards[7].textContent = stats.verifiedCompanies.toLocaleString();
        }

        // Update Top Companies by Jobs list
        if (stats.topCompaniesByJobs && stats.topCompaniesByJobs.length > 0) {
            const jobsContainer = document.querySelector('.col-12.col-xl-4:nth-of-type(2) .card-body > div');
            if (jobsContainer) {
                let html = '';
                const maxJobs = stats.topCompaniesByJobs[0].jobs_count;
                stats.topCompaniesByJobs.forEach((company, index) => {
                    const percentage = (company.jobs_count / maxJobs) * 100;
                    html += `
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">${index + 1}</span>
                                    <span class="fw-semibold text-truncate" style="max-width: 200px;" title="${company.name}">
                                        ${company.name}
                                    </span>
                                </div>
                                <span class="badge bg-success">${company.jobs_count.toLocaleString()} jobs</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                });
                jobsContainer.innerHTML = html;
            }
        }

        // Update Top Companies by Applications list
        if (stats.topCompaniesByApplications && stats.topCompaniesByApplications.length > 0) {
            const appsContainer = document.querySelector('.col-12.col-xl-4:nth-of-type(3) .card-body > div');
            if (appsContainer) {
                let html = '';
                const maxApps = stats.topCompaniesByApplications[0].applications_count;
                stats.topCompaniesByApplications.forEach((company, index) => {
                    const percentage = (company.applications_count / maxApps) * 100;
                    html += `
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">${index + 1}</span>
                                    <span class="fw-semibold text-truncate" style="max-width: 180px;" title="${company.name}">
                                        ${company.name}
                                    </span>
                                </div>
                                <span class="badge bg-info">${company.applications_count.toLocaleString()} apps</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                });
                appsContainer.innerHTML = html;
            }
        }

        // Update company activity chart
        if (window.companyActivityChart) {
            window.companyActivityChart.data.datasets[0].data = [
                stats.activeCompanies,
                stats.inactiveCompanies,
                stats.unverifiedCompanies
            ];
            window.companyActivityChart.update();
        }

        // Show a subtle notification
        showRefreshNotification();
    }

    function showRefreshNotification() {
        const notification = document.createElement('div');
        notification.className = 'alert alert-success position-fixed top-0 end-0 m-3';
        notification.style.zIndex = '9999';
        notification.innerHTML = '<i class="bi bi-check-circle me-2"></i>Data updated';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s';
            setTimeout(() => notification.remove(), 500);
        }, 2000);
    }


});
</script>
@endpush
@endsection
