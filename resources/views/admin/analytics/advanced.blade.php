@extends('layouts.admin')

@section('page_title', 'Advanced Analytics')

@section('styles')
<style>
    .analytics-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }

    .analytics-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .analytics-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .chart-container {
        position: relative;
        height: 400px;
        margin-top: 1rem;
    }

    .heatmap-container {
        position: relative;
        height: 500px;
        margin-top: 1rem;
    }

    .metric-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #667eea;
        margin: 0.5rem 0;
    }

    .metric-label {
        font-size: 0.875rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metric-change {
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .metric-change.positive {
        color: #10b981;
    }

    .metric-change.negative {
        color: #ef4444;
    }

    .date-range-selector {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .date-range-btn {
        padding: 0.5rem 1rem;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
    }

    .date-range-btn:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .date-range-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 12px;
    }

    .spinner {
        border: 4px solid #f3f4f6;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .funnel-step {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid #667eea;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
    }

    .funnel-step:hover {
        transform: translateX(8px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .funnel-label {
        font-weight: 600;
        color: #374151;
    }

    .funnel-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #667eea;
    }

    .funnel-rate {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }

    .analytics-table {
        width: 100%;
        margin-top: 1rem;
    }

    .analytics-table th {
        background: #f9fafb;
        padding: 0.75rem;
        font-weight: 600;
        color: #374151;
        text-align: left;
        border-bottom: 2px solid #e5e7eb;
    }

    .analytics-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .analytics-table tbody tr:hover {
        background: #f9fafb;
    }

    .badge-metric {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-high {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-medium {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-low {
        background: #fee2e2;
        color: #991b1b;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Analytics Header -->
    <div class="analytics-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2">📊 Advanced Analytics Dashboard</h1>
                <p class="mb-0 opacity-90">Deep insights into your platform's performance and trends</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="date-range-selector">
        <button class="date-range-btn active" data-range="7">Last 7 Days</button>
        <button class="date-range-btn" data-range="30">Last 30 Days</button>
        <button class="date-range-btn" data-range="60">Last 60 Days</button>
        <button class="date-range-btn" data-range="90">Last 90 Days</button>
        <button class="date-range-btn" data-range="365">Last Year</button>
    </div>

    <!-- Summary Metrics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="metric-card">
                <div class="metric-label">Total Jobs</div>
                <div class="metric-value" id="metric-jobs">{{ $metrics['total_jobs'] }}</div>
                <div class="metric-change positive">
                    <i class="bi bi-arrow-up"></i> Active
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="metric-card">
                <div class="metric-label">Applications</div>
                <div class="metric-value" id="metric-applications">{{ $metrics['total_applications'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="metric-card">
                <div class="metric-label">New Users</div>
                <div class="metric-value" id="metric-users">{{ $metrics['new_users'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-label">Acceptance Rate</div>
                <div class="metric-value" id="metric-acceptance">{{ $metrics['acceptance_rate'] }}%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-label">Avg Time to Hire</div>
                <div class="metric-value" id="metric-time">{{ $metrics['avg_time_to_hire'] }}</div>
                <div class="metric-label">days</div>
            </div>
        </div>
    </div>

    <!-- Time Series Chart -->
    <div class="row">
        <div class="col-12">
            <div class="analytics-card">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>Activity Trends Over Time
                </h5>
                <div class="chart-container">
                    <canvas id="timeSeriesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Heatmap and Conversion Funnel -->
    <div class="row">
        <div class="col-lg-8">
            <div class="analytics-card">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-heat me-2"></i>User Activity Heatmap
                    <small class="text-muted">(Registration Activity by Day & Hour)</small>
                </h5>
                <div class="heatmap-container">
                    <canvas id="activityHeatmap"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="analytics-card">
                <h5 class="mb-3">
                    <i class="bi bi-funnel me-2"></i>Conversion Funnel
                </h5>
                <div id="conversionFunnel">
                    <div class="text-center">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Trends -->
    <div class="row">
        <div class="col-lg-6">
            <div class="analytics-card">
                <h5 class="mb-0">
                    <i class="bi bi-briefcase me-2"></i>Top Job Categories
                </h5>
                <div class="chart-container" style="height: 350px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="analytics-card">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt me-2"></i>Top Hiring Locations
                </h5>
                <div class="chart-container" style="height: 350px;">
                    <canvas id="locationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Peak Hiring Times -->
    <div class="row">
        <div class="col-lg-6">
            <div class="analytics-card">
                <h5 class="mb-0">
                    <i class="bi bi-clock me-2"></i>Jobs by Day of Week
                </h5>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="dayOfWeekChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="analytics-card">
                <h5 class="mb-0">
                    <i class="bi bi-calendar3 me-2"></i>Hiring Activity by Month
                </h5>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="monthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="analytics-card">
                <h5 class="mb-3">
                    <i class="bi bi-table me-2"></i>Category Performance & Conversion Rates
                </h5>
                <div class="table-responsive">
                    <table class="analytics-table" id="categoryPerformanceTable">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total Applications</th>
                                <th>Accepted</th>
                                <th>Conversion Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@2.0.1/dist/chartjs-chart-matrix.min.js"></script>

<script>
let currentRange = 7;
let charts = {};

// Initialize all charts on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeDateRangeSelector();
    loadAllAnalytics();
});

function initializeDateRangeSelector() {
    document.querySelectorAll('.date-range-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-range-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentRange = parseInt(this.dataset.range);
            loadAllAnalytics();
        });
    });
}

function loadAllAnalytics() {
    loadTimeSeriesChart();
    loadHeatmap();
    loadConversionFunnel();
    loadCategoryChart();
    loadLocationChart();
    loadPeakHiringTimes();
}

// Time Series Chart
function loadTimeSeriesChart() {
    fetch(`{{ route('admin.analytics.time-series') }}?days=${currentRange}`)
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('timeSeriesChart');

            if (charts.timeSeries) {
                charts.timeSeries.destroy();
            }

            charts.timeSeries = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [
                        {
                            label: 'Jobs Posted',
                            data: data.map(d => d.jobs),
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Applications',
                            data: data.map(d => d.applications),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'New Users',
                            data: data.map(d => d.users),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
}

// Activity Heatmap
function loadHeatmap() {
    fetch(`{{ route('admin.analytics.heatmap') }}?days=${currentRange}`)
        .then(res => res.json())
        .then(response => {
            const ctx = document.getElementById('activityHeatmap');

            if (charts.heatmap) {
                charts.heatmap.destroy();
            }

            // Prepare data for matrix chart
            const matrixData = [];
            response.days.forEach((day, dayIndex) => {
                response.hours.forEach(hour => {
                    matrixData.push({
                        x: hour,
                        y: day,
                        v: response.data[dayIndex][hour]
                    });
                });
            });

            charts.heatmap = new Chart(ctx, {
                type: 'matrix',
                data: {
                    datasets: [{
                        label: 'User Registrations',
                        data: matrixData,
                        backgroundColor(context) {
                            const value = context.dataset.data[context.dataIndex].v;
                            const alpha = value / Math.max(...matrixData.map(d => d.v));
                            return `rgba(102, 126, 234, ${alpha})`;
                        },
                        borderWidth: 1,
                        borderColor: 'rgba(255, 255, 255, 0.5)',
                        width: ({chart}) => (chart.chartArea || {}).width / 24 - 1,
                        height: ({chart}) => (chart.chartArea || {}).height / 7 - 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: false,
                        tooltip: {
                            callbacks: {
                                title() {
                                    return '';
                                },
                                label(context) {
                                    const v = context.dataset.data[context.dataIndex];
                                    return [`${v.y}, ${v.x}:00`, `Registrations: ${v.v}`];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'category',
                            labels: response.hours,
                            offset: true,
                            ticks: {
                                stepSize: 2
                            },
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Hour of Day'
                            }
                        },
                        y: {
                            type: 'category',
                            labels: response.days,
                            offset: true,
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Day of Week'
                            }
                        }
                    }
                }
            });
        });
}

// Conversion Funnel
function loadConversionFunnel() {
    fetch(`{{ route('admin.analytics.conversion-rates') }}?days=${currentRange}`)
        .then(res => res.json())
        .then(data => {
            const funnel = data.funnel;
            const html = `
                <div class="funnel-step">
                    <div class="funnel-label">Total Users</div>
                    <div>
                        <div class="funnel-value">${funnel.total_users.toLocaleString()}</div>
                    </div>
                </div>
                <div class="funnel-step">
                    <div class="funnel-label">Applied for Jobs</div>
                    <div>
                        <div class="funnel-value">${funnel.users_with_applications.toLocaleString()}</div>
                        <div class="funnel-rate">${funnel.application_rate}% conversion</div>
                    </div>
                </div>
                <div class="funnel-step">
                    <div class="funnel-label">Total Applications</div>
                    <div>
                        <div class="funnel-value">${funnel.total_applications.toLocaleString()}</div>
                    </div>
                </div>
                <div class="funnel-step">
                    <div class="funnel-label">Accepted</div>
                    <div>
                        <div class="funnel-value">${funnel.accepted_applications.toLocaleString()}</div>
                        <div class="funnel-rate">${funnel.acceptance_rate}% success rate</div>
                    </div>
                </div>
            `;
            document.getElementById('conversionFunnel').innerHTML = html;

            // Update category performance table
            updateCategoryTable(data.by_category);
        });
}

function updateCategoryTable(categories) {
    const tbody = document.querySelector('#categoryPerformanceTable tbody');

    if (categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No data available</td></tr>';
        return;
    }

    tbody.innerHTML = categories.map(cat => {
        const rate = parseFloat(cat.conversion_rate || 0);
        let badge = 'badge-low';
        let performance = 'Low';

        if (rate >= 30) {
            badge = 'badge-high';
            performance = 'High';
        } else if (rate >= 15) {
            badge = 'badge-medium';
            performance = 'Medium';
        }

        return `
            <tr>
                <td><strong>${cat.category}</strong></td>
                <td>${cat.applications}</td>
                <td>${cat.accepted}</td>
                <td><strong>${rate}%</strong></td>
                <td><span class="badge-metric ${badge}">${performance}</span></td>
            </tr>
        `;
    }).join('');
}

// Category Chart
function loadCategoryChart() {
    fetch(`{{ route('admin.analytics.job-trends-category') }}?days=${currentRange}`)
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('categoryChart');

            if (charts.category) {
                charts.category.destroy();
            }

            charts.category = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.category),
                    datasets: [{
                        label: 'Total Jobs',
                        data: data.map(d => d.total_jobs),
                        backgroundColor: '#667eea',
                    }, {
                        label: 'Active Jobs',
                        data: data.map(d => d.active_jobs),
                        backgroundColor: '#10b981',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
}

// Location Chart
function loadLocationChart() {
    fetch(`{{ route('admin.analytics.job-trends-location') }}?days=${currentRange}`)
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('locationChart');

            if (charts.location) {
                charts.location.destroy();
            }

            charts.location = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: data.map(d => d.location),
                    datasets: [{
                        label: 'Job Postings',
                        data: data.map(d => d.job_count),
                        backgroundColor: '#f59e0b',
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
}

// Peak Hiring Times
function loadPeakHiringTimes() {
    fetch(`{{ route('admin.analytics.peak-hiring') }}?days=${currentRange}`)
        .then(res => res.json())
        .then(data => {
            // Day of Week Chart
            const dayCtx = document.getElementById('dayOfWeekChart');
            if (charts.dayOfWeek) {
                charts.dayOfWeek.destroy();
            }

            charts.dayOfWeek = new Chart(dayCtx, {
                type: 'bar',
                data: {
                    labels: data.jobs_by_day.map(d => d.day_name),
                    datasets: [{
                        label: 'Job Postings',
                        data: data.jobs_by_day.map(d => d.count),
                        backgroundColor: '#8b5cf6',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Month Chart
            const monthCtx = document.getElementById('monthChart');
            if (charts.month) {
                charts.month.destroy();
            }

            charts.month = new Chart(monthCtx, {
                type: 'line',
                data: {
                    labels: data.hiring_by_month.map(d => `${d.month} ${d.year}`),
                    datasets: [{
                        label: 'Jobs Posted',
                        data: data.hiring_by_month.map(d => d.job_count),
                        borderColor: '#ec4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
}
</script>
@endpush
