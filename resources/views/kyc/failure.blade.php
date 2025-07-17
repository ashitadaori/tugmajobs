@extends('front.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        KYC Verification Failed
                    </h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-danger mb-3">Verification Failed</h3>
                    <p class="lead mb-4">
                        We were unable to verify your identity at this time. This could be due to various reasons such as unclear documents or technical issues.
                    </p>
                    
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    
                    @if(isset($sessionId))
                    <div class="alert alert-info">
                        <small>Session ID: {{ $sessionId }}</small>
                    </div>
                    @endif
                    
                    <div class="alert alert-warning">
                        <h5>What you can do:</h5>
                        <ul class="list-unstyled mb-0">
                            <li>• Ensure your documents are clear and readable</li>
                            <li>• Make sure your documents are valid and not expired</li>
                            <li>• Try again with different lighting conditions</li>
                            <li>• Contact support if the issue persists</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        @auth
                            <a href="{{ route('kyc.start') }}" class="btn btn-primary btn-lg me-md-2">
                                <i class="fas fa-redo me-2"></i>
                                Try Again
                            </a>
                            @if(Auth::user()->isEmployer())
                                <a href="{{ route('employer.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Back to Dashboard
                                </a>
                            @else
                                <a href="{{ route('account.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Back to Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('account.login') }}" class="btn btn-primary btn-lg me-md-2">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login to Try Again
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
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