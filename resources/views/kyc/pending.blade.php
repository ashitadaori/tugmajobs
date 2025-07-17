@extends('front.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        KYC Verification Pending
                    </h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-hourglass-half text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-warning mb-3">Verification in Progress</h3>
                    <p class="lead mb-4">
                        Your identity verification is currently being processed. This usually takes a few minutes.
                    </p>
                    
                    @if(isset($sessionId))
                    <div class="alert alert-info">
                        <small>Session ID: {{ $sessionId }}</small>
                    </div>
                    @endif
                    
                    <div class="alert alert-info">
                        <h5>What happens next:</h5>
                        <ul class="list-unstyled mb-0">
                            <li>• Your documents are being reviewed</li>
                            <li>• You'll receive an email notification once complete</li>
                            <li>• The process typically takes 2-5 minutes</li>
                            <li>• You can check the status below</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button id="checkStatusBtn" class="btn btn-primary btn-lg me-md-2">
                            <i class="fas fa-sync me-2"></i>
                            Check Status
                        </button>
                        <a href="{{ route('account.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                    
                    <div id="statusResult" class="mt-4" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkStatusBtn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking...';
    btn.disabled = true;
    
    fetch('{{ route("kyc.check-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            session_id: '{{ $sessionId ?? "" }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('statusResult');
        
        if (data.status === 'completed') {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Verification completed! Redirecting...</div>';
            setTimeout(() => {
                window.location.href = '{{ route("kyc.success") }}';
            }, 2000);
        } else if (data.status === 'failed') {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Verification failed. Please try again.</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-clock me-2"></i>Still processing... Please wait a moment and try again.</div>';
        }
        
        resultDiv.style.display = 'block';
    })
    .catch(error => {
        document.getElementById('statusResult').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error checking status. Please try again.</div>';
        document.getElementById('statusResult').style.display = 'block';
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
@endsection