@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-building fa-3x text-primary mb-3"></i>
                        <h3 class="mb-2">Employer Sign In</h3>
                        <p class="text-muted">Access your employer dashboard</p>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#employerAuthModal" onclick="switchToEmployerLogin()">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Open Employer Sign In
                    </button>
                    
                    <p class="text-muted small">
                        Don't have an employer account? 
                        <button type="button" class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#employerAuthModal" onclick="switchToEmployerRegister()">
                            Create one here
                        </button>
                    </p>
                    
                    <hr class="my-4">
                    
                    <p class="text-muted small">
                        Looking for a job? 
                        <a href="{{ route('login') }}" class="text-primary">
                            Job seeker login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employer Auth Modal -->
@include('components.employer-auth-modal')

<script>
// Auto-open modal if there are validation errors
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    var employerAuthModal = new bootstrap.Modal(document.getElementById('employerAuthModal'));
    employerAuthModal.show();
    switchToEmployerLogin();

    // If password login was used, show password fields
    @if(old('login_method') === 'password')
        document.getElementById('employerPasswordField').style.display = 'block';
        document.getElementById('employerRememberField').style.display = 'block';
        document.getElementById('employerLoginMethod').value = 'password';
        document.getElementById('employerLoginSubmitBtn').textContent = 'Sign in';
        document.getElementById('employerTogglePasswordLogin').textContent = 'Sign in with email code instead';
    @endif
});
@endif
</script>
@endsection