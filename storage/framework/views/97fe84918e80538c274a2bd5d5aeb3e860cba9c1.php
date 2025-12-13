

<?php $__env->startSection('page-title', 'My Job Applications'); ?>

<?php $__env->startSection('jobseeker-content'); ?>
<style>
/* ============================================
   JOB APPLICATIONS - ENHANCED MODERN UI
   ============================================ */

.applications-container {
    padding: 0;
}

/* === Enhanced Page Header === */
.applications-header {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    border-radius: 16px;
}

.header-decoration {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
}

.header-decoration .circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}

.header-decoration .circle-1 {
    width: 200px;
    height: 200px;
    top: -100px;
    right: -50px;
}

.header-decoration .circle-2 {
    width: 150px;
    height: 150px;
    bottom: -75px;
    left: 10%;
}

.header-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.header-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.25);
}

.header-icon i {
    font-size: 1.5rem;
    color: #fff;
}

.header-text h1 {
    color: #fff;
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.025em;
}

.header-text p {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.95rem;
    margin: 0.25rem 0 0 0;
}

.btn-find-jobs {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #fff;
    color: #4f46e5;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
    margin-left: auto;
}

.btn-find-jobs:hover {
    background: #f0f0ff;
    color: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

/* === Stats Grid - Glass Morphism === */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #e5e7eb;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stat-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--stat-color);
    border-radius: 4px 0 0 4px;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
}

.stat-icon-wrapper {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    background: var(--stat-bg);
    color: var(--stat-color);
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    line-height: 1;
    letter-spacing: -0.025em;
}

.stat-label {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-top: 0.25rem;
}

/* Stat Variants */
.stat-card.total { --stat-color: #4f46e5; --stat-bg: #eef2ff; }
.stat-card.pending { --stat-color: #f59e0b; --stat-bg: #fef3c7; }
.stat-card.approved { --stat-color: #10b981; --stat-bg: #d1fae5; }
.stat-card.rejected { --stat-color: #ef4444; --stat-bg: #fee2e2; }

/* === Application Cards === */
.applications-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.application-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.application-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--card-accent, #e5e7eb);
}

.application-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.application-card.pending { --card-accent: #f59e0b; }
.application-card.approved { --card-accent: #10b981; }
.application-card.rejected { --card-accent: #ef4444; }

/* Card Header */
.card-main {
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
}

.job-details {
    flex: 1;
    min-width: 0;
}

.job-title-link {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    text-decoration: none;
    display: block;
    margin-bottom: 0.75rem;
    transition: color 0.2s ease;
}

.job-title-link:hover {
    color: #4f46e5;
}

.job-meta-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
}

.meta-tag i {
    color: #9ca3af;
    font-size: 0.75rem;
}

/* Status Section */
.status-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.625rem;
    flex-shrink: 0;
}

.applied-date {
    font-size: 0.75rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 700;
}

.status-pill.pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.status-pill.approved {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.status-pill.rejected {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
}

/* Feedback Section */
.feedback-section {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.feedback-section.success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.feedback-section.danger {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

.feedback-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.feedback-section.success .feedback-icon {
    background: #10b981;
    color: #fff;
}

.feedback-section.danger .feedback-icon {
    background: #ef4444;
    color: #fff;
}

.feedback-content {
    flex: 1;
}

.feedback-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.feedback-section.success .feedback-label { color: #065f46; }
.feedback-section.danger .feedback-label { color: #991b1b; }

.feedback-text {
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.5;
}

/* Action Buttons */
.card-actions {
    display: flex;
    gap: 0.625rem;
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-top: 1px solid #f1f5f9;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 10px;
    font-size: 0.8125rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn.primary {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
}

.action-btn.secondary {
    background: #fff;
    color: #4f46e5;
    border: 1px solid #e0e7ff;
}

.action-btn.secondary:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
}

.action-btn.danger {
    background: #fff;
    color: #ef4444;
    border: 1px solid #fecaca;
}

.action-btn.danger:hover {
    background: #fef2f2;
    border-color: #fca5a5;
}

/* Job Deleted State */
.application-card.job-deleted {
    --card-accent: #9ca3af;
    background: #f9fafb;
}

.application-card.job-deleted .job-title-link {
    color: #6b7280;
    cursor: default;
}

.deleted-notice {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #9ca3af;
    font-size: 0.8125rem;
}

.deleted-notice i {
    color: #f59e0b;
}

/* === Empty State === */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
}

.empty-illustration {
    width: 120px;
    height: 120px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.empty-illustration::after {
    content: '';
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    border: 2px dashed #c7d2fe;
}

.empty-illustration i {
    font-size: 3rem;
    color: #6366f1;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.empty-state p {
    font-size: 1rem;
    color: #6b7280;
    margin: 0 0 2rem 0;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-browse-jobs {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #fff;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}

.btn-browse-jobs:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
    color: #fff;
}

/* === Pagination === */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination-wrapper .pagination {
    background: #fff;
    border-radius: 12px;
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    display: flex;
    gap: 0.25rem;
}

.pagination-wrapper .page-item .page-link {
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    color: #6b7280;
    transition: all 0.2s ease;
}

.pagination-wrapper .page-item.active .page-link {
    background: #4f46e5;
    color: #fff;
}

.pagination-wrapper .page-item .page-link:hover {
    background: #f1f5f9;
    color: #4f46e5;
}

/* === Responsive === */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .applications-header {
        padding: 1.25rem;
        margin-bottom: 1rem;
        border-radius: 12px;
    }

    .header-inner {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .header-left {
        flex-direction: column;
    }

    .header-text h1 {
        font-size: 1.5rem;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-number {
        font-size: 1.5rem;
    }

    .card-main {
        flex-direction: column;
        gap: 1rem;
    }

    .status-section {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .card-actions {
        flex-wrap: wrap;
    }

    .action-btn {
        flex: 1;
        min-width: 100px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }

    .stat-icon-wrapper {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }

    .stat-number {
        font-size: 1.25rem;
    }

    .job-meta-tags {
        gap: 0.5rem;
    }

    .meta-tag {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}

/* === Animations === */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.application-card {
    animation: fadeInUp 0.4s ease-out backwards;
}

.application-card:nth-child(1) { animation-delay: 0.05s; }
.application-card:nth-child(2) { animation-delay: 0.1s; }
.application-card:nth-child(3) { animation-delay: 0.15s; }
.application-card:nth-child(4) { animation-delay: 0.2s; }
.application-card:nth-child(5) { animation-delay: 0.25s; }

.stat-card {
    animation: fadeInUp 0.4s ease-out backwards;
}

.stat-card:nth-child(1) { animation-delay: 0s; }
.stat-card:nth-child(2) { animation-delay: 0.05s; }
.stat-card:nth-child(3) { animation-delay: 0.1s; }
.stat-card:nth-child(4) { animation-delay: 0.15s; }
</style>

<div class="applications-container">
    <!-- Enhanced Page Header -->
    <header class="applications-header">
        <div class="header-decoration">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
        </div>
        <div class="header-inner">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="header-text">
                    <h1>Job Applications</h1>
                    <p>Track and manage your career opportunities</p>
                </div>
            </div>
            <a href="<?php echo e(route('jobs')); ?>" class="btn-find-jobs">
                <i class="fas fa-search"></i>
                Find Jobs
            </a>
        </div>
    </header>

    <?php if($jobApplications->isEmpty()): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-illustration">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3>No Applications Yet</h3>
            <p>You haven't applied to any jobs yet. Start exploring opportunities and take the first step in your career journey!</p>
            <a href="<?php echo e(route('jobs')); ?>" class="btn-browse-jobs">
                <i class="fas fa-search"></i>
                Browse Jobs
            </a>
        </div>
    <?php else: ?>
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo e($jobApplications->total()); ?></div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo e($jobApplications->where('status', 'pending')->count()); ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="stat-card approved">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo e($jobApplications->where('status', 'approved')->count()); ?></div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo e($jobApplications->where('status', 'rejected')->count()); ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <div class="applications-list">
            <?php $__currentLoopData = $jobApplications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobApplication): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isNewRejection = $jobApplication->status == 'rejected' &&
                                     $jobApplication->updated_at->diffInDays(now()) < 3;
                ?>
                <?php if($jobApplication->job): ?>
                <div class="application-card <?php echo e($jobApplication->status); ?>">
                    <div class="card-main">
                        <div class="job-details">
                            <a href="<?php echo e(route('jobDetail', $jobApplication->job_id)); ?>" class="job-title-link">
                                <?php echo e($jobApplication->job->title); ?>

                            </a>
                            <div class="job-meta-tags">
                                <span class="meta-tag">
                                    <i class="fas fa-briefcase"></i>
                                    <?php echo e($jobApplication->job->jobType->name ?? 'N/A'); ?>

                                </span>
                                <span class="meta-tag">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo e(Str::limit($jobApplication->job->location ?? 'N/A', 30)); ?>

                                </span>
                                <span class="meta-tag">
                                    <i class="fas fa-users"></i>
                                    <?php echo e($jobApplication->job->applications->count()); ?> applicant(s)
                                </span>
                            </div>
                        </div>
                        <div class="status-section">
                            <div class="applied-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo e(\Carbon\Carbon::parse($jobApplication->applied_date)->format('M d, Y')); ?>

                            </div>
                            <?php if($jobApplication->status == 'pending'): ?>
                                <span class="status-pill pending">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php elseif($jobApplication->status == 'approved'): ?>
                                <span class="status-pill approved">
                                    <i class="fas fa-check"></i> Approved
                                </span>
                            <?php elseif($jobApplication->status == 'rejected'): ?>
                                <span class="status-pill rejected">
                                    <i class="fas fa-times"></i> Rejected
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($jobApplication->status == 'approved' && $jobApplication->statusHistory()->where('status', 'approved')->latest()->first()?->notes): ?>
                        <div class="feedback-section success">
                            <div class="feedback-icon">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <div class="feedback-content">
                                <div class="feedback-label">Employer Message</div>
                                <div class="feedback-text"><?php echo e(Str::limit($jobApplication->statusHistory()->where('status', 'approved')->latest()->first()->notes, 150)); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($jobApplication->status == 'rejected'): ?>
                        <?php
                            $rejectionNote = $jobApplication->statusHistory()->where('status', 'rejected')->latest()->first()?->notes;
                        ?>
                        <?php if($rejectionNote): ?>
                            <div class="feedback-section danger">
                                <div class="feedback-icon">
                                    <i class="fas fa-info"></i>
                                </div>
                                <div class="feedback-content">
                                    <div class="feedback-label">Feedback</div>
                                    <div class="feedback-text"><?php echo e(Str::limit($rejectionNote, 150)); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="card-actions">
                        <a href="<?php echo e(route('account.showJobApplication', $jobApplication->id)); ?>" class="action-btn primary">
                            <i class="fas fa-eye"></i>
                            Details
                        </a>
                        <a href="<?php echo e(route('jobDetail', $jobApplication->job_id)); ?>" class="action-btn secondary">
                            <i class="fas fa-briefcase"></i>
                            View Job
                        </a>
                        <button type="button" class="action-btn danger" onclick="confirmRemoveApplication(<?php echo e($jobApplication->id); ?>)">
                            <i class="fas fa-trash-alt"></i>
                            <?php echo e($jobApplication->status == 'rejected' ? 'Remove' : 'Withdraw'); ?>

                        </button>
                    </div>
                </div>
                <?php else: ?>
                
                <div class="application-card <?php echo e($jobApplication->status); ?> job-deleted">
                    <div class="card-main">
                        <div class="job-details">
                            <span class="job-title-link">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Job No Longer Available
                            </span>
                            <div class="deleted-notice">
                                <i class="fas fa-info-circle"></i>
                                This job posting has been removed by the employer
                            </div>
                        </div>
                        <div class="status-section">
                            <div class="applied-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo e(\Carbon\Carbon::parse($jobApplication->applied_date)->format('M d, Y')); ?>

                            </div>
                            <span class="status-pill <?php echo e($jobApplication->status); ?>">
                                <i class="fas fa-<?php echo e($jobApplication->status == 'pending' ? 'clock' : ($jobApplication->status == 'approved' ? 'check' : 'times')); ?>"></i>
                                <?php echo e(ucfirst($jobApplication->status)); ?>

                            </span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button type="button" class="action-btn danger" onclick="confirmRemoveApplication(<?php echo e($jobApplication->id); ?>)">
                            <i class="fas fa-trash-alt"></i>
                            Remove
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Pagination -->
        <?php if($jobApplications->hasPages()): ?>
        <div class="pagination-wrapper">
            <?php echo e($jobApplications->links()); ?>

        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function confirmRemoveApplication(id) {
    if (confirm("Are you sure you want to withdraw this application? You can reapply after withdrawing.")) {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        fetch('<?php echo e(route("account.removeJobs")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                if (typeof showToast === 'function') {
                    showToast('Application withdrawn successfully!', 'success');
                }
                setTimeout(() => { window.location.reload(); }, 1500);
            } else {
                if (typeof showToast === 'function') {
                    showToast('Failed to withdraw application.', 'error');
                }
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') {
                showToast('An error occurred.', 'error');
            }
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.jobseeker', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/job/my-job-application.blade.php ENDPATH**/ ?>