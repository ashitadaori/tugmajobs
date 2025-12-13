

<?php $__env->startSection('page_title', 'Notification Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Manage your notification preferences</p>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <!-- Notification Settings -->
            <div class="table-card mb-4">
                <div class="table-header">
                    <h5 class="table-title">Email Notifications</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('employer.settings.notifications.update')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="notification_preferences[email_notifications]" 
                                   id="emailNotifications" value="1" 
                                   <?php echo e((old('notification_preferences.email_notifications', $employer->notification_preferences['email_notifications'] ?? false)) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="emailNotifications">
                                <strong>Email Notifications</strong>
                                <div class="small text-muted">Receive important updates via email</div>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="notification_preferences[marketing_emails]" 
                                   id="marketingEmails" value="1"
                                   <?php echo e((old('notification_preferences.marketing_emails', $employer->notification_preferences['marketing_emails'] ?? false)) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="marketingEmails">
                                <strong>Marketing Emails</strong>
                                <div class="small text-muted">Receive promotional emails and newsletters</div>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="notification_preferences[application_alerts]" 
                                   id="applicationAlerts" value="1"
                                   <?php echo e((old('notification_preferences.application_alerts', $employer->notification_preferences['application_alerts'] ?? true)) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="applicationAlerts">
                                <strong>New Application Alerts</strong>
                                <div class="small text-muted">Get notified when someone applies to your jobs</div>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="notification_preferences[job_alerts]" 
                                   id="jobAlerts" value="1"
                                   <?php echo e((old('notification_preferences.job_alerts', $employer->notification_preferences['job_alerts'] ?? true)) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="jobAlerts">
                                <strong>Job Status Updates</strong>
                                <div class="small text-muted">Receive notifications about job posting status changes</div>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.employer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/employer/settings/notifications.blade.php ENDPATH**/ ?>