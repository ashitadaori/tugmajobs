@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h1>Dashboard</h1>
                <p>Welcome back! Here's what's happening with your platform today.</p>
            </div>
            <div class="dashboard-header-right">
                <div class="system-status-badge">
                    <span class="status-dot"></span>
                    <span>System Online</span>
                </div>
                <a href="{{ route('admin.dashboard.export') }}" class="btn btn-outline-primary" title="Export statistics">
                    <i class="bi bi-download"></i>
                    <span>Export</span>
                </a>
                <button type="button" id="refresh-stats" class="btn btn-primary" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <!-- Total Users -->
            <a href="{{ route('admin.users.index') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-icon primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <h3>{{ number_format($totalUsers) }}</h3>
                    <p>Total Users</p>
                    <div class="stat-card-trend {{ $userGrowth >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ $userGrowth >= 0 ? 'up' : 'down' }}"></i>
                        <span>{{ abs($userGrowth) }}% from last month</span>
                    </div>
                </div>
                <div class="stat-card-link">
                    <i class="bi bi-arrow-right"></i> View all users
                </div>
            </a>

            <!-- Active Jobs -->
            <a href="{{ route('admin.jobs.index') }}" class="stat-card">
                <div class="stat-card-live"></div>
                <div class="stat-card-header">
                    <div class="stat-card-icon success">
                        <i class="bi bi-briefcase"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <h3>{{ number_format($activeJobs) }}</h3>
                    <p>Active Jobs</p>
                    <div class="stat-card-trend {{ $jobGrowth >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ $jobGrowth >= 0 ? 'up' : 'down' }}"></i>
                        <span>{{ abs($jobGrowth) }}% from last month</span>
                    </div>
                </div>
                <div class="stat-card-link">
                    <i class="bi bi-arrow-right"></i> View all jobs
                </div>
            </a>

            <!-- Pending Jobs -->
            <a href="{{ route('admin.jobs.pending') }}" class="stat-card {{ $pendingJobs > 0 ? 'has-alert' : '' }}">
                @if($pendingJobs > 0)
                    <span class="stat-card-badge bg-warning text-dark position-absolute top-0 end-0 m-3">{{ $pendingJobs }} pending</span>
                @endif
                <div class="stat-card-header">
                    <div class="stat-card-icon warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <h3>{{ number_format($pendingJobs) }}</h3>
                    <p>Pending Approval</p>
                    <div class="stat-card-trend {{ $pendingJobs > 0 ? 'neutral' : 'up' }}">
                        @if($pendingJobs > 0)
                            <i class="bi bi-exclamation-circle"></i>
                            <span>Needs review</span>
                        @else
                            <i class="bi bi-check-circle"></i>
                            <span>All jobs reviewed</span>
                        @endif
                    </div>
                </div>
                <div class="stat-card-link">
                    <i class="bi bi-arrow-right"></i> Review pending jobs
                </div>
            </a>

            <!-- Total Applications -->
            <a href="{{ route('admin.companies.index') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-icon info">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <h3>{{ number_format($totalApplications) }}</h3>
                    <p>Total Applications</p>
                    <div class="stat-card-trend {{ $applicationGrowth >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ $applicationGrowth >= 0 ? 'up' : 'down' }}"></i>
                        <span>{{ abs($applicationGrowth) }}% from last month</span>
                    </div>
                </div>
                <div class="stat-card-link">
                    <i class="bi bi-arrow-right"></i> View applications
                </div>
            </a>

            <!-- KYC Verified -->
            <a href="{{ route('admin.kyc.didit-verifications') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-icon primary">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <h3>{{ number_format($verifiedKyc) }}</h3>
                    <p>KYC Verified</p>
                    <div class="stat-card-trend neutral">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ $pendingKyc }} pending verification</span>
                    </div>
                </div>
                <div class="stat-card-link">
                    <i class="bi bi-arrow-right"></i> View KYC status
                </div>
            </a>
        </div>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Registration Chart -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h2>
                        New Registrations
                        <small>User registration trends</small>
                    </h2>
                    <a href="{{ route('admin.analytics.dashboard') }}" class="btn-link">View Analytics</a>
                </div>
                <div class="dashboard-card-body">
                    <div class="chart-container">
                        <canvas id="registrationChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- User Distribution -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h2>
                        User Distribution
                        <small>By role type</small>
                    </h2>
                    <a href="{{ route('admin.users.index') }}" class="btn-link">View All</a>
                </div>
                <div class="dashboard-card-body no-padding">
                    @php
                        $jobseekerCount = \App\Models\User::where('role', 'jobseeker')->count();
                        $employerCount = \App\Models\User::where('role', 'employer')->count();
                        $adminCount = \App\Models\User::where('role', 'admin')->count();
                    @endphp
                    <ul class="user-distribution-list">
                        <li class="user-distribution-item">
                            <div class="user-distribution-header">
                                <div class="user-distribution-info">
                                    <div class="user-distribution-icon jobseekers">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div class="user-distribution-text">
                                        <h4>Job Seekers</h4>
                                        <span>Active candidates</span>
                                    </div>
                                </div>
                                <span class="user-distribution-count">{{ number_format($jobseekerCount) }}</span>
                            </div>
                            <div class="user-distribution-progress">
                                <div class="user-distribution-progress-bar jobseekers"
                                     style="width: {{ $totalUsers > 0 ? ($jobseekerCount / $totalUsers * 100) : 0 }}%"></div>
                            </div>
                        </li>
                        <li class="user-distribution-item">
                            <div class="user-distribution-header">
                                <div class="user-distribution-info">
                                    <div class="user-distribution-icon employers">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="user-distribution-text">
                                        <h4>Employers</h4>
                                        <span>Companies hiring</span>
                                    </div>
                                </div>
                                <span class="user-distribution-count">{{ number_format($employerCount) }}</span>
                            </div>
                            <div class="user-distribution-progress">
                                <div class="user-distribution-progress-bar employers"
                                     style="width: {{ $totalUsers > 0 ? ($employerCount / $totalUsers * 100) : 0 }}%"></div>
                            </div>
                        </li>
                        <li class="user-distribution-item">
                            <div class="user-distribution-header">
                                <div class="user-distribution-info">
                                    <div class="user-distribution-icon admins">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <div class="user-distribution-text">
                                        <h4>Administrators</h4>
                                        <span>System admins</span>
                                    </div>
                                </div>
                                <span class="user-distribution-count">{{ number_format($adminCount) }}</span>
                            </div>
                            <div class="user-distribution-progress">
                                <div class="user-distribution-progress-bar admins"
                                     style="width: {{ $totalUsers > 0 ? ($adminCount / $totalUsers * 100) : 0 }}%"></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h2>
                    Recent Activity
                    <small>Latest platform events</small>
                </h2>
                <a href="{{ route('admin.settings.audit-log') }}" class="btn-link">View All Activity</a>
            </div>
            <div class="dashboard-card-body no-padding">
                <ul class="activity-feed">
                    @php
                        $recentJobs = \App\Models\Job::with('employer')->latest()->take(3)->get();
                        $recentUsers = \App\Models\User::latest()->take(3)->get();
                        $recentApplications = \App\Models\JobApplication::with(['user', 'job'])->latest()->take(3)->get();

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
                        <li class="activity-feed-item">
                            <div class="activity-feed-icon {{ $activity['type'] }}">
                                @if($activity['type'] === 'job')
                                    <i class="bi bi-briefcase"></i>
                                @elseif($activity['type'] === 'user')
                                    <i class="bi bi-person-plus"></i>
                                @else
                                    <i class="bi bi-file-earmark-text"></i>
                                @endif
                            </div>
                            <div class="activity-feed-content">
                                @if($activity['type'] === 'job')
                                    <p>
                                        <strong>{{ $activity['data']->employer->name ?? 'Unknown' }}</strong> posted a new job:
                                        <a href="{{ route('admin.jobs.show', $activity['data']->id) }}">{{ Str::limit($activity['data']->title, 40) }}</a>
                                    </p>
                                @elseif($activity['type'] === 'user')
                                    <p>
                                        <strong>{{ $activity['data']->name }}</strong> registered as
                                        <span class="badge bg-{{ $activity['data']->role === 'employer' ? 'success' : 'primary' }}">{{ ucfirst($activity['data']->role) }}</span>
                                    </p>
                                @else
                                    <p>
                                        <strong>{{ $activity['data']->user->name ?? 'User' }}</strong> applied to
                                        <a href="#">{{ Str::limit($activity['data']->job->title ?? 'Unknown Job', 30) }}</a>
                                    </p>
                                @endif
                                <span class="activity-feed-time">
                                    <i class="bi bi-clock"></i>
                                    {{ $activity['created_at']->diffForHumans() }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="activity-feed-empty">
                            <i class="bi bi-inbox"></i>
                            <p>No recent activity</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Registration Chart
            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            new Chart(registrationCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($registrationData->pluck('day')) !!},
                    datasets: [{
                        label: 'New Registrations',
                        data: {!! json_encode($registrationData->pluck('count')) !!},
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
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
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                },
                                stepSize: 1
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });

            // Show simple toast
            function showSimpleToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.textContent = message;
                toast.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 12px 20px;
                    background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                    color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                    border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
                    border-radius: 8px;
                    z-index: 9999;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    font-weight: 500;
                `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }

            // Manual refresh dashboard function
            function refreshDashboard() {
                const refreshBtn = document.getElementById('refresh-stats');
                const originalHTML = refreshBtn.innerHTML;

                refreshBtn.disabled = true;
                refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> <span>Refreshing...</span>';

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
                            showSimpleToast('Dashboard refreshed successfully!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing dashboard:', error);
                        showSimpleToast('Failed to refresh dashboard', 'error');
                        refreshBtn.disabled = false;
                        refreshBtn.innerHTML = originalHTML;
                    });
            }

            // Spin animation style
            const style = document.createElement('style');
            style.textContent = `
                .spin {
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        </script>
    @endpush
@endsection
