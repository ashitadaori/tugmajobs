

<?php $__env->startSection('page-title', 'Resume Builder'); ?>

<?php $__env->startSection('jobseeker-content'); ?>
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Resume Builder</h4>
                    <p class="text-muted mb-0">Create professional resumes with our easy-to-use builder</p>
                </div>
            </div>
        </div>
        
        <div class="card-body p-4">
            <?php if($resumes->isEmpty()): ?>
                <!-- Template Selection -->
                <div class="text-center mb-4">
                    <h5 class="mb-3">Choose a Template to Get Started</h5>
                    <p class="text-muted">Select a professional template and we'll auto-fill your basic information</p>
                </div>
                
                <div class="row g-4">
                    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4">
                            <div class="card template-card h-100">
                                <div class="card-body text-center">
                                    <div class="template-preview mb-3">
                                        <i class="fas fa-file-alt fa-4x text-primary"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo e($template->name); ?></h5>
                                    <p class="card-text text-muted"><?php echo e($template->description); ?></p>
                                    <a href="<?php echo e(route('account.resume-builder.create', ['template' => $template->id])); ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Use This Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <!-- Existing Resumes -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Your Resumes</h5>
                    <a href="<?php echo e(route('account.resume-builder.index')); ?>?new=1" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create New Resume
                    </a>
                </div>
                
                <?php if(request()->get('new') == 1): ?>
                    <!-- Show templates -->
                    <div class="row g-4 mb-4">
                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-4">
                                <div class="card template-card h-100">
                                    <div class="card-body text-center">
                                        <div class="template-preview mb-3">
                                            <i class="fas fa-file-alt fa-4x text-primary"></i>
                                        </div>
                                        <h5 class="card-title"><?php echo e($template->name); ?></h5>
                                        <p class="card-text text-muted"><?php echo e($template->description); ?></p>
                                        <a href="<?php echo e(route('account.resume-builder.create', ['template' => $template->id])); ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Use This Template
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="row g-3">
                    <?php $__currentLoopData = $resumes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resume): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo e($resume->title); ?></h6>
                                            <small class="text-muted">
                                                Template: <?php echo e($resume->template->name); ?> • 
                                                Updated <?php echo e($resume->updated_at->diffForHumans()); ?>

                                            </small>
                                        </div>
                                        <div class="btn-group">
                                            <a href="<?php echo e(route('account.resume-builder.preview', $resume->id)); ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('account.resume-builder.edit', $resume->id)); ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo e(route('account.resume-builder.download', $resume->id)); ?>" 
                                               class="btn btn-sm btn-outline-success" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form action="<?php echo e(route('account.resume-builder.destroy', $resume->id)); ?>" 
                                                  method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Delete this resume?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.template-card {
    transition: transform 0.2s;
    cursor: pointer;
}
.template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.template-preview {
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.jobseeker', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/resume-builder/index.blade.php ENDPATH**/ ?>