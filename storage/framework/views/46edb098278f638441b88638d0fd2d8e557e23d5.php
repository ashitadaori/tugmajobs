<!-- Employer Auth Modal Component -->
<div class="modal fade" id="employerAuthModal" tabindex="-1" aria-labelledby="employerAuthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Side - Welcome Section -->
                    <div class="col-md-5 employer-welcome-section d-flex align-items-center justify-content-center position-relative">
                        <div class="auth-pattern"></div>
                        <div class="text-center text-white p-5 position-relative z-index-2">
                            <div class="auth-logo mb-4">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                            <h3 class="fw-bold mb-3 auth-title" id="employerWelcomeTitle">Welcome Back<br>to TugmaJobs</h3>
                            <p class="mb-4 text-white auth-subtitle" id="employerWelcomeSubtitle">Sign in to continue to your account.</p>
                            <div class="auth-features mt-5">
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span>Post unlimited jobs</span>
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span>Access qualified candidates</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span>AI-powered matching</span>
                                </div>
                            </div>
                            <div class="mt-5 pt-4">
                                <button type="button" class="btn btn-outline-light w-100 jobseeker-btn" onclick="openJobseekerModal(event)">
                                    I'm a jobseeker
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Form Section -->
                    <div class="col-md-7 p-5 position-relative bg-white">
                        <!-- Close Button -->
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

                        <!-- Employer Login Form -->
                        <div id="employerLoginForm" class="auth-form">
                            <h2 class="fw-bold mb-2 text-dark" style="font-size: 1.75rem;">Sign in</h2>
                            <p class="text-muted mb-4 small">Welcome back! Please enter your details.</p>

                            <!-- Google Login Button -->
                            <div class="mb-4">
                                <a href="<?php echo e(route('social.redirect', ['provider' => 'google', 'role' => 'employer'])); ?>" class="btn btn-outline-secondary w-100 social-btn google-btn d-flex align-items-center justify-content-center">
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

                            <form method="POST" action="<?php echo e(route('employer.login.submit')); ?>" class="needs-validation" novalidate id="employerLoginFormElement">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="login_method" id="employerLoginMethod" value="magic_link">

                                <!-- Email Input -->
                                <div class="mb-3">
                                    <label for="employerLoginEmail" class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                        <span>Email address</span>
                                        <a href="#" class="text-decoration-none text-muted small">
                                            <i class="fas fa-question-circle me-1"></i>
                                        </a>
                                    </label>
                                    <input type="email"
                                           class="form-control form-control-lg <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="employerLoginEmail"
                                           name="email"
                                           placeholder="you@example.com"
                                           value="<?php echo e(old('email')); ?>"
                                           required>
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback">
                                            <?php echo e($message); ?>

                                        </div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">
                                            Please provide a valid email.
                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Password Input (Hidden by default) -->
                                <div class="mb-3" id="employerPasswordField" style="display: none;">
                                    <label for="employerLoginPassword" class="form-label fw-semibold text-dark">Password</label>
                                    <input type="password"
                                           class="form-control form-control-lg <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="employerLoginPassword"
                                           name="password"
                                           placeholder="Enter your password">
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback">
                                            <?php echo e($message); ?>

                                        </div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">
                                            Please provide your password.
                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Remember Me (Hidden by default) -->
                                <div class="mb-3" id="employerRememberField" style="display: none;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="employerRemember">
                                        <label class="form-check-label" for="employerRemember">
                                            Remember me
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-email-code btn-lg fw-bold" id="employerLoginSubmitBtn">
                                        Email me a sign in code
                                    </button>
                                </div>

                                <!-- Toggle Password Login Link -->
                                <div class="text-center mb-4">
                                    <a href="#" class="text-primary text-decoration-none small" id="employerTogglePasswordLogin" onclick="toggleEmployerPasswordLogin(event)">
                                        Sign in with password instead
                                    </a>
                                </div>

                                <!-- Switch to Register -->
                                <div class="text-center">
                                    <span class="text-dark">Don't have an account? </span>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold" onclick="switchToEmployerRegister()">Register</a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Employer Register Form -->
                        <div id="employerRegisterForm" class="auth-form" style="display: none;">
                            <h2 class="fw-bold mb-2 text-dark" style="font-size: 1.75rem;">Create account</h2>
                            <p class="text-muted mb-4 small">Get started with your employer account.</p>

                            <!-- Google Login Button -->
                            <div class="mb-4">
                                <a href="<?php echo e(route('social.redirect', ['provider' => 'google', 'role' => 'employer'])); ?>" class="btn btn-outline-secondary w-100 social-btn google-btn d-flex align-items-center justify-content-center">
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

                            <form method="POST" action="<?php echo e(route('employer.register.submit')); ?>" class="needs-validation" novalidate id="employerRegisterFormElement">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="registration_method" id="employerRegistrationMethod" value="magic_link">

                                <!-- Name Input (Hidden by default) -->
                                <div class="mb-3" id="employerNameField" style="display: none;">
                                    <label for="employerRegisterName" class="form-label fw-semibold text-dark">Full Name / Company Name</label>
                                    <input type="text"
                                           class="form-control form-control-lg"
                                           id="employerRegisterName"
                                           name="name"
                                           placeholder="Enter your name or company name"
                                           value="<?php echo e(old('name')); ?>">
                                    <div class="invalid-feedback">
                                        Please provide your name.
                                    </div>
                                </div>

                                <!-- Email Input -->
                                <div class="mb-3">
                                    <label for="employerRegisterEmail" class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                        <span>Email address</span>
                                        <a href="#" class="text-decoration-none text-muted small">
                                            <i class="fas fa-question-circle me-1"></i>
                                        </a>
                                    </label>
                                    <input type="email"
                                           class="form-control form-control-lg"
                                           id="employerRegisterEmail"
                                           name="email"
                                           placeholder="you@example.com"
                                           value="<?php echo e(old('email')); ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide a valid email.
                                    </div>
                                </div>

                                <!-- Password Input (Hidden by default) -->
                                <div class="mb-3" id="employerRegisterPasswordField" style="display: none;">
                                    <label for="employerRegisterPassword" class="form-label fw-semibold text-dark">Password</label>
                                    <input type="password"
                                           class="form-control form-control-lg"
                                           id="employerRegisterPassword"
                                           name="password"
                                           placeholder="Create a password (min 5 characters)">
                                    <div class="invalid-feedback">
                                        Please provide a password (minimum 5 characters).
                                    </div>
                                </div>

                                <!-- Confirm Password Input (Hidden by default) -->
                                <div class="mb-3" id="employerConfirmPasswordField" style="display: none;">
                                    <label for="employerConfirmPassword" class="form-label fw-semibold text-dark">Confirm Password</label>
                                    <input type="password"
                                           class="form-control form-control-lg"
                                           id="employerConfirmPassword"
                                           name="confirm_password"
                                           placeholder="Confirm your password">
                                    <div class="invalid-feedback">
                                        Passwords must match.
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-email-code btn-lg fw-bold" id="employerRegisterSubmitBtn">
                                        Email me a sign in code
                                    </button>
                                </div>

                                <!-- Toggle Password Registration Link -->
                                <div class="text-center mb-4">
                                    <a href="#" class="text-primary text-decoration-none small" id="employerTogglePasswordRegister" onclick="toggleEmployerPasswordRegister(event)">
                                        Register with password instead
                                    </a>
                                </div>

                                <!-- Switch to Login -->
                                <div class="text-center">
                                    <span class="text-dark">Already have an account? </span>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold" onclick="switchToEmployerLogin()">Sign In</a>
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
.employer-welcome-section {
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
    color: white !important;
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

.jobseeker-btn {
    border: 2px solid rgba(255, 255, 255, 0.8);
    color: white;
    font-weight: 500;
    padding: 12px 24px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.jobseeker-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: white;
    color: white;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .employer-welcome-section {
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

    .jobseeker-btn {
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
</style>

<script>
function switchToEmployerRegister() {
    event.preventDefault();
    document.getElementById('employerLoginForm').style.display = 'none';
    document.getElementById('employerRegisterForm').style.display = 'block';
    document.getElementById('employerWelcomeTitle').innerHTML = 'Join<br>TugmaJobs';
    document.getElementById('employerWelcomeSubtitle').textContent = 'Create your account to get started.';
    document.getElementById('employerWelcomeSubtitle').className = 'mb-4 text-white auth-subtitle';
}

function switchToEmployerLogin() {
    event.preventDefault();
    document.getElementById('employerRegisterForm').style.display = 'none';
    document.getElementById('employerLoginForm').style.display = 'block';
    document.getElementById('employerWelcomeTitle').innerHTML = 'Welcome Back<br>to TugmaJobs';
    document.getElementById('employerWelcomeSubtitle').textContent = 'Sign in to continue to your account.';
    document.getElementById('employerWelcomeSubtitle').className = 'mb-4 text-white auth-subtitle';
}

// Function to toggle between password and magic link login
function toggleEmployerPasswordLogin(event) {
    event.preventDefault();

    const passwordField = document.getElementById('employerPasswordField');
    const rememberField = document.getElementById('employerRememberField');
    const loginMethodInput = document.getElementById('employerLoginMethod');
    const submitBtn = document.getElementById('employerLoginSubmitBtn');
    const toggleLink = document.getElementById('employerTogglePasswordLogin');
    const passwordInput = document.getElementById('employerLoginPassword');

    if (passwordField.style.display === 'none') {
        // Switch to password login
        passwordField.style.display = 'block';
        rememberField.style.display = 'block';
        loginMethodInput.value = 'password';
        submitBtn.textContent = 'Sign in';
        toggleLink.textContent = 'Sign in with email code instead';
        passwordInput.setAttribute('required', 'required');
    } else {
        // Switch to magic link login
        passwordField.style.display = 'none';
        rememberField.style.display = 'none';
        loginMethodInput.value = 'magic_link';
        submitBtn.textContent = 'Email me a sign in code';
        toggleLink.textContent = 'Sign in with password instead';
        passwordInput.removeAttribute('required');
    }
}

// Function to toggle between password and magic link registration
function toggleEmployerPasswordRegister(event) {
    event.preventDefault();

    const nameField = document.getElementById('employerNameField');
    const passwordField = document.getElementById('employerRegisterPasswordField');
    const confirmPasswordField = document.getElementById('employerConfirmPasswordField');
    const registrationMethodInput = document.getElementById('employerRegistrationMethod');
    const submitBtn = document.getElementById('employerRegisterSubmitBtn');
    const toggleLink = document.getElementById('employerTogglePasswordRegister');
    const nameInput = document.getElementById('employerRegisterName');
    const passwordInput = document.getElementById('employerRegisterPassword');
    const confirmPasswordInput = document.getElementById('employerConfirmPassword');

    if (passwordField.style.display === 'none') {
        // Switch to password registration
        nameField.style.display = 'block';
        passwordField.style.display = 'block';
        confirmPasswordField.style.display = 'block';
        registrationMethodInput.value = 'password';
        submitBtn.textContent = 'Create Account';
        toggleLink.textContent = 'Register with email code instead';
        nameInput.setAttribute('required', 'required');
        passwordInput.setAttribute('required', 'required');
        confirmPasswordInput.setAttribute('required', 'required');
    } else {
        // Switch to magic link registration
        nameField.style.display = 'none';
        passwordField.style.display = 'none';
        confirmPasswordField.style.display = 'none';
        registrationMethodInput.value = 'magic_link';
        submitBtn.textContent = 'Email me a sign in code';
        toggleLink.textContent = 'Register with password instead';
        nameInput.removeAttribute('required');
        passwordInput.removeAttribute('required');
        confirmPasswordInput.removeAttribute('required');
    }
}

// Function to switch to jobseeker modal
function openJobseekerModal(event) {
    event.preventDefault();
    // Close employer modal
    var employerModal = bootstrap.Modal.getInstance(document.getElementById('employerAuthModal'));
    if (employerModal) {
        employerModal.hide();
    }
    // Open jobseeker modal
    setTimeout(function() {
        var authModal = new bootstrap.Modal(document.getElementById('authModal'));
        authModal.show();
    }, 300);
}

// Show employer modal on page load if there are validation errors for employer
<?php if($errors->any() && request()->is('employer/*')): ?>
document.addEventListener('DOMContentLoaded', function() {
    var employerAuthModal = new bootstrap.Modal(document.getElementById('employerAuthModal'));
    employerAuthModal.show();

    <?php if($errors->has('name') || $errors->has('password_confirmation') || $errors->has('confirm_password')): ?>
        switchToEmployerRegister();
        // If registration was using password method, show password fields
        <?php if(old('registration_method') === 'password'): ?>
            document.getElementById('employerNameField').style.display = 'block';
            document.getElementById('employerRegisterPasswordField').style.display = 'block';
            document.getElementById('employerConfirmPasswordField').style.display = 'block';
            document.getElementById('employerRegistrationMethod').value = 'password';
            document.getElementById('employerRegisterSubmitBtn').textContent = 'Create Account';
            document.getElementById('employerTogglePasswordRegister').textContent = 'Register with email code instead';
        <?php endif; ?>
    <?php else: ?>
        // For login errors, show password fields if password login was used
        <?php if(old('login_method') === 'password'): ?>
            document.getElementById('employerPasswordField').style.display = 'block';
            document.getElementById('employerRememberField').style.display = 'block';
            document.getElementById('employerLoginMethod').value = 'password';
            document.getElementById('employerLoginSubmitBtn').textContent = 'Sign in';
            document.getElementById('employerTogglePasswordLogin').textContent = 'Sign in with email code instead';
        <?php endif; ?>
    <?php endif; ?>
});
<?php endif; ?>
</script><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/employer-auth-modal.blade.php ENDPATH**/ ?>