@extends('layouts.admin')

@section('page_title', 'Analytics Dashboard')

@section('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
<style>
    .analytics-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .analytics-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .analytics-card-header {
        padding: 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .analytics-card-body {
        padding: 1.25rem;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stat-badge-blue {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .stat-badge-green {
        background: #dcfce7;
        color: #15803d;
    }

    .stat-badge-orange {
        background: #fed7aa;
        color: #c2410c;
    }

    .stat-badge-purple {
        background: #e9d5ff;
        color: #7c3aed;
    }

    /* Cluster Visualization */
    .cluster-bubble {
        position: relative;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .cluster-bubble:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .cluster-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        padding: 2rem;
        min-height: 300px;
        align-items: center;
    }

    .cluster-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    /* Map Container */
    #jobsMap, #heatMap {
        width: 100%;
        height: 500px;
        border-radius: 8px;
    }

    /* Skills Chart */
    .skill-bar {
        height: 24px;
        border-radius: 4px;
        background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 100%);
        transition: width 0.5s ease;
    }

    .skill-item {
        margin-bottom: 0.75rem;
    }

    .skill-name {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .skill-percentage {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Insights Panel */
    .insight-item {
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        border-left: 4px solid #3b82f6;
    }

    .insight-item.warning {
        border-left-color: #f59e0b;
        background: #fffbeb;
    }

    .insight-item.success {
        border-left-color: #10b981;
        background: #ecfdf5;
    }

    /* Loading State */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: inherit;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e5e7eb;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Tabs */
    .analytics-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .analytics-tab {
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        background: #f3f4f6;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .analytics-tab:hover {
        background: #e5e7eb;
    }

    .analytics-tab.active {
        background: #3b82f6;
        color: white;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Job Fair Planning */
    .venue-card {
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }

    .venue-rank {
        width: 28px;
        height: 28px;
        background: #3b82f6;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
    }

    /* Heatmap Legend */
    .heatmap-legend {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: white;
        border-radius: 4px;
        margin-top: 0.5rem;
    }

    .heatmap-gradient {
        width: 150px;
        height: 16px;
        background: linear-gradient(90deg, #3b82f6 0%, #f59e0b 50%, #ef4444 100%);
        border-radius: 4px;
    }

    /* Silhouette Score */
    .silhouette-meter {
        height: 8px;
        background: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .silhouette-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.5s ease;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .cluster-container {
            padding: 1rem;
        }

        #jobsMap, #heatMap {
            height: 350px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Analytics Dashboard</h1>
            <p class="text-muted mb-0">K-Means Clustering, Labor Trends & Geographic Analysis</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshAllData()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
            </button>
            <button class="btn btn-success" onclick="exportReport()">
                <i class="bi bi-download me-1"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="analytics-card">
                <div class="analytics-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Active Jobs</p>
                            <h3 class="mb-0">{{ number_format($stats['total_jobs']) }}</h3>
                        </div>
                        <span class="stat-badge stat-badge-blue">
                            <i class="bi bi-briefcase me-1"></i> Jobs
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="analytics-card">
                <div class="analytics-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Job Seekers</p>
                            <h3 class="mb-0">{{ number_format($stats['total_jobseekers']) }}</h3>
                        </div>
                        <span class="stat-badge stat-badge-green">
                            <i class="bi bi-people me-1"></i> Users
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="analytics-card">
                <div class="analytics-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Applications</p>
                            <h3 class="mb-0">{{ number_format($stats['total_applications']) }}</h3>
                        </div>
                        <span class="stat-badge stat-badge-orange">
                            <i class="bi bi-file-text me-1"></i> Applied
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="analytics-card">
                <div class="analytics-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">KYC Verified</p>
                            <h3 class="mb-0">{{ number_format($stats['verified_jobseekers']) }}</h3>
                        </div>
                        <span class="stat-badge stat-badge-purple">
                            <i class="bi bi-shield-check me-1"></i> Verified
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Tabs -->
    <div class="analytics-tabs">
        <button class="analytics-tab active" onclick="switchTab('clusters')">
            <i class="bi bi-diagram-3"></i> K-Means Clusters
        </button>
        <button class="analytics-tab" onclick="switchTab('skills')">
            <i class="bi bi-bar-chart"></i> Skills & Trends
        </button>
        <button class="analytics-tab" onclick="switchTab('map')">
            <i class="bi bi-geo-alt"></i> Job Vacancies Map
        </button>
        <button class="analytics-tab" onclick="switchTab('heatmap')">
            <i class="bi bi-map"></i> Applicant Density
        </button>
        <button class="analytics-tab" onclick="switchTab('jobfair')">
            <i class="bi bi-calendar-event"></i> Job Fair Planning
        </button>
    </div>

    <!-- Tab Contents -->
    <!-- K-Means Clusters Tab -->
    <div id="clusters-tab" class="tab-content active">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <div>
                            <h5 class="mb-1">K-Means Cluster Visualization</h5>
                            <p class="text-muted small mb-0">Job categories clustered by demand and skills</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="small text-muted me-2">Clusters (K):</label>
                            <select id="clusterK" class="form-select form-select-sm" style="width: 80px;" onchange="loadClusterData()">
                                <option value="3">3</option>
                                <option value="5" selected>5</option>
                                <option value="7">7</option>
                                <option value="10">10</option>
                            </select>
                        </div>
                    </div>
                    <div class="analytics-card-body position-relative">
                        <div id="clusterLoading" class="loading-overlay" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <div id="clusterContainer" class="cluster-container">
                            <!-- Clusters will be rendered here -->
                        </div>
                        <div id="clusterLegend" class="cluster-legend"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="analytics-card mb-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Clustering Quality</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Silhouette Score</span>
                                <span id="silhouetteScore" class="fw-bold">-</span>
                            </div>
                            <div class="silhouette-meter">
                                <div id="silhouetteFill" class="silhouette-fill bg-success" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">Higher is better (0-1 scale)</small>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 id="totalClusters" class="mb-0">-</h4>
                                <small class="text-muted">Clusters</small>
                            </div>
                            <div class="col-4">
                                <h4 id="totalClusterJobs" class="mb-0">-</h4>
                                <small class="text-muted">Jobs</small>
                            </div>
                            <div class="col-4">
                                <h4 id="totalClusterUsers" class="mb-0">-</h4>
                                <small class="text-muted">Seekers</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Key Insights</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="clusterInsights">
                            <!-- Insights will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skills & Trends Tab -->
    <div id="skills-tab" class="tab-content">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Top Skills in Demand</h5>
                        <select id="skillsDays" class="form-select form-select-sm" style="width: 120px;" onchange="loadSkillTrends()">
                            <option value="7">Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="90">Last 90 days</option>
                        </select>
                    </div>
                    <div class="analytics-card-body position-relative">
                        <div id="skillsLoading" class="loading-overlay" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <div id="skillsContainer">
                            <!-- Skills will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Job Posting Trends by Category</h5>
                    </div>
                    <div class="analytics-card-body">
                        <canvas id="categoryTrendsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Labor Market Indicators</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div class="row text-center" id="laborIndicators">
                            <!-- Labor indicators will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Vacancies Map Tab -->
    <div id="map-tab" class="tab-content">
        <div class="row g-4">
            <div class="col-lg-9">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Interactive Job Vacancies Map</h5>
                        <div class="d-flex gap-2">
                            <select id="mapCategory" class="form-select form-select-sm" style="width: 180px;" onchange="loadMapData()">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="analytics-card-body position-relative">
                        <div id="mapLoading" class="loading-overlay" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <div id="jobsMap"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Jobs by Barangay</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="barangayStats" style="max-height: 450px; overflow-y: auto;">
                            <!-- Barangay stats will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applicant Density Heatmap Tab -->
    <div id="heatmap-tab" class="tab-content">
        <div class="row g-4">
            <div class="col-lg-9">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Applicant Density Heat Map</h5>
                    </div>
                    <div class="analytics-card-body position-relative">
                        <div id="heatmapLoading" class="loading-overlay" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <div id="heatMap"></div>
                        <div class="heatmap-legend">
                            <span class="small">Low</span>
                            <div class="heatmap-gradient"></div>
                            <span class="small">High</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="analytics-card mb-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Density Statistics</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="densityStats">
                            <!-- Density stats will be rendered here -->
                        </div>
                    </div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Top Areas</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="topAreas" style="max-height: 300px; overflow-y: auto;">
                            <!-- Top areas will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Fair Planning Tab -->
    <div id="jobfair-tab" class="tab-content">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="analytics-card mb-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Industry Breakdown for Job Fair</h5>
                        <select id="jobfairIndustry" class="form-select form-select-sm" style="width: 180px;" onchange="loadJobFairData()">
                            <option value="">All Industries</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="analytics-card-body position-relative">
                        <div id="jobfairLoading" class="loading-overlay" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <canvas id="industryChart" height="250"></canvas>
                    </div>
                </div>

                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Top Participating Employers</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="topEmployers">
                            <!-- Top employers will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="analytics-card mb-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Job Fair Summary</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="jobfairSummary">
                            <!-- Summary will be rendered here -->
                        </div>
                    </div>
                </div>

                <div class="analytics-card mb-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Recommended Venues</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="recommendedVenues">
                            <!-- Recommended venues will be rendered here -->
                        </div>
                    </div>
                </div>

                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">Best Months for Job Fair</h5>
                    </div>
                    <div class="analytics-card-body">
                        <canvas id="monthlyTrendsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cluster Detail Modal -->
<div class="modal fade" id="clusterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clusterModalTitle">Cluster Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="clusterModalBody">
                <!-- Modal content will be rendered here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<script>
    // Mapbox token
    const mapboxToken = '{{ config('mapbox.public_token') }}';

    // Maps
    let jobsMap = null;
    let heatMap = null;

    // Charts
    let categoryTrendsChart = null;
    let industryChart = null;
    let monthlyTrendsChart = null;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadClusterData();
    });

    // Tab switching
    function switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.analytics-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelector(`[onclick="switchTab('${tabName}')"]`).classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(`${tabName}-tab`).classList.add('active');

        // Load data for the tab if not loaded
        switch(tabName) {
            case 'clusters':
                if (!document.getElementById('clusterContainer').innerHTML.trim()) {
                    loadClusterData();
                }
                break;
            case 'skills':
                loadSkillTrends();
                break;
            case 'map':
                if (!jobsMap) {
                    setTimeout(() => initJobsMap(), 100);
                }
                loadMapData();
                break;
            case 'heatmap':
                if (!heatMap) {
                    setTimeout(() => initHeatMap(), 100);
                }
                loadHeatmapData();
                break;
            case 'jobfair':
                loadJobFairData();
                break;
        }
    }

    // Load K-Means Cluster Data
    async function loadClusterData() {
        const k = document.getElementById('clusterK').value;
        document.getElementById('clusterLoading').style.display = 'flex';

        try {
            const response = await fetch(`{{ route('admin.analytics.clusters') }}?k=${k}`);
            const result = await response.json();

            if (result.success) {
                renderClusters(result.data);
            }
        } catch (error) {
            console.error('Error loading cluster data:', error);
        } finally {
            document.getElementById('clusterLoading').style.display = 'none';
        }
    }

    // Render clusters visualization
    function renderClusters(data) {
        const container = document.getElementById('clusterContainer');
        const legendContainer = document.getElementById('clusterLegend');

        // Calculate sizes based on job count
        const maxJobs = Math.max(...data.clusters.map(c => c.job_count), 1);

        let html = '';
        let legendHtml = '';

        data.clusters.forEach((cluster, index) => {
            const size = Math.max(60, Math.min(150, (cluster.job_count / maxJobs) * 150));

            html += `
                <div class="cluster-bubble"
                     style="width: ${size}px; height: ${size}px; background: ${cluster.color}; font-size: ${Math.max(12, size/5)}px;"
                     onclick="showClusterDetails(${index})"
                     data-cluster='${JSON.stringify(cluster)}'>
                    ${cluster.job_count}
                </div>
            `;

            legendHtml += `
                <div class="legend-item">
                    <div class="legend-color" style="background: ${cluster.color}"></div>
                    <span>${cluster.name}</span>
                </div>
            `;
        });

        container.innerHTML = html;
        legendContainer.innerHTML = legendHtml;

        // Update statistics
        document.getElementById('silhouetteScore').textContent = data.silhouette_score.toFixed(2);
        document.getElementById('silhouetteFill').style.width = `${data.silhouette_score * 100}%`;
        document.getElementById('totalClusters').textContent = data.k;
        document.getElementById('totalClusterJobs').textContent = data.total_jobs;
        document.getElementById('totalClusterUsers').textContent = data.total_jobseekers;

        // Render insights
        const insightsContainer = document.getElementById('clusterInsights');
        let insightsHtml = '';

        data.cluster_summary.insights.forEach((insight, i) => {
            const className = i === 0 ? 'success' : (insight.includes('gap') ? 'warning' : '');
            insightsHtml += `<div class="insight-item ${className}">${insight}</div>`;
        });

        insightsContainer.innerHTML = insightsHtml;

        // Store data for modal
        window.clusterData = data;
    }

    // Show cluster details modal
    function showClusterDetails(index) {
        const cluster = window.clusterData.clusters[index];
        const modal = new bootstrap.Modal(document.getElementById('clusterModal'));

        document.getElementById('clusterModalTitle').textContent = `${cluster.name} Cluster`;

        let skillsHtml = cluster.top_skills.slice(0, 5).map(skill => `
            <span class="badge bg-primary me-1 mb-1">${skill.name} (${skill.count})</span>
        `).join('');

        document.getElementById('clusterModalBody').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Statistics</h6>
                    <table class="table table-sm">
                        <tr><td>Total Jobs</td><td class="fw-bold">${cluster.job_count}</td></tr>
                        <tr><td>Applications</td><td class="fw-bold">${cluster.application_count}</td></tr>
                        <tr><td>Interested Jobseekers</td><td class="fw-bold">${cluster.jobseeker_count}</td></tr>
                        <tr><td>Average Salary</td><td class="fw-bold">PHP ${cluster.avg_salary.toLocaleString()}</td></tr>
                        <tr><td>Demand Score</td><td class="fw-bold">${cluster.demand_score}/100</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Top Skills in this Cluster</h6>
                    <div class="mb-3">${skillsHtml || '<span class="text-muted">No skills data</span>'}</div>

                    <h6>Job Locations</h6>
                    <p class="text-muted small">${cluster.locations.length} jobs with coordinates</p>
                </div>
            </div>
        `;

        modal.show();
    }

    // Load Skills & Trends
    async function loadSkillTrends() {
        const days = document.getElementById('skillsDays').value;
        document.getElementById('skillsLoading').style.display = 'flex';

        try {
            const response = await fetch(`{{ route('admin.analytics.skills') }}?days=${days}`);
            const result = await response.json();

            if (result.success) {
                renderSkills(result.data);
                renderCategoryTrends(result.data.category_trends);
                renderLaborIndicators(result.data.labor_indicators);
            }
        } catch (error) {
            console.error('Error loading skills data:', error);
        } finally {
            document.getElementById('skillsLoading').style.display = 'none';
        }
    }

    // Render skills chart
    function renderSkills(data) {
        const container = document.getElementById('skillsContainer');
        const maxCount = Math.max(...data.skills.map(s => s.count), 1);

        let html = '';
        data.skills.forEach(skill => {
            const width = (skill.count / maxCount) * 100;
            html += `
                <div class="skill-item">
                    <div class="d-flex justify-content-between">
                        <span class="skill-name">${skill.name}</span>
                        <span class="skill-percentage">${skill.count} jobs (${skill.percentage}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: ${width}%"></div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html || '<p class="text-muted">No skills data available</p>';
    }

    // Render category trends chart
    function renderCategoryTrends(trends) {
        const ctx = document.getElementById('categoryTrendsChart').getContext('2d');

        if (categoryTrendsChart) {
            categoryTrendsChart.destroy();
        }

        const colors = ['#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6', '#ec4899'];

        const datasets = trends.slice(0, 6).map((trend, i) => ({
            label: trend.category,
            data: trend.data.map(d => d.count),
            borderColor: colors[i],
            backgroundColor: colors[i] + '20',
            tension: 0.4,
            fill: true,
        }));

        const labels = trends[0]?.data.map(d => d.date) || [];

        categoryTrendsChart = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Render labor indicators
    function renderLaborIndicators(indicators) {
        const container = document.getElementById('laborIndicators');

        container.innerHTML = `
            <div class="col-md-2">
                <h4 class="${indicators.job_posting_growth >= 0 ? 'text-success' : 'text-danger'}">
                    ${indicators.job_posting_growth >= 0 ? '+' : ''}${indicators.job_posting_growth}%
                </h4>
                <small class="text-muted">Job Posting Growth</small>
            </div>
            <div class="col-md-2">
                <h4 class="${indicators.application_growth >= 0 ? 'text-success' : 'text-danger'}">
                    ${indicators.application_growth >= 0 ? '+' : ''}${indicators.application_growth}%
                </h4>
                <small class="text-muted">Application Growth</small>
            </div>
            <div class="col-md-2">
                <h4>${indicators.new_jobseekers}</h4>
                <small class="text-muted">New Job Seekers</small>
            </div>
            <div class="col-md-3">
                <h4>${indicators.jobs_per_applicant}</h4>
                <small class="text-muted">Jobs per Applicant</small>
            </div>
            <div class="col-md-3">
                <h4>${indicators.application_rate}</h4>
                <small class="text-muted">Applications per Job</small>
            </div>
        `;
    }

    // Initialize Jobs Map
    function initJobsMap() {
        if (!mapboxToken) {
            document.getElementById('jobsMap').innerHTML = '<div class="alert alert-warning m-3">Mapbox token not configured</div>';
            return;
        }

        mapboxgl.accessToken = mapboxToken;

        jobsMap = new mapboxgl.Map({
            container: 'jobsMap',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [125.42, 6.75],
            zoom: 12
        });

        jobsMap.addControl(new mapboxgl.NavigationControl());
    }

    // Load Map Data
    async function loadMapData() {
        const categoryId = document.getElementById('mapCategory').value;
        document.getElementById('mapLoading').style.display = 'flex';

        try {
            const response = await fetch(`{{ route('admin.analytics.map') }}?category_id=${categoryId}`);
            const result = await response.json();

            if (result.success) {
                renderMapMarkers(result.data);
                renderBarangayStats(result.data.barangay_stats);
            }
        } catch (error) {
            console.error('Error loading map data:', error);
        } finally {
            document.getElementById('mapLoading').style.display = 'none';
        }
    }

    // Render map markers
    function renderMapMarkers(data) {
        if (!jobsMap) return;

        // Remove existing markers
        document.querySelectorAll('.mapboxgl-marker').forEach(m => m.remove());

        data.markers.forEach(location => {
            const el = document.createElement('div');
            el.className = 'marker';
            el.style.cssText = `
                background: #3b82f6;
                color: white;
                padding: 4px 8px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: bold;
                cursor: pointer;
                min-width: 24px;
                text-align: center;
            `;
            el.textContent = location.count;

            const popup = new mapboxgl.Popup({ offset: 25 })
                .setHTML(`
                    <div style="max-width: 250px;">
                        <strong>${location.location}</strong><br>
                        <span class="text-muted">${location.count} job(s)</span>
                        <hr style="margin: 8px 0;">
                        ${location.jobs.slice(0, 3).map(j => `
                            <div style="font-size: 12px; margin-bottom: 4px;">
                                <strong>${j.title}</strong><br>
                                <span class="text-muted">${j.company}</span>
                            </div>
                        `).join('')}
                        ${location.jobs.length > 3 ? `<small class="text-muted">+${location.jobs.length - 3} more</small>` : ''}
                    </div>
                `);

            new mapboxgl.Marker(el)
                .setLngLat([location.lng, location.lat])
                .setPopup(popup)
                .addTo(jobsMap);
        });
    }

    // Render barangay statistics
    function renderBarangayStats(stats) {
        const container = document.getElementById('barangayStats');

        if (!stats || stats.length === 0) {
            container.innerHTML = '<p class="text-muted">No barangay data available</p>';
            return;
        }

        let html = '';
        stats.forEach((stat, i) => {
            html += `
                <div class="d-flex align-items-center justify-content-between py-2 ${i < stats.length - 1 ? 'border-bottom' : ''}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2">${i + 1}</span>
                        <span>${stat.barangay}</span>
                    </div>
                    <span class="fw-bold">${stat.job_count}</span>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Initialize Heat Map
    function initHeatMap() {
        if (!mapboxToken) {
            document.getElementById('heatMap').innerHTML = '<div class="alert alert-warning m-3">Mapbox token not configured</div>';
            return;
        }

        mapboxgl.accessToken = mapboxToken;

        heatMap = new mapboxgl.Map({
            container: 'heatMap',
            style: 'mapbox://styles/mapbox/dark-v11',
            center: [125.42, 6.75],
            zoom: 12
        });

        heatMap.addControl(new mapboxgl.NavigationControl());
    }

    // Load Heatmap Data
    async function loadHeatmapData() {
        document.getElementById('heatmapLoading').style.display = 'flex';

        try {
            const response = await fetch('{{ route('admin.analytics.density') }}');
            const result = await response.json();

            if (result.success) {
                renderHeatmap(result.data);
                renderDensityStats(result.data.statistics);
                renderTopAreas(result.data.barangay_distribution);
            }
        } catch (error) {
            console.error('Error loading heatmap data:', error);
        } finally {
            document.getElementById('heatmapLoading').style.display = 'none';
        }
    }

    // Render heatmap
    function renderHeatmap(data) {
        if (!heatMap) return;

        // Wait for map to load
        if (!heatMap.loaded()) {
            heatMap.on('load', () => renderHeatmap(data));
            return;
        }

        // Remove existing layers
        if (heatMap.getLayer('heatmap-layer')) {
            heatMap.removeLayer('heatmap-layer');
        }
        if (heatMap.getSource('heatmap-source')) {
            heatMap.removeSource('heatmap-source');
        }

        // Add heatmap source
        const geojson = {
            type: 'FeatureCollection',
            features: data.heatmap_points.map(point => ({
                type: 'Feature',
                properties: { intensity: point.intensity, count: point.count },
                geometry: {
                    type: 'Point',
                    coordinates: [point.lng, point.lat]
                }
            }))
        };

        heatMap.addSource('heatmap-source', {
            type: 'geojson',
            data: geojson
        });

        heatMap.addLayer({
            id: 'heatmap-layer',
            type: 'heatmap',
            source: 'heatmap-source',
            paint: {
                'heatmap-weight': ['get', 'intensity'],
                'heatmap-intensity': 1,
                'heatmap-color': [
                    'interpolate', ['linear'], ['heatmap-density'],
                    0, 'rgba(59, 130, 246, 0)',
                    0.2, 'rgba(59, 130, 246, 0.5)',
                    0.4, 'rgba(245, 158, 11, 0.6)',
                    0.6, 'rgba(245, 158, 11, 0.8)',
                    0.8, 'rgba(239, 68, 68, 0.9)',
                    1, 'rgba(239, 68, 68, 1)'
                ],
                'heatmap-radius': 30,
                'heatmap-opacity': 0.8
            }
        });
    }

    // Render density statistics
    function renderDensityStats(stats) {
        const container = document.getElementById('densityStats');

        container.innerHTML = `
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Applications</span>
                    <span class="fw-bold">${stats.total_applications}</span>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Avg per Barangay</span>
                    <span class="fw-bold">${stats.avg_per_barangay}</span>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Coverage</span>
                    <span class="fw-bold">${stats.coverage}</span>
                </div>
            </div>
            ${stats.highest_density ? `
            <div class="alert alert-info small mb-0">
                <strong>Highest Density:</strong><br>
                ${stats.highest_density.barangay} (${stats.highest_density.application_count} applications)
            </div>
            ` : ''}
        `;
    }

    // Render top areas
    function renderTopAreas(distribution) {
        const container = document.getElementById('topAreas');

        if (!distribution || distribution.length === 0) {
            container.innerHTML = '<p class="text-muted">No data available</p>';
            return;
        }

        let html = '';
        distribution.slice(0, 10).forEach((item, i) => {
            const maxCount = distribution[0].application_count;
            const width = (item.application_count / maxCount) * 100;

            html += `
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <span>${item.barangay}</span>
                        <span class="fw-bold">${item.application_count}</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-danger" style="width: ${width}%"></div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Load Job Fair Planning Data
    async function loadJobFairData() {
        const industry = document.getElementById('jobfairIndustry').value;
        document.getElementById('jobfairLoading').style.display = 'flex';

        try {
            const response = await fetch(`{{ route('admin.analytics.jobfair') }}?industry=${industry}`);
            const result = await response.json();

            if (result.success) {
                renderIndustryChart(result.data.industry_breakdown);
                renderTopEmployers(result.data.top_employers);
                renderJobFairSummary(result.data.summary);
                renderRecommendedVenues(result.data.recommended_venues);
                renderMonthlyTrends(result.data.monthly_trends);
            }
        } catch (error) {
            console.error('Error loading job fair data:', error);
        } finally {
            document.getElementById('jobfairLoading').style.display = 'none';
        }
    }

    // Render industry chart
    function renderIndustryChart(breakdown) {
        const ctx = document.getElementById('industryChart').getContext('2d');

        if (industryChart) {
            industryChart.destroy();
        }

        const labels = breakdown.map(b => b.category_name);
        const jobCounts = breakdown.map(b => b.job_count);
        const vacancies = breakdown.map(b => b.total_vacancies || 0);

        industryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Jobs',
                        data: jobCounts,
                        backgroundColor: '#3b82f6',
                    },
                    {
                        label: 'Vacancies',
                        data: vacancies,
                        backgroundColor: '#22c55e',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Render top employers
    function renderTopEmployers(employers) {
        const container = document.getElementById('topEmployers');

        if (!employers || employers.length === 0) {
            container.innerHTML = '<p class="text-muted">No employer data available</p>';
            return;
        }

        let html = '<div class="row g-3">';
        employers.forEach((emp, i) => {
            html += `
                <div class="col-md-6">
                    <div class="venue-card">
                        <div class="d-flex align-items-center">
                            <div class="venue-rank me-3">${i + 1}</div>
                            <div>
                                <strong>${emp.employer_name}</strong><br>
                                <small class="text-muted">${emp.job_count} jobs, ${emp.categories} categories</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    }

    // Render job fair summary
    function renderJobFairSummary(summary) {
        const container = document.getElementById('jobfairSummary');

        container.innerHTML = `
            <div class="row text-center g-3">
                <div class="col-6">
                    <h4 class="text-primary mb-0">${summary.total_jobs}</h4>
                    <small class="text-muted">Total Jobs</small>
                </div>
                <div class="col-6">
                    <h4 class="text-success mb-0">${summary.total_vacancies || 0}</h4>
                    <small class="text-muted">Vacancies</small>
                </div>
                <div class="col-6">
                    <h4 class="text-info mb-0">${summary.total_employers}</h4>
                    <small class="text-muted">Employers</small>
                </div>
                <div class="col-6">
                    <h4 class="text-warning mb-0">${summary.categories_covered}</h4>
                    <small class="text-muted">Categories</small>
                </div>
            </div>
        `;
    }

    // Render recommended venues
    function renderRecommendedVenues(venues) {
        const container = document.getElementById('recommendedVenues');

        if (!venues || venues.length === 0) {
            container.innerHTML = '<p class="text-muted">No venue data available</p>';
            return;
        }

        let html = '';
        venues.forEach((venue, i) => {
            html += `
                <div class="venue-card d-flex align-items-center">
                    <div class="venue-rank me-3">${i + 1}</div>
                    <div class="flex-grow-1">
                        <strong>${venue.barangay}</strong><br>
                        <small class="text-muted">${venue.job_count} jobs, ${venue.total_vacancies || 0} vacancies</small>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Render monthly trends chart
    function renderMonthlyTrends(trends) {
        const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');

        if (monthlyTrendsChart) {
            monthlyTrendsChart.destroy();
        }

        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const labels = trends.map(t => monthNames[t.month - 1]);
        const data = trends.map(t => t.count);

        monthlyTrendsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Job Postings',
                    data,
                    backgroundColor: '#f59e0b',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Refresh all data
    function refreshAllData() {
        // Clear cache first
        fetch('{{ route('admin.analytics.clear-cache') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(() => {
                loadClusterData();
                showAdminToast('Data refreshed successfully', 'success');
            });
    }

    // Export report
    function exportReport() {
        window.location.href = '{{ route('admin.analytics.export') }}';
    }
</script>
@endsection
