@extends('front.layouts.app')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="auth-wrapper">
                    <!-- Left side - Registration Form -->
                    <div class="auth-form-side">
                        <div class="auth-form-content">
                            <div class="logo-section mb-4">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h1>Create Account</h1>
                            <p class="subtitle">Join our community today</p>

                    <form action="{{ route('account.processRegistration') }}" method="post" name="registrationForm" id="registrationForm">
                        @csrf
                                <div class="mb-4">
                                    <label for="name" class="form-label">Full Name</label>
                                    <div class="input-group">
                                        <input type="text" name="name" id="name" 
                                            class="form-control @error('name') is-invalid @enderror" 
                                            placeholder="Enter your full name" value="{{ old('name') }}">
                                        <span class="input-icon">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <input type="email" name="email" id="email" 
                                            class="form-control @error('email') is-invalid @enderror" 
                                            placeholder="name@example.com" value="{{ old('email') }}">
                                        <span class="input-icon">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" 
                                            class="form-control @error('password') is-invalid @enderror" 
                                            placeholder="••••••••">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', 'togglePasswordIcon1')">
                                            <i class="fas fa-eye-slash" id="togglePasswordIcon1"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_password" id="confirm_password" 
                                            class="form-control @error('confirm_password') is-invalid @enderror" 
                                            placeholder="••••••••">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password', 'togglePasswordIcon2')">
                                            <i class="fas fa-eye-slash" id="togglePasswordIcon2"></i>
                                        </button>
                                    </div>
                                    @error('confirm_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="role" class="form-label">I am a</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="role" id="jobseeker" value="jobseeker" checked>
                                            <label class="form-check-label" for="jobseeker">Job Seeker</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="role" id="employer" value="employer">
                                            <label class="form-check-label" for="employer">Employer</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="auth-btn">
                                    Create Account
                                </button>

                                <div class="social-login">
                                    <div class="divider">
                                        <span>or sign up with</span>
                                    </div>
                                    <div class="social-buttons">
                                        <a href="#" class="social-button">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google">
                                        </a>
                                        <a href="#" class="social-button">
                                            <i class="fab fa-github"></i>
                                        </a>
                                        <a href="#" class="social-button">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                    </div>
                        </div>
                            </form>

                            <div class="auth-footer">
                                Already have an account? <a href="{{ route('login') }}">Sign in</a>
                        </div>
                        </div>
                    </div>

                    <!-- Right side - Features -->
                    <div class="auth-testimonial-side">
                        <div class="testimonial-content">
                            <h2>Join Our Growing Community</h2>
                            <div class="features-list">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="feature-text">
                                        <h4>Smart Job Matching</h4>
                                        <p>Our AI-powered system matches you with the perfect job opportunities.</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="feature-text">
                                        <h4>Real-time Notifications</h4>
                                        <p>Get instant alerts for new job postings that match your preferences.</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="feature-text">
                                        <h4>Easy Application Process</h4>
                                        <p>Apply to multiple jobs with just a few clicks using your profile.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="info-box">
                                <h3>Start Your Career Journey Today</h3>
                                <p>Join thousands of professionals who have found their dream jobs through our platform.</p>
                                <div class="stats">
                                    <div class="stat-item">
                                        <h4>500+</h4>
                                        <p>New Jobs Daily</p>
                                    </div>
                                    <div class="stat-item">
                                        <h4>10k+</h4>
                                        <p>Active Users</p>
                                    </div>
                                    <div class="stat-item">
                                        <h4>98%</h4>
                                        <p>Success Rate</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Reuse the same base styles from login.blade.php */
.auth-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
    background: #f5f7fa;
}

.auth-wrapper {
    display: flex;
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.auth-form-side {
    width: 45%;
    padding: 3rem;
    background: #fff;
}

.auth-testimonial-side {
    width: 55%;
    background: linear-gradient(135deg, #1e2a78 0%, #ff6b6b 100%);
    padding: 3rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.logo-section {
    text-align: center;
}

.logo-section i {
    font-size: 2rem;
    color: #1e2a78;
    background: rgba(30, 42, 120, 0.1);
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
}

.auth-form-content h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1e2a78;
    margin-bottom: 0.5rem;
}

.subtitle {
    color: #666;
    margin-bottom: 2rem;
}

.form-label {
    color: #1e2a78;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.input-group {
    position: relative;
}

.form-control {
    padding: 0.8rem 1rem;
    border-radius: 12px;
    border: 2px solid #eee;
    background: #f8f9fa;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #1e2a78;
    box-shadow: 0 0 0 4px rgba(30, 42, 120, 0.1);
}

.input-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.auth-btn {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 12px;
    background: #1e2a78;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.auth-btn:hover {
    background: #ff6b6b;
    transform: translateY(-2px);
}

.social-login {
    margin-top: 2rem;
}

.divider {
    text-align: center;
    position: relative;
    margin: 1.5rem 0;
}

.divider::before,
.divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 45%;
    height: 1px;
    background: #eee;
}

.divider::before {
    left: 0;
}

.divider::after {
    right: 0;
}

.divider span {
    background: #fff;
    padding: 0 1rem;
    color: #666;
    font-size: 0.9rem;
}

.social-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.social-button {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    border: 2px solid #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.3s ease;
}

.social-button img {
    width: 20px;
    height: 20px;
}

.social-button:hover {
    border-color: #1e2a78;
    color: #1e2a78;
    transform: translateY(-2px);
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    color: #666;
}

.auth-footer a {
    color: #1e2a78;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

/* Features Side Styling */
.testimonial-content {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.testimonial-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
}

.features-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.feature-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.feature-text h4 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.feature-text p {
    font-size: 0.9rem;
    opacity: 0.8;
    margin: 0;
}

.info-box {
    background: rgba(255,255,255,0.1);
    border-radius: 20px;
    padding: 2rem;
}

.info-box h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.info-box p {
    opacity: 0.8;
    margin-bottom: 1.5rem;
}

.stats {
    display: flex;
    justify-content: space-between;
    text-align: center;
}

.stat-item h4 {
    font-size: 1.5rem;
    margin-bottom: 0.2rem;
}

.stat-item p {
    font-size: 0.9rem;
    opacity: 0.8;
    margin: 0;
}

@media (max-width: 991px) {
    .auth-wrapper {
        flex-direction: column;
    }
    
    .auth-form-side,
    .auth-testimonial-side {
        width: 100%;
    }
    
    .auth-testimonial-side {
        padding: 2rem;
    }
}

@media (max-width: 576px) {
    .auth-form-side {
        padding: 2rem;
    }
    
    .testimonial-content h2 {
        font-size: 2rem;
    }
    
    .stats {
        flex-direction: column;
        gap: 1rem;
    }
}

.toggle-password {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-color: #eee;
    background: #f8f9fa;
}
.toggle-password:hover {
    background: #e9ecef;
    border-color: #ddd;
}
.toggle-password:focus {
    box-shadow: none;
    border-color: #1e2a78;
}
</style>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Password toggle function
    window.togglePassword = function(inputId, iconId) {
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
    };

    // Form submission
    $("#registrationForm").submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status == true) {
                    $('.invalid-feedback').remove();
                    $('.is-invalid').removeClass('is-invalid');
                    
                    // Redirect to login page immediately without showing JSON
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    // Handle validation errors
                    if (response.errors) {
                        $('.invalid-feedback').remove();
                        $('.is-invalid').removeClass('is-invalid');
                        
                        $.each(response.errors, function(key, value) {
                            $('#' + key).addClass('is-invalid');
                            $('#' + key).after('<div class="invalid-feedback">' + value + '</div>');
                        });
                    }
                }
            },
            error: function(response) {
                console.log('Error:', response);
            }
        });
    });
});
</script>
@endsection
