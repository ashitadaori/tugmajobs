@extends('layouts.employer')

@section('page_title', 'Job Management')

@section('content')
    <div class="jobs-page">
        <!-- Header Section -->
        <div class="jobs-header">
            <div class="header-left">
                <h1 class="page-title">Job Management</h1>
                <p class="page-description">Manage and track all your job postings</p>
            </div>
            <div class="header-right">
                @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
                    <button class="btn-post-job disabled" disabled>
                        <i class="bi bi-tools"></i>
                        <span>Under Maintenance</span>
                    </button>
                @else
                    <a href="{{ route('employer.jobs.create') }}" class="btn-post-job">
                        <i class="bi bi-plus-lg"></i>
                        <span>Post New Job</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $totalJobs }}</span>
                    <span class="stat-label">Total Jobs</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $activeJobs }}</span>
                    <span class="stat-label">Active</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $pendingJobs }}</span>
                    <span class="stat-label">Pending</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon applications">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $totalApplications }}</span>
                    <span class="stat-label">Applications</span>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <form method="GET" action="{{ route('employer.jobs.index') }}" class="filter-bar" id="filterForm">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" name="search" placeholder="Search jobs..."
                    value="{{ request('search') }}">
                <button type="button" class="clear-btn" id="clearSearch"
                    style="{{ request('search') ? 'display: flex;' : 'display: none;' }}">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="filter-options">
                <select id="statusFilter" name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="{{ App\Models\Job::STATUS_APPROVED }}" {{ request()->filled('status') && request('status') == App\Models\Job::STATUS_APPROVED ? 'selected' : '' }}>Active</option>
                    <option value="{{ App\Models\Job::STATUS_PENDING }}" {{ request()->filled('status') && request('status') == App\Models\Job::STATUS_PENDING ? 'selected' : '' }}>Pending</option>
                    <option value="{{ App\Models\Job::STATUS_REJECTED }}" {{ request()->filled('status') && request('status') == App\Models\Job::STATUS_REJECTED ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
        </form>

        <!-- Jobs List -->
        <div class="jobs-list">
            @forelse($jobs as $job)
                @php
                    $isRejected = $job->status === App\Models\Job::STATUS_REJECTED;
                    $isPending = $job->status === App\Models\Job::STATUS_PENDING;
                    $isActive = $job->status === App\Models\Job::STATUS_APPROVED;
                    $isClosed = $job->status === App\Models\Job::STATUS_CLOSED;
                @endphp
                <div class="job-card {{ $isRejected || $isClosed ? 'rejected' : '' }}" data-status="{{ $job->status }}">
                    <div class="job-main">
                        <div class="job-info">
                            <div class="job-title-row">
                                <h3 class="job-title">{{ $job->title }}</h3>
                                <span class="status-badge {{ $isActive ? 'active' : ($isPending ? 'pending' : 'rejected') }}"
                                    @if($isClosed) title="Job is closed" data-bs-toggle="tooltip" data-bs-placement="top"
                                    @endif>
                                    @if($isActive)
                                        <i class="bi bi-check-circle-fill"></i> Active
                                    @elseif($isPending)
                                        <i class="bi bi-clock-fill"></i> Pending
                                    @elseif($isRejected)
                                        <i class="bi bi-x-circle-fill"></i> Rejected
                                    @elseif($isClosed)
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                    @else
                                        <i class="bi bi-info-circle-fill"></i> {{ ucfirst($job->status) }}
                                    @endif
                                </span>
                            </div>
                            <div class="job-meta">
                                <span class="meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $job->location }}
                                </span>
                                <span class="meta-item">
                                    <i class="bi bi-briefcase"></i>
                                    {{ $job->jobType->name }}
                                </span>
                                <span class="meta-item">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $job->created_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                        <div class="job-stats">
                            <div class="stat-item">
                                <span class="stat-number">{{ $job->applications_count ?? 0 }}</span>
                                <span class="stat-text">Applicants</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">{{ $job->views_count ?? 0 }}</span>
                                <span class="stat-text">Views</span>
                            </div>
                        </div>
                    </div>

                    @if($isRejected && $job->rejection_reason)
                        <div class="rejection-notice">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div class="rejection-content">
                                <strong>Rejection Reason:</strong>
                                <p>{{ Str::limit($job->rejection_reason, 150) }}</p>
                                @if(strlen($job->rejection_reason) > 150)
                                    <button type="button" class="read-more-btn"
                                        onclick="showRejectionModal({{ $job->id }}, '{{ addslashes($job->title) }}', '{{ addslashes($job->rejection_reason ?? '') }}', '{{ $job->rejected_at ? $job->rejected_at->format('M d, Y \a\t g:i A') : '' }}')">
                                        Read full feedback
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="job-actions">
                        @if($isRejected)
                            <a href="{{ route('employer.jobs.edit', $job->id) }}" class="action-btn edit">
                                <i class="bi bi-pencil-square"></i>
                                Edit & Resubmit
                            </a>
                            <button type="button" class="action-btn view-feedback"
                                onclick="showRejectionModal({{ $job->id }}, '{{ addslashes($job->title) }}', '{{ addslashes($job->rejection_reason ?? '') }}', '{{ $job->rejected_at ? $job->rejected_at->format('M d, Y \a\t g:i A') : '' }}')">
                                <i class="bi bi-eye"></i>
                                View Feedback
                            </button>
                        @else
                            <a href="{{ route('employer.jobs.applicants', $job->id) }}" class="action-btn applicants">
                                <i class="bi bi-people"></i>
                                Applicants ({{ $job->applications_count ?? 0 }})
                            </a>
                            <a href="{{ route('employer.jobs.edit', $job->id) }}" class="action-btn edit">
                                <i class="bi bi-pencil"></i>
                                Edit
                            </a>
                        @endif
                        <button type="button" class="action-btn delete" onclick="confirmDelete({{ $job->id }})">
                            <i class="bi bi-trash"></i>
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <h3>No Jobs Posted Yet</h3>
                    <p>Start attracting top talent by posting your first job opening</p>
                    @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
                        <button class="btn-post-job disabled" disabled>
                            <i class="bi bi-tools"></i>
                            Under Maintenance
                        </button>
                    @else
                        <a href="{{ route('employer.jobs.create') }}" class="btn-post-job">
                            <i class="bi bi-plus-lg"></i>
                            Post Your First Job
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($jobs->hasPages())
            <div class="pagination-wrapper">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Job Rejected
                    </h5>
                    <button type="button" class="btn-close" onclick="closeRejectionModal()"></button>
                </div>
                <div class="modal-body">
                    <h6 id="jobTitle" class="job-title-modal"></h6>
                    <div class="feedback-box">
                        <label>Admin Feedback:</label>
                        <p id="rejectionReason"></p>
                    </div>
                    <div id="rejectionDate" class="rejection-date" style="display: none;">
                        <i class="bi bi-clock"></i>
                        <span id="rejectedAt"></span>
                    </div>
                    <div class="next-steps">
                        <h6>What to do next:</h6>
                        <ol>
                            <li>Review the feedback carefully</li>
                            <li>Edit your job posting to address the concerns</li>
                            <li>Resubmit for approval</li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectionModal()">Close</button>
                    <a id="editJobBtn" href="#" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i>
                        Edit & Resubmit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="delete-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5>Are you sure?</h5>
                    <p>This action cannot be undone and will remove all associated applications.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Job</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* ===== CLEAN PROFESSIONAL DESIGN ===== */
            .jobs-page {
                padding: 24px;
                max-width: 100%;
                background: #f8fafc;
                min-height: 100vh;
            }

            /* Header */
            .jobs-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 24px;
                flex-wrap: wrap;
                gap: 16px;
            }

            .page-title {
                font-size: 28px;
                font-weight: 700;
                color: #1e293b;
                margin: 0;
            }

            .page-description {
                color: #64748b;
                margin: 4px 0 0 0;
                font-size: 14px;
            }

            .btn-post-job {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 12px 24px;
                background: #059669;
                color: white;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 14px;
                text-decoration: none;
                transition: all 0.2s;
                cursor: pointer;
            }

            .btn-post-job:hover {
                background: #047857;
                color: white;
                transform: translateY(-1px);
            }

            .btn-post-job.disabled {
                background: #94a3b8;
                cursor: not-allowed;
            }

            /* Stats Row */
            .stats-row {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
                margin-bottom: 24px;
            }

            .stat-card {
                background: white;
                border-radius: 12px;
                padding: 20px;
                display: flex;
                align-items: center;
                gap: 16px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                border: 1px solid #e2e8f0;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }

            .stat-icon.total {
                background: #eff6ff;
                color: #3b82f6;
            }

            .stat-icon.active {
                background: #ecfdf5;
                color: #059669;
            }

            .stat-icon.pending {
                background: #fffbeb;
                color: #d97706;
            }

            .stat-icon.applications {
                background: #f5f3ff;
                color: #7c3aed;
            }

            .stat-content {
                display: flex;
                flex-direction: column;
            }

            .stat-value {
                font-size: 24px;
                font-weight: 700;
                color: #1e293b;
                line-height: 1;
            }

            .stat-label {
                font-size: 13px;
                color: #64748b;
                margin-top: 4px;
            }

            /* Filter Bar */
            .filter-bar {
                display: flex;
                gap: 16px;
                margin-bottom: 24px;
                flex-wrap: wrap;
            }

            .search-box {
                flex: 1;
                min-width: 280px;
                position: relative;
                display: flex;
                align-items: center;
            }

            .search-box i {
                position: absolute;
                left: 14px;
                color: #94a3b8;
                font-size: 16px;
            }

            .search-box input {
                width: 100%;
                padding: 12px 40px 12px 42px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: 14px;
                background: white;
                transition: all 0.2s;
            }

            .search-box input:focus {
                outline: none;
                border-color: #059669;
                box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
            }

            .clear-btn {
                position: absolute;
                right: 12px;
                background: none;
                border: none;
                color: #94a3b8;
                cursor: pointer;
                padding: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .clear-btn:hover {
                color: #64748b;
            }

            .filter-select {
                padding: 12px 16px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: 14px;
                background: white;
                min-width: 150px;
                cursor: pointer;
            }

            .filter-select:focus {
                outline: none;
                border-color: #059669;
            }

            /* Jobs List */
            .jobs-list {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .job-card {
                background: white;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                overflow: hidden;
                transition: all 0.2s;
            }

            .job-card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border-color: #cbd5e1;
            }

            .job-card.rejected {
                border-left: 4px solid #ef4444;
            }

            .job-main {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding: 20px 24px;
                gap: 24px;
            }

            .job-info {
                flex: 1;
                min-width: 0;
            }

            .job-title-row {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 12px;
            }

            .job-title {
                font-size: 18px;
                font-weight: 600;
                color: #1e293b;
                margin: 0;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
            }

            .status-badge.active {
                background: #ecfdf5;
                color: #059669;
            }

            .status-badge.pending {
                background: #fffbeb;
                color: #d97706;
            }

            .status-badge.rejected {
                background: #fef2f2;
                color: #dc2626;
            }

            .job-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 16px;
            }

            .meta-item {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
                color: #64748b;
            }

            .meta-item i {
                font-size: 14px;
                color: #94a3b8;
            }

            .job-stats {
                display: flex;
                gap: 24px;
                flex-shrink: 0;
            }

            .stat-item {
                text-align: center;
                padding: 8px 16px;
                background: #f8fafc;
                border-radius: 8px;
                min-width: 80px;
            }

            .stat-number {
                display: block;
                font-size: 20px;
                font-weight: 700;
                color: #1e293b;
            }

            .stat-text {
                font-size: 12px;
                color: #64748b;
            }

            /* Rejection Notice */
            .rejection-notice {
                display: flex;
                gap: 12px;
                padding: 16px 24px;
                background: #fef2f2;
                border-top: 1px solid #fecaca;
            }

            .rejection-notice>i {
                color: #dc2626;
                font-size: 18px;
                flex-shrink: 0;
                margin-top: 2px;
            }

            .rejection-content {
                flex: 1;
            }

            .rejection-content strong {
                color: #dc2626;
                font-size: 13px;
            }

            .rejection-content p {
                margin: 4px 0 0 0;
                color: #7f1d1d;
                font-size: 13px;
                line-height: 1.5;
            }

            .read-more-btn {
                background: none;
                border: none;
                color: #dc2626;
                font-size: 13px;
                font-weight: 600;
                padding: 0;
                margin-top: 8px;
                cursor: pointer;
                text-decoration: underline;
            }

            /* Job Actions */
            .job-actions {
                display: flex;
                gap: 8px;
                padding: 16px 24px;
                background: #f8fafc;
                border-top: 1px solid #e2e8f0;
                flex-wrap: wrap;
            }

            .action-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 500;
                border: 1px solid transparent;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
            }

            .action-btn.applicants {
                background: #059669;
                color: white;
            }

            .action-btn.applicants:hover {
                background: #047857;
                color: white;
            }

            .action-btn.edit {
                background: #3b82f6;
                color: white;
            }

            .action-btn.edit:hover {
                background: #2563eb;
                color: white;
            }

            .action-btn.view-feedback {
                background: #f59e0b;
                color: white;
            }

            .action-btn.view-feedback:hover {
                background: #d97706;
            }

            .action-btn.delete {
                background: white;
                color: #dc2626;
                border-color: #fecaca;
            }

            .action-btn.delete:hover {
                background: #fef2f2;
                border-color: #dc2626;
            }

            /* Empty State */
            .empty-state {
                text-align: center;
                padding: 60px 24px;
                background: white;
                border-radius: 12px;
                border: 2px dashed #e2e8f0;
            }

            .empty-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 24px;
                background: linear-gradient(135deg, #059669, #047857);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .empty-icon i {
                font-size: 32px;
                color: white;
            }

            .empty-state h3 {
                font-size: 20px;
                font-weight: 600;
                color: #1e293b;
                margin: 0 0 8px 0;
            }

            .empty-state p {
                color: #64748b;
                margin: 0 0 24px 0;
            }

            /* Pagination */
            .pagination-wrapper {
                display: flex;
                justify-content: center;
                margin-top: 24px;
            }

            .pagination-wrapper .pagination {
                display: flex;
                gap: 4px;
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .pagination-wrapper .page-link {
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 40px;
                height: 40px;
                padding: 8px 12px;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                color: #64748b;
                font-weight: 500;
                font-size: 14px;
                text-decoration: none;
                transition: all 0.2s;
            }

            .pagination-wrapper .page-link:hover {
                background: #f1f5f9;
                border-color: #cbd5e1;
                color: #1e293b;
            }

            .pagination-wrapper .page-item.active .page-link {
                background: #059669;
                border-color: #059669;
                color: white;
            }

            .pagination-wrapper .page-item.disabled .page-link {
                background: #f8fafc;
                color: #cbd5e1;
                cursor: not-allowed;
            }

            .pagination-wrapper .page-link svg {
                width: 16px;
                height: 16px;
            }

            /* Modal Styles */
            .modal-content {
                border-radius: 12px;
                border: none;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .modal-header {
                padding: 20px 24px;
                border-bottom: 1px solid #e2e8f0;
            }

            #rejectionModal .modal-header {
                background: #fef2f2;
            }

            #rejectionModal .modal-title {
                color: #dc2626;
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
            }

            .modal-body {
                padding: 24px;
            }

            .job-title-modal {
                font-size: 16px;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 16px;
            }

            .feedback-box {
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 8px;
                padding: 16px;
                margin-bottom: 16px;
            }

            .feedback-box label {
                display: block;
                font-weight: 600;
                color: #dc2626;
                font-size: 13px;
                margin-bottom: 8px;
            }

            .feedback-box p {
                margin: 0;
                color: #1e293b;
                line-height: 1.6;
            }

            .rejection-date {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #64748b;
                font-size: 13px;
                margin-bottom: 16px;
            }

            .next-steps {
                background: #fffbeb;
                border: 1px solid #fde68a;
                border-radius: 8px;
                padding: 16px;
            }

            .next-steps h6 {
                font-weight: 600;
                color: #92400e;
                margin: 0 0 12px 0;
                font-size: 14px;
            }

            .next-steps ol {
                margin: 0;
                padding-left: 20px;
                color: #78350f;
                font-size: 13px;
                line-height: 1.8;
            }

            .modal-footer {
                padding: 16px 24px;
                border-top: 1px solid #e2e8f0;
                gap: 8px;
            }

            .delete-icon {
                width: 64px;
                height: 64px;
                margin: 0 auto 16px;
                background: #fef2f2;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .delete-icon i {
                font-size: 28px;
                color: #dc2626;
            }

            #deleteModal .modal-body h5 {
                color: #1e293b;
                margin-bottom: 8px;
            }

            #deleteModal .modal-body p {
                color: #64748b;
                margin: 0;
            }

            /* Responsive */
            @media (max-width: 1024px) {
                .stats-row {
                    grid-template-columns: repeat(2, 1fr);
                }

                .job-main {
                    flex-direction: column;
                }

                .job-stats {
                    width: 100%;
                    justify-content: flex-start;
                }
            }

            @media (max-width: 640px) {
                .jobs-page {
                    padding: 16px;
                }

                .jobs-header {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .stats-row {
                    grid-template-columns: 1fr 1fr;
                }

                .stat-card {
                    padding: 16px;
                }

                .stat-value {
                    font-size: 20px;
                }

                .filter-bar {
                    flex-direction: column;
                }

                .search-box {
                    min-width: 100%;
                }

                .filter-select {
                    width: 100%;
                }

                .job-main {
                    padding: 16px;
                }

                .job-actions {
                    padding: 12px 16px;
                }

                .action-btn {
                    flex: 1;
                    justify-content: center;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchInput');
                const statusFilter = document.getElementById('statusFilter');
                const clearSearch = document.getElementById('clearSearch');
                const filterForm = document.getElementById('filterForm');
                let searchTimeout = null;

                // Submit form on status filter change
                statusFilter.addEventListener('change', function () {
                    filterForm.submit();
                });

                // Submit form on search with debounce
                searchInput.addEventListener('input', function () {
                    clearSearch.style.display = this.value ? 'flex' : 'none';

                    // Clear previous timeout
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }

                    // Debounce search to avoid too many requests
                    searchTimeout = setTimeout(function () {
                        filterForm.submit();
                    }, 500);
                });

                // Submit form on Enter key
                searchInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (searchTimeout) {
                            clearTimeout(searchTimeout);
                        }
                        filterForm.submit();
                    }
                });

                // Clear all filters
                clearSearch.addEventListener('click', function () {
                    searchInput.value = '';
                    statusFilter.value = '';
                    clearSearch.style.display = 'none';
                    filterForm.submit();
                });

                // Initialize Bootstrap tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            });

            let currentJobId = null;

            function showRejectionModal(jobId, jobTitle, rejectionReason, rejectedAt) {
                currentJobId = jobId;

                document.getElementById('jobTitle').textContent = jobTitle;
                document.getElementById('rejectionReason').textContent = rejectionReason || 'No reason provided';
                document.getElementById('editJobBtn').href = '/employer/jobs/' + jobId + '/edit';

                if (rejectedAt) {
                    document.getElementById('rejectedAt').textContent = 'Rejected on ' + rejectedAt;
                    document.getElementById('rejectionDate').style.display = 'flex';
                } else {
                    document.getElementById('rejectionDate').style.display = 'none';
                }

                const modal = document.getElementById('rejectionModal');
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');

                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'modalBackdrop';
                document.body.appendChild(backdrop);
                backdrop.onclick = closeRejectionModal;
            }

            function closeRejectionModal() {
                const modal = document.getElementById('rejectionModal');
                const backdrop = document.getElementById('modalBackdrop');

                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');

                if (backdrop) backdrop.remove();
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeRejectionModal();
            });

            function confirmDelete(jobId) {
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                document.getElementById('deleteForm').action = `/employer/jobs/${jobId}`;
                modal.show();
            }
        </script>
    @endpush
@endsection