@props(['user', 'showActions' => true, 'compact' => false])

@php
    $user = $user ?? Auth::user();
    $statusConfig = [
        'pending' => [
            'icon' => 'fas fa-clock',
            'color' => 'secondary',
            'bg' => 'light',
            'title' => 'Identity Verification Required',
            'message' => 'Verify your identity to build trust and unlock all features.',
            'action' => 'Start Verification'
        ],
        'in_progress' => [
            'icon' => 'fas fa-hourglass-half',
            'color' => 'warning',
            'bg' => 'warning-subtle',
            'title' => 'Verification in Progress',
            'message' => 'Your identity verification is being processed. This usually takes 2-5 minutes.',
            'action' => 'Check Status'
        ],
        'verified' => [
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'bg' => 'success-subtle',
            'title' => 'Identity Verified',
            'message' => 'Your identity has been successfully verified. You have access to all features.',
            'action' => null
        ],
        'failed' => [
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'bg' => 'danger-subtle',
            'title' => 'Verification Failed',
            'message' => 'We were unable to verify your identity. Please try again with clear documents.',
            'action' => 'Try Again'
        ],
        'expired' => [
            'icon' => 'fas fa-clock',
            'color' => 'dark',
            'bg' => 'dark-subtle',
            'title' => 'Verification Expired',
            'message' => 'Your verification session has expired. Please start a new verification.',
            'action' => 'Start New Verification'
        ]
    ];
    
    $config = $statusConfig[$user->kyc_status] ?? $statusConfig['pending'];
@endphp

<div class="card {{ $compact ? 'border-0 shadow-sm' : 'mb-4' }}">
    @if(!$compact)
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center">
            <i class="{{ $config['icon'] }} text-{{ $config['color'] }} me-2"></i>
            <h5 class="mb-0">Identity Verification</h5>
            @if($user->isKycVerified())
                <span class="badge bg-success ms-auto">
                    <i class="fas fa-check-circle me-1"></i>Verified
                </span>
            @endif
        </div>
    </div>
    @endif
    
    <div class="card-body {{ $compact ? 'p-3' : '' }}">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0 me-3">
                <div class="verification-icon bg-{{ $config['bg'] }} text-{{ $config['color'] }} rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: {{ $compact ? '40px' : '48px' }}; height: {{ $compact ? '40px' : '48px' }};">
                    <i class="{{ $config['icon'] }}" style="font-size: {{ $compact ? '1.2rem' : '1.5rem' }};"></i>
                </div>
            </div>
            
            <div class="flex-grow-1">
                <h6 class="mb-1 {{ $compact ? 'fs-6' : '' }}">{{ $config['title'] }}</h6>
                <p class="text-muted mb-0 {{ $compact ? 'small' : '' }}">{{ $config['message'] }}</p>
                
                @if($user->kyc_verified_at && $user->isKycVerified())
                    <small class="text-success">
                        <i class="fas fa-calendar-check me-1"></i>
                        Verified on {{ $user->kyc_verified_at->format('M d, Y') }}
                    </small>
                @endif
                
                @if($showActions && $config['action'])
                    <div class="mt-3">
                        @if($user->kyc_status === 'in_progress')
                            <button class="btn btn-{{ $config['color'] }} btn-sm" onclick="checkKycStatus('{{ $user->kyc_session_id }}')">
                                <i class="fas fa-sync me-1"></i>{{ $config['action'] }}
                            </button>
                        @else
                            <a href="{{ route('kyc.start.form') }}" class="btn btn-{{ $config['color'] }} btn-sm">
                                <i class="{{ $config['icon'] }} me-1"></i>{{ $config['action'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($showActions)
<script>
function checkKycStatus(sessionId) {
    if (!sessionId) {
        alert('No session ID available');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking...';
    btn.disabled = true;
    
    fetch('{{ route("kyc.check-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            session_id: sessionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'completed') {
            window.location.reload();
        } else if (data.status === 'failed') {
            alert('Verification failed. Please try again.');
            window.location.reload();
        } else {
            alert('Still processing. Please wait a moment and try again.');
        }
    })
    .catch(error => {
        alert('Error checking status. Please try again.');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endif