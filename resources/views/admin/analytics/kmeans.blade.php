@extends('layouts.admin')

@section('title', 'Job & User Insights - Smart Grouping')

@section('content')
<div class="container-fluid py-4">
    <!-- Friendly Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h1 class="h2 mb-2" style="color: #ffffff !important; font-weight: 700;">
                                <i class="fas fa-magic me-2" style="color: #ffffff !important;"></i>
                                Smart Job & User Grouping
                            </h1>
                            <p class="mb-0" style="color: rgba(255,255,255,0.9) !important; font-size: 1.1rem;">
                                Our AI automatically organizes jobs and users into similar groups to help you understand patterns and make better decisions.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-light btn-lg" onclick="refreshClusters()" style="font-weight: 600;">
                                <i class="fas fa-sync-alt me-2"></i> Refresh Analysis
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- What is This? Explainer -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="d-flex align-items-start">
                                <div class="rounded-circle p-3 bg-primary bg-opacity-10 me-3">
                                    <i class="fas fa-lightbulb text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">What is Smart Grouping?</h6>
                                    <p class="text-muted mb-0 small">
                                        Think of it like sorting clothes into piles - similar items go together.
                                        We group similar jobs (by salary, type, location) and similar users (by skills, preferences) automatically.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 border-end">
                            <div class="d-flex align-items-start">
                                <div class="rounded-circle p-3 bg-success bg-opacity-10 me-3">
                                    <i class="fas fa-target text-success fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Why is this Useful?</h6>
                                    <p class="text-muted mb-0 small">
                                        <strong>Find patterns:</strong> See what types of jobs are most common<br>
                                        <strong>Match better:</strong> Connect job seekers with similar job groups<br>
                                        <strong>Plan ahead:</strong> Identify gaps in job postings
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="rounded-circle p-3 bg-info bg-opacity-10 me-3">
                                    <i class="fas fa-chart-line text-info fa-lg"></i>
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
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10" style="width: 60px; height: 60px;">
                        <i class="fas fa-briefcase text-primary fa-lg"></i>
                    </div>
                    <h3 class="mb-1" id="jobsCount">{{ $stats['total_jobs'] }}</h3>
                    <p class="text-muted mb-0">Active Jobs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width: 60px; height: 60px;">
                        <i class="fas fa-users text-success fa-lg"></i>
                    </div>
                    <h3 class="mb-1" id="usersCount">{{ $stats['total_jobseekers'] }}</h3>
                    <p class="text-muted mb-0">Job Seekers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10" style="width: 60px; height: 60px;">
                        <i class="fas fa-layer-group text-warning fa-lg"></i>
                    </div>
                    <h3 class="mb-1" id="clustersCount">-</h3>
                    <p class="text-muted mb-0">Groups Found</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-info bg-opacity-10" style="width: 60px; height: 60px;">
                        <i class="fas fa-{{ $healthCheck['accessible'] ? 'cloud' : 'laptop' }} text-info fa-lg"></i>
                    </div>
                    <span class="badge bg-{{ $healthCheck['accessible'] ? 'success' : 'warning' }} mb-2">
                        {{ $healthCheck['accessible'] ? 'Cloud AI' : 'Local Analysis' }}
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
                <i class="fas fa-sliders-h text-primary me-2"></i>
                Analysis Settings
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1"></i> What to Analyze
                    </label>
                    <select class="form-select form-select-lg" id="clusterType" onchange="loadClusterData()">
                        <option value="job" selected>📋 Jobs - Group similar job postings</option>
                        <option value="user">👥 Users - Group similar job seekers</option>
                    </select>
                    <small class="text-muted">Choose whether to analyze jobs or users</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-th-large me-1"></i> Number of Groups
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
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            <div>
                                <strong>Tip:</strong> Start with 5 groups. If groups seem too broad, try 6-7. If too specific, try 3-4.
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
                <i class="fas fa-star text-warning me-2"></i>
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
                            <i class="fas fa-chart-pie text-primary me-2"></i>
                            Group Distribution
                        </h5>
                        <span class="badge bg-light text-dark" data-bs-toggle="tooltip" title="Shows what percentage of items belong to each group">
                            <i class="fas fa-question-circle"></i> What's this?
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
                        This chart shows how items are spread across different groups. Hover over a section to see details.
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
                            <i class="fas fa-chart-bar text-success me-2"></i>
                            Group Sizes
                        </h5>
                        <span class="badge bg-light text-dark" data-bs-toggle="tooltip" title="Shows the exact count of items in each group">
                            <i class="fas fa-question-circle"></i> What's this?
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
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
                <i class="fas fa-list-alt text-info me-2"></i>
                Group Details
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle me-1"></i>
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
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>
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
                                    <div class="progress-bar bg-primary" role="progressbar" id="qualityBar" style="width: 0%;">
                                        <span id="qualityText">Calculating...</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
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
            <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSection">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cog text-secondary me-2"></i>
                        Technical Details (For Advanced Users)
                    </h5>
                    <i class="fas fa-chevron-down text-muted"></i>
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
                                    <td class="fw-semibold" id="implementationSource">{{ $healthCheck['accessible'] ? 'Azure ML (Cloud)' : 'Local PHP' }}</td>
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

    <!-- Help Section -->
    <div class="card border-0 shadow-sm bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-question-circle text-primary fa-2x"></i>
                </div>
                <div class="col">
                    <h6 class="mb-1">Need Help Understanding This?</h6>
                    <p class="text-muted mb-0 small">
                        The grouping algorithm looks at various characteristics (like salary, job type, location, required skills) and finds items that are similar.
                        Groups with more items are more common patterns in your data. Use this to identify trends and opportunities!
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.analytics.dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-none" style="z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="text-primary">Analyzing Your Data...</h4>
            <p class="text-muted">Our AI is finding patterns and creating groups</p>
            <div class="progress mx-auto" style="width: 200px; height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    loadClusterData();
});

function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('d-none');
}

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
            showError('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading cluster data:', error);
        showError('Failed to load data. Please try refreshing the page.');
    } finally {
        hideLoading();
    }
}

async function refreshClusters() {
    const type = document.getElementById('clusterType').value;
    const k = document.getElementById('kValue').value;

    showLoading();

    try {
        const response = await fetch(`{{ route('admin.analytics.kmeans.refresh') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type, k })
        });
        const result = await response.json();

        if (result.success) {
            updateDashboard(result.data, result.source, type);
            showSuccess('Analysis refreshed with latest data!');
        } else {
            showError('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error refreshing clusters:', error);
        showError('Failed to refresh. Please try again.');
    } finally {
        hideLoading();
    }
}

function showError(message) {
    // Simple alert for now, could be replaced with toast
    alert(message);
}

function showSuccess(message) {
    alert(message);
}

function updateDashboard(data, source, type) {
    // Update basic counts
    document.getElementById('clustersCount').textContent = data.metrics.n_clusters;
    document.getElementById('clustersCountAlt').textContent = data.metrics.n_clusters;
    document.getElementById('samplesValue').textContent = data.metrics.n_samples.toLocaleString();
    document.getElementById('iterationsValue').textContent = data.metrics.n_iterations || '-';

    // Update technical metrics
    document.getElementById('inertiaValue').textContent = data.metrics.inertia ? data.metrics.inertia.toLocaleString() : '-';
    document.getElementById('silhouetteValue').textContent = data.metrics.silhouette_score !== null
        ? data.metrics.silhouette_score.toFixed(4)
        : 'N/A';

    // Update source status
    const isAzureML = source === 'azure_ml';
    document.getElementById('implementationSource').textContent = isAzureML
        ? 'Azure ML (Cloud)'
        : 'Local PHP Analysis';

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

    if (silhouetteScore === null || silhouetteScore === undefined) {
        qualityBar.style.width = '50%';
        qualityText.textContent = 'Calculating...';
        qualityBadge.textContent = 'N/A';
        qualityBadge.className = 'badge bg-secondary fs-6';
        return;
    }

    // Silhouette score ranges from -1 to 1
    // Convert to percentage (0-100)
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
                    <i class="fas fa-trophy text-primary me-2"></i>
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
                    <i class="fas fa-seedling text-warning me-2"></i>
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
                    <i class="fas fa-balance-scale text-info me-2"></i>
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

    // Calculate standard deviation
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
                        label: function(context) {
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
                        label: function(context) {
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
                        callback: function(value) {
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
        if (!clusterMap[point.cluster]) {
            clusterMap[point.cluster] = [];
        }
        clusterMap[point.cluster].push({ x: point.x, y: point.y, size: point.size });
    });

    Object.keys(clusterMap).forEach((cluster, idx) => {
        datasets.push({
            label: names[idx] || `Group ${parseInt(cluster) + 1}`,
            data: clusterMap[cluster],
            backgroundColor: colors[idx % colors.length],
            borderColor: colors[idx % colors.length],
            pointRadius: 10,
            pointHoverRadius: 14
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
                        label: function(context) {
                            const point = context.raw;
                            return `${context.dataset.label}: ${point.size} items`;
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
</script>
@endsection
