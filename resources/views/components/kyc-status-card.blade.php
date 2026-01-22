@props(['user', 'showActions' => true, 'compact' => false])

@php
    $user = $user ?? Auth::user();
    $statusConfig = [
        'pending' => [
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'warning',
            'bg' => 'warning-subtle',
            'title' => 'Identity Verification Required',
            'message' => '⚠️ You must complete KYC verification before you can apply for jobs. Verify your identity now to start applying.',
            'action' => 'Start Verification'
        ],
        'pending_review' => [
            'icon' => 'fas fa-hourglass-half',
            'color' => 'info',
            'bg' => 'info-subtle',
            'title' => 'Manual Review in Progress',
            'message' => 'Your documents are being reviewed by our team. This typically takes 1-3 business days.',
            'action' => 'View Status'
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
            'message' => '❌ We were unable to verify your identity. You cannot apply for jobs until verification is complete. Please try again with clear documents.',
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

<style>
.kyc-status-card {
    background: white;
    border-radius: var(--radius-lg, 16px);
    border: 1px solid var(--modern-gray-100, #f1f5f9);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: box-shadow 0.25s ease;
}

.kyc-status-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.kyc-card-header {
    padding: 1.25rem 1.5rem;
    background: var(--modern-gray-50, #f8fafc);
    border-bottom: 1px solid var(--modern-gray-100, #f1f5f9);
    display: flex;
    align-items: center;
}

.kyc-card-header h5 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--modern-gray-800, #1e293b);
    margin: 0;
}

.kyc-card-header .verified-badge {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    background: var(--modern-success-light, #d1fae5);
    color: #047857;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.kyc-card-body {
    padding: 1.75rem;
}

.kyc-icon-wrapper {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-md, 12px);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.kyc-icon-wrapper.compact {
    width: 40px;
    height: 40px;
}

.kyc-icon-wrapper.warning {
    background: var(--modern-warning-light, #fef3c7);
    color: var(--modern-warning, #f59e0b);
}

.kyc-icon-wrapper.success {
    background: var(--modern-success-light, #d1fae5);
    color: var(--modern-success, #10b981);
}

.kyc-icon-wrapper.danger {
    background: var(--modern-danger-light, #fee2e2);
    color: var(--modern-danger, #ef4444);
}

.kyc-icon-wrapper.dark {
    background: var(--modern-gray-100, #f1f5f9);
    color: var(--modern-gray-600, #475569);
}

.kyc-icon-wrapper.info {
    background: var(--modern-info-light, #e0f2fe);
    color: var(--modern-info, #0ea5e9);
}

.kyc-content h6 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--modern-gray-800, #1e293b);
    margin: 0 0 0.375rem 0;
}

.kyc-content p {
    font-size: 0.9375rem;
    color: var(--modern-gray-500, #64748b);
    margin: 0;
    line-height: 1.5;
}

.kyc-verified-date {
    font-size: 0.8125rem;
    color: var(--modern-success, #10b981);
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.kyc-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.kyc-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    border-radius: var(--radius-md, 12px);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}

.kyc-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
}

.kyc-btn-primary.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
}

.kyc-btn-primary.warning:hover {
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
}

.kyc-btn-primary.danger {
    background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

.kyc-btn-primary.danger:hover {
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
}

.kyc-btn-primary.info {
    background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
}

.kyc-btn-primary.info:hover {
    box-shadow: 0 6px 16px rgba(14, 165, 233, 0.35);
}

.kyc-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: white;
    color: var(--modern-gray-700, #334155);
    border: 1px solid var(--modern-gray-300, #cbd5e1);
    border-radius: var(--radius-md, 12px);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}

.kyc-btn-secondary:hover {
    background: var(--modern-gray-50, #f8fafc);
    border-color: var(--modern-gray-400, #94a3b8);
}
</style>

<div class="kyc-status-card {{ $compact ? '' : 'mb-4' }}">
    @if(!$compact)
    <div class="kyc-card-header">
        <i class="{{ $config['icon'] }} me-2" style="color: var(--modern-{{ $config['color'] }}, inherit);"></i>
        <h5>Identity Verification</h5>
        @if($user->isKycVerified())
            <span class="verified-badge">
                <i class="fas fa-check-circle"></i>Verified
            </span>
        @endif
    </div>
    @endif

    <div class="kyc-card-body {{ $compact ? 'p-3' : '' }}">
        <div class="d-flex align-items-start" style="gap: 1rem;">
            <div class="kyc-icon-wrapper {{ $config['color'] }} {{ $compact ? 'compact' : '' }}">
                <i class="{{ $config['icon'] }}" style="font-size: {{ $compact ? '1.125rem' : '1.375rem' }};"></i>
            </div>

            <div class="kyc-content flex-grow-1">
                <h6 class="{{ $compact ? 'fs-6' : '' }}">{{ $config['title'] }}</h6>
                <p class="{{ $compact ? 'small' : '' }}">{{ $config['message'] }}</p>

                @if($user->kyc_verified_at && $user->isKycVerified())
                    <div class="kyc-verified-date">
                        <i class="fas fa-calendar-check"></i>
                        Verified on {{ $user->kyc_verified_at->format('M d, Y') }}
                    </div>
                @endif

                @if($showActions && $config['action'])
                    <div class="kyc-actions">
                        @if($user->kyc_status === 'pending_review')
                            {{-- Manual review in progress - show view status button --}}
                            <a href="{{ route('kyc.manual.status') }}" class="kyc-btn-primary info">
                                <i class="fas fa-eye"></i>{{ $config['action'] }}
                            </a>
                        @elseif($user->kyc_status === 'in_progress')
                            <button class="kyc-btn-primary {{ $config['color'] }}" onclick="checkKycStatus('{{ $user->kyc_session_id }}')">
                                <i class="fas fa-sync"></i>{{ $config['action'] }}
                            </button>
                            <button class="kyc-btn-secondary" onclick="window.resetKycVerification ? window.resetKycVerification() : alert('Reset function not available')">
                                <i class="fas fa-redo"></i>Start Over
                            </button>
                        @else
                            {{-- Show only the main verification option --}}
                            <a href="{{ route('kyc.start.form') }}" class="kyc-btn-primary {{ $config['color'] }}">
                                <i class="fas fa-bolt"></i>{{ $config['action'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($showActions)
<!-- Include KYC inline verification script -->
<script src="{{ asset('assets/js/kyc-inline-verification.js') }}"></script>

<script>
// Set current user ID for verification polling
window.currentUserId = {{ Auth::id() }};

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

// Enhanced check status function that uses the global function if available
function enhancedCheckStatus() {
    if (typeof window.checkVerificationComplete === 'function') {
        window.checkVerificationComplete();
    } else if (typeof window.checkVerificationStatus === 'function') {
        window.checkVerificationStatus();
    } else {
        // Fallback to the original function
        const sessionId = '{{ $user->kyc_session_id }}';
        if (sessionId) {
            checkKycStatus(sessionId);
        } else {
            alert('No session available for status check');
        }
    }
}

// Debug function availability on load
document.addEventListener('DOMContentLoaded', function() {
    console.log('KYC Status Card loaded');
    
    // Check if KYC functions are available
    const functions = ['startInlineVerification', 'checkVerificationComplete', 'resetKycVerification'];
    functions.forEach(funcName => {
        if (typeof window[funcName] === 'function') {
            console.log(`✓ ${funcName} is available in status card`);
        } else {
            console.log(`✗ ${funcName} is NOT available in status card`);
        }
    });
});
</script>
@endif