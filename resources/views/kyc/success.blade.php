@extends('front.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        KYC Verification Successful
                    </h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-success mb-3">Verification Complete!</h3>
                    <p class="lead mb-4">
                        Your identity has been successfully verified. You can now access all features of our platform.
                    </p>
                    
                    @if(isset($sessionId))
                    <div class="alert alert-info">
                        <small>Session ID: {{ $sessionId }}</small>
                    </div>
                    @endif
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-info-circle me-2"></i>Important Note:</h6>
                        <p class="mb-0">If you started verification on a different device (desktop/laptop), that page should automatically update and redirect you to the dashboard within a few seconds.</p>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        @auth
                            @if(Auth::user()->isEmployer())
                                <a href="{{ route('employer.dashboard') }}" class="btn btn-primary btn-lg me-md-2">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('account.dashboard') }}" class="btn btn-primary btn-lg me-md-2">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Go to Dashboard
                                </a>
                            @endif
                            <a href="{{ route('jobs') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-briefcase me-2"></i>
                                Browse Jobs
                            </a>
                        @else
                            <a href="{{ route('account.login') }}" class="btn btn-primary btn-lg me-md-2">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login to Continue
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-home me-2"></i>
                                Go to Home
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection