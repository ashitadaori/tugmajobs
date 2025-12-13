@extends('front.layouts.app')

@section('content')
<!-- Meta tags for KYC completion handler -->
<meta name="user-id" content="{{ Auth::id() }}">
<meta name="user-role" content="{{ Auth::user()->role }}">
@if(Auth::user()->kyc_session_id)
<meta name="kyc-session-id" content="{{ Auth::user()->kyc_session_id }}">
@endif
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
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
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
                        @if(Auth::user()->kyc_status === 'pending')
                            <button type="button" class="btn btn-primary btn-lg px-5" id="startBtn" onclick="startInlineVerification()">
                                <i class="fas fa-play me-2"></i>
                                Start Verification
                            </button>
                        @elseif(in_array(Auth::user()->kyc_status, ['in_progress', 'failed', 'expired']))
                            <button type="button" class="btn btn-primary btn-lg px-5" id="startBtn" onclick="startInlineVerification()">
                                <i class="fas fa-play me-2"></i>
                                @if(Auth::user()->kyc_status === 'in_progress')
                                    Continue Verification
                                @else
                                    Try Again
                                @endif
                            </button>
                        @elseif(Auth::user()->kyc_status === 'verified')
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Your identity is already verified!</strong>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Verification Status Polling (hidden by default) -->
                    <div id="verificationStatus" class="text-center mt-4" style="display: none;">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h5 class="text-info">Verification in Progress</h5>
                                <p class="mb-2">Please complete the verification in the popup window or on your mobile device.</p>
                                <p class="small text-muted">This page will automatically update when verification is complete.</p>
                                <div class="mt-3">
                                    <button class="btn btn-outline-secondary btn-sm me-2" onclick="window.checkVerificationComplete ? window.checkVerificationComplete() : console.error('checkVerificationComplete not found')">
                                        <i class="fas fa-sync me-1"></i>
                                        Check Status
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="window.resetKycVerification ? window.resetKycVerification() : console.error('resetKycVerification not found')">
                                        <i class="fas fa-redo me-1"></i>
                                        Start Over
                                    </button>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Closed the verification window? Click "Start Over" to reset and begin fresh.
                                    </small>
                                </div>
                            </div>
                        </div>
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
                        
                        @if(app()->environment('local'))
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Testing: Use <code>php artisan kyc:reset [user_id]</code> to reset KYC status
                                </small>
                            </div>
                        @endif
                    </div>
                    
                    @if(Auth::user()->kyc_status !== 'pending')
                        <div class="alert 
                            @if(Auth::user()->kyc_status === 'verified') alert-success
                            @elseif(Auth::user()->kyc_status === 'failed') alert-danger
                            @elseif(Auth::user()->kyc_status === 'expired') alert-warning
                            @else alert-info
                            @endif mt-4">
                            <h5>
                                @if(Auth::user()->kyc_status === 'verified')
                                    <i class="fas fa-check-circle me-2"></i>
                                @elseif(Auth::user()->kyc_status === 'failed')
                                    <i class="fas fa-times-circle me-2"></i>
                                @elseif(Auth::user()->kyc_status === 'expired')
                                    <i class="fas fa-clock me-2"></i>
                                @else
                                    <i class="fas fa-info-circle me-2"></i>
                                @endif
                                Current Status: {{ ucfirst(str_replace('_', ' ', Auth::user()->kyc_status)) }}
                            </h5>
                            <p class="mb-0">
                                @if(Auth::user()->kyc_status === 'in_progress')
                                    <strong>Verification in Progress:</strong> You have a verification session that may have been interrupted. You can continue with the existing session or reset to start fresh.
                                    <br><small class="text-muted">If you accidentally closed the verification window, you can continue where you left off or start over.</small>
                                @elseif(Auth::user()->kyc_status === 'verified')
                                    <strong>Identity Verified:</strong> Your identity was successfully verified on {{ Auth::user()->kyc_verified_at->format('M d, Y \a\t g:i A') }}. No further action is required.
                                @elseif(Auth::user()->kyc_status === 'failed')
                                    <strong>Verification Failed:</strong> Your previous verification attempt was unsuccessful. This could be due to unclear document photos or other issues. You can try again with better lighting and clearer images.
                                @elseif(Auth::user()->kyc_status === 'expired')
                                    <strong>Session Expired:</strong> Your previous verification session timed out. This happens if the verification process is not completed within the allowed time frame. You can start a new verification.
                                @endif
                            </p>
                            
                            @if(in_array(Auth::user()->kyc_status, ['in_progress', 'failed', 'expired']))
                                <div class="mt-3 pt-3 border-top">
                                    <h6><i class="fas fa-lightbulb me-2"></i>What you can do:</h6>
                                    <ul class="mb-0 small">
                                        @if(Auth::user()->kyc_status === 'in_progress')
                                            <li><strong>Continue:</strong> Resume your existing verification session</li>
                                            <li><strong>Reset:</strong> Cancel the current session and start completely fresh</li>
                                        @else
                                            <li><strong>Try Again:</strong> Start a new verification with the same process</li>
                                            <li><strong>Reset:</strong> Clear your verification history and start fresh</li>
                                        @endif
                                        <li><strong>Need Help?</strong> Contact support if you continue to experience issues</li>
                                    </ul>
                                    
                                    <div class="mt-3 text-center">
                                        @if(Auth::user()->kyc_status === 'in_progress')
                                            <button class="btn btn-outline-secondary btn-sm me-2" onclick="window.checkVerificationStatus ? window.checkVerificationStatus() : alert('Function not loaded')">
                                                <i class="fas fa-sync me-1"></i>
                                                Check Status
                                            </button>
                                            <button class="btn btn-outline-warning btn-sm" onclick="window.resetKycVerification ? window.resetKycVerification() : alert('Function not loaded')">
                                                <i class="fas fa-redo me-1"></i>
                                                Start Over
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Include the KYC inline verification script -->
<script src="{{ asset('assets/js/kyc-inline-verification.js') }}"></script>
<!-- Include the KYC completion handler -->
<script src="{{ asset('assets/js/kyc-completion-handler.js') }}"></script>

<script>
// Auto-start polling if user is already in verification process
@if(Auth::user()->kyc_status === 'in_progress')
    document.addEventListener('DOMContentLoaded', function() {
        console.log('User has in_progress KYC status, showing verification status...');
        // Show verification status and start polling for in-progress verifications
        showVerificationStatus();
        startVerificationPolling();
    });
@endif

function showVerificationStatus() {
    const statusDiv = document.getElementById('verificationStatus');
    if (statusDiv) {
        statusDiv.style.display = 'block';
        statusDiv.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        console.log('Verification status section shown');
    } else {
        console.error('Verification status div not found');
    }
}

// Debug function availability
document.addEventListener('DOMContentLoaded', function() {
    console.log('KYC Start page loaded');
    console.log('Current user KYC status: {{ Auth::user()->kyc_status }}');
    
    // Check if functions are available
    const functions = ['startInlineVerification', 'checkVerificationComplete', 'resetKycVerification', 'checkVerificationStatus'];
    functions.forEach(funcName => {
        if (typeof window[funcName] === 'function') {
            console.log(`✓ ${funcName} is available`);
        } else {
            console.error(`✗ ${funcName} is NOT available`);
        }
    });
    
    // Check if verification status section should be visible
    @if(Auth::user()->kyc_status === 'in_progress')
        console.log('User has in_progress status - verification status section should be visible');
        const statusDiv = document.getElementById('verificationStatus');
        if (statusDiv) {
            console.log('Verification status div found, display style:', statusDiv.style.display);
        } else {
            console.error('Verification status div not found!');
        }
    @endif
    
    // Add manual test buttons for debugging
    if ({{ Auth::user()->kyc_status === 'in_progress' ? 'true' : 'false' }}) {
        const debugDiv = document.createElement('div');
        debugDiv.className = 'alert alert-warning mt-3';
        debugDiv.innerHTML = `
            <h6>Debug Controls (Development Only):</h6>
            <button class="btn btn-sm btn-secondary me-2" onclick="testCheckStatus()">Test Check Status</button>
            <button class="btn btn-sm btn-warning" onclick="testResetKyc()">Test Reset KYC</button>
        `;
        document.querySelector('.card-body').appendChild(debugDiv);
    }
});

// Debug test functions
function testCheckStatus() {
    console.log('Testing Check Status function...');
    if (typeof window.checkVerificationComplete === 'function') {
        console.log('Calling checkVerificationComplete...');
        window.checkVerificationComplete();
    } else if (typeof window.checkVerificationStatus === 'function') {
        console.log('Calling checkVerificationStatus...');
        window.checkVerificationStatus();
    } else {
        console.error('No check status function available!');
        alert('Check status function not available');
    }
}

function testResetKyc() {
    console.log('Testing Reset KYC function...');
    if (typeof window.resetKycVerification === 'function') {
        console.log('Calling resetKycVerification...');
        window.resetKycVerification();
    } else {
        console.error('Reset KYC function not available!');
        alert('Reset KYC function not available');
    }
}
</script>
@endpush