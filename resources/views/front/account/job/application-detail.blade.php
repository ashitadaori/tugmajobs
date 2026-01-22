@extends('layouts.jobseeker')

@section('page-title', 'Application Details - ' . $application->job->title)

@section('jobseeker-content')
    <div class="application-detail-page">
        {{-- Back Button --}}
        <div class="mb-4">
            <a href="{{ route('account.myJobApplications') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Applications
            </a>
        </div>

        {{-- Job Header Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $application->job->title }}</h3>
                        <div class="d-flex flex-wrap gap-3 text-muted">
                            <span><i class="fas fa-building me-1"></i>
                                {{ $application->job->company->name ?? $application->job->employer->employerProfile->company_name ?? 'Company' }}</span>
                            <span><i class="fas fa-map-marker-alt me-1"></i> {{ $application->job->location }}</span>
                            <span><i class="fas fa-briefcase me-1"></i>
                                {{ $application->job->jobType->name ?? 'Full-time' }}</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i> Applied
                                {{ $application->applied_date ? $application->applied_date->format('M d, Y') : $application->created_at->format('M d, Y') }}
                            </small>
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
            {{-- Left Column: Progress & Timeline --}}
            <div class="col-lg-8">
                {{-- Progress Tracker --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2 text-primary"></i> Application Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress-tracker">
                            @php
                                $stages = [
                                    ['key' => 'application', 'name' => 'Application', 'icon' => 'fa-file-alt'],
                                    ['key' => 'requirements', 'name' => 'Documents', 'icon' => 'fa-folder-open'],
                                    ['key' => 'interview', 'name' => 'Interview', 'icon' => 'fa-user-tie'],
                                    ['key' => 'hired', 'name' => 'Hired', 'icon' => 'fa-check-circle'],
                                ];
                                $currentStageIndex = array_search($application->stage, array_column($stages, 'key'));
                                if ($application->stage === 'rejected') {
                                    $currentStageIndex = -1; // Mark as rejected
                                }
                            @endphp

                            <div class="d-flex justify-content-between position-relative">
                                <div class="progress-line"></div>
                                @foreach($stages as $index => $stage)
                                    @php
                                        $isCompleted = $currentStageIndex !== false && $index < $currentStageIndex;
                                        $isCurrent = $application->stage === $stage['key'];
                                        $isRejected = $application->stage === 'rejected';
                                    @endphp
                                    <div
                                        class="progress-step text-center {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }} {{ $isRejected && $index == 0 ? 'rejected' : '' }}">
                                        <div
                                            class="step-icon {{ $isCompleted ? 'bg-success text-white' : ($isCurrent ? ($application->stage_status === 'rejected' ? 'bg-danger text-white' : 'bg-primary text-white') : 'bg-light text-muted') }}">
                                            <i class="fas {{ $stage['icon'] }}"></i>
                                        </div>
                                        <div class="step-label mt-2">{{ $stage['name'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Employer Messages / Status History --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-comments me-2 text-primary"></i> Messages & Updates from Employer
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($application->statusHistory->count() > 0)
                            <div class="timeline">
                                @foreach($application->statusHistory as $history)
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'application_approved' => 'success',
                                            'requirements_approved' => 'success',
                                            'interview_approved' => 'success',
                                            'interview_scheduled' => 'info',
                                            'interview_rescheduled' => 'warning',
                                            'hired' => 'success',
                                            'documents_approved' => 'success',
                                            'stage_approved' => 'success'
                                        ];
                                        $statusIcons = [
                                            'pending' => 'fa-clock',
                                            'approved' => 'fa-check-circle',
                                            'rejected' => 'fa-times-circle',
                                            'application_approved' => 'fa-check',
                                            'requirements_approved' => 'fa-folder-open',
                                            'interview_approved' => 'fa-user-tie',
                                            'interview_scheduled' => 'fa-calendar-check',
                                            'interview_rescheduled' => 'fa-calendar-alt',
                                            'hired' => 'fa-handshake',
                                            'documents_approved' => 'fa-file-check',
                                            'stage_approved' => 'fa-check-circle'
                                        ];
                                        $color = $statusColors[$history->status] ?? 'secondary';
                                        $icon = $statusIcons[$history->status] ?? 'fa-info-circle';
                                    @endphp
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-{{ $color }}">
                                            <i class="fas {{ $icon }} text-white"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 fw-bold text-{{ $color }}">
                                                    {{ ucwords(str_replace('_', ' ', $history->status)) }}
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $history->created_at->format('M d, Y h:i A') }}
                                                </small>
                                            </div>
                                            @if($history->notes)
                                                <div
                                                    class="message-box p-2 rounded {{ $color === 'danger' ? 'bg-danger-subtle' : ($color === 'success' ? 'bg-success-subtle' : 'bg-light') }}">
                                                    <p class="mb-0">
                                                        <i class="fas fa-quote-left text-muted me-1 small"></i>
                                                        {{ $history->notes }}
                                                    </p>
                                                </div>
                                            @endif
                                            @if($history->updatedByUser)
                                                <small class="text-muted d-block mt-2">
                                                    <i class="fas fa-user me-1"></i> Updated by: {{ $history->updatedByUser->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No messages or updates yet. The employer will update your application
                                    soon.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Details --}}
            <div class="col-lg-4">
                {{-- Interview Details (if scheduled) --}}
                @if($application->hasScheduledInterview())
                    <div class="card interview-card border-0 shadow-sm mb-3">
                        <div class="card-header interview-header">
                            <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Interview Scheduled</h6>
                        </div>
                        <div class="card-body py-2 px-3">
                            <div class="interview-detail-item">
                                <div class="detail-icon"><i class="fas fa-calendar"></i></div>
                                <div class="detail-content">
                                    <span class="detail-label">Date</span>
                                    <span
                                        class="detail-value">{{ $application->interview_date ? $application->interview_date->format('l, M d, Y') : 'TBD' }}</span>
                                </div>
                            </div>
                            <div class="interview-detail-item">
                                <div class="detail-icon"><i class="fas fa-clock"></i></div>
                                <div class="detail-content">
                                    <span class="detail-label">Time</span>
                                    <span class="detail-value">
                                        @if($application->interview_time)
                                            {{ \Carbon\Carbon::parse($application->interview_time)->format('h:i A') }}
                                        @else
                                            TBD
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="interview-detail-item">
                                <div class="detail-icon"><i class="fas fa-video"></i></div>
                                <div class="detail-content">
                                    <span class="detail-label">Type</span>
                                    <span class="detail-value">{{ $application->getInterviewTypeName() }}</span>
                                </div>
                            </div>
                            @if($application->interview_type === 'video_call')
                                <div class="interview-detail-item">
                                    <div class="detail-icon"><i class="fas fa-link"></i></div>
                                    <div class="detail-content">
                                        <span class="detail-label">Meeting Link</span>
                                        @if($application->interview_location)
                                            <a href="{{ $application->interview_location }}" target="_blank"
                                                class="btn btn-sm btn-primary mt-1">
                                                <i class="fas fa-external-link-alt me-1"></i> Join Meeting
                                            </a>
                                        @else
                                            <span class="detail-value">TBD</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="interview-detail-item">
                                    <div class="detail-icon"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="detail-content w-100">
                                        <span class="detail-label">Location</span>
                                        <span class="detail-value mb-2">{{ $application->interview_location ?? 'TBD' }}</span>

                                        @if($application->interview_location)
                                            <div id="interview-map"
                                                style="width: 100%; height: 200px; border-radius: 8px; margin-top: 8px; border: 1px solid #e2e8f0;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($application->interview_notes)
                                <div class="interview-notes mt-2">
                                    <span class="detail-label"><i class="fas fa-sticky-note me-1"></i> Notes</span>
                                    <div class="notes-content">{{ $application->interview_notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Required Documents / Document Submission Stage --}}
                @if($application->stage === 'requirements')
                    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
                        <div class="card-header bg-warning bg-opacity-10 border-bottom">
                            <h5 class="mb-0 text-warning"><i class="fas fa-file-upload me-2"></i> Document Submission Required
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($application->job->jobRequirements && $application->job->jobRequirements->count() > 0)
                                <p class="text-muted mb-3">Please submit the following documents to proceed:</p>
                                <ul class="list-group list-group-flush mb-3">
                                    @foreach($application->job->jobRequirements as $requirement)
                                        @php
                                            $submitted = $application->submitted_documents && isset($application->submitted_documents[$requirement->id]);
                                        @endphp
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <span>{{ $requirement->name }}</span>
                                                @if($requirement->is_required)
                                                    <span class="badge bg-danger ms-1">Required</span>
                                                @endif
                                                @if($requirement->description)
                                                    <small class="d-block text-muted">{{ $requirement->description }}</small>
                                                @endif
                                            </div>
                                            @if($submitted)
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Submitted</span>
                                            @else
                                                <span class="badge bg-warning"><i class="fas fa-clock"></i> Pending</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                @if($application->stage_status === 'pending')
                                    <a href="{{ route('job.submitRequirements', $application->id) }}" class="btn btn-warning w-100">
                                        <i class="fas fa-upload me-2"></i> Submit Documents Now
                                    </a>
                                @elseif($application->stage_status === 'approved')
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-check-circle me-2"></i> Your documents have been approved!
                                    </div>
                                @endif
                            @else
                                {{-- No documents required for this job --}}
                                <div class="text-center py-3">
                                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                    <h6>No Documents Required</h6>
                                    <p class="text-muted mb-3">This job does not require any additional documents.</p>
                                    @if($application->stage_status === 'pending')
                                        <a href="{{ route('job.submitRequirements', $application->id) }}" class="btn btn-success">
                                            <i class="fas fa-arrow-right me-2"></i> Continue to Next Stage
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($application->job->jobRequirements && $application->job->jobRequirements->count() > 0)
                    {{-- Show submitted documents after requirements stage --}}
                    <div class="card documents-card border-0 shadow-sm mb-3">
                        <div class="card-header documents-header">
                            <div class="d-flex align-items-center">
                                <div class="header-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h6 class="mb-0 ms-2">Submitted Documents</h6>
                            </div>
                        </div>
                        <div class="card-body py-0 px-3">
                            @foreach($application->job->jobRequirements as $requirement)
                                @php
                                    $submitted = $application->submitted_documents && isset($application->submitted_documents[$requirement->id]);
                                @endphp
                                <div class="document-item">
                                    <div class="document-info">
                                        <div class="document-icon {{ $submitted ? 'submitted' : 'pending' }}">
                                            <i class="fas {{ $submitted ? 'fa-file-check' : 'fa-file' }}"></i>
                                        </div>
                                        <span class="document-name">{{ $requirement->name }}</span>
                                    </div>
                                    <span class="document-status {{ $submitted ? 'submitted' : 'not-required' }}">
                                        <i class="fas {{ $submitted ? 'fa-check-circle' : 'fa-minus-circle' }}"></i>
                                        {{ $submitted ? 'Submitted' : 'Not Required' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Application Summary --}}
                <div class="card summary-card border-0 shadow-sm mb-3">
                    <div class="card-header summary-header">
                        <div class="d-flex align-items-center">
                            <div class="header-icon summary-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h6 class="mb-0 ms-2">Application Summary</h6>
                        </div>
                    </div>
                    <div class="card-body py-0 px-3">
                        <div class="summary-item">
                            <div class="summary-left">
                                <div class="summary-item-icon status">
                                    <i class="fas fa-flag"></i>
                                </div>
                                <span class="summary-label">Status</span>
                            </div>
                            @php
                                $statusClass = '';
                                $stageName = $application->getStageName();
                                if (str_contains(strtolower($stageName), 'reject')) {
                                    $statusClass = 'status-rejected';
                                } elseif (str_contains(strtolower($stageName), 'hired') || str_contains(strtolower($stageName), 'approved')) {
                                    $statusClass = 'status-approved';
                                } elseif (str_contains(strtolower($stageName), 'pending') || str_contains(strtolower($stageName), 'review')) {
                                    $statusClass = 'status-pending';
                                } else {
                                    $statusClass = 'status-default';
                                }
                            @endphp
                            <span class="summary-status-badge {{ $statusClass }}">{{ $stageName }}</span>
                        </div>
                        <div class="summary-item">
                            <div class="summary-left">
                                <div class="summary-item-icon date">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <span class="summary-label">Applied Date</span>
                            </div>
                            <span
                                class="summary-value">{{ $application->applied_date ? $application->applied_date->format('M d, Y') : $application->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="summary-item">
                            <div class="summary-left">
                                <div class="summary-item-icon update">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="summary-label">Last Updated</span>
                            </div>
                            <span class="summary-value">{{ $application->updated_at->format('M d, Y') }}</span>
                        </div>
                        @if($application->resume)
                            <div class="summary-item resume-item">
                                <div class="summary-left">
                                    <div class="summary-item-icon resume">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <span class="summary-label">Resume</span>
                                </div>
                                <a href="{{ asset('storage/' . $application->resume) }}" target="_blank" class="resume-btn">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- View Job Button --}}
                <a href="{{ route('jobDetail', $application->job_id) }}" class="view-job-btn">
                    <i class="fas fa-briefcase me-2"></i> View Job Posting
                    <i class="fas fa-arrow-right ms-auto"></i>
                </a>
            </div>
        </div>
    </div>

    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const interviewLocation = "{{ $application->interview_location ?? '' }}";
            const mapContainer = document.getElementById('interview-map');

            if (mapContainer && interviewLocation) {
                // Mapbox Token - Should match the one used in Employer view
                mapboxgl.accessToken = 'pk.eyJ1Ijoia2hlbnJpY2toIiwiYSI6ImNtazM2azJyeDBvenIzaXBlb2ZlYThvY3cifQ.XyLAFaZh57ALvtctyCg1MQ';

                // Function to search with fallback (broader search if exact fails)
                const searchLocationWithFallback = (searchQuery, originalQuery, depth = 0) => {
                    fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(searchQuery)}.json?access_token=${mapboxgl.accessToken}&types=address,poi,place,locality&limit=1`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.features && data.features.length > 0) {
                                const [lng, lat] = data.features[0].center;

                                const map = new mapboxgl.Map({
                                    container: 'interview-map',
                                    style: 'mapbox://styles/mapbox/streets-v11',
                                    center: [lng, lat],
                                    zoom: 14,
                                    interactive: true
                                });

                                // Add navigation controls
                                map.addControl(new mapboxgl.NavigationControl(), 'top-right');

                                // Add a marker with the original location name
                                new mapboxgl.Marker({ color: '#3b82f6' })
                                    .setLngLat([lng, lat])
                                    .setPopup(new mapboxgl.Popup().setHTML(`<p style="margin:5px;font-weight:bold;font-size:12px;">${originalQuery}</p>`))
                                    .addTo(map);
                            } else {
                                // Try fallback: remove first part if comma exists
                                const parts = searchQuery.split(',').map(p => p.trim());
                                if (parts.length > 1 && depth < 3) {
                                    // Try searching with remaining parts
                                    const fallbackQuery = parts.slice(1).join(', ');
                                    console.log('No results found for "' + searchQuery + '". Trying broader search:', fallbackQuery);
                                    searchLocationWithFallback(fallbackQuery, originalQuery, depth + 1);
                                } else {
                                    // No results even with fallback
                                    console.warn('No location found for:', originalQuery);
                                    mapContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted small">Map location not found</div>';
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Mapbox Geocoding Error:', err);
                            mapContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted small">Error loading map</div>';
                        });
                };

                // Start search with the full location
                searchLocationWithFallback(interviewLocation, interviewLocation, 0);
            }
        });
    </script>

    <style>
        .application-detail-page {
            padding: 0;
            max-width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        .application-detail-page .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }

        .application-detail-page .row>[class*="col-"] {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .application-detail-page .card {
            max-width: 100%;
            overflow: hidden;
        }

        /* Progress Tracker */
        .progress-tracker {
            padding: 20px 0;
        }

        .progress-line {
            position: absolute;
            top: 25px;
            left: 12%;
            right: 12%;
            height: 4px;
            background: #e5e7eb;
            z-index: 0;
        }

        .progress-step {
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .step-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #6b7280;
        }

        .progress-step.completed .step-label,
        .progress-step.current .step-label {
            color: #1f2937;
            font-weight: 600;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding: 12px 15px;
            max-height: 450px;
            overflow-y: auto;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 26px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            padding-left: 45px;
            padding-bottom: 12px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: 12px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .timeline-content {
            background: #f9fafb;
            border-radius: 6px;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }

        .timeline-content .d-flex {
            flex-wrap: wrap;
            gap: 5px;
        }

        .timeline-content h6 {
            font-size: 0.8rem;
            margin-bottom: 0;
        }

        .timeline-content small {
            font-size: 0.7rem;
        }

        .message-box {
            font-style: italic;
            font-size: 0.8rem;
            margin-top: 6px;
        }

        .message-box p {
            margin-bottom: 0;
            font-size: 0.8rem;
            line-height: 1.4;
        }

        .message-box .fa-quote-left {
            font-size: 0.65rem;
        }

        /* Subtle Background Colors */
        .bg-danger-subtle {
            background-color: #fee2e2 !important;
        }

        .bg-success-subtle {
            background-color: #d1fae5 !important;
        }

        .bg-info-subtle {
            background-color: #dbeafe !important;
        }

        /* Interview Card Styles */
        .interview-card {
            border-left: 3px solid #3b82f6 !important;
        }

        .interview-header {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-bottom: 1px solid #bfdbfe;
            padding: 10px 12px;
        }

        .interview-header h6 {
            color: #1e40af;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .interview-detail-item {
            display: flex;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .interview-detail-item:last-child {
            border-bottom: none;
        }

        .detail-icon {
            width: 28px;
            height: 28px;
            background: #f1f5f9;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .detail-icon i {
            font-size: 0.75rem;
            color: #64748b;
        }

        .detail-content {
            flex: 1;
            min-width: 0;
        }

        .detail-label {
            display: block;
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .detail-value {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: #1e293b;
        }

        .interview-notes {
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px 10px;
        }

        .notes-content {
            font-size: 0.8rem;
            color: #3b82f6;
            font-style: italic;
            margin-top: 4px;
        }

        /* ===== Documents Card Styles ===== */
        .documents-card {
            border-radius: 12px !important;
            overflow: hidden;
        }

        .documents-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-bottom: none;
            padding: 14px 16px;
        }

        .documents-header .header-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .documents-header .header-icon i {
            color: #ffffff;
            font-size: 0.9rem;
        }

        .documents-header h6 {
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .document-item:last-child {
            border-bottom: none;
        }

        .document-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .document-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .document-icon.submitted {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #059669;
        }

        .document-icon.pending {
            background: #f1f5f9;
            color: #94a3b8;
        }

        .document-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1e293b;
        }

        .document-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .document-status.submitted {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #047857;
        }

        .document-status.not-required {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ===== Summary Card Styles ===== */
        .summary-card {
            border-radius: 12px !important;
            overflow: hidden;
            border: none !important;
        }

        .summary-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-bottom: none;
            padding: 14px 16px;
        }

        .summary-header .header-icon.summary-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .summary-header .header-icon i {
            color: #ffffff;
            font-size: 0.9rem;
        }

        .summary-header h6 {
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .summary-item-icon.status {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        .summary-item-icon.date {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }

        .summary-item-icon.update {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4f46e5;
        }

        .summary-item-icon.resume {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .summary-label {
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
        }

        .summary-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* Status Badge Styles */
        .summary-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .summary-status-badge.status-rejected {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .summary-status-badge.status-approved {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #047857;
        }

        .summary-status-badge.status-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        .summary-status-badge.status-default {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4f46e5;
        }

        /* Resume Button */
        .resume-btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        .resume-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        }

        /* View Job Button */
        .view-job-btn {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .view-job-btn:hover {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-color: transparent;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .view-job-btn i:first-child {
            font-size: 1rem;
        }

        .view-job-btn i:last-child {
            font-size: 0.75rem;
            opacity: 0.7;
            transition: transform 0.2s ease;
        }

        .view-job-btn:hover i:last-child {
            transform: translateX(4px);
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .progress-line {
                left: 5%;
                right: 5%;
            }

            .step-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .step-label {
                font-size: 0.75rem;
            }

            .timeline {
                padding: 12px;
                max-height: 400px;
            }

            .timeline-item {
                padding-left: 40px;
                padding-bottom: 12px;
            }

            .timeline::before {
                left: 22px;
            }

            .timeline-marker {
                left: 8px;
                width: 24px;
                height: 24px;
                font-size: 0.65rem;
            }

            .timeline-content {
                padding: 8px 10px;
            }

            .timeline-content h6 {
                font-size: 0.8rem;
            }

            .message-box {
                padding: 8px !important;
            }
        }
    </style>
@endsection