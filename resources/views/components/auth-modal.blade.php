<!-- Modern Auth Modal Component -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Side - Welcome Section -->
                    <div class="col-md-5 auth-welcome-section d-flex align-items-center justify-content-center position-relative">
                        <div class="auth-pattern"></div>
                        <div class="text-center text-white p-5 position-relative z-index-2">
                            <div class="auth-logo mb-4">
                                <i class="fas fa-clipboard fa-3x"></i>
                            </div>
                            <h3 class="fw-bold mb-3 auth-title" id="welcomeTitle">Welcome Back<br>to TugmaJobs</h3>
                            <p class="mb-4 text-white auth-subtitle" id="welcomeSubtitle">Sign in to continue to your account.</p>
                            <div class="auth-features mt-5">
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span>Find your dream job</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span>Connect with employers</span>
                                </div>
                            </div>
                            <div class="mt-5 pt-4">
                                <button type="button" class="btn btn-outline-light w-100 employer-btn" onclick="openEmployerModal(event)">
                                    I'm an employer
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Form Section -->
                    <div class="col-md-7 p-5 position-relative bg-white">
                        <!-- Close Button -->
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

                        <!-- Login Form -->
                        <div id="loginForm" class="auth-form">
                            <h2 class="fw-bold mb-2 text-dark" style="font-size: 1.75rem;">Sign in</h2>
                            <p class="text-muted mb-4 small">Welcome back! Please enter your details.</p>

                            <!-- Google Login Button -->
                            <div class="mb-4">
                                <a href="{{ route('social.redirect', ['provider' => 'google', 'role' => 'jobseeker']) }}" class="btn btn-outline-secondary w-100 social-btn google-btn d-flex align-items-center justify-content-center">
                                    <i class="fab fa-google me-2"></i>
                                    Continue with Google
                                </a>
                            </div>

                            <!-- Divider -->
                            <div class="divider-section mb-4">
                                <hr class="divider-line">
                                <span class="divider-text">or</span>
                                <hr class="divider-line">
                            </div>

                            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                                @csrf
                                <input type="hidden" name="login_method" id="loginMethod" value="magic_link">

                                <!-- Email Input -->
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                        <span>Email address</span>
                                        <a href="#" class="text-decoration-none text-muted small">
                                            <i class="fas fa-question-circle me-1"></i>
                                        </a>
                                    </label>
                                    <input type="email"
                                           class="form-control form-control-lg"
                                           id="loginEmail"
                                           name="email"
                                           placeholder="you@example.com"
                                           value="{{ old('email') }}"
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide a valid email.
                                    </div>
                                </div>

                                <!-- Password Input (hidden by default) -->
                                <div class="mb-3" id="loginPasswordField" style="display: none;">
                                    <label for="loginPassword" class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                        <span>Password</span>
                                        <a href="{{ route('account.forgotPassword') }}" class="text-decoration-none text-primary small">
                                            Forgot password?
                                        </a>
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control form-control-lg"
                                               id="loginPassword"
                                               name="password"
                                               placeholder="Enter your password">
                                        <button type="button" class="btn btn-outline-secondary password-toggle-btn" onclick="togglePasswordVisibility('loginPassword', 'loginPasswordIcon')">
                                            <i class="fas fa-eye-slash" id="loginPasswordIcon"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Please provide your password.
                                    </div>
                                </div>

                                <!-- Remember Me (shown only for password login) -->
                                <div class="mb-3 form-check" id="loginRememberMe" style="display: none;">
                                    <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-email-code btn-lg fw-bold" id="loginSubmitBtn">
                                        Email me a sign in code
                                    </button>
                                </div>

                                <!-- Toggle Login Method -->
                                <div class="text-center mb-3">
                                    <a href="#" class="text-muted text-decoration-none small" id="toggleLoginMethod" onclick="toggleLoginPassword(event)">
                                        <i class="fas fa-key me-1"></i>
                                        Sign in with password instead
                                    </a>
                                </div>

                                <!-- Switch to Register -->
                                <div class="text-center">
                                    <span class="text-dark">Don't have an account? </span>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold" onclick="switchToRegister()">Register</a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Register Form -->
                        <div id="registerForm" class="auth-form" style="display: none;">
                            <h2 class="fw-bold mb-2 text-dark" style="font-size: 1.75rem;">Create account</h2>
                            <p class="text-muted mb-4 small">Get started with your free account.</p>

                            <!-- Google Login Button -->
                            <div class="mb-4">
                                <a href="{{ route('social.redirect', ['provider' => 'google', 'role' => 'jobseeker']) }}" class="btn btn-outline-secondary w-100 social-btn google-btn d-flex align-items-center justify-content-center">
                                    <i class="fab fa-google me-2"></i>
                                    Continue with Google
                                </a>
                            </div>

                            <!-- Divider -->
                            <div class="divider-section mb-4">
                                <hr class="divider-line">
                                <span class="divider-text">or</span>
                                <hr class="divider-line">
                            </div>

                            <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                                @csrf
                                <input type="hidden" name="registration_method" id="registrationMethod" value="magic_link">

                                <!-- Name Input (hidden by default for magic link) -->
                                <div class="mb-3" id="registerNameField" style="display: none;">
                                    <label for="registerName" class="form-label fw-semibold text-dark">
                                        <span>Full Name</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           id="registerName"
                                           name="name"
                                           placeholder="Enter your full name"
                                           value="{{ old('name') }}">
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Please provide your name.</div>
                                    @enderror
                                </div>

                                <!-- Email Input -->
                                <div class="mb-3">
                                    <label for="registerEmail" class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                        <span>Email address</span>
                                        <a href="#" class="text-decoration-none text-muted small">
                                            <i class="fas fa-question-circle me-1"></i>
                                        </a>
                                    </label>
                                    <input type="email"
                                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           id="registerEmail"
                                           name="email"
                                           placeholder="you@example.com"
                                           value="{{ old('email') }}"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Please provide a valid email.</div>
                                    @enderror
                                </div>

                                <!-- Password Input (hidden by default) -->
                                <div class="mb-3" id="registerPasswordField" style="display: none;">
                                    <label for="registerPassword" class="form-label fw-semibold text-dark">
                                        <span>Password</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                                               id="registerPassword"
                                               name="password"
                                               placeholder="Create a strong password">
                                        <button type="button" class="btn btn-outline-secondary password-toggle-btn" onclick="togglePasswordVisibility('registerPassword', 'registerPasswordIcon')">
                                            <i class="fas fa-eye-slash" id="registerPasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Password must be at least 8 characters.</div>
                                    @enderror
                                    <div class="form-text small text-muted">
                                        Must be at least 8 characters long
                                    </div>
                                </div>

                                <!-- Confirm Password Input (hidden by default) -->
                                <div class="mb-3" id="registerPasswordConfirmField" style="display: none;">
                                    <label for="registerPasswordConfirm" class="form-label fw-semibold text-dark">
                                        <span>Confirm Password</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control form-control-lg"
                                               id="registerPasswordConfirm"
                                               name="password_confirmation"
                                               placeholder="Re-enter your password">
                                        <button type="button" class="btn btn-outline-secondary password-toggle-btn" onclick="togglePasswordVisibility('registerPasswordConfirm', 'registerPasswordConfirmIcon')">
                                            <i class="fas fa-eye-slash" id="registerPasswordConfirmIcon"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Passwords must match.
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-email-code btn-lg fw-bold" id="registerSubmitBtn">
                                        Email me a sign in code
                                    </button>
                                </div>

                                <!-- Toggle Registration Method -->
                                <div class="text-center mb-3">
                                    <a href="#" class="text-muted text-decoration-none small" id="toggleRegisterMethod" onclick="toggleRegisterPassword(event)">
                                        <i class="fas fa-key me-1"></i>
                                        Sign up with password instead
                                    </a>
                                </div>

                                <!-- Switch to Login -->
                                <div class="text-center">
                                    <span class="text-dark">Already have an account? </span>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold" onclick="switchToLogin()">Sign In</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-welcome-section {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    min-height: 550px;
    position: relative;
    overflow: hidden;
}

.auth-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 2px, transparent 2px),
        radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 2px, transparent 2px);
    background-size: 50px 50px;
    background-position: 0 0, 25px 25px;
}

.auth-logo {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    backdrop-filter: blur(10px);
}

.auth-title {
    font-size: 1.75rem;
    line-height: 1.2;
}

.welcome-accent {
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, #10b981, #059669);
    margin: 0 auto;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.auth-features {
    font-size: 0.9rem;
}

.feature-item {
    display: flex;
    align-items: center;
    color: white !important;
}

.feature-item span {
    color: white !important;
}

.feature-item i {
    color: #10b981;
    font-size: 0.8rem;
}

.auth-welcome-section * {
    color: white !important;
}

.auth-welcome-section h3 {
    color: white !important;
}

.auth-welcome-section p {
    color: white !important;
}

.social-btn {
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.social-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.divider-section {
    position: relative;
    text-align: center;
}

.divider-line {
    border: none;
    height: 1px;
    background: #e9ecef;
    margin: 0;
    flex: 1;
}

.divider-text {
    background: white;
    padding: 0 15px;
    color: #6c757d;
    font-size: 14px;
}

.divider-section {
    display: flex;
    align-items: center;
    gap: 0;
}

.form-control-lg {
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 15px;
    transition: all 0.2s ease;
    background: #fff;
}

.form-control-lg:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.form-control-lg::placeholder {
    color: #9ca3af;
}

.form-select-lg {
    padding: 15px 20px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    font-size: 16px;
}

.btn-success {
    background: #00ff88;
    border: none;
    border-radius: 8px;
    padding: 15px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: #00e67a;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 255, 136, 0.3);
}

.google-btn {
    background: #fff;
    border: 1px solid #d1d5db;
    color: #374151;
    font-weight: 500;
    padding: 11px 16px;
    font-size: 15px;
}

.google-btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #111827;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.google-btn .fab.fa-google {
    color: #4285f4;
    font-size: 18px;
}

.btn-email-code {
    background: #2563eb;
    border: none;
    color: white;
    border-radius: 8px;
    padding: 15px;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-email-code:hover {
    background: #1d4ed8;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

.employer-btn {
    border: 2px solid rgba(255, 255, 255, 0.8);
    color: white;
    font-weight: 500;
    padding: 12px 24px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.employer-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: white;
    color: white;
    transform: translateY(-1px);
}



.modal-content {
    border-radius: 16px;
    overflow: hidden;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

@media (max-width: 768px) {
    .auth-welcome-section {
        min-height: 250px;
    }

    .modal-dialog {
        margin: 10px;
    }

    .auth-title {
        font-size: 1.5rem;
    }

    .auth-features {
        font-size: 0.85rem;
    }

    .employer-btn {
        padding: 10px 20px;
        font-size: 14px;
    }

    .col-md-7.p-5 {
        padding: 2rem !important;
    }
}

/* Smooth fade-in animation */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}

/* Label styling */
.form-label {
    font-size: 14px;
    color: #374151;
    margin-bottom: 6px;
}

/* Link styling */
a.text-primary {
    color: #2563eb !important;
}

a.text-primary:hover {
    color: #1d4ed8 !important;
    text-decoration: underline !important;
}

/* Close button enhancement */
.btn-close {
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.btn-close:hover {
    opacity: 1;
}

/* Password toggle button styling */
.password-toggle-btn {
    border-left: 0;
    border-color: #d1d5db;
    background: #f9fafb;
    padding: 0 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.password-toggle-btn:focus {
    box-shadow: none;
    border-color: #2563eb;
}

.password-toggle-btn i {
    color: #6b7280;
    font-size: 14px;
}

.input-group .form-control-lg {
    border-right: 0;
}

.input-group .form-control-lg:focus {
    border-right: 0;
}

.input-group .form-control-lg:focus + .password-toggle-btn {
    border-color: #2563eb;
}
</style>

<script>
// Password visibility toggle function
function togglePasswordVisibility(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}

function switchToRegister() {
    event.preventDefault();
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
    document.getElementById('welcomeTitle').innerHTML = 'Join<br>TugmaJobs';
    document.getElementById('welcomeSubtitle').textContent = 'Create your account to get started.';
    document.getElementById('welcomeSubtitle').className = 'mb-4 text-white auth-subtitle';
}

function switchToLogin() {
    event.preventDefault();
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('welcomeTitle').innerHTML = 'Welcome Back<br>to TugmaJobs';
    document.getElementById('welcomeSubtitle').textContent = 'Sign in to continue to your account.';
    document.getElementById('welcomeSubtitle').className = 'mb-4 text-white auth-subtitle';
}

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Function to toggle login password field
function toggleLoginPassword(event) {
    event.preventDefault();
    const passwordField = document.getElementById('loginPasswordField');
    const rememberMeField = document.getElementById('loginRememberMe');
    const loginMethodInput = document.getElementById('loginMethod');
    const toggleLink = document.getElementById('toggleLoginMethod');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const passwordInput = document.getElementById('loginPassword');

    if (passwordField.style.display === 'none') {
        // Switch to password mode
        passwordField.style.display = 'block';
        rememberMeField.style.display = 'block';
        loginMethodInput.value = 'password';
        toggleLink.innerHTML = '<i class="fas fa-envelope me-1"></i>Sign in with email code instead';
        submitBtn.textContent = 'Sign In';
        passwordInput.setAttribute('required', 'required');
    } else {
        // Switch to magic link mode
        passwordField.style.display = 'none';
        rememberMeField.style.display = 'none';
        loginMethodInput.value = 'magic_link';
        toggleLink.innerHTML = '<i class="fas fa-key me-1"></i>Sign in with password instead';
        submitBtn.textContent = 'Email me a sign in code';
        passwordInput.removeAttribute('required');
    }
}

// Function to toggle register password fields
function toggleRegisterPassword(event) {
    event.preventDefault();
    const nameField = document.getElementById('registerNameField');
    const passwordField = document.getElementById('registerPasswordField');
    const passwordConfirmField = document.getElementById('registerPasswordConfirmField');
    const registrationMethodInput = document.getElementById('registrationMethod');
    const toggleLink = document.getElementById('toggleRegisterMethod');
    const submitBtn = document.getElementById('registerSubmitBtn');
    const nameInput = document.getElementById('registerName');
    const passwordInput = document.getElementById('registerPassword');
    const passwordConfirmInput = document.getElementById('registerPasswordConfirm');

    if (passwordField.style.display === 'none') {
        // Switch to password mode
        nameField.style.display = 'block';
        passwordField.style.display = 'block';
        passwordConfirmField.style.display = 'block';
        registrationMethodInput.value = 'password';
        toggleLink.innerHTML = '<i class="fas fa-envelope me-1"></i>Sign up with email code instead';
        submitBtn.textContent = 'Create Account';
        nameInput.setAttribute('required', 'required');
        passwordInput.setAttribute('required', 'required');
        passwordInput.setAttribute('minlength', '8');
        passwordConfirmInput.setAttribute('required', 'required');
    } else {
        // Switch to magic link mode
        nameField.style.display = 'none';
        passwordField.style.display = 'none';
        passwordConfirmField.style.display = 'none';
        registrationMethodInput.value = 'magic_link';
        toggleLink.innerHTML = '<i class="fas fa-key me-1"></i>Sign up with password instead';
        submitBtn.textContent = 'Email me a sign in code';
        nameInput.removeAttribute('required');
        passwordInput.removeAttribute('required');
        passwordInput.removeAttribute('minlength');
        passwordConfirmInput.removeAttribute('required');
    }
}

// Function to switch to employer modal
function openEmployerModal(event) {
    event.preventDefault();
    // Close jobseeker modal
    var authModal = bootstrap.Modal.getInstance(document.getElementById('authModal'));
    if (authModal) {
        authModal.hide();
    }
    // Open employer modal
    setTimeout(function() {
        var employerModal = new bootstrap.Modal(document.getElementById('employerAuthModal'));
        employerModal.show();
    }, 300);
}

// Show modal on page load if there are validation errors (only for jobseeker routes, not employer)
@if($errors->any() && !request()->is('employer/*'))
document.addEventListener('DOMContentLoaded', function() {
    var authModal = new bootstrap.Modal(document.getElementById('authModal'));
    authModal.show();

    @if($errors->has('name') || $errors->has('password') || $errors->has('password_confirmation') || $errors->has('role'))
        switchToRegister();
        // If password fields had errors, show password registration mode
        @if($errors->has('password') || $errors->has('password_confirmation') || $errors->has('name'))
            toggleRegisterPasswordOnLoad();
        @endif
    @endif
});

// Function to toggle password fields on page load (without event)
function toggleRegisterPasswordOnLoad() {
    const nameField = document.getElementById('registerNameField');
    const passwordField = document.getElementById('registerPasswordField');
    const passwordConfirmField = document.getElementById('registerPasswordConfirmField');
    const registrationMethodInput = document.getElementById('registrationMethod');
    const toggleLink = document.getElementById('toggleRegisterMethod');
    const submitBtn = document.getElementById('registerSubmitBtn');
    const nameInput = document.getElementById('registerName');
    const passwordInput = document.getElementById('registerPassword');
    const passwordConfirmInput = document.getElementById('registerPasswordConfirm');

    nameField.style.display = 'block';
    passwordField.style.display = 'block';
    passwordConfirmField.style.display = 'block';
    registrationMethodInput.value = 'password';
    toggleLink.innerHTML = '<i class="fas fa-envelope me-1"></i>Sign up with email code instead';
    submitBtn.textContent = 'Create Account';
    nameInput.setAttribute('required', 'required');
    passwordInput.setAttribute('required', 'required');
    passwordInput.setAttribute('minlength', '8');
    passwordConfirmInput.setAttribute('required', 'required');
}
@endif
</script>