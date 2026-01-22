<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Saved Jobs - TugmaJobs</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        /* Top Navigation Bar Styling */
        .top-navbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .brand-logo {
            font-size: 18px;
            color: #1e293b;
            display: flex;
            align-items: center;
        }
        
        .brand-logo .text-primary {
            color: #6366f1 !important;
        }
        
        .nav-links {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .nav-link {
            padding: 8px 16px;
            color: #64748b;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }
        
        .nav-link:hover {
            background: #f1f5f9;
            color: #6366f1;
        }
        
        .nav-link.active {
            background: #ede9fe;
            color: #6366f1;
        }
        
        .notification-icon {
            cursor: pointer;
            color: #64748b;
            font-size: 18px;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .notification-icon:hover {
            background: #f1f5f9;
            color: #6366f1;
        }
        
        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .user-info {
            color: #1e293b;
            font-weight: 500;
            font-size: 14px;
        }
        
        .dropdown-menu {
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 8px 0;
            min-width: 180px;
        }
        
        .dropdown-item {
            padding: 10px 20px;
            color: #64748b;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background: #f8fafc;
            color: #6366f1;
        }
        
        .dropdown-toggle::after {
            margin-left: 6px;
        }
        
        .job-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .company-logo {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
        
        .company-initial {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: 700;
        }
        
        .job-title {
            color: #1e293b;
            font-size: 20px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .job-title:hover {
            color: #6366f1;
        }
        
        .company-name {
            color: #6366f1;
            font-weight: 500;
            font-size: 15px;
        }
        
        .job-detail {
            color: #64748b;
            font-size: 14px;
        }
        
        .btn-view {
            background: #6366f1;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            transition: background 0.2s;
        }
        
        .btn-view:hover {
            background: #4f46e5;
            color: white;
        }
        
        .btn-remove {
            background: white;
            color: #dc2626;
            padding: 10px 24px;
            border-radius: 8px;
            border: 2px solid #dc2626;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-remove:hover {
            background: #dc2626;
            color: white;
        }
        
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 60px 30px;
            text-align: center;
        }

        /* Enhanced Empty State */
        .enhanced-empty-state {
            background: white;
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .empty-state-icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .empty-state-title {
            color: #1e293b;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .empty-state-description {
            color: #64748b;
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        .empty-state-tips {
            background: #f8fafc;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto 40px;
            text-align: left;
        }

        .tips-title {
            color: #1e293b;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tips-list li {
            color: #475569;
            font-size: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .tips-list li i {
            color: #10b981;
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .empty-state-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .empty-state-actions .btn {
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .empty-state-actions .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .empty-state-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .empty-state-actions .btn-outline-primary {
            border: 2px solid #6366f1;
            color: #6366f1;
        }

        .empty-state-actions .btn-outline-primary:hover {
            background: #6366f1;
            color: white;
            transform: translateY(-2px);
        }

        .empty-state-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
        }

        .stat-item i {
            color: #6366f1;
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .enhanced-empty-state {
                padding: 40px 20px;
            }

            .empty-state-icon-wrapper {
                width: 100px;
                height: 100px;
                font-size: 50px;
            }

            .empty-state-title {
                font-size: 24px;
            }

            .empty-state-description {
                font-size: 16px;
            }

            .empty-state-actions {
                flex-direction: column;
            }

            .empty-state-actions .btn {
                width: 100%;
            }

            .empty-state-stats {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    
    <!-- Top Navigation Bar (Exact Copy from Original) -->
    <div class="top-navbar">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="brand-logo me-4">
                        <i class="fas fa-briefcase text-primary me-2"></i>
                        <strong>TugmaJobs</strong>
                    </div>
                    <nav class="nav-links d-none d-md-flex">
                        <a href="{{ route('home', ['force_home' => 1]) }}" class="nav-link">🏠 Home</a>
                        <a href="{{ route('jobs') }}" class="nav-link">🔍 Find Jobs</a>
                        <a href="{{ route('companies') }}" class="nav-link">🏢 Companies</a>
                        <div class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">💼 My Career</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('account.dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.myJobApplications') }}">Applications</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.saved-jobs.index') }}">Saved Jobs</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('account.myProfile') }}">Profile</a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="notification-icon position-relative">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">0</span>
                    </div>
                    <div class="user-info d-flex align-items-center">
                        <span class="me-2">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        <div class="dropdown">
                            <button class="btn btn-link p-0 dropdown-toggle" data-bs-toggle="dropdown" style="text-decoration: none; color: inherit;">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('account.settings') }}">Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1200px;">
        
        <!-- Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="mb-2" style="color: #1e293b; font-size: 28px; font-weight: 700;">
                            <i class="fas fa-bookmark" style="color: #6366f1;"></i>
                            My Saved Jobs
                        </h1>
                        <p class="mb-0" style="color: #64748b;">
                            <a href="{{ route('account.dashboard') }}" style="color: #6366f1; text-decoration: none;">Dashboard</a>
                            <span class="mx-2" style="color: #cbd5e1;">/</span>
                            <span>Saved Jobs</span>
                        </p>
                    </div>
                    <div class="badge bg-light text-dark" style="font-size: 18px; padding: 12px 20px;">
                        <strong>{{ $savedJobs->total() }}</strong> saved jobs
                    </div>
                </div>
            </div>
        </div>

        @php
            $jobsArray = $savedJobs->items();
            $hasJobs = count($jobsArray) > 0;
        @endphp

        @if($hasJobs)
            <!-- Jobs List -->
            @foreach($jobsArray as $savedJob)
                @if($savedJob->job)
                    <div class="job-card">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-2">
                                    <a href="{{ route('jobDetail', $savedJob->job->id) }}" class="job-title">
                                        {{ $savedJob->job->title }}
                                    </a>
                                </h3>
                                <p class="company-name mb-2">
                                    <i class="fas fa-building"></i>
                                    {{ $savedJob->job->employer->name ?? 'Company' }}
                                </p>
                                <div class="d-flex flex-wrap gap-3 job-detail">
                                    <span>
                                        <i class="fas fa-map-marker-alt" style="color: #6366f1;"></i>
                                        {{ $savedJob->job->location }}
                                    </span>
                                    <span>
                                        <i class="fas fa-briefcase" style="color: #6366f1;"></i>
                                        {{ $savedJob->job->jobType->name ?? 'N/A' }}
                                    </span>
                                    @if($savedJob->job->salary_min && $savedJob->job->salary_max)
                                        <span>
                                            <i class="fas fa-peso-sign" style="color: #10b981;"></i>
                                            ₱{{ number_format($savedJob->job->salary_min) }} - ₱{{ number_format($savedJob->job->salary_max) }}
                                        </span>
                                    @endif
                                    <span>
                                        <i class="fas fa-clock" style="color: #6366f1;"></i>
                                        Saved {{ $savedJob->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('jobDetail', $savedJob->job->id) }}" class="btn-view">
                                        <i class="fas fa-eye"></i> View Job
                                    </a>
                                    <button onclick="removeJob({{ $savedJob->job->id }})" class="btn-remove">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Pagination -->
            @if($savedJobs->hasPages())
                <div class="mt-4">
                    {{ $savedJobs->links() }}
                </div>
            @endif
        @else
            <!-- Enhanced Empty State -->
            <div class="enhanced-empty-state">
                <div class="empty-state-icon-wrapper">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h3 class="empty-state-title">
                    No Saved Jobs Yet
                </h3>
                <p class="empty-state-description">
                    Build your collection of interesting opportunities! Save jobs to review later and never miss out on the perfect role.
                </p>

                <!-- Tips Section -->
                <div class="empty-state-tips">
                    <h5 class="tips-title"><i class="fas fa-lightbulb me-2"></i>How to Save Jobs:</h5>
                    <ul class="tips-list">
                        <li><i class="fas fa-check-circle"></i> Browse jobs that match your skills</li>
                        <li><i class="fas fa-check-circle"></i> Click the heart icon on any job card</li>
                        <li><i class="fas fa-check-circle"></i> Come back anytime to review your saved jobs</li>
                        <li><i class="fas fa-check-circle"></i> Apply when you're ready!</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="empty-state-actions">
                    <a href="{{ route('jobs') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>Browse All Jobs
                    </a>
                    <a href="{{ route('account.jobAlerts') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-bell me-2"></i>Set Job Alerts
                    </a>
                </div>

                <!-- Quick Stats -->
                <div class="empty-state-stats">
                    <div class="stat-item">
                        <i class="fas fa-briefcase"></i>
                        <span>1000+ Active Jobs</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-building"></i>
                        <span>500+ Companies</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span>Join 10k+ Job Seekers</span>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function removeJob(jobId) {
        if (!confirm('Are you sure you want to remove this job from your saved list?')) {
            return;
        }
        
        fetch('{{ route("account.saved-jobs.destroy") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ job_id: jobId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to remove job. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
    </script>
</body>
</html>
