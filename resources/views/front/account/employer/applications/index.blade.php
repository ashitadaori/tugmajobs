@extends('layouts.employer')

@section('page_title', 'All Applications')

@section('content')
    <!-- Filters & Search -->
    <div class="ep-card ep-mb-6">
        <div class="ep-card-body">
            <form action="{{ route('employer.applications.index') }}" method="GET">
                <div class="filter-grid">
                    <!-- Search -->
                    <div class="ep-form-group" style="margin-bottom: 0;">
                        <label class="ep-form-label">Search Applicants</label>
                        <div style="position: relative;">
                            <i class="bi bi-search"
                                style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--ep-gray-400);"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search by name or email..." class="ep-form-input" style="padding-left: 40px;">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="ep-form-group" style="margin-bottom: 0;">
                        <label class="ep-form-label">Status</label>
                        <select name="status" class="ep-form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="shortlisted" {{ request('status') == 'shortlisted' ? 'selected' : '' }}>Shortlisted
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Job Filter -->
                    <div class="ep-form-group" style="margin-bottom: 0;">
                        <label class="ep-form-label">Job Position</label>
                        <select name="job" class="ep-form-select">
                            <option value="">All Jobs</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" {{ request('job') == $job->id ? 'selected' : '' }}>
                                    {{ Str::limit($job->title, 30) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Apply Filter Button -->
                    <div class="ep-form-group" style="margin-bottom: 0;">
                        <label class="ep-form-label">&nbsp;</label>
                        <button type="submit" class="ep-btn ep-btn-primary" style="width: 100%;">
                            <i class="bi bi-funnel"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="ep-stats-grid stats-5-col ep-mb-6">
        <div class="ep-stat-card compact">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="stat-icon-sm primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="ep-stat-label" style="margin-bottom: 2px;">Total</div>
                    <div class="ep-stat-value" style="font-size: 1.5rem;">{{ $applications->total() }}</div>
                </div>
            </div>
        </div>
        <div class="ep-stat-card compact">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="stat-icon-sm warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="ep-stat-label" style="margin-bottom: 2px;">Pending</div>
                    <div class="ep-stat-value" style="font-size: 1.5rem;">
                        {{ $applications->where('status', 'pending')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="ep-stat-card compact">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="stat-icon-sm success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="ep-stat-label" style="margin-bottom: 2px;">Approved</div>
                    <div class="ep-stat-value" style="font-size: 1.5rem;">
                        {{ $applications->where('status', 'approved')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="ep-stat-card compact">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="stat-icon-sm info">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="ep-stat-label" style="margin-bottom: 2px;">Shortlisted</div>
                    <div class="ep-stat-value" style="font-size: 1.5rem;">
                        {{ $applications->where('shortlisted', true)->count() }}</div>
                </div>
            </div>
        </div>
        <div class="ep-stat-card compact">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="stat-icon-sm danger">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div>
                    <div class="ep-stat-label" style="margin-bottom: 2px;">Rejected</div>
                    <div class="ep-stat-value" style="font-size: 1.5rem;">
                        {{ $applications->where('status', 'rejected')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="ep-card" style="overflow: visible !important;">
        <div class="ep-card-header">
            <h3 class="ep-card-title">
                <i class="bi bi-people"></i>
                Applications
            </h3>
            <div style="display: flex; gap: 8px;">
                @if($applications->count() > 0)
                    <button class="ep-btn ep-btn-outline ep-btn-sm" id="exportBtn">
                        <i class="bi bi-download"></i>
                        Export
                    </button>
                @endif
            </div>
        </div>
        <div class="ep-card-body" style="padding: 0; overflow: visible !important;">
            @if($applications->count() > 0)
                <div class="ep-table-wrapper" style="overflow: visible !important;">
                    <table class="ep-table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Applicant</th>
                                <th>Job Position</th>
                                <th>Experience</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Applied Date</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input application-checkbox"
                                            value="{{ $application->id }}">
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            @php
                                                $image = $application->user->image;
                                                $socialImage = $application->user->profile_image;
                                                
                                                $imgSrc = null;
                                                if ($image && file_exists(public_path('profile_img/thumb/' . $image))) {
                                                    $imgSrc = asset('profile_img/thumb/' . $image);
                                                } elseif ($socialImage) {
                                                    $imgSrc = $socialImage;
                                                }
                                            @endphp
            
                                            @if(Auth::user()->isAdmin())
                                                @if($imgSrc)
                                                    <img src="{{ $imgSrc }}" 
                                                         alt="{{ $application->user->name }}" 
                                                         class="applicant-avatar"
                                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="applicant-avatar-placeholder" style="display: none;">
                                                        <i class="bi bi-person-fill" style="font-size: 20px;"></i>
                                                    </div>
                                                @else
                                                    <div class="applicant-avatar-placeholder">
                                                        <i class="bi bi-person-fill" style="font-size: 20px;"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div style="font-weight: 600; color: var(--ep-gray-800); margin-bottom: 2px;">{{ $application->user->name }}</div>
                                                    <div style="font-size: 12px; color: var(--ep-gray-500);">{{ $application->user->email }}</div>
                                                </div>
                                            @else
                                                <div class="applicant-avatar-placeholder" style="background: #eee;">
                                                    <i class="bi bi-person-fill" style="font-size: 20px; color: #999;"></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; color: var(--ep-gray-800); margin-bottom: 2px;">Applicant</div>
                                                    <div style="font-size: 11px; color: #777; font-style: italic;">Hidden</div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500; color: var(--ep-gray-700);">
                                            {{ Str::limit($application->job->title, 35) }}</div>
                                        <div style="font-size: 12px; color: var(--ep-gray-500);">
                                            <i class="bi bi-geo-alt"></i> {{ $application->job->location }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($application->user->jobseeker && $application->user->jobseeker->experience)
                                            <span class="ep-badge ep-badge-gray">{{ $application->user->jobseeker->experience }}</span>
                                        @else
                                            <span style="color: var(--ep-gray-400); font-size: 12px;">N/A</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @php
                                            $status = $application->status ?? 'pending';
                                            $statusConfig = [
                                                'pending' => ['class' => 'ep-badge-warning', 'icon' => 'hourglass-split'],
                                                'approved' => ['class' => 'ep-badge-success', 'icon' => 'check-circle-fill'],
                                                'rejected' => ['class' => 'ep-badge-danger', 'icon' => 'x-circle-fill'],
                                            ];
                                            $config = $statusConfig[$status] ?? $statusConfig['pending'];
                                        @endphp
                                        <span class="ep-badge {{ $config['class'] }}">
                                            <i class="bi bi-{{ $config['icon'] }}"></i>
                                            {{ ucfirst($status) }}
                                        </span>
                                        @if($application->shortlisted)
                                            <span class="ep-badge ep-badge-info" style="margin-left: 4px;">
                                                <i class="bi bi-star-fill"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: center; color: var(--ep-gray-600);">
                                        {{ $application->created_at->format('M d, Y') }}
                                        <div style="font-size: 11px; color: var(--ep-gray-400);">
                                            {{ $application->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                            <a href="{{ route('employer.applications.show', $application->id) }}"
                                                class="ep-btn ep-btn-icon ep-btn-primary ep-btn-sm" title="View Application">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <div class="dropdown">
                                                <button class="ep-btn ep-btn-icon ep-btn-outline ep-btn-sm dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                    style="padding: 6px 8px;">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <h6 class="dropdown-header">Actions</h6>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                            onclick="toggleShortlist({{ $application->id }})">
                                                            <i
                                                                class="bi bi-star{{ $application->shortlisted ? '-fill text-warning' : '' }} me-2"></i>
                                                            {{ $application->shortlisted ? 'Remove from Shortlist' : 'Add to Shortlist' }}
                                                        </a>
                                                    </li>
                                                    @if(Auth::user()->isAdmin() && $application->resume)
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ Storage::url('resumes/' . $application->resume) }}"
                                                                target="_blank">
                                                                <i class="bi bi-download me-2"></i>
                                                                Download Resume
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <h6 class="dropdown-header">Change Status</h6>
                                                    </li>
                                                    @if($application->status !== 'approved')
                                                        <li>
                                                            <a class="dropdown-item text-success" href="javascript:void(0)"
                                                                onclick="updateStatus({{ $application->id }}, 'approved')">
                                                                <i class="bi bi-check-circle me-2"></i>
                                                                Approve
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if($application->status !== 'rejected')
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                                onclick="updateStatus({{ $application->id }}, 'rejected')">
                                                                <i class="bi bi-x-circle me-2"></i>
                                                                Reject
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($applications->hasPages())
                    <div class="ep-card-footer">
                        <div
                            style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                            <div style="font-size: 14px; color: var(--ep-gray-500);">
                                Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of
                                {{ $applications->total() }} applications
                            </div>
                            <div class="ep-pagination">
                                {{ $applications->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="ep-empty-state">
                    <div class="ep-empty-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h4 class="ep-empty-title">No Applications Found</h4>
                    <p class="ep-empty-description">
                        @if(request()->hasAny(['search', 'status', 'job']))
                            No applications match your filters. Try adjusting your search criteria.
                        @else
                            You haven't received any applications yet. Post a job to start receiving applications.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'status', 'job']))
                        <a href="{{ route('employer.applications.index') }}" class="ep-btn ep-btn-outline">
                            <i class="bi bi-x-circle"></i>
                            Clear Filters
                        </a>
                    @else
                        <a href="{{ route('employer.jobs.create') }}" class="ep-btn ep-btn-primary">
                            <i class="bi bi-plus-circle"></i>
                            Post a Job
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalTitle">Provide Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="feedbackModalLabel" style="color: var(--ep-gray-600); margin-bottom: 16px;"></p>
                    <textarea class="ep-form-textarea" id="feedbackText" rows="4" maxlength="500"
                        placeholder="Enter your feedback..."></textarea>
                    <small style="color: var(--ep-gray-500); display: block; margin-top: 8px;">Maximum 500
                        characters</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="ep-btn" id="submitFeedbackBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .filter-grid {
                display: grid;
                grid-template-columns: 2fr 1fr 1fr auto;
                gap: 16px;
                align-items: end;
            }

            .stats-5-col {
                grid-template-columns: repeat(5, 1fr);
            }

            .ep-stat-card.compact {
                padding: 16px;
            }

            .stat-icon-sm {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
            }

            .stat-icon-sm.primary {
                background: var(--ep-primary-50);
                color: var(--ep-primary);
            }

            .stat-icon-sm.success {
                background: var(--ep-success-bg);
                color: var(--ep-success);
            }

            .stat-icon-sm.warning {
                background: var(--ep-warning-bg);
                color: var(--ep-warning);
            }

            .stat-icon-sm.danger {
                background: var(--ep-danger-bg);
                color: var(--ep-danger);
            }

            .stat-icon-sm.info {
                background: var(--ep-info-bg);
                color: var(--ep-info);
            }

            .applicant-avatar {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                object-fit: cover;
            }

            .applicant-avatar-placeholder {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: var(--ep-primary-50);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--ep-primary);
                font-weight: 600;
                font-size: 16px;
            }

            .form-check-input {
                width: 18px;
                height: 18px;
                cursor: pointer;
            }

            .dropdown-toggle::after {
                display: none;
            }

            /* Ensure icon doesn't block button clicks */
            .dropdown-toggle i {
                pointer-events: none;
            }

            /* Dropdown Menu Styles */
            .dropdown-menu {
                min-width: 200px;
                border: 1px solid var(--ep-gray-200);
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
                padding: 8px 0;
                background: white;
                z-index: 1050;
            }

            .dropdown-header {
                font-size: 11px;
                font-weight: 600;
                color: var(--ep-gray-500);
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 8px 16px;
                margin: 0;
            }

            .dropdown-item {
                display: flex;
                align-items: center;
                padding: 10px 16px;
                font-size: 14px;
                color: var(--ep-gray-700);
                text-decoration: none;
                transition: all 0.2s ease;
                cursor: pointer;
            }

            .dropdown-item:hover {
                background: var(--ep-gray-50);
                color: var(--ep-gray-900);
            }

            .dropdown-item i {
                font-size: 16px;
                width: 20px;
            }

            .dropdown-item.text-success {
                color: var(--ep-success);
            }

            .dropdown-item.text-success:hover {
                background: var(--ep-success-bg);
                color: var(--ep-success);
            }

            .dropdown-item.text-danger {
                color: var(--ep-danger);
            }

            .dropdown-item.text-danger:hover {
                background: var(--ep-danger-bg);
                color: var(--ep-danger);
            }

            .dropdown-item.text-warning {
                color: var(--ep-warning);
            }

            .dropdown-divider {
                border-top: 1px solid var(--ep-gray-200);
                margin: 8px 0;
            }

            /* Dropdown styling and positioning fix */
            .ep-table .dropdown {
                position: static;
            }

            .ep-table .dropdown-menu {
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 12px !important;
                min-width: 200px;
                position: absolute;
                z-index: 1050;
            }

            /* Ensure table cells don't clip dropdowns */
            .ep-table td {
                position: static;
            }

            /* Remove card overflow clipping */
            .ep-card {
                overflow: visible !important;
            }

            .ep-card-body {
                overflow: visible !important;
            }

            .ep-table-wrapper {
                overflow-x: auto;
                overflow-y: visible;
            }

            @media (max-width: 1200px) {
                .stats-5-col {
                    grid-template-columns: repeat(3, 1fr);
                }

                .filter-grid {
                    grid-template-columns: 1fr 1fr;
                }
            }

            @media (max-width: 768px) {
                .stats-5-col {
                    grid-template-columns: repeat(2, 1fr);
                }

                .filter-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 576px) {
                .stats-5-col {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            let currentApplicationId = null;
            let currentStatus = null;

            document.addEventListener('DOMContentLoaded', function () {
                // Select all checkbox
                const selectAll = document.getElementById('selectAll');
                const checkboxes = document.querySelectorAll('.application-checkbox');

                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                }

                // Individual checkbox change
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                        if (selectAll) {
                            selectAll.checked = allChecked;
                            selectAll.indeterminate = someChecked && !allChecked;
                        }
                    });
                });

                // Manually initialize Bootstrap dropdowns for the table
                var dropdownElementList = document.querySelectorAll('.ep-table [data-bs-toggle="dropdown"]');
                dropdownElementList.forEach(function (dropdownToggle) {
                    if (typeof bootstrap !== 'undefined') {
                        new bootstrap.Dropdown(dropdownToggle);
                        console.log('Dropdown initialized for:', dropdownToggle);
                    }
                });
            });

            function updateStatus(applicationId, status) {
                currentApplicationId = applicationId;
                currentStatus = status;

                const modalTitle = status === 'rejected' ? 'Reject Application' : 'Approve Application';
                const modalLabel = status === 'rejected'
                    ? 'Please provide feedback to help the candidate improve (optional):'
                    : 'Add a message for the candidate (optional):';
                const btnClass = status === 'rejected' ? 'ep-btn-danger' : 'ep-btn-success';
                const btnText = status === 'rejected' ? 'Reject' : 'Approve';

                document.getElementById('feedbackModalTitle').textContent = modalTitle;
                document.getElementById('feedbackModalLabel').textContent = modalLabel;
                document.getElementById('feedbackText').value = '';

                const submitBtn = document.getElementById('submitFeedbackBtn');
                submitBtn.className = 'ep-btn ' + btnClass;
                submitBtn.textContent = btnText;
                submitBtn.onclick = submitStatusUpdate;

                const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
                modal.show();
            }

            function submitStatusUpdate() {
                const feedback = document.getElementById('feedbackText').value.trim();

                fetch(`/employer/applications/${currentApplicationId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: currentStatus,
                        notes: feedback
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
                        modal.hide();

                        if (data.status) {
                            showToast('success', data.message || 'Application status updated successfully!');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('error', data.message || 'Failed to update status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred while updating the status.');
                    });
            }

            function toggleShortlist(applicationId) {
                fetch(`{{ url('/employer/applications') }}/${applicationId}/shortlist`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showToast('success', data.message);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('error', data.message || 'Failed to update shortlist status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred while updating the shortlist');
                    });
            }

            function showToast(type, message) {
                if (typeof Toastify !== 'undefined') {
                    Toastify({
                        text: message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: type === 'success' ? '#059669' : '#dc2626',
                            borderRadius: '8px',
                            padding: '12px 20px',
                            fontFamily: 'Inter, sans-serif'
                        }
                    }).showToast();
                } else {
                    alert(message);
                }
            }
        </script>
    @endpush
@endsection