@props(['user' => null, 'dismissible' => true])

@php
    $user = $user ?? Auth::user();
@endphp

@if($user && $user->needsKycVerification() && !session('kyc_banner_dismissed'))
<div class="alert alert-warning alert-dismissible fade show kyc-reminder-banner" role="alert" id="kycReminderBanner">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-shield-alt text-warning" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-bold">
                        @if($user->role === 'employer')
                            Verify Your Company Identity
                        @else
                            Verify Your Identity
                        @endif
                    </h6>
                    <p class="mb-0 small">
                        @if($user->role === 'employer')
                            Build trust with job seekers by verifying your company. Verified employers get 3x more applications.
                        @else
                            Stand out to employers with a verified profile. Verified candidates are 5x more likely to get hired.
                        @endif
                    </p>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <a href="{{ route('kyc.start.form') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-check-circle me-1"></i>
                    Verify Now
                </a>
                @if($dismissible)
                <button type="button" class="btn btn-link btn-sm text-muted p-0 ms-2" onclick="dismissKycBanner()">
                    <i class="fas fa-times"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function dismissKycBanner() {
    document.getElementById('kycReminderBanner').style.display = 'none';
    
    // Store dismissal in session storage for this session
    sessionStorage.setItem('kyc_banner_dismissed', 'true');
    
    // Also send to server to remember for longer
    fetch('{{ route("kyc.dismiss-banner") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).catch(error => console.log('Failed to save banner dismissal'));
}

// Check if banner was dismissed in this session
if (sessionStorage.getItem('kyc_banner_dismissed')) {
    document.getElementById('kycReminderBanner').style.display = 'none';
}
</script>

<style>
.kyc-reminder-banner {
    margin-bottom: 0;
    border: none;
    border-radius: 0;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-bottom: 3px solid #ffc107;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.kyc-reminder-banner .container {
    padding: 1rem;
}

@media (max-width: 768px) {
    .kyc-reminder-banner .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .kyc-reminder-banner .btn {
        margin-top: 0.5rem;
    }
}
</style>
@endif