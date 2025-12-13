@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-building fa-3x text-success mb-3"></i>
                        <h3 class="mb-2">Create Employer Account</h3>
                        <p class="text-muted">Join thousands of companies hiring on TugmaJobs</p>
                    </div>
                    
                    <button type="button" class="btn btn-success btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#employerAuthModal" onclick="switchToEmployerRegister()">
                        <i class="fas fa-user-plus me-2"></i>
                        Create Employer Account
                    </button>
                    
                    <p class="text-muted small">
                        Already have an employer account? 
                        <button type="button" class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#employerAuthModal" onclick="switchToEmployerLogin()">
                            Sign in here
                        </button>
                    </p>
                    
                    <hr class="my-4">
                    
                    <p class="text-muted small">
                        Looking for a job? 
                        <a href="{{ route('register') }}" class="text-primary">
                            Job seeker registration
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
    switchToEmployerRegister();
});
@endif
</script>
@endsection