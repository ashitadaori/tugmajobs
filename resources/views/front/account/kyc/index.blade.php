@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-center mb-4">Identity Verification Status</h3>
                    
                    @if(session('success'))
                        <div class="alert alert-success mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info mb-4">
                            {{ session('info') }}
                        </div>
                    @endif

                    <div class="verification-status mb-4">
                        @switch($user->kyc_status)
                            @case('verified')
                                <div class="alert alert-success">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Verification Complete
                                    </h5>
                                    <p class="mb-0">Your identity has been verified successfully.</p>
                                    <small class="text-muted">
                                        Verified on {{ $user->kyc_completed_at->format('M d, Y') }}
                                    </small>
                                </div>
                                @break

                            @case('in_progress')
                                <div class="alert alert-info">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-clock me-2"></i>
                                        Verification in Progress
                                    </h5>
                                    <p class="mb-0">Your verification is being processed. This usually takes a few minutes.</p>
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-primary" onclick="checkStatus()">
                                            Check Status
                                        </button>
                                    </div>
                                </div>
                                @break

                            @case('failed')
                                <div class="alert alert-danger">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        Verification Failed
                                    </h5>
                                    <p class="mb-0">Unfortunately, your verification attempt was unsuccessful.</p>
                                    <div class="mt-3">
                                        <a href="{{ route('account.kyc.create') }}" class="btn btn-primary">
                                            Try Again
                                        </a>
                                    </div>
                                </div>
                                @break

                            @default
                                <div class="alert alert-warning">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Verification Required
                                    </h5>
                                    <p class="mb-0">Please complete your identity verification to access all features.</p>
                                    <div class="mt-3">
                                        <a href="{{ route('account.kyc.create') }}" class="btn btn-primary">
                                            Start Verification
                                        </a>
                                    </div>
                                </div>
                        @endswitch
                    </div>

                    <div class="verification-info">
                        <h5 class="mb-3">Why do we need to verify your identity?</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                To ensure a safe and trusted community
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-user-check text-primary me-2"></i>
                                To prevent fraud and maintain platform integrity
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-lock text-primary me-2"></i>
                                To protect sensitive information
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkStatus() {
    fetch('{{ route("account.kyc.check-status") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.status !== '{{ $user->kyc_status }}') {
            window.location.reload();
        } else {
            alert('Status unchanged. Please try again in a few moments.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error checking status. Please try again.');
    });
}
</script>
@endpush 