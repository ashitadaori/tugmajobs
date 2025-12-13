@extends('layouts.employer')

@section('page_title', 'Analytics')

@section('content')
<div class="analytics-page">
    <!-- Page Header -->
    <div class="analytics-header">
        <div class="analytics-header-info">
            <p class="analytics-subtitle">Track your hiring performance and job posting metrics</p>
        </div>
        <div class="analytics-header-actions">
            <!-- Date Range Filter -->
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" id="dateRangeButton">
                    <i class="bi bi-calendar3 me-2"></i><span id="dateRangeText">All Time</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item date-range-option" href="#" data-range="all">All Time</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item date-range-option" href="#" data-range="7">Last 7 Days</a></li>
                    <li><a class="dropdown-item date-range-option" href="#" data-range="30">Last 30 Days</a></li>
                    <li><a class="dropdown-item date-range-option" href="#" data-range="60">Last 60 Days</a></li>
                    <li><a class="dropdown-item date-range-option" href="#" data-range="90">Last 90 Days</a></li>
                </ul>
            </div>

            <!-- Action Buttons Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical me-1"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button class="dropdown-item" type="button" id="exportReportBtn">
                            <i class="bi bi-download me-2"></i>Export Report (CSV)
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item text-warning" type="button" id="resetAnalyticsBtn">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Analytics
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item text-success" type="button" id="restoreAnalyticsBtn">
                            <i class="bi bi-arrow-clockwise me-2"></i>Restore Data
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon views">
                <i class="bi bi-eye"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Total Views</span>
                <span class="metric-value" id="totalViewsValue">{{ number_format($totalViews ?? 0) }}</span>
                <span class="metric-change {{ ($viewsChange ?? 0) >= 0 ? 'positive' : 'negative' }}" id="viewsChange">
                    <i class="bi bi-arrow-{{ ($viewsChange ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($viewsChange ?? 0) }}% from last period
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon applications">
                <i class="bi bi-people"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Applications</span>
                <span class="metric-value" id="totalApplicationsValue">{{ number_format($totalApplications ?? 0) }}</span>
                <span class="metric-change {{ ($applicationsChange ?? 0) >= 0 ? 'positive' : 'negative' }}" id="applicationsChange">
                    <i class="bi bi-arrow-{{ ($applicationsChange ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($applicationsChange ?? 0) }}% from last period
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon conversion">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Conversion Rate</span>
                <span class="metric-value" id="conversionRateValue">{{ $conversionRate ?? '0' }}%</span>
                <span class="metric-change positive">
                    Views to applications
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon time">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Avg. Time to Hire</span>
                <span class="metric-value">{{ $avgTimeToHire ?? 14 }} days</span>
                <span class="metric-change positive">
                    Average across all jobs
                </span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="row g-4">
            <!-- Application Trends Chart -->
            <div class="col-12 col-xl-8">
                <div class="chart-card">
                    <div class="chart-header">
                        <h5 class="chart-title">
                            <i class="bi bi-graph-up me-2"></i>Approved Applications Trend
                        </h5>
                        <span class="chart-subtitle">Applications approved over the selected period</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="applicationTrendsChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Performing Jobs -->
            <div class="col-12 col-xl-4">
                <div class="chart-card">
                    <div class="chart-header">
                        <h5 class="chart-title">
                            <i class="bi bi-trophy me-2"></i>Top Performing Jobs
                        </h5>
                        <span class="chart-subtitle">By application count</span>
                    </div>
                    <div class="chart-body">
                        <div class="top-jobs-list" id="topJobsList">
                            @forelse($topJobs ?? [] as $job)
                                <div class="top-job-item">
                                    <div class="job-info">
                                        <h6 class="job-title">{{ $job['title'] ?? 'Job Title' }}</h6>
                                        <small class="job-date">Posted {{ $job['created_at'] ? $job['created_at']->diffForHumans() : 'recently' }}</small>
                                    </div>
                                    <div class="job-stats">
                                        <span class="job-apps">{{ $job['applications_count'] ?? 0 }}</span>
                                        <small class="job-label">applications</small>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state-small">
                                    <i class="bi bi-briefcase"></i>
                                    <p>No job data available yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics Row -->
    <div class="row g-4 mb-4">
        <!-- Application Sources -->
        <div class="col-12 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="bi bi-pie-chart me-2"></i>Application Sources
                    </h5>
                    <span class="chart-subtitle">Where candidates find your jobs</span>
                </div>
                <div class="chart-body">
                    <canvas id="sourceChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Hiring Funnel -->
        <div class="col-12 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="bi bi-funnel me-2"></i>Hiring Funnel
                    </h5>
                    <span class="chart-subtitle">Candidate progression through stages</span>
                </div>
                <div class="chart-body">
                    <div class="funnel-list">
                        <div class="funnel-item">
                            <div class="funnel-bar" style="width: 100%; background: #3b82f6;"></div>
                            <div class="funnel-info">
                                <span class="funnel-label">Applications Received</span>
                                <span class="funnel-value">{{ $funnelData['applications'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="funnel-item">
                            <div class="funnel-bar" style="width: 75%; background: #06b6d4;"></div>
                            <div class="funnel-info">
                                <span class="funnel-label">Initial Screening</span>
                                <span class="funnel-value">{{ $funnelData['screening'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="funnel-item">
                            <div class="funnel-bar" style="width: 50%; background: #f59e0b;"></div>
                            <div class="funnel-info">
                                <span class="funnel-label">Interviews Scheduled</span>
                                <span class="funnel-value">{{ $funnelData['interviews'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="funnel-item">
                            <div class="funnel-bar" style="width: 25%; background: #10b981;"></div>
                            <div class="funnel-info">
                                <span class="funnel-label">Offers Extended</span>
                                <span class="funnel-value">{{ $funnelData['offers'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Performance Breakdown Table -->
    <div class="performance-section">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h5 class="chart-title">
                        <i class="bi bi-table me-2"></i>Job Performance Breakdown
                    </h5>
                    <span class="chart-subtitle">Track individual job metrics and performance</span>
                </div>
                <button class="btn btn-sm btn-outline-primary" id="exportTableBtn">
                    <i class="bi bi-download me-1"></i> Export Table
                </button>
            </div>
            <div class="chart-body p-0">
                @if($jobPerformanceBreakdown->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-briefcase"></i>
                        <h6>No Jobs Posted Yet</h6>
                        <p>Post your first job to see performance analytics</p>
                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Post New Job
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table performance-table" id="performanceTable">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th class="text-center">Views</th>
                                    <th class="text-center">Applications</th>
                                    <th class="text-center">Conversion</th>
                                    <th class="text-center">Posted Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobPerformanceBreakdown as $job)
                                <tr>
                                    <td>
                                        <div class="job-cell">
                                            <span class="job-name">{{ $job['title'] }}</span>
                                            <small class="job-id">ID: #{{ $job['id'] }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="stat-value">{{ number_format($job['views']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="stat-value">{{ number_format($job['applications']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $job['conversion_rate'] >= 10 ? 'bg-success' : ($job['conversion_rate'] >= 5 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ $job['conversion_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="date-cell">
                                            <span class="date-main">{{ $job['posted_date']->format('M d, Y') }}</span>
                                            <small class="date-ago">{{ $job['posted_date']->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $job['status'] == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($job['status']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($job['views_trend'] > 0)
                                            <span class="trend-badge up">
                                                <i class="bi bi-arrow-up"></i> {{ $job['views_trend'] }}%
                                            </span>
                                        @elseif($job['views_trend'] < 0)
                                            <span class="trend-badge down">
                                                <i class="bi bi-arrow-down"></i> {{ abs($job['views_trend']) }}%
                                            </span>
                                        @else
                                            <span class="trend-badge neutral">
                                                <i class="bi bi-dash"></i> 0%
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Footer -->
                    <div class="performance-summary">
                        <div class="summary-item">
                            <span class="summary-value text-primary">{{ number_format($totalViews) }}</span>
                            <span class="summary-label">Total Views</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-value text-success">{{ number_format($totalApplications) }}</span>
                            <span class="summary-label">Total Applications</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-value text-info">{{ $conversionRate }}%</span>
                            <span class="summary-label">Avg Conversion</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-value text-warning">{{ $jobPerformanceBreakdown->count() }}</span>
                            <span class="summary-label">Total Jobs</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reset Confirmation Modal -->
<div class="modal fade" id="resetConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Reset Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset the analytics display? This will:</p>
                <ul>
                    <li>Set the displayed counters to zero</li>
                    <li>Track new views and applications from this point forward</li>
                    <li>Date range filters will still show actual historical data</li>
                </ul>
                <p class="text-muted small"><strong>Note:</strong> This only affects the display. Your actual data is not deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmResetBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Analytics
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Analytics Page Styles */
.analytics-page {
    padding: 0;
}

/* Header */
.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.analytics-subtitle {
    color: #64748b;
    margin: 0;
    font-size: 0.875rem;
}

.analytics-header-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1200px) {
    .metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }
}

.metric-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.metric-icon.views {
    background: #dbeafe;
    color: #2563eb;
}

.metric-icon.applications {
    background: #dcfce7;
    color: #16a34a;
}

.metric-icon.conversion {
    background: #fef3c7;
    color: #d97706;
}

.metric-icon.time {
    background: #f3e8ff;
    color: #9333ea;
}

.metric-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.metric-label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.metric-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.metric-change {
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.25rem;
}

.metric-change.positive {
    color: #16a34a;
}

.metric-change.negative {
    color: #dc2626;
}

/* Charts Section */
.charts-section {
    margin-bottom: 1.5rem;
}

.chart-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.chart-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.chart-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
}

.chart-subtitle {
    font-size: 0.75rem;
    color: #64748b;
    display: block;
}

.chart-body {
    padding: 1.25rem;
}

/* Top Jobs List */
.top-jobs-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.top-job-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.job-info {
    min-width: 0;
}

.job-info .job-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.job-info .job-date {
    color: #64748b;
}

.job-stats {
    text-align: right;
    flex-shrink: 0;
}

.job-stats .job-apps {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2563eb;
    display: block;
}

.job-stats .job-label {
    font-size: 0.7rem;
    color: #64748b;
}

/* Funnel List */
.funnel-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.funnel-item {
    position: relative;
}

.funnel-bar {
    height: 8px;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.funnel-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.funnel-label {
    font-size: 0.875rem;
    color: #475569;
}

.funnel-value {
    font-weight: 700;
    color: #1e293b;
}

/* Performance Table */
.performance-section {
    margin-bottom: 1.5rem;
}

.performance-table {
    margin-bottom: 0;
}

.performance-table thead th {
    background: #f8fafc;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.performance-table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.performance-table tbody tr:hover {
    background: #f8fafc;
}

.job-cell {
    display: flex;
    flex-direction: column;
}

.job-cell .job-name {
    font-weight: 600;
    color: #1e293b;
}

.job-cell .job-id {
    font-size: 0.75rem;
    color: #94a3b8;
}

.stat-value {
    font-weight: 600;
    color: #1e293b;
}

.date-cell {
    display: flex;
    flex-direction: column;
}

.date-cell .date-main {
    font-weight: 500;
    color: #1e293b;
}

.date-cell .date-ago {
    color: #94a3b8;
}

.trend-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.trend-badge.up {
    background: #dcfce7;
    color: #16a34a;
}

.trend-badge.down {
    background: #fee2e2;
    color: #dc2626;
}

.trend-badge.neutral {
    background: #f1f5f9;
    color: #64748b;
}

/* Performance Summary */
.performance-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    padding: 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .performance-summary {
        grid-template-columns: repeat(2, 1fr);
    }
}

.summary-item {
    text-align: center;
}

.summary-value {
    font-size: 1.5rem;
    font-weight: 700;
    display: block;
}

.summary-label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Empty State */
.empty-state {
    padding: 3rem;
    text-align: center;
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-state h6 {
    color: #475569;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #94a3b8;
    margin-bottom: 1rem;
}

.empty-state-small {
    padding: 2rem;
    text-align: center;
}

.empty-state-small i {
    font-size: 2rem;
    color: #cbd5e1;
    display: block;
    margin-bottom: 0.5rem;
}

.empty-state-small p {
    color: #94a3b8;
    margin: 0;
    font-size: 0.875rem;
}

/* Toast Notification */
.analytics-toast {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease;
}

.analytics-toast.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.analytics-toast.warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
}

.analytics-toast.info {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let applicationTrendsChart;
    let sourceChart;
    let currentRange = null;
    let isResetMode = localStorage.getItem('analyticsResetMode') === 'true';

    // Original data from server
    const originalData = {
        totalViews: {{ $totalViews ?? 0 }},
        totalApplications: {{ $totalApplications ?? 0 }},
        conversionRate: {{ $conversionRate ?? 0 }},
        avgTimeToHire: {{ $avgTimeToHire ?? 0 }},
        viewsChange: {{ $viewsChange ?? 0 }},
        applicationsChange: {{ $applicationsChange ?? 0 }}
    };

    // Initial data from server
    const initialApplicationTrends = @json($applicationTrends ?? []);
    const initialApplicationSources = @json($applicationSources ?? []);

    // Get reset baseline if exists
    let resetBaseline = null;
    const storedBaseline = localStorage.getItem('analyticsResetBaseline');
    if (storedBaseline) {
        try {
            resetBaseline = JSON.parse(storedBaseline);
        } catch (e) {
            console.error('Error parsing reset baseline:', e);
        }
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `analytics-toast ${type}`;
        toast.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Initialize Application Trends Chart
    function initApplicationTrendsChart(data) {
        const ctx = document.getElementById('applicationTrendsChart');
        if (!ctx) return;

        if (applicationTrendsChart) {
            applicationTrendsChart.destroy();
        }

        const labels = data.map(item => item.date);
        const values = data.map(item => item.count);

        applicationTrendsChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Approved Applications',
                    data: values,
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    borderColor: '#16a34a',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#16a34a',
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
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Initialize Source Chart
    function initSourceChart(data) {
        const ctx = document.getElementById('sourceChart');
        if (!ctx) return;

        if (sourceChart) {
            sourceChart.destroy();
        }

        const labels = Object.keys(data);
        const values = Object.values(data);

        sourceChart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels.length > 0 ? labels : ['No data'],
                datasets: [{
                    data: values.length > 0 ? values : [1],
                    backgroundColor: values.length > 0 ? [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ] : ['#e5e7eb'],
                    borderWidth: 0
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
                            usePointStyle: true
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // Update stats display
    function updateStatsDisplay(metrics) {
        document.getElementById('totalViewsValue').textContent = metrics.totalViews.toLocaleString();
        document.getElementById('totalApplicationsValue').textContent = metrics.totalApplications.toLocaleString();

        const conversionRate = metrics.totalViews > 0
            ? ((metrics.totalApplications / metrics.totalViews) * 100).toFixed(1)
            : 0;
        document.getElementById('conversionRateValue').textContent = conversionRate + '%';

        // Update change indicators
        const viewsChangeEl = document.getElementById('viewsChange');
        viewsChangeEl.className = `metric-change ${metrics.viewsChange >= 0 ? 'positive' : 'negative'}`;
        viewsChangeEl.innerHTML = `<i class="bi bi-arrow-${metrics.viewsChange >= 0 ? 'up' : 'down'}"></i> ${Math.abs(metrics.viewsChange)}% from last period`;

        const appsChangeEl = document.getElementById('applicationsChange');
        appsChangeEl.className = `metric-change ${metrics.applicationsChange >= 0 ? 'positive' : 'negative'}`;
        appsChangeEl.innerHTML = `<i class="bi bi-arrow-${metrics.applicationsChange >= 0 ? 'up' : 'down'}"></i> ${Math.abs(metrics.applicationsChange)}% from last period`;
    }

    // Load analytics data via AJAX
    function loadAnalyticsData(range) {
        const button = document.getElementById('dateRangeButton');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Loading...';
        button.disabled = true;

        fetch(`{{ route('employer.analytics.data') }}?range=${range}`)
            .then(response => response.json())
            .then(data => {
                initApplicationTrendsChart(data.applicationTrends);
                updateStatsDisplay(data.metrics);
                document.getElementById('dateRangeText').textContent = `Last ${range} Days`;
                currentRange = range;

                return fetch(`{{ route('employer.analytics.sources') }}?range=${range}`);
            })
            .then(response => response.json())
            .then(sourcesData => {
                initSourceChart(sourcesData);
            })
            .catch(error => {
                console.error('Error loading analytics:', error);
                showToast('Error loading analytics data', 'warning');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }

    // Apply reset mode display
    function applyResetMode() {
        if (!isResetMode || !resetBaseline) return;

        const newViews = Math.max(0, originalData.totalViews - resetBaseline.views);
        const newApplications = Math.max(0, originalData.totalApplications - resetBaseline.applications);
        const newConversionRate = newViews > 0 ? ((newApplications / newViews) * 100).toFixed(1) : 0;

        document.getElementById('totalViewsValue').textContent = newViews.toLocaleString();
        document.getElementById('totalApplicationsValue').textContent = newApplications.toLocaleString();
        document.getElementById('conversionRateValue').textContent = newConversionRate + '%';

        // Reset change indicators
        document.getElementById('viewsChange').innerHTML = '<i class="bi bi-dash"></i> 0% from reset point';
        document.getElementById('applicationsChange').innerHTML = '<i class="bi bi-dash"></i> 0% from reset point';
    }

    // Reset Analytics Button
    document.getElementById('resetAnalyticsBtn')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('resetConfirmModal'));
        modal.show();
    });

    // Confirm Reset Button
    document.getElementById('confirmResetBtn')?.addEventListener('click', function() {
        // Store current counts as baseline
        const baseline = {
            views: originalData.totalViews,
            applications: originalData.totalApplications,
            timestamp: new Date().toISOString()
        };

        localStorage.setItem('analyticsResetMode', 'true');
        localStorage.setItem('analyticsResetBaseline', JSON.stringify(baseline));

        isResetMode = true;
        resetBaseline = baseline;

        // Apply reset display
        document.getElementById('totalViewsValue').textContent = '0';
        document.getElementById('totalApplicationsValue').textContent = '0';
        document.getElementById('conversionRateValue').textContent = '0%';
        document.getElementById('viewsChange').innerHTML = '<i class="bi bi-dash"></i> Tracking from reset point';
        document.getElementById('applicationsChange').innerHTML = '<i class="bi bi-dash"></i> Tracking from reset point';

        bootstrap.Modal.getInstance(document.getElementById('resetConfirmModal')).hide();
        showToast('Analytics reset! New activity will be tracked from this point.', 'success');
    });

    // Restore Data Button
    document.getElementById('restoreAnalyticsBtn')?.addEventListener('click', function() {
        localStorage.removeItem('analyticsResetMode');
        localStorage.removeItem('analyticsResetBaseline');

        isResetMode = false;
        resetBaseline = null;

        // Restore original values
        updateStatsDisplay(originalData);

        showToast('Analytics restored to actual data!', 'success');
    });

    // Export Report Button - Full CSV Export
    document.getElementById('exportReportBtn')?.addEventListener('click', function() {
        // Prepare CSV data
        let csvContent = '';

        // Header
        csvContent += 'Analytics Report - Generated on ' + new Date().toLocaleDateString() + '\n\n';

        // Summary Metrics
        csvContent += 'SUMMARY METRICS\n';
        csvContent += 'Metric,Value\n';
        csvContent += `Total Views,${originalData.totalViews}\n`;
        csvContent += `Total Applications,${originalData.totalApplications}\n`;
        csvContent += `Conversion Rate,${originalData.conversionRate}%\n`;
        csvContent += `Avg Time to Hire,${originalData.avgTimeToHire} days\n`;
        csvContent += `Views Change,${originalData.viewsChange}%\n`;
        csvContent += `Applications Change,${originalData.applicationsChange}%\n\n`;

        // Job Performance Breakdown
        csvContent += 'JOB PERFORMANCE BREAKDOWN\n';
        const table = document.getElementById('performanceTable');
        if (table) {
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push('"' + th.textContent.trim() + '"');
            });
            csvContent += headers.join(',') + '\n';

            table.querySelectorAll('tbody tr').forEach(row => {
                const cells = [];
                row.querySelectorAll('td').forEach(td => {
                    cells.push('"' + td.textContent.replace(/\s+/g, ' ').trim() + '"');
                });
                csvContent += cells.join(',') + '\n';
            });
        }

        // Download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `analytics-report-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        showToast('Report exported successfully!', 'success');
    });

    // Export Table Button
    document.getElementById('exportTableBtn')?.addEventListener('click', function() {
        const table = document.getElementById('performanceTable');
        if (!table) {
            showToast('No data to export', 'warning');
            return;
        }

        let csv = [];
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const csvRow = [];
            cols.forEach(col => {
                csvRow.push('"' + col.textContent.replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"');
            });
            csv.push(csvRow.join(','));
        });

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `job-performance-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        showToast('Table exported successfully!', 'success');
    });

    // Date Range Options
    document.querySelectorAll('.date-range-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const range = this.getAttribute('data-range');

            if (range === 'all') {
                currentRange = null;
                document.getElementById('dateRangeText').textContent = 'All Time';
                location.reload();
            } else {
                loadAnalyticsData(range);
            }
        });
    });

    // Initialize charts
    initApplicationTrendsChart(initialApplicationTrends);
    initSourceChart(initialApplicationSources);

    // Apply reset mode if active
    if (isResetMode && !currentRange) {
        applyResetMode();
    }
});
</script>
@endpush
