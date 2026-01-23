@extends('layouts.admin')

@section('title', 'K-Means Clustering Visualization')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">K-Means Clustering Algorithm</h1>
            <p class="text-muted mb-0">Azure ML powered job and user clustering visualization</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshClusters()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <a href="{{ route('admin.analytics.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Analytics
            </a>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3 bg-primary bg-opacity-10">
                                <i class="fas fa-cloud text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Algorithm Source</h6>
                            <h4 class="mb-0" id="sourceStatus">
                                @if($healthCheck['accessible'])
                                    <span class="text-success">Azure ML</span>
                                @else
                                    <span class="text-warning">Local Fallback</span>
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3 bg-success bg-opacity-10">
                                <i class="fas fa-briefcase text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Jobs Analyzed</h6>
                            <h4 class="mb-0" id="jobsCount">{{ $stats['total_jobs'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3 bg-info bg-opacity-10">
                                <i class="fas fa-users text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Users Analyzed</h6>
                            <h4 class="mb-0" id="usersCount">{{ $stats['total_jobseekers'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3 bg-warning bg-opacity-10">
                                <i class="fas fa-sitemap text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Clusters</h6>
                            <h4 class="mb-0" id="clustersCount">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Clustering Type</label>
                    <select class="form-select" id="clusterType" onchange="loadClusterData()">
                        <option value="job" selected>Job Clustering</option>
                        <option value="user">User Clustering</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Number of Clusters (K)</label>
                    <select class="form-select" id="kValue" onchange="loadClusterData()">
                        <option value="3">K = 3</option>
                        <option value="4">K = 4</option>
                        <option value="5" selected>K = 5</option>
                        <option value="6">K = 6</option>
                        <option value="7">K = 7</option>
                        <option value="8">K = 8</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Algorithm Status</label>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-{{ $healthCheck['accessible'] ? 'success' : 'warning' }} fs-6">
                            <i class="fas fa-{{ $healthCheck['accessible'] ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
                            {{ $healthCheck['accessible'] ? 'Azure ML Connected' : 'Using Local Fallback' }}
                        </span>
                        <small class="text-muted">{{ $healthCheck['message'] }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Algorithm Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body text-center">
                    <h6 class="text-muted text-uppercase mb-2">Inertia (WCSS)</h6>
                    <h3 class="mb-1" id="inertiaValue">-</h3>
                    <small class="text-muted">Within-Cluster Sum of Squares</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body text-center">
                    <h6 class="text-muted text-uppercase mb-2">Silhouette Score</h6>
                    <h3 class="mb-1" id="silhouetteValue">-</h3>
                    <small class="text-muted">Cluster Quality (-1 to 1)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body text-center">
                    <h6 class="text-muted text-uppercase mb-2">Iterations</h6>
                    <h3 class="mb-1" id="iterationsValue">-</h3>
                    <small class="text-muted">Until Convergence</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body text-center">
                    <h6 class="text-muted text-uppercase mb-2">Samples</h6>
                    <h3 class="mb-1" id="samplesValue">-</h3>
                    <small class="text-muted">Data Points Clustered</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Cluster Distribution Pie Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Cluster Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="clusterPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cluster Sizes Bar Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-success me-2"></i>
                        Cluster Sizes
                    </h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="clusterBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scatter Plot -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-project-diagram text-info me-2"></i>
                        Cluster Centroids Visualization (2D Projection)
                    </h5>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="scatterChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Algorithm Details -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs text-secondary me-2"></i>
                        Algorithm Parameters
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td class="text-muted">Algorithm</td>
                                <td class="fw-semibold">K-Means Clustering</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Implementation</td>
                                <td class="fw-semibold" id="implementationSource">{{ $healthCheck['accessible'] ? 'Azure ML (scikit-learn)' : 'Local Fallback (PHP)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Initialization Method</td>
                                <td class="fw-semibold">{{ $config['clustering']['init_method'] ?? 'k-means++' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Max Iterations</td>
                                <td class="fw-semibold">{{ $config['clustering']['max_iterations'] ?? 100 }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tolerance</td>
                                <td class="fw-semibold">{{ $config['clustering']['tolerance'] ?? 0.0001 }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Scaling</td>
                                <td class="fw-semibold">{{ $config['scaling']['enabled'] ? ucfirst($config['scaling']['method'] ?? 'standard') . ' Scaler' : 'Disabled' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        How K-Means Works
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-simple">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-pill">1</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Initialize</strong>
                                <p class="text-muted mb-0 small">Select K random centroids using k-means++ method</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-pill">2</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Assign</strong>
                                <p class="text-muted mb-0 small">Assign each data point to nearest centroid</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-pill">3</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Update</strong>
                                <p class="text-muted mb-0 small">Recalculate centroid positions</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <span class="badge bg-success rounded-pill">4</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Converge</strong>
                                <p class="text-muted mb-0 small">Repeat until centroids stabilize</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none" style="z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Running K-Means Algorithm...</h5>
            <p class="text-muted">Analyzing data and computing clusters</p>
        </div>
    </div>
</div>
@endsection

@section('customJs')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let pieChart = null;
let barChart = null;
let scatterChart = null;

const colors = [
    '#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#14b8a6', '#f43f5e'
];

document.addEventListener('DOMContentLoaded', function() {
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
            updateDashboard(result.data, result.source);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading cluster data:', error);
        alert('Failed to load clustering data');
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
            updateDashboard(result.data, result.source);
            alert('Clusters refreshed successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error refreshing clusters:', error);
        alert('Failed to refresh clusters');
    } finally {
        hideLoading();
    }
}

function updateDashboard(data, source) {
    // Update metrics
    document.getElementById('clustersCount').textContent = data.metrics.n_clusters;
    document.getElementById('inertiaValue').textContent = data.metrics.inertia.toLocaleString();
    document.getElementById('silhouetteValue').textContent = data.metrics.silhouette_score !== null
        ? data.metrics.silhouette_score.toFixed(4)
        : 'N/A';
    document.getElementById('iterationsValue').textContent = data.metrics.n_iterations || '-';
    document.getElementById('samplesValue').textContent = data.metrics.n_samples.toLocaleString();

    // Update source status
    const isAzureML = source === 'azure_ml';
    document.getElementById('sourceStatus').innerHTML = isAzureML
        ? '<span class="text-success">Azure ML</span>'
        : '<span class="text-warning">Local Fallback</span>';
    document.getElementById('implementationSource').textContent = isAzureML
        ? 'Azure ML (scikit-learn)'
        : 'Local Fallback (PHP)';

    // Update charts
    updatePieChart(data.cluster_sizes, data.cluster_names);
    updateBarChart(data.cluster_sizes, data.cluster_names);
    updateScatterChart(data.scatter_data, data.cluster_names);
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
                borderWidth: 2,
                borderColor: '#fff'
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} items (${percentage}%)`;
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
                label: 'Items in Cluster',
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
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
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
            label: names[idx] || `Cluster ${parseInt(cluster) + 1}`,
            data: clusterMap[cluster],
            backgroundColor: colors[idx % colors.length],
            borderColor: colors[idx % colors.length],
            pointRadius: 12,
            pointHoverRadius: 16
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
                    position: 'bottom',
                    labels: {
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const point = context.raw;
                            return `${context.dataset.label}: (${point.x.toFixed(2)}, ${point.y.toFixed(2)}) - ${point.size} items`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Feature Dimension 1'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Feature Dimension 2'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });
}
</script>
@endsection
