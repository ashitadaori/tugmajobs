@extends('layouts.admin')

@section('page_title', 'Application Details - ' . $application->user->name)

@section('content')
    <div class="container-fluid">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('admin.jobs.applicants', $application->job_id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Applicants
            </a>
        </div>

        <!-- Applicant Header -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-1">

                            @php
                                $jobSeeker = $application->user->jobSeekerProfile;
                                $profileImage = $jobSeeker && $jobSeeker->profile_photo ? asset('storage/' . $jobSeeker->profile_photo) : $application->user->profile_image;
                            @endphp
                            @if($profileImage)
                                <img src="{{ $profileImage }}" alt="{{ $application->user->name }}" class="rounded-circle"
                                    style="width: 70px; height: 70px; object-fit: cover; border: 3px solid white;"
                                    onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle bg-white align-items-center justify-content-center"
                                    style="width: 70px; height: 70px; font-size: 1.5rem; color: #667eea; display: none;">
                                    {{ strtoupper(substr($application->user->name, 0, 1)) }}
                                </div>
                            @else
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center"
                                    style="width: 70px; height: 70px; font-size: 1.5rem; color: #667eea;">
                                    {{ strtoupper(substr($application->user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-7">
                            <h3 class="mb-1" style="color: white;">{{ $application->user->name }}</h3>
                            <p class="mb-1 opacity-75">Applied for: <strong>{{ $application->job->title }}</strong></p>
                            <div class="d-flex gap-3 flex-wrap">
                                @if($jobSeeker && $jobSeeker->current_job_title)
                                    <span><i class="bi bi-briefcase"></i> {{ $jobSeeker->current_job_title }}</span>
                                @endif
                                <span><i class="bi bi-phone"></i>
                                    {{ $jobSeeker->phone ?? $application->user->phone ?? 'N/A' }}</span>
                                <span><i class="bi bi-envelope"></i> {{ $application->user->email }}</span>
                                <span><i class="bi bi-calendar"></i> Applied
                                    {{ $application->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="badge {{ $application->getStageBadgeClass() }} fs-6 px-3 py-2">
                                {{ $application->getStageName() }}
                            </span>
                            @if($application->stage_status)
                                <span class="badge {{ $application->getStageStatusBadgeClass() }} fs-6 px-3 py-2 ms-2">
                                    {{ ucfirst($application->stage_status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Hiring Pipeline -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-kanban me-2"></i>Hiring Pipeline</h5>
                            <div class="d-flex gap-2">
                                @if($application->stage !== 'hired' && $application->stage !== 'rejected')
                                    @if($application->stage_status === 'pending')
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="approveStage({{ $application->id }})">
                                            <i class="bi bi-check-lg me-1"></i>Approve
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="rejectApplication({{ $application->id }})">
                                            <i class="bi bi-x-lg me-1"></i>Reject
                                        </button>
                                    @elseif($application->stage_status === 'approved' && $application->canAdvanceStage())
                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="advanceStage({{ $application->id }})">
                                            <i class="bi bi-arrow-right me-1"></i>Advance
                                        </button>
                                    @endif
                                    @if($application->stage === 'interview' && !$application->hasScheduledInterview())
                                        <button type="button" class="btn btn-info btn-sm text-white"
                                            onclick="showScheduleInterviewModal()">
                                            <i class="bi bi-calendar-plus me-1"></i>Schedule Interview
                                        </button>
                                    @endif
                                    @if($application->stage === 'interview' && $application->hasScheduledInterview())
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="markAsHired({{ $application->id }})">
                                            <i class="bi bi-trophy me-1"></i>Mark as Hired
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Progress Bar -->
                            <div class="position-relative mb-4" style="padding: 0 40px;">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $application->getProgressPercentage() }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between position-absolute w-100"
                                    style="top: -12px; left: 0;">
                                    @php
                                        $stages = ['application' => 'Application', 'requirements' => 'Documents', 'interview' => 'Interview', 'hired' => 'Hired'];
                                        $currentStageIndex = array_search($application->stage, array_keys($stages));
                                    @endphp
                                    @foreach($stages as $stageKey => $stageName)
                                        @php
                                            $stageIndex = array_search($stageKey, array_keys($stages));
                                            $isCompleted = $stageIndex < $currentStageIndex || ($stageIndex == $currentStageIndex && $application->stage_status === 'approved');
                                            $isActive = $stageKey === $application->stage;
                                        @endphp
                                        <div class="text-center" style="width: 25%;">
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2
                                                                                                {{ $isCompleted ? 'bg-success text-white' : ($isActive ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                                                style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                @if($isCompleted)
                                                    <i class="bi bi-check"></i>
                                                @else
                                                    {{ $stageIndex + 1 }}
                                                @endif
                                            </div>
                                            <div class="small {{ $isActive ? 'fw-bold' : '' }}">{{ $stageName }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Details -->
                    @if($application->hasScheduledInterview())
                        <div class="card mb-4 border-info">
                            <div class="card-header bg-info bg-opacity-10 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-info"><i class="bi bi-camera-video me-2"></i>Interview Scheduled</h5>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="showRescheduleModal()">
                                    <i class="bi bi-calendar2-x me-1"></i>Reschedule
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-calendar-event me-2"></i>Date:</strong>
                                        <span class="d-block">{{ $application->interview_date->format('l, F d, Y') }}</span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-clock me-2"></i>Time:</strong>
                                        <span
                                            class="d-block">{{ \Carbon\Carbon::parse($application->interview_time)->format('h:i A') }}</span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i
                                                class="bi bi-{{ $application->interview_type === 'video_call' ? 'camera-video' : ($application->interview_type === 'phone' ? 'telephone' : 'building') }} me-2"></i>Type:</strong>
                                        <span class="d-block">{{ $application->getInterviewTypeName() }}</span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        @if($application->interview_type === 'video_call')
                                            <strong><i class="bi bi-link me-2"></i>Meeting Link:</strong>
                                            <span class="d-block">
                                                <a href="{{ $application->interview_location }}" target="_blank"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i>Join Meeting
                                                </a>
                                            </span>
                                        @else
                                            <strong><i class="bi bi-geo-alt me-2"></i>Location:</strong>
                                            <span class="d-block">{{ $application->interview_location }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($application->interview_notes)
                                    <div class="alert alert-info mt-2 mb-0">
                                        <strong>Notes:</strong> {{ $application->interview_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Cover Letter -->
                    @if($application->cover_letter)
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Cover Letter</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">
                                    {{ $application->cover_letter }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Status History -->
                    @if($application->statusHistory->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    @foreach($application->statusHistory as $history)
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'interview_scheduled' => 'info',
                                                'interview_rescheduled' => 'warning',
                                                'hired' => 'success',
                                            ];
                                            $color = $statusColors[$history->status] ?? 'secondary';
                                        @endphp
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-{{ $color }}"></div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between">
                                                    <strong
                                                        class="text-{{ $color }}">{{ ucwords(str_replace('_', ' ', $history->status)) }}</strong>
                                                    <small
                                                        class="text-muted">{{ $history->created_at->format('M d, Y h:i A') }}</small>
                                                </div>
                                                @if($history->notes)
                                                    <p class="mb-0 mt-1 text-muted">{{ $history->notes }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            @if($application->resume)
                                <a href="{{ asset('storage/' . $application->resume) }}" target="_blank"
                                    class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-file-pdf me-2"></i>View Resume
                                </a>
                            @endif
                            @if($application->hasSubmittedRequirements())
                                <a href="{{ route('admin.jobs.application.documents', $application->id) }}"
                                    class="btn btn-outline-primary w-100 mb-2">
                                    <i class="bi bi-folder2-open me-2"></i>View Documents
                                    ({{ count($application->submitted_documents) }})
                                </a>
                            @endif
                            <a href="{{ route('admin.users.show', $application->user_id) }}"
                                class="btn btn-outline-secondary w-100">
                                <i class="bi bi-person me-2"></i>View Full Profile
                            </a>
                        </div>
                    </div>

                    <!-- Job Info -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Job Information</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Position:</strong> {{ $application->job->title }}</p>
                            <p class="mb-2"><strong>Location:</strong> {{ $application->job->location }}</p>
                            <p class="mb-2"><strong>Type:</strong> {{ $application->job->jobType->name ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Category:</strong> {{ $application->job->category->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Requirements Status -->
                    @if($application->job->jobRequirements && $application->job->jobRequirements->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Required Documents</h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    @foreach($application->job->jobRequirements as $requirement)
                                        @php
                                            $submitted = $application->submitted_documents && isset($application->submitted_documents[$requirement->id]);
                                        @endphp
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $requirement->name }}
                                            @if($submitted)
                                                <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                            @else
                                                <span class="badge bg-secondary"><i class="bi bi-dash"></i></span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes Modal -->
        <div class="modal fade" id="notesModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notesModalTitle">Add Notes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" id="actionNotes" rows="4"
                            placeholder="Enter notes or feedback for the applicant..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .timeline {
                position: relative;
                padding-left: 30px;
            }

            .timeline::before {
                content: '';
                position: absolute;
                left: 8px;
                top: 0;
                bottom: 0;
                width: 2px;
                background: #e5e7eb;
            }

            .timeline-item {
                position: relative;
                padding-bottom: 20px;
            }

            .timeline-item:last-child {
                padding-bottom: 0;
            }

            .timeline-marker {
                position: absolute;
                left: -26px;
                top: 5px;
                width: 12px;
                height: 12px;
                border-radius: 50%;
            }

            .timeline-content {
                padding-left: 10px;
            }
        </style>

        <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
        <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
        <script
            src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
        <link rel="stylesheet"
            href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css"
            type="text/css">

        <style>
            .mapboxgl-ctrl-geocoder {
                min-width: 100% !important;
                width: 100% !important;
                max-width: none !important;
                box-shadow: none !important;
                margin: 0 !important;
            }

            .mapboxgl-ctrl-geocoder--input {
                padding-left: 36px !important;
            }
        </style>

@endsection

    @push('scripts')
        <script>
            let currentAction = null;

            function approveStage(applicationId) {
                currentAction = 'approve';
                document.getElementById('notesModalTitle').textContent = 'Approve Stage';
                document.getElementById('actionNotes').value = '';
                new bootstrap.Modal(document.getElementById('notesModal')).show();
            }

            function rejectApplication(applicationId) {
                currentAction = 'reject';
                document.getElementById('notesModalTitle').textContent = 'Reject Application';
                document.getElementById('actionNotes').placeholder = 'Please provide a reason for rejection...';
                document.getElementById('actionNotes').value = '';
                new bootstrap.Modal(document.getElementById('notesModal')).show();
            }

            function advanceStage(applicationId) {
                currentAction = 'advance';
                document.getElementById('notesModalTitle').textContent = 'Advance to Next Stage';
                document.getElementById('actionNotes').value = '';
                new bootstrap.Modal(document.getElementById('notesModal')).show();
            }

            document.getElementById('confirmActionBtn').addEventListener('click', function () {
                const notes = document.getElementById('actionNotes').value;
                bootstrap.Modal.getInstance(document.getElementById('notesModal')).hide();

                fetch(`{{ route('admin.jobs.application.updateStage', $application->id) }}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: currentAction, notes: notes })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showToast('success', data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast('error', data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred');
                    });
            });

            function showScheduleInterviewModal() {
                const today = new Date().toISOString().split('T')[0];
                const modalHTML = `
                                    <div class="modal fade" id="interviewModal" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Schedule Interview</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="interviewForm" onsubmit="return false;">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Interview Date *</label>
                                                                <input type="date" class="form-control" id="interview_date" min="${today}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Interview Time *</label>
                                                                <input type="time" class="form-control" id="interview_time" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Interview Type *</label>
                                                                <select class="form-select" id="interview_type" required>
                                                                    <option value="">Select type...</option>
                                                                    <option value="in_person">In Person</option>
                                                                    <option value="video_call">Video Call</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" id="interview_location_label">Location/Link *</label>
                                                                <input type="text" class="form-control" id="interview_location" required placeholder="Office address or meeting link" autocomplete="off">
                                                                <div id="interview_geocoder" style="display: none; border-radius: 8px; border: 1px solid #dee2e6; overflow: hidden; margin-bottom: 8px;"></div>
                                                                <div id="interview_map" style="display: none; width: 100%; height: 200px; border-radius: 8px; border: 1px solid #dee2e6;"></div>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Additional Notes</label>
                                                                <textarea class="form-control" id="interview_notes" rows="3" placeholder="Any instructions for the candidate..."></textarea>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-primary" onclick="submitInterview()">
                                                        <i class="bi bi-calendar-check me-1"></i>Schedule
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;

                document.getElementById('interviewModal')?.remove();
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                new bootstrap.Modal(document.getElementById('interviewModal')).show();

                // Initialize dynamic location field
                if (typeof window.updateLocationField === 'function') {
                    window.updateLocationField('interview_type', 'interview_location_label', 'interview_location');
                }
            }

            function submitInterview() {
                const form = document.getElementById('interviewForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Sync geocoder input if In Person
                if (document.getElementById('interview_type').value === 'in_person') {
                    const geocoderInput = document.querySelector('#interview_geocoder input');
                    if (geocoderInput && geocoderInput.value) {
                        document.getElementById('interview_location').value = geocoderInput.value;
                    }
                }

                const data = {
                    interview_date: document.getElementById('interview_date').value,
                    interview_time: document.getElementById('interview_time').value,
                    interview_type: document.getElementById('interview_type').value,
                    interview_location: document.getElementById('interview_location').value,
                    interview_notes: document.getElementById('interview_notes').value
                };

                bootstrap.Modal.getInstance(document.getElementById('interviewModal')).hide();

                fetch(`{{ route('admin.jobs.application.scheduleInterview', $application->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showToast('success', data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast('error', data.message || 'Failed to schedule interview');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred');
                    });
            }

            function showRescheduleModal() {
                const today = new Date().toISOString().split('T')[0];
                const currentTime = '{{ $application->interview_time ?? "" }}';
                const currentType = '{{ $application->interview_type ?? "" }}';
                const currentLocation = `{{ $application->interview_location ?? "" }}`;

                const modalHTML = `
                                    <div class="modal fade" id="rescheduleModal" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning bg-opacity-10">
                                                    <h5 class="modal-title text-warning"><i class="bi bi-calendar2-x me-2"></i>Reschedule Interview</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning mb-3">
                                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                                        <strong>Note:</strong> The applicant will be notified about this schedule change.
                                                    </div>
                                                    <form id="rescheduleForm" onsubmit="return false;">
                                                        <div class="row g-3">
                                                            <div class="col-12">
                                                                <label class="form-label">Reason for Rescheduling *</label>
                                                                <textarea class="form-control" id="reschedule_reason" rows="2" required placeholder="Please provide a reason..."></textarea>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">New Interview Date *</label>
                                                                <input type="date" class="form-control" id="reschedule_date" min="${today}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">New Interview Time *</label>
                                                                <input type="time" class="form-control" id="reschedule_time" value="${currentTime}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Interview Type *</label>
                                                                <select class="form-select" id="reschedule_type" required>
                                                                    <option value="">Select type...</option>
                                                                    <option value="in_person" ${currentType === 'in_person' ? 'selected' : ''}>In Person</option>
                                                                    <option value="video_call" ${currentType === 'video_call' ? 'selected' : ''}>Video Call</option>
                                                                </select>
                                                            </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label" id="reschedule_location_label">Location/Link *</label>
                                                            <input type="text" class="form-control" id="reschedule_location" value="${currentLocation}" required autocomplete="off">
                                                            <div id="reschedule_geocoder" style="display: none; border-radius: 8px; border: 1px solid #dee2e6; overflow: hidden; margin-bottom: 8px;"></div>
                                                            <div id="reschedule_map" style="display: none; width: 100%; height: 200px; border-radius: 8px; border: 1px solid #dee2e6;"></div>
                                                        </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Additional Notes</label>
                                                                <textarea class="form-control" id="reschedule_notes" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-warning" onclick="submitReschedule()">
                                                        <i class="bi bi-calendar-check me-1"></i>Reschedule
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;

                document.getElementById('rescheduleModal')?.remove();
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                new bootstrap.Modal(document.getElementById('rescheduleModal')).show();

                // Initialize dynamic location field
                if (typeof window.updateLocationField === 'function') {
                    window.updateLocationField('reschedule_type', 'reschedule_location_label', 'reschedule_location');
                }
            }

            function submitReschedule() {
                const form = document.getElementById('rescheduleForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Sync geocoder input if In Person
                if (document.getElementById('reschedule_type').value === 'in_person') {
                    const geocoderInput = document.querySelector('#reschedule_geocoder input');
                    if (geocoderInput && geocoderInput.value) {
                        document.getElementById('reschedule_location').value = geocoderInput.value;
                    }
                }

                const data = {
                    interview_date: document.getElementById('reschedule_date').value,
                    interview_time: document.getElementById('reschedule_time').value,
                    interview_type: document.getElementById('reschedule_type').value,
                    interview_location: document.getElementById('reschedule_location').value,
                    interview_notes: document.getElementById('reschedule_notes').value,
                    reschedule_reason: document.getElementById('reschedule_reason').value
                };

                bootstrap.Modal.getInstance(document.getElementById('rescheduleModal')).hide();

                fetch(`{{ route('admin.jobs.application.rescheduleInterview', $application->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showToast('success', data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast('error', data.message || 'Failed to reschedule');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred');
                    });
            }

            function markAsHired(applicationId) {
                if (confirm('Mark this applicant as hired?')) {
                    fetch(`{{ route('admin.jobs.application.markHired', $application->id) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ notes: '' })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                showToast('success', data.message);
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showToast('error', data.message || 'An error occurred');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('error', 'An error occurred');
                        });
                }
            }

            function showToast(type, message) {
                // Simple alert fallback - you can replace with your toast implementation
                alert(message);
            }

            // Mapbox Token
            const MAPBOX_TOKEN = 'pk.eyJ1Ijoia2hlbnJpY3RoIiwiYSI6ImNtazM2azJyeDBvenIzaXBlb2ZlYThvY3cifQ.XyLAFaZh57ALvtctyCg1MQ';

            // Store map instances
            window.mapboxMaps = window.mapboxMaps || {};

            // Initialize Geocoder and Map
            function initializeGeocoder(containerId, inputId, initialValue = '') {
                if (!document.getElementById(containerId)) return;

                mapboxgl.accessToken = MAPBOX_TOKEN;
                const geocoder = new MapboxGeocoder({
                    accessToken: mapboxgl.accessToken,
                    types: 'address,poi',
                    placeholder: 'Search for location...',
                    mapboxgl: mapboxgl
                });

                geocoder.addTo('#' + containerId);

                // Initialize Map
                const mapContainerId = inputId === 'interview_location' ? 'interview_map' : 'reschedule_map';
                const mapContainer = document.getElementById(mapContainerId);

                let map;
                let marker;

                if (mapContainer) {
                    map = new mapboxgl.Map({
                        container: mapContainerId,
                        style: 'mapbox://styles/mapbox/streets-v11',
                        center: [125.5, 7.1],  // Default to Philippines
                        zoom: 5,
                        interactive: true
                    });

                    window.mapboxMaps[mapContainerId] = map;
                    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

                    const resizeObserver = new ResizeObserver(() => {
                        map.resize();
                    });
                    resizeObserver.observe(mapContainer);
                }

                geocoder.on('result', function (e) {
                    document.getElementById(inputId).value = e.result.place_name;

                    if (map && e.result.center) {
                        const [lng, lat] = e.result.center;
                        map.resize();
                        map.flyTo({ center: [lng, lat], zoom: 14 });

                        if (marker) marker.remove();
                        marker = new mapboxgl.Marker({ color: '#3b82f6' })
                            .setLngLat([lng, lat])
                            .addTo(map);
                    }
                });

                geocoder.on('clear', function () {
                    document.getElementById(inputId).value = '';
                });

                // Enter key handler for direct search
                setTimeout(() => {
                    const geocoderInput = document.querySelector('#' + containerId + ' input');
                    if (geocoderInput) {
                        const handleEnter = function (e) {
                            if (e.key === 'Enter' || e.keyCode === 13) {
                                e.preventDefault();
                                e.stopPropagation();

                                const query = geocoderInput.value.trim();
                                if (query) {
                                    fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${MAPBOX_TOKEN}&types=address,poi,place,locality&limit=1`)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.features && data.features.length > 0) {
                                                const result = data.features[0];
                                                geocoderInput.value = query;
                                                document.getElementById(inputId).value = query;

                                                if (map && result.center) {
                                                    const [lng, lat] = result.center;
                                                    map.resize();
                                                    map.flyTo({ center: [lng, lat], zoom: 14 });

                                                    if (marker) marker.remove();
                                                    marker = new mapboxgl.Marker({ color: '#3b82f6' })
                                                        .setLngLat([lng, lat])
                                                        .addTo(map);
                                                }
                                            }
                                        });
                                }
                                return false;
                            }
                        };

                        geocoderInput.addEventListener('keydown', handleEnter, true);
                    }
                }, 500);

                if (typeof initialValue === 'string' && initialValue.trim() !== '') {
                    setTimeout(() => {
                        geocoder.query(initialValue);
                    }, 500);
                }

                return geocoder;
            }

            // Dynamic field update based on interview type
            window.updateLocationField = function (typeSelectId, labelId, inputId) {
                const typeSelect = document.getElementById(typeSelectId);
                const label = document.getElementById(labelId);
                const input = document.getElementById(inputId);
                const geocoderContainerId = inputId === 'interview_location' ? 'interview_geocoder' : 'reschedule_geocoder';
                const geocoderContainer = document.getElementById(geocoderContainerId);

                if (!typeSelect || !label || !input) return;

                const updateField = () => {
                    const type = typeSelect.value;
                    const mapContainerId = inputId === 'interview_location' ? 'interview_map' : 'reschedule_map';
                    const mapContainer = document.getElementById(mapContainerId);

                    if (type === 'in_person') {
                        label.textContent = 'Location *';

                        // Show Geocoder and Map
                        if (geocoderContainer) {
                            geocoderContainer.style.display = 'block';
                            if (mapContainer) {
                                mapContainer.style.display = 'block';
                                setTimeout(() => {
                                    window.dispatchEvent(new Event('resize'));
                                    if (window.mapboxMaps && window.mapboxMaps[mapContainerId]) {
                                        window.mapboxMaps[mapContainerId].resize();
                                    }
                                }, 100);
                            }

                            // Hide standard input
                            input.style.display = 'none';
                            input.setAttribute('type', 'hidden');

                            if (geocoderContainer.innerHTML === '') {
                                initializeGeocoder(geocoderContainerId, inputId, input.value);
                            }
                        } else {
                            // Fallback if no geocoder container (shouldn't happen)
                            input.style.display = 'block';
                            input.setAttribute('type', 'text');
                            input.placeholder = 'Office address (e.g., Building A, Room 101)';
                        }
                    } else {
                        // Hide Geocoder and Map
                        if (geocoderContainer) {
                            geocoderContainer.style.display = 'none';
                            if (mapContainer) mapContainer.style.display = 'none';
                        }

                        // Show standard input
                        input.style.display = 'block';
                        input.setAttribute('type', 'text');
                        input.required = true;

                        if (type === 'video_call') {
                            label.textContent = 'Meeting Link *';
                            input.placeholder = 'Paste video call link here';
                        } else {
                            label.textContent = 'Location/Link *';
                            input.placeholder = 'Office address or meeting link';
                        }
                    }
                };

                typeSelect.removeEventListener('change', updateField);
                typeSelect.addEventListener('change', updateField);
                updateField();
            };

            // Fix map rendering in modals
            document.addEventListener('DOMContentLoaded', function () {
                document.body.addEventListener('shown.bs.modal', function () {
                    setTimeout(() => {
                        if (window.mapboxMaps) {
                            Object.values(window.mapboxMaps).forEach(map => map.resize());
                        }
                        window.dispatchEvent(new Event('resize'));
                    }, 200);
                }, true);
            });
        </script>
    @endpush