@extends('layouts.jobseeker')

@section('page-title', 'Change Password')

@section('jobseeker-content')
<style>
/* Change Password - Using Modern Design System */
.change-password-pro {
    padding: 0 1rem;
    max-width: 700px;
}

/* Password Card - Using modern-card design system */
.password-card {
    background: white;
    border-radius: var(--radius-lg, 16px);
    border: 1px solid var(--modern-gray-100, #f1f5f9);
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.25s ease;
}

.password-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.password-card-body {
    padding: 2rem;
}

/* Form Styling - Using modern-form design system */
.password-form .form-group {
    margin-bottom: 1.5rem;
}

.password-form .form-group:last-of-type {
    margin-bottom: 1.75rem;
}

.password-form label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--modern-gray-700, #334155);
    margin-bottom: 0.5rem;
}

.password-form label .required {
    color: var(--modern-danger, #ef4444);
    margin-left: 0.125rem;
}

.password-form .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border: 1px solid var(--modern-gray-300, #cbd5e1);
    border-radius: var(--radius-md, 12px);
    transition: all 0.25s ease;
    background: white;
    color: var(--modern-gray-800, #1e293b);
}

.password-form .form-control:focus {
    outline: none;
    border-color: var(--modern-primary, #6366f1);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.password-form .form-control.is-invalid {
    border-color: var(--modern-danger, #ef4444);
}

.password-form .form-control.is-invalid:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.password-form .form-hint {
    font-size: 0.8125rem;
    color: var(--modern-gray-500, #64748b);
    margin-top: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.password-form .form-hint i {
    color: var(--modern-gray-400, #94a3b8);
    font-size: 0.75rem;
}

.password-form .invalid-feedback {
    font-size: 0.8125rem;
    color: var(--modern-danger, #ef4444);
    margin-top: 0.375rem;
    display: none;
}

.password-form .form-control.is-invalid + .invalid-feedback {
    display: block;
}

/* Password Input with Toggle - Using design system */
.password-input-wrapper {
    position: relative;
}

.password-input-wrapper .form-control {
    padding-right: 2.75rem;
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--modern-gray-400, #94a3b8);
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.25s ease;
}

.password-toggle:hover {
    color: var(--modern-primary, #6366f1);
}

/* Action Buttons - Using btn-modern design system */
.password-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.5rem;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

.btn-save:disabled {
    background: var(--modern-gray-300, #cbd5e1);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Spinner Animation */
.fa-spin {
    animation: fa-spin 1s linear infinite;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.btn-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: var(--modern-gray-700, #334155);
    border: 1px solid var(--modern-gray-300, #cbd5e1);
    border-radius: var(--radius-md, 12px);
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s ease;
}

.btn-back:hover {
    background: var(--modern-gray-50, #f8fafc);
    color: var(--modern-gray-800, #1e293b);
    border-color: var(--modern-gray-400, #94a3b8);
}

/* Alert Styles - Using modern-alert design system */
.alert-pro {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: var(--radius-md, 12px);
    margin-bottom: 1.5rem;
    border: none;
}

.alert-pro.success {
    background: var(--modern-success-light, #d1fae5);
    color: #047857;
}

.alert-pro.danger {
    background: var(--modern-danger-light, #fee2e2);
    color: #b91c1c;
}

.alert-pro i {
    font-size: 1.125rem;
}

.alert-pro .btn-close {
    margin-left: auto;
    opacity: 0.7;
    background: none;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    padding: 0;
    color: inherit;
    transition: opacity 0.25s ease;
}

.alert-pro .btn-close:hover {
    opacity: 1;
}

/* Security Tips - Using security-tips-box design system */
.security-tips {
    background: var(--modern-gray-50, #f8fafc);
    border: 1px solid var(--modern-gray-200, #e2e8f0);
    border-radius: var(--radius-md, 12px);
    padding: 1.25rem;
    margin-top: 1.5rem;
}

.security-tips h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--modern-gray-700, #334155);
    margin: 0 0 0.75rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.security-tips h4 i {
    color: var(--modern-primary, #6366f1);
}

.security-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.security-tips li {
    font-size: 0.8125rem;
    color: var(--modern-gray-600, #475569);
    padding: 0.375rem 0;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.security-tips li i {
    color: var(--modern-success, #10b981);
    margin-top: 0.125rem;
    font-size: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
    .password-actions {
        flex-direction: column-reverse;
    }

    .btn-save,
    .btn-back {
        width: 100%;
    }
}
</style>

<div class="change-password-pro">
    <!-- Page Header - Using modern design system -->
    <div class="simple-page-header">
        <h1><i class="fas fa-key"></i> Change Password</h1>
        <p>Update your password to keep your account secure</p>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Password Card -->
    <div class="password-card">
        <div class="password-card-body">
            <form id="changePasswordForm" class="password-form" method="POST">
                @csrf

                <div class="form-group">
                    <label for="old_password">Current Password <span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="old_password" name="old_password" required placeholder="Enter your current password">
                        <button type="button" class="password-toggle" onclick="togglePassword('old_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="old_password_error"></div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password <span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Enter your new password">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="form-hint"><i class="fas fa-info-circle"></i> Password must be at least 6 characters long</p>
                    <div class="invalid-feedback" id="new_password_error"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm your new password">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="confirm_password_error"></div>
                </div>

                <div class="password-actions">
                    <a href="{{ route('account.settings') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Settings
                    </a>
                    <button type="submit" class="btn-save" id="submitBtn">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                </div>
            </form>

            <!-- Security Tips -->
            <div class="security-tips">
                <h4><i class="fas fa-shield-alt"></i> Password Security Tips</h4>
                <ul>
                    <li><i class="fas fa-check"></i> Use a mix of uppercase and lowercase letters</li>
                    <li><i class="fas fa-check"></i> Include numbers and special characters</li>
                    <li><i class="fas fa-check"></i> Avoid using personal information</li>
                    <li><i class="fas fa-check"></i> Don't reuse passwords from other accounts</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.parentElement.querySelector('.password-toggle i');

    if (field.type === 'password') {
        field.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        clearErrors();

        // Validate passwords match
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            showError('confirm_password', 'Passwords do not match');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        // Prepare form data
        const formData = new FormData(form);

        fetch('{{ route("account.changePassword") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Success
                showAlert('success', data.message || 'Password updated successfully!');
                form.reset();
            } else {
                // Validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showError(field, data.errors[field][0]);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while updating your password. Please try again.');
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });

    function clearErrors() {
        const errorElements = document.querySelectorAll('.invalid-feedback');
        const inputElements = document.querySelectorAll('.form-control');

        errorElements.forEach(element => {
            element.textContent = '';
            element.style.display = 'none';
        });

        inputElements.forEach(element => {
            element.classList.remove('is-invalid');
        });

        // Remove any existing alerts
        const alertContainer = document.getElementById('alertContainer');
        alertContainer.innerHTML = '';
    }

    function showError(field, message) {
        const input = document.getElementById(field);
        const errorElement = document.getElementById(field + '_error');

        if (input && errorElement) {
            input.classList.add('is-invalid');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

        alertContainer.innerHTML = `
            <div class="alert-pro ${type}" role="alert">
                <i class="${icon}"></i>
                <span>${message}</span>
                <button type="button" class="btn-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert-pro');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    }
});
</script>
@endsection
