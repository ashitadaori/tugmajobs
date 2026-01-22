@extends('layouts.employer')

@section('page_title', 'Applicants for ' . $job->title)

@section('content')
    <div class="container-fluid">
        <!-- Job Header -->
        <div class="job-header-card mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="job-title">{{ $job->title }}</h2>
                    <div class="job-meta">
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> {{ $job->location }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-briefcase"></i> {{ $job->job_type }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-calendar"></i> Posted {{ $job->created_at->diffForHumans() }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-users"></i> {{ $applications->total() }} Applicants
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to My Jobs
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Filter Applicants</h5>
                </div>
                <div class="col-md-6">
                    <div class="filter-buttons text-end">
                        <button class="btn btn-sm btn-outline-primary filter-btn active" data-status="all">
                            All ({{ $applications->total() }})
                        </button>
                        <button class="btn btn-sm btn-outline-warning filter-btn" data-status="pending">
                            Pending ({{ $applications->where('status', 'pending')->count() }})
                        </button>
                        <button class="btn btn-sm btn-outline-success filter-btn" data-status="approved">
                            Approved ({{ $applications->where('status', 'approved')->count() }})
                        </button>
                        <button class="btn btn-sm btn-outline-danger filter-btn" data-status="rejected">
                            Rejected ({{ $applications->where('status', 'rejected')->count() }})
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applicants List -->
        <div class="applicants-section">
            @if($applications->count() > 0)
                <div class="row">
                    @foreach($applications as $application)
                        <div class="col-lg-6 col-xl-4 mb-4 applicant-item" data-status="{{ $application->status }}">
                            <div class="applicant-card">
                                <!-- Applicant Header -->
                                <div class="applicant-header">
                                    <div class="applicant-avatar">
                                        @php
                                            $imgSrc = $application->user->profile_image;
                                        @endphp

                                        @if(Auth::user()->isAdmin())
                                            @if($imgSrc)
                                                <img src="{{ $imgSrc }}" alt="{{ $application->user->name }}"
                                                    onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="applicant-avatar-placeholder"
                                                    style="display: none; width: 60px; height: 60px; background: #eee; border-radius: 50%; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user" style="font-size: 24px; color: #999;"></i>
                                                </div>
                                            @else
                                                <div class="applicant-avatar-placeholder"
                                                    style="width: 60px; height: 60px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user" style="font-size: 24px; color: #999;"></i>
                                                </div>
                                            @endif
                                        @else
                                            <div class="applicant-avatar-placeholder"
                                                style="width: 60px; height: 60px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user-secret" style="font-size: 24px; color: #999;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="applicant-info">
                                        <h6 class="applicant-name">
                                            @if(Auth::user()->isAdmin())
                                                {{ $application->user->name }}
                                            @else
                                                Applicant
                                            @endif
                                        </h6>
                                        @if($application->user->jobSeekerProfile && $application->user->jobSeekerProfile->designation)
                                            <p class="applicant-title">{{ $application->user->jobSeekerProfile->designation }}</p>
                                        @endif
                                        <div class="applicant-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                Applied {{ $application->created_at->format('M d, Y') }}
                                                ({{ $application->created_at->diffForHumans() }})
                                            </small>
                                        </div>
                                    </div>
                                    <div class="applicant-status">
                                        <span class="status-badge status-{{ $application->status }}">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Applicant Details -->
                                <div class="applicant-details">
                                    @if(Auth::user()->isAdmin())
                                        <div class="detail-row">
                                            <span class="detail-label">Email:</span>
                                            <span class="detail-value">{{ $application->user->email }}</span>
                                        </div>
                                        @if($application->user->mobile)
                                            <div class="detail-row">
                                                <span class="detail-label">Phone:</span>
                                                <span class="detail-value">{{ $application->user->mobile }}</span>
                                            </div>
                                        @endif
                                        @if($application->user->jobSeekerProfile && $application->user->jobSeekerProfile->location)
                                            <div class="detail-row">
                                                <span class="detail-label">Location:</span>
                                                <span class="detail-value">{{ $application->user->jobSeekerProfile->location }}</span>
                                            </div>
                                        @endif
                                    @endif

                                    @if($application->user->jobSeekerProfile && $application->user->jobSeekerProfile->experience_years)
                                        <div class="detail-row">
                                            <span class="detail-label">Experience:</span>
                                            <span class="detail-value">{{ $application->user->jobSeekerProfile->experience_years }}
                                                years</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="applicant-actions">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <a href="{{ route('employer.jobseeker.profile', $application->user_id) }}"
                                                class="btn btn-info btn-sm w-100">
                                                <i class="fas fa-user"></i> View Full Profile
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="{{ route('employer.applications.show', $application->id) }}"
                                                class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-eye"></i> View Application
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            @if(Auth::user()->isAdmin())
                                                @if($application->resume)
                                                    <a href="{{ asset('storage/' . $application->resume) }}"
                                                        class="btn btn-success btn-sm w-100" target="_blank">
                                                        <i class="fas fa-download"></i> Resume
                                                    </a>
                                                @else
                                                    <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                        <i class="fas fa-file"></i> No Resume
                                                    </button>
                                                @endif
                                            @else
                                                <button class="btn btn-outline-secondary btn-sm w-100" disabled title="Resume hidden">
                                                    <i class="fas fa-lock"></i> Resume
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Quick Actions -->
                                    @if($application->status === 'pending')
                                        <div class="row g-2 mt-2">
                                            <div class="col-6">
                                                <button class="btn btn-success btn-sm w-100"
                                                    onclick="updateStatus({{ $application->id }}, 'approved')">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-danger btn-sm w-100"
                                                    onclick="updateStatus({{ $application->id }}, 'rejected')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $applications->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4>No Applications Yet</h4>
                        <p class="text-muted">No one has applied for this job position yet.</p>
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to My Jobs
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .job-header-card {
            background: #5CB338 !important;
            padding: 2rem;
            border-radius: 1rem;
            color: white !important;
            box-shadow: 0 4px 20px rgba(92, 179, 56, 0.3);
        }

        .job-header-card * {
            color: white !important;
        }

        .job-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white !important;
        }

        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white !important;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            backdrop-filter: blur(10px);
        }

        .meta-item i {
            color: white !important;
        }

        .filters-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #e2e8f0;
        }

        .filter-btn {
            margin-left: 0.5rem;
            transition: all 0.2s ease;
        }

        .filter-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .applicant-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            height: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .applicant-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .applicant-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .applicant-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .applicant-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .applicant-info {
            flex: 1;
        }

        .applicant-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .applicant-title {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .applicant-meta {
            font-size: 0.8rem;
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .applicant-details {
            margin-bottom: 1.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f8fafc;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        .detail-value {
            font-size: 0.85rem;
            color: #1e293b;
            font-weight: 600;
        }

        .applicant-actions {
            margin-top: auto;
        }

        .empty-state {
            background: white;
            border-radius: 1rem;
            border: 2px solid #e2e8f0;
        }

        @media (max-width: 768px) {
            .job-meta {
                flex-direction: column;
                gap: 0.75rem;
            }

            .filter-buttons {
                text-align: left !important;
                margin-top: 1rem;
            }

            .filter-btn {
                margin: 0.25rem;
                font-size: 0.8rem;
            }
        }
    </style>

    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function () {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const applicantItems = document.querySelectorAll('.applicant-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const status = this.dataset.status;

                    // Update active button
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Filter applicants
                    applicantItems.forEach(item => {
                        if (status === 'all' || item.dataset.status === status) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });

        // Update application status
        function updateStatus(applicationId, status) {
            if (!confirm(`Are you sure you want to ${status} this application?`)) {
                return;
            }

            fetch(`/employer/applications/${applicationId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Show success toast
                        showToast(data.message || 'Application status updated successfully', 'success');
                        // Reload after a short delay to show the toast
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Error updating application status', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error updating application status', 'error');
                });
        }
    </script>
@endsection