

<?php $__env->startSection('page_title', 'Application Details - ' . $application->user->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="<?php echo e(route('admin.jobs.applicants', $application->job_id)); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Applicants
        </a>
    </div>

    <!-- Applicant Header -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-1">
                    <?php if($application->user->image): ?>
                        <img src="<?php echo e(Storage::url($application->user->image)); ?>" alt="<?php echo e($application->user->name); ?>"
                             class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover; border: 3px solid white;">
                    <?php else: ?>
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center"
                             style="width: 70px; height: 70px; font-size: 1.5rem; color: #667eea;">
                            <?php echo e(strtoupper(substr($application->user->name, 0, 1))); ?>

                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-7">
                    <h3 class="mb-1" style="color: white;"><?php echo e($application->user->name); ?></h3>
                    <p class="mb-1 opacity-75">Applied for: <strong><?php echo e($application->job->title); ?></strong></p>
                    <div class="d-flex gap-3 flex-wrap">
                        <span><i class="bi bi-envelope"></i> <?php echo e($application->user->email); ?></span>
                        <span><i class="bi bi-calendar"></i> Applied <?php echo e($application->created_at->diffForHumans()); ?></span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge <?php echo e($application->getStageBadgeClass()); ?> fs-6 px-3 py-2">
                        <?php echo e($application->getStageName()); ?>

                    </span>
                    <?php if($application->stage_status): ?>
                        <span class="badge <?php echo e($application->getStageStatusBadgeClass()); ?> fs-6 px-3 py-2 ms-2">
                            <?php echo e(ucfirst($application->stage_status)); ?>

                        </span>
                    <?php endif; ?>
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
                        <?php if($application->stage !== 'hired' && $application->stage !== 'rejected'): ?>
                            <?php if($application->stage_status === 'pending'): ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="approveStage(<?php echo e($application->id); ?>)">
                                    <i class="bi bi-check-lg me-1"></i>Approve
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="rejectApplication(<?php echo e($application->id); ?>)">
                                    <i class="bi bi-x-lg me-1"></i>Reject
                                </button>
                            <?php elseif($application->stage_status === 'approved' && $application->canAdvanceStage()): ?>
                                <button type="button" class="btn btn-primary btn-sm" onclick="advanceStage(<?php echo e($application->id); ?>)">
                                    <i class="bi bi-arrow-right me-1"></i>Advance
                                </button>
                            <?php endif; ?>
                            <?php if($application->stage === 'interview' && !$application->hasScheduledInterview()): ?>
                                <button type="button" class="btn btn-info btn-sm text-white" onclick="showScheduleInterviewModal()">
                                    <i class="bi bi-calendar-plus me-1"></i>Schedule Interview
                                </button>
                            <?php endif; ?>
                            <?php if($application->stage === 'interview' && $application->hasScheduledInterview()): ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="markAsHired(<?php echo e($application->id); ?>)">
                                    <i class="bi bi-trophy me-1"></i>Mark as Hired
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="position-relative mb-4" style="padding: 0 40px;">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?php echo e($application->getProgressPercentage()); ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between position-absolute w-100" style="top: -12px; left: 0;">
                            <?php
                                $stages = ['application' => 'Application', 'requirements' => 'Documents', 'interview' => 'Interview', 'hired' => 'Hired'];
                                $currentStageIndex = array_search($application->stage, array_keys($stages));
                            ?>
                            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stageKey => $stageName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $stageIndex = array_search($stageKey, array_keys($stages));
                                    $isCompleted = $stageIndex < $currentStageIndex || ($stageIndex == $currentStageIndex && $application->stage_status === 'approved');
                                    $isActive = $stageKey === $application->stage;
                                ?>
                                <div class="text-center" style="width: 25%;">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2
                                        <?php echo e($isCompleted ? 'bg-success text-white' : ($isActive ? 'bg-primary text-white' : 'bg-light text-muted')); ?>"
                                        style="width: 30px; height: 30px; font-size: 0.8rem;">
                                        <?php if($isCompleted): ?>
                                            <i class="bi bi-check"></i>
                                        <?php else: ?>
                                            <?php echo e($stageIndex + 1); ?>

                                        <?php endif; ?>
                                    </div>
                                    <div class="small <?php echo e($isActive ? 'fw-bold' : ''); ?>"><?php echo e($stageName); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interview Details -->
            <?php if($application->hasScheduledInterview()): ?>
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
                                <span class="d-block"><?php echo e($application->interview_date->format('l, F d, Y')); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="bi bi-clock me-2"></i>Time:</strong>
                                <span class="d-block"><?php echo e(\Carbon\Carbon::parse($application->interview_time)->format('h:i A')); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="bi bi-<?php echo e($application->interview_type === 'video_call' ? 'camera-video' : ($application->interview_type === 'phone' ? 'telephone' : 'building')); ?> me-2"></i>Type:</strong>
                                <span class="d-block"><?php echo e($application->getInterviewTypeName()); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?php if($application->interview_type === 'video_call'): ?>
                                    <strong><i class="bi bi-link me-2"></i>Meeting Link:</strong>
                                    <span class="d-block">
                                        <a href="<?php echo e($application->interview_location); ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>Join Meeting
                                        </a>
                                    </span>
                                <?php else: ?>
                                    <strong><i class="bi bi-geo-alt me-2"></i>Location:</strong>
                                    <span class="d-block"><?php echo e($application->interview_location); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if($application->interview_notes): ?>
                            <div class="alert alert-info mt-2 mb-0">
                                <strong>Notes:</strong> <?php echo e($application->interview_notes); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cover Letter -->
            <?php if($application->cover_letter): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Cover Letter</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;"><?php echo e($application->cover_letter); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status History -->
            <?php if($application->statusHistory->count() > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php $__currentLoopData = $application->statusHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'interview_scheduled' => 'info',
                                        'interview_rescheduled' => 'warning',
                                        'hired' => 'success',
                                    ];
                                    $color = $statusColors[$history->status] ?? 'secondary';
                                ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-<?php echo e($color); ?>"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <strong class="text-<?php echo e($color); ?>"><?php echo e(ucwords(str_replace('_', ' ', $history->status))); ?></strong>
                                            <small class="text-muted"><?php echo e($history->created_at->format('M d, Y h:i A')); ?></small>
                                        </div>
                                        <?php if($history->notes): ?>
                                            <p class="mb-0 mt-1 text-muted"><?php echo e($history->notes); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <?php if($application->resume): ?>
                        <a href="<?php echo e(asset('storage/' . $application->resume)); ?>" target="_blank" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-file-pdf me-2"></i>View Resume
                        </a>
                    <?php endif; ?>
                    <?php if($application->hasSubmittedRequirements()): ?>
                        <a href="<?php echo e(route('admin.jobs.application.documents', $application->id)); ?>" class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-folder2-open me-2"></i>View Documents (<?php echo e(count($application->submitted_documents)); ?>)
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.users.show', $application->user_id)); ?>" class="btn btn-outline-secondary w-100">
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
                    <p class="mb-2"><strong>Position:</strong> <?php echo e($application->job->title); ?></p>
                    <p class="mb-2"><strong>Location:</strong> <?php echo e($application->job->location); ?></p>
                    <p class="mb-2"><strong>Type:</strong> <?php echo e($application->job->jobType->name ?? 'N/A'); ?></p>
                    <p class="mb-0"><strong>Category:</strong> <?php echo e($application->job->category->name ?? 'N/A'); ?></p>
                </div>
            </div>

            <!-- Requirements Status -->
            <?php if($application->job->jobRequirements && $application->job->jobRequirements->count() > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Required Documents</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php $__currentLoopData = $application->job->jobRequirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $submitted = $application->submitted_documents && isset($application->submitted_documents[$requirement->id]);
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo e($requirement->name); ?>

                                    <?php if($submitted): ?>
                                        <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-dash"></i></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
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
                <textarea class="form-control" id="actionNotes" rows="4" placeholder="Enter notes or feedback for the applicant..."></textarea>
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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

document.getElementById('confirmActionBtn').addEventListener('click', function() {
    const notes = document.getElementById('actionNotes').value;
    bootstrap.Modal.getInstance(document.getElementById('notesModal')).hide();

    fetch(`<?php echo e(route('admin.jobs.application.updateStage', $application->id)); ?>`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
                        <form id="interviewForm">
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
                                        <option value="phone">Phone Call</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Location/Link *</label>
                                    <input type="text" class="form-control" id="interview_location" required placeholder="Office address or meeting link">
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

    fetch(`<?php echo e(route('admin.jobs.application.scheduleInterview', $application->id)); ?>`, {
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
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred');
    });
}

function showRescheduleModal() {
    const today = new Date().toISOString().split('T')[0];
    const currentTime = '<?php echo e($application->interview_time ?? ""); ?>';
    const currentType = '<?php echo e($application->interview_type ?? ""); ?>';
    const currentLocation = `<?php echo e($application->interview_location ?? ""); ?>`;

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
                        <form id="rescheduleForm">
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
                                        <option value="phone" ${currentType === 'phone' ? 'selected' : ''}>Phone Call</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Location/Link *</label>
                                    <input type="text" class="form-control" id="reschedule_location" value="${currentLocation}" required>
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

    fetch(`<?php echo e(route('admin.jobs.application.rescheduleInterview', $application->id)); ?>`, {
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
        fetch(`<?php echo e(route('admin.jobs.application.markHired', $application->id)); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/jobs/application-show.blade.php ENDPATH**/ ?>