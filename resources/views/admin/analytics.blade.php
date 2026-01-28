@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Analytics Dashboard</h1>
            <p class="text-muted mb-0">Comprehensive insights, statistics, and AI-powered analysis</p>
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

    <!-- Tab Navigation -->
    <ul class="nav nav-pills nav-fill mb-4 analytics-tabs" id="analyticsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-graph-up me-2"></i>Overview & Stats
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="smart-grouping-tab" data-bs-toggle="tab" data-bs-target="#smart-grouping" type="button" role="tab">
                <i class="bi bi-diagram-3 me-2"></i>Smart Grouping (AI)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="companies-tab" data-bs-toggle="tab" data-bs-target="#companies" type="button" role="tab">
                <i class="bi bi-building me-2"></i>Company Analytics
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="analyticsTabContent">

        <!-- ==================== OVERVIEW TAB ==================== -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Total Jobs</h6>
                                <h3 class="stats-value mb-1">{{ number_format($totalJobs) }}</h3>
                                <small class="text-success"><i class="bi bi-check-circle me-1"></i>{{ number_format($activeJobs) }} active</small>
                            </div>
                            <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-briefcase"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Applications</h6>
                                <h3 class="stats-value mb-1">{{ number_format($totalApplications) }}</h3>
                                <small class="text-warning"><i class="bi bi-clock me-1"></i>{{ number_format($pendingApplications) }} pending</small>
                            </div>
                            <div class="stats-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-file-text"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Total Users</h6>
                                <h3 class="stats-value mb-1">{{ number_format($totalUsers) }}</h3>
                                <small class="text-muted"><i class="bi bi-people me-1"></i>All roles</small>
                            </div>
                            <div class="stats-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Pending Jobs</h6>
                                <h3 class="stats-value mb-1 {{ $pendingJobs > 0 ? 'text-warning' : '' }}">{{ number_format($pendingJobs) }}</h3>
                                @if($pendingJobs > 0)
                                    <a href="{{ route('admin.jobs.index', ['status' => '0']) }}" class="text-warning text-decoration-none small">
                                        <i class="bi bi-arrow-right me-1"></i>Review Now
                                    </a>
                                @else
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>All reviewed</small>
                                @endif
                            </div>
                            <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Trends Chart -->
                <div class="col-12 col-xl-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="card-title mb-0">Trends Over Time</h5>
                                    <small class="text-muted">Track growth and activity</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active" data-chart-type="jobs">Jobs</button>
                                    <button type="button" class="btn btn-outline-secondary" data-chart-type="applications">Applications</button>
                                    <button type="button" class="btn btn-outline-secondary" data-chart-type="users">Users</button>
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
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Job Status Distribution</h5>
                            <small class="text-muted">Current job statuses</small>
                        </div>
                        <div class="card-body">
                            <canvas id="jobStatusChart" height="250"></canvas>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><span class="status-dot bg-success"></span>Approved</span>
                                    <strong>{{ number_format($approvedJobs) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><span class="status-dot bg-warning"></span>Pending</span>
                                    <strong>{{ number_format($pendingJobs) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><span class="status-dot bg-danger"></span>Rejected</span>
                                    <strong>{{ number_format($rejectedJobs) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="row g-4">
                <!-- Application Status -->
                <div class="col-12 col-xl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Application Status</h5>
                            <small class="text-muted">Current application statuses</small>
                        </div>
                        <div class="card-body">
                            <canvas id="applicationStatusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Categories -->
                <div class="col-12 col-xl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top Job Categories</h5>
                            <small class="text-muted">Most popular categories</small>
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
        </div>

        <!-- ==================== SMART GROUPING TAB ==================== -->
        <div class="tab-pane fade" id="smart-grouping" role="tabpanel">
            <!-- What is Smart Grouping Explainer -->
            <div class="card border-0 shadow-sm mb-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h4 class="mb-2" style="color: #fff !important;">
                                <i class="bi bi-magic me-2"></i>Smart Grouping (K-Means Clustering)
                            </h4>
                            <p class="mb-0" style="color: rgba(255,255,255,0.9) !important;">
                                Our AI automatically organizes jobs and users into similar groups to help you understand patterns and make better decisions.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-light btn-lg" onclick="refreshClusters()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Run Analysis
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works Explainer -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10" style="width: 60px; height: 60px;">
                                <i class="bi bi-lightbulb text-primary fs-4"></i>
                            </div>
                            <h6 class="fw-bold mb-2">What is This?</h6>
                            <p class="text-muted small mb-0">
                                Think of sorting clothes into piles - similar items go together. We group similar jobs (by salary, type, location) and users (by skills) automatically.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width: 60px; height: 60px;">
                                <i class="bi bi-bullseye text-success fs-4"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Why is it Useful?</h6>
                            <p class="text-muted small mb-0">
                                <strong>Find patterns</strong> in job postings<br>
                                <strong>Match better</strong> job seekers to jobs<br>
                                <strong>Identify gaps</strong> in the market
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-info bg-opacity-10" style="width: 60px; height: 60px;">
                                <i class="bi bi-bar-chart text-info fs-4"></i>
                            </div>
                            <h6 class="fw-bold mb-2">How to Read Charts</h6>
                            <p class="text-muted small mb-0">
                                <strong>Pie chart:</strong> % in each group<br>
                                <strong>Bar chart:</strong> Count per group<br>
                                <strong>Bigger = More common</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analysis Controls -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-sliders text-primary me-2"></i>Analysis Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>What to Analyze</label>
                            <select class="form-select form-select-lg" id="clusterType" onchange="loadClusterData()">
                                <option value="job" selected>Jobs - Group similar job postings</option>
                                <option value="user">Users - Group similar job seekers</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="bi bi-grid-3x3-gap me-1"></i>Number of Groups</label>
                            <select class="form-select form-select-lg" id="kValue" onchange="loadClusterData()">
                                <option value="3">3 Groups - Broad categories</option>
                                <option value="4">4 Groups - Moderate detail</option>
                                <option value="5" selected>5 Groups - Balanced (Recommended)</option>
                                <option value="6">6 Groups - More detail</option>
                                <option value="7">7 Groups - Fine-grained</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-light border mb-0 py-2">
                                <small><i class="bi bi-info-circle text-primary me-1"></i><strong>Tip:</strong> Start with 5 groups. Adjust if too broad or specific.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats for K-Means -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body py-3">
                            <h3 class="mb-1 text-primary" id="kmeans-items">-</h3>
                            <small class="text-muted">Items Analyzed</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body py-3">
                            <h3 class="mb-1 text-success" id="kmeans-groups">-</h3>
                            <small class="text-muted">Groups Found</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body py-3">
                            <span class="badge bg-primary mb-1 fs-6" id="kmeans-quality">-</span>
                            <small class="text-muted d-block">Quality Score</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body py-3">
                            <span class="badge bg-secondary mb-1 fs-6" id="kmeans-engine">Local</span>
                            <small class="text-muted d-block">Analysis Engine</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Insights -->
            <div class="card border-0 shadow-sm mb-4" id="insightsCard" style="display: none;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-stars text-warning me-2"></i>Key Insights</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="insightsContent"></div>
                </div>
            </div>

            <!-- K-Means Charts -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Group Distribution</h5>
                            <small class="text-muted">Percentage of items in each group</small>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px;">
                                <canvas id="clusterPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0"><i class="bi bi-bar-chart text-success me-2"></i>Group Sizes</h5>
                            <small class="text-muted">Number of items in each group</small>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px;">
                                <canvas id="clusterBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Details -->
            <div class="card border-0 shadow-sm" id="groupDetailsCard" style="display: none;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-list-ul text-info me-2"></i>Group Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="groupDetailsContent"></div>
                </div>
            </div>
        </div>

        <!-- ==================== COMPANIES TAB ==================== -->
        <div class="tab-pane fade" id="companies" role="tabpanel">
            <!-- Company Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Total Companies</h6>
                                <h3 class="stats-value mb-1">{{ number_format($totalCompanies) }}</h3>
                                <small class="text-muted"><i class="bi bi-building me-1"></i>Employers</small>
                            </div>
                            <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Active Companies</h6>
                                <h3 class="stats-value mb-1 text-success">{{ number_format($activeCompanies) }}</h3>
                                <small class="text-muted"><i class="bi bi-check-circle me-1"></i>Posted jobs</small>
                            </div>
                            <div class="stats-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Inactive Companies</h6>
                                <h3 class="stats-value mb-1 text-warning">{{ number_format($inactiveCompanies) }}</h3>
                                <small class="text-muted"><i class="bi bi-x-circle me-1"></i>No jobs yet</small>
                            </div>
                            <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="stats-label text-muted mb-1">Verified Companies</h6>
                                <h3 class="stats-value mb-1 text-info">{{ number_format($verifiedCompanies) }}</h3>
                                <small class="text-muted"><i class="bi bi-patch-check me-1"></i>Email verified</small>
                            </div>
                            <div class="stats-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-patch-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Charts Row -->
            <div class="row g-4">
                <!-- Company Activity Status -->
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Company Activity Status</h5>
                            <small class="text-muted">Active vs Inactive</small>
                        </div>
                        <div class="card-body">
                            <canvas id="companyActivityChart" height="250"></canvas>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><span class="status-dot bg-success"></span>Active</span>
                                    <strong>{{ number_format($activeCompanies) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><span class="status-dot bg-warning"></span>Inactive</span>
                                    <strong>{{ number_format($inactiveCompanies) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><span class="status-dot bg-secondary"></span>Unverified</span>
                                    <strong>{{ number_format($unverifiedCompanies) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Companies by Jobs -->
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top Companies by Jobs</h5>
                            <small class="text-muted">Most job postings</small>
                        </div>
                        <div class="card-body">
                            @if($topCompaniesByJobs->count() > 0)
                                <div class="company-list">
                                    @foreach($topCompaniesByJobs as $index => $company)
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                    <span class="fw-semibold text-truncate" style="max-width: 150px;" title="{{ $company->name }}">
                                                        {{ $company->name }}
                                                    </span>
                                                </div>
                                                <span class="badge bg-success">{{ number_format($company->jobs_count) }}</span>
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
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top Companies by Applications</h5>
                            <small class="text-muted">Most applications received</small>
                        </div>
                        <div class="card-body">
                            @if($topCompaniesByApplications->count() > 0)
                                <div class="company-list">
                                    @foreach($topCompaniesByApplications as $index => $company)
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                    <span class="fw-semibold text-truncate" style="max-width: 150px;" title="{{ $company->name }}">
                                                        {{ $company->name }}
                                                    </span>
                                                </div>
                                                <span class="badge bg-info">{{ number_format($company->applications_count) }}</span>
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
    </div>
</div>

<!-- Loading Overlay for K-Means -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-none" style="z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem;" role="status"></div>
            <h4 class="text-primary">Analyzing Your Data...</h4>
            <p class="text-muted">Finding patterns and creating groups</p>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Stats Card Styling */
.stats-card {
    background: var(--admin-card-bg, #fff);
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid var(--admin-border-color, #e5e7eb);
    transition: all 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stats-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-value {
    font-size: 1.75rem;
    font-weight: 700;
}

.stats-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

/* Tab Styling */
.analytics-tabs .nav-link {
    color: var(--admin-text-main, #374151);
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s;
}

.analytics-tabs .nav-link:hover {
    background: var(--admin-menu-hover-bg, #f3f4f6);
}

.analytics-tabs .nav-link.active {
    background: #6366f1;
    color: white;
}

/* Card Styling */
.card {
    border: 1px solid var(--admin-border-color, #e5e7eb);
    border-radius: 12px;
}

.card-header {
    background-color: var(--admin-card-bg, #fff);
    border-bottom: 1px solid var(--admin-border-color, #e5e7eb);
    border-radius: 12px 12px 0 0 !important;
    padding: 1rem 1.25rem;
}

/* Status Dot */
.status-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 8px;
}

/* Company List */
.company-list {
    max-height: 350px;
    overflow-y: auto;
}

/* Dark Mode Adjustments */
[data-theme="dark"] .stats-icon {
    opacity: 0.9;
}

[data-theme="dark"] .bg-opacity-10 {
    --bs-bg-opacity: 0.15 !important;
}

[data-theme="dark"] .analytics-tabs .nav-link {
    color: #e5e7eb;
}

[data-theme="dark"] .analytics-tabs .nav-link:hover {
    background: #374151;
}

[data-theme="dark"] .bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

[data-theme="dark"] #loadingOverlay {
    background: rgba(17, 24, 39, 0.95) !important;
}

[data-theme="dark"] #loadingOverlay h4 {
    color: #818cf8 !important;
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

    // ==================== OVERVIEW CHARTS ====================

    // Initialize Job Status Chart
    const jobStatusCtx = document.getElementById('jobStatusChart').getContext('2d');
    new Chart(jobStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [{{ $approvedJobs }}, {{ $pendingJobs }}, {{ $rejectedJobs }}],
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            plugins: { legend: { display: false } }
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
                backgroundColor: ['#f59e0b', '#22c55e', '#ef4444'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
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
                backgroundColor: ['#22c55e', '#f59e0b', '#6b7280'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            plugins: { legend: { display: false } }
        }
    });

    // Load trends chart
    function loadTrendsChart(type, days) {
        fetch(`{{ route('admin.analytics.dashboard') }}?type=${type}&days=${days}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (trendsChart) trendsChart.destroy();
            const ctx = document.getElementById('trendsChart').getContext('2d');
            trendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: type.charAt(0).toUpperCase() + type.slice(1),
                        data: data.values,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
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

    // ==================== K-MEANS CHARTS ====================

    let pieChart = null;
    let barChart = null;
    const colors = ['#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];

    // Load K-Means when tab is shown
    document.getElementById('smart-grouping-tab').addEventListener('shown.bs.tab', function() {
        if (!pieChart) loadClusterData();
    });

    window.loadClusterData = async function() {
        const type = document.getElementById('clusterType').value;
        const k = document.getElementById('kValue').value;
        showLoading();

        try {
            const response = await fetch(`{{ route('admin.analytics.kmeans.data') }}?type=${type}&k=${k}`);
            const result = await response.json();
            if (result.success) {
                updateKMeansDashboard(result.data, result.source, type);
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load data');
        } finally {
            hideLoading();
        }
    }

    window.refreshClusters = async function() {
        const type = document.getElementById('clusterType').value;
        const k = document.getElementById('kValue').value;
        showLoading();

        try {
            const response = await fetch(`{{ route('admin.analytics.kmeans.refresh') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ type, k })
            });
            const result = await response.json();
            if (result.success) {
                updateKMeansDashboard(result.data, result.source, type);
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            hideLoading();
        }
    }

    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('d-none');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('d-none');
    }

    function updateKMeansDashboard(data, source, type) {
        // Update quick stats
        document.getElementById('kmeans-items').textContent = data.metrics.n_samples.toLocaleString();
        document.getElementById('kmeans-groups').textContent = data.metrics.n_clusters;

        // Quality badge
        const score = data.metrics.silhouette_score;
        const qualityBadge = document.getElementById('kmeans-quality');
        if (score !== null) {
            let label = score >= 0.5 ? 'Excellent' : score >= 0.25 ? 'Good' : score >= 0 ? 'Fair' : 'Poor';
            let colorClass = score >= 0.5 ? 'bg-success' : score >= 0.25 ? 'bg-primary' : score >= 0 ? 'bg-warning' : 'bg-danger';
            qualityBadge.textContent = label;
            qualityBadge.className = 'badge fs-6 ' + colorClass;
        }

        // Engine badge
        document.getElementById('kmeans-engine').textContent = source === 'azure_ml' ? 'Cloud AI' : 'Local';

        // Update charts
        updateClusterCharts(data.cluster_sizes, data.cluster_names);

        // Update insights
        updateInsights(data, type);

        // Update group details
        updateGroupDetails(data.cluster_sizes, data.cluster_names, type);
    }

    function updateClusterCharts(sizes, names) {
        const pieCtx = document.getElementById('clusterPieChart').getContext('2d');
        const barCtx = document.getElementById('clusterBarChart').getContext('2d');

        if (pieChart) pieChart.destroy();
        if (barChart) barChart.destroy();

        pieChart = new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: names,
                datasets: [{ data: sizes, backgroundColor: colors.slice(0, sizes.length), borderWidth: 3, borderColor: '#fff' }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                return `${ctx.raw.toLocaleString()} (${((ctx.raw / total) * 100).toFixed(1)}%)`;
                            }
                        }
                    }
                }
            }
        });

        barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: names,
                datasets: [{ data: sizes, backgroundColor: colors.slice(0, sizes.length), borderRadius: 8 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } },
                    x: { ticks: { font: { size: 10 }, maxRotation: 45 } }
                }
            }
        });
    }

    function updateInsights(data, type) {
        const sizes = data.cluster_sizes;
        const names = data.cluster_names;
        const total = sizes.reduce((a, b) => a + b, 0);
        const maxIdx = sizes.indexOf(Math.max(...sizes));
        const minIdx = sizes.indexOf(Math.min(...sizes));
        const typeLabel = type === 'job' ? 'jobs' : 'users';

        document.getElementById('insightsContent').innerHTML = `
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3 bg-primary bg-opacity-10">
                    <h6 class="text-primary mb-1"><i class="bi bi-trophy me-1"></i>Largest Group</h6>
                    <strong>${names[maxIdx]}</strong>
                    <div class="text-muted small">${sizes[maxIdx].toLocaleString()} ${typeLabel} (${((sizes[maxIdx]/total)*100).toFixed(1)}%)</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3 bg-warning bg-opacity-10">
                    <h6 class="text-warning mb-1"><i class="bi bi-arrow-down-circle me-1"></i>Smallest Group</h6>
                    <strong>${names[minIdx]}</strong>
                    <div class="text-muted small">${sizes[minIdx].toLocaleString()} ${typeLabel} (${((sizes[minIdx]/total)*100).toFixed(1)}%)</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3 bg-info bg-opacity-10">
                    <h6 class="text-info mb-1"><i class="bi bi-pie-chart me-1"></i>Total Analyzed</h6>
                    <strong>${total.toLocaleString()} ${typeLabel}</strong>
                    <div class="text-muted small">Across ${sizes.length} groups</div>
                </div>
            </div>
        `;
        document.getElementById('insightsCard').style.display = 'block';
    }

    function updateGroupDetails(sizes, names, type) {
        const total = sizes.reduce((a, b) => a + b, 0);
        const typeLabel = type === 'job' ? 'jobs' : 'users';
        let html = '';

        names.forEach((name, i) => {
            const pct = ((sizes[i] / total) * 100).toFixed(1);
            html += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="border rounded p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded-circle me-2" style="width:12px;height:12px;background:${colors[i]}"></div>
                            <strong>${name}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">${sizes[i].toLocaleString()} ${typeLabel}</span>
                            <span class="badge" style="background:${colors[i]}">${pct}%</span>
                        </div>
                        <div class="progress mt-2" style="height:6px">
                            <div class="progress-bar" style="width:${pct}%;background:${colors[i]}"></div>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById('groupDetailsContent').innerHTML = html;
        document.getElementById('groupDetailsCard').style.display = 'block';
    }
});
</script>
@endpush
@endsection
