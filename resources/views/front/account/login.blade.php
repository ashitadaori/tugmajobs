@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 text-center">{{ __('Welcome Back!') }}</h4>
                    <p class="text-center text-muted mb-0">{{ __('Please login to your account') }}</p>
                </div>

                <div class="card-body p-4">
                    <!-- Google Login Button -->
                    <div class="mb-4">
                        <a href="{{ route('social.redirect', ['provider' => 'google', 'role' => 'jobseeker']) }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-2">
                            <i class="fab fa-google me-2" style="color: #4285f4;"></i>
                            Continue with Google
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="d-flex align-items-center mb-4">
                        <hr class="flex-grow-1">
                        <span class="px-3 text-muted small">or</span>
                        <hr class="flex-grow-1">
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="login_method" id="loginMethod" value="magic_link">

                        <div class="mb-4">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input id="email" type="email"
                                    class="form-control border-start-0 @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}"
                                    required autocomplete="email" autofocus
                                    placeholder="Enter your email">
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Field (hidden by default for magic link) -->
                        <div class="mb-4" id="passwordField" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <a class="text-primary text-decoration-none small" href="{{ route('account.forgotPassword') }}">
                                    {{ __('Forgot Password?') }}
                                </a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input id="password" type="password"
                                    class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror"
                                    name="password" autocomplete="current-password"
                                    placeholder="Enter your password">
                                <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePasswordVisibility()" style="border-color: #dee2e6;">
                                    <i class="fas fa-eye-slash" id="passwordToggleIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Remember Me (shown only for password login) -->
                        <div class="mb-4" id="rememberMeField" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2" id="submitBtn">
                                <i class="fas fa-envelope me-2"></i>{{ __('Email me a sign in code') }}
                            </button>
                        </div>

                        <!-- Toggle Login Method -->
                        <div class="text-center mt-3">
                            <a href="#" class="text-muted text-decoration-none small" id="toggleMethod" onclick="toggleLoginMethod(event)">
                                <i class="fas fa-key me-1"></i>
                                Sign in with password instead
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white py-3 text-center">
                    <p class="mb-0">{{ __('Don\'t have an account?') }}
                        <a href="{{ route('account.registration') }}" class="text-primary text-decoration-none">{{ __('Register here') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLoginMethod(event) {
    event.preventDefault();
    const passwordField = document.getElementById('passwordField');
    const rememberMeField = document.getElementById('rememberMeField');
    const loginMethodInput = document.getElementById('loginMethod');
    const toggleLink = document.getElementById('toggleMethod');
    const submitBtn = document.getElementById('submitBtn');
    const passwordInput = document.getElementById('password');

    if (passwordField.style.display === 'none') {
        // Switch to password mode
        passwordField.style.display = 'block';
        rememberMeField.style.display = 'block';
        loginMethodInput.value = 'password';
        toggleLink.innerHTML = '<i class="fas fa-envelope me-1"></i>Sign in with email code instead';
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Sign In';
        passwordInput.setAttribute('required', 'required');
    } else {
        // Switch to magic link mode
        passwordField.style.display = 'none';
        rememberMeField.style.display = 'none';
        loginMethodInput.value = 'magic_link';
        toggleLink.innerHTML = '<i class="fas fa-key me-1"></i>Sign in with password instead';
        submitBtn.innerHTML = '<i class="fas fa-envelope me-2"></i>Email me a sign in code';
        passwordInput.removeAttribute('required');
    }
}

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('passwordToggleIcon');

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

// Show toast notifications for login errors
document.addEventListener('DOMContentLoaded', function() {
    @if(Session::has('error'))
        if (typeof showToast === 'function') {
            showToast('{{ Session::get('error') }}', 'error', 5000);
        }
    @endif

    @if(Session::has('success'))
        if (typeof showToast === 'function') {
            showToast('{{ Session::get('success') }}', 'success', 5000);
        }
    @endif

    @if($errors->any())
        if (typeof showToast === 'function') {
            @foreach($errors->all() as $error)
                showToast('{{ $error }}', 'error', 5000);
            @endforeach
        }
    @endif
});
</script>
@endsection
