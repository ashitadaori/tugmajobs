

<?php $__env->startSection('page_title', 'Pending Jobs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Top Action Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Pending Jobs</h2>
                    <p class="text-muted mb-0">Review and approve job postings</p>
                </div>
                <a href="<?php echo e(route('admin.jobs.create')); ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Post New Job
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Pending Jobs Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history text-warning me-2"></i>
                    Pending Jobs (<?php echo e($jobs->total()); ?>)
                </h5>
                <div class="btn-group btn-group-sm">
                    <a href="<?php echo e(route('admin.jobs.index')); ?>" class="btn btn-outline-primary">All Jobs</a>
                    <a href="<?php echo e(route('admin.jobs.pending')); ?>" class="btn btn-outline-warning active">Pending</a>
                    <a href="<?php echo e(route('admin.jobs.create')); ?>" class="btn btn-success">
                        <i class="bi bi-plus"></i> New
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Submitted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($job->title); ?></div>
                                    <?php if($job->posted_by_admin ?? false): ?>
                                        <span class="badge bg-info text-white" style="font-size: 0.7rem;">
                                            <i class="bi bi-shield-check"></i> Admin Posted
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($job->employer->name ?? 'N/A'); ?></td>
                                <td><?php echo e($job->category->name ?? 'N/A'); ?></td>
                                <td><?php echo e($job->jobType->name ?? 'N/A'); ?></td>
                                <td>
                                    <small class="text-muted"><?php echo e($job->created_at->diffForHumans()); ?></small>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo e(route('admin.jobs.show', $job)); ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="View & Review">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                                    <p class="text-muted mt-2">No pending jobs! All caught up.</p>
                                    <a href="<?php echo e(route('admin.jobs.index')); ?>" class="btn btn-outline-primary">
                                        View All Jobs
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if($jobs->hasPages()): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing <?php echo e($jobs->firstItem()); ?> to <?php echo e($jobs->lastItem()); ?> of <?php echo e($jobs->total()); ?> jobs
                    </div>
                    <nav>
                        <?php echo e($jobs->links('pagination::bootstrap-5')); ?>

                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Custom styles */
.table-hover tbody tr:hover {
    background-color: #fff3cd;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/jobs/pending.blade.php ENDPATH**/ ?>