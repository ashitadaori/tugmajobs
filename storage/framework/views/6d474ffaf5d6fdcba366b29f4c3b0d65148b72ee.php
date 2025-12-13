

<?php $__env->startSection('page-title', 'Account Settings'); ?>

<?php $__env->startSection('jobseeker-content'); ?>
<style>
/* Settings Professional Styles */
.settings-pro {
    padding: 0;
}

/* Page Header */
.page-header-settings {
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #e5e7eb;
}

.page-header-settings h1 {
    font-size: 1.375rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.375rem 0;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.page-header-settings h1 i {
    color: #4f46e5;
    font-size: 1.25rem;
}

.page-header-settings p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Settings Card */
.settings-card {
    background: white;
    border-radius: 0.875rem;
    border: 1px solid #e5e7eb;
    margin-bottom: 1.25rem;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.settings-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.settings-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    background: #f9fafb;
}

.settings-card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.settings-card-header h3 i {
    color: #4f46e5;
    font-size: 0.9375rem;
}

.settings-card-header p {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0;
}

.settings-card-body {
    padding: 1.5rem;
}

/* Profile Picture Section */
.profile-picture-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-image-wrapper {
    position: relative;
}

.profile-image-wrapper .avatar {
    width: 100px;
    height: 100px;
    border-radius: 0.75rem;
    object-fit: cover;
    border: 3px solid #e5e7eb;
    transition: border-color 0.2s ease;
}

.profile-image-wrapper:hover .avatar {
    border-color: #4f46e5;
}

.profile-image-info h4 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.375rem 0;
}

.profile-image-info p {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0 0 1rem 0;
}

.upload-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    color: #4f46e5;
    border: 1px solid #e0e7ff;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-upload:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
}

.btn-upload-submit {
    display: none;
    padding: 0.5rem 1rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-upload-submit:hover {
    background: #4338ca;
}

.file-name-display {
    font-size: 0.8125rem;
    color: #6b7280;
}

/* Form Styling */
.settings-form .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.settings-form .form-row:last-child {
    margin-bottom: 0;
}

.settings-form .form-group {
    margin-bottom: 1.25rem;
}

.settings-form .form-group.full-width {
    grid-column: span 2;
}

.settings-form label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.settings-form label .required {
    color: #ef4444;
    margin-left: 0.125rem;
}

.settings-form .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    background: white;
}

.settings-form .form-control:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.settings-form .form-hint {
    font-size: 0.8125rem;
    color: #6b7280;
    margin-top: 0.375rem;
}

.settings-form .error-message {
    font-size: 0.8125rem;
    color: #ef4444;
    margin-top: 0.375rem;
    display: block;
}

/* Checkbox Settings */
.settings-checkbox {
    padding: 1rem 1.25rem;
    border: 1px solid #f3f4f6;
    border-radius: 0.625rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}

.settings-checkbox:last-child {
    margin-bottom: 0;
}

.settings-checkbox:hover {
    background: #f9fafb;
    border-color: #e5e7eb;
}

.settings-checkbox label {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    cursor: pointer;
    margin: 0;
}

.settings-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 2px;
    accent-color: #4f46e5;
}

.settings-checkbox .checkbox-content {
    flex: 1;
}

.settings-checkbox .checkbox-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.settings-checkbox .checkbox-desc {
    font-size: 0.8125rem;
    color: #6b7280;
    line-height: 1.5;
}

/* Buttons */
.btn-primary-settings {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary-settings:hover {
    background: #4338ca;
    transform: translateY(-1px);
}

.btn-primary-settings:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary-settings {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-secondary-settings:hover {
    background: #f3f4f6;
    color: #374151;
}

.form-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

/* Danger Zone */
.settings-card.danger {
    border-color: #fecaca;
}

.settings-card.danger .settings-card-header {
    background: #fef2f2;
    border-bottom-color: #fecaca;
}

.settings-card.danger .settings-card-header h3 {
    color: #b91c1c;
}

.settings-card.danger .settings-card-header h3 i {
    color: #ef4444;
}

.btn-danger-settings {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: #ef4444;
    border: 1px solid #fecaca;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-danger-settings:hover {
    background: #fef2f2;
    border-color: #ef4444;
}

.btn-danger-settings:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Invalid Input Styling */
.form-control.is-invalid {
    border-color: #ef4444 !important;
    background-image: none;
}

.form-control.is-invalid:focus {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}

.error-message {
    display: none;
    font-size: 0.8125rem;
    color: #ef4444;
    margin-top: 0.375rem;
}

/* Button Loading Animation */
.btn-primary-settings:disabled {
    cursor: not-allowed;
    transform: none;
}

.btn-primary-settings .fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Success Button State */
.btn-primary-settings.success-state {
    background: #10b981 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-picture-section {
        flex-direction: column;
        text-align: center;
    }

    .profile-image-info {
        text-align: center;
    }

    .upload-actions {
        justify-content: center;
    }

    .settings-form .form-row {
        grid-template-columns: 1fr;
    }

    .settings-form .form-group.full-width {
        grid-column: span 1;
    }

    .password-form .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary-settings,
    .btn-secondary-settings,
    .btn-danger-settings {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="settings-pro">
    <!-- Page Header -->
    <div class="page-header-settings">
        <h1><i class="fas fa-cog"></i> Account Settings</h1>
        <p>Manage your account preferences and security settings</p>
    </div>

    <!-- Profile Picture Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3><i class="fas fa-camera"></i> Profile Picture</h3>
            <p>Upload a profile picture to personalize your account</p>
        </div>
        <div class="settings-card-body">
            <div class="profile-picture-section">
                <div class="profile-image-wrapper">
                    <?php if(Auth::user()->image != ''): ?>
                        <img src="<?php echo e(asset('profile_img/thumb/'.Auth::user()->image)); ?>" alt="Profile" class="avatar" id="previewImage">
                    <?php else: ?>
                        <img src="<?php echo e(asset('assets/images/avatar7.png')); ?>" alt="Profile" class="avatar" id="previewImage">
                    <?php endif; ?>
                </div>
                <div class="profile-image-info">
                    <h4>Upload a new photo</h4>
                    <p>JPG, PNG or GIF. Max size 5MB</p>
                    <form id="profileImageForm" action="<?php echo e(route('account.updateProfileimg')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="upload-actions">
                            <label class="btn-upload">
                                <i class="fas fa-upload"></i> Choose File
                                <input type="file" id="avatarInput" name="image" style="display: none;" accept="image/*">
                            </label>
                            <span class="file-name-display" id="fileNameDisplay">No file chosen</span>
                            <button type="submit" class="btn-upload-submit" id="uploadImageBtn">
                                <i class="fas fa-check"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Basic Information Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3><i class="fas fa-user"></i> Basic Information</h3>
            <p>Update your account email and name</p>
        </div>
        <div class="settings-card-body">
            <form id="basicInfoForm" class="settings-form" method="POST" action="<?php echo e(route('account.updateProfile')); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <input type="hidden" name="mobile" value="<?php echo e(Auth::user()->mobile); ?>">
                <input type="hidden" name="designation" value="<?php echo e(Auth::user()->designation); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo e(old('name', Auth::user()->name)); ?>" required>
                        <span class="error-message" id="name_error"></span>
                    </div>
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?php echo e(old('email', Auth::user()->email)); ?>" required>
                        <p class="form-hint">This is your login email</p>
                        <span class="error-message" id="email_error"></span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-settings" id="saveBasicInfoBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="<?php echo e(route('account.myProfile')); ?>" class="btn-secondary-settings">
                        <i class="fas fa-user-edit"></i> Edit Full Profile
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Preferences Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3><i class="fas fa-bell"></i> Notification Preferences</h3>
            <p>Choose what notifications you want to receive</p>
        </div>
        <div class="settings-card-body">
            <form id="notificationForm" method="POST">
                <?php echo csrf_field(); ?>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_new_jobs"
                               <?php echo e((Auth::user()->notification_preferences['email_new_jobs'] ?? true) ? 'checked' : ''); ?>>
                        <div class="checkbox-content">
                            <div class="checkbox-title">New Job Matches</div>
                            <div class="checkbox-desc">Receive emails when new jobs match your preferences</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_application_updates"
                               <?php echo e((Auth::user()->notification_preferences['email_application_updates'] ?? true) ? 'checked' : ''); ?>>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Application Updates</div>
                            <div class="checkbox-desc">Get notified about your application status changes</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_messages"
                               <?php echo e((Auth::user()->notification_preferences['email_messages'] ?? true) ? 'checked' : ''); ?>>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Messages</div>
                            <div class="checkbox-desc">Receive emails when employers message you</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_marketing"
                               <?php echo e((Auth::user()->notification_preferences['email_marketing'] ?? false) ? 'checked' : ''); ?>>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Marketing Emails</div>
                            <div class="checkbox-desc">Receive tips, news, and promotional content</div>
                        </div>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-settings">
                        <i class="fas fa-bell"></i> Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Privacy Settings Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3><i class="fas fa-shield-alt"></i> Privacy Settings</h3>
            <p>Control who can see your profile</p>
        </div>
        <div class="settings-card-body">
            <form id="privacyForm" method="POST">
                <?php echo csrf_field(); ?>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="profile_visible"
                               <?php echo e(Auth::user()->profile_visible ?? true ? 'checked' : ''); ?>>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Make my profile visible to employers</div>
                            <div class="checkbox-desc">Allow employers to find and view your profile</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="show_resume"
                               <?php echo e(Auth::user()->show_resume ?? true ? 'checked' : ''); ?>>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Allow employers to download my resume</div>
                            <div class="checkbox-desc">Employers can download your resume when viewing your profile</div>
                        </div>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-settings">
                        <i class="fas fa-shield-alt"></i> Save Privacy Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="settings-card danger">
        <div class="settings-card-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
            <p>Temporarily deactivate your account</p>
        </div>
        <div class="settings-card-body">
            <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1rem;">
                Your profile will be hidden from employers. You can reactivate your account at any time by logging in.
            </p>

            <form id="deactivateAccountForm" method="POST" action="<?php echo e(route('account.deactivate')); ?>">
                <?php echo csrf_field(); ?>

                <div class="settings-checkbox" style="border-color: #fecaca;">
                    <label>
                        <input type="checkbox" id="deactivateConfirm">
                        <div class="checkbox-content">
                            <div class="checkbox-title" style="color: #b91c1c;">I understand that my account will be deactivated</div>
                            <div class="checkbox-desc">My profile will be hidden from employers until I log in again</div>
                        </div>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-danger-settings" id="deactivateAccountBtn" disabled>
                        <i class="fas fa-power-off"></i> Deactivate My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear error messages when user starts typing
    document.querySelectorAll('input, select, textarea').forEach(function(el) {
        el.addEventListener('input', function() {
            const errorEl = this.closest('.form-group')?.querySelector('.error-message');
            if (errorEl) errorEl.textContent = '';
        });
    });

    // Handle basic info form submission
    const basicInfoForm = document.getElementById('basicInfoForm');
    if (basicInfoForm) {
        basicInfoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            const saveBtn = document.getElementById('saveBasicInfoBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.status) {
                    if (typeof showToast === 'function') {
                        showToast('Account information updated successfully', 'success');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    if(data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const errorEl = document.getElementById(key + '_error');
                            if(errorEl) errorEl.textContent = data.errors[key][0];
                        });
                    }
                    if (typeof showToast === 'function') {
                        showToast('Please fix the errors', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Error updating information', 'error');
                }
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
        });
    }

    // Handle profile image upload
    const profileImageForm = document.getElementById('profileImageForm');
    const avatarInput = document.getElementById('avatarInput');

    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            const fileName = this.value.split('\\').pop();
            document.getElementById('fileNameDisplay').textContent = fileName || 'No file chosen';
            document.getElementById('uploadImageBtn').style.display = 'inline-flex';

            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (profileImageForm) {
        profileImageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const uploadBtn = document.getElementById('uploadImageBtn');
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.status) {
                    if (typeof showToast === 'function') {
                        showToast('Profile picture updated successfully!', 'success');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    let errorMsg = 'Error updating profile picture';
                    if(data.errors && data.errors.image) {
                        errorMsg = Array.isArray(data.errors.image) ? data.errors.image[0] : data.errors.image;
                    }
                    if (typeof showToast === 'function') {
                        showToast(errorMsg, 'error');
                    }
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-check"></i> Upload';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Error uploading image. Please try again.', 'error');
                }
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-check"></i> Upload';
            });
        });
    }

    // Handle notification preferences
    const notificationForm = document.getElementById('notificationForm');
    if (notificationForm) {
        notificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof showToast === 'function') {
                showToast('Notification preferences saved!', 'success');
            }
        });
    }

    // Handle privacy settings
    const privacyForm = document.getElementById('privacyForm');
    if (privacyForm) {
        privacyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof showToast === 'function') {
                showToast('Privacy settings saved!', 'success');
            }
        });
    }

    // Handle deactivate account checkbox
    const deactivateConfirm = document.getElementById('deactivateConfirm');
    const deactivateBtn = document.getElementById('deactivateAccountBtn');

    if (deactivateConfirm && deactivateBtn) {
        deactivateConfirm.addEventListener('change', function() {
            deactivateBtn.disabled = !this.checked;
        });

        deactivateBtn.addEventListener('click', function() {
            if(confirm('Are you sure you want to deactivate your account? Your profile will be hidden from employers until you log in again.')) {
                document.getElementById('deactivateAccountForm').submit();
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.jobseeker', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/settings.blade.php ENDPATH**/ ?>