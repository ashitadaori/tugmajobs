

<?php $__env->startSection('page_title', 'Application Details'); ?>

<?php $__env->startSection('content'); ?>
<!-- Professional Header with Applicant Overview -->
<div class="applicant-header-card">
    <div class="applicant-header-content">
        <div class="applicant-header-left">
            <a href="<?php echo e(route('employer.applications.index')); ?>" class="back-link">
                <i class="bi bi-arrow-left"></i>
                Back to Applications
            </a>
            <div class="applicant-main-info">
                <div class="applicant-avatar-wrapper">
                    <?php if($application->user->image): ?>
                        <img src="<?php echo e(Storage::url($application->user->image)); ?>" alt="<?php echo e($application->user->name); ?>" class="applicant-avatar-lg">
                    <?php else: ?>
                        <div class="applicant-avatar-placeholder-lg">
                            <?php echo e(strtoupper(substr($application->user->name, 0, 1))); ?>

                        </div>
                    <?php endif; ?>
                    <?php if($application->shortlisted): ?>
                        <span class="shortlist-indicator" title="Shortlisted">
                            <i class="bi bi-star-fill"></i>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="applicant-details">
                    <h1 class="applicant-name"><?php echo e($application->user->name); ?></h1>
                    <p class="applicant-position">Applied for: <strong><?php echo e($application->job->title); ?></strong></p>
                    <div class="applicant-meta">
                        <span class="meta-item">
                            <i class="bi bi-envelope"></i>
                            <?php echo e($application->user->email); ?>

                        </span>
                        <?php if($application->user->jobseeker && $application->user->jobseeker->phone): ?>
                            <span class="meta-item">
                                <i class="bi bi-telephone"></i>
                                <?php echo e($application->user->jobseeker->phone); ?>

                            </span>
                        <?php endif; ?>
                        <span class="meta-item">
                            <i class="bi bi-calendar3"></i>
                            Applied <?php echo e($application->created_at->diffForHumans()); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="applicant-header-right">
            <div class="status-badges">
                <?php
                    $statusConfig = [
                        'pending' => ['class' => 'status-pending', 'icon' => 'hourglass-split'],
                        'approved' => ['class' => 'status-approved', 'icon' => 'check-circle-fill'],
                        'rejected' => ['class' => 'status-rejected', 'icon' => 'x-circle-fill'],
                    ];
                    $config = $statusConfig[$application->status] ?? $statusConfig['pending'];
                ?>
                <span class="status-badge <?php echo e($config['class']); ?>">
                    <i class="bi bi-<?php echo e($config['icon']); ?>"></i>
                    <?php echo e(ucfirst($application->status)); ?>

                </span>
            </div>
            <div class="header-actions">
                <a href="<?php echo e(route('employer.jobseeker.profile', $application->user_id)); ?>" class="ep-btn ep-btn-outline">
                    <i class="bi bi-person-lines-fill"></i>
                    Full Profile
                </a>
                <button type="button" class="ep-btn <?php echo e($application->shortlisted ? 'ep-btn-warning' : 'ep-btn-outline'); ?>" onclick="toggleShortlist(<?php echo e($application->id); ?>)">
                    <i class="bi bi-star<?php echo e($application->shortlisted ? '-fill' : ''); ?>"></i>
                    <?php echo e($application->shortlisted ? 'Shortlisted' : 'Shortlist'); ?>

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
            <?php if($application->stage !== 'hired' && $application->stage !== 'rejected'): ?>
                <?php if($application->stage_status === 'pending'): ?>
                    <button type="button" class="ep-btn ep-btn-success ep-btn-sm" onclick="approveStage(<?php echo e($application->id); ?>)">
                        <i class="bi bi-check-lg"></i>
                        Approve Stage
                    </button>
                    <button type="button" class="ep-btn ep-btn-danger ep-btn-sm" onclick="rejectApplication(<?php echo e($application->id); ?>)">
                        <i class="bi bi-x-lg"></i>
                        Reject
                    </button>
                <?php elseif($application->stage_status === 'approved' && $application->canAdvanceStage()): ?>
                    <button type="button" class="ep-btn ep-btn-primary ep-btn-sm" onclick="advanceStage(<?php echo e($application->id); ?>)">
                        <i class="bi bi-arrow-right"></i>
                        Advance Stage
                    </button>
                <?php endif; ?>
                <?php if($application->stage === 'interview' && !$application->hasScheduledInterview()): ?>
                    <button type="button" class="ep-btn ep-btn-info ep-btn-sm" onclick="showScheduleInterviewModal()">
                        <i class="bi bi-calendar-plus"></i>
                        Schedule Interview
                    </button>
                <?php endif; ?>
                <?php if($application->stage === 'interview' && $application->hasScheduledInterview()): ?>
                    <button type="button" class="ep-btn ep-btn-success ep-btn-sm" onclick="markAsHired(<?php echo e($application->id); ?>)">
                        <i class="bi bi-trophy"></i>
                        Mark as Hired
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="pipeline-body">
        <div class="pipeline-stages">
            <?php
                $stages = ['application' => 'Application', 'requirements' => 'Documents', 'interview' => 'Interview', 'hired' => 'Hired'];
                $currentStageIndex = array_search($application->stage, array_keys($stages));
                $progress = $application->getProgressPercentage();
            ?>
            <div class="pipeline-progress-bar">
                <div class="pipeline-progress-fill" style="width: <?php echo e($progress); ?>%"></div>
            </div>
            <div class="pipeline-stage-indicators">
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stageKey => $stageName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $stageIndex = array_search($stageKey, array_keys($stages));
                        $isCompleted = $stageIndex < $currentStageIndex || ($stageIndex == $currentStageIndex && $application->stage_status === 'approved');
                        $isActive = $stageKey === $application->stage;
                        $isPending = $isActive && $application->stage_status === 'pending';
                    ?>
                    <div class="pipeline-stage <?php echo e($isCompleted ? 'completed' : ''); ?> <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($isPending ? 'pending' : ''); ?>">
                        <div class="stage-indicator">
                            <?php if($isCompleted): ?>
                                <i class="bi bi-check-lg"></i>
                            <?php elseif($isActive): ?>
                                <span class="stage-number"><?php echo e($stageIndex + 1); ?></span>
                            <?php else: ?>
                                <span class="stage-number"><?php echo e($stageIndex + 1); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="stage-name"><?php echo e($stageName); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Interview Details -->
        <?php if($application->hasScheduledInterview()): ?>
            <div class="interview-scheduled-card">
                <div class="interview-icon">
                    <i class="bi bi-camera-video"></i>
                </div>
                <div class="interview-details">
                    <h4>Interview Scheduled</h4>
                    <div class="interview-info-grid">
                        <div class="interview-info-item">
                            <i class="bi bi-calendar-event"></i>
                            <span><?php echo e($application->interview_date->format('l, F d, Y')); ?></span>
                        </div>
                        <div class="interview-info-item">
                            <i class="bi bi-clock"></i>
                            <span><?php echo e($application->interview_time); ?></span>
                        </div>
                        <div class="interview-info-item">
                            <i class="bi bi-<?php echo e($application->interview_type === 'video_call' ? 'camera-video' : ($application->interview_type === 'phone' ? 'telephone' : 'building')); ?>"></i>
                            <span><?php echo e($application->getInterviewTypeName()); ?></span>
                        </div>
                        <div class="interview-info-item">
                            <i class="bi bi-geo-alt"></i>
                            <span><?php echo e($application->interview_location); ?></span>
                        </div>
                    </div>
                    <?php if($application->interview_notes): ?>
                        <p class="interview-notes"><?php echo e($application->interview_notes); ?></p>
                    <?php endif; ?>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="showRescheduleModal()">
                            <i class="bi bi-calendar2-x me-1"></i> Reschedule Interview
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Documents Button -->
        <?php if($application->hasSubmittedRequirements()): ?>
            <div class="documents-link">
                <a href="<?php echo e(route('employer.applications.documents', $application->id)); ?>" class="ep-btn ep-btn-outline ep-btn-sm">
                    <i class="bi bi-folder2-open"></i>
                    View Submitted Documents (<?php echo e(count($application->submitted_documents)); ?>)
                </a>
            </div>
        <?php endif; ?>
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
                        <span class="ep-badge <?php echo e($statusClass ?? 'ep-badge-gray'); ?>"><?php echo e(ucfirst($application->status)); ?></span>
                    </div>
                    <div class="quick-info-item">
                        <span class="info-label">Applied</span>
                        <span class="info-value"><?php echo e($application->created_at->format('M d, Y')); ?></span>
                    </div>
                    <div class="quick-info-item">
                        <span class="info-label">Current Stage</span>
                        <span class="info-value"><?php echo e($application->getStageName()); ?></span>
                    </div>
                    <?php if($application->user->jobseeker && $application->user->jobseeker->experience): ?>
                        <div class="quick-info-item">
                            <span class="info-label">Experience</span>
                            <span class="info-value"><?php echo e($application->user->jobseeker->experience); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($application->user->jobseeker && $application->user->jobseeker->location): ?>
                        <div class="quick-info-item">
                            <span class="info-label">Location</span>
                            <span class="info-value"><?php echo e($application->user->jobseeker->location); ?></span>
                        </div>
                    <?php endif; ?>
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
                <?php
                    // Check multiple possible resume locations
                    $resumePath = $application->resume
                        ?? ($application->user->jobSeekerProfile->resume_file ?? null)
                        ?? ($application->user->jobseeker->resume_file ?? null);
                ?>

                <?php if($resumePath): ?>
                    <div class="resume-preview">
                        <div class="resume-icon">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                        </div>
                        <p class="resume-filename"><?php echo e(basename($resumePath)); ?></p>
                        <div class="resume-actions">
                            <a href="<?php echo e(asset('storage/' . $resumePath)); ?>" class="ep-btn ep-btn-primary ep-btn-sm" target="_blank">
                                <i class="bi bi-eye"></i>
                                View
                            </a>
                            <a href="<?php echo e(asset('storage/' . $resumePath)); ?>" class="ep-btn ep-btn-outline ep-btn-sm" download>
                                <i class="bi bi-download"></i>
                                Download
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state-sm">
                        <i class="bi bi-file-earmark-x"></i>
                        <p>No resume uploaded</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Required Documents Card -->
        <?php if($application->job->jobRequirements && $application->job->jobRequirements->count() > 0): ?>
        <div class="ep-card documents-card">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-file-earmark-check"></i>
                    Required Documents
                </h3>
            </div>
            <div class="ep-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $application->job->jobRequirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $submitted = $application->submitted_documents && isset($application->submitted_documents[$requirement->id]);
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span><?php echo e($requirement->name); ?></span>
                                <?php if($requirement->is_required): ?>
                                    <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Required</span>
                                <?php endif; ?>
                            </div>
                            <?php if($submitted): ?>
                                <span class="badge bg-success"><i class="bi bi-check"></i> Submitted</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-dash"></i> Pending</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <?php if($application->hasSubmittedRequirements()): ?>
                    <div class="p-3 border-top">
                        <a href="<?php echo e(route('employer.applications.documents', $application->id)); ?>" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-folder2-open me-1"></i> View Submitted Documents
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cover Letter Card -->
        <?php if($application->cover_letter): ?>
        <div class="ep-card cover-letter-card">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-envelope-paper"></i>
                    Cover Letter
                </h3>
            </div>
            <div class="ep-card-body">
                <div class="cover-letter-content" style="white-space: pre-wrap; line-height: 1.6; color: #374151; font-size: 0.9375rem; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%; overflow: hidden;">
                    <?php echo e($application->cover_letter); ?>

                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Job Details Card -->
        <div class="ep-card job-details-card">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-briefcase"></i>
                    Applied Position
                </h3>
            </div>
            <div class="ep-card-body">
                <h4 class="job-title-sm"><?php echo e($application->job->title); ?></h4>
                <div class="job-meta-list">
                    <span class="job-meta-item">
                        <i class="bi bi-geo-alt"></i>
                        <?php echo e($application->job->location); ?>

                    </span>
                    <span class="job-meta-item">
                        <i class="bi bi-clock"></i>
                        <?php echo e($application->job->jobType->name ?? 'N/A'); ?>

                    </span>
                    <span class="job-meta-item">
                        <i class="bi bi-tag"></i>
                        <?php echo e($application->job->category->name ?? 'N/A'); ?>

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
                <?php if($application->user->jobseeker): ?>
                    <div class="profile-sections">
                        <!-- Contact Information -->
                        <div class="profile-section">
                            <h4 class="section-title">Contact Information</h4>
                            <div class="contact-grid">
                                <div class="contact-item">
                                    <div class="contact-icon"><i class="bi bi-envelope"></i></div>
                                    <div class="contact-details">
                                        <span class="contact-label">Email</span>
                                        <span class="contact-value"><?php echo e($application->user->email); ?></span>
                                    </div>
                                </div>
                                <?php if($application->user->jobseeker->phone): ?>
                                    <div class="contact-item">
                                        <div class="contact-icon"><i class="bi bi-telephone"></i></div>
                                        <div class="contact-details">
                                            <span class="contact-label">Phone</span>
                                            <span class="contact-value"><?php echo e($application->user->jobseeker->phone); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if($application->user->jobseeker->location): ?>
                                    <div class="contact-item">
                                        <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
                                        <div class="contact-details">
                                            <span class="contact-label">Location</span>
                                            <span class="contact-value"><?php echo e($application->user->jobseeker->location); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if($application->user->jobseeker->website): ?>
                                    <div class="contact-item">
                                        <div class="contact-icon"><i class="bi bi-globe"></i></div>
                                        <div class="contact-details">
                                            <span class="contact-label">Website</span>
                                            <a href="<?php echo e($application->user->jobseeker->website); ?>" target="_blank" class="contact-value link"><?php echo e($application->user->jobseeker->website); ?></a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Skills -->
                        <?php if($application->user->jobseeker->skills): ?>
                            <div class="profile-section">
                                <h4 class="section-title">Skills</h4>
                                <div class="skills-container">
                                    <?php
                                        $skills = is_string($application->user->jobseeker->skills)
                                            ? explode(',', $application->user->jobseeker->skills)
                                            : (is_array($application->user->jobseeker->skills) ? $application->user->jobseeker->skills : []);
                                    ?>
                                    <?php $__currentLoopData = $skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(trim($skill)): ?>
                                            <span class="skill-badge"><?php echo e(trim($skill)); ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Education -->
                        <?php if($application->user->jobseeker->education): ?>
                            <div class="profile-section">
                                <h4 class="section-title">Education</h4>
                                <?php
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
                                ?>

                                <?php if($educationData && is_array($educationData)): ?>
                                    <div class="education-list">
                                        <?php $__currentLoopData = $educationData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $edu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(is_array($edu)): ?>
                                                <div class="education-item">
                                                    <div class="edu-icon"><i class="bi bi-mortarboard-fill"></i></div>
                                                    <div class="edu-details">
                                                        <?php if(isset($edu['degree']) && $edu['degree']): ?>
                                                            <h5 class="edu-degree"><?php echo e($edu['degree']); ?></h5>
                                                        <?php endif; ?>
                                                        <?php if(isset($edu['school']) && $edu['school']): ?>
                                                            <p class="edu-school"><?php echo e($edu['school']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if(isset($edu['field_of_study']) && $edu['field_of_study']): ?>
                                                            <p class="edu-field"><?php echo e($edu['field_of_study']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if(isset($edu['start_date'])): ?>
                                                            <span class="edu-duration">
                                                                <?php echo e($edu['start_date']); ?> - <?php echo e(isset($edu['currently_studying']) && $edu['currently_studying'] ? 'Present' : ($edu['end_date'] ?? '')); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php elseif(is_string($education)): ?>
                                    <p class="education-text"><?php echo e($education); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Experience -->
                        <?php if($application->user->jobseeker->experience): ?>
                            <div class="profile-section">
                                <h4 class="section-title">Experience Level</h4>
                                <p class="experience-text"><?php echo e($application->user->jobseeker->experience); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-person-x"></i>
                        <p>No profile information available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Screening Questions -->
        <?php if($application->preliminary_answers && is_array($application->preliminary_answers)): ?>
            <div class="ep-card">
                <div class="ep-card-header">
                    <h3 class="ep-card-title">
                        <i class="bi bi-patch-question"></i>
                        Screening Questions
                    </h3>
                </div>
                <div class="ep-card-body">
                    <div class="qa-list">
                        <?php $__currentLoopData = $application->preliminary_answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="qa-item">
                                <div class="qa-question">
                                    <span class="qa-number">Q<?php echo e($index + 1); ?></span>
                                    <span class="qa-text"><?php echo e($answer['question'] ?? 'Question not available'); ?></span>
                                </div>
                                <div class="qa-answer">
                                    <?php echo e($answer['answer'] ?? 'No answer provided'); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Application History -->
        <?php if($application->statusHistory && $application->statusHistory->isNotEmpty()): ?>
            <div class="ep-card">
                <div class="ep-card-header">
                    <h3 class="ep-card-title">
                        <i class="bi bi-clock-history"></i>
                        Activity Timeline
                    </h3>
                </div>
                <div class="ep-card-body">
                    <div class="activity-timeline">
                        <?php $__currentLoopData = $application->statusHistory->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $timelineClass = match($history->status) {
                                    'pending' => 'timeline-warning',
                                    'approved' => 'timeline-success',
                                    'rejected' => 'timeline-danger',
                                    default => 'timeline-gray'
                                };
                            ?>
                            <div class="timeline-entry <?php echo e($timelineClass); ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">Status changed to <?php echo e(ucfirst($history->status)); ?></span>
                                        <span class="timeline-time"><?php echo e($history->created_at->format('M d, Y \a\t h:i A')); ?></span>
                                    </div>
                                    <?php if($history->notes): ?>
                                        <p class="timeline-notes"><?php echo e($history->notes); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
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
    color: rgba(255,255,255,0.8);
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
    border: 3px solid rgba(255,255,255,0.3);
}

.applicant-avatar-placeholder-lg {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 600;
    color: #fff;
    border: 3px solid rgba(255,255,255,0.3);
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
    color: rgba(255,255,255,0.9);
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
    color: rgba(255,255,255,0.8);
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
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
}

.header-actions .ep-btn:hover {
    background: rgba(255,255,255,0.25);
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

    .application-sidebar > * {
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

    .application-sidebar > * {
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleShortlist(applicationId) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
    button.disabled = true;

    fetch(`<?php echo e(route('employer.applications.toggleShortlist', $application->id)); ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
                        <form id="interviewForm">
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
                                        <option value="phone">Phone Call</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight: 500;">Location/Link *</label>
                                    <input type="text" class="form-control" id="interview_location" required
                                        placeholder="Office address or meeting link"
                                        style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
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
    new bootstrap.Modal(document.getElementById('interviewModal')).show();
}

function submitInterview() {
    const form = document.getElementById('interviewForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
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

    fetch(`/employer/applications/<?php echo e($application->id); ?>/schedule-interview`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
    const currentDate = '<?php echo e($application->interview_date ? $application->interview_date->format("Y-m-d") : ""); ?>';
    const currentTime = '<?php echo e($application->interview_time ?? ""); ?>';
    const currentType = '<?php echo e($application->interview_type ?? ""); ?>';
    const currentLocation = '<?php echo e($application->interview_location ?? ""); ?>';

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
                        <form id="rescheduleForm">
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
                                        <option value="phone" ${currentType === 'phone' ? 'selected' : ''}>Phone Call</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight: 500;">Location/Link *</label>
                                    <input type="text" class="form-control" id="reschedule_location" value="${currentLocation}" required
                                        placeholder="Office address or meeting link"
                                        style="border-radius: 8px; border: 1px solid var(--ep-gray-200);">
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
    new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
}

function submitReschedule() {
    const form = document.getElementById('rescheduleForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
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

    fetch(`/employer/applications/<?php echo e($application->id); ?>/reschedule-interview`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.employer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/employer/applications/show.blade.php ENDPATH**/ ?>