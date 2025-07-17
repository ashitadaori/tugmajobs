@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 text-center">{{ __('Create an Account') }}</h4>
                    <p class="text-center text-muted mb-0">{{ __('Join our community today') }}</p>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Full Name') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-user text-muted"></i>
                                </span>
                                <input id="name" type="text" 
                                    class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                    name="name" value="{{ old('name') }}" 
                                    required autocomplete="name" autofocus
                                    placeholder="Enter your full name">
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input id="email" type="email" 
                                    class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" 
                                    required autocomplete="email"
                                    placeholder="Enter your email">
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input id="password" type="password" 
                                    class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                    name="password" required autocomplete="new-password"
                                    placeholder="Create a password">
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input id="password-confirm" type="password" 
                                    class="form-control border-start-0" 
                                    name="password_confirmation" required 
                                    autocomplete="new-password"
                                    placeholder="Confirm your password">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="role" class="form-label">{{ __('Register as') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-user-tag text-muted"></i>
                                </span>
                                <select id="role" 
                                    class="form-select border-start-0 @error('role') is-invalid @enderror" 
                                    name="role" required>
                                    <option value="">Select your role</option>
                                    <option value="jobseeker" {{ old('role') == 'jobseeker' ? 'selected' : '' }}>Job Seeker</option>
                                    <option value="employer" {{ old('role') == 'employer' ? 'selected' : '' }}>Employer</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2">
                                {{ __('Register') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white py-3 text-center">
                    <p class="mb-0">{{ __('Already have an account?') }} 
                        <a href="{{ route('login') }}" class="text-primary text-decoration-none">{{ __('Login here') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 