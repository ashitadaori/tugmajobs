@extends('layouts.jobseeker')

@section('page-title', 'Account Settings')

@section('jobseeker-content')
<style>
/* Settings Professional Styles - Using Modern Design System */
.settings-pro {
    padding: 0 1rem;
    max-width: 900px;
}

/* Settings Card - Uses modern-card from design system */
.settings-card {
    background: white;
    border-radius: var(--radius-lg, 16px);
    border: 1px solid var(--modern-gray-100, #f1f5f9);
    margin-bottom: 1.75rem;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.settings-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.settings-card-header {
    padding: 1.5rem 1.75rem;
    border-bottom: 1px solid var(--modern-gray-100, #f1f5f9);
    background: var(--modern-gray-50, #f8fafc);
}

.settings-card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--modern-gray-800, #1e293b);
    margin: 0 0 0.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.settings-card-header h3 i {
    color: var(--modern-primary, #6366f1);
    font-size: 0.9375rem;
}

.settings-card-header p {
    font-size: 0.8125rem;
    color: var(--modern-gray-500, #64748b);
    margin: 0;
}

.settings-card-body {
    padding: 1.75rem;
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
    border-radius: var(--radius-lg, 16px);
    object-fit: cover;
    border: 3px solid var(--modern-gray-200, #e2e8f0);
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-image-wrapper:hover .avatar {
    border-color: var(--modern-primary, #6366f1);
    transform: scale(1.02);
}

.profile-image-info h4 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--modern-gray-800, #1e293b);
    margin: 0 0 0.375rem 0;
}

.profile-image-info p {
    font-size: 0.8125rem;
    color: var(--modern-gray-500, #64748b);
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
    color: var(--modern-primary, #6366f1);
    border: 1px solid var(--modern-primary-100, #e0e7ff);
    border-radius: var(--radius-md, 12px);
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-upload:hover {
    background: var(--modern-primary-50, #eef2ff);
    border-color: var(--modern-primary-light, #818cf8);
    transform: translateY(-1px);
}

.btn-upload-submit {
    display: none;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    border-radius: var(--radius-md, 12px);
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}

.btn-upload-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
}

.file-name-display {
    font-size: 0.8125rem;
    color: var(--modern-gray-500, #64748b);
}

/* Form Styling - Using modern-form design system */
.settings-form .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.settings-form .form-row:last-child {
    margin-bottom: 0;
}

.settings-form .form-group {
    margin-bottom: 1.5rem;
}

.settings-form .form-group.full-width {
    grid-column: span 2;
}

.settings-form label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--modern-gray-700, #334155);
    margin-bottom: 0.5rem;
}

.settings-form label .required {
    color: var(--modern-danger, #ef4444);
    margin-left: 0.125rem;
}

.settings-form .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border: 1px solid var(--modern-gray-300, #cbd5e1);
    border-radius: var(--radius-md, 12px);
    transition: all 0.25s ease;
    background: white;
    color: var(--modern-gray-800, #1e293b);
}

.settings-form .form-control:focus {
    outline: none;
    border-color: var(--modern-primary, #6366f1);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.settings-form .form-hint {
    font-size: 0.8125rem;
    color: var(--modern-gray-500, #64748b);
    margin-top: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.settings-form .error-message {
    font-size: 0.8125rem;
    color: var(--modern-danger, #ef4444);
    margin-top: 0.375rem;
    display: block;
}

/* Checkbox Settings - Using modern toggle design */
.settings-checkbox {
    padding: 1rem 1.25rem;
    border: 1px solid var(--modern-gray-100, #f1f5f9);
    border-radius: var(--radius-md, 12px);
    margin-bottom: 0.75rem;
    transition: all 0.25s ease;
    background: white;
}

.settings-checkbox:last-child {
    margin-bottom: 0;
}

.settings-checkbox:hover {
    background: var(--modern-gray-50, #f8fafc);
    border-color: var(--modern-gray-200, #e2e8f0);
    transform: translateX(4px);
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
    border: 2px solid var(--modern-gray-300, #cbd5e1);
    border-radius: 5px;
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 2px;
    accent-color: var(--modern-primary, #6366f1);
}

.settings-checkbox .checkbox-content {
    flex: 1;
}

.settings-checkbox .checkbox-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--modern-gray-800, #1e293b);
    margin-bottom: 0.25rem;
}

.settings-checkbox .checkbox-desc {
    font-size: 0.8125rem;
    color: var(--modern-gray-500, #64748b);
    line-height: 1.5;
}

/* Buttons - Using btn-modern design system */
.btn-primary-settings {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    border-radius: var(--radius-md, 12px);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25);
}

.btn-primary-settings:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

.btn-primary-settings:disabled {
    background: var(--modern-gray-300, #cbd5e1);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-secondary-settings {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: var(--modern-gray-700, #334155);
    border: 1px solid var(--modern-gray-300, #cbd5e1);
    border-radius: var(--radius-md, 12px);
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-secondary-settings:hover {
    background: var(--modern-gray-50, #f8fafc);
    border-color: var(--modern-gray-400, #94a3b8);
    color: var(--modern-gray-800, #1e293b);
}

.form-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

/* Danger Zone - Using danger-zone-card design system */
.settings-card.danger {
    border-color: var(--modern-danger-light, #fee2e2);
}

.settings-card.danger .settings-card-header {
    background: var(--modern-danger-light, #fee2e2);
    border-bottom-color: rgba(239, 68, 68, 0.2);
}

.settings-card.danger .settings-card-header h3 {
    color: #b91c1c;
}

.settings-card.danger .settings-card-header h3 i {
    color: var(--modern-danger, #ef4444);
}

.btn-danger-settings {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: var(--modern-danger, #ef4444);
    border: 1px solid var(--modern-danger, #ef4444);
    border-radius: var(--radius-md, 12px);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-danger-settings:hover {
    background: var(--modern-danger, #ef4444);
    color: white;
    transform: translateY(-1px);
}

.btn-danger-settings:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
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
    <!-- Page Header - Using modern design system -->
    <div class="simple-page-header">
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
                    @if (Auth::user()->image != '')
                        <img src="{{ asset('profile_img/thumb/'.Auth::user()->image) }}" alt="Profile" class="avatar" id="previewImage">
                    @else
                        <img src="{{ asset('assets/images/avatar7.png') }}" alt="Profile" class="avatar" id="previewImage">
                    @endif
                </div>
                <div class="profile-image-info">
                    <h4>Upload a new photo</h4>
                    <p>JPG, PNG or GIF. Max size 5MB</p>
                    <form id="profileImageForm" action="{{ route('account.updateProfileimg') }}" method="POST" enctype="multipart/form-data">
                        @csrf
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
            <form id="basicInfoForm" class="settings-form" method="POST" action="{{ route('account.updateProfile') }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="mobile" value="{{ Auth::user()->mobile }}">
                <input type="hidden" name="designation" value="{{ Auth::user()->designation }}">

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', Auth::user()->name) }}" required>
                        <span class="error-message" id="name_error"></span>
                    </div>
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', Auth::user()->email) }}" required>
                        <p class="form-hint">This is your login email</p>
                        <span class="error-message" id="email_error"></span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-settings" id="saveBasicInfoBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('account.myProfile') }}" class="btn-secondary-settings">
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
                @csrf

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_new_jobs"
                               {{ (Auth::user()->notification_preferences['email_new_jobs'] ?? true) ? 'checked' : '' }}>
                        <div class="checkbox-content">
                            <div class="checkbox-title">New Job Matches</div>
                            <div class="checkbox-desc">Receive emails when new jobs match your preferences</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_application_updates"
                               {{ (Auth::user()->notification_preferences['email_application_updates'] ?? true) ? 'checked' : '' }}>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Application Updates</div>
                            <div class="checkbox-desc">Get notified about your application status changes</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_messages"
                               {{ (Auth::user()->notification_preferences['email_messages'] ?? true) ? 'checked' : '' }}>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Messages</div>
                            <div class="checkbox-desc">Receive emails when employers message you</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="email_marketing"
                               {{ (Auth::user()->notification_preferences['email_marketing'] ?? false) ? 'checked' : '' }}>
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
                @csrf

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="profile_visible"
                               {{ Auth::user()->profile_visible ?? true ? 'checked' : '' }}>
                        <div class="checkbox-content">
                            <div class="checkbox-title">Make my profile visible to employers</div>
                            <div class="checkbox-desc">Allow employers to find and view your profile</div>
                        </div>
                    </label>
                </div>

                <div class="settings-checkbox">
                    <label>
                        <input type="checkbox" name="show_resume"
                               {{ Auth::user()->show_resume ?? true ? 'checked' : '' }}>
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

            <form id="deactivateAccountForm" method="POST" action="{{ route('account.deactivate') }}">
                @csrf

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

@push('scripts')
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
@endpush
@endsection
