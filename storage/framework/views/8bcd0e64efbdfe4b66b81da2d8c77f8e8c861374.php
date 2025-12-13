

<?php $__env->startSection('page_title', 'Site Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-gear text-primary me-2"></i>
                        Site Settings
                    </h2>
                    <p class="text-muted mb-0">Configure your job portal settings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-sliders me-2"></i>
                        General Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <!-- Site Name -->
                        <div class="mb-4">
                            <label for="site_name" class="form-label fw-bold">
                                <i class="bi bi-building me-1"></i>
                                Site Name
                            </label>
                            <input type="text" 
                                   class="form-control <?php $__errorArgs = ['site_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="site_name" 
                                   name="site_name" 
                                   value="<?php echo e(old('site_name', $settings['site_name'])); ?>"
                                   required>
                            <?php $__errorArgs = ['site_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">The name of your job portal</small>
                        </div>

                        <!-- Site Email -->
                        <div class="mb-4">
                            <label for="site_email" class="form-label fw-bold">
                                <i class="bi bi-envelope me-1"></i>
                                Site Email
                            </label>
                            <input type="email" 
                                   class="form-control <?php $__errorArgs = ['site_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="site_email" 
                                   name="site_email" 
                                   value="<?php echo e(old('site_email', $settings['site_email'])); ?>"
                                   required>
                            <?php $__errorArgs = ['site_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">Email address for system notifications</small>
                        </div>

                        <!-- Jobs Per Page -->
                        <div class="mb-4">
                            <label for="jobs_per_page" class="form-label fw-bold">
                                <i class="bi bi-list-ol me-1"></i>
                                Jobs Per Page
                            </label>
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['jobs_per_page'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="jobs_per_page" 
                                   name="jobs_per_page" 
                                   value="<?php echo e(old('jobs_per_page', $settings['jobs_per_page'])); ?>"
                                   min="5" 
                                   max="100"
                                   required>
                            <?php $__errorArgs = ['jobs_per_page'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">Number of jobs to display per page (5-100)</small>
                        </div>

                        <!-- Enable Job Alerts -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="enable_job_alerts" 
                                       name="enable_job_alerts" 
                                       value="1"
                                       <?php echo e(old('enable_job_alerts', $settings['enable_job_alerts']) ? 'checked' : ''); ?>>
                                <label class="form-check-label fw-bold" for="enable_job_alerts">
                                    <i class="bi bi-bell me-1"></i>
                                    Enable Job Alerts
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Allow users to receive email notifications for new jobs</small>
                        </div>

                        <!-- Enable AI Features -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="enable_ai_features" 
                                       name="enable_ai_features" 
                                       value="1"
                                       <?php echo e(old('enable_ai_features', $settings['enable_ai_features']) ? 'checked' : ''); ?>>
                                <label class="form-check-label fw-bold" for="enable_ai_features">
                                    <i class="bi bi-robot me-1"></i>
                                    Enable AI Features
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Enable AI-powered job matching and recommendations</small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- System Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        System Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Laravel Version:</strong>
                        <span class="float-end"><?php echo e(app()->version()); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>PHP Version:</strong>
                        <span class="float-end"><?php echo e(PHP_VERSION); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Environment:</strong>
                        <span class="float-end badge bg-<?php echo e(app()->environment() === 'production' ? 'success' : 'warning'); ?>">
                            <?php echo e(ucfirst(app()->environment())); ?>

                        </span>
                    </div>
                    <div>
                        <strong>Debug Mode:</strong>
                        <span class="float-end badge bg-<?php echo e(config('app.debug') ? 'danger' : 'success'); ?>">
                            <?php echo e(config('app.debug') ? 'ON' : 'OFF'); ?>

                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.maintenance.index')); ?>" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-tools me-1"></i>
                            Maintenance Mode
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearCache()">
                            <i class="bi bi-trash me-1"></i>
                            Clear Cache
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Are you sure you want to clear all cache?')) {
        fetch('<?php echo e(route("admin.settings.clear-cache")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAdminToast('Cache cleared successfully!', 'success');
            } else {
                showAdminToast('Failed to clear cache', 'error');
            }
        })
        .catch(error => {
            showAdminToast('An error occurred', 'error');
        });
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/settings/index.blade.php ENDPATH**/ ?>