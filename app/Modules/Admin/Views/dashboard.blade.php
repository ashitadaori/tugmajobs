@extends('layouts.admin')

@section('content')
<div class="container-fluid">
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
