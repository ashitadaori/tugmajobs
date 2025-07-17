@extends('front.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>
                        Identity Verification (KYC)
                    </h4>
                </div>
                <div class="card-body py-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt text-primary" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h3 class="text-center mb-4">Verify Your Identity</h3>
                    
                    <p class="lead text-center mb-4">
                        To ensure the security and integrity of our platform, we need to verify your identity.
                    </p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock text-info mb-3" style="font-size: 2rem;"></i>
                                    <h5>Quick Process</h5>
                                    <p class="small">Verification typically takes 2-5 minutes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-lock text-success mb-3" style="font-size: 2rem;"></i>
                                    <h5>Secure & Private</h5>
                                    <p class="small">Your data is encrypted and protected</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h5><i class="fas fa-info-circle me-2"></i>What you'll need:</h5>
                        <ul class="mb-0">
                            <li>A valid government-issued ID (passport, driver's license, or national ID)</li>
                            <li>A smartphone or computer with a camera</li>
                            <li>Good lighting for clear photos</li>
                            <li>About 5 minutes of your time</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Important:</h5>
                        <ul class="mb-0">
                            <li>Make sure your documents are not expired</li>
                            <li>Ensure photos are clear and readable</li>
                            <li>Complete the process in one session</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mt-4">
                        <form action="{{ route('kyc.start') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-play me-2"></i>
                                Start Verification
                            </button>
                        </form>
                    </div>
                    
                    <div class="text-center mt-3">
                        @if(Auth::user()->isEmployer())
                            <a href="{{ route('employer.dashboard') }}" class="btn btn-link">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                        @else
                            <a href="{{ route('account.dashboard') }}" class="btn btn-link">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection