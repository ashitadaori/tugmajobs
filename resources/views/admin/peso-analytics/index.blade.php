@extends('layouts.admin')

@section('page_title', 'Analytics Dashboard')

@section('styles')
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
    <style>
        .analytics-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 0;
            transition: all 0.3s ease;
            height: 100%;
        }

        .analytics-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .analytics-card-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .analytics-card-body {
            padding: 1.25rem;
        }

        /* Map Container */
        #jobsMap,
        #heatMap {
            width: 100%;
            height: 500px;
            border-radius: 0 0 1rem 1rem;
        }

        /* Skills Chart */
        .skill-item {
            margin-bottom: 1.25rem;
        }

        .skill-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .skill-percentage {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Loading State */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 1rem;
        }

        /* Heatmap Legend */
        .heatmap-legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .heatmap-gradient {
            width: 120px;
            height: 8px;
            background: linear-gradient(90deg, #3b82f6 0%, #f59e0b 50%, #ef4444 100%);
            border-radius: 4px;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Tab Content Transitions */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scatter-plot-container {
            position: relative;
            height: 400px;
            padding: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                            class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Analytics Hub</li>
                </ol>
            </nav>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h1 class="h2 fw-bold text-dark mb-1">Labor Market Analytics</h1>
                    <div class="d-flex align-items-center gap-3 text-muted small">
                        <span class="d-flex align-items-center">
                            <i class="bi bi-cpu-fill text-primary me-2"></i> K-Means Analysis Engine
                        </span>
                        <span class="d-none d-sm-inline">•</span>
                        <span class="d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-success me-2"></i> Geographic Insights
                        </span>
                        <span class="d-none d-sm-inline">•</span>
                        <span>Updated: {{ now()->format('H:i') }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-white bg-white border" onclick="refreshAllData()" title="Refresh Statistics">
                            <i class="bi bi-arrow-clockwise text-primary"></i>
                        </button>
                        <button class="btn btn-white bg-white border" onclick="exportReport()"
                            title="Export Analytics Report">
                            <i class="bi bi-download text-success"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm"
                        onclick="clearAnalyticsCache()">
                        <i class="bi bi-trash3"></i>
                        <span class="d-none d-sm-inline">Clear Cache</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Statistics Row -->
        <div class="row g-4 mb-4">
            <!-- Active Jobs -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Active Jobs</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_jobs']) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                            <i class="bi bi-briefcase-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <span class="text-success fw-semibold"><i class="bi bi-graph-up me-1"></i>Approved only</span>
                    </div>
                </div>
            </div>

            <!-- Job Seekers -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Job Seekers</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_jobseekers']) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded-3 text-success">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <span class="text-dark fw-semibold">Total registered</span>
                    </div>
                </div>
            </div>

            <!-- Total Applications -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Total Applications</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_applications']) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 text-warning">
                            <i class="bi bi-file-earmark-text-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <span class="text-dark fw-semibold">Across all jobs</span>
                    </div>
                </div>
            </div>

            <!-- KYC Verified -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">KYC Verified</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['verified_jobseekers']) }}</h3>
                        </div>
                        <div class="bg-purple bg-opacity-10 p-2 rounded-3 text-purple"
                            style="color: #6f42c1; background-color: rgba(111, 66, 193, 0.1);">
                            <i class="bi bi-shield-check-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <span class="text-purple fw-semibold" style="color: #6f42c1;">Trust Level High</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Navigation Tabs -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
            <div class="card-body p-2">
                <div class="nav nav-pills nav-fill flex-nowrap overflow-auto custom-scrollbar gap-2" role="tablist">
                    <button class="nav-link active d-flex align-items-center justify-content-center gap-2 py-3 rounded-3"
                        onclick="switchTab('skills')">
                        <i class="bi bi-bar-chart-fill"></i>
                        <span class="fw-semibold">Skills & Trends</span>
                    </button>
                    <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-3 rounded-3"
                        onclick="switchTab('clusters')">
                        <i class="bi bi-diagram-3-fill"></i>
                        <span class="fw-semibold">K-Means Clusters</span>
                    </button>
                    <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-3 rounded-3"
                        onclick="switchTab('map')">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span class="fw-semibold">Job Map</span>
                    </button>
                    <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-3 rounded-3"
                        onclick="switchTab('heatmap')">
                        <i class="bi bi-map-fill"></i>
                        <span class="fw-semibold">Applicant Density</span>
                    </button>
                    <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-3 rounded-3"
                        onclick="switchTab('jobfair')">
                        <i class="bi bi-calendar-event-fill"></i>
                        <span class="fw-semibold">Job Fair</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Contents -->
        <!-- Skills & Trends Tab (Default Active) -->
        <div id="skills-tab" class="tab-content active">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Top Skills in Demand</h5>
                            <select id="skillsDays" class="form-select form-select-sm" style="width: 140px;"
                                onchange="loadSkillTrends()">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                            </select>
                        </div>
                        <div class="analytics-card-body position-relative">
                            <div id="skillsLoading" class="loading-overlay" style="display: none;">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                            <div id="skillsContainer" class="custom-scrollbar" style="max-height: 400px; overflow-y: auto;">
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
                            <div style="height: 400px;">
                                <canvas id="categoryTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Labor Market Indicators</h5>
                        </div>
                        <div class="analytics-card-body">
                            <div class="row g-4 text-center" id="laborIndicators">
                                <!-- Labor indicators will be rendered here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- K-Means Clusters Tab -->
        <div id="clusters-tab" class="tab-content">
            <!-- Smart Segmentation Analysis -->
            <div class="analytics-card mb-4 bg-light border-0">
                <div class="analytics-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto mb-3 mb-md-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="bi bi-cpu-fill fs-3"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mb-1 fw-bold">Smart Segmentation Analysis</h5>
                            <p class="text-muted small mb-0">Our AI engine groups similar records to reveal deep patterns in
                                the labor market.
                                <a href="javascript:void(0)" class="text-primary text-decoration-none ms-1"
                                    data-bs-toggle="collapse" data-bs-target="#kmeansExplanation">
                                    How it works <i class="bi bi-chevron-down small"></i>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-auto mt-3 mt-md-0 d-flex gap-3">
                            <div>
                                <label class="small text-muted fw-bold d-block mb-1">ANALYSIS TYPE</label>
                                <select class="form-select form-select-sm" id="clusterType" onchange="loadClusterData()">
                                    <option value="job" selected>Jobs Market</option>
                                    <option value="user">Jobseeker Profiles</option>
                                </select>
                            </div>
                            <div>
                                <label class="small text-muted fw-bold d-block mb-1">CLUSTERS (K)</label>
                                <select class="form-select form-select-sm" id="kValue" onchange="loadClusterData()">
                                    @for($i = 2; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ $i == 5 ? 'selected' : '' }}>{{ $i }} Groups</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Explanation (Hidden by default to save space) -->
                    <div class="collapse mt-4" id="kmeansExplanation">
                        <hr>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <h6 class="fw-bold fw-bold mb-2 small"><i class="bi bi-lightbulb text-warning me-2"></i>THE
                                    LOGIC</h6>
                                <p class="text-muted small mb-0">We plot every data point on a multi-dimensional map.
                                    K-Means finds the "gravitational centers" of where most records cluster together,
                                    defining your natural market segments.</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="fw-bold fw-bold mb-2 small"><i class="bi bi-graph-up text-success me-2"></i>THE
                                    AXES</h6>
                                <p class="text-muted small mb-0">For Jobs, we analyze <strong>Salary vs. Demand</strong>.
                                    For Seekers, we look at <strong>Skills Density vs. Experience Level</strong>.</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="fw-bold fw-bold mb-2 small"><i
                                        class="bi bi-check-circle text-primary me-2"></i>THE GOAL</h6>
                                <p class="text-muted small mb-0">Identify underserved segments, over-saturated categories,
                                    and prime opportunities for policy intervention or job fair focus.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Visualization Main Panel -->
                <div class="col-lg-8">
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Cluster Visualization</h5>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary active" onclick="setClusterView('scatter')"
                                    id="scatterViewBtn">
                                    <i class="bi bi-scatter-chart"></i>
                                </button>
                                <button class="btn btn-outline-primary" onclick="setClusterView('bubble')"
                                    id="bubbleViewBtn">
                                    <i class="bi bi-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="analytics-card-body position-relative">
                            <div id="clusterLoading" class="loading-overlay" style="display: none;">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>

                            <div id="scatterPlotView" class="scatter-plot-container">
                                <canvas id="clusterScatterChart"></canvas>
                            </div>

                            <div id="bubbleView" style="display: none; height: 400px;"
                                class="d-flex align-items-center justify-content-center">
                                <div id="clusterContainer" class="cluster-container w-100"></div>
                            </div>

                            <div id="clusterLegend" class="d-flex flex-wrap gap-2 justify-content-center mt-3"></div>
                        </div>
                    </div>

                    <div class="analytics-card mt-4">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Group Metrics & Distribution</h5>
                        </div>
                        <div class="analytics-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="small text-muted text-uppercase">
                                            <th class="ps-4">Segment Name</th>
                                            <th class="text-center">Count</th>
                                            <th class="text-center">Applications</th>
                                            <th class="text-center">Avg Salary</th>
                                            <th class="text-center">Demand</th>
                                            <th class="pe-4">Cohesion (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="clusterMetricsTable" class="small">
                                        <!-- Rendered via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Analysis -->
                <div class="col-lg-4">
                    <!-- Analysis Health -->
                    <div class="analytics-card mb-4">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Analysis Health</h5>
                        </div>
                        <div class="analytics-card-body">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Quality Score</span>
                                    <span id="silhouetteScore" class="badge bg-success rounded-pill">-</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div id="silhouetteFill" class="progress-bar bg-success" style="width: 0%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block" style="font-size: 0.7rem;">Measures how distinct the
                                    groups are from each other.</small>
                            </div>

                            <div class="row text-center g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light">
                                        <small class="text-muted d-block">Inertia</small>
                                        <span id="inertiaScore" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light">
                                        <small class="text-muted d-block">Records</small>
                                        <span id="totalClusterJobs" class="fw-bold">-</span>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div id="clusterInsights" class="custom-scrollbar" style="max-height: 250px; overflow-y: auto;">
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-robot fs-2 d-block mb-2 opacity-25"></i>
                                    AI is generating insights...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details -->
                    <div class="analytics-card">
                        <div class="analytics-card-header py-2 collapsible-trigger collapsed" data-bs-toggle="collapse"
                            data-bs-target="#techDetails">
                            <span class="small fw-bold text-muted text-uppercase">Engine Details</span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse" id="techDetails">
                            <div class="analytics-card-body pt-0">
                                <ul class="list-unstyled small mb-0">
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Algorithm</span>
                                        <span class="fw-semibold">K-Means++</span>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Iterations</span>
                                        <span class="fw-semibold" id="iterationsValue">-</span>
                                    </li>
                                    <li class="d-flex justify-content-between py-2">
                                        <span class="text-muted">Confidence</span>
                                        <span class="fw-semibold text-primary" id="qualityBadge">-</span>
                                    </li>
                                </ul>
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
                            <select id="mapCategory" class="form-select form-select-sm" style="width: 200px;"
                                onchange="loadMapData()">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="analytics-card-body p-0 position-relative">
                            <div id="mapLoading" class="loading-overlay" style="display: none;">
                                <div class="spinner-border text-primary" role="status"></div>
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
                        <div class="analytics-card-body p-0">
                            <div id="barangayStats" class="custom-scrollbar" style="max-height: 500px; overflow-y: auto;">
                                <!-- Barangay stats via JS -->
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
                        <div class="analytics-card-body p-0 position-relative">
                            <div id="heatmapLoading" class="loading-overlay" style="display: none;">
                                <div class="spinner-border text-primary" role="status"></div>
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
                            <div id="densityStats"></div>
                        </div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Top Intensity Areas</h5>
                        </div>
                        <div class="analytics-card-body p-0">
                            <div id="topAreas" class="custom-scrollbar" style="max-height: 300px; overflow-y: auto;"></div>
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
                            <select id="jobfairIndustry" class="form-select form-select-sm" style="width: 200px;"
                                onchange="loadJobFairData()">
                                <option value="">All Industries</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="analytics-card-body">
                            <div style="height: 350px;">
                                <canvas id="industryChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="analytics-card">
                        <div class="analytics-card-header border-0 pb-0">
                            <h5 class="mb-0">Top Participating Employers</h5>
                        </div>
                        <div class="analytics-card-body">
                            <div id="topEmployers"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="analytics-card mb-4">
                        <div class="analytics-card-header bg-primary text-white">
                            <h5 class="mb-0">Planning Summary</h5>
                        </div>
                        <div class="analytics-card-body">
                            <div id="jobfairSummary"></div>
                        </div>
                    </div>

                    <div class="analytics-card mb-4">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Recommended Venues</h5>
                        </div>
                        <div class="analytics-card-body p-0">
                            <div id="recommendedVenues"></div>
                        </div>
                    </div>

                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h5 class="mb-0">Historical Trends</h5>
                        </div>
                        <div class="analytics-card-body">
                            <div style="height: 200px;">
                                <canvas id="monthlyTrendsChart"></canvas>
                            </div>
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

        // K-Means Cluster Analysis
        async function loadClusterData() {
            const type = document.getElementById('clusterType')?.value || 'jobs';
            const k = document.getElementById('kValue')?.value || 5;
            const loading = document.getElementById('clustersLoading');

            if (loading) loading.style.display = 'flex';

            try {
                const response = await fetch(`{{ route('admin.analytics.kmeans') }}?type=${type}&k=${k}`);
                const result = await response.json();

                if (result.success) {
                    renderClusterResults(result.data);
                }
            } catch (error) {
                console.error('Error loading cluster data:', error);
            } finally {
                if (loading) loading.style.display = 'none';
            }
        }

        function renderClusterResults(data) {
            // Update Scatter Chart
            updateClusterScatterChart(data.clusters, data.cluster_names);

            // Update Metrics Table
            const tbody = document.getElementById('clusterMetricsTable');
            if (tbody) {
                let tableHtml = '';
                data.clusters.forEach((cluster, idx) => {
                    const color = colors[idx % colors.length];
                    tableHtml += `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${color}; margin-right: 8px;"></div>
                                            Group ${idx + 1}
                                        </div>
                                    </td>
                                    <td>${cluster.size.toLocaleString()}</td>
                                    <td>${cluster.percentage}%</td>
                                </tr>
                            `;
                });
                tbody.innerHTML = tableHtml;
            }

            // Update Quality metrics
            const metrics = data.quality_metrics;
            if (metrics) {
                const sil = document.getElementById('silhouetteScore');
                const silFill = document.getElementById('silhouetteFill');
                const inertia = document.getElementById('inertiaScore');
                const iter = document.getElementById('iterationsValue');
                const badge = document.getElementById('qualityBadge');

                if (sil) sil.textContent = metrics.silhouette.toFixed(3);
                if (silFill) silFill.style.width = (Math.max(0, metrics.silhouette) * 100) + '%';
                if (inertia) inertia.textContent = metrics.inertia.toLocaleString();
                if (iter) iter.textContent = metrics.iterations;

                if (badge) {
                    if (metrics.silhouette > 0.5) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Excellent';
                    } else if (metrics.silhouette > 0.3) {
                        badge.className = 'badge bg-info';
                        badge.textContent = 'Good';
                    } else {
                        badge.className = 'badge bg-warning';
                        badge.textContent = 'Fair';
                    }
                }
            }

            // Summary Stats
            const totalJobs = document.getElementById('totalClusterJobs');
            if (totalJobs && data.summary_stats) {
                totalJobs.textContent = data.summary_stats.total_items.toLocaleString();
            }

            // Insights
            const insightsBox = document.getElementById('clusterInsights');
            if (insightsBox && data.clusters) {
                let insightsHtml = '';
                data.clusters.slice(0, 3).forEach((cluster, idx) => {
                    const categories = cluster.top_categories || [];
                    insightsHtml += `<div class="mb-2 small">
                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                <strong>Group ${idx + 1}:</strong> ${categories.join(', ') || 'N/A'}
                            </div>`;
                });
                insightsBox.innerHTML = insightsHtml || '<p class="text-muted small">No insights available</p>';
            }
        }

        function updateClusterScatterChart(clusters, names) {
            const ctx = document.getElementById('clusterScatterChart')?.getContext('2d');
            if (!ctx) return;

            if (scatterChart) scatterChart.destroy();

            const datasets = clusters.map((cluster, idx) => ({
                label: names[idx] || `Group ${idx + 1}`,
                data: cluster.points.map(p => ({ x: p.x, y: p.y })),
                backgroundColor: colors[idx % colors.length],
                pointRadius: 6,
                pointHoverRadius: 10,
                borderWidth: 2,
                borderColor: '#fff'
            }));

            scatterChart = new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: { display: false }
                        },
                        y: {
                            display: false,
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        // Tab switching
        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.analytics-tab').forEach(tab => tab.classList.remove('active'));
            const activeTab = document.querySelector(`[onclick="switchTab('${tabName}')"]`);
            if (activeTab) activeTab.classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            const content = document.getElementById(`${tabName}-tab`);
            if (content) content.classList.add('active');

            // Load data for the tab if not loaded
            switch (tabName) {
                case 'clusters':
                    loadClusterData();
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
                case 'prescriptive':
                    loadPrescriptiveData();
                    break;
            }
        }



        // Prescriptive Tab switching
            function switchPrescriptiveTab(subTab) {
                // Update buttons
                document.querySelectorAll('.prescriptive-tab-btn').forEach(btn => btn.classList.remove('active'));
                const activeBtn = document.querySelector(`[onclick="switchPrescriptiveTab('${subTab}')"]`);
                if (activeBtn) activeBtn.classList.add('active');

                // Update content areas
                const sections = [
                    'intervention', 'hiring', 'skills-gap', 'opportunities', 
                    'strategic', 'employer', 'jobseeker', 'allocation', 'quick'
                ];

                sections.forEach(s => {
                    const el = document.getElementById(`${s}Container`);
                    if (el) {
                        // Navigate up to the parent card or container to hide it
                        const card = el.closest('.analytics-card');
                        if (card) {
                            card.style.display = s === subTab ? 'block' : 'none';
                        }
                    }
                });
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