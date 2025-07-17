@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Admin Dashboard Navigation Bar -->
    <div class="admin-dashboard-navbar mb-4">
        <div class="d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <h4 class="mb-0 me-4">Admin Dashboard</h4>
                <div class="dashboard-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
            
            <div class="admin-actions d-flex align-items-center gap-3">
                <!-- System Status -->
                <div class="system-status">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-success me-2"></div>
                        <small class="text-muted">System Online</small>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="btn-group">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-people me-1"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.kyc.index') }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-shield-check me-1"></i> KYC Queue
                        @if(isset($pendingKyc) && $pendingKyc > 0)
                            <span class="badge bg-danger ms-1">{{ $pendingKyc }}</span>
                        @endif
                    </a>
                </div>
                
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">5</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">System Alerts</li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>{{ $pendingKyc ?? 0 }} KYC pending review</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-flag me-2 text-danger"></i>3 jobs flagged for review</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person-plus me-2 text-info"></i>{{ $totalUsers ?? 0 }} new users today</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Quick Navigation Tabs -->
        <div class="admin-tabs">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people me-1"></i> Users
                        @if(isset($totalUsers) && $totalUsers > 0)
                            <span class="badge bg-primary ms-1">{{ number_format($totalUsers) }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.jobs.index') }}">
                        <i class="bi bi-briefcase me-1"></i> Jobs
                        @if(isset($activeJobs) && $activeJobs > 0)
                            <span class="badge bg-success ms-1">{{ number_format($activeJobs) }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.kyc.index') }}">
                        <i class="bi bi-shield-check me-1"></i> KYC
                        @if(isset($pendingKyc) && $pendingKyc > 0)
                            <span class="badge bg-warning ms-1">{{ $pendingKyc }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.categories.index') }}">
                        <i class="bi bi-tags me-1"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.analytics.dashboard') }}">
                        <i class="bi bi-graph-up me-1"></i> Analytics
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                        <i class="bi bi-gear me-1"></i> System
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-sliders me-2"></i> Settings
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings.security-log') }}">
                            <i class="bi bi-shield-lock me-2"></i> Security Log
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings.audit-log') }}">
                            <i class="bi bi-clipboard-data me-2"></i> Audit Log
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Dashboard</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary">
                <i class="bi bi-download me-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2">Total Users</h6>
                        <h2 class="mb-0">{{ number_format($totalUsers) }}</h2>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> {{ $userGrowth }}% from last month
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
                        <h6 class="text-muted mb-2">Active Jobs</h6>
                        <h2 class="mb-0">{{ number_format($activeJobs) }}</h2>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> {{ $jobGrowth }}% from last month
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
                        <h6 class="text-muted mb-2">Pending KYC</h6>
                        <h2 class="mb-0">{{ $pendingKyc }}</h2>
                        <div class="small text-{{ $kycChange >= 0 ? 'success' : 'danger' }}">
                            <i class="bi bi-arrow-{{ $kycChange >= 0 ? 'up' : 'down' }}"></i> 
                            {{ abs($kycChange) }} from last month
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <div class="bg-light rounded-circle p-3">
                            <i class="bi bi-shield-check text-primary"></i>
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
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> {{ $applicationGrowth }}% from last month
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
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Registration Chart -->
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">New User Registrations</h5>
                    <div class="small text-muted">User registration trends over time</div>
                </div>
                <div class="card-body">
                    <canvas id="registrationChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- User Types Chart -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Distribution</h5>
                    <div class="small text-muted">By role type</div>
                </div>
                <div class="card-body">
                    <canvas id="userTypesChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Admin Dashboard Navbar Styles */
.admin-dashboard-navbar {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    padding: 0 1.5rem;
}

.dashboard-breadcrumb .breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
}

.dashboard-breadcrumb .breadcrumb-item a {
    color: #6b7280;
    text-decoration: none;
}

.dashboard-breadcrumb .breadcrumb-item.active {
    color: #1f2937;
}

.system-status .status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.admin-tabs {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.admin-tabs .nav-pills .nav-link {
    color: #1f2937;
    background: none;
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    margin-right: 0.5rem;
    transition: all 0.2s ease;
    font-weight: 500;
}

.admin-tabs .nav-pills .nav-link:hover {
    background-color: #f3f4f6;
    color: #2563eb;
}

.admin-tabs .nav-pills .nav-link.active {
    background-color: #2563eb;
    color: white;
}

.admin-tabs .nav-pills .nav-link .badge {
    font-size: 0.75rem;
}

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

@media (max-width: 768px) {
    .admin-dashboard-navbar .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .admin-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .admin-tabs {
        overflow-x: auto;
    }
    
    .admin-tabs .nav {
        flex-nowrap;
        min-width: max-content;
    }
}
</style>
@endpush

@push('scripts')
<script>
    // Registration Chart
    const registrationCtx = document.getElementById('registrationChart').getContext('2d');
    new Chart(registrationCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($registrationData->pluck('day')) !!},
            datasets: [{
                label: 'New Registrations',
                data: {!! json_encode($registrationData->pluck('count')) !!},
                backgroundColor: '#0dcaf0',
                borderColor: '#0dcaf0',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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

    // User Types Chart
    const userTypesCtx = document.getElementById('userTypesChart').getContext('2d');
    new Chart(userTypesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Job Seekers', 'Employers', 'Admins'],
            datasets: [{
                data: {!! json_encode($userTypeData) !!},
                backgroundColor: ['#0d6efd', '#198754', '#ffc107'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection
