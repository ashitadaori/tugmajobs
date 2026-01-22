@extends('layouts.employer')

@section('page_title', 'Application Details')

@section('content')
    <!-- Professional Header with Applicant Overview -->
    <div class="applicant-header-card">
        <div class="applicant-header-content">
            <div class="applicant-header-left">
                <a href="{{ route('employer.applications.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Applications
                </a>
                <div class="applicant-main-info">
                    <div class="applicant-avatar-wrapper">
                        @php
                            $imgSrc = $application->user->profile_image;
                        @endphp

                        @if($imgSrc)
                            @if(Auth::user()->isAdmin())
                                <img src="{{ $imgSrc }}" alt="{{ $application->user->name }}" class="applicant-avatar-lg"
                                    onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="applicant-avatar-placeholder-lg" style="display: none;">
                                    <i class="bi bi-person-fill" style="font-size: 32px;"></i>
                                </div>
                            @else
                                <div class="applicant-avatar-placeholder-lg" style="background: rgba(255,255,255,0.2);">
                                    <i class="bi bi-person-fill" style="font-size: 32px; color: #fff;"></i>
                                </div>
                            @endif
                        @else
                            <div class="applicant-avatar-placeholder-lg">
                                <i class="bi bi-person-fill" style="font-size: 32px;"></i>
                            </div>
                        @endif
                        @if($application->shortlisted)
                            <span class="shortlist-indicator" title="Shortlisted">
                                <i class="bi bi-star-fill"></i>
                            </span>
                        @endif
                    </div>
                    <div class="applicant-details">
                        <h1 class="applicant-name">
                            @if(Auth::user()->isAdmin())
                                {{ $application->user->name }}
                            @else
                                Applicant
                            @endif
                        </h1>
                        <p class="applicant-position">Applied for: <strong>{{ $application->job->title }}</strong></p>
                        <div class="applicant-meta">
                            @if(Auth::user()->isAdmin())
                                <span class="meta-item">
                                    <i class="bi bi-envelope"></i>
                                    {{ $application->user->email }}
                                </span>
                                @if($application->user->jobseeker && $application->user->jobseeker->phone)
                                    <span class="meta-item">
                                        <i class="bi bi-telephone"></i>
                                        {{ $application->user->jobseeker->phone }}
                                    </span>
                                @endif
                            @endif
                            <span class="meta-item">
                                <i class="bi bi-calendar3"></i>
                                Applied {{ $application->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="applicant-header-right">
                <div class="status-badges">
                    @php
                        $statusConfig = [
                            'pending' => ['class' => 'status-pending', 'icon' => 'hourglass-split'],
                            'approved' => ['class' => 'status-approved', 'icon' => 'check-circle-fill'],
                            'rejected' => ['class' => 'status-rejected', 'icon' => 'x-circle-fill'],
                        ];
                        $config = $statusConfig[$application->status] ?? $statusConfig['pending'];
                    @endphp
                    <span class="status-badge {{ $config['class'] }}">
                        <i class="bi bi-{{ $config['icon'] }}"></i>
                        {{ ucfirst($application->status) }}
                    </span>
                </div>
                <div class="header-actions">
                    <a href="{{ route('employer.jobseeker.profile', $application->user_id) }}"
                        class="ep-btn ep-btn-outline">
                        <i class="bi bi-person-lines-fill"></i>
                        Full Profile
                    </a>
                    <button type="button"
                        class="ep-btn {{ $application->shortlisted ? 'ep-btn-warning' : 'ep-btn-outline' }}"
                        onclick="toggleShortlist({{ $application->id }})">
                        <i class="bi bi-star{{ $application->shortlisted ? '-fill' : '' }}"></i>
                        {{ $application->shortlisted ? 'Shortlisted' : 'Shortlist' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Progress Pipeline -->
    <div class="ep-card pipeline-card">
        <div class="pipeline-header">
            <h3 class="pipeline-title">
                <i class="bi bi-kanban"></i>
                Hiring Pipeline
            </h3>
            <div class="pipeline-actions">
                @if($application->stage !== 'hired' && $application->stage !== 'rejected')
                    @if($application->stage_status === 'pending')
                        <button type="button" class="ep-btn ep-btn-success ep-btn-sm"
                            onclick="approveStage({{ $application->id }})">
                            <i class="bi bi-check-lg"></i>
                            Approve Stage
                        </button>
                        <button type="button" class="ep-btn ep-btn-danger ep-btn-sm"
                            onclick="rejectApplication({{ $application->id }})">
                            <i class="bi bi-x-lg"></i>
                            Reject
                        </button>
                    @elseif($application->stage_status === 'approved' && $application->canAdvanceStage())
                        <button type="button" class="ep-btn ep-btn-primary ep-btn-sm"
                            onclick="advanceStage({{ $application->id }})">
                            <i class="bi bi-arrow-right"></i>
                            Advance Stage
                        </button>
                    @endif
                    @if($application->stage === 'interview' && !$application->hasScheduledInterview())
                        <button type="button" class="ep-btn ep-btn-info ep-btn-sm" onclick="showScheduleInterviewModal()">
                            <i class="bi bi-calendar-plus"></i>
                            Schedule Interview
                        </button>
                    @endif
                    @if($application->stage === 'interview' && $application->hasScheduledInterview())
                        <button type="button" class="ep-btn ep-btn-success ep-btn-sm" onclick="markAsHired({{ $application->id }})">
                            <i class="bi bi-trophy"></i>
                            Mark as Hired
                        </button>
                    @endif
                @endif
            </div>
        </div>
        <div class="pipeline-body">
            <div class="pipeline-stages">
                @php
                    $stages = ['application' => 'Application', 'requirements' => 'Documents', 'interview' => 'Interview', 'hired' => 'Hired'];
                    $currentStageIndex = array_search($application->stage, array_keys($stages));
                    $progress = $application->getProgressPercentage();
                @endphp
                <div class="pipeline-progress-bar">
                    <div class="pipeline-progress-fill" style="width: {{ $progress }}%"></div>
                </div>
                <div class="pipeline-stage-indicators">
                    @foreach($stages as $stageKey => $stageName)
                        @php
                            $stageIndex = array_search($stageKey, array_keys($stages));
                            $isCompleted = $stageIndex < $currentStageIndex || ($stageIndex == $currentStageIndex && $application->stage_status === 'approved');
                            $isActive = $stageKey === $application->stage;
                            $isPending = $isActive && $application->stage_status === 'pending';
                        @endphp
                        <div
                            class="pipeline-stage {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }} {{ $isPending ? 'pending' : '' }}">
                            <div class="stage-indicator">
                                @if($isCompleted)
                                    <i class="bi bi-check-lg"></i>
                                @elseif($isActive)
                                    <span class="stage-number">{{ $stageIndex + 1 }}</span>
                                @else
                                    <span class="stage-number">{{ $stageIndex + 1 }}</span>
                                @endif
                            </div>
                            <span class="stage-name">{{ $stageName }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Interview Details -->
            @if($application->hasScheduledInterview())
                <div class="interview-scheduled-card">
                    <div class="interview-icon">
                        <i class="bi bi-camera-video"></i>
                    </div>
                    <div class="interview-details">
                        <h4>Interview Scheduled</h4>
                        <div class="interview-info-grid">
                            <div class="interview-info-item">
                                <i class="bi bi-calendar-event"></i>
                                <span>{{ $application->interview_date->format('l, F d, Y') }}</span>
                            </div>
                            <div class="interview-info-item">
                                <i class="bi bi-clock"></i>
                                <span>{{ $application->interview_time }}</span>
                            </div>
                            <div class="interview-info-item">
                                <i
                                    class="bi bi-{{ $application->interview_type === 'video_call' ? 'camera-video' : ($application->interview_type === 'phone' ? 'telephone' : 'building') }}"></i>
                                <span>{{ $application->getInterviewTypeName() }}</span>
                            </div>
                            <div class="interview-info-item">
                                <i class="bi bi-geo-alt"></i>
                                <span>{{ $application->interview_location }}</span>
                            </div>
                        </div>
                        @if($application->interview_notes)
                            <p class="interview-notes">{{ $application->interview_notes }}</p>
                        @endif
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="showRescheduleModal()">
                                <i class="bi bi-calendar2-x me-1"></i> Reschedule Interview
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Documents Button -->
            @if($application->hasSubmittedRequirements())
                <div class="documents-link">
                    <a href="{{ route('employer.applications.documents', $application->id) }}"
                        class="ep-btn ep-btn-outline ep-btn-sm">
                        <i class="bi bi-folder2-open"></i>
                        View Submitted Documents ({{ count($application->submitted_documents) }})
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="application-content-grid">
        <!-- Left Sidebar -->
        <div class="application-sidebar">
            <!-- Quick Info Card -->
            <div class="ep-card quick-info-card">
                <div class="ep-card-header">
                    <h3 class="ep-card-title">
                        <i class="bi bi-lightning-charge"></i>
                        Quick Info
                    </h3>
                </div>
                <div class="ep-card-body">
                    <div class="quick-info-list">
                        <div class="quick-info-item">
                            <span class="info-label">Status</span>
                            <span
                                class="ep-badge {{ $statusClass ?? 'ep-badge-gray' }}">{{ ucfirst($application->status) }}</span>
                        </div>
                        <div class="quick-info-item">
                            <span class="info-label">Applied</span>
                            <span class="info-value">{{ $application->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="quick-info-item">
                            <span class="info-label">Current Stage</span>
                            <span class="info-value">{{ $application->getStageName() }}</span>
                        </div>
                        @if($application->user->jobseeker && $application->user->jobseeker->experience)
                            <div class="quick-info-item">
                                <span class="info-label">Experience</span>
                                <span
                                    class="info-value">{{ $application->user->jobseeker->experience_level ?? $application->user->jobseeker->total_experience }}</span>
                            </div>
                        @endif
                        @if($application->user->jobseeker && $application->user->jobseeker->location)
                            <div class="quick-info-item">
                                <span class="info-label">Location</span>
                                <span class="info-value">{{ $application->user->jobseeker->location }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Resume Card -->
            <div class="ep-card resume-card">
                <div class="ep-card-header">
                    <h3 class="ep-card-title">
                        <i class="bi bi-file-earmark-text"></i>
                        Resume
                    </h3>
                </div>
                <div class="ep-card-body">
                    @php
                        // Check multiple possible resume locations
                        $resumePath = $application->resume
                            ?? ($application->user->jobSeekerProfile->resume_file ?? null)
                            ?? ($application->user->jobseeker->resume_file ?? null);
                    @endphp

                    @if($resumePath)
                        @if(Auth::user()->isAdmin())
                            <div class="resume-preview">
                                <div class="resume-icon">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </div>
                                <p class="resume-filename">{{ basename($resumePath) }}</p>
                                <div class="resume-actions">
                                    <a href="{{ asset('storage/' . $resumePath) }}" class="ep-btn ep-btn-primary ep-btn-sm"
                                        target="_blank">
                                        <i class="bi bi-eye"></i>
                                        View
                                    </a>
                                    <a href="{{ asset('storage/' . $resumePath) }}" class="ep-btn ep-btn-outline ep-btn-sm"
                                        download>
                                        <i class="bi bi-download"></i>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="empty-state-sm">
                                <i class="bi bi-shield-lock"></i>
                                <p>Resume Protected</p>
                            </div>
                        @endif
                    @else
                        <div class="empty-state-sm">
                            <i class="bi bi-file-earmark-x"></i>
                            <p>No resume uploaded</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Required Documents Card -->
            @if($application->job->jobRequirements && $application->job->jobRequirements->count() > 0)
                <div class="ep-card documents-card">
                    <div class="ep-card-header">
                        <h3 class="ep-card-title">
                            <i class="bi bi-file-earmark-check"></i>
                            Required Documents
                        </h3>
                    </div>
                    <div class="ep-card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($application->job->jobRequirements as $requirement)
                                @php
                                    $submitted = $application->submitted_documents && isset($application->submitted_documents[$requirement->id]);
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span>{{ $requirement->name }}</span>
                                        @if($requirement->is_required)
                                            <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Required</span>
                                        @endif
                                    </div>
                                    @if($submitted)
                                        <span class="badge bg-success"><i class="bi bi-check"></i> Submitted</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-dash"></i> Pending</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if($application->hasSubmittedRequirements())
                            <div class="p-3 border-top">
                                <a href="{{ route('employer.applications.documents', $application->id) }}"
                                    class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-folder2-open me-1"></i> View Submitted Documents
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Cover Letter Card -->
            @if($application->cover_letter)
                <div class="ep-card cover-letter-card">
                    <div class="ep-card-header">
                        <h3 class="ep-card-title">
                            <i class="bi bi-envelope-paper"></i>
                            Cover Letter
                        </h3>
                    </div>
                    <div class="ep-card-body">
                        <div class="cover-letter-content"
                            style="white-space: pre-wrap; line-height: 1.6; color: #374151; font-size: 0.9375rem; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%; overflow: hidden;">
                            {{ $application->cover_letter }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Job Details Card -->
            <div class="ep-card job-details-card">
                <div class="ep-card-header">
                    <h3 class="ep-card-title">
                        <i class="bi bi-briefcase"></i>
                        Applied Position
                    </h3>
                </div>
                <div class="ep-card-body">
                    <h4 class="job-title-sm">{{ $application->job->title }}</h4>
                    <div class="job-meta-list">
                        <span class="job-meta-item">
                            <i class="bi bi-geo-alt"></i>
                            {{ $application->job->location }}
                        </span>
                        <span class="job-meta-item">
                            <i class="bi bi-clock"></i>
                            {{ $application->job->jobType->name ?? 'N/A' }}
                        </span>
                        <span class="job-meta-item">
                            <i class="bi bi-tag"></i>
                            {{ $application->job->category->name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="application-main">
            <!-- Candidate Profile -->
            <div class="ep-card candidate-profile-card">
                <div class="ep-card-header">
                    <h3 class="ep-card-title">
                        <i class="bi bi-person-vcard"></i>
                        Candidate Profile
                    </h3>
                </div>
                <div class="ep-card-body">
                    @if($application->user->jobseeker)
                        <div class="profile-sections">
                            <!-- Professional Summary -->
                            @if($application->user->jobseeker->professional_summary)
                                <div class="profile-section">
                                    <h4 class="section-title">Professional Summary</h4>
                                    <p class="summary-text">{{ $application->user->jobseeker->professional_summary }}</p>
                                </div>
                            @endif

                            <!-- Contact Information -->
                            <div class="profile-section">
                                <h4 class="section-title">Contact Information</h4>
                                @if(Auth::user()->isAdmin())
                                    <div class="contact-grid">
                                        <div class="contact-item">
                                            <div class="contact-icon"><i class="bi bi-envelope"></i></div>
                                            <div class="contact-details">
                                                <span class="contact-label">Email</span>
                                                <span class="contact-value">{{ $application->user->email }}</span>
                                            </div>
                                        </div>
                                        @if($application->user->jobseeker->phone)
                                            <div class="contact-item">
                                                <div class="contact-icon"><i class="bi bi-telephone"></i></div>
                                                <div class="contact-details">
                                                    <span class="contact-label">Phone</span>
                                                    <span class="contact-value">{{ $application->user->jobseeker->phone }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if($application->user->jobseeker->location)
                                            <div class="contact-item">
                                                <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
                                                <div class="contact-details">
                                                    <span class="contact-label">Location</span>
                                                    <span class="contact-value">{{ $application->user->jobseeker->location }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if($application->user->jobseeker->website)
                                            <div class="contact-item">
                                                <div class="contact-icon"><i class="bi bi-globe"></i></div>
                                                <div class="contact-details">
                                                    <span class="contact-label">Website</span>
                                                    <a href="{{ $application->user->jobseeker->website }}" target="_blank"
                                                        class="contact-value link">{{ $application->user->jobseeker->website }}</a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="contact-grid">
                                        <p style="color: #666; font-style: italic;">Contact information is hidden.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Skills -->
                            @if($application->user->jobseeker->skills)
                                <div class="profile-section">
                                    <h4 class="section-title">Skills</h4>
                                    <div class="skills-container">
                                        @php
                                            $skills = is_string($application->user->jobseeker->skills)
                                                ? explode(',', $application->user->jobseeker->skills)
                                                : (is_array($application->user->jobseeker->skills) ? $application->user->jobseeker->skills : []);
                                        @endphp
                                        @foreach($skills as $skill)
                                            @if(trim($skill))
                                                <span class="skill-badge">{{ trim($skill) }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Education -->
                            @if($application->user->jobseeker->education)
                                <div class="profile-section">
                                    <h4 class="section-title">Education</h4>
                                    @php
                                        $education = $application->user->jobseeker->education;
                                        $educationData = null;
                                        if (is_string($education) && (str_starts_with($education, '{') || str_starts_with($education, '['))) {
                                            try {
                                                $educationData = json_decode($education, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($educationData)) {
                                                    if (isset($educationData['degree']) || isset($educationData['school'])) {
                                                        $educationData = [$educationData];
                                                    }
                                                } else {
                                                    $educationData = null;
                                                }
                                            } catch (Exception $e) {
                                                $educationData = null;
                                            }
                                        }
                                    @endphp

                                    @if($educationData && is_array($educationData))
                                        <div class="education-list">
                                            @foreach($educationData as $edu)
                                                @if(is_array($edu))
                                                    <div class="education-item">
                                                        <div class="edu-icon"><i class="bi bi-mortarboard-fill"></i></div>
                                                        <div class="edu-details">
                                                            @if(isset($edu['degree']) && $edu['degree'])
                                                                <h5 class="edu-degree">{{ $edu['degree'] }}</h5>
                                                            @endif
                                                            @if(isset($edu['school']) && $edu['school'])
                                                                <p class="edu-school">{{ $edu['school'] }}</p>
                                                            @endif
                                                            @if(isset($edu['field_of_study']) && $edu['field_of_study'])
                                                                <p class="edu-field">{{ $edu['field_of_study'] }}</p>
                                                            @endif
                                                            @if(isset($edu['start_date']))
                                                                <span class="edu-duration">
                                                                    {{ $edu['start_date'] }} -
                                                                    {{ isset($edu['currently_studying']) && $edu['currently_studying'] ? 'Present' : ($edu['end_date'] ?? '') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif(is_string($education))
                                        <p class="education-text">{{ $education }}</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Work Experience -->
                            @if($application->user->jobseeker->work_experience)
                                <div class="profile-section">
                                    <h4 class="section-title">Work Experience</h4>
                                    @php
                                        $workExperience = $application->user->jobseeker->work_experience;
                                        $workData = null;
                                        if (is_string($workExperience) && (str_starts_with($workExperience, '{') || str_starts_with($workExperience, '['))) {
                                            try {
                                                $workData = json_decode($workExperience, true);
                                                // Handle single object vs array of objects
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($workData)) {
                                                    // Check if it's a single object (associative array) or array of objects
                                                    // If the first key is string, it's likely a single object
                                                    $isAssoc = array_keys($workData) !== range(0, count($workData) - 1);
                                                    if ($isAssoc && (isset($workData['job_title']) || isset($workData['company']))) {
                                                        $workData = [$workData];
                                                    }
                                                } else {
                                                    $workData = null;
                                                }
                                            } catch (Exception $e) {
                                                $workData = null;
                                            }
                                        } elseif (is_array($workExperience)) {
                                            $workData = $workExperience;
                                        }
                                    @endphp

                                    @if($workData && is_array($workData))
                                        <div class="experience-list">
                                            @foreach($workData as $work)
                                                @if(is_array($work))
                                                    <div class="experience-item">
                                                        <div class="exp-icon"><i class="bi bi-briefcase-fill"></i></div>
                                                        <div class="exp-details">
                                                            @if(isset($work['job_title']) && $work['job_title'])
                                                                <h5 class="exp-title">{{ $work['job_title'] }}</h5>
                                                            @endif

                                                            @if(isset($work['company']) && $work['company'])
                                                                <p class="exp-company">{{ $work['company'] }}</p>
                                                            @endif

                                                            <div class="exp-meta">
                                                                @if(isset($work['start_date']))
                                                                    <span class="exp-duration">
                                                                        {{ $work['start_date'] }} -
                                                                        {{ isset($work['is_current']) && $work['is_current'] ? 'Present' : ($work['end_date'] ?? '') }}
                                                                    </span>
                                                                @endif

                                                                @if(isset($work['location']) && $work['location'])
                                                                    <span class="exp-location">
                                                                        <i class="bi bi-geo-alt-fill"></i> {{ $work['location'] }}
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            @if(isset($work['description']) && $work['description'])
                                                                <p class="exp-description">{{ $work['description'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif(is_string($workExperience))
                                        <p class="experience-text">{{ $workExperience }}</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Experience -->
                            @if($application->user->jobseeker->experience)
                                <div class="profile-section">
                                    <h4 class="section-title">Experience Level</h4>
                                    <p class="experience-text">
                                        {{ $application->user->jobseeker->experience_level ?? 'Not specified' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-person-x"></i>
                            <p>No profile information available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Screening Questions -->
            @if($application->preliminary_answers && is_array($application->preliminary_answers))
                <div class="ep-card">
                    <div class="ep-card-header">
                        <h3 class="ep-card-title">
                            <i class="bi bi-patch-question"></i>
                            Screening Questions
                        </h3>
                    </div>
                    <div class="ep-card-body">
                        <div class="qa-list">
                            @foreach($application->preliminary_answers as $index => $answer)
                                <div class="qa-item">
                                    <div class="qa-question">
                                        <span class="qa-number">Q{{ $index + 1 }}</span>
                                        <span class="qa-text">{{ $answer['question'] ?? 'Question not available' }}</span>
                                    </div>
                                    <div class="qa-answer">
                                        {{ $answer['answer'] ?? 'No answer provided' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Application History -->
            @if($application->statusHistory && $application->statusHistory->isNotEmpty())
                <div class="ep-card">
                    <div class="ep-card-header">
                        <h3 class="ep-card-title">
                            <i class="bi bi-clock-history"></i>
                            Activity Timeline
                        </h3>
                    </div>
                    <div class="ep-card-body">
                        <div class="activity-timeline">
                            @foreach($application->statusHistory->sortByDesc('created_at') as $history)
                                @php
                                    $timelineClass = match ($history->status) {
                                        'pending' => 'timeline-warning',
                                        'approved' => 'timeline-success',
                                        'rejected' => 'timeline-danger',
                                        default => 'timeline-gray'
                                    };
                                @endphp
                                <div class="timeline-entry {{ $timelineClass }}">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <span class="timeline-title">Status changed to {{ ucfirst($history->status) }}</span>
                                            <span
                                                class="timeline-time">{{ $history->created_at->format('M d, Y \a\t h:i A') }}</span>
                                        </div>
                                        @if($history->notes)
                                            <p class="timeline-notes">{{ $history->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            /* Header Card */
            .applicant-header-card {
                background: linear-gradient(135deg, var(--ep-primary) 0%, #4338ca 100%);
                border-radius: 16px;
                padding: 24px;
                margin-bottom: 24px;
                color: #fff;
            }

            .applicant-header-content {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 24px;
                flex-wrap: wrap;
            }

            .back-link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                color: rgba(255, 255, 255, 0.8);
                text-decoration: none;
                font-size: 13px;
                margin-bottom: 16px;
                transition: color 0.2s;
            }

            .back-link:hover {
                color: #fff;
            }

            .applicant-main-info {
                display: flex;
                gap: 20px;
                align-items: center;
            }

            .applicant-avatar-wrapper {
                position: relative;
            }

            .applicant-avatar-lg {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid rgba(255, 255, 255, 0.3);
            }

            .applicant-avatar-placeholder-lg {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
                font-weight: 600;
                color: #fff;
                border: 3px solid rgba(255, 255, 255, 0.3);
            }

            .shortlist-indicator {
                position: absolute;
                bottom: 0;
                right: 0;
                width: 24px;
                height: 24px;
                background: var(--ep-warning);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 12px;
                border: 2px solid #fff;
            }

            .applicant-name {
                font-size: 24px;
                font-weight: 700;
                margin: 0 0 4px 0;
                color: #fff;
            }

            .applicant-position {
                font-size: 14px;
                color: rgba(255, 255, 255, 0.9);
                margin: 0 0 12px 0;
            }

            .applicant-position strong {
                color: #fff;
            }

            .applicant-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 16px;
            }

            .meta-item {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
                color: rgba(255, 255, 255, 0.8);
            }

            .meta-item i {
                font-size: 14px;
            }

            .applicant-header-right {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 12px;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 600;
            }

            .status-pending {
                background: rgba(251, 191, 36, 0.2);
                color: #fbbf24;
            }

            .status-approved {
                background: rgba(34, 197, 94, 0.2);
                color: #22c55e;
            }

            .status-rejected {
                background: rgba(239, 68, 68, 0.2);
                color: #ef4444;
            }

            .header-actions {
                display: flex;
                gap: 8px;
            }

            .header-actions .ep-btn {
                background: rgba(255, 255, 255, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.3);
                color: #fff;
            }

            .header-actions .ep-btn:hover {
                background: rgba(255, 255, 255, 0.25);
            }

            .header-actions .ep-btn-warning {
                background: var(--ep-warning);
                border-color: var(--ep-warning);
            }

            /* Pipeline Card */
            .pipeline-card {
                margin-bottom: 24px;
            }

            .pipeline-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 20px;
                border-bottom: 1px solid var(--ep-gray-100);
            }

            .pipeline-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 16px;
                font-weight: 600;
                color: var(--ep-gray-800);
                margin: 0;
            }

            .pipeline-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }

            .pipeline-body {
                padding: 24px;
            }

            .pipeline-stages {
                position: relative;
                padding-bottom: 20px;
            }

            .pipeline-progress-bar {
                position: absolute;
                top: 20px;
                left: 40px;
                right: 40px;
                height: 4px;
                background: var(--ep-gray-100);
                border-radius: 2px;
                z-index: 0;
            }

            .pipeline-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, var(--ep-success) 0%, var(--ep-primary) 100%);
                border-radius: 2px;
                transition: width 0.5s ease;
            }

            .pipeline-stage-indicators {
                display: flex;
                justify-content: space-between;
                position: relative;
                z-index: 1;
            }

            .pipeline-stage {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }

            .stage-indicator {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: var(--ep-gray-100);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 14px;
                color: var(--ep-gray-400);
                border: 3px solid #fff;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .pipeline-stage.completed .stage-indicator {
                background: var(--ep-success);
                color: #fff;
            }

            .pipeline-stage.active .stage-indicator {
                background: var(--ep-primary);
                color: #fff;
                transform: scale(1.1);
            }

            .pipeline-stage.pending .stage-indicator {
                background: var(--ep-warning);
                color: #fff;
            }

            .stage-name {
                font-size: 12px;
                font-weight: 500;
                color: var(--ep-gray-500);
            }

            .pipeline-stage.completed .stage-name,
            .pipeline-stage.active .stage-name {
                color: var(--ep-gray-800);
                font-weight: 600;
            }

            /* Interview Card */
            .interview-scheduled-card {
                display: flex;
                gap: 16px;
                padding: 16px;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
                border-radius: 12px;
                border: 1px solid rgba(59, 130, 246, 0.2);
                margin-top: 20px;
            }

            .interview-icon {
                width: 48px;
                height: 48px;
                background: var(--ep-primary);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 20px;
                flex-shrink: 0;
            }

            .interview-details h4 {
                font-size: 15px;
                font-weight: 600;
                color: var(--ep-gray-800);
                margin: 0 0 12px 0;
            }

            .interview-info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .interview-info-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                color: var(--ep-gray-600);
            }

            .interview-info-item i {
                color: var(--ep-primary);
            }

            .interview-notes {
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid rgba(59, 130, 246, 0.2);
                font-size: 13px;
                color: var(--ep-gray-600);
                font-style: italic;
            }

            .documents-link {
                margin-top: 16px;
                padding-top: 16px;
                border-top: 1px solid var(--ep-gray-100);
            }

            /* Content Grid */
            .application-content-grid {
                display: grid;
                grid-template-columns: 320px 1fr;
                gap: 24px;
            }

            /* Sidebar */
            .application-sidebar {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .quick-info-list {
                display: flex;
                flex-direction: column;
            }

            .quick-info-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 0;
                border-bottom: 1px solid var(--ep-gray-100);
            }

            .quick-info-item:last-child {
                border-bottom: none;
            }

            .quick-info-item .info-label {
                font-size: 13px;
                color: var(--ep-gray-500);
            }

            .quick-info-item .info-value {
                font-size: 13px;
                font-weight: 500;
                color: var(--ep-gray-800);
            }

            /* Resume Card */
            .resume-preview {
                text-align: center;
            }

            .resume-icon {
                font-size: 48px;
                color: #dc2626;
                margin-bottom: 12px;
            }

            .resume-filename {
                font-size: 13px;
                color: var(--ep-gray-600);
                margin-bottom: 16px;
                word-break: break-all;
            }

            .resume-actions {
                display: flex;
                gap: 8px;
                justify-content: center;
            }

            .empty-state-sm {
                text-align: center;
                padding: 24px;
                color: var(--ep-gray-400);
            }

            .empty-state-sm i {
                font-size: 36px;
                margin-bottom: 8px;
            }

            .empty-state-sm p {
                font-size: 13px;
                margin: 0;
            }

            /* Job Details Card */
            .job-title-sm {
                font-size: 15px;
                font-weight: 600;
                color: var(--ep-gray-800);
                margin: 0 0 12px 0;
            }

            .job-meta-list {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .job-meta-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                color: var(--ep-gray-600);
            }

            .job-meta-item i {
                color: var(--ep-gray-400);
                width: 16px;
            }

            /* Main Content */
            .application-main {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            /* Profile Sections */
            .profile-sections {
                display: flex;
                flex-direction: column;
                gap: 24px;
            }

            .profile-section {
                padding-bottom: 24px;
                border-bottom: 1px solid var(--ep-gray-100);
            }

            .profile-section:last-child {
                padding-bottom: 0;
                border-bottom: none;
            }

            .section-title {
                font-size: 14px;
                font-weight: 600;
                color: var(--ep-gray-800);
                margin: 0 0 16px 0;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            /* Contact Grid */
            .contact-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .contact-item {
                display: flex;
                gap: 12px;
            }

            .contact-icon {
                width: 40px;
                height: 40px;
                background: var(--ep-primary-50);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--ep-primary);
                flex-shrink: 0;
            }

            .contact-details {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .contact-label {
                font-size: 11px;
                color: var(--ep-gray-500);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .contact-value {
                font-size: 14px;
                color: var(--ep-gray-800);
                font-weight: 500;
            }

            .contact-value.link {
                color: var(--ep-primary);
                text-decoration: none;
            }

            .contact-value.link:hover {
                text-decoration: underline;
            }

            /* Skills */
            .skills-container {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .skill-badge {
                display: inline-block;
                padding: 6px 12px;
                background: var(--ep-primary-50);
                color: var(--ep-primary);
                border-radius: 20px;
                font-size: 12px;
                font-weight: 500;
            }

            /* Education */
            .education-list {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .education-item {
                display: flex;
                gap: 12px;
            }

            .edu-icon {
                width: 40px;
                height: 40px;
                background: var(--ep-gray-50);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--ep-primary);
                flex-shrink: 0;
            }

            .edu-details {
                flex: 1;
            }

            .edu-degree {
                font-size: 14px;
                font-weight: 600;
                color: var(--ep-gray-800);
                margin: 0 0 4px 0;
            }

            .edu-school {
                font-size: 13px;
                color: var(--ep-gray-600);
                margin: 0 0 2px 0;
            }

            .edu-field {
                font-size: 12px;
                color: var(--ep-gray-500);
                margin: 0;
            }

            .edu-duration {
                font-size: 11px;
                color: var(--ep-gray-400);
                margin-top: 4px;
            }

            .experience-text,
            .education-text {
                font-size: 14px;
                color: var(--ep-gray-700);
                margin: 0;
            }

            /* Cover Letter */
            .cover-letter-content {
                font-size: 14px;
                line-height: 1.8;
                color: var(--ep-gray-700);
                white-space: pre-wrap;
                word-wrap: break-word;
                word-break: break-word;
                overflow-wrap: break-word;
                max-width: 100%;
                overflow: hidden;
            }

            /* Q&A */
            .qa-list {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .qa-item {
                background: var(--ep-gray-50);
                border-radius: 12px;
                padding: 16px;
                border-left: 4px solid var(--ep-primary);
            }

            .qa-question {
                display: flex;
                gap: 12px;
                margin-bottom: 12px;
            }

            .qa-number {
                width: 28px;
                height: 28px;
                background: var(--ep-primary);
                color: #fff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: 600;
                flex-shrink: 0;
            }

            .qa-text {
                font-size: 14px;
                font-weight: 600;
                color: var(--ep-gray-800);
            }

            .qa-answer {
                font-size: 14px;
                color: var(--ep-gray-600);
                line-height: 1.6;
                padding-left: 40px;
            }

            /* Activity Timeline */
            .activity-timeline {
                position: relative;
                padding-left: 24px;
            }

            .activity-timeline::before {
                content: '';
                position: absolute;
                left: 6px;
                top: 8px;
                bottom: 8px;
                width: 2px;
                background: var(--ep-gray-100);
            }

            .timeline-entry {
                position: relative;
                padding-bottom: 20px;
            }

            .timeline-entry:last-child {
                padding-bottom: 0;
            }

            .timeline-dot {
                position: absolute;
                left: -24px;
                top: 4px;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                background: var(--ep-gray-300);
                border: 2px solid #fff;
                box-shadow: 0 0 0 2px var(--ep-gray-100);
            }

            .timeline-entry.timeline-warning .timeline-dot {
                background: var(--ep-warning);
                box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2);
            }

            .timeline-entry.timeline-success .timeline-dot {
                background: var(--ep-success);
                box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
            }

            .timeline-entry.timeline-danger .timeline-dot {
                background: var(--ep-danger);
                box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
            }

            .timeline-content {
                padding-left: 8px;
            }

            .timeline-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 4px;
            }

            .timeline-title {
                font-size: 14px;
                font-weight: 600;
                color: var(--ep-gray-800);
            }

            .timeline-time {
                font-size: 12px;
                color: var(--ep-gray-500);
            }

            .timeline-notes {
                font-size: 13px;
                color: var(--ep-gray-600);
                margin: 0;
                padding: 8px 12px;
                background: var(--ep-gray-50);
                border-radius: 6px;
                margin-top: 8px;
            }

            /* Empty State */
            .empty-state {
                text-align: center;
                padding: 40px 20px;
                color: var(--ep-gray-400);
            }

            .empty-state i {
                font-size: 48px;
                margin-bottom: 16px;
            }

            .empty-state p {
                font-size: 14px;
                margin: 0;
            }

            /* Responsive */
            @media (max-width: 1024px) {
                .application-content-grid {
                    grid-template-columns: 1fr;
                }

                .application-sidebar {
                    flex-direction: row;
                    flex-wrap: wrap;
                }

                .application-sidebar>* {
                    flex: 1;
                    min-width: 280px;
                }

                .contact-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 768px) {
                .applicant-header-content {
                    flex-direction: column;
                }

                .applicant-header-right {
                    align-items: flex-start;
                    width: 100%;
                }

                .header-actions {
                    width: 100%;
                }

                .header-actions .ep-btn {
                    flex: 1;
                }

                .pipeline-actions {
                    width: 100%;
                    justify-content: flex-start;
                }

                .pipeline-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 12px;
                }

                .interview-info-grid {
                    grid-template-columns: 1fr;
                }

                .application-sidebar {
                    flex-direction: column;
                }

                .application-sidebar>* {
                    min-width: 100%;
                }
            }

            @media (max-width: 576px) {
                .applicant-main-info {
                    flex-direction: column;
                    text-align: center;
                }

                .applicant-meta {
                    justify-content: center;
                }

                .pipeline-stage-indicators {
                    flex-wrap: wrap;
                    gap: 16px;
                    justify-content: center;
                }

                .pipeline-progress-bar {
                    display: none;
                }
            }
        </style>
        <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
        <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
        <script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
        <link rel="stylesheet"
            href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css" type="text/css">
        <style>
            .mapboxgl-ctrl-geocoder {
                min-width: 100%;
                width: 100%;
                box-shadow: none;
            }

            .mapboxgl-ctrl-geocoder--input {
                padding-left: 36px !important;
            }

            /* Location Search Styles - Ported from Company Profile */
            .location-search-wrapper {
                display: flex;
                gap: 8px;
                align-items: stretch;
                margin-bottom: 8px;
            }

            .location-input-container {
                flex: 1;
                position: relative;
            }

            .location-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--ep-gray-400);
                font-size: 18px;
                z-index: 1;
            }

            .location-search-input {
                padding-left: 42px !important;
                border-radius: 8px;
                border: 1px solid var(--ep-gray-200);
                width: 100%;
                padding: 0.375rem 0.75rem;
            }

            .location-detect-btn {
                padding: 0 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
            }

            .location-suggestions-dropdown {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid var(--ep-gray-300);
                border-top: none;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
                border-bottom-left-radius: 8px;
                border-bottom-right-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 1060;
                /* Higher than modal */
                max-height: 200px;
                overflow-y: auto;
            }

            .location-suggestion-item {
                padding: 10px 16px;
                cursor: pointer;
                border-bottom: 1px solid var(--ep-gray-100);
                transition: background-color 0.15s ease;
            }

            .location-suggestion-item:hover {
                background-color: var(--ep-primary-50);
            }

            .location-suggestion-item:last-child {
                border-bottom: none;
            }

            .location-suggestion-name {
                font-weight: 600;
                color: var(--ep-gray-800);
                font-size: 14px;
            }

            .location-suggestion-address {
                font-size: 12px;
                color: var(--ep-gray-500);
                margin-top: 2px;
            }

            .location-map-placeholder {
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: var(--ep-gray-400);
                background: #f3f4f6;
            }
        </style>

        <script>
            // Placeholder Mapbox Token - User t              o replace
            const MAPBOX_TOKEN = 'pk.eyJ1Ijoia2hlb    nJpY2toIiwiYSI6ImNtazM2azJyeDBvenIzaXBlb2ZlYThvY3cifQ.XyLAFaZh57ALvtctyCg1MQ'; // Dummy token

            // Check for dummy token and alert user
            if (MAPBOX_TOKEN.includes('$ample') || MAPBOX_TOKEN.includes('placeholder')) {
                console.warn('Using dummy Mapbox token. Map tiles will likely not load.');
                document.addEventListener('DOMContentLoaded', () => {
                    if (typeof showToast === 'function') {
                        showToast('error', 'Map Error: Using invalid/dummy Mapbox Token. Please update MAPBOX_TOKEN in the code.');
                    } else {
                        alert('Map Error: Using invalid/dummy Mapbox Token. Please update MAPBOX_TOKEN in show.blade.php');
                    }
                });
            }

            window.mapboxMaps = window.mapboxMaps || {};

            window.mapboxMaps = window.mapboxMaps || {};
            let locationConfig = null;

            // Fetch location config (bounds) on load
            fetch('/api/location/config')
                .then(response => response.json())
                .then(data => {
                    locationConfig = data;
                    console.log('Location config loaded:', data);
                })
                .catch(error => console.error('Error loading location config:', error));

            function isWithinStaCruz(lng, lat) {
                if (!locationConfig || !locationConfig.stacruz_bounds) {
                    // Fail fast if config not loaded, or lenient? 
                    // Let's go with lenient but log warning until config loads
                    return true;
                }

                const bounds = locationConfig.stacruz_bounds;
                // Ensure bounds structure matches expectation (southwest, northeast arrays)
                if (bounds.southwest && bounds.northeast) {
                    return lng >= bounds.southwest[0] &&
                        lng <= bounds.northeast[0] &&
                        lat >= bounds.southwest[1] &&
                        lat <= bounds.northeast[1];
                }
                return true;
            }

            function initializeCustomLocationSearch(searchInputId, suggestionsId, mapContainerId, hiddenInputId, detectBtnId) {
                const searchInput = document.getElementById(searchInputId);
                const suggestionsList = document.getElementById(suggestionsId);
                const detectBtn = document.getElementById(detectBtnId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const mapContainer = document.getElementById(mapContainerId);

                if (!searchInput || !suggestionsList || !mapContainer) return;

                let map;
                let marker;
                let debounceTimer;

                // Initialize Map
                const initializeMap = () => {
                    if (!window.mapboxgl) return;

                    mapboxgl.accessToken = MAPBOX_TOKEN;

                    // Default to Sta. Cruz or config center
                    const defaultCenter = locationConfig && locationConfig.center
                        ? [locationConfig.center.lng, locationConfig.center.lat]
                        : [125.412, 6.845]; // Approx Sta. Cruz

                    map = new mapboxgl.Map({
                        container: mapContainerId,
                        style: 'mapbox://styles/mapbox/streets-v12',
                        center: defaultCenter,
                        zoom: 13
                    });

                    window.mapboxMaps[mapContainerId] = map;
                    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

                    // Map Click Handler
                    map.on('click', (e) => {
                        const { lng, lat } = e.lngLat;
                        updateCoordinates(lng, lat);
                        reverseGeocode(lng, lat);
                    });

                    // Resize observer
                    const resizeObserver = new ResizeObserver(() => {
                        map.resize();
                    });
                    resizeObserver.observe(mapContainer);
                };

                // Initialize the map immediately
                initializeMap();

                // Search Input Handler
                searchInput.addEventListener('input', function () {
                    const query = this.value.trim();
                    clearTimeout(debounceTimer);

                    if (query.length < 2) {
                        suggestionsList.style.display = 'none';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        searchPlaces(query);
                    }, 300);
                });

                // Detect Location Button
                if (detectBtn) {
                    detectBtn.addEventListener('click', function () {
                        if (navigator.geolocation) {
                            const originalHtml = this.innerHTML;
                            this.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                            this.disabled = true;

                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    const { longitude, latitude } = position.coords;
                                    updateCoordinates(longitude, latitude);
                                    reverseGeocode(longitude, latitude);

                                    map.flyTo({ center: [longitude, latitude], zoom: 15 });

                                    this.innerHTML = originalHtml;
                                    this.disabled = false;
                                },
                                (error) => {
                                    console.error('Geolocation error:', error);
                                    showToast('error', 'Unable to retrieve your location.');
                                    this.innerHTML = originalHtml;
                                    this.disabled = false;
                                }
                            );
                        } else {
                            showToast('error', 'Geolocation is not supported by your browser.');
                        }
                    });
                }

                // Hide suggestions on click outside
                document.addEventListener('click', function (e) {
                    if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                        suggestionsList.style.display = 'none';
                    }
                });

                function searchPlaces(query) {
                    fetch(`/api/location/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            showSuggestions(data);
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                        });
                }

                function showSuggestions(places) {
                    suggestionsList.innerHTML = '';
                    if (places.length === 0) {
                        suggestionsList.style.display = 'none';
                        return;
                    }

                    places.forEach(place => {
                        const div = document.createElement('div');
                        div.className = 'location-suggestion-item';
                        div.innerHTML = `
                                                                                            <div class="location-suggestion-name">${place.place_name}</div>
                                                                                            <div class="location-suggestion-address">${place.context || ''}</div>
                                                                                        `;
                        div.addEventListener('click', () => selectPlace(place));
                        suggestionsList.appendChild(div);
                    });

                    suggestionsList.style.display = 'block';
                }

                function selectPlace(place) {
                    const [lng, lat] = place.center;

                    // Check bounds strictly before setting ANY values
                    if (!isWithinStaCruz(lng, lat)) {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Location must be within Sta. Cruz, Davao del Sur.');
                        } else {
                            alert('Location must be within Sta. Cruz, Davao del Sur.');
                        }
                        return;
                    }

                    searchInput.value = place.place_name;
                    suggestionsList.style.display = 'none';
                    hiddenInput.value = place.place_name; // Set the submit value

                    updateCoordinates(lng, lat);

                    if (map) {
                        map.flyTo({ center: [lng, lat], zoom: 15 });
                        // Ensure resize happens after flyTo/visibility change to be safe
                        map.resize();
                    }
                }

                function updateCoordinates(lng, lat) {
                    if (!map) return;

                    // Check bounds STRICTLY
                    if (!isWithinStaCruz(lng, lat)) {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Location must be within Sta. Cruz, Davao del Sur.');
                        } else {
                            alert('Location must be within Sta. Cruz, Davao del Sur.');
                        }
                        return; // Do not update map or input
                    }

                    if (marker) marker.remove();
                    marker = new mapboxgl.Marker({ color: '#ea580c', draggable: true })
                        .setLngLat([lng, lat])
                        .addTo(map);

                    // Store valid coordinates to revert to if drag fails
                    let validLng = lng;
                    let validLat = lat;

                    marker.on('dragend', () => {
                        const lngLat = marker.getLngLat();

                        if (!isWithinStaCruz(lngLat.lng, lngLat.lat)) {
                            if (typeof showToast === 'function') {
                                showToast('error', 'Location must be within Sta. Cruz, Davao del Sur.');
                            } else {
                                alert('Location must be within Sta. Cruz, Davao del Sur.');
                            }
                            // Revert marker position
                            marker.setLngLat([validLng, validLat]);
                            return;
                        }

                        // Update valid coordinates
                        validLng = lngLat.lng;
                        validLat = lngLat.lat;

                        // Recursive call not needed for marker move as we manually update input here?
                        // Actually original code called reverseGeocode, let's keep that but NO recursive updateCoordinates call
                        // to avoid re-creating marker.
                        reverseGeocode(lngLat.lng, lngLat.lat);
                    });
                }

                function reverseGeocode(lng, lat) {
                    fetch(`/api/location/reverse-geocode?lng=${lng}&lat=${lat}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.features && data.features.length > 0) {
                                const place = data.features[0];
                                searchInput.value = place.place_name;
                                hiddenInput.value = place.place_name;
                            }
                        })
                        .catch(error => console.error('Reverse geocode error:', error));
                }
            }
        </script>
    @endpush

    @push('scripts')
        <script>
            function toggleShortlist(applicationId) {
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                button.disabled = true;

                fetch(`{{ route('employer.applications.toggleShortlist', $application->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showToast('success', data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast('error', data.message || 'Failed to update shortlist status');
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred');
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
            }

            function approveStage(applicationId) {
                showActionModal(applicationId, 'approve', 'Approve Stage', 'Add a note for the applicant (optional):', 'Approve');
            }

            function rejectApplication(applicationId) {
                showActionModal(applicationId, 'reject', 'Reject Application', 'Please provide a reason for rejection (recommended):', 'Reject');
            }

            function advanceStage(applicationId) {
                if (confirm('Advance this application to the next stage?')) {
                    submitStageAction(applicationId, 'advance', null);
                }
            }

            function showActionModal(applicationId, action, title, label, buttonText) {
                const isReject = action === 'reject';
                const modalHTML = `
                                                                                                            <div class="modal fade" id="actionModal" tabindex="-1">
                                                                                                                <div class="modal-dialog modal-dialog-centered">
                                                                                                                    <div class="modal-content" style="border-radius: 16px; border: none;">
                                                                                                                        <div class="modal-header" style="border-bottom: 1px solid var(--ep-gray-100); padding: 20px 24px;">
                                                                                                                            <h5 class="modal-title" style="font-weight: 600; color: var(--ep-gray-800);">${title}</h5>
                                                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                                                        </div>
                                                                                                                        <div class="modal-body" style="padding: 24px;">
                                                                                                                            <p style="color: var(--ep-gray-600); margin-bottom: 16px;">${label}</p>
                                                                                                                            <textarea class="form-control" id="actionNotes" rows="4" placeholder="Enter notes here..."
                                                                                                                                style="border: 1px solid var(--ep-gray-200); border-radius: 8px; resize: none;"></textarea>
                                                                                                                        </div>
                                                                                                                        <div class="modal-footer" style="border-top: 1px solid var(--ep-gray-100); padding: 16px 24px;">
                                                                                                                            <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                                                            <button type="button" class="ep-btn ${isReject ? 'ep-btn-danger' : 'ep-btn-success'}" onclick="submitActionFromModal(${applicationId}, '${action}')">
                                                                                                                                ${buttonText}
                                                                                                                            </button>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        `;

                document.getElementById('actionModal')?.remove();
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                new bootstrap.Modal(document.getElementById('actionModal')).show();
            }

            function submitActionFromModal(applicationId, action) {
                const notes = document.getElementById('actionNotes').value.trim();
                bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
                submitStageAction(applicationId, action, notes);
            }

            function submitStageAction(applicationId, action, notes) {
                const buttons = document.querySelectorAll('button');
                buttons.forEach(btn => btn.disabled = true);

                fetch(`/employer/applications/${applicationId}/stage`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action, notes })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showToast('success', data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast('error', data.message || 'Failed to update application');
                            buttons.forEach(btn => btn.disabled = false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred');
                        buttons.forEach(btn => btn.disabled = false);
                    });
            }

            function showScheduleInterviewModal() {
                const today = new Date().toISOString().split('T')[0];
                const modalHTML = `
                                                                                                            <div class="modal fade" id="interviewModal" tabindex="-1">
                                                                                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                                                                    <div class="modal-content" style="border-radius: 16px; border: none;">
                                                                                                                        <div class="modal-header" style="border-bottom: 1px solid var(--ep-gray-100); padding: 20px 24px;">
                                                                                                                            <h5 class="modal-title" style="font-weight: 600; color: var(--ep-gray-800);">
                                                                                                                                <i class="bi bi-calendar-plus me-2"></i>Schedule Interview
                                                                                                                            </h5>
                                                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                                                        </div>
                                                                                                                        <div class="modal-body" style="padding: 24px;">
                                                                                                                            <form id="interviewForm" onsubmit="return false;">
                                                                                                                                <div class="row g-3">
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">Interview Date *</label>
                                                                                                                                        <input type="date" class="form-control" id="interview_date" min="${today}" required
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
                                                                                                                                    </div>
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">Interview Time *</label>
                                                                                                                                        <input type="time" class="form-control" id="interview_time" required
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
                                                                                                                                    </div>
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">Interview Type *</label>
                                                                                                                                        <select class="form-select" id="interview_type" required
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
                                                                                                                                            <option value="">Select type...</option>
                                                                                                                                            <option value="in_person">In Person</option>
                                                                                                                                            <option value="video_call">Video Call</option>
                                                                                                                                        </select>
                                                                                                                                    </div>
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;" id="interview_location_label">Location/Link *</label>

                                                                                                                                        <!-- Standard Input (Video/Link) -->
                                                                                                                                        <input type="text" class="form-control" id="interview_location" required
                                                                                                                                            placeholder="Office address or meeting link"
                                                                                                                                            autocomplete="off"
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">

                                                                                                                                        <!-- Mapbox Geocoder Container (In Person) -->
                                                                                                                                        <!-- Mapbox Geocoder Container (In Person) -->
                                                                                                                                        <!-- Custom Location Search (In Person) -->
                                                                                                                                    <div id="interview_search_wrapper" class="location-search-wrapper" style="display: none;">
                                                                                                                                        <div class="location-input-container">
                                                                                                                                            <i class="bi bi-geo-alt location-icon"></i>
                                                                                                                                            <input type="text" id="interview_search_input" class="location-search-input" 
                                                                                                                                                placeholder="Search location in Sta. Cruz..." autocomplete="off">
                                                                                                                                            <div class="location-suggestions-dropdown" id="interview_suggestions" style="display: none;"></div>
                                                                                                                                        </div>
                                                                                                                                        <button type="button" class="ep-btn ep-btn-secondary location-detect-btn" 
                                                                                                                                            id="interview_detect_btn" title="Use current location">
                                                                                                                                            <i class="bi bi-crosshair"></i>
                                                                                                                                        </button>
                                                                                                                                    </div>
                                                                                                                                        <div id="interview_map" style="display: none; width: 100%; height: 200px; border-radius: 8px; border: 1px solid var(--ep-gray-200);"></div>
                                                                                                                                    </div>
                                                                                                                                    <div class="col-12">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">Additional Notes</label>
                                                                                                                                        <textarea class="form-control" id="interview_notes" rows="3"
                                                                                                                                            placeholder="Any instructions for the candidate..."
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200); resize: none;"></textarea>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </form>
                                                                                                                        </div>
                                                                                                                        <div class="modal-footer" style="border-top: 1px solid var(--ep-gray-100); padding: 16px 24px;">
                                                                                                                            <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                                                            <button type="button" class="ep-btn ep-btn-primary" onclick="submitInterview()">
                                                                                                                                <i class="bi bi-calendar-check me-1"></i>Schedule
                                                                                                                            </button>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        `;

                document.getElementById('interviewModal')?.remove();
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                const interviewModalEl = document.getElementById('interviewModal');
                const interviewModal = new bootstrap.Modal(interviewModalEl);
                interviewModal.show();

                // Force map resize when modal is fully shown
                interviewModalEl.addEventListener('shown.bs.modal', function () {
                    console.log('Interview modal shown - resizing maps');
                    if (window.mapboxMaps && window.mapboxMaps['interview_map']) {
                        window.mapboxMaps['interview_map'].resize();
                    }
                });

                // Initialize dynamic location field
                // Initialize custom location search
                setTimeout(() => {
                    initializeCustomLocationSearch('interview_search_input', 'interview_suggestions', 'interview_map', 'interview_location', 'interview_detect_btn');
                }, 200);

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

                // Manual sync for Mapbox input if In Person
                if (document.getElementById('interview_type').value === 'in_person') {
                    const searchInput = document.getElementById('interview_search_input');
                    if (searchInput && searchInput.value) {
                        document.getElementById('interview_location').value = searchInput.value;
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

                const buttons = document.querySelectorAll('button');
                buttons.forEach(btn => btn.disabled = true);

                fetch(`/employer/applications/{{ $application->id }}/schedule-interview`, {
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
                            buttons.forEach(btn => btn.disabled = false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred');
                        buttons.forEach(btn => btn.disabled = false);
                    });
            }

            function showRescheduleModal() {
                const today = new Date().toISOString().split('T')[0];
                const currentDate = '{{ $application->interview_date ? $application->interview_date->format("Y-m-d") : "" }}';
                const currentTime = '{{ $application->interview_time ?? "" }}';
                const currentType = '{{ $application->interview_type ?? "" }}';
                const currentLocation = '{{ $application->interview_location ?? "" }}';

                const modalHTML = `
                                                                                                            <div class="modal fade" id="rescheduleModal" tabindex="-1">
                                                                                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                                                                    <div class="modal-content" style="border-radius: 16px; border: none;">
                                                                                                                        <div class="modal-header bg-warning bg-opacity-10" style="border-bottom: 1px solid var(--ep-gray-100); padding: 20px 24px;">
                                                                                                                            <h5 class="modal-title text-warning" style="font-weight: 600;">
                                                                                                                                <i class="bi bi-calendar2-x me-2"></i>Reschedule Interview
                                                                                                                            </h5>
                                                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                                                        </div>
                                                                                                                        <div class="modal-body" style="padding: 24px;">
                                                                                                                            <div class="alert alert-warning mb-4">
                                                                                                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                                                                                                <strong>Note:</strong> The applicant will be notified about this schedule change.
                                                                                                                            </div>
                                                                                                                            <form id="rescheduleForm" onsubmit="return false;">
                                                                                                                                <div class="row g-3">
                                                                                                                                    <div class="col-12">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">Reason for Rescheduling *</label>
                                                                                                                                        <textarea class="form-control" id="reschedule_reason" rows="2" required
                                                                                                                                            placeholder="Please provide a reason for rescheduling (e.g., scheduling conflict, emergency, etc.)"
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200); resize: none;"></textarea>
                                                                                                                                    </div>
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">New Interview Date *</label>
                                                                                                                                        <input type="date" class="form-control" id="reschedule_date" min="${today}" required
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
                                                                                                                                    </div>
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">New Interview Time *</label>
                                                                                                                                        <input type="time" class="form-control" id="reschedule_time" value="${currentTime}" required
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
                                                                                                                                    </div>
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">Interview Type *</label>
                                                                                                                                        <select class="form-select" id="reschedule_type" required
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
                                                                                                                                            <option value="">Select type...</option>
                                                                                                                                            <option value="in_person" ${currentType === 'in_person' ? 'selected' : ''}>In Person</option>
                                                                                                                                            <option value="video_call" ${currentType === 'video_call' ? 'selected' : ''}>Video Call</option>

                                                                                                                                        </select>
                                                                                                                                    </div>
                                                                                                                                    <div class="col-md-6">
                                                                                                                                        <label class="form-label" style="font-weight: 500;" id="reschedule_location_label">Location/Link *</label>

                                                                                                                                        <!-- Standard Input (Video/Link) -->
                                                                                                                                        <input type="text" class="form-control" id="reschedule_location" value="${currentLocation}" required
                                                                                                                                            placeholder="Office address or meeting link"
                                                                                                                                            autocomplete="off"
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">

                                                                                                                                        <!-- Mapbox Geocoder Container (In Person) -->
                                                                                                                                        <!-- Mapbox Geocoder Container (In Person) -->
                                                                                                                                        <!-- Custom Location Search (In Person) -->
                                                                                                                                    <div id="reschedule_search_wrapper" class="location-search-wrapper" style="display: none;">
                                                                                                                                        <div class="location-input-container">
                                                                                                                                            <i class="bi bi-geo-alt location-icon"></i>
                                                                                                                                            <input type="text" id="reschedule_search_input" class="location-search-input" 
                                                                                                                                                placeholder="Search location in Sta. Cruz..." autocomplete="off">
                                                                                                                                            <div class="location-suggestions-dropdown" id="reschedule_suggestions" style="display: none;"></div>
                                                                                                                                        </div>
                                                                                                                                        <button type="button" class="ep-btn ep-btn-secondary location-detect-btn" 
                                                                                                                                            id="reschedule_detect_btn" title="Use current location">
                                                                                                                                            <i class="bi bi-crosshair"></i>
                                                                                                                                        </button>
                                                                                                                                    </div>
                                                                                                                                        <div id="reschedule_map" style="display: none; width: 100%; height: 200px; border-radius: 8px; border: 1px solid var(--ep-gray-200);"></div>
                                                                                                                                    </div>
                                                                                                                                    <div class="col-12">
                                                                                                                                        <label class="form-label" style="font-weight: 500;">Additional Notes</label>
                                                                                                                                        <textarea class="form-control" id="reschedule_notes" rows="2"
                                                                                                                                            placeholder="Any updated instructions for the candidate..."
                                                                                                                                            style="border-radius: 8px; border: 1px solid var(--ep-gray-200); resize: none;"></textarea>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </form>
                                                                                                                        </div>
                                                                                                                        <div class="modal-footer" style="border-top: 1px solid var(--ep-gray-100); padding: 16px 24px;">
                                                                                                                            <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                                                            <button type="button" class="btn btn-warning text-white" onclick="submitReschedule()">
                                                                                                                                <i class="bi bi-calendar-check me-1"></i>Reschedule Interview
                                                                                                                            </button>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        `;

                document.getElementById('rescheduleModal')?.remove();
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                const rescheduleModalEl = document.getElementById('rescheduleModal');
                const rescheduleModal = new bootstrap.Modal(rescheduleModalEl);
                rescheduleModal.show();

                // Force map resize when modal is fully shown
                rescheduleModalEl.addEventListener('shown.bs.modal', function () {
                    if (window.mapboxMaps && window.mapboxMaps['reschedule_map']) {
                        window.mapboxMaps['reschedule_map'].resize();
                    }
                });

                // Initialize dynamic location field
                // Initialize custom location search
                setTimeout(() => {
                    initializeCustomLocationSearch('reschedule_search_input', 'reschedule_suggestions', 'reschedule_map', 'reschedule_location', 'reschedule_detect_btn');

                    // Pre-fill if existing location
                    const currentLoc = '{{ $application->interview_location ?? "" }}';
                    if (document.getElementById('reschedule_type').value === 'in_person' && currentLoc) {
                        document.getElementById('reschedule_search_input').value = currentLoc;
                        // Trigger search/map update here if needed or let user do it
                    }
                }, 200);

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

                // Manual sync for Mapbox input if In Person
                if (document.getElementById('reschedule_type').value === 'in_person') {
                    const searchInput = document.getElementById('reschedule_search_input');
                    if (searchInput && searchInput.value) {
                        document.getElementById('reschedule_location').value = searchInput.value;
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

                const buttons = document.querySelectorAll('button');
                buttons.forEach(btn => btn.disabled = true);

                fetch(`/employer/applications/{{ $application->id }}/reschedule-interview`, {
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
                            showToast('error', data.message || 'Failed to reschedule interview');
                            buttons.forEach(btn => btn.disabled = false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred');
                        buttons.forEach(btn => btn.disabled = false);
                    });
            }

            function markAsHired(applicationId) {
                if (confirm('Mark this applicant as hired? This will finalize the application.')) {
                    const buttons = document.querySelectorAll('button');
                    buttons.forEach(btn => btn.disabled = true);

                    fetch(`/employer/applications/${applicationId}/mark-hired`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                showToast('success', data.message);
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showToast('error', data.message || 'Failed to mark as hired');
                                buttons.forEach(btn => btn.disabled = false);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('error', 'An error occurred');
                            buttons.forEach(btn => btn.disabled = false);
                        });
                }
            }

            function showToast(type, message) {
                if (typeof Toastify !== 'undefined') {
                    Toastify({
                        text: message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: type === 'success' ? 'linear-gradient(135deg, #059669 0%, #10b981 100%)' : 'linear-gradient(135deg, #dc2626 0%, #ef4444 100%)',
                            borderRadius: '10px',
                            padding: '14px 20px',
                            fontFamily: 'Inter, sans-serif',
                            boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
                        }
                    }).showToast();
                } else {
                    alert(message);
                }
            }
        </script>
        <script>
            // Define globally to be accessible from modal functions
            // Define globally to be accessible from modal functions
            window.updateLocationField = function (typeSelectId, labelId, inputId) {
                const typeSelect = document.getElementById(typeSelectId);
                const label = document.getElementById(labelId);
                const input = document.getElementById(inputId);
                const geocoderContainerId = inputId === 'interview_location' ? 'interview_search_wrapper' : 'reschedule_search_wrapper';
                const geocoderContainer = document.getElementById(geocoderContainerId);

                if (!typeSelect || !label || !input) return;

                const updateField = () => {
                    const type = typeSelect.value;
                    const mapContainerId = inputId === 'interview_location' ? 'interview_map' : 'reschedule_map';
                    const mapContainer = document.getElementById(mapContainerId);

                    if (type === 'in_person') {
                        label.textContent = 'Location *';

                        // Show Geocoder and Map, Hide Standard Input
                        if (geocoderContainer) {
                            geocoderContainer.style.display = 'flex';
                            if (mapContainer) {
                                mapContainer.style.display = 'block';
                                // Trigger resize to fix blank map when toggling visibility
                                setTimeout(() => {
                                    window.dispatchEvent(new Event('resize'));
                                    if (window.mapboxMaps && window.mapboxMaps[mapContainerId]) {
                                        window.mapboxMaps[mapContainerId].resize();
                                    }
                                }, 100);
                            }

                            input.style.display = 'none';
                            // We don't set required=false here because we want to force a value, 
                            // but since the input is hidden, HTML validation might block submission if empty.
                            // The geocoder sync logic handles filling the hidden input.
                            // However, for standard HTML validation, a hidden input is not validated for "required" attribute usually.
                            // But let's keep it simple.
                            input.setAttribute('type', 'hidden'); // Ensure it's hidden but exists

                            // Initialize if empty logic removed as we use custom init function
                        }

                    } else {
                        // Standard Input Logic
                        if (geocoderContainer) {
                            geocoderContainer.style.display = 'none';
                            if (mapContainer) mapContainer.style.display = 'none';
                        }

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

                // Remove existing listener to avoid duplicates
                typeSelect.removeEventListener('change', updateField);
                typeSelect.addEventListener('change', updateField);

                // Initial call
                updateField();
            };

            document.addEventListener('DOMContentLoaded', function () {
                // Fix for Mapbox map not rendering correctly in Bootstrap modals
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const target = mutation.target;
                            // Check if a modal has been shown (Bootstrap adds 'show' class and style display:block)
                            if (target.classList.contains('modal') && target.classList.contains('show')) {
                                // Small delay to ensure transition is complete
                                setTimeout(() => {
                                    // Resize all registered map instances
                                    if (window.mapboxMaps) {
                                        Object.values(window.mapboxMaps).forEach(map => map.resize());
                                    }
                                    window.dispatchEvent(new Event('resize'));
                                }, 200);
                            }
                        }
                    });
                });

                // Observe all modals
                document.querySelectorAll('.modal').forEach(modal => {
                    observer.observe(modal, { attributes: true });
                });

                // Also listen for Bootstrap native events if jQuery is available, or use a delegate approach
                // Since we are using vanilla JS mostly here:
                document.body.addEventListener('shown.bs.modal', function () {
                    setTimeout(() => {
                        if (window.mapboxMaps) {
                            Object.values(window.mapboxMaps).forEach(map => map.resize());
                        }
                        window.dispatchEvent(new Event('resize'));
                    }, 200);
                }, true); // Capture phase
            });
        </script>
    @endpush
@endsection

@push('styles')
    <style>
        /* Profile Summary */
        .summary-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #4b5563;
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid var(--ep-primary);
        }

        /* Experience List */
        .experience-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .experience-item {
            display: flex;
            gap: 16px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .experience-item:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .exp-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(79, 70, 229, 0.1);
            color: var(--ep-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .exp-details {
            flex: 1;
        }

        .exp-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 4px 0;
        }

        .exp-company {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin: 0 0 8px 0;
        }

        .exp-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 8px;
            font-size: 13px;
            color: #6b7280;
        }

        .exp-duration,
        .exp-location {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .exp-description {
            font-size: 14px;
            line-height: 1.5;
            color: #4b5563;
            margin: 0;
            white-space: pre-line;
        }
    </style>
@endpush