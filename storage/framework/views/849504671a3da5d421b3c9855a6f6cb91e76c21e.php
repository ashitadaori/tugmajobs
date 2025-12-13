

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Maintenance Mode</h1>
            <p class="text-muted">Control maintenance notifications for different user types</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.maintenance.update')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row g-4">
            <!-- Jobseeker Maintenance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Job Seeker Maintenance</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="jobseeker_active" 
                                   name="jobseeker_active" value="1"
                                   <?php echo e($jobseekerMaintenance && $jobseekerMaintenance->is_active ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="jobseeker_active">
                                <strong>Enable Maintenance Mode</strong>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="jobseeker_message" class="form-label">Maintenance Message</label>
                            <textarea class="form-control <?php $__errorArgs = ['jobseeker_message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="jobseeker_message" name="jobseeker_message" rows="4" 
                                      required><?php echo e(old('jobseeker_message', $jobseekerMaintenance->message ?? '')); ?></textarea>
                            <?php $__errorArgs = ['jobseeker_message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">This message will be shown to job seekers when maintenance is active</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Status:</strong> 
                            <?php if($jobseekerMaintenance && $jobseekerMaintenance->is_active): ?>
                                <span class="badge bg-warning text-dark">Maintenance Active</span>
                            <?php else: ?>
                                <span class="badge bg-success">Normal Operation</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employer Maintenance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-building me-2"></i>Employer Maintenance</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="employer_active" 
                                   name="employer_active" value="1"
                                   <?php echo e($employerMaintenance && $employerMaintenance->is_active ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="employer_active">
                                <strong>Enable Maintenance Mode</strong>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="employer_message" class="form-label">Maintenance Message</label>
                            <textarea class="form-control <?php $__errorArgs = ['employer_message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="employer_message" name="employer_message" rows="4" 
                                      required><?php echo e(old('employer_message', $employerMaintenance->message ?? '')); ?></textarea>
                            <?php $__errorArgs = ['employer_message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">This message will be shown to employers when maintenance is active</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Status:</strong> 
                            <?php if($employerMaintenance && $employerMaintenance->is_active): ?>
                                <span class="badge bg-warning text-dark">Maintenance Active</span>
                            <?php else: ?>
                                <span class="badge bg-success">Normal Operation</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-2"></i>Save Maintenance Settings
            </button>
        </div>
    </form>

    <!-- Information Card -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>How It Works</h5>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Admins are never affected</strong> - You can always access the system</li>
                <li><strong>Users see a banner notification</strong> - They can still use the system but will see your maintenance message</li>
                <li><strong>Independent control</strong> - Enable maintenance for job seekers and employers separately</li>
                <li><strong>Customizable messages</strong> - Tailor the message for each user type</li>
                <li><strong>Instant updates</strong> - Changes take effect immediately</li>
            </ul>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/maintenance/index.blade.php ENDPATH**/ ?>