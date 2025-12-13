<!-- KYC Verification Modal -->
<div class="modal fade" id="kycModal" tabindex="-1" aria-labelledby="kycModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content kyc-modal-enhanced">
            <div class="modal-header">
                <h5 class="modal-title" id="kycModalLabel">
                    <i class="fas fa-shield-check"></i>
                    Identity Verification Required
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-shield-alt text-primary" style="font-size: 3rem;"></i>
                </div>
                
                <h4 class="text-center mb-3">Verify Your Identity to Continue</h4>
                
                <p class="text-center text-muted mb-4">
                    To post jobs and access employer features, you need to complete identity verification.
                </p>
                
                <!-- Current Status Display -->
                @auth
                    @if(Auth::user()->kyc_status !== 'pending')
                        <div class="alert 
                            @if(Auth::user()->kyc_status === 'verified') alert-success
                            @elseif(Auth::user()->kyc_status === 'failed') alert-danger
                            @elseif(Auth::user()->kyc_status === 'expired') alert-warning
                            @else alert-info
                            @endif mb-4">
                            <h6>
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
                            </h6>
                            <p class="mb-0 small">
                                @if(Auth::user()->kyc_status === 'in_progress')
                                    Your verification is in progress. You can continue or start over.
                                @elseif(Auth::user()->kyc_status === 'failed')
                                    Your previous verification failed. Please try again.
                                @elseif(Auth::user()->kyc_status === 'expired')
                                    Your verification session expired. Please start a new verification.
                                @endif
                            </p>
                        </div>
                    @endif
                @endauth
                
                <!-- What you'll need -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-id-card text-primary me-3"></i>
                            <span>Valid government ID</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-camera text-primary me-3"></i>
                            <span>Camera access</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-clock text-primary me-3"></i>
                            <span>5 minutes of your time</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-lightbulb text-primary me-3"></i>
                            <span>Good lighting</span>
                        </div>
                    </div>
                </div>
                
                <!-- Verification Status Polling (hidden by default) -->
                <div id="modalVerificationStatus" class="text-center mt-4" style="display: none;">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6 class="text-info">Verification in Progress</h6>
                            <p class="mb-2 small">Please complete the verification in the popup window.</p>
                            <p class="small text-muted">This modal will automatically update when verification is complete.</p>
                            <div class="mt-3">
                                <button class="btn btn-outline-secondary btn-sm me-2" onclick="window.checkVerificationComplete ? window.checkVerificationComplete() : console.error('Function not available')">
                                    <i class="fas fa-sync me-1"></i>
                                    Check Status
                                </button>
                                <button class="btn btn-outline-warning btn-sm" onclick="window.resetKycVerification ? window.resetKycVerification() : console.error('Function not available')">
                                    <i class="fas fa-redo me-1"></i>
                                    Start Over
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @auth
                    @if(Auth::user()->kyc_status === 'pending')
                        <button type="button" class="btn btn-primary btn-lg flex-fill" onclick="startModalVerification()">
                            <i class="fas fa-play me-2"></i>
                            Start Verification
                        </button>
                    @elseif(in_array(Auth::user()->kyc_status, ['in_progress', 'failed', 'expired']))
                        <button type="button" class="btn btn-primary me-2" onclick="startModalVerification()">
                            <i class="fas fa-play me-2"></i>
                            @if(Auth::user()->kyc_status === 'in_progress')
                                Continue Verification
                            @else
                                Try Again
                            @endif
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="window.resetKycVerification ? window.resetKycVerification() : console.error('Function not available')">
                            <i class="fas fa-redo me-2"></i>
                            Reset
                        </button>
                    @endif
                @endauth
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Include the KYC inline verification script -->
<script src="{{ asset('assets/js/kyc-inline-verification.js') }}"></script>

<script>
// Modal-specific verification function
function startModalVerification() {
    console.log('Starting modal verification...');
    
    // Show the verification status section in the modal
    const modalStatusDiv = document.getElementById('modalVerificationStatus');
    if (modalStatusDiv) {
        modalStatusDiv.style.display = 'block';
    }
    
    // Call the main verification function
    if (typeof window.startInlineVerification === 'function') {
        window.startInlineVerification();
    } else {
        console.error('startInlineVerification function not available');
        alert('Verification function not available. Please refresh the page and try again.');
    }
}

// Enhanced verification success handler for modal
const originalShowVerificationSuccess = window.showVerificationSuccess;
window.showVerificationSuccess = function(message) {
    console.log('🎉 KYC verification successful - handling modal');
    
    // Call the original function to show the popup
    if (originalShowVerificationSuccess) {
        originalShowVerificationSuccess(message);
    }
    
    // Close the modal immediately
    const kycModal = document.getElementById('kycModal');
    if (kycModal) {
        const modal = bootstrap.Modal.getInstance(kycModal);
        if (modal) {
            modal.hide();
        }
    }
    
    // Refresh the page after showing success popup for 3 seconds
    setTimeout(() => {
        location.reload();
    }, 3000);
};

// Enhanced verification error handler for modal
const originalShowVerificationError = window.showVerificationError;
window.showVerificationError = function(message) {
    console.log('❌ KYC verification failed - handling modal');
    
    // Call the original function to show the popup
    if (originalShowVerificationError) {
        originalShowVerificationError(message);
    }
    
    // Close the modal immediately
    const kycModal = document.getElementById('kycModal');
    if (kycModal) {
        const modal = bootstrap.Modal.getInstance(kycModal);
        if (modal) {
            modal.hide();
        }
    }
};

// Show modal when KYC is required
function showKycModal() {
    const kycModal = new bootstrap.Modal(document.getElementById('kycModal'));
    kycModal.show();
}

// Make function globally available
window.showKycModal = showKycModal;
window.startModalVerification = startModalVerification;
</script>
@endpush