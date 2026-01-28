@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <!-- Admin Dashboard Header -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center">
                    <h1 class="h3 mb-0 me-3">Dashboard Overview</h1>
                    <div class="system-status bg-white border rounded-pill px-3 py-1 d-flex align-items-center shadow-sm">
                        <div class="status-indicator bg-success me-2"></div>
                        <small class="text-success fw-bold">SYSTEM ONLINE</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                            <i class="bi bi-people me-1"></i> Manage Users
                        </a>
                        <a href="{{ route('admin.users.index', ['kyc_status' => 'all']) }}" class="btn btn-info text-white">
                            <i class="bi bi-shield-check me-1"></i> KYC Overview
                        </a>
                    </div>
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('admin.dashboard.export') }}" class="btn btn-success" title="Export Statistics">
                            <i class="bi bi-download"></i>
                        </a>
                        <button type="button" id="refresh-stats" class="btn btn-light border" onclick="refreshDashboard()"
                            title="Refresh Data">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Users -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                    <div id="total-users"
                        class="stats-card stats-card-clickable h-100 p-4 border-0 shadow-sm bg-white rounded-4">
                        <div class="d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                                    <i class="bi bi-people-fill fs-4"></i>
                                </div>
                                <div class="growth-indicator small text-success">
                                    <i class="bi bi-graph-up-arrow me-1"></i>{{ $userGrowth }}%
                                </div>
                            </div>
                            <h6 class="text-muted fw-bold small text-uppercase mb-1">Total Users</h6>
                            <h2 class="mb-0 fw-bold text-dark">{{ number_format($totalUsers) }}</h2>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Active Jobs -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('admin.companies.index') }}" class="text-decoration-none">
                    <div id="active-jobs"
                        class="stats-card stats-card-clickable h-100 p-4 border-0 shadow-sm bg-white rounded-4">
                        <div class="live-indicator"></div>
                        <div class="d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="stats-icon bg-success bg-opacity-10 text-success rounded-3 p-2">
                                    <i class="bi bi-briefcase-fill fs-4"></i>
                                </div>
                                <div class="growth-indicator small text-success">
                                    <i class="bi bi-graph-up-arrow me-1"></i>{{ $jobGrowth }}%
                                </div>
                            </div>
                            <h6 class="text-muted fw-bold small text-uppercase mb-1">Active Jobs</h6>
                            <h2 class="mb-0 fw-bold text-dark">{{ number_format($activeJobs) }}</h2>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Pending Jobs -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('admin.jobs.pending') }}" class="text-decoration-none">
                    <div id="pending-jobs"
                        class="stats-card stats-card-clickable h-100 p-4 border-0 shadow-sm bg-white rounded-4 {{ $pendingJobs > 0 ? 'border-warning border-start border-4' : '' }}">
                        <div class="d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div
                                    class="stats-icon {{ $pendingJobs > 0 ? 'bg-warning text-warning' : 'bg-secondary text-secondary' }} bg-opacity-10 rounded-3 p-2">
                                    <i class="bi bi-clock-history fs-4"></i>
                                </div>
                                @if($pendingJobs > 0)
                                    <span class="badge bg-warning text-dark rounded-pill">{{ $pendingJobs }}</span>
                                @endif
                            </div>
                            <h6 class="text-muted fw-bold small text-uppercase mb-1">Pending Jobs</h6>
                            <h2 class="mb-0 fw-bold {{ $pendingJobs > 0 ? 'text-warning' : 'text-dark' }}">
                                {{ number_format($pendingJobs) }}</h2>
                        </div>
                    </div>
                </a>
            </div>

            <!-- KYC Verified -->
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.kyc.didit-verifications') }}" class="text-decoration-none">
                    <div id="verified-kyc"
                        class="stats-card stats-card-clickable h-100 p-4 border-0 shadow-sm bg-white rounded-4">
                        <div class="d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="stats-icon bg-info bg-opacity-10 text-info rounded-3 p-2">
                                    <i class="bi bi-shield-check-fill fs-4"></i>
                                </div>
                                <div class="small text-muted">
                                    <span class="text-info">{{ $pendingKyc }}</span> pending
                                </div>
                            </div>
                            <h6 class="text-muted fw-bold small text-uppercase mb-1">KYC Verified</h6>
                            <h2 class="mb-0 fw-bold text-dark">{{ $verifiedKyc }}</h2>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Applications -->
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.companies.index') }}" class="text-decoration-none">
                    <div id="total-applications"
                        class="stats-card stats-card-clickable h-100 p-4 border-0 shadow-sm bg-white rounded-4">
                        <div class="d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="stats-icon bg-purple bg-opacity-10 text-purple rounded-3 p-2"
                                    style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                    <i class="bi bi-file-earmark-text-fill fs-4"></i>
                                </div>
                                <div class="growth-indicator small text-success">
                                    <i class="bi bi-graph-up-arrow me-1"></i>{{ $applicationGrowth }}%
                                </div>
                            </div>
                            <h6 class="text-muted fw-bold small text-uppercase mb-1">Total Applications</h6>
                            <h2 class="mb-0 fw-bold text-dark">{{ number_format($totalApplications) }}</h2>
                        </div>
                    </div>
                </a>
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

            <!-- User Distribution by Role -->
            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">User Distribution</h5>
                            <div class="small text-muted">By role type</div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <!-- Job Seekers -->
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                                            <i class="bi bi-person-badge text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Job Seekers</h6>
                                            <small class="text-muted">Active candidates</small>
                                        </div>
                                    </div>
                                    <h4 class="mb-0 text-primary">
                                        {{ \App\Models\User::where('role', 'jobseeker')->count() }}
                                    </h4>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary"
                                        style="width: {{ $totalUsers > 0 ? (\App\Models\User::where('role', 'jobseeker')->count() / $totalUsers * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Employers -->
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                                            <i class="bi bi-building text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Employers</h6>
                                            <small class="text-muted">Companies hiring</small>
                                        </div>
                                    </div>
                                    <h4 class="mb-0 text-success">{{ \App\Models\User::where('role', 'employer')->count() }}
                                    </h4>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $totalUsers > 0 ? (\App\Models\User::where('role', 'employer')->count() / $totalUsers * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Admins with User List -->
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                                            <i class="bi bi-shield-check text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Administrators</h6>
                                            <small class="text-muted">System admins</small>
                                        </div>
                                    </div>
                                    <h4 class="mb-0 text-warning">{{ \App\Models\User::where('role', 'admin')->count() }}
                                    </h4>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-warning"
                                        style="width: {{ $totalUsers > 0 ? (\App\Models\User::where('role', 'admin')->count() / $totalUsers * 100) : 0 }}%">
                                    </div>
                                </div>

                                <!-- Admin Users List -->
                                @php
                                    $adminUsers = \App\Models\User::where('role', 'admin')->latest()->take(5)->get();
                                @endphp
                                @if($adminUsers->count() > 0)
                                    <div class="admin-users-list mt-2">
                                        <small class="text-muted fw-semibold d-block mb-2">Admin Users:</small>
                                        @foreach($adminUsers as $admin)
                                            <div class="d-flex align-items-center py-2 border-top">
                                                <div class="flex-shrink-0">
                                                    @if($admin->profile_photo)
                                                        <img src="{{ asset('profile_img/' . $admin->profile_photo) }}"
                                                            class="rounded-circle" width="32" height="32" alt="{{ $admin->name }}">
                                                    @else
                                                        <div class="bg-warning bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <span class="text-warning fw-bold">{{ substr($admin->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <div class="fw-semibold" style="font-size: 0.875rem;">{{ $admin->name }}</div>
                                                    <small class="text-muted">{{ $admin->email }}</small>
                                                </div>
                                                <a href="{{ route('admin.users.show', $admin->id) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                        @if(\App\Models\User::where('role', 'admin')->count() > 5)
                                            <div class="text-center mt-2">
                                                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}"
                                                    class="btn btn-sm btn-link">
                                                    View all {{ \App\Models\User::where('role', 'admin')->count() }} admins
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Feed Row -->
        <div class="row g-4 mb-4">
            <!-- Activity Feed -->
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Recent Activity</h5>
                            <small class="text-muted">Latest platform events</small>
                        </div>
                        <a href="{{ route('admin.settings.audit-log') }}" class="btn btn-sm btn-outline-primary">View
                            All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="activity-feed">
                            @php
                                // Get recent activities
                                $recentJobs = \App\Models\Job::with('employer')->latest()->take(3)->get();
                                $recentUsers = \App\Models\User::latest()->take(3)->get();
                                $recentApplications = \App\Models\JobApplication::with(['user', 'job'])->latest()->take(3)->get();

                                // Merge and sort by created_at
                                $activities = collect();

                                foreach ($recentJobs as $job) {
                                    $activities->push([
                                        'type' => 'job',
                                        'data' => $job,
                                        'created_at' => $job->created_at
                                    ]);
                                }

                                foreach ($recentUsers as $user) {
                                    $activities->push([
                                        'type' => 'user',
                                        'data' => $user,
                                        'created_at' => $user->created_at
                                    ]);
                                }

                                foreach ($recentApplications as $app) {
                                    $activities->push([
                                        'type' => 'application',
                                        'data' => $app,
                                        'created_at' => $app->created_at
                                    ]);
                                }

                                $activities = $activities->sortByDesc('created_at')->take(8);
                            @endphp

                            @forelse($activities as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon
                                                                            @if($activity['type'] === 'job') activity-icon-primary
                                                                            @elseif($activity['type'] === 'user') activity-icon-success
                                                                            @else activity-icon-info
                                                                            @endif">
                                        @if($activity['type'] === 'job')
                                            <i class="bi bi-briefcase"></i>
                                        @elseif($activity['type'] === 'user')
                                            <i class="bi bi-person-plus"></i>
                                        @else
                                            <i class="bi bi-file-earmark-text"></i>
                                        @endif
                                    </div>
                                    <div class="activity-content">
                                        @if($activity['type'] === 'job')
                                            <p class="activity-text">
                                                <strong>{{ $activity['data']->employer->name ?? 'Unknown' }}</strong> posted a new
                                                job:
                                                <a
                                                    href="{{ route('admin.jobs.show', $activity['data']->id) }}">{{ Str::limit($activity['data']->title, 40) }}</a>
                                            </p>
                                        @elseif($activity['type'] === 'user')
                                            <p class="activity-text">
                                                <strong>{{ $activity['data']->name }}</strong> registered as
                                                <span
                                                    class="badge bg-{{ $activity['data']->role === 'employer' ? 'success' : 'primary' }} badge-sm">{{ ucfirst($activity['data']->role) }}</span>
                                            </p>
                                        @else
                                            <p class="activity-text">
                                                <strong>{{ $activity['data']->user->name ?? 'User' }}</strong> applied to
                                                <a href="#">{{ Str::limit($activity['data']->job->title ?? 'Unknown Job', 30) }}</a>
                                            </p>
                                        @endif
                                        <span class="activity-time">
                                            <i class="bi bi-clock me-1"></i>{{ $activity['created_at']->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">No recent activity</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        .stats-icon {
            transition: transform 0.3s ease;
        }

        .stats-card-clickable:hover .stats-icon {
            transform: scale(1.1);
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>

    @push('scripts')
        <script>
            let platformChart = null;

            function initPlatformChart(data) {
                const ctx = document.getElementById('platformActivityChart').getContext('2d');

                if (platformChart) {
                    platformChart.destroy();
                }

                platformChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(item => item.date),
                        datasets: [
                            {
                                label: 'Users',
                                data: data.map(item => item.users),
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 2,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Jobs',
                                data: data.map(item => item.jobs),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 2,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: '#1f2937',
                                padding: 12,
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 13 },
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    stepSize: 1,
                                    color: '#6b7280'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    maxTicksLimit: 10,
                                    color: '#6b7280'
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        }
                    }
                });
            }

            // Dashboard Statistics Update System
            class AdminDashboardUpdater {
                constructor() {
                    this.isUpdating = false;
                    this.init();
                }

                init() {
                    const refreshBtn = document.getElementById('refresh-stats');
                    if (refreshBtn) {
                        refreshBtn.addEventListener('click', () => this.updateStats());
                    }

                    // Initial chart load
                    const initialData = {!! json_encode($chartData) !!};
                    initPlatformChart(initialData);
                }

                async updateStats() {
                    if (this.isUpdating) return;

                    this.isUpdating = true;
                    this.showLoadingIndicator();

                    try {
                        const response = await fetch('{{ route('admin.dashboard.stats') }}', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) throw new Error('Refresh failed');
                        const result = await response.json();

                        if (result.success) {
                            this.updateDashboardCards(result.data);
                            if (result.data.chartData) {
                                initPlatformChart(result.data.chartData);
                            }
                        }
                    } catch (error) {
                        console.error('Dashboard refresh error:', error);
                    } finally {
                        this.isUpdating = false;
                        this.hideLoadingIndicator();
                    }
                }

                updateDashboardCards(data) {
                    this.animateValue('total-users', data.totalUsers);
                    this.animateValue('active-jobs', data.activeJobs);
                    this.animateValue('pending-jobs', data.pendingJobs);
                    this.animateValue('verified-kyc', data.verifiedKyc);
                    this.animateValue('total-applications', data.totalApplications);
                }

                animateValue(id, newValue) {
                    const el = document.querySelector(`#${id} h2`);
                    if (!el) return;

                    const startValue = parseInt(el.textContent.replace(/,/g, '')) || 0;
                    const duration = 1000;
                    const startTime = performance.now();

                    const step = (currentTime) => {
                        const progress = Math.min((currentTime - startTime) / duration, 1);
                        const current = Math.floor(startValue + progress * (newValue - startValue));
                        el.textContent = new Intl.NumberFormat().format(current);

                        if (progress < 1) {
                            requestAnimationFrame(step);
                        }
                    };
                    requestAnimationFrame(step);
                }

                showLoadingIndicator() {
                    const btn = document.getElementById('refresh-stats');
                    if (btn) btn.classList.add('disabled');
                    const icon = btn?.querySelector('i');
                    if (icon) icon.classList.add('spin');
                }

                hideLoadingIndicator() {
                    const btn = document.getElementById('refresh-stats');
                    if (btn) btn.classList.remove('disabled');
                    const icon = btn?.querySelector('i');
                    if (icon) icon.classList.remove('spin');
                }
            }

            // Function to manually refresh dashboard with cache clearing
            function refreshDashboard() {
                const refreshBtn = document.getElementById('refresh-stats');
                const icon = refreshBtn?.querySelector('i');

                if (refreshBtn) refreshBtn.classList.add('disabled');
                if (icon) icon.classList.add('spin');

                fetch('{{ route('admin.dashboard.clear-cache') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing dashboard:', error);
                        if (refreshBtn) refreshBtn.classList.remove('disabled');
                        if (icon) icon.classList.remove('spin');
                    });
            }

            // Initialize on Load
            document.addEventListener('DOMContentLoaded', () => {
                new AdminDashboardUpdater();
            });
        </script>
    @endpush
@endsection