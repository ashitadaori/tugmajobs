

<?php $__env->startSection('page_title', 'Company Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Company Management</h2>
                    <p class="text-muted mb-0">Manage all companies and their job postings</p>
                </div>
                <a href="<?php echo e(route('admin.company-management.create')); ?>" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Add New Company
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="<?php echo e(route('admin.company-management.index')); ?>" method="GET">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by company name, email, location..."
                               value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <?php if($company->logo_url): ?>
                                <img src="<?php echo e($company->logo_url); ?>" 
                                     alt="<?php echo e($company->name); ?>"
                                     class="rounded-circle border border-2"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                     style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold;">
                                    <?php echo e($company->initials); ?>

                                </div>
                            <?php endif; ?>
                        </div>

                        <h5 class="card-title text-center mb-2"><?php echo e($company->name); ?></h5>
                        
                        <?php if($company->email): ?>
                            <p class="text-muted text-center small mb-2">
                                <i class="bi bi-envelope"></i> <?php echo e($company->email); ?>

                            </p>
                        <?php endif; ?>

                        <?php if($company->location): ?>
                            <p class="text-muted text-center small mb-3">
                                <i class="bi bi-geo-alt"></i> <?php echo e($company->location); ?>

                            </p>
                        <?php endif; ?>

                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="mb-0 text-primary"><?php echo e($company->jobs_count); ?></h4>
                                    <small class="text-muted">Jobs</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-0">
                                    <?php if($company->is_active): ?>
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                    <?php endif; ?>
                                </h4>
                                <small class="text-muted">Status</small>
                            </div>
                        </div>

                        <div class="btn-group w-100 mb-2">
                            <a href="<?php echo e(route('admin.company-management.show', $company)); ?>" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="<?php echo e(route('admin.company-management.edit', $company)); ?>" 
                               class="btn btn-outline-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>

                        <form action="<?php echo e(route('admin.company-management.destroy', $company)); ?>" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this company?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-building" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No companies found</p>
                        <a href="<?php echo e(route('admin.company-management.create')); ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Add First Company
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if($companies->hasPages()): ?>
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($companies->links()); ?>

        </div>
    <?php endif; ?>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/company-management/index.blade.php ENDPATH**/ ?>