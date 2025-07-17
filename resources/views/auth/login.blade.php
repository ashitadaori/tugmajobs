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
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

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

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                @if (Route::has('password.request'))
                                    <a class="text-primary text-decoration-none small" href="{{ route('password.request') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                                @endif
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input id="password" type="password" 
                                    class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                    name="password" required autocomplete="current-password"
                                    placeholder="Enter your password">
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2">
                                {{ __('Login') }}
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer bg-white py-3 text-center">
                    <p class="mb-0">{{ __('Don\'t have an account?') }} 
                        <a href="{{ route('register') }}" class="text-primary text-decoration-none">{{ __('Register here') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 