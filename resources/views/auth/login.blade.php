@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-sign-in-alt fa-3x text-primary mb-3"></i>
                        <h3 class="mb-2">Sign In to TugmaJobs</h3>
                        <p class="text-muted">Use our modern sign-in experience</p>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToLogin()">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Open Sign In Modal
                    </button>
                    
                    <p class="text-muted small">
                        Don't have an account? 
                        <button type="button" class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToRegister()">
                            Sign up here
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-open modal if there are validation errors
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    var authModal = new bootstrap.Modal(document.getElementById('authModal'));
    authModal.show();
    switchToLogin();
});
@endif
</script>
@endsection 