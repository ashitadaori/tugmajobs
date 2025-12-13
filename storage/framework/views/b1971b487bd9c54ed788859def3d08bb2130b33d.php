

<?php $__env->startSection('page_title', 'Settings'); ?>

<?php $__env->startSection('content'); ?>
<!-- Account Information -->
<div class="ep-card ep-mb-6">
    <div class="ep-card-header">
        <h3 class="ep-card-title">
            <i class="bi bi-person-circle"></i>
            Account Information
        </h3>
    </div>
    <div class="ep-card-body">
        <form>
            <div class="settings-grid">
                <div class="ep-form-group">
                    <label class="ep-form-label">Full Name</label>
                    <input type="text" class="ep-form-input" value="<?php echo e(Auth::user()->name ?? ''); ?>">
                </div>
                <div class="ep-form-group">
                    <label class="ep-form-label">Email Address</label>
                    <input type="email" class="ep-form-input" value="<?php echo e(Auth::user()->email ?? ''); ?>">
                </div>
                <div class="ep-form-group">
                    <label class="ep-form-label">Job Title</label>
                    <input type="text" class="ep-form-input" value="" placeholder="e.g. HR Manager">
                </div>
                <div class="ep-form-group">
                    <label class="ep-form-label">Phone Number</label>
                    <input type="tel" class="ep-form-input" value="" placeholder="e.g. +63 912 345 6789">
                </div>
            </div>
            <button type="button" class="ep-btn ep-btn-primary">
                <i class="bi bi-check-circle"></i>
                Save Changes
            </button>
        </form>
    </div>
</div>

<!-- Preferences -->
<div class="ep-card ep-mb-6">
    <div class="ep-card-header">
        <h3 class="ep-card-title">
            <i class="bi bi-sliders"></i>
            Preferences
        </h3>
    </div>
    <div class="ep-card-body">
        <form>
            <div class="settings-grid">
                <div class="ep-form-group">
                    <label class="ep-form-label">Time Zone</label>
                    <select class="ep-form-select">
                        <option value="Asia/Manila" selected>Philippine Time (UTC+8)</option>
                        <option value="Asia/Singapore">Singapore Time (UTC+8)</option>
                        <option value="Asia/Hong_Kong">Hong Kong Time (UTC+8)</option>
                        <option value="UTC">UTC (UTC+0)</option>
                    </select>
                </div>
                <div class="ep-form-group">
                    <label class="ep-form-label">Language</label>
                    <select class="ep-form-select">
                        <option value="en" selected>English</option>
                        <option value="fil">Filipino</option>
                    </select>
                </div>
                <div class="ep-form-group">
                    <label class="ep-form-label">Date Format</label>
                    <select class="ep-form-select">
                        <option value="MM/DD/YYYY" selected>MM/DD/YYYY</option>
                        <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                        <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                    </select>
                </div>
                <div class="ep-form-group">
                    <label class="ep-form-label">Currency</label>
                    <select class="ep-form-select">
                        <option value="PHP" selected>PHP (₱)</option>
                        <option value="USD">USD ($)</option>
                    </select>
                </div>
            </div>
            <button type="button" class="ep-btn ep-btn-primary">
                <i class="bi bi-check-circle"></i>
                Save Preferences
            </button>
        </form>
    </div>
</div>

<!-- Privacy Settings -->
<div class="ep-card ep-mb-6">
    <div class="ep-card-header">
        <h3 class="ep-card-title">
            <i class="bi bi-shield-check"></i>
            Privacy Settings
        </h3>
    </div>
    <div class="ep-card-body">
        <div class="settings-switches">
            <label class="settings-switch">
                <div class="switch-content">
                    <div class="switch-title">Make company profile public</div>
                    <div class="switch-description">Allow job seekers to find and view your company profile</div>
                </div>
                <div class="switch-toggle">
                    <input type="checkbox" id="profileVisibility" checked>
                    <span class="toggle-slider"></span>
                </div>
            </label>

            <label class="settings-switch">
                <div class="switch-content">
                    <div class="switch-title">Show contact information</div>
                    <div class="switch-description">Display your contact details on job postings</div>
                </div>
                <div class="switch-toggle">
                    <input type="checkbox" id="showContactInfo" checked>
                    <span class="toggle-slider"></span>
                </div>
            </label>

            <label class="settings-switch">
                <div class="switch-content">
                    <div class="switch-title">Allow direct messages from candidates</div>
                    <div class="switch-description">Let candidates contact you directly through the platform</div>
                </div>
                <div class="switch-toggle">
                    <input type="checkbox" id="allowDirectMessages">
                    <span class="toggle-slider"></span>
                </div>
            </label>

            <label class="settings-switch">
                <div class="switch-content">
                    <div class="switch-title">Share anonymous analytics</div>
                    <div class="switch-description">Help improve the platform by sharing usage data</div>
                </div>
                <div class="switch-toggle">
                    <input type="checkbox" id="shareAnalytics">
                    <span class="toggle-slider"></span>
                </div>
            </label>
        </div>
    </div>
</div>

<!-- Application Settings -->
<div class="ep-card ep-mb-6">
    <div class="ep-card-header">
        <h3 class="ep-card-title">
            <i class="bi bi-file-earmark-text"></i>
            Application Settings
        </h3>
    </div>
    <div class="ep-card-body">
        <form>
            <div class="settings-grid">
                <div class="ep-form-group">
                    <label class="ep-form-label">Auto-close jobs after</label>
                    <select class="ep-form-select">
                        <option value="30" selected>30 days</option>
                        <option value="60">60 days</option>
                        <option value="90">90 days</option>
                        <option value="never">Never</option>
                    </select>
                </div>
                <div class="ep-form-group">
                    <label class="ep-form-label">Maximum applications per job</label>
                    <input type="number" class="ep-form-input" value="100" min="1" max="1000">
                </div>
            </div>

            <div class="checkbox-options">
                <label class="checkbox-option">
                    <input type="checkbox" id="requireCoverLetter" checked>
                    <span class="checkmark"></span>
                    <span class="checkbox-label">Require cover letter for all applications</span>
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" id="autoReply" checked>
                    <span class="checkmark"></span>
                    <span class="checkbox-label">Send automatic reply to applicants</span>
                </label>
            </div>

            <button type="button" class="ep-btn ep-btn-primary" style="margin-top: 20px;">
                <i class="bi bi-check-circle"></i>
                Save Settings
            </button>
        </form>
    </div>
</div>

<!-- Danger Zone -->
<div class="ep-card danger-zone">
    <div class="ep-card-header danger-header">
        <h3 class="ep-card-title">
            <i class="bi bi-exclamation-triangle"></i>
            Danger Zone
        </h3>
    </div>
    <div class="ep-card-body">
        <div class="danger-actions">
            <div class="danger-action">
                <div class="danger-info">
                    <h4>Deactivate Account</h4>
                    <p>Temporarily deactivate your account. Your profile and job postings will be hidden. You can reactivate it anytime by logging back in.</p>
                </div>
                <button type="button" class="ep-btn ep-btn-outline warning-btn" data-bs-toggle="modal" data-bs-target="#deactivateAccountModal">
                    <i class="bi bi-pause-circle"></i>
                    Deactivate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Account Modal -->
<div class="modal fade" id="deactivateAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deactivate Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo e(route('employer.settings.account.deactivate')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <strong>Note:</strong> Your account will be temporarily deactivated. Your profile and job postings will be hidden. You can reactivate by logging in again.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enter your password to confirm:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Deactivate Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--ep-space-4);
    margin-bottom: var(--ep-space-5);
}

/* Toggle Switches */
.settings-switches {
    display: flex;
    flex-direction: column;
    gap: var(--ep-space-4);
}

.settings-switch {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--ep-space-4);
    background: var(--ep-gray-50);
    border-radius: var(--ep-radius-lg);
    cursor: pointer;
    transition: all var(--ep-transition-base);
}

.settings-switch:hover {
    background: var(--ep-primary-50);
}

.switch-content {
    flex: 1;
}

.switch-title {
    font-weight: 600;
    color: var(--ep-gray-800);
    margin-bottom: 4px;
}

.switch-description {
    font-size: var(--ep-font-size-sm);
    color: var(--ep-gray-500);
}

.switch-toggle {
    position: relative;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
    margin-left: var(--ep-space-4);
}

.switch-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--ep-gray-300);
    transition: 0.3s;
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.switch-toggle input:checked + .toggle-slider {
    background-color: var(--ep-primary);
}

.switch-toggle input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

/* Checkbox Options */
.checkbox-options {
    display: flex;
    flex-direction: column;
    gap: var(--ep-space-3);
}

.checkbox-option {
    display: flex;
    align-items: center;
    gap: var(--ep-space-3);
    cursor: pointer;
    padding: var(--ep-space-3) 0;
}

.checkbox-option input {
    display: none;
}

.checkmark {
    width: 22px;
    height: 22px;
    border: 2px solid var(--ep-gray-300);
    border-radius: var(--ep-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all var(--ep-transition-base);
}

.checkbox-option input:checked + .checkmark {
    background: var(--ep-primary);
    border-color: var(--ep-primary);
}

.checkbox-option input:checked + .checkmark::after {
    content: '\2713';
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.checkbox-label {
    font-size: var(--ep-font-size-sm);
    color: var(--ep-gray-700);
}

/* Danger Zone */
.danger-zone {
    border-color: var(--ep-danger);
}

.danger-header {
    background: linear-gradient(135deg, var(--ep-danger), var(--ep-danger-light));
}

.danger-header .ep-card-title {
    color: white !important;
}

.danger-header .ep-card-title i {
    color: white !important;
}

.danger-actions {
    display: flex;
    flex-direction: column;
    gap: var(--ep-space-4);
}

.danger-action {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--ep-space-4);
}

.danger-info h4 {
    font-size: var(--ep-font-size-base);
    font-weight: 600;
    color: var(--ep-gray-800);
    margin: 0 0 4px 0;
}

.danger-info p {
    font-size: var(--ep-font-size-sm);
    color: var(--ep-gray-500);
    margin: 0;
}

.warning-btn {
    border-color: var(--ep-warning);
    color: var(--ep-warning);
}

.warning-btn:hover {
    background: var(--ep-warning);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }

    .settings-switch {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--ep-space-3);
    }

    .switch-toggle {
        margin-left: 0;
    }

    .danger-action {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.employer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/employer/settings/index.blade.php ENDPATH**/ ?>