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
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .legend-item:hover {
            background: #f3f4f6;
        }

        .legend-item.inactive {
            opacity: 0.4;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        /* Scatter Plot Container */
        .scatter-plot-container {
            position: relative;
            height: 450px;
            padding: 1rem;
        }

        /* Cluster Metrics Table */
        .cluster-metrics-table {
            font-size: 0.85rem;
        }

        .cluster-metrics-table th {
            background: #f8fafc;
            font-weight: 600;
        }

        .compactness-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .compactness-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        /* View Toggle Buttons */
        .view-toggle {
            display: flex;
            gap: 0.25rem;
            background: #f3f4f6;
            padding: 0.25rem;
            border-radius: 6px;
        }

        .view-toggle-btn {
            padding: 0.375rem 0.75rem;
            border: none;
            background: transparent;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-toggle-btn.active {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .view-toggle-btn:hover:not(.active) {
            background: #e5e7eb;
        }

        /* Map Container */
        #jobsMap,
        #heatMap {
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
            to {
                transform: rotate(360deg);
            }
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

            #jobsMap,
            #heatMap {
                height: 350px;
            }
        }

        /* K-Means Explanation Styles */
        .kmeans-explanation {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .explanation-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            cursor: pointer;
        }

        .explanation-header h6 {
            margin: 0;
            color: #0369a1;
            font-weight: 600;
        }

        .explanation-icon {
            width: 40px;
            height: 40px;
            background: #0ea5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .explanation-content {
            font-size: 0.9rem;
            color: #334155;
            line-height: 1.6;
        }

        .explanation-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .explanation-step {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #0ea5e9;
        }

        .step-number {
            width: 24px;
            height: 24px;
            background: #0ea5e9;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .step-title {
            font-weight: 600;
            color: #0369a1;
            margin-bottom: 0.5rem;
        }

        .step-description {
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Calculation Breakdown Styles */
        .calculation-card {
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .calculation-card h6 {
            color: #a16207;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .formula-box {
            background: white;
            padding: 0.75rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            border: 1px solid #fde68a;
        }

        .formula-explanation {
            font-size: 0.8rem;
            color: #78716c;
            margin-top: 0.25rem;
        }

        /* Chart Legend Explanation */
        .chart-legend-guide {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .legend-guide-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .legend-guide-item:last-child {
            border-bottom: none;
        }

        .legend-guide-symbol {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .legend-guide-text {
            flex: 1;
        }

        .legend-guide-text strong {
            display: block;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .legend-guide-text span {
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Metric Explanation Tooltip */
        .metric-explain {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .metric-explain .help-icon {
            width: 16px;
            height: 16px;
            background: #e0f2fe;
            color: #0284c7;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            cursor: help;
        }

        /* Collapsible section */
        .collapsible-trigger {
            cursor: pointer;
            user-select: none;
        }

        .collapsible-trigger .bi-chevron-down {
            transition: transform 0.3s ease;
        }

        .collapsible-trigger.collapsed .bi-chevron-down {
            transform: rotate(-90deg);
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
            <!-- What is This? Explainer -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 border-end">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle p-3 bg-primary bg-opacity-10 me-3">
                                            <i class="bi bi-lightbulb text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">What is Smart Grouping?</h6>
                                            <p class="text-muted mb-0 small">
                                                Think of it like sorting clothes into piles - similar items go together.
                                                We group similar jobs (by salary, type, location) and similar users (by
                                                skills, preferences) automatically.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 border-end">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle p-3 bg-success bg-opacity-10 me-3">
                                            <i class="bi bi-bullseye text-success fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Why is this Useful?</h6>
                                            <p class="text-muted mb-0 small">
                                                <strong>Find patterns:</strong> See what types of jobs are most common<br>
                                                <strong>Match better:</strong> Connect job seekers with similar job
                                                groups<br>
                                                <strong>Plan ahead:</strong> Identify gaps in job postings
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle p-3 bg-info bg-opacity-10 me-3">
                                            <i class="bi bi-graph-up text-info fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">How to Read the Charts</h6>
                                            <p class="text-muted mb-0 small">
                                                <strong>Pie chart:</strong> Shows % of jobs/users in each group<br>
                                                <strong>Bar chart:</strong> Shows exact count per group<br>
                                                <strong>Scatter plot:</strong> Shows how groups differ from each other
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-briefcase text-primary fs-3"></i>
                            </div>
                            <h3 class="mb-1" id="jobsCount">-</h3>
                            <p class="text-muted mb-0">Active Jobs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-people text-success fs-3"></i>
                            </div>
                            <h3 class="mb-1" id="usersCount">-</h3>
                            <p class="text-muted mb-0">Job Seekers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-layers text-warning fs-3"></i>
                            </div>
                            <h3 class="mb-1" id="clustersCount">-</h3>
                            <p class="text-muted mb-0">Groups Found</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-info bg-opacity-10"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-cpu text-info fs-3"></i>
                            </div>
                            <span class="badge bg-warning mb-2" id="healthCheckStatus">
                                Check Status
                            </span>
                            <p class="text-muted mb-0 small">Analysis Engine</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Simple Controls -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-sliders text-primary me-2"></i>
                        Analysis Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1"></i> What to Analyze
                            </label>
                            <select class="form-select form-select-lg" id="clusterType" onchange="loadClusterData()">
                                <option value="job" selected>📋 Jobs - Group similar job postings</option>
                                <option value="user">👥 Users - Group similar job seekers</option>
                            </select>
                            <small class="text-muted">Choose whether to analyze jobs or users</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-grid-3x3 me-1"></i> Number of Groups
                            </label>
                            <select class="form-select form-select-lg" id="kValue" onchange="loadClusterData()">
                                <option value="3">3 Groups - Broad categories</option>
                                <option value="4">4 Groups - Moderate detail</option>
                                <option value="5" selected>5 Groups - Balanced (Recommended)</option>
                                <option value="6">6 Groups - More detail</option>
                                <option value="7">7 Groups - Fine-grained</option>
                            </select>
                            <small class="text-muted">More groups = more specific categories</small>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-light border mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle text-primary me-2"></i>
                                    <div>
                                        <strong>Tip:</strong> Start with 5 groups. If groups seem too broad, try 6-7. If too
                                        specific, try 3-4.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Insights Summary -->
            <div class="card border-0 shadow-sm mb-4" id="insightsCard" style="display: none;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-star text-warning me-2"></i>
                        Key Insights
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row" id="insightsContent">
                        <!-- Dynamically populated -->
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <!-- Cluster Distribution Pie Chart -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-pie-chart text-primary me-2"></i>
                                    Group Distribution
                                </h5>
                                <span class="badge bg-light text-dark" data-bs-toggle="tooltip"
                                    title="Shows what percentage of items belong to each group">
                                    <i class="bi bi-question-circle"></i> What's this?
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <i class="bi bi-lightbulb text-warning me-1"></i>
                                This chart shows how items are spread across different groups. Hover over a section to see
                                details.
                            </p>
                            <div style="height: 320px;">
                                <canvas id="clusterPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cluster Sizes Bar Chart -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-bar-chart text-success me-2"></i>
                                    Group Sizes
                                </h5>
                                <span class="badge bg-light text-dark" data-bs-toggle="tooltip"
                                    title="Shows the exact count of items in each group">
                                    <i class="bi bi-question-circle"></i> What's this?
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <i class="bi bi-lightbulb text-warning me-1"></i>
                                Taller bars mean more items in that group. This helps identify the most common types.
                            </p>
                            <div style="height: 320px;">
                                <canvas id="clusterBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Group Breakdown -->
            <div class="card border-0 shadow-sm mb-4" id="groupDetailsCard" style="display: none;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-list-task text-info me-2"></i>
                        Group Details
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Here's a breakdown of each group with what makes them unique:
                    </p>
                    <div class="row" id="groupDetailsContent">
                        <!-- Dynamically populated -->
                    </div>
                </div>
            </div>

            <!-- Quality Score -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-speedometer2 text-primary me-2"></i>
                                Analysis Quality
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-semibold">Grouping Quality Score</span>
                                            <span class="badge bg-primary fs-6" id="qualityBadge">-</span>
                                        </div>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-primary" role="progressbar" id="qualityBar"
                                                style="width: 0%;">
                                                <span id="qualityText">Calculating...</span>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Higher score = groups are more distinct and meaningful
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="border rounded p-3">
                                                <h4 class="mb-1 text-primary" id="samplesValue">-</h4>
                                                <small class="text-muted">Items Analyzed</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-3">
                                                <h4 class="mb-1 text-success" id="iterationsValue">-</h4>
                                                <small class="text-muted">Analysis Rounds</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-3">
                                                <h4 class="mb-1 text-info" id="clustersCountAlt">-</h4>
                                                <small class="text-muted">Groups Created</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Section (Collapsible) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button"
                        data-bs-toggle="collapse" data-bs-target="#advancedSection">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-gear text-secondary me-2"></i>
                                Technical Details (For Advanced Users)
                            </h5>
                            <i class="bi bi-chevron-down text-muted"></i>
                        </div>
                    </button>
                </div>
                <div class="collapse" id="advancedSection">
                    <div class="card-body border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Algorithm Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted" width="40%">Algorithm</td>
                                            <td class="fw-semibold">K-Means Clustering</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Engine</td>
                                            <td class="fw-semibold" id="implementationSource">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Inertia (WCSS)</td>
                                            <td class="fw-semibold" id="inertiaValue">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Silhouette Score</td>
                                            <td class="fw-semibold" id="silhouetteValue">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Cluster Centroid Visualization</h6>
                                <div style="height: 200px;">
                                    <canvas id="scatterChart"></canvas>
                                </div>
                                <small class="text-muted">Each point represents the center of a group</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Overlay for Analysis -->
            <div id="loadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-none"
                style="z-index: 9999; display:none;">
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h4 class="text-primary">Analyzing Your Data...</h4>
                        <p class="text-muted">Our AI is finding patterns and creating groups</p>
                        <div class="progress mx-auto" style="width: 200px; height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- How K-Means Works - Beginner-Friendly Explanation -->
        <div class="kmeans-explanation mb-4">
            <div class="explanation-header collapsible-trigger" data-bs-toggle="collapse"
                data-bs-target="#kmeansExplainContent">
                <div class="explanation-icon">
                    <i class="bi bi-lightbulb"></i>
                </div>
                <div>
                    <h6>What is K-Means Clustering? <small class="text-muted fw-normal">(Click to expand/collapse)</small>
                    </h6>
                    <p class="mb-0 small text-muted">A simple guide to understanding this chart</p>
                </div>
                <i class="bi bi-chevron-down ms-auto"></i>
            </div>
            <div class="collapse show" id="kmeansExplainContent">
                <div class="explanation-content">
                    <p><strong>In simple terms:</strong> K-Means is like sorting your closet! Imagine you have many
                        different jobs, and you want to organize them into groups based on how similar they are. Jobs with
                        similar salaries and popularity get grouped together.</p>

                    <div class="explanation-steps">
                        <div class="explanation-step">
                            <div class="step-title"><span class="step-number">1</span> Collect Data</div>
                            <div class="step-description">We gather information about each job: salary offered and how many
                                people applied (popularity).</div>
                        </div>
                        <div class="explanation-step">
                            <div class="step-title"><span class="step-number">2</span> Place on Chart</div>
                            <div class="step-description">Each job becomes a dot on the chart. Left-to-right shows salary
                                (low to high). Bottom-to-top shows popularity.</div>
                        </div>
                        <div class="explanation-step">
                            <div class="step-title"><span class="step-number">3</span> Find Groups</div>
                            <div class="step-description">The algorithm finds jobs that are close together and colors them
                                the same. Each color = one job category/cluster.</div>
                        </div>
                        <div class="explanation-step">
                            <div class="step-title"><span class="step-number">4</span> Mark Centers</div>
                            <div class="step-description">The star (★) shows the "center" of each group - the average
                                position of all jobs in that cluster.</div>
                        </div>
                    </div>

                    <!-- Chart Reading Guide -->
                    <div class="chart-legend-guide mt-3">
                        <h6 class="mb-3"><i class="bi bi-book me-2"></i>How to Read the Chart</h6>
                        <div class="legend-guide-item">
                            <div class="legend-guide-symbol">
                                <span style="color: #3b82f6;">●</span>
                            </div>
                            <div class="legend-guide-text">
                                <strong>Colored Dots = Individual Jobs</strong>
                                <span>Each dot represents one job posting. Same color means same job category.</span>
                            </div>
                        </div>
                        <div class="legend-guide-item">
                            <div class="legend-guide-symbol">
                                <span style="color: #f59e0b; font-size: 1.5rem;">★</span>
                            </div>
                            <div class="legend-guide-text">
                                <strong>Stars = Cluster Centers (Centroids)</strong>
                                <span>The "average" position of all jobs in that group. Think of it as the typical job in
                                    that category.</span>
                            </div>
                        </div>
                        <div class="legend-guide-item">
                            <div class="legend-guide-symbol">
                                <i class="bi bi-arrow-right text-secondary"></i>
                            </div>
                            <div class="legend-guide-text">
                                <strong>X-Axis (Horizontal) = Salary Level</strong>
                                <span>Jobs on the right pay more than jobs on the left.</span>
                            </div>
                        </div>
                        <div class="legend-guide-item">
                            <div class="legend-guide-symbol">
                                <i class="bi bi-arrow-up text-secondary"></i>
                            </div>
                            <div class="legend-guide-text">
                                <strong>Y-Axis (Vertical) = Popularity</strong>
                                <span>Jobs at the top received more applications (more popular with job seekers).</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <div>
                            <h5 class="mb-1">
                                K-Means Cluster Visualization
                                <i class="bi bi-info-circle-fill text-primary ms-1" style="font-size: 0.9em; cursor: help;"
                                    data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right"
                                    data-bs-html="true" title="Quick Guide"
                                    data-bs-content="<div class='small'><p class='mb-2'><strong>What you're seeing:</strong> Jobs organized into groups based on salary and popularity.</p><ul class='mb-0 ps-3'><li><strong>Dots:</strong> Each dot = 1 job posting</li><li><strong>Colors:</strong> Same color = same job category</li><li><strong>Stars (★):</strong> The 'center' of each group</li><li><strong>Position:</strong> Right = higher salary, Top = more popular</li><li><strong>Tip:</strong> Click on dots or stars to see details!</li></ul></div>">
                                </i>
                            </h5>
                            <p class="text-muted small mb-0">Jobs plotted by salary and demand, grouped into clusters by
                                category</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="view-toggle">
                                <button class="view-toggle-btn active" onclick="setClusterView('scatter')"
                                    id="scatterViewBtn">
                                    <i class="bi bi-scatter-chart"></i> Scatter
                                </button>
                                <button class="view-toggle-btn" onclick="setClusterView('bubble')" id="bubbleViewBtn">
                                    <i class="bi bi-circle"></i> Bubble
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="small text-muted">K:</label>
                                <select id="clusterK" class="form-select form-select-sm" style="width: 70px;"
                                    onchange="loadClusterData()">
                                    <option value="3">3</option>
                                    <option value="5" selected>5</option>
                                    <option value="7">7</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="analytics-card-body position-relative">
                        <div id="clusterLoading" class="loading-overlay" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <!-- Scatter Plot View -->
                        <div id="scatterPlotView" class="scatter-plot-container">
                            <canvas id="clusterScatterChart"></canvas>
                        </div>
                        <!-- Bubble View (Original) -->
                        <div id="bubbleView" style="display: none;">
                            <div id="clusterContainer" class="cluster-container">
                                <!-- Clusters will be rendered here -->
                            </div>
                        </div>
                        <div id="clusterLegend" class="cluster-legend"></div>
                    </div>
                </div>

                <!-- Cluster Distribution Table -->
                <div class="analytics-card mt-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-table me-2"></i>Job Categories Summary
                        </h5>
                        <small class="text-muted">Click any row to see detailed information</small>
                    </div>
                    <div class="analytics-card-body">
                        <p class="small text-muted mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            This table shows key statistics for each job category. Higher demand scores and compactness
                            indicate stronger, more defined job markets.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-hover cluster-metrics-table mb-0">
                                <thead>
                                    <tr>
                                        <th>
                                            Job Category
                                            <i class="bi bi-question-circle ms-1 text-muted"
                                                style="font-size: 0.75rem; cursor: help;" data-bs-toggle="tooltip"
                                                title="The name of the job group/category"></i>
                                        </th>
                                        <th class="text-center">
                                            Jobs
                                            <i class="bi bi-question-circle ms-1 text-muted"
                                                style="font-size: 0.75rem; cursor: help;" data-bs-toggle="tooltip"
                                                title="Total number of job openings in this category"></i>
                                        </th>
                                        <th class="text-center">
                                            Applications
                                            <i class="bi bi-question-circle ms-1 text-muted"
                                                style="font-size: 0.75rem; cursor: help;" data-bs-toggle="tooltip"
                                                title="How many people have applied to jobs in this category"></i>
                                        </th>
                                        <th class="text-center">
                                            Interested
                                            <i class="bi bi-question-circle ms-1 text-muted"
                                                style="font-size: 0.75rem; cursor: help;" data-bs-toggle="tooltip"
                                                title="Number of job seekers who prefer this category"></i>
                                        </th>
                                        <th class="text-center">
                                            Avg Salary
                                            <i class="bi bi-question-circle ms-1 text-muted"
                                                style="font-size: 0.75rem; cursor: help;" data-bs-toggle="tooltip"
                                                title="Average salary offered for jobs in this category"></i>
                                        </th>
                                        <th class="text-center">
                                            Demand
                                            <i class="bi bi-question-circle ms-1 text-muted"
                                                style="font-size: 0.75rem; cursor: help;" data-bs-toggle="tooltip"
                                                title="How 'hot' is this category? Higher score = more demand from both employers and job seekers"></i>
                                        </th>
                                        <th style="width: 130px;">
                                            Similarity
                                            <i class="bi bi-question-circle ms-1 text-muted"
                                                style="font-size: 0.75rem; cursor: help;" data-bs-toggle="tooltip"
                                                title="How similar are jobs within this category? Higher % = jobs in this group have similar salaries and popularity"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="clusterMetricsTable">
                                    <!-- Cluster metrics will be rendered here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="analytics-card mb-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Clustering Quality</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    Silhouette Score
                                    <i class="bi bi-question-circle ms-1" style="cursor: help;" data-bs-toggle="tooltip"
                                        title="How well-separated are the groups? Higher score = cleaner, more distinct groups. Think of it like asking: Are jobs in the same category really similar to each other?"></i>
                                </span>
                                <span id="silhouetteScore" class="fw-bold">-</span>
                            </div>
                            <div class="silhouette-meter">
                                <div id="silhouetteFill" class="silhouette-fill bg-success" style="width: 0%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Groups overlap</small>
                                <small class="text-muted">Groups distinct</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    Inertia (Spread)
                                    <i class="bi bi-question-circle ms-1" style="cursor: help;" data-bs-toggle="tooltip"
                                        title="How spread out are jobs within each group? Lower number = jobs in each group are tightly packed together (more similar). Higher number = jobs are spread out."></i>
                                </span>
                                <span id="inertiaScore" class="fw-bold">-</span>
                            </div>
                            <small class="text-muted d-block mt-1">Lower = tighter groups</small>
                        </div>

                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 id="totalClusters" class="mb-0 text-primary">-</h4>
                                <small class="text-muted">Groups (K)</small>
                            </div>
                            <div class="col-4">
                                <h4 id="totalClusterJobs" class="mb-0 text-success">-</h4>
                                <small class="text-muted">Total Jobs</small>
                            </div>
                            <div class="col-4">
                                <h4 id="totalClusterUsers" class="mb-0 text-info">-</h4>
                                <small class="text-muted">Seekers</small>
                            </div>
                        </div>

                        <hr>
                        <div class="small text-muted">
                            <strong>What do these scores mean?</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li><span class="text-success">●</span> <strong>Good (>0.5):</strong> Job categories are
                                    clearly different from each other</li>
                                <li><span class="text-warning">●</span> <strong>Fair (0.25-0.5):</strong> Some overlap
                                    between categories</li>
                                <li><span class="text-danger">●</span> <strong>Weak (<0.25):< /strong> Categories are mixed
                                            - try changing K value</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Calculation Breakdown Section -->
                <div class="analytics-card mb-4">
                    <div class="analytics-card-header collapsible-trigger" data-bs-toggle="collapse"
                        data-bs-target="#calculationBreakdown">
                        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>How We Calculate Results</h5>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="collapse" id="calculationBreakdown">
                        <div class="analytics-card-body">
                            <div class="calculation-card mb-3">
                                <h6><i class="bi bi-geo-alt"></i> Finding the Center (Centroid)</h6>
                                <div class="formula-box">
                                    Center X = (Sum of all job X positions) ÷ (Number of jobs)<br>
                                    Center Y = (Sum of all job Y positions) ÷ (Number of jobs)
                                </div>
                                <p class="formula-explanation">
                                    <strong>In simple terms:</strong> We add up where all jobs in a group are positioned,
                                    then divide by how many jobs there are. This gives us the "average" location - the
                                    center of the group.
                                </p>
                            </div>

                            <div class="calculation-card mb-3">
                                <h6><i class="bi bi-arrows-angle-contract"></i> Measuring Spread (Inertia)</h6>
                                <div class="formula-box">
                                    Inertia = Sum of (distance from each job to its group center)²
                                </div>
                                <p class="formula-explanation">
                                    <strong>In simple terms:</strong> For each job, we measure how far it is from the center
                                    of its group. We square these distances (to make bigger gaps count more) and add them
                                    all up. A smaller number means jobs are closer to their group's center = better
                                    grouping!
                                </p>
                            </div>

                            <div class="calculation-card mb-3">
                                <h6><i class="bi bi-bullseye"></i> Compactness Score</h6>
                                <div class="formula-box">
                                    Average Distance = (Sum of all distances to center) ÷ (Number of jobs)<br>
                                    Compactness = 1 - (Average Distance ÷ 100)
                                </div>
                                <p class="formula-explanation">
                                    <strong>In simple terms:</strong> This tells us how "tight" each group is. A compactness
                                    of 80% means jobs in that category are very similar to each other. A lower percentage
                                    means the jobs in that group vary a lot.
                                </p>
                            </div>

                            <div class="calculation-card mb-3">
                                <h6><i class="bi bi-graph-up"></i> Demand Score</h6>
                                <div class="formula-box">
                                    Demand Score = (Applications per Job × 10) + (Jobseekers per Job × 5) + (Job Count × 2)
                                </div>
                                <p class="formula-explanation">
                                    <strong>In simple terms:</strong> This measures how "hot" a job category is. More
                                    applications, more interested jobseekers, and more job openings all increase the score.
                                    A score of 50+ means high demand!
                                </p>
                            </div>

                            <div class="calculation-card">
                                <h6><i class="bi bi-bar-chart"></i> Silhouette Score</h6>
                                <div class="formula-box">
                                    For each job: Compare distance to own group vs. distance to nearest other group
                                </div>
                                <p class="formula-explanation">
                                    <strong>In simple terms:</strong> For each job, we ask: "Is this job closer to others in
                                    its own group, or to jobs in different groups?" If most jobs are much closer to their
                                    own group, the score is high (good!). If jobs could belong to multiple groups, the score
                                    is low.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>What This Means For You</h5>
                    </div>
                    <div class="analytics-card-body">
                        <p class="small text-muted mb-3">
                            Actionable insights based on the clustering analysis:
                        </p>
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
                        <select id="skillsDays" class="form-select form-select-sm" style="width: 120px;"
                            onchange="loadSkillTrends()">
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
                            <select id="mapCategory" class="form-select form-select-sm" style="width: 180px;"
                                onchange="loadMapData()">
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
                        <select id="jobfairIndustry" class="form-select form-select-sm" style="width: 180px;"
                            onchange="loadJobFairData()">
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

    <!-- Prescriptive Analytics Tab -->
    <div id="prescriptive-tab" class="tab-content">
        <div class="row g-4">
            <!-- Header Row -->
            <div class="col-12">
                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <div>
                            <h5 class="mb-1">Prescriptive Analytics Dashboard</h5>
                            <p class="text-muted small mb-0">AI-powered recommendations for optimal decision making</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="loadPrescriptiveData()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                            <button class="btn btn-success btn-sm" onclick="exportPrescriptiveReport()">
                                <i class="bi bi-download me-1"></i> Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-8">
                <div class="analytics-card position-relative">
                    <div id="prescriptiveLoading" class="loading-overlay" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                    <div class="analytics-card-body">
                        <!-- Prescriptive Sub-tabs -->
                        <ul class="nav nav-pills mb-4" id="prescriptiveTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="priorities-tab-btn" data-bs-toggle="pill"
                                    data-bs-target="#priorities-content" type="button">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Priorities
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="hiring-tab-btn" data-bs-toggle="pill"
                                    data-bs-target="#hiring-content" type="button">
                                    <i class="bi bi-people me-1"></i> Hiring
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="skills-gap-tab-btn" data-bs-toggle="pill"
                                    data-bs-target="#skills-gap-content" type="button">
                                    <i class="bi bi-mortarboard me-1"></i> Skill Gaps
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="opportunities-tab-btn" data-bs-toggle="pill"
                                    data-bs-target="#opportunities-content" type="button">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Opportunities
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="strategic-tab-btn" data-bs-toggle="pill"
                                    data-bs-target="#strategic-content" type="button">
                                    <i class="bi bi-bullseye me-1"></i> Strategic
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="prescriptiveTabContent">
                            <!-- Intervention Priorities -->
                            <div class="tab-pane fade show active" id="priorities-content" role="tabpanel">
                                <div id="prioritiesContainer">
                                    <div class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                        Loading intervention priorities...
                                    </div>
                                </div>
                            </div>

                            <!-- Hiring Recommendations -->
                            <div class="tab-pane fade" id="hiring-content" role="tabpanel">
                                <div id="hiringContainer">
                                    <div class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                        Loading hiring recommendations...
                                    </div>
                                </div>
                            </div>

                            <!-- Skill Gap Actions -->
                            <div class="tab-pane fade" id="skills-gap-content" role="tabpanel">
                                <div id="skillsGapContainer">
                                    <div class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                        Loading skill gap actions...
                                    </div>
                                </div>
                            </div>

                            <!-- Market Opportunities -->
                            <div class="tab-pane fade" id="opportunities-content" role="tabpanel">
                                <div id="opportunitiesContainer">
                                    <div class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                        Loading market opportunities...
                                    </div>
                                </div>
                            </div>

                            <!-- Strategic Initiatives -->
                            <div class="tab-pane fade" id="strategic-content" role="tabpanel">
                                <div id="strategicContainer">
                                    <div class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                        Loading strategic initiatives...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employer Engagement Actions -->
                <div class="analytics-card mt-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0"><i class="bi bi-person-workspace text-primary me-2"></i>Employer Engagement
                            Actions</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="employerActionsContainer">
                            <div class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Loading employer actions...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobseeker Support Actions -->
                <div class="analytics-card mt-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0"><i class="bi bi-person-badge text-info me-2"></i>Jobseeker Support Actions</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="jobseekerActionsContainer">
                            <div class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Loading jobseeker actions...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="col-lg-4">
                <div class="analytics-card mb-4">
                    <div class="analytics-card-header">
                        <h5 class="mb-0"><i class="bi bi-lightning-charge text-warning me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="quickActionsContainer">
                            <div class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Loading...
                            </div>
                        </div>
                    </div>
                </div>

                <div class="analytics-card">
                    <div class="analytics-card-header">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check text-success me-2"></i>Resource Allocation</h5>
                    </div>
                    <div class="analytics-card-body">
                        <div id="resourceAllocationContainer">
                            <div class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Cluster Detail Modal -->
    <div class="modal fade" id="clusterModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="clusterModalTitle">Cluster Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="clusterModalBody">
                    <!-- Modal content will be rendered here -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        // K-Means specific charts
        let pieChart = null;
        let barChart = null;
        let scatterChart = null;

        const colors = [
            '#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#14b8a6', '#f43f5e'
        ];

        const colorNames = [
            'Blue', 'Red', 'Green', 'Orange', 'Purple',
            'Pink', 'Cyan', 'Lime', 'Teal', 'Rose'
        ];

        // Current view state
        let hiddenClusters = new Set();

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadClusterData();

            // Initialize Bootstrap Popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            });

            // Initialize Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))

            // Handle collapsible section toggle states
            document.querySelectorAll('.collapsible-trigger').forEach(function (trigger) {
                const targetId = trigger.getAttribute('data-bs-target');
                const targetEl = document.querySelector(targetId);

                if (targetEl) {
                    targetEl.addEventListener('show.bs.collapse', function () {
                        trigger.classList.remove('collapsed');
                    });
                    targetEl.addEventListener('hide.bs.collapse', function () {
                        trigger.classList.add('collapsed');
                    });
                    // Set initial state
                    if (!targetEl.classList.contains('show')) {
                        trigger.classList.add('collapsed');
                    }
                }
            });
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });

        // Show loading overlay
        function showLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'block';
            if (overlay) overlay.classList.remove('d-none');
        }

        // Hide loading overlay
        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
            if (overlay) overlay.classList.add('d-none');
        }

        // Load Cluster Data
        async function loadClusterData() {
            const type = document.getElementById('clusterType').value;
            const k = document.getElementById('kValue').value;

            showLoading();

            try {
                const response = await fetch(`{{ route('admin.analytics.kmeans.data') }}?type=${type}&k=${k}`);
                const result = await response.json();

                if (result.success) {
                    updateDashboard(result.data, result.source, type);
                } else {
                    console.error('Error:', result.message);
                }
            } catch (error) {
                console.error('Error loading cluster data:', error);
            } finally {
                hideLoading();
            }
        }

        function updateDashboard(data, source, type) {
            // Update basic counts
            if (document.getElementById('clustersCount')) document.getElementById('clustersCount').textContent = data.metrics.n_clusters;
            if (document.getElementById('clustersCountAlt')) document.getElementById('clustersCountAlt').textContent = data.metrics.n_clusters;
            if (document.getElementById('samplesValue')) document.getElementById('samplesValue').textContent = data.metrics.n_samples.toLocaleString();
            if (document.getElementById('iterationsValue')) document.getElementById('iterationsValue').textContent = data.metrics.n_iterations || '-';
            if (document.getElementById('jobsCount') && data.total_jobs) document.getElementById('jobsCount').textContent = data.total_jobs;
            if (document.getElementById('usersCount') && data.total_users) document.getElementById('usersCount').textContent = data.total_users;

            // Update technical metrics
            if (document.getElementById('inertiaValue')) document.getElementById('inertiaValue').textContent = data.metrics.inertia ? data.metrics.inertia.toLocaleString() : '-';
            if (document.getElementById('silhouetteValue')) document.getElementById('silhouetteValue').textContent = data.metrics.silhouette_score !== null
                ? data.metrics.silhouette_score.toFixed(4)
                : 'N/A';

            // Update source status
            const isAzureML = source === 'azure_ml';
            if (document.getElementById('implementationSource')) document.getElementById('implementationSource').textContent = isAzureML
                ? 'Azure ML (Cloud)'
                : 'Local PHP Analysis';

            if (document.getElementById('healthCheckStatus')) {
                document.getElementById('healthCheckStatus').textContent = isAzureML ? 'Cloud AI' : 'Local';
                document.getElementById('healthCheckStatus').className = isAzureML ? 'badge bg-success mb-2' : 'badge bg-warning mb-2';
            }

            // Update quality score visualization
            updateQualityScore(data.metrics.silhouette_score);

            // Update charts
            updatePieChart(data.cluster_sizes, data.cluster_names);
            updateBarChart(data.cluster_sizes, data.cluster_names);
            updateScatterChart(data.scatter_data, data.cluster_names);

            // Update insights
            updateInsights(data, type);

            // Update group details
            updateGroupDetails(data.cluster_sizes, data.cluster_names, type);
        }

        function updateQualityScore(silhouetteScore) {
            const qualityBar = document.getElementById('qualityBar');
            const qualityText = document.getElementById('qualityText');
            const qualityBadge = document.getElementById('qualityBadge');

            if (!qualityBar) return;

            if (silhouetteScore === null || silhouetteScore === undefined) {
                qualityBar.style.width = '50%';
                qualityText.textContent = 'Calculating...';
                qualityBadge.textContent = 'N/A';
                qualityBadge.className = 'badge bg-secondary fs-6';
                return;
            }

            // Silhouette score ranges from -1 to 1, convert to 0-100%
            const percentage = ((silhouetteScore + 1) / 2) * 100;

            let label, colorClass;
            if (silhouetteScore >= 0.5) {
                label = 'Excellent';
                colorClass = 'bg-success';
            } else if (silhouetteScore >= 0.25) {
                label = 'Good';
                colorClass = 'bg-primary';
            } else if (silhouetteScore >= 0) {
                label = 'Fair';
                colorClass = 'bg-warning';
            } else {
                label = 'Poor';
                colorClass = 'bg-danger';
            }

            qualityBar.style.width = percentage + '%';
            qualityBar.className = 'progress-bar ' + colorClass;
            qualityText.textContent = label + ' (' + (silhouetteScore * 100).toFixed(1) + '%)';
            qualityBadge.textContent = label;
            qualityBadge.className = 'badge fs-6 ' + colorClass;
        }

        function updateInsights(data, type) {
            const insightsCard = document.getElementById('insightsCard');
            const insightsContent = document.getElementById('insightsContent');

            if (!insightsCard || !insightsContent) return;

            const sizes = data.cluster_sizes;
            const names = data.cluster_names;
            const total = sizes.reduce((a, b) => a + b, 0);

            // Find largest and smallest groups
            const maxSize = Math.max(...sizes);
            const minSize = Math.min(...sizes);
            const maxIndex = sizes.indexOf(maxSize);
            const minIndex = sizes.indexOf(minSize);

            const maxPercentage = ((maxSize / total) * 100).toFixed(1);
            const minPercentage = ((minSize / total) * 100).toFixed(1);

            const typeLabel = type === 'job' ? 'jobs' : 'job seekers';

            let html = `
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100 bg-primary bg-opacity-10">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-trophy text-primary me-2"></i>
                                                <strong>Largest Group</strong>
                                            </div>
                                            <h5 class="text-primary mb-1">${names[maxIndex]}</h5>
                                            <p class="text-muted mb-0">
                                                ${maxSize.toLocaleString()} ${typeLabel} (${maxPercentage}% of total)
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100 bg-warning bg-opacity-10">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-flower1 text-warning me-2"></i>
                                                <strong>Smallest Group</strong>
                                            </div>
                                            <h5 class="text-warning mb-1">${names[minIndex]}</h5>
                                            <p class="text-muted mb-0">
                                                ${minSize.toLocaleString()} ${typeLabel} (${minPercentage}% of total)
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100 bg-info bg-opacity-10">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-symmetry-horizontal text-info me-2"></i>
                                                <strong>Distribution Balance</strong>
                                            </div>
                                            <h5 class="text-info mb-1">${getBalanceLabel(sizes)}</h5>
                                            <p class="text-muted mb-0">
                                                ${getBalanceDescription(sizes, type)}
                                            </p>
                                        </div>
                                    </div>
                                `;

            insightsContent.innerHTML = html;
            insightsCard.style.display = 'block';
        }

        function getBalanceLabel(sizes) {
            const total = sizes.reduce((a, b) => a + b, 0);
            const idealSize = total / sizes.length;

            const variance = sizes.reduce((sum, size) => sum + Math.pow(size - idealSize, 2), 0) / sizes.length;
            const stdDev = Math.sqrt(variance);
            const coefficientOfVariation = (stdDev / idealSize) * 100;

            if (coefficientOfVariation < 20) return 'Well Balanced';
            if (coefficientOfVariation < 40) return 'Moderately Balanced';
            if (coefficientOfVariation < 60) return 'Somewhat Uneven';
            return 'Highly Uneven';
        }

        function getBalanceDescription(sizes, type) {
            const label = getBalanceLabel(sizes);
            const typeLabel = type === 'job' ? 'jobs' : 'seekers';

            if (label === 'Well Balanced') {
                return `${typeLabel} are evenly spread across groups`;
            } else if (label === 'Moderately Balanced') {
                return `Most ${typeLabel} are reasonably distributed`;
            } else if (label === 'Somewhat Uneven') {
                return `Some groups have significantly more ${typeLabel}`;
            }
            return `${typeLabel} are concentrated in a few groups`;
        }

        function updateGroupDetails(sizes, names, type) {
            const card = document.getElementById('groupDetailsCard');
            const content = document.getElementById('groupDetailsContent');

            if (!card || !content) return;

            const total = sizes.reduce((a, b) => a + b, 0);
            const typeLabel = type === 'job' ? 'jobs' : 'job seekers';

            let html = '';

            names.forEach((name, index) => {
                const size = sizes[index];
                const percentage = ((size / total) * 100).toFixed(1);
                const color = colors[index % colors.length];

                html += `
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle me-2" style="width: 12px; height: 12px; background: ${color};"></div>
                                                    <strong>${name}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">${size.toLocaleString()} ${typeLabel}</span>
                                                    <span class="badge" style="background: ${color};">${percentage}%</span>
                                                </div>
                                                <div class="progress mt-2" style="height: 6px;">
                                                    <div class="progress-bar" role="progressbar" style="width: ${percentage}%; background: ${color};"></div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
            });

            content.innerHTML = html;
            card.style.display = 'block';
        }

        function updatePieChart(sizes, names) {
            const ctx = document.getElementById('clusterPieChart').getContext('2d');

            if (pieChart) {
                pieChart.destroy();
            }

            pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: names,
                    datasets: [{
                        data: sizes,
                        backgroundColor: colors.slice(0, sizes.length),
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.raw.toLocaleString()} items (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateBarChart(sizes, names) {
            const ctx = document.getElementById('clusterBarChart').getContext('2d');

            if (barChart) {
                barChart.destroy();
            }

            barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [{
                        label: 'Number of Items',
                        data: sizes,
                        backgroundColor: colors.slice(0, sizes.length),
                        borderRadius: 8,
                        borderSkipped: false
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
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            callbacks: {
                                label: function (context) {
                                    return context.raw.toLocaleString() + ' items';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 10 },
                                maxRotation: 45
                            }
                        }
                    }
                }
            });
        }

        function updateScatterChart(scatterData, names) {
            const ctx = document.getElementById('scatterChart').getContext('2d');

            if (scatterChart) {
                scatterChart.destroy();
            }

            // Group data by cluster
            const datasets = [];
            const clusterMap = {};

            scatterData.forEach(point => {
                const clusterKey = point.cluster;
                if (!clusterMap[clusterKey]) {
                    clusterMap[clusterKey] = [];
                }
                clusterMap[clusterKey].push({ x: point.x, y: point.y, size: point.size || 10 });
            });

            Object.keys(clusterMap).forEach((cluster, idx) => {
                const colorIndex = idx % colors.length;
                datasets.push({
                    label: names[idx] || `Group ${parseInt(cluster) + 1}`,
                    data: clusterMap[cluster],
                    backgroundColor: colors[colorIndex],
                    borderColor: 'rgba(255,255,255,0.8)',
                    borderWidth: 1,
                    pointRadius: 6,
                    pointHoverRadius: 10
                });
            });

            scatterChart = new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const point = context.raw;
                                    return `${context.dataset.label}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    }
                }
            });
        }
        // Tab switching
        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.analytics-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector(`[onclick="switchTab('${tabName}')"]`).classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`${tabName}-tab`).classList.add('active');

            // Load data for the tab if not loaded
            switch (tabName) {
                case 'clusters':
                    if (!pieChart) {
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



        // Render clusters visualization (main entry point)


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
                center: [125.4130, 6.8370], // Santa Cruz, Davao del Sur center
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
                center: [125.4130, 6.8370], // Santa Cruz, Davao del Sur center
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

        // ==================== PRESCRIPTIVE ANALYTICS FUNCTIONS ====================

        // Load Prescriptive Analytics Data
        async function loadPrescriptiveData() {
            const loadingEl = document.getElementById('prescriptiveLoading');
            if (loadingEl) {
                loadingEl.style.display = 'flex';
            }

            try {
                // Route missing
                const response = await fetch('#');

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    renderPrescriptiveData(result.data);
                } else {
                    console.error('Prescriptive analytics returned unsuccessful response:', result);
                    showPrescriptiveError('Failed to load prescriptive analytics data.');
                }
            } catch (error) {
                console.error('Error loading prescriptive data:', error);
                showPrescriptiveError('Error loading prescriptive analytics: ' + error.message);
            } finally {
                if (loadingEl) {
                    loadingEl.style.display = 'none';
                }
            }
        }

        // Show error message in prescriptive containers
        function showPrescriptiveError(message) {
            const containers = [
                'prioritiesContainer', 'hiringContainer', 'skillsGapContainer',
                'opportunitiesContainer', 'strategicContainer', 'employerActionsContainer',
                'jobseekerActionsContainer', 'resourceAllocationContainer', 'quickActionsContainer'
            ];

            containers.forEach(id => {
                const container = document.getElementById(id);
                if (container) {
                    container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>${message}</div>`;
                }
            });
        }

        // Render all prescriptive data
        function renderPrescriptiveData(data) {
            renderInterventionPriorities(data.intervention_priorities || []);
            renderHiringRecommendations(data.hiring_recommendations || []);
            renderSkillGapActions(data.skill_gap_actions || []);
            renderMarketOpportunities(data.market_opportunities || []);
            renderStrategicInitiatives(data.strategic_initiatives || []);
            renderEmployerActions(data.employer_engagement_actions || []);
            renderJobseekerActions(data.jobseeker_support_actions || []);
            renderResourceAllocation(data.resource_allocation || []);
            renderQuickActions(data);
        }

        // Render Intervention Priorities
        function renderInterventionPriorities(priorities) {
            const container = document.getElementById('prioritiesContainer');

            if (!priorities || priorities.length === 0) {
                container.innerHTML = '<p class="text-muted">No intervention priorities at this time.</p>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-hover">';
            html += '<thead><tr><th>Category</th><th>Score</th><th>Urgency</th><th>Impact</th><th>Recommended Action</th><th>Timeline</th></tr></thead><tbody>';

            priorities.forEach(priority => {
                const scoreClass = priority.overall_score > 70 ? 'danger' : (priority.overall_score > 50 ? 'warning' : 'info');
                html += `
                                            <tr>
                                                <td><strong>${priority.category}</strong></td>
                                                <td>
                                                    <span class="badge bg-${scoreClass}">${priority.overall_score}</span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 8px; width: 80px;">
                                                        <div class="progress-bar bg-danger" style="width: ${priority.urgency}%"></div>
                                                    </div>
                                                    <small>${priority.urgency}</small>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 8px; width: 80px;">
                                                        <div class="progress-bar bg-success" style="width: ${priority.impact}%"></div>
                                                    </div>
                                                    <small>${priority.impact}</small>
                                                </td>
                                                <td>${priority.recommended_intervention}</td>
                                                <td><span class="badge bg-secondary">${priority.timeline}</span></td>
                                            </tr>
                                        `;
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // Render Hiring Recommendations
        function renderHiringRecommendations(recommendations) {
            const container = document.getElementById('hiringContainer');

            if (!recommendations || recommendations.length === 0) {
                container.innerHTML = '<p class="text-muted">No hiring recommendations at this time.</p>';
                return;
            }

            let html = '';
            recommendations.forEach(rec => {
                const priorityClass = rec.priority === 'high' ? 'danger' : (rec.priority === 'medium' ? 'warning' : 'info');
                html += `
                                            <div class="card mb-3 border-left-${priorityClass}" style="border-left: 4px solid var(--bs-${priorityClass});">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">${rec.title}</h6>
                                                        <span class="badge bg-${priorityClass}">${rec.priority}</span>
                                                    </div>
                                                    <p class="card-text text-muted small">${rec.description}</p>
                                                    <div class="mb-2">
                                                        <strong class="text-success"><i class="bi bi-graph-up me-1"></i>Expected Impact:</strong>
                                                        <span>${rec.expected_impact}</span>
                                                    </div>
                                                    ${rec.action_steps ? `
                                                    <div>
                                                        <strong>Action Steps:</strong>
                                                        <ol class="small mb-0 mt-1">
                                                            ${rec.action_steps.map(step => `<li>${step}</li>`).join('')}
                                                        </ol>
                                                    </div>
                                                    ` : ''}
                                                    ${rec.metrics ? `
                                                    <div class="mt-2 d-flex gap-3">
                                                        ${rec.metrics.jobs_available ? `<span class="badge bg-primary">Jobs: ${rec.metrics.jobs_available}</span>` : ''}
                                                        ${rec.metrics.current_jobseekers ? `<span class="badge bg-success">Jobseekers: ${rec.metrics.current_jobseekers}</span>` : ''}
                                                        ${rec.metrics.gap ? `<span class="badge bg-warning">Gap: ${rec.metrics.gap}</span>` : ''}
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        `;
            });

            container.innerHTML = html;
        }

        // Render Skill Gap Actions
        function renderSkillGapActions(actions) {
            const container = document.getElementById('skillsGapContainer');

            if (!actions || actions.length === 0) {
                container.innerHTML = '<p class="text-muted">No skill gaps identified at this time.</p>';
                return;
            }

            let html = '<div class="row">';
            actions.forEach(action => {
                const priorityClass = action.priority === 'critical' ? 'danger' : (action.priority === 'high' ? 'warning' : 'info');
                html += `
                                            <div class="col-md-6 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                                        <strong>${action.skill}</strong>
                                                        <span class="badge bg-${priorityClass}">${action.priority}</span>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Skill Gap:</span>
                                                            <strong class="text-danger">${action.gap_percentage}%</strong>
                                                        </div>
                                                        <div class="progress mb-3" style="height: 10px;">
                                                            <div class="progress-bar bg-danger" style="width: ${action.gap_percentage}%"></div>
                                                        </div>
                                                        <div class="row text-center mb-3">
                                                            <div class="col-6">
                                                                <small class="text-muted">Demand</small>
                                                                <h5 class="mb-0 text-primary">${action.demand}</h5>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">Supply</small>
                                                                <h5 class="mb-0 text-success">${action.supply}</h5>
                                                            </div>
                                                        </div>
                                                        <p class="small mb-2"><strong>Recommendation:</strong> ${action.recommended_action}</p>
                                                        <p class="small mb-2"><i class="bi bi-clock me-1"></i><strong>Training Duration:</strong> ${action.training_duration}</p>
                                                        ${action.potential_partners ? `
                                                        <p class="small mb-0"><strong>Partners:</strong> ${action.potential_partners.join(', ')}</p>
                                                        ` : ''}
                                                    </div>
                                                </div>
                                            </div>
                                        `;
            });
            html += '</div>';

            container.innerHTML = html;
        }

        // Render Market Opportunities
        function renderMarketOpportunities(opportunities) {
            const container = document.getElementById('opportunitiesContainer');

            if (!opportunities || opportunities.length === 0) {
                container.innerHTML = '<p class="text-muted">No market opportunities identified at this time.</p>';
                return;
            }

            let html = '';
            opportunities.forEach(opp => {
                const typeIcon = opp.type === 'emerging_sector' ? 'bi-rocket-takeoff' : 'bi-geo-alt';
                const typeColor = opp.type === 'emerging_sector' ? 'success' : 'primary';
                html += `
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-3">
                                                            <span class="badge bg-${typeColor} p-2">
                                                                <i class="bi ${typeIcon} fs-5"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">${opp.category || opp.location}</h6>
                                                            <p class="text-muted mb-2">${opp.opportunity}</p>
                                                            ${opp.growth_rate ? `<span class="badge bg-success me-2"><i class="bi bi-graph-up me-1"></i>${opp.growth_rate}% growth</span>` : ''}
                                                            ${opp.potential_jobs ? `<span class="badge bg-primary">Projected: ${opp.potential_jobs} jobs</span>` : ''}
                                                            <p class="mt-2 mb-1"><strong>Recommendation:</strong> ${opp.recommendation}</p>
                                                            ${opp.action_items ? `
                                                            <ul class="small mb-0">
                                                                ${opp.action_items.map(item => `<li>${item}</li>`).join('')}
                                                            </ul>
                                                            ` : ''}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
            });

            container.innerHTML = html;
        }

        // Render Strategic Initiatives
        function renderStrategicInitiatives(initiatives) {
            const container = document.getElementById('strategicContainer');

            if (!initiatives || initiatives.length === 0) {
                container.innerHTML = '<p class="text-muted">No strategic initiatives at this time.</p>';
                return;
            }

            let html = '';
            initiatives.forEach(init => {
                const priorityClass = init.priority === 'critical' ? 'danger' : (init.priority === 'high' ? 'warning' : 'info');
                html += `
                                            <div class="card mb-3 border-${priorityClass}">
                                                <div class="card-header bg-${priorityClass} bg-opacity-10">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">${init.title}</h6>
                                                        <span class="badge bg-${priorityClass}">${init.priority}</span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted">${init.description}</p>
                                                    ${init.strategies ? `
                                                    <div class="mb-3">
                                                        <strong>Strategies:</strong>
                                                        <ul class="mt-1">
                                                            ${init.strategies.map(s => `<li>${s}</li>`).join('')}
                                                        </ul>
                                                    </div>
                                                    ` : ''}
                                                    ${init.target ? `<p class="mb-1"><strong><i class="bi bi-bullseye me-1"></i>Target:</strong> ${init.target}</p>` : ''}
                                                    ${init.timeline ? `<p class="mb-0"><strong><i class="bi bi-calendar me-1"></i>Timeline:</strong> ${init.timeline}</p>` : ''}
                                                </div>
                                            </div>
                                        `;
            });

            container.innerHTML = html;
        }

        // Render Employer Engagement Actions
        function renderEmployerActions(actions) {
            const container = document.getElementById('employerActionsContainer');

            if (!actions || actions.length === 0) {
                container.innerHTML = '<p class="text-muted">No employer engagement actions needed at this time.</p>';
                return;
            }

            let html = '';
            actions.forEach(action => {
                const priorityClass = action.priority === 'high' ? 'warning' : 'info';
                html += `
                                            <div class="alert alert-${priorityClass} mb-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="alert-heading">${action.title}</h6>
                                                        <p class="mb-2">${action.description}</p>
                                                        ${action.target_count ? `<span class="badge bg-dark">Target: ${action.target_count} employers</span>` : ''}
                                                    </div>
                                                    <span class="badge bg-${priorityClass}">${action.type}</span>
                                                </div>
                                                ${action.action_steps ? `
                                                <hr>
                                                <strong>Action Steps:</strong>
                                                <ol class="mb-0 mt-1">
                                                    ${action.action_steps.map(step => `<li>${step}</li>`).join('')}
                                                </ol>
                                                ` : ''}
                                            </div>
                                        `;
            });

            container.innerHTML = html;
        }

        // Render Jobseeker Support Actions
        function renderJobseekerActions(actions) {
            const container = document.getElementById('jobseekerActionsContainer');

            if (!actions || actions.length === 0) {
                container.innerHTML = '<p class="text-muted">No jobseeker support actions needed at this time.</p>';
                return;
            }

            let html = '';
            actions.forEach(action => {
                const priorityClass = action.priority === 'high' ? 'danger' : (action.priority === 'medium' ? 'warning' : 'info');
                html += `
                                            <div class="card mb-3">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <strong>${action.title}</strong>
                                                    <span class="badge bg-${priorityClass}">${action.priority}</span>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted">${action.description}</p>
                                                    ${action.count ? `<p><span class="badge bg-primary">${action.count} jobseekers affected</span></p>` : ''}
                                                    ${action.action_steps ? `
                                                    <strong>Action Steps:</strong>
                                                    <ul class="mt-1">
                                                        ${action.action_steps.map(step => `<li>${step}</li>`).join('')}
                                                    </ul>
                                                    ` : ''}
                                                    ${action.expected_outcome ? `<p class="mb-0 text-success"><i class="bi bi-check-circle me-1"></i><strong>Expected Outcome:</strong> ${action.expected_outcome}</p>` : ''}
                                                    ${action.common_issues ? `
                                                    <div class="mt-2">
                                                        <strong>Common Issues:</strong>
                                                        <ul class="small mb-0">
                                                            ${action.common_issues.map(issue => `<li>${issue}</li>`).join('')}
                                                        </ul>
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        `;
            });

            container.innerHTML = html;
        }

        // Render Resource Allocation
        function renderResourceAllocation(allocations) {
            const container = document.getElementById('resourceAllocationContainer');

            if (!allocations || allocations.length === 0) {
                container.innerHTML = '<p class="text-muted">No resource allocation data available.</p>';
                return;
            }

            let html = '';
            allocations.forEach(alloc => {
                html += `
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <strong>${alloc.category}</strong>
                                                    <span class="badge bg-primary">${alloc.job_share}%</span>
                                                </div>
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar" style="width: ${alloc.job_share}%"></div>
                                                </div>
                                                <small class="text-muted">${alloc.recommendation}</small>
                                            </div>
                                        `;
            });

            container.innerHTML = html;
        }

        // Render Quick Actions
        function renderQuickActions(data) {
            const container = document.getElementById('quickActionsContainer');

            let criticalCount = 0;
            let highCount = 0;
            let mediumCount = 0;

            // Count priorities
            (data.intervention_priorities || []).forEach(p => {
                if (p.overall_score > 70) criticalCount++;
                else if (p.overall_score > 50) highCount++;
                else mediumCount++;
            });

            (data.skill_gap_actions || []).forEach(a => {
                if (a.priority === 'critical') criticalCount++;
                else if (a.priority === 'high') highCount++;
                else mediumCount++;
            });

            let html = `
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-danger bg-opacity-10 rounded">
                                            <div>
                                                <strong class="text-danger">Critical Actions</strong>
                                                <p class="mb-0 small text-muted">Requires immediate attention</p>
                                            </div>
                                            <span class="badge bg-danger fs-5">${criticalCount}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-warning bg-opacity-10 rounded">
                                            <div>
                                                <strong class="text-warning">High Priority</strong>
                                                <p class="mb-0 small text-muted">Address within 1 week</p>
                                            </div>
                                            <span class="badge bg-warning fs-5">${highCount}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-info bg-opacity-10 rounded">
                                            <div>
                                                <strong class="text-info">Medium Priority</strong>
                                                <p class="mb-0 small text-muted">Plan for this month</p>
                                            </div>
                                            <span class="badge bg-info fs-5">${mediumCount}</span>
                                        </div>
                                    `;

            container.innerHTML = html;
        }

        // Export Prescriptive Report
        function exportPrescriptiveReport() {
            // Route missing
            window.location.href = '#';
        }
    </script>
@endsection