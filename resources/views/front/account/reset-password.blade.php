@extends('front.layouts.app')

@section('content')
<div class="reset-password-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-lg-10 col-xl-9">
                <div class="reset-card">
                    <div class="row g-0">
                        <!-- Left Side - Welcome Section -->
                        <div class="col-md-5 welcome-section d-none d-md-flex">
                            <div class="welcome-pattern"></div>
                            <div class="welcome-content">
                                <div class="welcome-logo">
                                    <i class="fas fa-shield-alt fa-3x"></i>
                                </div>
                                <h3 class="welcome-title">Reset Your<br>Password</h3>
                                <p class="welcome-subtitle">Create a new secure password for your account.</p>
                                <div class="welcome-features">
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <span>Use at least 5 characters</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <span>Mix letters and numbers</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <span>Avoid common words</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Form Section -->
                        <div class="col-md-7 form-section">
                            <div class="form-content">
                                <h2 class="form-title">Set New Password</h2>
                                <p class="form-subtitle">Choose a strong password for your account</p>

                                @if(Session::has('success'))
                                <div class="alert alert-success d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ Session::get('success') }}
                                </div>
                                @endif

                                @if(Session::has('error'))
                                <div class="alert alert-danger d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ Session::get('error') }}
                                </div>
                                @endif

                                <form action="{{ route('account.processResetPassword') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $tokenString }}">

                                    <div class="mb-4">
                                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="new_password" id="new_password"
                                                class="form-control form-control-lg @error('new_password') is-invalid @enderror"
                                                placeholder="Enter new password">
                                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" onclick="togglePassword('new_password', 'toggleIcon1')">
                                                <i class="fas fa-eye-slash" id="toggleIcon1"></i>
                                            </button>
                                        </div>
                                        @error('new_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_password" id="confirm_password"
                                                class="form-control form-control-lg @error('confirm_password') is-invalid @enderror"
                                                placeholder="Confirm new password">
                                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                                <i class="fas fa-eye-slash" id="toggleIcon2"></i>
                                            </button>
                                        </div>
                                        @error('confirm_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg fw-bold submit-btn">
                                            <i class="fas fa-lock me-2"></i>Reset Password
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <a href="{{ route('login') }}" class="text-muted text-decoration-none small">
                                            <i class="fas fa-arrow-left me-1"></i>Back to Login
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.reset-password-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
}

.reset-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
}

/* Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 500px;
}

.welcome-pattern {
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

.welcome-content {
    position: relative;
    z-index: 2;
    text-align: center;
    padding: 2rem;
    color: white;
}

.welcome-logo {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    backdrop-filter: blur(10px);
}

.welcome-logo i {
    color: white;
}

.welcome-title {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 1rem;
    color: white !important;
}

.welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    color: white !important;
}

.welcome-features {
    text-align: left;
    max-width: 250px;
    margin: 0 auto;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    color: white !important;
}

.feature-item i {
    color: #10b981;
    font-size: 0.8rem;
}

.feature-item span {
    color: white !important;
}

/* Form Section */
.form-section {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
}

.form-content {
    width: 100%;
    max-width: 400px;
}

.form-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: #6b7280;
    margin-bottom: 2rem;
}

.form-label {
    font-size: 14px;
    color: #374151;
    margin-bottom: 6px;
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

/* Input group for password toggle */
.input-group .form-control-lg {
    border-right: 0;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.input-group .form-control-lg:focus {
    border-right: 0;
}

.input-group .form-control-lg:focus + .password-toggle-btn {
    border-color: #2563eb;
}

.password-toggle-btn {
    border-left: 0;
    border-color: #d1d5db;
    background: #f9fafb;
    padding: 0 16px;
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
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

.submit-btn {
    background: #2563eb;
    border: none;
    border-radius: 8px;
    padding: 14px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

.alert {
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-section {
        padding: 2rem;
    }

    .reset-password-page {
        padding: 1rem;
    }

    .reset-card {
        border-radius: 16px;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Password toggle function
function togglePassword(inputId, iconId) {
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
</script>
@endsection
